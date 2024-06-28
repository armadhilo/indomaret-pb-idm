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
        $file = $request->file;
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

        $this->processSalesData($dpp_igr,$ppn_igr, $dpp_idm,$ppn_idm, $retur_fisik,$retur_peforma, $KodeIGR, $UserMODUL, $StationMODUL);

    }

    public function processSalesData($dppIgr, $ppnIgr, $dppIdm, $ppnIdm, $returFisik, $returPerforma, $dtKey,$dtRetur, $KodeIGR, $UserMODUL, $StationMODUL)
    {
        $storage_path = "app/WT/";
        $newFileName = 'WT_FILE.VZG';
        $dataStruk = [];
        $filenameFileStorage =  $storage_path.$newFileName;
        if (abs(($dppIgr + $ppnIgr) - ($dppIdm + $ppnIdm)) < 2000) {
            if (($returFisik + $returPerforma) || (($dppIgr + $ppnIgr)) > 0) {
                $filePath = $filenameFileStorage;

                if (File::exists($filePath)) {
                        $nSukses = 0;
                        $nTolakan = 0;
                        $output = "";

                        try {
                            $this->DB_PGSQL->beginTransaction();

                            foreach ($dtKey as $key => $dt) {
                                $no_pb = $dt->no_pb;
                                $no_dspb = $dt->no_dspb;
                                $toko = $dt->toko;
                                $sph = $dt->sph;
                                $tglsph = date("Y-m-d", strtotime($dt->tglsph));
                                $result = $this->DB_PGSQL->select("CALL sp_create_sales_idm (?, ?, ?, ?, ?, ?, ?, ?, '')", [$no_dspb,$no_pb,$toko,$KodeIGR,$UserMODUL,$StationMODUL,$sph,$tglsph]);
    
                                if (count($result) > 0 && $result[0]->p_status === 'S') {
                                    $nSukses++;
                                }
                            }
                            
                            $this->DB_PGSQL->commit();

                            if (count($dtKey) == $nSukses) {
                                if (count($dtKey) > 0) {
                                    // Creating Struk
                                    $dataStruk = $this->createStruk();
                                }

                                if (count($dtRetur) > 0) {
                                    $this->insertWt($dtRetur, $nTolakan);
                                }

                                if ($nTolakan == 0 && File::exists($filePath)) {
                                    unlink(storage_path($filenameFileStorage));
                                }

                                $detailMessage = $nTolakan > 0 ? ", Data Retur Double/PLu Tidak Dikenal, Proses Retur DITOLAK!" : "";
                                return ["messages"=>"WT Sales Selesai Diproses ".$detailMessage,"data_struk"=>$dataStruk];
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

    public function list_file(Request $request){
        // dd($request->all());
        $file = $request->file;
        $fileName = $request->file('file')->getClientOriginalName();
        $array_csv = $this->csv_to_array($file);
        $temp_csv = [];
        $list_toko = [];
        $flagSudahProses = 0;
        $tolakan = 0;
        $nDataProses = 0;
        $hdr = 0;
        $returPerforma = 0;
        $returFisik = 0;
        $dppIgr = 0;
        $ppnIgr = 0;
        $dppIdm = 0;
        $ppnIdm = 0;
        $qty = 0;
        $ppn = 0;
        $price = 0;
        $noDspb = null;
        $noPb = null;
        $toko = null;
        $docnoIdm = null;
        $tglDocnoIdm = null;
        // $toko = null;
        $KodeIGR = '28';//session('KODECABANG'); // Replace with actual value
        // $KodeIGR = session('KODECABANG'); // Replace with actual value
        $dtKey = [];
        // dd($array_csv);
    
        $file = $request->file('file');
        $storage_path = "app/WT/";
        $newFileName = 'WT_FILE.VZG';
        $filenameFileStorage =  $storage_path.$newFileName;

        if (!File::isDirectory(storage_path($storage_path))) {
            
            File::makeDirectory(storage_path($storage_path), 0755, true); 
        }
        if (file_exists(storage_path($filenameFileStorage))) {
            
            unlink(storage_path($filenameFileStorage));
        } 
        $PublicStoragePath = $file->storeAs('WT', $newFileName);


        foreach ($array_csv as $key => $value) {
            $temp_csv[$value['SHOP']] [] = $value;
        }
        
        foreach ($temp_csv as $key => $value) {
            // get nama toko
            $get_nama_toko = $this->DB_PGSQL 
                              ->table("tbmaster_tokoigr")
                              ->selectRaw("TKO_NAMAOMI")
                              ->whereRaw("TKO_KODEIGR =  '".$KodeIGR."'") 
                              ->whereRaw("TKO_KODEOMI = '$key'") 
                              ->whereRaw("TKO_NAMASBU = 'INDOMARET'") 
                              ->get();
                              
            if (!isset($get_nama_toko[0]->tko_namaomi)) {
                $nama_toko = "Nama Toko Tidak Ditemukan";
            }else {
                $nama_toko = $get_nama_toko[0]->tko_namaomi;
            }
            $list_toko []=(object)[
                'toko' => $key,
                'nama_toko' => $nama_toko,
                'hari_bln'=>substr($fileName, -7, 2) . '-' . substr($fileName, -9, 2),
                'file_wt'=> $fileName
            ];

        }

        foreach ($temp_csv as $keytoko => $data_toko_csv) {

            foreach ($data_toko_csv as $key => $value) {
                if ($value['RTYPE'] === "B" || $value['RTYPE'] === "K") {
                    if ($value['TOKO'] === "" || $value['SHOP'] === "") {
                        // dd('masuk 1.1');
                        $this->recordTolakan("Field Toko atau Shop Kosong!",$nama_toko,$value['SHOP']);
                        $tolakan += 1;
                        break;
                    }
                    if ($value['TOKO'] === "GI" . $KodeIGR  && $value['RTYPE'] === "B") {
                        // dd('masuk 1.2');
                        // Process "B" type records
                        if ($value['TOKO'] === "GI" . $KodeIGR  && $value['RTYPE'] === "B") {

                            if ($noDspb !== $value['DOCNO2'] || $noPb !== $value['INVNO'] || $toko !== $value['SHOP']) {

                                $flagDoubleDspb = false;
                                if ($noDspb !== null) {
                                    for ($i = 0; $i < 11; $i++) {
                                        if ($array[$i] === null) {
                                            $array[$i] = $noDspb;
                                            // break;
                                        } else if ($array[$i] === $value['DOCNO2'] ) {
                                            $flagDoubleDspb = true;
                                        }
                                    }
                                }
                                
                                $dt = $this->DB_PGSQL 
                                        ->table($this->DB_PGSQL->raw("tbmaster_pbomi, tbtr_idmkoli"))
                                        ->selectRaw("
                                                SUM(COALESCE(pbo_ttlnilai, 0)) AS dpp, SUM(COALESCE(pbo_ttlppn, 0)) AS ppn, COALESCE(IKL_RECORDID, '1') AS PROSES
                                        ")
                                        ->whereRaw("pbo_tglpb::date = ikl_tglpb::date")
                                        ->whereRaw("pbo_nopb = ikl_nopb")
                                        ->whereRaw("pbo_kodeomi = ikl_kodeidm")  // commend for debug
                                        ->whereRaw("pbo_nokoli = ikl_nokoli")  // commend for debug
                                        ->whereRaw("ikl_registerrealisasi = '".$value['DOCNO2']."'")  // commend for debug
                                        ->whereRaw("ikl_nopb = '".$value['INVNO']."'")  // commend for debug
                                        ->whereRaw("ikl_kodeidm = '".$value['SHOP']."'")  // commend for debug
                                        ->groupBy("ikl_recordid")
                                        //    ->limit(10) // debug
                                        ->get();

                                foreach ($dt as $row) {
                                    $flagSudahProses = $row->proses;
                                    if (!$flagDoubleDspb) {
                                        if ($flagSudahProses == "1") {
                                            $dppIgr += (int)str_replace('.', ',', $row->dpp);
                                            $ppnIgr += (int)str_replace('.', ',', $row->ppn);
                                        } else {
                                            $nDataProses += 1;
                                        }
                                    }
                                }
            
                                $noDspb = $value['DOCNO2'];
                                $noPb = $value['INVNO'];
                                $toko = $value['SHOP'];
                                $docnoIdm = trim($value['DOCNO']) ?: "";
                                $tglDocnoIdm = trim($value['TGL1']) ?: "";

                                if ($flagSudahProses == "1" && !$flagDoubleDspb) {
                                    $dtKey[] = [
                                        'no_pb' => $noPb,
                                        'no_dspb' => $noDspb,
                                        'toko' => $toko,
                                        'sph' => $docnoIdm,
                                        'tglsph' => $tglDocnoIdm,
                                    ];
                                }
            
                                $hdr += 1;

                            } 

                            // if ($key == 10) {
                            //     dd($noDspb,$dt);
                            // }

                            if ($flagSudahProses == "1") {
                                $qty = (int)str_replace('.', ',', $value['QTY']) ?: 0;
                                $ppn = (int)str_replace('.', ',', $value['PPNRP_IDM']) ?: 0;
                                $price = (int)str_replace('.', ',', $value['PRICE_IDM']) ?: 0;
            
                                $dppIdm += ($qty * $price);
                                $ppnIdm += $ppn;
                            }
                            

                        }
                    }elseif ($value['TOKO'] === "GI" . $KodeIGR  && $value['RTYPE'] === "K") {
                        $cmdCount = $this->DB_PGSQL 
                                        ->table($this->DB_PGSQL->raw("tbhistory_pluidm, tbmaster_prodmast"))
                                        ->selectRaw("COUNT(1)")
                                        ->whereRaw(" his_pluigr = prd_prdcd")
                                        ->whereRaw(" his_kodeigr = prd_kodeigr")
                                        ->whereRaw(" his_pluidm = '".$value['PRDCD']."'")
                                        ->get();
                        $cmdCount = $cmdCount[0]->count;

                        if ($cmdCount[0]->count === 0) {
                            $this->recordTolakan("PLU " . $value['PRDCD'] . " Ini Tidak Ada Di Master IGR!",$nama_toko,$value['SHOP']);
                            return response()->json(['message' => 'Proses Retur Di Batalkan!'], 400);
                        }
                        
                        $qty = (int)str_replace('.', ',', $value['QTY']) ?: 0;
                        $ppn = (int)str_replace('.', ',', $value['PPNRP_IDM']) ?: 0;
                        $price = (int)str_replace('.', ',', $value['PRICE_IDM']) ?: 0;

                        if ($price == 0) {
                            $this->recordTolakan("Plu " . $value['PRDCD'] . " NRB " . $myRow['DOCNO'] . " Price IDM=0 ",$nama_toko,$value['SHOP']);
                            return response()->json(['message' => 'Plu ' . $value['PRDCD'] . ' Mempunyai Price IDM = 0 , Data WT Ditolak!'], 400);
                        }



                        

                        $dtGetPPN = $this->DB_PGSQL 
                                        ->table($this->DB_PGSQL->raw("tbhistory_pluidm, tbmaster_prodmast, tbmaster_kodefp"))
                                        ->selectRaw("UPPER(COALESCE(kfp_statuspajak,'TIDAK KENA PPN')) as status_ppn")
                                        ->whereRaw(" his_pluigr = prd_prdcd")
                                        ->whereRaw(" his_kodeigr = prd_kodeigr")
                                        ->whereRaw(" his_pluidm = '".$value['PRDCD']."'")
                                        ->whereRaw(" COALESCE(prd_flagbkp1,'N') = kfp_flagbkp1")
                                        ->whereRaw(" COALESCE(prd_flagbkp2,'N') = kfp_flagbkp2")
                                        ->get();
                        $dtGetPPN =  $dtGetPPN[0]->status_ppn;
                        if (!$flagFTZ) {
                            if (count($dtGetPPN) > 0) {
                                if ($dtGetPPN[0]->kfp_statuspajak === "KENA PPN" && $ppn == 0) {
                                    $this->recordTolakan("PLU " . $value['PRDCD'] . " BKP dan Memiliki PPN = 0!",$nama_toko,$value['SHOP']);
                                    return response()->json(['message' => 'Plu ' . $value['PRDCD'] . ' Mempunyai PPN = 0!'], 400);
                                }
                                if ($dtGetPPN[0]->kfp_statuspajak === "TIDAK KENA PPN" && $ppn > 0) {
                                    $this->recordTolakan("PLU " . $value['PRDCD'] . " TIDAK BKP dan Memiliki PPN > 0!",$nama_toko,$value['SHOP']);
                                    return response()->json(['message' => 'Plu ' . $value['PRDCD'] . ' Mempunyai PPN > 0!'], 400);
                                }
                            }
                        }
        
                        $returFisik += ($qty * $price);
                        $returPerforma += $ppn;

                    }
                } else {
                    $tolakan += 1;
                }
            
                
            }

            $list_input[$keytoko][] = (object)[  
                'dpp_igr' => $dppIgr,
                'ppn_igr' => $ppnIgr,
                'total_igr'=>$dppIgr+$ppnIgr,
                'dpp_idm' => $dppIdm,
                'ppn_idm' => $ppnIdm,
                'total_idm'=>$dppIdm+$ppnIdm,
                'retur_performa' => $returPerforma,
                'retur_fisik' => $returFisik,
                // 'noDspb' => $noDspb,
                // 'noPb' => $noPb,
                // 'toko' => $toko,
                // 'docnoIdm' => $docnoIdm,
                // 'tglDocnoIdm' => $tglDocnoIdm,
            ];
        }

    
        return response()->json(['errors'=>true,'messages'=>'Berhasil','data'=>[
            'data_input' => $list_input,
            'data_toko' => $list_toko,
            'data_file' => $temp_csv,
            'data_key' => $dtKey,
        ]],200);


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