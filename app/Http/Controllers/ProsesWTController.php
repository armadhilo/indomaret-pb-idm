<?php

namespace App\Http\Controllers;

use App\Traits\LibraryCSV;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProsesWTController extends Controller
{
    use LibraryCSV;
    public $DB_PGSQL;
    public function __construct()
    { 
        $this->DB_PGSQL = DB::connection('pgsql');

        // try {
        //     $this->DB_PGSQL->beginTransaction();

            
        //     $this->DB_PGSQL->commit();
        // } catch (\Throwable $th) {
            
        //     $this->DB_PGSQL->rollBack();
        //     dd($th);
        //     return response()->json(['errors'=>true,'messages'=>$th->getMessage()],500);
        // }
    }

    public function index(){
        $flag = [
            "flagFTZ" => session()->get('flagFTZ'),
            "flagIGR" => session()->get('flagIGR'),
            "flagSPI" => session()->get('flagSPI'),
            "flagHHSPI" => session()->get('flagHHSPI')
        ];
        return view("menu.proses_wt.index",compact('flag'));
    }

    public function proses_wt(Request $request)
    {
        // dd($request->all());
        $namafile = $request->namafile;
        $dpp_idm = $request->dpp_idm;
        $ppn_idm = $request->ppn_idm;
        $total_idm = $request->total_idm;
        $dpp_igr = $request->dpp_igr;
        $ppn_igr = $request->ppn_igr;
        $total_igr = $request->total_igr;
        $retur_fisik = $request->retur_fisik;
        $retur_peforma  = $request->retur_peforma;
        $UserMODUL = session('userid');
        $KodeIGR = session('KODECABANG');
        $StationMODUL = session("SPI_STATION");
        $dtKey =json_decode(base64_decode($request->dtKey));
        $storage_path = storage_path("app/WT/");
        $filenameFileStorage =  $storage_path.$namafile;
        // $dtRetur = $this->make_csv($filenameFileStorage);
        $dtRetur = [];
        $response = (object)$this->processSalesData($dpp_igr,$ppn_igr, $dpp_idm,$ppn_idm, $retur_fisik,$retur_peforma,$dtKey,$dtRetur, $KodeIGR, $UserMODUL, $StationMODUL,$namafile);
        
        if (isset($response->errors)) {
            $code = 500;
        }else{
            $code = 200;
        }

       return response()->json($response,$code);

    }

    public function processSalesData($dppIgr, $ppnIgr, $dppIdm, $ppnIdm, $returFisik, $returPerforma, $dtKey,$dtRetur, $KodeIGR, $UserMODUL, $StationMODUL,$filename)
    {
        $storage_path = storage_path("app/WT/");
        $dataStruk = [];
        $filenameFileStorage =  $storage_path.$filename;
        // if (abs(($dppIgr + $ppnIgr) - ($dppIdm + $ppnIdm)) < 2000) {
        if (abs(($dppIgr + $ppnIgr) - ($dppIdm + $ppnIdm)) > 2000) { // debug
            if (($returFisik + $returPerforma) || (($dppIgr + $ppnIgr)) > 0) {
                $filePath = $filenameFileStorage;

                if (File::exists($filePath)) {
                        $nSukses = 0;
                        $nTolakan = 0;
                        $output = "";

                        try {
                            $this->DB_PGSQL->beginTransaction();

                            foreach ($dtKey as $key => $dt) {
                                $no_pb = $dt->no_pb[0];
                                $no_dspb = $dt->no_dspb[0];
                                $toko = $dt->toko[0];
                                $sph = $dt->sph[0];
                                $tglsph = date("Y-m-d", strtotime($dt->tglsph[0]));
                                $result = $this->DB_PGSQL->select("CALL sp_create_sales_idm ('$no_dspb','$no_pb','$toko','$KodeIGR','$UserMODUL','$StationMODUL','$sph','$tglsph', '')");
                                if (count($result) > 0 && $result[0]->p_status === 'S') {
                                    $nSukses++;
                                }
                                // $nSukses++; // debug
                            }
                            
                            $this->DB_PGSQL->commit();

                            if (count($dtKey) == $nSukses) {
                                if (count($dtKey) > 0) {
                                    // Creating Struk
                                    $dataStruk = $this->kertasBesar($dtKey);
                                }

                                if (count($dtRetur) > 0) {
                                    $this->insertWt($dtRetur, $nTolakan);
                                }

                                if ($nTolakan == 0 && File::exists($filePath)) {
                                    unlink($filenameFileStorage);
                                }
                                // return ["errors"=>false,"messages"=>"WT Sales Selesai Diproses ". $nTolakan > 0 ? ", Data Retur Double/PLu Tidak Dikenal, Proses Retur DITOLAK!" : "","data_struk"=>$dataStruk];
                                return ["messages"=>"WT Sales Selesai Diproses ","data_struk"=>$dataStruk];
                            } else {
                                return ["errors"=>true, "messages"=>"WT Gagal Diproses! INDOGROSIR"];
                            }

                        } catch (\Exception $e) {
                           $this->DB_PGSQL->rollBack();
                           return ["errors"=>true, "messages"=>"Gagal Karena: " . substr($e->getMessage(), 1, 50)];
                        }
                } else {
                    return ["errors"=>true, "messages"=>"File WT Tidak Ada! INDOGROSIR"];
                }
            } else {
                return ["errors"=>true, "messages"=>"WT Tidak Ada Data!"];
            }
        } else {
            $this->recordTolakan("WT Selisih Tidak Bisa Di Proses!");
            return ["errors"=>true, "messages"=>"WT Selisih Tidak Bisa Di Proses!"];
        }
    }

    public function kertasBesar($data_key)
    {
        $dtKey = $data_key;
        $output = "";
        $NamaToko=  "Nama Toko Tidak Terdaftar";

        $UserMODUL = session('userid');
        $KodeIGR = session('KODECABANG');
        $StationMODUL = session("SPI_STATION");
        
        $objPersh = DB::select("SELECT PRS_NAMACABANG, PRS_SINGKATANWILAYAH, PRS_TELEPON FROM TBMASTER_PERUSAHAAN");

        if (!empty($objPersh)) {
            $output .= $this->strCenter($objPersh[0]->prs_namacabang, 96) . PHP_EOL;
            $output .= $this->strCenter($objPersh[0]->prs_singkatanwilayah, 96) . PHP_EOL;
            $output .= $this->strCenter("Telp." . $objPersh[0]->prs_telepon, 96) . PHP_EOL;
        }

        foreach ($dtKey as $key) {
            $no_dspb = $key->no_dspb[0];
            $toko = $key->toko[0];
            $no_pb = $key->no_pb[0];

            $sql = "SELECT IKL_IDSTRUK as struk, to_Date(IKL_IDVERIFIKASI,'YYYYMMdd') as tgl, ikl_nokoli
                    FROM TBTR_IDMKOLI
                     WHERE IKL_REGISTERREALISASI = '$no_dspb' -- command to debug
                     AND IKL_KODEIDM = '$toko' -- command to debug
                     AND IKL_NOPB = '$no_pb' -- command to debug
                     AND ikl_recordid = '2' -- command to debug
                    LIMIT 1
                    ";

            $koliData = DB::select($sql);

            if (!empty($koliData)) {
                $sql = "SELECT JH_KODEIGR, JH_TRANSACTIONNO, JH_TRANSACTIONDATE, JH_CASHIERSTATION,
                               JH_CASHIERID, JH_TRANSACTIONCREDITAMT, JH_TRANSACTIONAMT,
                               JH_DISCOUNTAMT, TKO_KODECUSTOMER
                        FROM TBTR_JUALHEADER
                        JOIN TBMASTER_TOKOIGR ON JH_KODEIGR = TKO_KODEIGR
                         WHERE JH_TRANSACTIONNO = '".$koliData[0]->struk."'  -- command for debug
                         AND JH_TRANSACTIONDATE::date = '".$koliData[0]->tgl."'::date -- command for debug
                         AND JH_CASHIERID = '".$UserMODUL."'  -- command for debug
                         AND JH_CASHIERSTATION = '".$StationMODUL."'  -- command for debug
                         AND JH_TRANSACTIONTYPE = 'S' -- command for debug
                        -- WHERE JH_TRANSACTIONTYPE = 'S' -- debug
                        -- LIMIT 1 -- debug
                        ";

                $jualHeader = DB::select($sql);

                if (!empty($jualHeader)) {
                    $output .= $this->strCenter(date('d-m-Y (H:i:s)', strtotime($jualHeader[0]->jh_transactiondate)) . " ID:" . $jualHeader[0]->jh_cashierid . " CAB:" . $jualHeader[0]->jh_kodeigr . " STT:" . $jualHeader[0]->jh_cashierstation . " NO.TR:" . $jualHeader[0]->jh_transactionno, 96) . PHP_EOL;
                    $output .= str_repeat(" ", 96) . PHP_EOL;
                    $output .= str_repeat("=", 96) . PHP_EOL;
                    $output .= " NO. NAMA BARANG                            PLU       QTY    H.SATUAN    DISC.            TOTAL " . PHP_EOL;
                    $output .= str_repeat("=", 96) . PHP_EOL;

                    $sql = "SELECT TRJD_PRDCD, TRJD_QUANTITY, PRD_DESKRIPSIPENDEK,
                                   TRJD_UNITPRICE, TRJD_DISCOUNT, TRJD_NOMINALAMT
                            FROM TBTR_JUALDETAIL
                            JOIN TBMASTER_PRODMAST ON TRJD_PRDCD = PRD_PRDCD
                             WHERE TRJD_TRANSACTIONNO = '".$koliData[0]->struk."'    -- command for debug
                             AND DATE_TRUNC('DAY', TRJD_TRANSACTIONDATE) = '".$koliData[0]->tgl."'   -- command for debug
                             AND TRJD_CREATE_BY = '".$UserMODUL."'    -- command for debug
                             AND TRJD_CASHIERSTATION = '".$StationMODUL."'    -- command for debug
                             AND TRJD_TRANSACTIONTYPE = 'S'   -- command for debug
                            -- WHERE TRJD_TRANSACTIONTYPE = 'S' -- debug
                            -- LIMIT 1 -- debug
                            ";

                    $jualDetails = DB::select($sql);

                    $no = 1;
                    foreach ($jualDetails as $detail) {
                        $output .= str_pad($no, 4, ' ', STR_PAD_LEFT) . " " .
                                   str_pad($detail->prd_deskripsipendek, 34, " ") . " " .
                                   "(" . $detail->trjd_prdcd . ") " .
                                   $this->strCenter(number_format($detail->trjd_quantity, 0), 10) .
                                   str_pad(number_format($detail->trjd_unitprice, 0), 10, " ", STR_PAD_LEFT) .
                                   str_pad(number_format($detail->trjd_discount, 0), 10, " ", STR_PAD_LEFT) .
                                   str_pad(number_format($detail->trjd_nominalamt, 0), 16, " ", STR_PAD_LEFT) . PHP_EOL;
                        $no++;
                    }

                    $output .= str_repeat("=", 96) . PHP_EOL;
                    $output .= "TOTAL ITEM : " . str_pad($no, 4, " ", STR_PAD_LEFT) . PHP_EOL;
                    $output .= "TOTAL ( + PPN ) :   " . str_pad(number_format($jualHeader[0]->jh_discountamt, 0), 10) . str_pad(number_format($jualHeader[0]->jh_transactionamt, 0), 16, " ", STR_PAD_LEFT) . PHP_EOL;

                    if ($jualHeader[0]->jh_transactioncreditamt > 0) {
                        $output .= str_pad("PEMBAYARAN KREDIT", 29) . ":" . str_pad(number_format($jualHeader[0]->jh_transactioncreditamt, 0), 15, " ", STR_PAD_LEFT) . PHP_EOL;
                    }
                    if ($jualHeader[0]->jh_transactionamt > 0) {
                        $output .= str_pad("TOTAL PEMBAYARAN", 29) . ":" . str_pad(number_format($jualHeader[0]->jh_transactionamt, 0), 15, " ", STR_PAD_LEFT) . PHP_EOL;
                    }
                    $output .= "MEMBER : " . $jualHeader[0]->tko_kodecustomer . " - " . $NamaToko . PHP_EOL;
                    $output .= "No KOLI  : " . $koliData[0]->ikl_nokoli . PHP_EOL;
                    $output .= $this->strCenter("Selesai : " . date('H:i:s'), 96) . PHP_EOL;
                }
            }
        }


        return $output;
    }

    public function strCenter($string, $width)
    {
        return str_pad($string, $width, ' ', STR_PAD_BOTH);
    }

    public function list_file(Request $request){
         $files = $request->files;
         $kodeIGR = session()->get('KODECABANG');
         $PublicStoragePath = [];
         $lvWt = [];
         $n = 0;

        foreach ($files as $key => $file) {
            $file = $file[0];
            $fileName = $file->getClientOriginalName();
            // $array_csv = $this->csv_to_array($file);
            $storage_path = "WT/";
            $filenameFileStorage =  $storage_path.$fileName;
            if (!File::isDirectory(storage_path($storage_path))) {
                File::makeDirectory(storage_path($storage_path), 0755, true); 
            }
            if (file_exists(storage_path($filenameFileStorage))) {
                unlink(storage_path($filenameFileStorage));
            } 
            //save file
            $path_file = $file->getPathName();
            Storage::put($filenameFileStorage, file_get_contents($path_file));


            if (strlen($fileName) >= 11 && substr($fileName, -11, 2) == "WT") {
                $toko = substr($fileName, -5, 1) . substr($fileName, -3, 3);

                $namaToko = $this->DB_PGSQL->table('tbmaster_tokoigr')
                    ->where('tko_kodeigr', $kodeIGR)
                    ->where('tko_kodeomi', $toko)
                    ->where('tko_namasbu', 'INDOMARET')
                    ->value('tko_namaomi');

                if (empty($namaToko)) {
                    $namaToko = "Nama toko tidak terdaftar";
                }

                $lvWt[] = [
                    'number' => $n + 1,
                    'toko' => $toko,
                    'nama_toko' => $namaToko,
                    'hari_bln' => substr($fileName, -7, 2) . "-" . substr($fileName, -9, 2),
                    'file_wt' => substr($fileName, -11, 11),
                    'backgroundColor' => $n % 2 == 0 ? 'BurlyWood' : null
                    
                ];

                $n++;
            }
        }

        return response()->json(['errors'=>true,'messages'=>'Berhasil','data'=>[
            'data_toko' => $lvWt,
        ]],200);


    }


    public function read_csv(Request $request){
        $flagFTZ = session()->get('flagFTZ');
        $returPerforma = 0;
        $returFisik = 0;
        $dppIdm = 0;
        $ppnIdm = 0;
        $dppIgr = 0;
        $ppnIgr = 0;
        $hdr = 0;
        $qty = 0;
        $ppn = 0;
        $price = 0;
        $nDataProses = 0;
        $Tolakan = 0;
        $noDspb = "";
        $array_noDspb = [];
        $noPb = "";
        $toko = "";
        $docnoIdm = "";
        $tglDocnoIdm = "";
        // $kodeIGR = session()->get('KODECABANG');
        $kodeIGR = "28";
        $flagDoubleDspb = false;
        $flagSudahProses = null;
        $dtKey = [];
        $myTable = [];
        $fileName = $request->file_wt;
        $storage_path = storage_path("app/WT/");
        $filenameFileStorage =  $storage_path.$fileName;
        $file = file_get_contents($filenameFileStorage);
        if (!file_exists($filenameFileStorage)) {
            return response()->json(['errors'=>true,'messages'=>'Data file tidak ada / Tidak tersimpan'],500);
        } 
        $array_csv = $this->csv_to_array($filenameFileStorage);
        
        try {
            $this->DB_PGSQL->beginTransaction();
            foreach ($array_csv as $key => $myRow) {
                $fieldValuesLength = count($myRow);

                if ($fieldValuesLength >= 1 && ($fieldValuesLength == 41 || $fieldValuesLength == 42 || $fieldValuesLength == 43)) {
                  
                    if (strtoupper($myRow['RTYPE']) === "B" || strtoupper($myRow['RTYPE']) === "K") {
                        
                        if (empty(trim($myRow['TOKO'])) || empty(trim($myRow['SHOP']))) {
                            $this->recordTolakan("Field Toko atau Shop Kosong!");
                            $Tolakan += 1;
                            return response()->json(['errors'=>true,'messages'=>"Field Toko atau Shop Kosong!"],500);
                        }
                    
                    
                        if($myRow['TOKO'] === "GI" . $kodeIGR && strtoupper($myRow['RTYPE']) === "B"){
                            if ($noDspb !== $myRow['DOCNO2'] || $noPb !== $myRow['INVNO'] || $toko !== $myRow['SHOP']) {
                    
                                if (!is_null($noDspb)) {
                                    $flagDoubleDspb = false;
                                    for ($i = 0; $i <= 10; $i++) {
                                        if (empty($array_noDspb[$i])) {
                                            $array_noDspb[$i] = $noDspb;
                                            break;
                                        } else {
                                            if ($array_noDspb[$i] === $myRow['DOCNO2']) {
                                                $flagDoubleDspb = true;
                                            }
                                        }
                                    }
                                }
                    
                                $sql = "
                                    SELECT 
                                        SUM(COALESCE(pbo_ttlnilai, 0)) AS dpp, 
                                        SUM(COALESCE(pbo_ttlppn, 0)) AS ppn, 
                                        COALESCE(IKL_RECORDID, '1') AS PROSES
                                    FROM 
                                        tbmaster_pbomi, 
                                        tbtr_idmkoli 
                                    WHERE 
                                        pbo_tglpb::date = ikl_tglpb::date
                                        AND pbo_nopb = ikl_nopb 
                                        AND pbo_kodeomi = ikl_kodeidm 
                                        AND pbo_nokoli = ikl_nokoli 
                                         AND ikl_registerrealisasi = '".$myRow['DOCNO2']."'  -- command for debug
                                         AND ikl_nopb = '".$myRow['INVNO']."'  -- command for debug
                                         AND ikl_kodeidm = '".$myRow['SHOP']."' -- command for debug
                                         -- AND ikl_kodeidm = 'T3EA' -- debug
                                        GROUP BY 
                                        IKL_RECORDID
                                         -- limit 10 -- debug
                                ";
                    
                                $results = DB::select($sql);

                    
                                if (count($results) > 0) {
                                    foreach ($results as $key => $row) {
                                        // if ($key < 1) {  // debug
                                            // debug
                                            $flagSudahProses = $row->proses;
                                            if (!$flagDoubleDspb) {
                                                if ($flagSudahProses == "1") {
                                                    $dppIgr += (float)str_replace(".", ",", $row->dpp);
                                                    $ppnIgr += (float)str_replace(".", ",", $row->ppn);
                                                } else {
                                                    $nDataProses += 1;
                                                }
                                            }

                                        // }// debug
                                    }
                                }
                    
                                $noDspb = $myRow['DOCNO2'];
                                $noPb = $myRow['INVNO'];
                                $toko = $myRow['SHOP'];
                                $docnoIdm = !empty(trim($myRow['DOCNO'])) ? $myRow['DOCNO'] : '';
                                $tglDocnoIdm = !empty(trim($myRow['TGL1'])) ? $myRow['TGL1'] : '';
                    
                                if ($flagSudahProses == "1" && !$flagDoubleDspb) {
                                    if (!isset($dtKey['no_pb'])) {
                                        $dtKey['no_pb'] = [];
                                    }
                                    if (!isset($dtKey['no_dspb'])) {
                                        $dtKey['no_dspb'] = [];
                                    }
                                    if (!isset($dtKey['toko'])) {
                                        $dtKey['toko'] = [];
                                    }
                                    if (!isset($dtKey['sph'])) {
                                        $dtKey['sph'] = [];
                                    }
                                    if (!isset($dtKey['tglsph'])) {
                                        $dtKey['tglsph'] = [];
                                    }
                    
                                    $dtKey['no_pb'][] = $noPb;
                                    $dtKey['no_dspb'][] = $noDspb;
                                    $dtKey['toko'][] = $toko;
                                    $dtKey['sph'][] = $docnoIdm;
                                    $dtKey['tglsph'][] = $tglDocnoIdm;
                                }
                    
                                $hdr += 1;
                                
                            }
                            if ($flagSudahProses == "1") {
                    
                                $qty = !empty($myRow['QTY']) ? (float)str_replace(".", ",", $myRow['QTY']) : 0;
                                $ppn = !empty($myRow['PPNRP_IDM']) ? (float)str_replace(".", ",", $myRow['PPNRP_IDM']) : 0;
                                $price = !empty($myRow['PRICE_IDM']) ? (float)str_replace(".", ",", $myRow['PRICE_IDM']) : 0;
                    
                                $dppIdm += ($qty * $price);
                                $ppnIdm += $ppn;
                            }
                    
                    
                        }elseif ($myRow['TOKO'] === "GI" . $kodeIGR && strtoupper($myRow['RTYPE']) === "K") {
                            
                            // Assuming $myRow is an associative array with the necessary keys such as "PRDCD", "QTY", "PPNRP_IDM", "PRICE_IDM", "DOCNO", "RATE_PPN", "TGL1", "KETERANGAN", "ISTYPE"
                            $sql = "SELECT COUNT(1) FROM tbhistory_pluidm, tbmaster_prodmast "
                                . "WHERE his_pluigr = prd_prdcd AND his_kodeigr = prd_kodeigr AND his_pluidm ='".$myRow['PRDCD']."'";
                            $results = DB::select($sql);
                            
                            if ($results[0]->count == 0) {
                                $this->recordTolakan("PLU " . $myRow['PRDCD'] . " Ini Tidak Ada Di Master IGR!");
                                $Tolakan += 1;
                               
                                return response()->json(['errors'=>true,'messages'=>"Ini Tidak Ada Di Master IGR!"],500);
                            }
                            
                            if (!empty($myRow['QTY'])) {
                                $qty = (float)str_replace(".", ",", $myRow['QTY']);
                            } else {
                                $qty = 0;
                            }
                            
                            if (!empty($myRow['PPNRP_IDM'])) {
                                $ppn = (float)str_replace(".", ",", $myRow['PPNRP_IDM']);
                            } else {
                                $ppn = 0;
                            }
                            
                            if (!empty($myRow['PRICE_IDM'])) {
                                $price = (float)str_replace(".", ",", $myRow['PRICE_IDM']);
                            } else {
                                $price = 0;
                            }
                            
                            if ($price == 0) {
                                $this->recordTolakan("Plu " . $myRow['PRDCD'] . " NRB " . $myRow['DOCNO'] . " Price IDM=0 ");
                                $Tolakan += 1;
                                return response()->json(['errors'=>true,'messages'=>"Plu " . $myRow['PRDCD'] . " Dengan NRB = " . $myRow['DOCNO'] . " Mempunyai Price IDM = 0 , Data WT Ditolak!"],500);
                            }
                            
                            $sql = "SELECT UPPER(COALESCE(kfp_statuspajak,'TIDAK KENA PPN')) FROM tbhistory_pluidm, tbmaster_prodmast, tbmaster_kodefp "
                                . "WHERE his_pluigr = prd_prdcd AND his_kodeigr = prd_kodeigr AND his_pluidm =  '".$myRow['PRDCD']."'"
                                . "AND COALESCE(prd_flagbkp1,'N') = kfp_flagbkp1 AND COALESCE(prd_flagbkp2,'N') = kfp_flagbkp2";
                            
                            $results = DB::select($sql);
                            if (!$flagFTZ && count($results) > 0) {
                                $statusPajak = $results[0]->column1;
                            
                                if ($statusPajak == "KENA PPN" && $ppn == 0) {
                                    $this->recordTolakan("PLU " . $myRow['PRDCD'] . " BKP dan Memiliki PPN = 0!");
                                    $Tolakan += 1;
                                    return response()->json(['errors'=>true,'messages'=> "Plu " . $myRow['PRDCD'] . " Dengan NRB = " . $myRow['DOCNO'] . " Mempunyai PPN = 0!"],500);
                                }
                            
                                if ($statusPajak == "TIDAK KENA PPN" && $ppn > 0) {
                                    $this->recordTolakan("PLU " . $myRow['PRDCD'] . " BTKP dan Memiliki PPN > 0!");
                                    $Tolakan += 1;
                                    return response()->json(['errors'=>true,'messages'=> "Plu " . $myRow['PRDCD'] . " Dengan NRB = " . $myRow['DOCNO'] . " BTKP dan Mempunyai PPN > 0!"],500);
                                }
                            }
                            
                            if ($ppn > 0) {
                                if (isset($myRow['RATE_PPN'])) {
                                    $ppnRate = $myRow['RATE_PPN'];
                                } else {
                                    $edate = $myRow['TGL1'];
                                    $tglnrb = date('Y-m-d', strtotime($edate));
                            
                                    if ($tglnrb < date('Y-m-d',strtotime('2022-04-01'))) {
                                        $ppnRate = 10;
                                    } else {
                                        $sql = "SELECT COALESCE(MAX(COALESCE(prd_ppn,0)),0) FROM tbhistory_pluidm, tbmaster_prodmast WHERE his_pluigr = prd_prdcd AND his_pluidm = '".$myRow['PRDCD']."'";
                                        $results = DB::select($sql);
                                        $ppnRate = $results[0]->column1;
                                    }
                                }
                            
                                if (abs((($qty * $price) * ($ppnRate / 100)) - $ppn) > $ppnRate) {
                                    $this->recordTolakan("PLU " . $myRow['PRDCD'] . " Memiliki PPN <> " . $ppnRate . "%!");
                                    $Tolakan += 1;
                                    return response()->json(['errors'=>true,'messages'=> "Plu " . $myRow['PRDCD'] . " Dengan NRB = " . $myRow['DOCNO'] . " Memiliki PPN <> " . $ppnRate . "%!"],500);
                                }
                            } elseif ($flagFTZ && $ppn > 0) {
                                $this->recordTolakan("Cabang FTZ Mempunyai PPN > 0 ! PLU : " . $myRow['PRDCD']);
                                $Tolakan += 1;
                                return response()->json(['errors'=>true,'messages'=> "Cabang FTZ Mempunyai PPN > 0 ! PLU : " . $myRow['PRDCD']],500);
                                
                            }
                            
                            // ISTYPE logic
                            if (!empty($myRow['KETERANGAN']) && !empty($myRow['ISTYPE'])) {
                                if (substr($myRow['KETERANGAN'], 0, 3) === "010" && $myRow['ISTYPE'] === "01") {
                                    $returPerforma += (($qty * $price) + $ppn);
                                } else {
                                    $returFisik += (($qty * $price) + $ppn);
                                }
                            }
                            
                            // Assuming myTable is a DataTable or a similar structure where $myRow is added
                            $myTable[] = $myRow;
                    
                        }   
                    }
                    
                } else {
                    $this->recordTolakan("Jumlah Column Tidak Standard (41/42/43)");
                    $Tolakan += 1;
                    return response()->json(['errors'=>true,'messages'=> "Jumlah Column Tidak Standard (36/38/39/40), Data WT Ditolak!"],500);
                }
            }

            if ($nDataProses > 0 && $Tolakan == 0) {
                return response()->json(['errors'=>true,'messages'=>"Ada " . $nDataProses . " DSPB Yang Pernah Diproses Sebelumnya Di WT Ini!"],500);
            }
            
            $this->DB_PGSQL->commit();
            $data = [
                "returPerforma" => $returPerforma,
                "returFisik" => $returFisik,
                "dppIdm" => $dppIdm,
                "ppnIdm" => $ppnIdm,
                "dppIgr" => $dppIgr,
                "ppnIgr" => $ppnIgr,
                "hdr" => $hdr,
                "qty" => $qty,
                "ppn" => $ppn,
                "price" => $price,
                "dtKey" => $dtKey,
                "myTable" => $array_csv,
                "nDataProses" => $nDataProses,
                "Tolakan" => $Tolakan
            ];

            return response()->json(['errors'=>false,'data'=>$data,'messages'=> "Berhasil"],200); 
        } catch (\Throwable $th) {
            
            $this->DB_PGSQL->rollBack();
            // dd($th);
            return response()->json(['errors'=>true,'messages'=>$th->getMessage()],500);
        }

    }



    public function insertWt($dtSource, &$nTolakan)
    {
        $cterm = 0;
        $qty = 0;
        $price = 0;
        $gross = 0;
        $ppn = 0;
        $ppnRate = 0;
        $PRICE_IDM = 0;
        $PPNBM_IDM = 0;
        $PPNRP_IDM = 0;
        $totRet = 0;
        $trnsId = 0;
        $tgl1 = null;
        $tgl2 = null;
        $noNrb = '';

        $dt = $dtSource;
        $rows = $dtSource->sortBy('DOCNO');

       

        $nTolakan = 0;
        $totRet = 0;
        $noNrb = '';

        foreach ($dt as $i => $row) {
            // echo "Proses Data Retur ke " . ($i + 1) . " Dari " . count($dt);

            if (trim($row->DOCNO) != '') {
                if (!empty($row->tgl1)) {
                    $tgl1 = date("Y-m-d",strtotime($row->tgl1));
                }

                if (!empty($row->tgl2)) {
                    $tgl2 = date("Y-m-d",strtotime($row->tgl2));
                }

                $sql = "SELECT COUNT(1) FROM tbtr_wt_interface WHERE TOKO = ? AND DOCNO = ? AND TGL1 = ? AND PRDCD = ? AND SHOP = ?";
                $count = DB::selectOne($sql, [$row->toko, $row->docno, $tgl1, $row->prdcd, $row->shop])->count;

                if ($count > 0) {
                    $sql = "SELECT COALESCE(RECID, 'N') as RECID FROM tbtr_wt_interface WHERE TOKO = ? AND DOCNO = ? AND TGL1 = ? AND SHOP = ? GROUP BY RECID";
                    $recid = DB::selectOne($sql, [$row->toko, $row->docno, $tgl1, $row->shop])->RECID;

                    if ($recid == 'N') {
                        if (confirm("Retur Nrb = {$row->docno} Sudah Ada Di Database ,Hapus Data Sebelumnya?") == true) {
                            $sql = "DELETE FROM tbtr_wt_interface WHERE TOKO = ? AND DOCNO = ? AND TGL1 = ? AND SHOP = ?";
                            DB::delete($sql, [$row->toko, $row->docno, $tgl1, $row->shop]);
                        } else {
                            $nTolakan++;
                            return;
                        }
                    } else {
                        $nTolakan++;
                        $this->recordTolakan("Retur Nrb = {$row->docno} Sudah Diproses, Tidak Bisa Di REVISI!");
                        return ['errors'=>true,'messages'=>"Retur Nrb = {$row->docno} Sudah Diproses, Tidak Bisa Di REVISI!"];
                    }
                } else {
                    if ($this->cekPiutang($row->docno, $row->shop, $tgl1)) {
                        $nTolakan++;
                        $this->recordTolakan("Retur Nrb : {$row->docno} Sudah masuk piutang.");
                        return ['errors'=>true,'messages'=>"Retur Nrb = {$row->docno} Sudah Selesai Diproses, Tidak Bisa Di Revisi!"];
                    }

                    if ($noNrb != $row->docno) {
                        $trnsId = DB::table('sequence')->increment('SEQ_RETUR_IDM');
                        $noNrb = $row->docno;
                    }
                }

                $qty = $this->parseDouble($row->qty);
                $price = $this->parseDouble($row->price);
                $gross = $this->parseDouble($row->gross);
                $cterm = $this->parseDouble($row->cterm);
                $ppn = $this->parseDouble($row->ppn);
                $PRICE_IDM = $this->parseDouble($row->price_idm);
                $PPNBM_IDM = $this->parseDouble($row->ppnbm_idm);
                $PPNRP_IDM = $this->parseDouble($row->ppnrp_idm);
                $ppnRate = $this->getPpnRate($row->prdcd, $tgl1, $row->rate_ppn);

                $insertData = [
                    'p_id' => $trnsId,
                    'recid' => 'N',
                    'rtype' => $row->rtype,
                    'docno' => $row->docno,
                    'seqno' => $row->seqno,
                    'div' => $row->div,
                    'prdcd' => $row->prdcd,
                    'qty' => $qty,
                    'price' => $PRICE_IDM,
                    'gross' => $qty * $PRICE_IDM,
                    'cterm' => $cterm,
                    'docno2' => $row->docno2,
                    'istype' => $row->istype,
                    'invno' => $row->invno,
                    'toko' => $row->toko,
                    'date1' => $row->date,
                    'date2' => $row->date2,
                    'keterangan' => $row->keterangan,
                    'ptag' => $row->ptag,
                    'cat_cod' => $row->cat_cod,
                    'lokasi' => $row->lokasi,
                    'tgl1' => $tgl1,
                    'tgl2' => $tgl2,
                    'ppn' => $PPNRP_IDM,
                    'toko_1' => $row->toko_1,
                    'date3' => $row->date3,
                    'docno3' => $row->docno3,
                    'shop' => $row->shop,
                    'price_idm' => $PRICE_IDM,
                    'ppnbm_idm' => $PPNBM_IDM,
                    'ppnrp_idm' => $PPNRP_IDM,
                    'lt' => $row->lt,
                    'rak' => $row->rak,
                    'bar' => $row->bar,
                    'bkp' => $row->bkp,
                    'sub_bkp' => $row->sub_bkp,
                    'plumd' => $row->plumd,
                    'wt_create_dt' => date('Y-m-d'),
                    'wt_create_by' => session()->get('userid'),
                    'nm_wt' => $row->nm_wt,
                    'ppn_rate' => $ppnRate,
                    'jam' => $row->jam ?? '',
                ];

                DB::table('tbtr_wt_interface')->insert($insertData);

                $totRet += ($qty * $PRICE_IDM) + $PPNRP_IDM;
            } else {
                alert("Retur Tidak Mempunyai NRB!");
                $nTolakan++;
                $this->recordTolakan("Retur Tidak Mempunyai NRB!");
                return;
            }
        }

        if (abs($totRet - ($returFisik + $returPerforma)) > 100) {
            echo "Nilai Data yg akan masuk Database = $totRet , Nilai Data WT = " . ($returFisik + $returPerforma) . " , Retur Ditolak!";
            $nTolakan++;
            $this->recordTolakan("Nilai Data yg akan masuk Database = $totRet , Nilai Data WT = " . ($this->returFisik + $this->returPerforma));
            return;
        } else {
            $this->insertProforma();
            echo "Proses WT";
        }
    }

    public function recordTolakan($msg=null, $namawt=null, $toko=null) {
        try {
            $this->DB_PGSQL->beginTransaction();

            $this->DB_PGSQL
            ->table("tbtr_tolakanwt")
            ->insert([
                "tlk_namawt"=> $namawt,
                "tlk_toko"=> $toko,
                "tlk_message"=> $msg,
                "tlk_create_dt"=> date('Y-m-d'),
                "tlk_create_by"=> session()->get('userid'),
            ]);
            $this->DB_PGSQL->commit();

            return ['errors'=>true,'messages'=>$msg];
        } catch (\Throwable $th) {
            
            $this->DB_PGSQL->rollBack();
            // dd($th);
            return ['errors'=>true,'messages'=>$th->getMessage()];
        }
        
    }
}
