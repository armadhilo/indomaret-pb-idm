<?php

namespace App\Http\Controllers;

use App\Traits\LibraryCSV;
use App\Traits\LibraryZIP;
use App\Traits\mdPublic;
use Illuminate\Http\Request;
use PDF;
use DB;

class VoucherController extends Controller
{
    public $DB_PGSQL;
    use mdPublic,LibraryCSV,LibraryZIP;
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
    
    public function create_table_log_npb(){

        $tableName = 'log_npb';

        $tableExists = DB::select("SELECT * FROM information_schema.tables WHERE UPPER(table_name) = UPPER('$tableName')");

        if (empty($tableExists)) {
            $createTableQuery = "
                CREATE TABLE log_npb (
                    npb_tgl_proses  DATE,
                    npb_kodetoko    VARCHAR(5),
                    npb_nopb        VARCHAR(20),
                    npb_tglpb       DATE,
                    npb_nodspb      VARCHAR(20),
                    npb_file        VARCHAR(100),
                    npb_jenis       VARCHAR(10),
                    npb_url         VARCHAR(100),
                    npb_response    TEXT,
                    npb_jml_item    NUMERIC,
                    npb_create_web  VARCHAR(12),
                    npb_create_csv  VARCHAR(12),
                    npb_kirim       VARCHAR(12),
                    npb_confirm     VARCHAR(30),
                    npb_tgl_retry   DATE,
                    npb_jml_retry   NUMERIC,
                    npb_create_by   VARCHAR(20),
                    npb_create_dt   DATE,
                    npb_modify_by   VARCHAR(20),
                    npb_modify_dt   DATE
                )
            ";

            DB::statement($createTableQuery);

            return response()->json(['message' => 'Table created successfully!']);
        } else {
            // Uncomment and modify if you need to delete old records
            // $deleteOldRecordsQuery = "DELETE FROM log_npb WHERE DATE_TRUNC('day', npb_create_dt) < DATE_TRUNC('day', NOW() - 40)";
            // DB::statement($deleteOldRecordsQuery);

            return response()->json(['message' => 'Table already exists!']);
        }
    }

    public function voucher_load(Request $request){
        $this->create_table_log_npb();

        $tanggal = isset($request->tanggal)?$request->tanggal:null;
        $data = $this->initial($tanggal);
        if (isset($data->errors)) {
            return response()->json(['errors'=>true,'messages' => $data->messages],500);
        }

        return response()->json(['errors'=>false,'messages'=>'berhasi','data'=>$data],200);
    }
    
     public function initial($tanggal = null, $flag_detail = null, $mode_program = false){
     
        $picking = 0;
        $siapDspb = 0;
        $selesaiDspb = 0;
        $jmlhPb = 0;

        if ($tanggal) {
            $dtTransValue = date('Y-m-d',strtotime($tanggal));
        } else {
            $dtTransValue = date('Y-m-d');
        }
        
        $ModeProgram = session()->get('flagIGR')?'INDOMARET':'OMI';  // Replace with the actual mode program value

        try {
            // Query 1: Get total count
            $sql = "
            SELECT COUNT(*)
            FROM (
                SELECT 1
                FROM TBTR_HEADER_MATERAI_VOUCHER 
                JOIN tbmaster_tokoigr ON hmv_kodetoko = tko_kodeomi
                 -- WHERE hmv_tgltransaksi::date = ' $dtTransValue'::date  -- command for debug 
               
            ";
            if ($ModeProgram == 'OMI') {
                $sql .= " 
                         where TKO_KODESBU = 'O' limit 100 --debug
                        ";
                $sql .= " 
                         -- AND TKO_KODESBU = 'O')  -- command for debug
                        ) as query";
            } else {
                $sql .= " 
                         where TKO_KODESBU = 'I' limit 100 --debug
                        ";
                $sql .= " 
                         -- AND TKO_KODESBU = 'I'  -- command for debug
                        ) as query";
            }

            $jmlhPb = $this->DB_PGSQL->select($sql)[0]->count;
            

