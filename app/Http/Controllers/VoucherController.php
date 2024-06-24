<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use DB;

class VoucherController extends Controller
{
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
        return view("menu.voucher.index",compact('flag'));
    }

    public function voucher_load(Request $request){
        // $this->create_table_log_npb();

        $tanggal = isset($request->tanggal)?$request->tanggal:null;
        $data = $this->initial($tanggal);


        return response()->json(['errors'=>false,'messages'=>'berhasi','data'=>$data],200);
    }
    
    public function initial($tanggal = null, $flag_detail = null, $mode_program = false){
        $picking = $siapDspb = $selesaiDspb = $jmlhPb = 0;
        

        if ($mode_program) {
           $selesaiDspb_condition =  " TKO_KODESBU = 'O'";
           $jmlhPb_condition =  " TKO_KODESBU = 'O'";
        } else {
           $selesaiDspb_condition =  " TKO_KODESBU = 'I'";
           $jmlhPb_condition =  " TKO_KODESBU = 'I'";
           
        }
        
        // ===========================
        // Query to get total jumlah PB
        // ===========================
        $jmlhPb =  $this->DB_PGSQL
                        ->table("tbtr_header_materai_voucher")
                        ->join("tbmaster_tokoigr",function($join){
                            $join->on("hmv_kodetoko","=","tko_kodeomi");
                        })
                        ->selectRaw(" COUNT(*) as count ");
        if (($tanggal)) {
            $jmlhPb = $jmlhPb
                       ->whereRaw("hmv_tgltransaksi::date = '$tanggal'::date");     
        }
        $jmlhPb = $jmlhPb
                  ->whereRaw($jmlhPb_condition)
                  ->get();
        $jmlhPb = $jmlhPb[0]->count;
                   
        // ===========================
        // Query to get total jumlah picking
        // ===========================
        $query_picking =  $this->DB_PGSQL
                        ->table("tbtr_header_materai_voucher")
                        ->join("tbmaster_pbomi",function($join){
                            $join->on("hmv_kodetoko" ,"=" ,"pbo_kodeomi") 
                                ->on("hmv_nopb" ,"=" ,"pbo_nopb") 
                                ->on("hmv_tglpb" ,"=" ,"pbo_tglpb");
                        })
                        ->join("plu_materai_voucher",function($join){
                            $join->on("pmv_pluidm", "=" ,"pbo_pluomi");
                        })
                        ->selectRaw(" HMV_KODETOKO KODETOKO, HMV_NOPB NOPB, HMV_TGLPB TGLPB, HMV_ITEMPB ITEMPB ")
                        ->distinct()
                        ->whereRaw("PMV_PLUIGR IS NOT NULL ")
                        ->whereRaw("HMV_FLAG = '1' ");
        if (($tanggal)) {
            $query_picking = $query_picking
                        ->whereRaw("hmv_tgltransaksi::date = '$tanggal'::date");     
        }
        $query_picking = $query_picking->toSql();
        $picking =  $this->DB_PGSQL
                        ->table($this->DB_PGSQL->raw("($query_picking) as query"))
                        ->selectRaw(" COUNT(*) as count ")
                        ->get();
      
        $picking = $picking[0]->count;


        // ===========================
        // Query to get total jumlah siapDspb
        // ===========================
        $query_siapDspb =  $this->DB_PGSQL
                        ->table("tbtr_header_materai_voucher")
                        ->join("tbmaster_pbomi",function($join){
                            $join->on("hmv_kodetoko" ,"=" ,"pbo_kodeomi") 
                                ->on("hmv_nopb" ,"=" ,"pbo_nopb") 
                                ->on("hmv_tglpb" ,"=" ,"pbo_tglpb");
                        })
                        ->join("plu_materai_voucher",function($join){
                            $join->on("pmv_pluidm", "=" ,"pbo_pluomi");
                        })
                        ->selectRaw(" HMV_KODETOKO KODETOKO, HMV_NOPB NOPB, HMV_TGLPB TGLPB, HMV_ITEMPB ITEMPB ")
                        ->distinct()
                        ->whereRaw("PMV_PLUIGR IS NOT NULL ")
                        ->whereRaw("HMV_FLAG = '4' ");
        if (($tanggal)) {
            $query_siapDspb = $query_siapDspb
                        ->whereRaw("hmv_tgltransaksi::date = '$tanggal'::date");     
        }
        $query_siapDspb = $query_siapDspb->toSql();
        $siapDspb =  $this->DB_PGSQL
                        ->table($this->DB_PGSQL->raw("($query_siapDspb) as query"))
                        ->selectRaw(" COUNT(*) as count ")
                        ->get();
      
        $siapDspb = $siapDspb[0]->count;

         // ===========================
        // Query to get total selesaiDspb
        // ===========================
        $selesaiDspb =  $this->DB_PGSQL
                        ->table("tbtr_header_materai_voucher")
                        ->join("tbmaster_tokoigr",function($join){
                            $join->on("hmv_kodetoko","=","tko_kodeomi");
                        })
                        ->selectRaw(" COUNT(*) as count ")
                        ->whereRaw("HMV_FLAG = '5'");
        if ($tanggal) {
            $selesaiDspb = $selesaiDspb
                       ->whereRaw("hmv_tgltransaksi::date = '$tanggal'::date");     
        }
        $selesaiDspb = $selesaiDspb
                  ->whereRaw($selesaiDspb_condition)
                  ->get();
        $selesaiDspb = $selesaiDspb[0]->count;

                           
        // Set labels
        $lblPB = $jmlhPb;
        $lblPicking = $picking;
        $lblDspb = $selesaiDspb;
        $lblSDspb = $siapDspb;

       
        $query_dt = $this->DB_PGSQL
                         ->table("tbtr_header_materai_voucher")
                         ->join("tbmaster_pbomi",function($join){
                             $join->on("hmv_kodetoko" ,"=" ,"pbo_kodeomi") 
                                 ->on("hmv_nopb" ,"=" ,"pbo_nopb") 
                                 ->on("hmv_tglpb" ,"=" ,"pbo_tglpb");
                         })
                         ->join("plu_materai_voucher",function($join){
                             $join->on("pmv_pluidm", "=" ,"pbo_pluomi");
                         })
                         ->selectRaw("
                         CASE WHEN coalesce(HMV_FLAG,'0') = '1' THEN 'Siap Picking' ELSE CASE WHEN coalesce(HMV_FLAG,'0') = '4' THEN 'Siap DSPB' ELSE 'Selesai DSPB' END END STAT,
                         HMV_KODETOKO KODETOKO, HMV_NOPB NOPB, HMV_TGLPB::date TGLPB, HMV_ITEMPB ITEMPB  
                         ")
                         ->distinct()
                         ->whereRaw("PMV_PLUIGR IS NOT NULL ");
        if ($tanggal) {
            $query_dt = $query_dt
                        ->whereRaw("hmv_tgltransaksi::date = '$tanggal'::date");     
        }
        $query_dt = $query_dt->toSql();

        $dt = $this->DB_PGSQL
                   ->table($this->DB_PGSQL->raw("($query_dt) a"))
                   ->selectRaw("ROW_NUMBER() OVER() NO, a.*")
                   ->get();
        
        $lblMonitoring =" PB Terupload";
        $data = [
                   "pb_total" => $jmlhPb,
                   "siap_picking" => $picking,
                   "siap_dspb" => $siapDspb,
                   "selesai_dspb" => $selesaiDspb,
                   "list_data"=> $dt,
                   "label" =>$lblMonitoring,
                   "count" =>count($dt)
                   ];

        return $data;

    }

    public function dspb_vm($kodetoko = null, $tglpb = null, $nopb= null){
        
         try {
            $this->DB_PGSQL->beginTransaction();

            $noDspb = $this->DB_PGSQL->select("SELECT NEXTVAL('SEQ_NPB')")[0]->nextval;
            $thnPb = $this->DB_PGSQL->select("SELECT TO_CHAR(CURRENT_DATE, 'YY')")[0]->to_char;
            $tglServer = date('Y-m-d');
            $noDspb = $thnPb . str_pad($noDspb, 5, "0", STR_PAD_LEFT);
            $kodeDCIDM = "";
            
            $dt =  $this->DB_PGSQL
                        ->table("master_supply_idm")
                        ->selectRaw("msi_kodedc")
                        ->whereRaw("msi_kodetoko = '$kodetoko'")
                        ->get();
                        
            if (count($dt) > 0) {
                $kodeDCIDM = $dt[0]->msi_kodedc;
            }
            
            $dtD = $this->DB_PGSQL
                        ->table("tbmaster_pbomi")
                        ->join('tbmaster_prodmast',function($join){
                            $join->on("pbo_pluigr","=","prd_prdcd");
                        })
                        ->selectRaw("
                            '*' recid,
                            NULL rtype,
                            'test'  docno,
                            ROW_NUMBER() OVER() seqno,
                            pbo_nopb picno,
                            NULL picnot,
                            TO_CHAR(pbo_tglpb, 'dd-MM-YYYY') pictgl,
                            pbo_pluomi prdcd,
                            (select prd_deskripsipendek from tbmaster_prodmast
                            where prd_prdcd = pbo_pluigr LIMIT 1) nama,
                            pbo_kodedivisi div,
                            pbo_qtyorder qty,
                            pbo_qtyrealisasi sj_qty,
                            pbo_hrgsatuan price,
                            pbo_ttlnilai gross,
                            pbo_ttlppn ppnrp,
                            pbo_hrgsatuan hpp,
                            pbo_kodeomi toko,
                            'V-' keter,
                            TO_CHAR(CURRENT_DATE, 'dd-MM-YYYY') tanggal1,
                            TO_CHAR(pbo_tglpb, 'dd-MM-YYYY') tanggal2,
                            pbo_nopb docno2,
                            NULL lt,
                            NULL rak,
                            NULL bar,
                            (SELECT 'GI' || prs_kodeigr
                            FROM tbmaster_perusahaan
                            LIMIT 1) kirim,
                            lpad(pbo_NOKOLI,12,'0') dus_no,
                            COALESCE(prd_ppn, 0) ppn_rate,
                            COALESCE(prd_flagbkp1, 'N') BKP,
                            COALESCE(prd_flagbkp2, 'N') SUB_BKP
                        
                        ")
                        ->whereRaw(" PBO_TGLPB::timestamp = '$tglpb'::timestamp ")
                        ->whereRaw(" pbo_nopb = '$nopb'")
                        ->whereRaw(" pbo_kodeomi = '$kodetoko'")
                        ->whereRaw(" pbo_qtyrealisasi > 0")
                        ->whereRaw(" pbo_recordid = '4'")
                        ->whereRaw(" pbo_nokoli like '04%'")
                        ->get();
                       
            if (count($dtD)) {
                $update_db= $this->update_db($noDspb, $kodetoko,$nopb,$tglpb);
                $nmNpb = "NPV" . $dtD[0]->kirim . $dtD[0]->toko . $tglServer;

                // make csv

                // if (file_exists($_txtPath . DIRECTORY_SEPARATOR . $nmNpb . ".ZIP")) {
                //     MessageDialog::show(EnumMessageType::Information, EnumCommonButtonMessage::Ok, "Tunggu 1 Menit agar NPV tidak mempunyai Nama yang sama!", "INDOGROSIR");
                //     $lblMonitoring->setText("Waiting 50 s!");
                //     Application::doEvents();
                //     sleep(50);

                //     if ($cn->state == ConnectionState::Closed) {
                //         $cn->open();
                //     }
                //     $tglServer = date('Y-m-d');
                // }
                $lblMonitoring ="Write NPV!";
                $jamCreateWeb = date("H:i:s");
                $jamCreateCSV = date("H:i:s");

                 // buat csv

                 $nmNpb = "NPV" . $dtD[0]->kirim . $dtD[0]->toko . $tglServer;
                //  DSPB::writeCSV("NPV" . $dtD[0]->kirim . $dtD[0]->toko . $_tglServer->format('YmdHis'), $dtD, $_txtPath, false);
                 
                 // end buat csv

                 $dtH = $this->DB_PGSQL
                                ->table("tbmaster_pbomi as a")
                                ->selectRaw("
                                    '" . $noDspb . "' docno,
                                    TO_CHAR(CURRENT_DATE, 'dd-MM-YYYY') doc_date,
                                    pbo_kodeomi toko,
                                    (SELECT 'GI' || prs_kodeigr FROM tbmaster_perusahaan LIMIT 1) gudang, 
                                    COUNT(DISTINCT pbo_pluomi) item, SUM(pbo_qtyorder) qty, 
                                    SUM(pbo_ttlnilai) gross, NULL koli, NULL kubikasi 
                                ")
                                ->whereRaw("A.PBO_TGLPB = TO_DATE(?, 'dd/MM/YYYY')  ")
                                ->whereRaw("pbo_nopb = '$nopb'")
                                ->whereRaw("pbo_kodeomi = '$kodetoko'")
                                ->whereRaw("pbo_nokoli like '04%' ")
                                ->whereRaw("PBO_QTYREALISASI > 0 ")
                                ->groupBy("pbo_tglpb","pbo_kodeomi")
                                ->get();
                if (count($dtH) > 0) {
                    // buat csv
                    $nmRpb = "XPV" . $dtH[0]->gudang . $dtH[0]->toko . $_tglServer->format('YmdHis');
                    // DSPB::writeCSV("XPV" . $dtH[0]->gudang . $dtH[0]->toko . $_tglServer->format('YmdHis'), $dtH, $_txtPath, false);
                    // end buat csv
                }
                // buat zip ??
                // Ionic.Zip
                //   $listFile = [];
                //   $listFile[] = $_txtPath . DIRECTORY_SEPARATOR . $nmNpb . ".CSV";
                //   $listFile[] = $_txtPath . DIRECTORY_SEPARATOR . $nmRpb . ".CSV";
                //   Zip_File($_txtPath . DIRECTORY_SEPARATOR . $nmNpb . ".ZIP", $listFile);
                // end buat zip ??


                // SIMPAN NAMA NPB
                $this->simpanDSPB($nmNpb . ".ZIP", $kodetoko, $nopb, 0, 0, $noDspb, "V- PBVOUCHER");

                // Caesar_Encrypt_AllNumeric(noDspb & "9999", _tglServer)
                $npbAspera = $this->DB_PGSQL
                                ->table("tbmaster_webservice")
                                ->selectRaw("COALESCE(ws_aktif, 0) as ws_aktif")
                                ->whereRaw("ws_nama = 'NPB'")
                                ->get();
                $npbAspera = $npbAspera[0]->ws_aktif;

                // Check and delete CSV files
                // if (File::exists($_txtPath . DIRECTORY_SEPARATOR . $nmNpb . ".CSV")) {
                //     File::delete($_txtPath . DIRECTORY_SEPARATOR . $nmNpb . ".CSV");
                // }
                // if (File::exists($_txtPath . DIRECTORY_SEPARATOR . $nmRpb . ".CSV")) {
                //     File::delete($_txtPath . DIRECTORY_SEPARATOR . $nmRpb . ".CSV");
                // }

                  // Retrieve npbIP and npbGudang values
                    
                  $npbAsperaCondition = ($kodeDCIDM !== "") ? "AND ws_dc = '" . $kodeDCIDM . "'" : "";
                  $npbIP = $this->DB_PGSQL->select("SELECT ws_url FROM tbmaster_webservice WHERE ws_nama = 'NPB' AND COALESCE(ws_aktif, 0) = 1 " . $npbAsperaCondition);
                  $npbGudang = ($kodeDCIDM !== "") ? $kodeDCIDM : $this->DB_PGSQL->select("SELECT ws_DC FROM tbmaster_webservice WHERE ws_nama = 'NPB' " . $npbAsperaCondition);

                  if (!is_null($npbIP)) {

                    $okNPB = true;
                    $okNPB = $this->insert_to_npb($_tglServer.$npbGudang, $nmNpb, $dtH, $dtD);

                      // Insert into log_npb
                      $this->DB_PGSQL
                            ->table("log_npb")
                            ->insert([
                                    "npb_tgl_proses" =>  $this->DB_PGSQL->raw("current_date"),
                                    "npb_kodetoko" =>  $kodetoko,
                                    "npb_nopb" =>  $nopb,
                                    "npb_tglpb" =>  $this->DB_PGSQL->raw("'$tglpb'::date"),
                                    "npb_nodspb" =>  $noDspb,
                                    "npb_file" =>  $nmNpb,
                                    "npb_jml_item" =>  $jmlItem,
                                    "npb_jenis" =>  $this->DB_PGSQL->raw("'VOUCHER'"),
                                    "npb_url" =>  $npbIP,
                                    "npb_response" =>  $this->DB_PGSQL->raw(($okNPB ? "SUKSES! " : "GAGAL! ") . $npbRes),
                                    "npb_create_web" =>  $jamCreateWeb,
                                    "npb_create_csv" =>  $jamCreateCSV,
                                    "npb_kirim" =>  $jamKirim,
                                    "npb_confirm" =>  $tglConfirm,
                                    "npb_jml_retry" =>  0,
                                    "npb_create_by" =>  session('userid'),
                                    "npb_create_dt" => $this->DB_PGSQL->raw("current_date")
                                ]);
                    //send mail
                    // $lblMonitoring->setText("Send MAIL!");
                    // Application::doEvents();
                    // DSPB::sendMail($_txtPath . DIRECTORY_SEPARATOR . $nmNpb . ".ZIP", $nmNpb, Caesar_Encrypt("P" . $noDspb . "9999", $_tglServer));

                    //create report 
                    $this->create_report($kodetoko, $tglpb, $nopb);

                    // create report qr code
                    // if ($checkQR) {
                    //     // create_qr_code($nmNpb,
                    //     //             $kodetoko,
                    //     //             $noDspb,
                    //     //             str_replace("-", "/", $dtD[0]->TANGGAL1),
                    //     //             count($dtD),
                    //     //             $dtD);
                    // }
                  }

                 

                
            } else {
                $response = ["errors"=>true, "messages"=>"Tidak ada data!"];
            }
            
         
            
                
                
                
                
                

            // $dtD->clear();

            // $labelStatus = "Load Data Pb to Memory";
            // event(new UpdateStatusEvent($labelStatus));

            // $dtD = DB::select($sb->toString(), [$_tglpb, $nopb, $kodetoko], 20);



            
            $this->DB_PGSQL->commit();
        } catch (\Throwable $th) {
            
            $this->DB_PGSQL->rollBack();
            // dd($th);5
            return response()->json(['errors'=>true,'messages'=>$th->getMessage()],500);
        }
    }
    public function update_db($nodspb = null, $kodetoko=null,$nopb=null,$tglpb = null){
        
        try {
            $this->DB_PGSQL->beginTransaction();
            $kodeigr = session('KODECABANG');
            // Update IDM KOLI
            $point = "update idm koli";
            $this->DB_PGSQL->table('tbtr_idmkoli')
                ->whereRaw("ikl_tglpb::date  = '".$tglpb."'::date")
                ->whereRaw("ikl_kodeidm  = '".$kodetoko."'")
                ->whereRaw("ikl_nopb  = '".$nopb."'")
                ->whereRaw("ikl_nokoli  like '04%'")
                ->update([
                    'ikl_registerrealisasi' => $nodspb,
                    'ikl_nobpd' => '1',
                    'ikl_idtransaksi' => $nodspb,
                    'ikl_tglbpd' =>  $this->DB_PGSQL->raw('current_date'),
                    'ikl_recordid' => '1'
                ]);
                
            // Update TBMASTER_STOCK
            $point = "UPDATE TBMASTER_STOCK";
            $sql = "UPDATE tbmaster_stock ";
            $sql .= "SET (st_intransit, st_saldoakhir) = ";
            $sql .= "(SELECT (st_intransit - SUM(pbo_qtyrealisasi * CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END)), ";
            $sql .= "(st_saldoakhir - SUM(pbo_qtyrealisasi * CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END)) ";
            $sql .= "FROM tbmaster_pbomi, tbmaster_prodmast ";
            $sql .= "WHERE pbo_kodeigr = prd_kodeigr ";
            $sql .= "AND pbo_pluigr = prd_prdcd ";
            $sql .= "AND pbo_kodeigr = st_kodeigr ";
            $sql .= "AND SUBSTR(pbo_pluigr, 1, 6) || '0' = st_prdcd ";
            $sql .= "AND PBO_TGLPB::date = '" . $tglpb. "'::date ";
            $sql .= "AND pbo_nopb = '" . $nopb . "' ";
            $sql .= "AND pbo_kodeomi = '" . $kodetoko . "' ";
            $sql .= "AND pbo_recordid = '4' ";
            $sql .= "AND SUBSTR(PRD_PRDCD,1,6) || '0' = ST_PRDCD ) ";
            $sql .= "WHERE st_kodeigr = '" . $kodeigr . "' ";
            $sql .= "AND st_lokasi = '01' ";
            $sql .= "AND EXISTS( ";
            $sql .= "SELECT 1 ";
            $sql .= "FROM tbmaster_pbomi ";
            $sql .= "WHERE pbo_kodeigr = st_kodeigr ";
            $sql .= "AND SUBSTR(pbo_pluigr, 1, 6) || '0' = st_prdcd ";
            $sql .= "AND PBO_TGLPB::date = '" . $tglpb . "'::date ";
            $sql .= "AND pbo_recordid = '4' ";
            $sql .= "AND pbo_nopb = '" . $nopb . "' ";
            $sql .= "AND pbo_kodeomi = '" . $kodetoko . "' ";
            $sql .= "AND pbo_nokoli LIKE '04%' ";
            $sql .= ")";

            $this->DB_PGSQL->statement($sql);

            // Insert into tbtr_realpb
            $point = "INSERT REALPB";
            $sql = "INSERT INTO tbtr_realpb (RPB_KODEIGR, RPB_NOKOLI, RPB_NOURUT, RPB_TGLDOKUMEN, RPB_NODOKUMEN, RPB_IDSURATJALAN, RPB_KODECUSTOMER, RPB_KODEOMI, RPB_PLU1, RPB_PLU2, RPB_HRGSATUAN, RPB_QTYORDER, RPB_QTYREALISASI, RPB_NILAIORDER, RPB_PPNORDER, RPB_TTLNILAI, RPB_TTLPPN, RPB_DISTRIBUTIONFEE, RPB_COST, RPB_QTYV, RPB_QTYBDR, RPB_HBDR, RPB_FLAG, RPB_CREATE_BY, RPB_CREATE_DT) ";
            $sql .= "SELECT PBO_KODEIGR, PBO_NOKOLI, ROW_NUMBER() OVER(), PBO_TGLPB, PBO_NOPB, '" . $nodspb . "', PBO_KODEMEMBER, PBO_KODEOMI, PBO_PLUOMI, PBO_PLUIGR, PBO_HRGSATUAN, PBO_QTYORDER, PBO_QTYREALISASI, PBO_NILAIORDER, PBO_PPNORDER, PBO_TTLNILAI, PBO_TTLPPN, PBO_DISTRIBUTIONFEE, ST_AVGCOST, 0, 0, 0, '4', '" . session('userid') . "', current_date ";
            $sql .= "FROM tbmaster_pbomi, tbmaster_STOCK ";
            $sql .= "WHERE PBO_KODEIGR = ST_KODEIGR ";
            $sql .= "AND SUBSTR(PBO_PLUIGR,1,6) || '0' = ST_PRDCD ";
            $sql .= "AND ST_LOKASI = '01' ";
            $sql .= "AND pbo_tglpb::date = '" . $tglpb . "'::date ";
            $sql .= "AND pbo_nopb = '" . $nopb . "' ";
            $sql .= "AND pbo_kodeomi = '" . $kodetoko . "' ";
            $sql .= "AND pbo_recordid = '4' ";
            $sql .= "AND pbo_nokoli LIKE '04%' ";

            $this->DB_PGSQL->statement($sql);

            // Update HEADER MAJALAH
            $point = "UPDATE HEADER MAJALAH";
            $this->DB_PGSQL
                  ->table("tbtr_header_materai_voucher")
                  ->whereRaw(" HMV_TGLPB::date = '" . $tglpb . "'::date")
                  ->whereRaw(" HMV_NOPB = '" . $nopb . "' ")
                  ->whereRaw(" HMV_KODETOKO = '" . $toko . "' ")
                  ->update([
                    "hmv_flag"=>  $this->DB_PGSQL->raw("'5'")
                  ]);



            $this->DB_PGSQL->commit();

            // Set flagSukses to true
            return true;
        } catch (\Throwable $th) {
            
            $this->DB_PGSQL->rollBack();
            // dd($th);
            return response()->json(['errors'=>true,'messages'=>$th->getMessage()],500);
        }
    }

    public function trf_ulang(Request $request){

        $tglServer = date('Y-m-d');
        $noDspb = '';
        $nmNpb = '';
        $nmRpb = '';
        

        if (empty($cn)) {
            $cn = DB::connection()->getPdo();
        }

        $tglpb = now()->format('d/m/Y');
        $tglpb = Str::replaceMatches('/(\d{2})\/(\d{2})\/(\d{4})/', function ($matches) {
            return $matches[2].'/'.$matches[1].'/'.$matches[3];
        }, $tglpb);

        $noDspb = $this->DB_PGSQL
                       ->table("tbtr_idmkoli")
                       ->selectRaw("IKL_REGISTERREALISASI")
                       ->distinct()
                       ->whereRaw("ikl_kodeidm = '" . $toko . "'  ")
                       ->whereRaw("ikl_nopb = '" . $nopb . "'  ")
                       ->whereRaw("IKL_TGLPB::date = '" . $tglpb . "'::date  ")
                       ->whereRaw("IKL_NOKOLI LIKE '04%'  ")
                       ->get();
        $noDspb = isset($noDspb[0]->ikl_registerrealisasi)?$noDspb[0]->ikl_registerrealisasi:null; 

        try {
            $this->DB_PGSQL->beginTransaction();

            if ( $noDspb) {
                
                $dtH = $this->DB_PGSQL
                            ->table($this->DB_PGSQL->raw("tbmaster_pbomi, tbtr_idmkoli"))
                            ->selectRaw("
                                '" . $noDspb . "' docno,
                                TO_CHAR(IKL_TGLBPD, 'dd-MM-YYYY') doc_date,
                                pbo_kodeomi toko,
                                (SELECT 'GI' || prs_kodeigr FROM tbmaster_perusahaan LIMIT 1) gudang,
                                COUNT(DISTINCT pbo_pluomi) item, SUM(pbo_qtyorder) qty
                                SUM(pbo_ttlnilai) gross, NULL koli, NULL kubikasi 
                            ")
                            ->whereRaw("pbo_tglpb::date = ikl_tglpb::date ") 
                            ->whereRaw("PBO_QTYREALISASI > 0 ") 
                            ->whereRaw("pbo_nopb = ikl_nopb ") 
                            ->whereRaw("pbo_kodeomi = ikl_kodeidm ") 
                            ->whereRaw("pbo_nokoli = ikl_nokoli ") 
                            ->whereRaw("PBO_TGLPB::date = '" . $_tglpb . "'::date") 
                            ->whereRaw("pbo_nopb = '" . $nopb . "' ") 
                            ->whereRaw("pbo_kodeomi = '" . $kodetoko . "' ") 
                            ->whereRaw("pbo_nokoli like '04%' ") 
                            ->groupBy("ikl_tglbpd","pbo_kodeomi")
                            ->get();

                $namafile = $dtH[0]->gudang . $dtH[0]->toko . date("YmdHis");
                // $namafile = getFileName($kodetoko, $noDspb, "NPV");
                
                if (count($dtH) > 0) {
                    //make CSV
                    DSPB::writeCSV("XPV" . $namafile, $dtH, $_txtPath, false);
                }

                $dtD = $this->DB_PGSQL
                            ->table($this->DB_PGSQL->raw("tbmaster_pbomi, tbtr_idmkoli, tbmaster_prodmast "))
                            ->selectRaw("
                                '*' recid,
                                NULL rtype,
                                '$noDspb'  docno,
                                ROW_NUMBER() OVER() seqno,
                                pbo_nopb picno,
                                NULL picnot,
                                TO_CHAR(pbo_tglpb, 'dd-MM-YYYY') pictgl,
                                pbo_pluomi prdcd,
                                (SELECT prd_deskripsipendek FROM tbmaster_prodmast WHERE prd_prdcd = pbo_pluigr) nama,
                                pbo_kodedivisi div,
                                pbo_qtyorder qty,
                                pbo_qtyrealisasi sj_qty,
                                pbo_hrgsatuan price,
                                pbo_ttlnilai gross,
                                pbo_ttlppn ppnrp,
                                pbo_hrgsatuan hpp,
                                pbo_kodeomi toko,
                                'V-' keter,
                                TO_CHAR(IKL_TGLBPD, 'dd-MM-YYYY') tanggal1,
                                TO_CHAR(pbo_tglpb, 'dd-MM-YYYY') tanggal2,
                                pbo_nopb docno2,
                                NULL lt,
                                NULL rak,
                                NULL bar,
                                (SELECT 'GI' || prs_kodeigr FROM tbmaster_perusahaan LIMIT 1) kirim,
                                lpad(pbo_NOKOLI,12,'0') dus_no,
                                NULL TGLEXP,
                                COALESCE(prd_ppn, 0) ppn_rate,
                                COALESCE(prd_flagbkp1, 'N') BKP,
                                COALESCE(prd_flagbkp2,'N') SUB_BKP 
                            ")
                            ->whereRaw(" PBO_TGLPB = '" . $tglpb . "'::date ")
                            ->whereRaw("PBO_QTYREALISASI > 0 ")
                            ->whereRaw("pbo_tglpb::date = ikl_tglpb::date ")
                            ->whereRaw("pbo_nopb = ikl_nopb ")
                            ->whereRaw("pbo_kodeomi = ikl_kodeidm ")
                            ->whereRaw("pbo_nokoli = ikl_nokoli ")
                            ->whereRaw("pbo_pluigr = prd_prdcd ")
                            ->whereRaw("pbo_nopb = '" . $nopb . "' ")
                            ->whereRaw("pbo_kodeomi = '" . $toko . "' ")
                            ->whereRaw("pbo_nokoli like '04%'")
                            ->get();

                if (count($dtD) > 0) {
                    //make CSV
                    DSPB::writeCSV("NPV" . $namafile, $dtD, $_txtPath, false);
                }

                // Update Variables
                $nmRpb = "XPV" . $namafile;
                $nmNpb = "NPV" . $namafile;

                // Check and delete existing ZIP file
                // if (Storage::exists($_txtPath . "/" . $nmNpb . ".ZIP")) {
                //     Storage::delete($_txtPath . "/" . $nmNpb . ".ZIP");
                // }

                // Zip files
                // $listFile = [
                //     $_txtPath . "/" . $nmNpb . ".CSV",
                //     $_txtPath . "/" . $nmRpb . ".CSV"
                // ];
                // Zip_File($_txtPath . "/" . $nmNpb . ".ZIP", $listFile);

                // // Delete CSV files
                // if (Storage::exists($_txtPath . "/" . $nmNpb . ".CSV")) {
                //     Storage::delete($_txtPath . "/" . $nmNpb . ".CSV");
                // }
                // if (Storage::exists($_txtPath . "/" . $nmRpb . ".CSV")) {
                //     Storage::delete($_txtPath . "/" . $nmRpb . ".CSV");
                // }

                // Check if data exists and meets certain conditions
                if (ORADataFound($cn, "LOG_NPB", "npb_kodetoko = '" . $kodetoko . "' AND npb_nopb = '" . $nopb . "' AND npb_nodspb = '" . $noDspb . "' AND npb_jenis = 'VOUCHER' AND npb_response LIKE 'GAGAL%'")) {

                    // Get data
                    $kodeDCIDM = "";
                    $dt = $this->DB_PGSQL
                               ->table("master_supply_idm")
                               ->selectRaw("msi_kodedc")
                               ->whereRaw("msi_kodetoko = '" . $kodetoko . "'")
                               ->get();
                               
                    if (count($dt) > 0) {
                        $kodeDCIDM = $dt->rows()[0]["msi_kodedc"];
                    }

                    // Get NPB IP
                    $npbIP = null;
                    $npbIP = $this->DB_PGSQL
                                  ->table("tbmaster_webservice")
                                  ->selectRaw("ws_url")
                                  ->whereRaw("ws_nama = 'NPB'")
                                  ->whereRaw("COALESCE(ws_aktif, 0) = 1 ");
                    if (count($kodeDCIDM)) {
                        $npbIP = $npbIP->whereRaw("ws_dc = '$kodeDCIDM'");
                    }
                    $npbIP = $npbIP->get();
                    $npbIP = $npbIP[0]->ws_url;



                    $npbGudang = null;
                    if ($kodeDCIDM != "") {
                        $npbGudang = $kodeDCIDM;
                    } else {
                        $npbGudang = $this->DB_PGSQL
                                        ->table("tbmaster_webservice")
                                        ->selectRaw("ws_dc")
                                        ->whereRaw("ws_nama = 'NPB'")
                                        ->whereRaw("COALESCE(ws_aktif, 0) = 1 ");
                        if (count($kodeDCIDM)) {
                            $npbGudang = $npbGudang->whereRaw("ws_dc = '$kodeDCIDM'");
                        }
                        $npbGudang = $npbGudang->get();
                        $npbGudang = $npbGudang[0]->ws_dc;
                    }

                    // Proceed if NPB IP exists
                    if ($npbIP !== null && $npbIP !== "") {
                        $okNPB = true;

                        // Insert data to NPB
                        $okNPB = insert_to_npb($tglServer.$npbGudang, $nmNpb, $dtH, $dtD);

                        // Update NPB Log
                        $this->DB_PGSQL
                            ->table('log_npb')
                            ->where('npb_kodetoko', $kodetoko)
                            ->where('npb_nopb', $nopb)
                            ->where('npb_nodspb', $noDspb)
                            ->where('npb_file', $nmNpb)
                            ->where('npb_jenis', 'VOUCHER')
                            ->update([
                                'npb_response' => ($okNPB ? 'SUKSES! ' : 'GAGAL! ') . $npbRes,
                                'npb_confirm' => $tglConfirm,
                                'npb_tgl_retry' => date('Y-m-d'),
                                'npb_jml_retry' => $this->DB_PGSQL->raw('npb_jml_retry + 1'),
                                'npb_modify_by' => session()->get('userid'),
                                'npb_modify_dt' => date('Y-m-d')
                            ]);
                    }
                }
                $sql = "SELECT DISTINCT hmv_kodetoko kodetoko, hmv_tglpb tglpb, hmv_nopb nopb, ";
                $sql .= "pmv_pluidm pluidm, pmv_pluigr pluigr, pch_qtyrealisasi qty, prd_deskripsipanjang desc2 ";
                $sql .= "FROM tbtr_header_materai_voucher ";
                $sql .= "JOIN tbmaster_pbomi ON hmv_kodetoko = pbo_kodeomi AND hmv_nopb = pbo_nopb AND hmv_tglpb = pbo_tglpb ";
                $sql .= "JOIN tbtr_picking_h ON hmv_kodetoko = pch_kodetoko AND hmv_nopb = pch_nopb AND hmv_tglpb = pch_tglpb AND pch_pluigr = SUBSTR(pbo_pluigr, 1, 6) || '0' ";
                $sql .= "JOIN tbtr_picking_d ON pch_id = pcd_id ";
                $sql .= "JOIN PLU_MATERAI_VOUCHER ON pbo_pluomi = pmv_pluidm ";
                $sql .= "JOIN tbmaster_prodmast ON pch_pluigr = prd_prdcd ";
                $sql .= "WHERE hmv_kodetoko = '" . $kodetoko . "'::date ";
                $sql .= "AND hmv_nopb = '" . $nopb . "'::date ";
                $sql .= "AND hmv_tglpb = '" . $tglpb . "'::date";
                $dt = $this->DB_PGSQL->select($sql);

                if (count($dt) > 0) {
                    $ds = new \DataSet();
                    $adp = new \OdbcDataAdapter();
                
                    // Create a DataTable
                    $dt = CreateTable($dt);
                    $ds->addTable($dt);
                
                    // Instantiate the report
                    $oRpt = new rptNoReferensi();
                
                    // Fetch data for the report
                    $Sql = "SELECT * FROM (SELECT DISTINCT hmv_kodetoko kodetoko, hmv_tglpb tglpb, hmv_nopb nopb, ";
                    $Sql .= "ikl_registerrealisasi nodspb, ikl_nokoli nokoli ";
                    $Sql .= "FROM tbtr_header_materai_voucher ";
                    $Sql .= "JOIN tbtr_idmkoli ON hmv_kodetoko = ikl_kodeidm AND hmv_nopb = ikl_nopb AND TO_CHAR(hmv_tglpb,'yyyyMMdd') = ikl_tglpb ";
                    $Sql .= "JOIN tbmaster_pbomi ON hmv_kodetoko = pbo_kodeomi AND hmv_nopb = pbo_nopb AND hmv_tglpb = pbo_tglpb ";
                    $Sql .= "WHERE hmv_kodetoko = '" . $kodetoko . "' ";
                    $Sql .= "AND hmv_nopb = '" . $nopb . "' ";
                    $Sql .= "AND hmv_tglpb = TO_DATE('" . $_tglpb->format("d-m-Y") . "','dd-MM-yyyy') ";
                    $Sql .= "AND ikl_nokoli LIKE '04%' ORDER BY ikl_nokoli DESC) t LIMIT 1";
                
                    $ds->addTable(DB::select($Sql), "HEADER");
                
                    // Fetch data for PERUSAHAAN table
                    $Sql = "SELECT prs_kodeigr kode_igr, PRS_NAMACABANG FROM tbmaster_perusahaan";
                    $ds->addTable(DB::select($Sql), "PERUSAHAAN");
                
                    // Fetch data for TOKO table
                    $Sql = "SELECT tko_kodeigr kode_igr, TKO_NAMAOMI, TKO_KODEOMI ";
                    $Sql .= "FROM tbmaster_tokoigr ";
                    $Sql .= "WHERE TKO_KODEOMI = '" . $kodetoko . "' ";
                    $Sql .= "AND TKO_NAMASBU = 'INDOMARET' ";
                
                    $ds->addTable(DB::select($Sql), "TOKO");
                
                    // Write DataSet to XML file
                    $xmlData = $ds->getXml();
                    Storage::put('reporting.xml', $xmlData);
                
                    // Set DataSet as DataSource for the report
                    $oRpt->setDataSource($ds);
                
                    // Show the report
                    return view('your-report-view', ['report' => $oRpt]);
                
                    // You might need to replace 'your-report-view' with the actual view name where you render the report.
                
                    // Display completion message
                    echo "Selesai Transfer Ulang di " . $_txtPath . "!";
                }



                $response = ['errors'=>false,'messages'=>"Berhasil Transfer Ulang"];
                $code = 200;
            } else {
                $response = ['errors'=>true,'messages'=>"Gagal Transfer Ulang, Data No DSPB tidak ditemukan"];
                $code = 422;
            }
            
            $this->DB_PGSQL->commit();
            return response()->json($response,$code);
        } catch (\Throwable $th) {
            
            $this->DB_PGSQL->rollBack();
            // dd($th);
            return response()->json(['errors'=>true,'messages'=>$th->getMessage()],500);
        }

    }

    public function create_report($kodetoko = null, $tglpb = null, $nopb = null){
       
        try {
            $this->DB_PGSQL->beginTransaction();
            
            $flagFTZ = false;
            $flagFTZ_condition = null; 
            if ($flagFTZ ) {
                $flagFTZ_condition = "SUM(COALESCE(pbo_ttlnilai, 0) + COALESCE(pbo_ttlppn, 0)) AS bkp,0 AS btkp,0 AS ppn"; 
               
            } else {
                $flagFTZ_condition = "SUM(CASE WHEN COALESCE(pbo_ttlppn, 0) > 0 THEN COALESCE(pbo_ttlnilai, 0) END) AS bkp, SUM(CASE WHEN COALESCE(pbo_ttlppn, 0) = 0 THEN COALESCE(pbo_ttlnilai, 0) END) AS btkp, SUM(pbo_ttlppn) AS ppn "; 
               
            }
            
            $print_SJ = $this->DB_PGSQL
                             ->table("tbmaster_pbomi")
                             ->join("tbtr_idmkoli",function($join) use($kodetoko,$tglpb,$nopb){
                                $join->on($this->DB_PGSQL->raw("pbo_tglpb::timestamp"),"=",$this->DB_PGSQL->raw("ikl_tglpb::timestamp"))
                                    ->on("pbo_nopb","=","ikl_nopb")  
                                    ->on("pbo_kodeomi","=","ikl_kodeidm")  
                                    ->on("pbo_nokoli","=","ikl_nokoli")  
                                    ->on($this->DB_PGSQL->raw("ikl_tglpb::date"),"=",$this->DB_PGSQL->raw(" '" . $tglpb. "'::date "))
                                    ->on($this->DB_PGSQL->raw("ikl_kodeidm"),"=",$this->DB_PGSQL->raw("'" . $kodetoko . "' "))  
                                    ->on($this->DB_PGSQL->raw("ikl_nopb"),"=",$this->DB_PGSQL->raw("'" . $nopb . "' "));
                             })
                             ->join("tbmaster_prodmast",function($join){
                                 $join->on("pbo_pluigr", "=", "prd_prdcd" );
                             })
                             ->leftJoin("tbmaster_kodefp",function($join){
                                 $join->on( $this->DB_PGSQL->raw("COALESCE(prd_flagbkp1, 'N')"), "=", "kfp_flagbkp1")
                                      ->on( $this->DB_PGSQL->raw(" COALESCE(prd_flagbkp2, 'N')"), "=", "kfp_flagbkp2");
                             })
                             ->selectRaw("
                                ikl_kodeigr AS kode_igr, ikl_nokoli AS koli, ikl_nocontainer AS CONTAINER, COUNT(pbo_qtyrealisasi) AS item, 
                                ".$flagFTZ_condition."
                             ")
                             ->whereRaw("PBO_QTYREALISASI > 0 ")
                             ->whereRaw("PBO_RECORDID = '4' ") // command for testing
                             ->whereRaw("PBO_NOKOLI LIKE '04%' ") // command for testing
                             ->groupBy("ikl_kodeigr", "ikl_nokoli", "ikl_nocontainer" )
                             ->orderBy("ikl_nokoli","asc")
                             ->get();

            $headerData_select_condition = "";
            if (count($print_SJ)) {
                $headerData_select_condition = "null AS encrypt, IKL_KODEIGR AS KODE_IGR, ikl_nopb, ikl_nobpd, 'REPRINT' AS reprint,";
                
            } else {
                $headerData_select_condition = "null AS encrypt, IKL_KODEIGR AS KODE_IGR, ikl_nopb, ikl_nobpd, NULL AS reprint,";
                
            }
            $headerData =  $this->DB_PGSQL
                                ->table("tbtr_idmkoli")
                                ->selectRaw("
                                    ".$headerData_select_condition."
                                    ikl_registerrealisasi AS dspb, ikl_tglbpd AS tgl_dspb 
                                ")
                                ->whereRaw("ikl_tglpb::date = '" . $tglpb. "'::date ")
                                ->whereRaw("ikl_kodeidm = '" . $kodetoko . "' ")
                                ->whereRaw("ikl_nopb = '" . $nopb . "' ")
                                ->whereRaw("IKL_NOKOLI IS NOT NULL ")
                                ->whereRaw("IKL_NOKOLI LIKE '04%' ")
                                ->limit(1)
                                ->get();
            
            $perusahaanData =  $this->DB_PGSQL
                                ->table("tbmaster_perusahaan")
                                ->selectRaw("prs_kodeigr kode_igr, prs_namacabang ")
                                ->get();
            $tokoData =  $this->DB_PGSQL
                                ->table("tbmaster_tokoigr")
                                ->selectRaw("tko_kodeigr kode_igr ,tko_namaomi , tko_kodeomi ")
                                ->whereRaw("TKO_KODEOMI = '" .$kodetoko."'")
                                ->whereRaw("TKO_NAMASBU = 'INDOMARET'")
                                ->get();
            $cluster_data =  $this->DB_PGSQL
                                ->table("cluster_idm")
                                ->selectRaw("cls_kode,cls_group")
                                ->whereRaw("cls_toko = '" .$kodetoko."'")
                                ->get();
            
            $cluster = count($cluster_data)? $cluster_data[0]->cls_kode:null;
            $group = count($cluster_data)? $cluster_data[0]->cls_group:null;

            // Check if the 'SJ' table in the dataset has rows
            // if (count($ds['SJ']) > 0) {

            //     if (!empty($headerData)) {
            //         $headerData[0]->ENCRYPT = Caesar_Encrypt("P" . $headerData[0]->DSPB . "9999", $headerData[0]->TGL_DSPB);
            //     }

            //     // Set up the report
            //     $oRpt->SetDataSource([
            //         'HEADER' => $headerData,
            //         'PERUSAHAAN' => $perusahaanData,
            //         'TOKO' => $tokoData
            //     ]);
            //     $oRpt->SetParameterValue("cluster", "Cluster: " . $cluster . " Group: " . $group);

            //     // Write the dataset to XML file
            //     $ds->writeXml(public_path('reporting.xml'), LIBXML_NOEMPTYTAG);

            //     // Load the report view
            //     return view('report', ['report' => $oRpt]);
            // }

            //  datanoseri
            $noseriData = $this->DB_PGSQL
                               ->table("tbtr_header_materai_voucher")
                               ->join("tbmaster_pbomi",function($join){
                                  $join->on("hmv_kodetoko", "=", "pbo_kodeomi")
                                        ->on("hmv_nopb", "=", "pbo_nopb")
                                        ->on("hmv_tglpb", "=", "pbo_tglpb");
                               })
                               ->join("tbtr_picking_h",function($join){
                                  $join->on("hmv_kodetoko", "=", "pch_kodetoko")
                                        ->on("hmv_nopb", "=", "pch_nopb")
                                        ->on("hmv_tglpb", "=", "pch_tglpb")
                                        ->on("pch_pluigr", "=",  $this->DB_PGSQL->raw("CONCAT(SUBSTR(pbo_pluigr, 1, 6), '0')"));
                               })
                               ->join("tbtr_picking_d",function($join){
                                  $join->on("pch_id", "=", "pcd_id");
                               })
                               ->join("plu_materai_voucher",function($join){
                                  $join->on("pbo_pluomi", "=", "pmv_pluidm");
                               })
                               ->join("tbmaster_prodmast",function($join){
                                  $join->on("pch_pluigr", "=", "prd_prdcd");
                               })
                               ->selectRaw("
                                    hmv_kodetoko AS kodetoko, hmv_tglpb AS tglpb, hmv_nopb AS nopb,
                                    pmv_pluidm AS pluidm, pmv_pluigr AS pluigr, pch_qtyrealisasi AS qty, prd_deskripsipanjang AS desc2
                               ")
                               ->distinct()
                               ->whereRaw("hmv_kodetoko = '" . $kodetoko . "' ")
                               ->whereRaw("hmv_nopb = '" . $nopb . "' ")
                               ->whereRaw("hmv_tglpb = '" . $tglpb . "'")
                               ->get();

            if (count($noseriData)) {
                $headerResultQuery = $this->DB_PGSQL
                                     ->table("tbtr_header_materai_voucher")
                                     ->join("tbtr_idmkoli",function($join){
                                        $join->on("hmv_kodetoko", "=" ,"ikl_kodeidm")
                                            ->on("hmv_nopb", "=" ,"ikl_nopb")
                                            ->on($this->DB_PGSQL->raw("TO_CHAR(hmv_tglpb,'YYYYMMDD')"), "=" ,"ikl_tglpb");
                                     })
                                     ->join("tbmaster_pbomi",function($join){
                                        $join->on("hmv_kodetoko", "=" ,"pbo_kodeomi")
                                            ->on("hmv_nopb", "=" ,"pbo_nopb")
                                            ->on("hmv_tglpb", "=" ,"pbo_tglpb");
                                     })
                                     ->selectRaw("
                                        hmv_kodetoko AS kodetoko, hmv_tglpb AS tglpb, hmv_nopb AS nopb, 
                                        ikl_registerrealisasi AS nodspb, ikl_nokoli AS nokoli 
                                     ")
                                     ->distinct()
                                     ->whereRaw("hmv_kodetoko = '" . $kodetoko . "' ")
                                     ->whereRaw("hmv_nopb = '" . $nopb . "' ")
                                     ->whereRaw("hmv_tglpb::date = '" . $tglpb . "'::date ")
                                     ->whereRaw("ikl_nokoli LIKE '04%' ")
                                     ->orderBy("ikl_nokoli","desc")
                                     ->toSql();
                $headerResult = $this->DB_PGSQL
                                     ->table( $this->DB_PGSQL->raw("($headerResultQuery) as t"))
                                     ->selectRaw("*")
                                     ->limit(1)
                                     ->get();
            }

            $data = [
                'noseri'=> $noseriData?$noseriData:null,
                'header' => $headerResult?$headerResult[0]:null,
                'perusahaan'=> $perusahaanData?$perusahaanData:null,
                'toko' => $tokoData?$tokoData:null,
                'cluster' => $cluster?$cluster:null,
                'group' => $group?$group:null,
                'container' => $print_SJ[0]?$print_SJ[0]:null

            ];

            
            
            $this->DB_PGSQL->commit();
            return $data;
        } catch (\Throwable $th) {
            
            $this->DB_PGSQL->rollBack();
            // dd($th);
            return response()->json(['errors'=>true,'messages'=>$th->getMessage()],500);
        }
    }

    public function print_qr(Request $request){
        $header_cetak_custom = 'upper';
        $date = date('Y-m');
        $perusahaan = '[{"prs_namaperusahaan":"PT.INTI CAKRAWALA CITRA","prs_namacabang":"INDOGROSIR SEMARANG POST","customer":"105005 - PUJI RAHAYU","nomor_faktur":"010.007-23.30540947","tgl_faktur":"2023-05-31 00:00:00","dpp":"6432836.0000","ppn":"707624.0000","ppn_bkp":"707624.0000","ppn_bebas":"0","ppn_dtp":"0"}]';
        $data = '[{"prs_kodeigr":"22","prs_kodeperusahaan":"PT","prs_namaperusahaan":"PT.INTI CAKRAWALA CITRA","prs_kodewilayah":"SM2","prs_namawilayah":"SEMARANG","prs_singkatanwilayah":"SMG","prs_koderegional":"01","prs_namaregional":"SEMARANG","prs_singkatanregional":"SMG","prs_kodecabang":"22","prs_namacabang":"INDOGROSIR SEMARANG POST","prs_singkatancabang":"IGR SMG","prs_kodesbu":"4","prs_namasbu":"INDOGROSIR","prs_singkatansbu":"IGR","prs_lokasisbu":"SEMARANG","prs_alamat1":"JL.RAYA KALIGAWE 38 KM 5,1","prs_alamat2":"TERBOYO WETAN","prs_alamat3":"GENUK","prs_telepon":"02476928282","prs_npwp":"01.781.214.0-046.000","prs_nosk":"01.781.214.0-046.000","prs_tglsk":"2007-04-09 00:00:00","prs_alamatfakturpajak1":"JL.ANCOL BARAT I NO.9-10 ANCOL","prs_alamatfakturpajak2":"PADEMANGAN JAKARTA UTARA","prs_alamatfakturpajak3":"DKI JAKARTA 14430","prs_modalawal":"40000000","prs_fmmupb":"100000","prs_flagppn":"Y","prs_flagpkp":null,"prs_jenistimbangan":"3","prs_fmfsgd":null,"prs_jenisprinter":"2","prs_classcabang":"M","prs_tipehrg":"A","prs_nokpp":null,"prs_creditlimit":"0","prs_flagdpd":"Y","prs_limitprofitlabelbiru":"1","prs_noserifakturpajak":null,"prs_nopo":"00000","prs_nobpb":"000000","prs_nonpb":"00000","prs_nonkb":"00000","prs_nobapb":"00000","prs_nompp":"00000","prs_noklm":"00000","prs_notkl":"00000","prs_nofaktur":"00000","prs_nofakturpajak1":"0000000","prs_nofakturpajak2":"0000000","prs_nonrb":"0000000","prs_nobrb":"00000","prs_nonk":"00000","prs_nond":"00000","prs_periodebaru":null,"prs_periodeterakhir":"2023-10-13 22:17:07","prs_bulanberjalan":"10","prs_tahunberjalan":"2023","prs_toleransihrg":"0","prs_fmflcs":null,"prs_nilaippn":"11","prs_nilaippnbm":"0","prs_kodemto":"016","prs_reportpath":null,"prs_ipserver":"\\\\192.168.237.194\\d\\grosir","prs_userserver":"igrsmg","prs_pwdserver":"igrsmg","prs_directorypb":"G:\\Grosir\\Lhost\\MM","prs_rptname":"rep_ias-igrsmg","prs_mdftpurl":null,"prs_mdftpuser":null,"prs_mdftppassword":null,"prs_mdftpport":null,"prs_directorykirim":null,"prs_create_by":"sys","prs_create_dt":"2011-07-25 13:01:21","prs_modify_by":"DV3","prs_modify_dt":"2023-10-13 22:17:07","prs_kphconst":"6","prs_flagcmo":"Y","prs_tglcmo":"2017-10-02 00:00:00","prs_flag_ftz":null,"prs_potong_plano_scan":null}]';
        // $data = json_decode($data);
        $data = [];
        $perusahaan = json_decode($perusahaan);
        $perusahaan = $perusahaan[0];
        $pdf = PDF::loadview('menu.voucher.report.reportqr', compact('data','date','perusahaan','header_cetak_custom'));
        $pdf->output();
        $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
        $canvas = $dompdf->get_canvas();
        //make page text in footer and right side
        $canvas->page_text(615, 40, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, array(0, 0, 0));
    
    
        return $pdf->stream('report_qr_voucher '.date('Y-m-d'));
    }
    public function print_report(Request $request){

        // $kodetoko = $request->kodetoko;
        // $tglpb = $request->tglpb;
        // $nopb = $request->nopb;
        $kodetoko = "FHHK";
        $nopb = "900319";
        $tglpb = "2023-02-02";
        $header_cetak_custom = 'bellow';
        //Testing
        // $data = $this->dspb_vm($kodetoko,$tglpb,$nopb);
        $data_qr = (new QRController)->create_qr_code('2009021701_GSM',$kodetoko,'G1111',);
        
        dd('keluar');
       
        //end testing
        // $data = $this->create_report($kodetoko,$tglpb,$nopb);
        // $data['kodetoko'] = $kodetoko;
        // $data['tglpb'] = $tglpb;
        // $data['nopb'] = $nopb;
        // $date = date('Y-m');
        // $perusahaan = $data['perusahaan'];
        // $pdf = PDF::loadview('menu.voucher.report.report_ rincian_nomor_referensi_materai', compact('data','date','perusahaan','header_cetak_custom'));
        // $pdf->output();
        // $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
        // $canvas = $dompdf->get_canvas();

        // // //make page text in header and left side
        // $canvas->page_text(595, 810, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, array(0, 0, 0));
    
    
        // return $pdf->stream('report_rincian_nomor_referensi_materai '.date('Y-m-d'));
    }

    public function picking_load(Request $request){

        $tglpb = $request->tglpb;
        $kodetoko = $request->kodetoko;
        $nopb = $request->nopb;
        $qtyOrder = null;
        $deskripsi = null;
        $qtyRealisasi = null;
        $plu = null;
        $noSeri = null;
        $picking = $this->DB_PGSQL
                        ->table("tbtr_header_materai_voucher")
                        ->join("tbmaster_pbomi",function($join){
                            $join->on("hmv_kodetoko" ,"=" ,"pbo_kodeomi") 
                                ->on("hmv_nopb" ,"=" ,"pbo_nopb") 
                                ->on($this->DB_PGSQL->raw("hmv_tglpb::date") ,"=" ,$this->DB_PGSQL->raw("pbo_tglpb::date"));
                        })
                        ->join("plu_materai_voucher",function($join){
                            $join->on("pmv_pluidm", "=" ,"pbo_pluomi");
                        })
                        ->join("tbmaster_prodmast",function($join){
                            $join->on("pmv_pluigr", "=" ,"prd_prdcd");
                        })
                        ->leftjoin("tbtr_picking_h",function($join){
                            $join->on("pbo_kodeomi", "=" ,"pch_kodetoko")
                                 ->on("pbo_tglpb" ,"=" ,"pch_tglpb") 
                                 ->on("pbo_nopb" ,"=" ,"pch_nopb") 
                                 ->on("pmv_pluigr" ,"=" ,"pch_pluigr");
                        })
                        ->selectRaw("
                            PMV_PLUIGR AS PLU, PBO_QTYORDER AS QTY_ORDER, PRD_DESKRIPSIPANJANG AS DESKRIPSI, HMV_KODETOKO AS KODETOKO, HMV_NOPB AS NOPB, HMV_TGLPB::date AS TGLPB, PCH_QTYREALISASI AS QTY_REALISASI
                        ")
                        ->distinct()
                        ->whereRaw("PMV_PLUIGR IS NOT NULL")
                        ->whereRaw("HMV_TGLPB::date = '$tglpb'::date")
                        ->whereRaw("HMV_NOPB = '$nopb")
                        ->whereRaw("HMV_KODETOKO = '$kodetoko")
                        ->orderBy("pmv_pluigr","asc")
                        ->get();
        
        if (count($picking)) {
            // Retrieve the data and perform necessary actions
            // For example:
            $qtyOrder = $picking[0]->qty_order;
            $deskripsi = $picking[0]->deskripsi;
            $qtyRealisasi = $picking[0]->qty_realisasi;
            $plu = $picking[0]->plu;
            $noSeri = $this->load_no_seri($tglpb,$nopb,$kodetoko,$plu);
            $data = [
                "no_seri" => $noSeri,
                "plu_picking" => $plu,
                "deskripsi_picking" => $deskripsi,
                "qty_order_picking" => $qtyOrder,
                "qty_realisasi_picking" => $qtyRealisasi,
                "no_picking1" => 0,
                "no_picking2" => 0,
            ];
            
            return response()->json(['errors'=>false,'messages'=>'berhasi','data'=>$data],200);
        }else{

            return response()->json(['errors'=>true,'messages'=>'Data Tidak Ditemukan'],422);
        }      
        
        return response()->json(['errors'=>false,'messages'=>'berhasi','data'=>$data],200);

    }

    public function plu_load(Request $request){
        $tglpb = $request->tglpb;
        $kodetoko = $request->kodetoko;
        $nopb = $request->nopb;
        $picking = $this->DB_PGSQL
                        ->table("tbtr_header_materai_voucher")
                        ->join("tbmaster_pbomi",function($join){
                            $join->on("hmv_kodetoko" ,"=" ,"pbo_kodeomi") 
                                ->on("hmv_nopb" ,"=" ,"pbo_nopb") 
                                ->on($this->DB_PGSQL->raw("hmv_tglpb::date") ,"=" ,$this->DB_PGSQL->raw("pbo_tglpb::date"));
                        })
                        ->join("plu_materai_voucher",function($join){
                            $join->on("pmv_pluidm", "=" ,"pbo_pluomi");
                        })
                        ->join("tbmaster_prodmast",function($join){
                            $join->on("pmv_pluigr", "=" ,"prd_prdcd");
                        })
                        ->leftjoin("tbtr_picking_h",function($join){
                            $join->on("pbo_kodeomi", "=" ,"pch_kodetoko")
                                 ->on("pbo_tglpb" ,"=" ,"pch_tglpb") 
                                 ->on("pbo_nopb" ,"=" ,"pch_nopb") 
                                 ->on("pmv_pluigr" ,"=" ,"pch_pluigr");
                        })
                        ->selectRaw("
                            PMV_PLUIGR AS PLU, PBO_QTYORDER AS QTY_ORDER, PRD_DESKRIPSIPANJANG AS DESKRIPSI, HMV_KODETOKO AS KODETOKO, HMV_NOPB AS NOPB, HMV_TGLPB::date AS TGLPB, PCH_QTYREALISASI AS QTY_REALISASI
                        ")
                        ->distinct()
                        ->whereRaw("PMV_PLUIGR IS NOT NULL")
                        ->whereRaw("HMV_TGLPB::date = '$tglpb'::date")
                        ->whereRaw("HMV_NOPB = '$nopb")
                        ->whereRaw("HMV_KODETOKO = '$kodetoko")
                        ->orderBy("pmv_pluigr","asc")
                        ->get();
        if (!empty($picking)) {
            // Retrieve the data and perform necessary actions
            // For example:
            $qtyOrder = $picking[0]->qty_order;
            $deskripsi = $picking[0]->deskripsi;
            $qtyRealisasi = $picking[0]->qty_realisasi;
            $plu = $picking[0]->plu;
            $noSeri = $this->load_no_seri($tglpb,$nopb,$kodetoko,$plu);
            $data = [
                "no_seri" => $noSeri,
                "plu_picking" => $plu,
                "deskripsi_picking" => $deskripsi,
                "qty_order_picking" => $qtyOrder,
                "qty_realisasi_picking" => $qtyRealisasi,
                "no_picking1" => 0,
                "no_picking2" => 0,
            ];
            
            return response()->json(['errors'=>false,'messages'=>'berhasi','data'=>$data],200);
        }else{

            return response()->json(['errors'=>true,'messages'=>'Data Picking Tidak Ditemukan'],422);
        }


    }
    
    public function load_no_seri($tglpb,$nopb,$kodetoko,$plu) {
        $noseri = $this->DB_PGSQL
                       ->table("tbtr_picking_d")
                       ->join("tbtr_picking_h",function($join){
                           $join->on("pcd_id", "=" ,"pch_id");
                       })
                       ->selectRaw("pcd_referensi noseri ")
                       ->whereRaw("PCH_KODETOKO = '$kodetoko'")
                       ->whereRaw("PCH_NOPB = '$nopb'")
                       ->whereRaw("PCH_TGLPB::date = '$tglpb'::date")
                       ->whereRaw("PCH_PLUIGR = '$plu'")
                       ->get();

        return $noseri;

    }

    public function save_data_picker(Request $request){
        $noid = date('YmdHis');

        // Start a database transaction
    
        try {

            $kodetoko = $request->kodetoko;
            $tglpb = $request->tglpb;
            $nopb = $request->nopb;
            $nopb = $request->nopb;
            $no_seri = $request->no_seri;
            $plu_picking = $request->plu_picking;
            $deskripsi_picking = $request->deskripsi_picking;
            $qty_order_picking = $request->qty_order_picking;
            $qty_realisasi_picking = $request->qty_realisasi_picking;
            $no_picking1 = $request->no_picking1;
            $no_picking2 = $request->no_picking2;

            $this->DB_PGSQL->beginTransaction();
            foreach ($no_seri as $row) {
                $this->DB_PGSQL->table('tbtr_picking_d')->insert([
                    'pcd_id' => $noid,
                    'pcd_referensi' => $row->noseri,
                    'pcd_create_by' => session()->get('userid'),
                    'pcd_create_dt' => date('Y-m-d'),
                ]);
            }
    
            $this->DB_PGSQL->table('TBTR_PICKING_H')->insert([
                'pch_id' => $noid,
                'pch_nopb' => $nopb,
                'pch_tglpb' => date('Y-m-d',strtotime($tglpb)),
                'pch_kodetoko' => $kodetoko,
                'pch_pluigr' => $plu,
                'pch_qtyorder' => $qty_order_picking,
                'pch_qtyrealisasi' => $qty_realisasi_picking,
                'pch_create_by' => session()->get('userid'),
                'pch_create_dt' => date('Y-m-d'),
            ]);
    
            // Commit the transaction if all queries succeeded
            $this->DB_PGSQL->commit();
    
            return response()->json(['errors'=>false,'messages' => 'Data Berhasil Disimpan'], 200);
        } catch (\Exception $e) {
            // Rollback the transaction if any query fails
            $this->DB_PGSQL->rollback();
            return response()->json(['errors'=>true,'messages' => 'Gagal Menyimpan data: ' . $e->getMessage()], 500);
        }
    }
}