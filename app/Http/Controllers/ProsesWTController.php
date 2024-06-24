<?php

namespace App\Http\Controllers;

use App\Traits\LibraryCSV;
use Illuminate\Http\Request;
use DB;

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
        $noDspb = null;
        $noPb = null;
        $toko = null;
        $KodeIGR = '28';//session('KODECABANG'); // Replace with actual value
        // $KodeIGR = session('KODECABANG'); // Replace with actual value
        $dtKey = [];
        // dd($array_csv);
    

        foreach ($array_csv as $key => $value) {
            $temp_csv[$value['TOKO']] [] = $value;
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

        foreach ($temp_csv as $toko => $data_toko_csv) {
            foreach ($data_toko_csv as $key => $value) {
                if ($value['RTYPE'] === "B" || $value['RTYPE'] === "K") {
                    if ($value['TOKO'] === "" || $value['SHOP'] === "") {
                        // dd('masuk 1.1');
                        $this->recordTolakan("Field Toko atau Shop Kosong!",$nama_toko,$value['TOKO']);
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
                                            break;
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
                                           ->whereRaw("pbo_kodeomi = ikl_kodeidm")
                                           ->whereRaw("pbo_nokoli = ikl_nokoli")
                                           ->whereRaw("ikl_registerrealisasi = '".$value['DOCNO2']."'")  // commend for debug
                                           ->whereRaw("ikl_nopb = '".$value['INVNO']."'")  // commend for debug
                                           ->whereRaw("ikl_kodeidm = '".$value['SHOP']."'")  // commend for debug
                                           ->groupBy("ikl_recordid")
                                        //    ->limit(10) // debug
                                           ->toSql();
dd($dt);
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

                            if ($key == 10) {
                                dd($noDspb,$dt);
                            }
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
                            $this->recordTolakan("PLU " . $value['PRDCD'] . " Ini Tidak Ada Di Master IGR!");
                            return response()->json(['message' => 'Proses Retur Di Batalkan!'], 400);
                        }
                        
                        $qty = (int)str_replace('.', ',', $value['QTY']) ?: 0;
                        $ppn = (int)str_replace('.', ',', $value['PPNRP_IDM']) ?: 0;
                        $price = (int)str_replace('.', ',', $value['PRICE_IDM']) ?: 0;

                        if ($price == 0) {
                            $this->recordTolakan("Plu " . $value['PRDCD'] . " NRB " . $myRow['DOCNO'] . " Price IDM=0 ");
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
                                    $this->recordTolakan("PLU " . $value['PRDCD'] . " BKP dan Memiliki PPN = 0!");
                                    return response()->json(['message' => 'Plu ' . $value['PRDCD'] . ' Mempunyai PPN = 0!'], 400);
                                }
                                if ($dtGetPPN[0]->kfp_statuspajak === "TIDAK KENA PPN" && $ppn > 0) {
                                    $this->recordTolakan("PLU " . $value['PRDCD'] . " TIDAK BKP dan Memiliki PPN > 0!");
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
        }
        
            return response()->json(['errors'=>true,'messages'=>'Berhasil','data'=>[
                'data_toko' => $list_toko,
                'data_file' => $temp_csv,
                'data_key' => $dtKey,
                'retur_performa' => $returPerforma,
                'retur_fisik' => $returFisik,
                'dpp_igr' => $dppIgr,
                'ppn_igr' => $ppnIgr,
                'total_igr'=>$dppIgr+$ppnIgr,
                'dpp_idm' => $dppIdm,
                'ppn_idm' => $ppnIdm,
                'total_idm'=>$dppIdm+$ppnIdm
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