            // Query 2: Get picking count
            $sql = "
                SELECT COUNT(1)
                FROM (
                    SELECT DISTINCT HMV_KODETOKO, HMV_NOPB, HMV_TGLPB, HMV_ITEMPB
                    FROM TBTR_HEADER_MATERAI_VOUCHER 
                    JOIN TBMASTER_PBOMI ON HMV_KODETOKO = PBO_KODEOMI 
                    AND HMV_NOPB = PBO_NOPB 
                    AND HMV_TGLPB = PBO_TGLPB 
                    JOIN PLU_MATERAI_VOUCHER ON PMV_PLUIDM = PBO_PLUOMI
                    WHERE PMV_PLUIGR IS NOT NULL 
                    AND HMV_FLAG = '1' 
                     -- AND hmv_tgltransaksi::date = ' $dtTransValue'::date -- command for debug
                     limit 100 -- debug
                ) a
            ";

            $picking = $this->DB_PGSQL->select($sql)[0]->count;

            // Query 3: Get siapDspb count
            $sql = "
                SELECT COUNT(1)
                FROM (
                    SELECT DISTINCT HMV_KODETOKO, HMV_NOPB, HMV_TGLPB, HMV_ITEMPB
                    FROM TBTR_HEADER_MATERAI_VOUCHER 
                    JOIN TBMASTER_PBOMI ON HMV_KODETOKO = PBO_KODEOMI 
                    AND HMV_NOPB = PBO_NOPB 
                    AND HMV_TGLPB = PBO_TGLPB 
                    JOIN PLU_MATERAI_VOUCHER ON PMV_PLUIDM = PBO_PLUOMI
                    WHERE PMV_PLUIGR IS NOT NULL 
                    AND HMV_FLAG = '4' 
                    -- AND hmv_tgltransaksi::date = ' $dtTransValue'::date -- command for debug
                     limit 100 -- debug
                ) a
            ";

            $siapDspb = $this->DB_PGSQL->select($sql)[0]->count;

            // Query 4: Get selesaiDspb count
            $sql = "
                -- select COUNT(1) from (
                SELECT COUNT(1)
                FROM TBTR_HEADER_MATERAI_VOUCHER 
                JOIN tbmaster_tokoigr ON hmv_kodetoko = tko_kodeomi 
                 -- WHERE hmv_tgltransaksi::date = ' $dtTransValue'::date -- command for debug
                 -- AND HMV_FLAG = '5' -- command for debug
                 where HMV_FLAG = '5' -- debug
                
            ";
            if ($ModeProgram == 'OMI') {
                $sql .= " AND TKO_KODESBU = 'O'";
            } else {
                $sql .= " AND TKO_KODESBU = 'I'";
            }

            $sql .=  "-- limit 100 -- debug";
            $sql .=  "-- ) as query";

            $selesaiDspb = $this->DB_PGSQL->select($sql)[0]->count;

            // Update labels
            $lblPB = $jmlhPb;
            $lblPicking = $picking;
            $lblDspb = $selesaiDspb;
            $lblSDspb = $siapDspb;

            // Query 5: Get detailed data
            $sql = "
                SELECT ROW_NUMBER() OVER() AS NO, a.*
                FROM (
                    SELECT DISTINCT 
                        CASE 
                            WHEN COALESCE(HMV_FLAG, '0') = '1' THEN 'Siap Picking' 
                            WHEN COALESCE(HMV_FLAG, '0') = '4' THEN 'Siap DSPB' 
                            ELSE 'Selesai DSPB' 
                        END AS STAT, 
                        HMV_KODETOKO, HMV_NOPB, HMV_TGLPB::date, HMV_ITEMPB
                    FROM TBTR_HEADER_MATERAI_VOUCHER 
                    JOIN TBMASTER_PBOMI ON HMV_KODETOKO = PBO_KODEOMI 
                    AND HMV_NOPB = PBO_NOPB 
                    AND HMV_TGLPB = PBO_TGLPB 
                    JOIN PLU_MATERAI_VOUCHER ON PMV_PLUIDM = PBO_PLUOMI
                    WHERE PMV_PLUIGR IS NOT NULL 
                     -- AND hmv_tgltransaksi::date = ' $dtTransValue'::date -- command for debug
                     limit 100 -- debug
                ) a
            ";

            $dt = $this->DB_PGSQL->select($sql);


          
            $lblMonitoring = count($dt) . " PB Terupload";

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

        } catch (\Exception $e) {
            dd($e);
            return ['errors'=>true,'messages' => 'Gagal!'.$e->getMessage()];
        }

          
     }

    public function dspb_vm($kodetoko = null, $tglpb = null, $nopb= null){
        
         try {
            $this->DB_PGSQL->beginTransaction();

            $noDspb = $this->DB_PGSQL->select("SELECT NEXTVAL('SEQ_NPB')")[0]->nextval;
            $thnPb = $this->DB_PGSQL->select("SELECT TO_CHAR(CURRENT_DATE, 'YY')")[0]->to_char;
            $tglServer = date('Y-m-d');
            $noDspb = $thnPb . str_pad($noDspb, 5, "0", STR_PAD_LEFT);
            $kodeDCIDM = "";
            $listPath = [];
            $pathSaveZip ="zip_voucher/";
            $storage_path = "csv_voucher/";
            
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
                        // ->whereRaw(" PBO_TGLPB::date = '$tglpb'::date ")
                        // ->whereRaw(" pbo_nopb = '$nopb'")
                        // ->whereRaw(" pbo_kodeomi = '$kodetoko'")
                        // ->whereRaw(" pbo_qtyrealisasi > 0")
                        // ->whereRaw(" pbo_recordid = '4'")
                        // ->whereRaw(" pbo_nokoli like '04%'")
                        ->limit(1) // debug
                        ->get();
                       
            if (count($dtD)) {
                // $update_db= $this->update_db($noDspb, $kodetoko,$nopb,$tglpb); // command for debug
                $nmNpb = "NPV" . $dtD[0]->kirim . $dtD[0]->toko . $tglServer.".csv";
                $header =["recid","rtype","docno","seqno","picno","picnot","pictgl","prdcd","nama","div","qty","sj_qty","price","gross","ppnrp","hpp","toko","keter","tanggal1","tanggal2","docno2","lt","rak","bar","kirim","dus_no","ppn_rate","bkp","sub_bkp"];
                $listPath[] = $this->make_csv(json_decode(json_encode($dtD),true),$nmNpb,$storage_path,$header);

                $lblMonitoring ="Write NPV!";
                $jamCreateWeb = date("H:i:s");
                $jamCreateCSV = date("H:i:s");

                 $dtH = $this->DB_PGSQL
                                ->table("tbmaster_pbomi as a")
                                ->selectRaw("
                                    '" . $noDspb . "' docno,
                                    TO_CHAR(CURRENT_DATE, 'dd-MM-YYYY') doc_date,
                                    pbo_kodeomi toko,
                                    (SELECT 'GI' || prs_kodeigr FROM tbmaster_perusahaan LIMIT 1) gudang, 
                                    COUNT(DISTINCT pbo_pluomi) item, 
                                    SUM(pbo_qtyorder) qty, 
                                    SUM(pbo_ttlnilai) gross, 
                                    NULL koli, 
                                    NULL kubikasi 
                                ")
                                // ->whereRaw("A.PBO_TGLPB::date = '$tglpb'::date  ")
                                // ->whereRaw("pbo_nopb = '$nopb'")
                                // ->whereRaw("pbo_kodeomi = '$kodetoko'")
                                // ->whereRaw("pbo_nokoli like '04%' ")
                                // ->whereRaw("PBO_QTYREALISASI > 0 ")
                                ->limit(1) // debug
                                ->groupBy("pbo_tglpb","pbo_kodeomi")
                                ->get();
                if (count($dtH) > 0) {
                    // buat csv
                    $nmRpb = "XPV" . $dtH[0]->gudang . $dtH[0]->toko . date('Ymdhis').".csv";
                    $header =["docno","doc_date","toko","gudang", "item", "qty", "gross", "koli", "kubikasi" ];
                    $listPath[] = $this->make_csv(json_decode(json_encode($dtH),true),$nmRpb,$storage_path,$header);
                }
                // buat zip 
                $pathZip = $this->make_zip($nmNpb.'.zip', null, $listPath, $pathSaveZip);
                dd($pathZip);
                // Ionic.Zip
                //   $listFile = [];
                //   $listFile[] = $_txtPath . DIRECTORY_SEPARATOR . $nmNpb . ".CSV";
                //   $listFile[] = $_txtPath . DIRECTORY_SEPARATOR . $nmRpb . ".CSV";
                //   Zip_File($_txtPath . DIRECTORY_SEPARATOR . $nmNpb . ".ZIP", $listFile);
                // end buat zip ??


                // SIMPAN NAMA NPB
                // $this->simpanDSPB($nmNpb . ".ZIP", $kodetoko, $nopb, 0, 0, $noDspb, "V- PBVOUCHER"); // command for debug

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
                    // $okNPB = $this->insert_to_npb($_tglServer.$npbGudang, $nmNpb, $dtH, $dtD); // command for debug

                      // Insert into log_npb // command for debug
                    //   $this->DB_PGSQL
                    //         ->table("log_npb")
                    //         ->insert([
                    //                 "npb_tgl_proses" =>  $this->DB_PGSQL->raw("current_date"),
                    //                 "npb_kodetoko" =>  $kodetoko,
                    //                 "npb_nopb" =>  $nopb,
                    //                 "npb_tglpb" =>  $this->DB_PGSQL->raw("'$tglpb'::date"),
                    //                 "npb_nodspb" =>  $noDspb,
                    //                 "npb_file" =>  $nmNpb,
                    //                 "npb_jml_item" =>  $jmlItem,
                    //                 "npb_jenis" =>  $this->DB_PGSQL->raw("'VOUCHER'"),
                    //                 "npb_url" =>  $npbIP,
                    //                 "npb_response" =>  $this->DB_PGSQL->raw(($okNPB ? "SUKSES! " : "GAGAL! ") . $npbRes),
                    //                 "npb_create_web" =>  $jamCreateWeb,
                    //                 "npb_create_csv" =>  $jamCreateCSV,
                    //                 "npb_kirim" =>  $jamKirim,
                    //                 "npb_confirm" =>  $tglConfirm,
                    //                 "npb_jml_retry" =>  0,
                    //                 "npb_create_by" =>  session('userid'),
                    //                 "npb_create_dt" => $this->DB_PGSQL->raw("current_date")
                    //             ]);
                    //send mail
                    // $lblMonitoring->setText("Send MAIL!");
                    // Application::doEvents();
                    // DSPB::sendMail($_txtPath . DIRECTORY_SEPARATOR . $nmNpb . ".ZIP", $nmNpb, Caesar_Encrypt("P" . $noDspb . "9999", $_tglServer));
                  }

                 

                
                $response = ["errors"=>false, "messages"=>"Successfully"];
            } else {
                $response = ["errors"=>true, "messages"=>"Tidak ada data!"];
            }
            
            $this->DB_PGSQL->commit();
            return $response;
        } catch (\Throwable $th) {
            
            $this->DB_PGSQL->rollBack();
            // dd($th);5
            return ['errors'=>true,'messages'=>$th->getMessage()];
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
            return ['errors'=>true,'messages'=>$th->getMessage(),'point'=>$point];
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

    public function data_qrcode($kodetoko,$nopb,$tglpb){

        $data_qr = (new QRController)->load_qr($kodetoko,$nopb,$tglpb);
        return $data_qr;
    }
    public function data_sj($kodetoko,$nopb,$tglpb){

        $flagFTZ = session()->get('flagFTZ'); // Set the actual flag value

        $sql = "SELECT ikl_kodeigr AS kode_igr, ikl_nokoli AS koli, ikl_nocontainer AS CONTAINER, COUNT(pbo_qtyrealisasi) AS item, ";

        if ($flagFTZ) {
            $sql .= "SUM(COALESCE(pbo_ttlnilai,0) + COALESCE(pbo_ttlppn,0)) AS bkp, 
                    0 AS btkp, 
                    0 AS ppn ";
        } else {
            $sql .= "SUM(CASE WHEN COALESCE(pbo_ttlppn,0) > 0 THEN COALESCE(pbo_ttlnilai,0) END) AS bkp, 
                    SUM(CASE WHEN COALESCE(pbo_ttlppn,0) = 0 THEN COALESCE(pbo_ttlnilai,0) END) AS btkp, 
                    SUM(pbo_ttlppn) AS ppn ";
        }

        $sql .= "FROM tbmaster_pbomi 
                JOIN tbtr_idmkoli ON pbo_tglpb::date = ikl_tglpb::date
                AND pbo_nopb = ikl_nopb 
                AND pbo_kodeomi = ikl_kodeidm 
                AND pbo_nokoli = ikl_nokoli 
                 AND ikl_tglpb::date = '$tglpb'::date  -- command for debug 
                 AND ikl_kodeidm = '$kodetoko'  -- command for debug 
                 AND IKL_NOPB = '$nopb'  -- command for debug 
                JOIN tbmaster_prodmast ON pbo_pluigr = prd_prdcd 
                LEFT JOIN tbmaster_kodefp ON COALESCE(prd_flagbkp1, 'N') = kfp_flagbkp1 
                AND COALESCE(prd_flagbkp2, 'N') = kfp_flagbkp2 
                 WHERE PBO_QTYREALISASI > 0 -- command for debug 
                 AND PBO_RECORDID = '4' -- command for debug 
                 AND PBO_NOKOLI LIKE '04%' -- command for debug 
                GROUP BY ikl_kodeigr, IKL_NOKOLI, ikl_nocontainer 
                ORDER BY IKL_NOKOLI
                -- limit 10 -- debug
                ";

        // Execute the raw query with bindings
        $data = $this->DB_PGSQL->select($sql);

        //debug 
        // $data = [];

        // $data[] = (object)[
        //     "kode_igr"=> "Test kode_igr",
        //     "koli"=> "Test koli",
        //     "container"=> "Test container",
        //     "item"=> "Test item",
        //     "bkp"=> "Test bkp",
        //     "btkp"=> "Test btkp",
        //     "ppn"=> "Test ppn",
        // ];

        $sql = "
            select * from (select distinct hmv_kodetoko as kodetoko, hmv_tglpb as tglpb, hmv_nopb as nopb,
            ikl_registerrealisasi as nodspb, ikl_nokoli as nokoli
            from tbtr_header_materai_voucher
            join tbtr_idmkoli on hmv_kodetoko = ikl_kodeidm and hmv_nopb = ikl_nopb and hmv_tglpb::date = ikl_tglpb::date
            join tbmaster_pbomi on hmv_kodetoko = pbo_kodeomi and hmv_nopb = pbo_nopb and hmv_tglpb = pbo_tglpb
            where hmv_kodetoko = '$kodetoko'
            and hmv_nopb = '$nopb'
            and hmv_tglpb::date = '$tglpb'::date
            and ikl_nokoli like '04%' order by ikl_nokoli desc) as t LIMIT 1";

        $header =  $this->DB_PGSQL->select($sql);
        $sql = "
            select tko_kodeigr as kode_igr, TKO_NAMAOMI, TKO_KODEOMI
            from tbmaster_tokoigr
            where TKO_KODEOMI = '$kodetoko'
            and TKO_NAMASBU = 'INDOMARET'";
        
        $toko = $this->DB_PGSQL->select($sql);
        // Fetch additional data for cluster and group
        $cluster = $this->DB_PGSQL->table('cluster_idm')->where('cls_toko', $kodetoko)->value('cls_kode');
        $group = $this->DB_PGSQL->table('cluster_idm')->where('cls_toko', $kodetoko)->value('cls_group');
        if (count($data) ) {
            return (object)['data'=>$data,'label'=>"Cluster : $cluster  Group : $group",'header'=>$header,'toko'=>$toko,'cluster'=>$cluster,'group'=>$group];
        }else{
            return (object)['errors'=>true,'messages'=>'Data Tidak Ada'];
        }

    }
    public function data_nomor_referensi($kodetoko,$nopb,$tglpb){
        $sql = "
        select distinct hmv_kodetoko as kodetoko, hmv_tglpb as tglpb, hmv_nopb as nopb,
        pmv_pluidm as pluidm, pmv_pluigr as pluigr, pch_qtyrealisasi as qty, prd_deskripsipanjang as desc2
        from tbtr_header_materai_voucher
        join tbmaster_pbomi on hmv_kodetoko = pbo_kodeomi and hmv_nopb = pbo_nopb and hmv_tglpb = pbo_tglpb
        join tbtr_picking_h on hmv_kodetoko = pch_kodetoko and hmv_nopb = pch_nopb and hmv_tglpb = pch_tglpb AND pch_pluigr = substr(pbo_pluigr,1,6) || '0'
        join tbtr_picking_d on pch_id = pcd_id
        join PLU_MATERAI_VOUCHER on pbo_pluomi = pmv_pluidm
        join tbmaster_prodmast on pch_pluigr = prd_prdcd
        where hmv_kodetoko = '$kodetoko'
        and hmv_nopb = '$nopb'
        and hmv_tglpb::date = '$tglpb'::date";
    
        $rinci_noseri =  $this->DB_PGSQL->select($sql);
        if (count($rinci_noseri)) {
            $sql = "
                select * from (select distinct hmv_kodetoko as kodetoko, hmv_tglpb as tglpb, hmv_nopb as nopb,
                ikl_registerrealisasi as nodspb, ikl_nokoli as nokoli
                from tbtr_header_materai_voucher
                join tbtr_idmkoli on hmv_kodetoko = ikl_kodeidm and hmv_nopb = ikl_nopb and hmv_tglpb::date = ikl_tglpb::date
                join tbmaster_pbomi on hmv_kodetoko = pbo_kodeomi and hmv_nopb = pbo_nopb and hmv_tglpb = pbo_tglpb
                where hmv_kodetoko = '$kodetoko'
                and hmv_nopb = '$nopb'
                and hmv_tglpb::date = '$tglpb'::date
                and ikl_nokoli like '04%' order by ikl_nokoli desc) as t LIMIT 1";

            $header =  $this->DB_PGSQL->select($sql);
            $sql = "
                select tko_kodeigr as kode_igr, TKO_NAMAOMI, TKO_KODEOMI
                from tbmaster_tokoigr
                where TKO_KODEOMI = '$kodetoko'
                and TKO_NAMASBU = 'INDOMARET'";
            
            $toko = $this->DB_PGSQL->select($sql);
     
            return (object) ['rinci_noseri'=>$rinci_noseri,'header'=>$header,'toko'=>$toko];
        } else {
           return (object)['errors'=>true,'messages'=>'Data Tidak Ada'];
        }
        


    }

    public function download_report(Request $request, $data){
        $data = json_decode(base64_decode($data));
        $jenis_page = 'default-page';
        $folder_page = $data->folder_page;
        $title_report = $data->title_report;
        $filename = $data->filename;
        $header_cetak_custom = false;
        $postiion_page_number_x = 63;
        $postiion_page_number_y = 615;
        $tanggal = date('Y-m-d');
                
        $perusahaan = $this->DB_PGSQL
                           ->table("tbmaster_perusahaan")
                           ->whereRaw("prs_kodeigr = '".session('KODECABANG')."'")
                           ->get();
        $perusahaan = $perusahaan[0];


        switch ($data->filename) {
            case 'reportqr':
                $data->data = $this->data_qrcode($data->kodetoko,$data->nopb,$data->tglpb);
                $header_cetak_custom = 'upper';
                if (isset($data->data->errors)) {
                    $data = null;
                }
                
                break;
            case 'report_rincian_nomor_referensi_materai':
                $data->data = $this->data_nomor_referensi($data->kodetoko,$data->nopb,$data->tglpb);
                // $header_cetak_custom = 'upper';
                // dd($data);
                if (isset($data->data->errors)) {
                    $data = null;
                }
                
                break;
            case 'SJ':
                $data->data = $this->data_sj($data->kodetoko,$data->nopb,$data->tglpb);
                // $header_cetak_custom = 'upper';
                // dd($data);
                if (!isset($data->data->errors)) {
                    $encryptedValue = $this->caesarEncrypt("P" .  $data->data->header[0]->nodspb . "9999", date('Y-m-d'));
                    $data->data->header[0]->encrypt = $encryptedValue;
                }
                if (isset($data->data->errors)) {
                    $data = null;
                }
                
                break;
            default:
                break;
        }

            
        $pdf = PDF::loadview('menu.voucher.'.$folder_page.'.'.$filename, compact('data','tanggal','perusahaan','header_cetak_custom'));
        $pdf->output();
        $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
        $canvas = $dompdf->get_canvas();

        // //make page text in header and right side

        $canvas->page_text($postiion_page_number_y,$postiion_page_number_x , "Page {PAGE_NUM} of {PAGE_COUNT}", null, 7, array(0, 0, 0));

        return $pdf->stream($title_report );

    }

    public function print_report(Request $request){
        $qrcode = $request->qrcode == "true"?true:false;
        $kodetoko = $request->kodetoko;
        $nopb = $request->nopb;
        $tglpb = $request->tglpb;
        $link_qr = null;

        
        $data_report =[];
        $data_report['qrcode'] = $qrcode;
        $data_report['kodetoko'] = $kodetoko;
        $data_report['nopb'] = $nopb;
        $data_report['tglpb'] = $tglpb;
        $data_report['filename'] = 'reportqr';
        $data_report['folder_page'] = 'report'; 

        $data_report['jenis_page'] = 'default-page';
        $data_report['title_report'] = 'REPORT';
        $encrypt_data = base64_encode(json_encode($data_report));
        if($qrcode){
            $link_qr = url('/api/voucher/download/report/'.$encrypt_data);
        }  
        $data_report['filename'] = 'report_rincian_nomor_referensi_materai';
        $encrypt_data = base64_encode(json_encode($data_report));   
        $link_ref = url('/api/voucher/download/report/'.$encrypt_data);
        $data_report['filename'] = 'SJ';
        $encrypt_data = base64_encode(json_encode($data_report));   
        $link_sj = url('/api/voucher/download/report/'.$encrypt_data);

        $dspb_vm = $this->dspb_vm($kodetoko, $tglpb, $nopb);
        dd($dspb_vm);
        if(!$dspb_vm){
            if((object)$dspb_vm->errors){
                return response()->json($dspb_vm,500);
            }
        }
        return response()->json(['errors'=>false,'messages'=>'berhasi','link_qr'=>$link_qr,'link_ref'=>$link_ref,'link_sj'=>$link_sj],200);
        
    }

    public function picking_load(Request $request){

        $tglpb = $request->tglpb;
        $kodetoko = $request->kodetoko;
        $nopb = $request->nopb;
        $qtyOrder = null;
        $deskripsi = null;
        $qtyRealisasi = null;
        $plu = null;
        $Searchplu = null;
        $noSeri = null;
        $headerText = $kodetoko . " / " . $nopb . " / " .date('d-m-Y',strtotime($tglpb));

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
                        ->whereRaw("HMV_NOPB = '$nopb'")
                        ->whereRaw("HMV_KODETOKO = '$kodetoko'")
                        ->orderBy("pmv_pluigr","asc")
                        // ->limit(1)
                        ->get();
        foreach ($picking as $row) {
            $exists =  $this->DB_PGSQL->table('tbtr_picking_h')
                ->where('pch_pluigr', $row->plu)
                ->where('pch_nopb', $nopb)
                ->whereRaw("pch_tglpb::date = '$tglpb'::date")
                ->where('pch_kodetoko', $kodetoko)
                ->exists();

            if ($exists) {
                $plu = $row->plu . " #";
                $Searchplu = $row->plu;
            } else {
                $plu = $row->plu;
                $Searchplu = $row->plu;
            }
        }
        
        if (count($picking)) {
            // Retrieve the data and perform necessary actions
            // For example:
            $qtyOrder = $picking[0]->qty_order;
            $deskripsi = $picking[0]->deskripsi;
            $qtyRealisasi = $picking[0]->qty_realisasi;
            // $plu = $picking[0]->plu;
            $noSeri = $this->load_no_seri($tglpb,$nopb,$kodetoko,$Searchplu);
            $data = [
                "picking_data" =>$picking,
                "header" =>$headerText,
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
        $headerText = $kodetoko . " / " . $nopb . " / " . date('d-m-Y',strtotime($tglpb));
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
                        ->limit(1)
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
                "picking_data" =>$picking,
                "header" =>$headerText,
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
            $no_seri = $request->noseri;
            $jmlh_seri = (int)$request->jmlh_seri;
            $plu_picking = strpos($request->plu_picking, ' #')?str_replace(' #', '', $request->plu_picking):$request->nopb;
            $qty_order_picking = (int)$request->qty_order_picking;
            $qty_realisasi_picking = (int)$request->qty_realisasi_picking;
            $no_picking1 = (int)$request->no_picking1;
            $no_picking2 = (int)$request->no_picking2;
            // $deskripsi_picking = $request->deskripsi_picking;





        if (!$qty_realisasi_picking) {
            return response()->json(['errors'=>true,'messages' => 'Qty Realisasi belum diisi'], 422);
        }
        
        if (!$jmlh_seri) {
            return response()->json(['errors'=>true,'messages' => 'Tidak ada data nomor referensi'], 422);
        }
        
        if ($qty_realisasi_picking != $jmlh_seri) {
            return response()->json(['errors'=>true,'messages' => 'Jumlah nomor referensi tidak sama qty realisasi'], 422);
        }

        $exists =  $this->DB_PGSQL->table('tbtr_picking_h')
            ->where('pch_nopb', $nopb)
            ->whereRaw("PCH_TGLPB::date = '$tglpb'::date")
            ->where('pch_kodetoko', $kodetoko)
            ->where('pch_pluigr', $plu_picking)
            ->exists();

        $this->DB_PGSQL->beginTransaction();
        if ($exists) {

            $pid = $this->DB_PGSQL->table('tbtr_picking_h')
                ->select('pch_id')
                ->whereRaw("PCH_TGLPB::date = '$tglpb'::date")
                ->where('pch_nopb', $nopb)
                ->where('pch_kodetoko', $kodetoko)
                ->where('pch_pluigr', $plu_picking)
                ->value('pch_id');

            if ($pid) {

                $this->DB_PGSQL->table('tbtr_picking_d')->where('pcd_id', $pid)->delete();

                $this->DB_PGSQL->table('tbtr_picking_d')->insert([
                    'pcd_id' => $pid,
                    'pcd_referensi' => $no_seri,
                    'pcd_create_by' => session()->get('userid'),
                    'pcd_create_dt' => date('Y-m-d'),
                ]);

                $this->DB_PGSQL->table('tbtr_picking_h')
                    ->where('pch_id', $pid)
                    ->update([
                        'pch_qtyrealisasi' => $qty_realisasi_picking,
                        'pch_modify_by' => session()->get('userid'),
                        'pch_modify_dt' => date('Y-m-d'),
                    ]);

            } else {
                return response()->json(['errors'=>true,'messages' => 'Data tidak ditemukan!'], 404);
            }
        } else {
            $this->DB_PGSQL->table('tbtr_picking_d')->insert([
                'pcd_id' => $noid,
                'pcd_referensi' => $no_seri,
                'pcd_create_by' => session()->get('userid'),
                'pcd_create_dt' => date('Y-m-d'),
            ]);
    
            $this->DB_PGSQL->table('tbtr_picking_h')->insert([
                'pch_id' => $noid,
                'pch_nopb' => $nopb,
                'pch_tglpb' => date('Y-m-d',strtotime($tglpb)),
                'pch_kodetoko' => $kodetoko,
                'pch_pluigr' => $plu_picking,
                'pch_qtyorder' => $qty_order_picking,
                'pch_qtyrealisasi' => $qty_realisasi_picking,
                'pch_create_by' => session()->get('userid'),
                'pch_create_dt' => date('Y-m-d'),
            ]);
        }
        

    
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