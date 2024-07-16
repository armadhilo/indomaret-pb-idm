<?php

namespace App\Http\Controllers;

use App\Traits\LibraryCSV;
use Illuminate\Http\Request;
use DB;
use PDF;

class MonitoringController extends Controller
{
    use LibraryCSV;
    public $DB_PGSQL;
    public $kodeigr;
    public function __construct()
    { 
        $this->kodeigr = session('KODECABANG');
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
        return view("menu.monitoring.index",compact('flag'));
    }

    public function create_table_log_npb(){

        $tableName = 'log_npb';

        $tableExists = $this->DB_PGSQL->select("SELECT * FROM information_schema.tables WHERE UPPER(table_name) = UPPER('$tableName')");

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

            $this->DB_PGSQL->statement($createTableQuery);

            return response()->json(['message' => 'Table created successfully!']);
        } else {
            // Uncomment and modify if you need to delete old records
            // $deleteOldRecordsQuery = "DELETE FROM log_npb WHERE DATE_TRUNC('day', npb_create_dt) < DATE_TRUNC('day', NOW() - 40)";
            // DB::statement($deleteOldRecordsQuery);

            return ['message' => 'Table already exists!'];
        }
    }

    public function create_table_log_omi(){
        return true;    
    }

    public function monitoring_load(Request $request){
        $param = isset($request->param)?$request->param:null;
        $report_zona = isset($request->report_zona)?$request->report_zona:false;
        $flag_detail = isset($request->flag_detail)?$request->flag_detail:false;
        $tanggal = isset($request->tanggal)?$request->tanggal:null;
        $zona = isset($request->zona)?$request->zona:null;
        $mode_omi = !(session()->get('flagIGR'))?true:false;

        $this->create_table_log_npb();
        $this->create_table_log_omi();

        $data_card = $this->initial($param,$report_zona,$flag_detail,$tanggal,$zona,$mode_omi);
        $data_zona = $this->load_zona();
        if (isset($data_card->errors)) {
            return response()->json(['errors'=>true,'messages'=>$data_card->messages],500);
        }

        return response()->json(['errors'=>false,'messages'=>'berhasi','data_zona'=>$data_zona,'data_card'=>$data_card],200);
    }

    public function load_zona(){

        $data = $this->DB_PGSQL
                     ->table("zona_idm")
                     ->select("zon_kode")
                     ->distinct()
                     ->orderBy("zon_kode","asc")
                     ->get();
        return $data;
    }


    public function initial($param,$report_zona = null, $flag_detail = false,$tanggal = '',$zona =null,$mode_omi = false)
    {
        $sendJalur = 0;
        $picking = 0;
        $scanning = 0;
        $siapDspb = 0;
        $selesaiDspb = 0;
        $jmlhPb = 0;
        $selesaiLoading = 0;
        $dtTrans = $tanggal?date('Y-m-d',strtotime($tanggal)): date('Y-m-d');
        $dtTrans = date('Y-m-d');
        $debug_condition = false;
        $command_for_debug = $debug_condition?"-- ":'';
        $debug = !$debug_condition?"-- ":'';

        try {

            // Count total PB
            $sql = "SELECT COUNT(*)
                FROM (
                    SELECT 1
                    FROM tbtr_header_pbidm
                    JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
                    ".$command_for_debug." WHERE hpbi_tgltransaksi::date = '$dtTrans'::date 
                    ";
            if (!$debug_condition) {
                if ($mode_omi) {
                    $sql .= "AND TKO_KODESBU = 'O' ";
                } else {
                    $sql .= "AND TKO_KODESBU = 'I' ";
                }
            }
            $sql .= ") as query";
            $jmlhPb = $this->DB_PGSQL->selectOne($sql)->count;
            // Count PB SendJalur
            $sql = "SELECT COUNT(1)
                    FROM tbtr_header_pbidm
                    JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
                    ".$command_for_debug." WHERE hpbi_tgltransaksi::date = '$dtTrans'::date
                    ".$command_for_debug." AND HPBI_FLAG IS NOT NULL 
                    ".$debug." where HPBI_FLAG IS NOT NULL 
                    ";
            if (!$debug_condition) {
                if ($mode_omi) {
                    $sql .= "AND TKO_KODESBU = 'O' ";
                } else {
                    $sql .= "AND TKO_KODESBU = 'I' ";
                }
            }
            $sendJalur = $this->DB_PGSQL->selectOne($sql)->count;

            // Count PB Picking
            $sql = "SELECT COUNT(1)
                    FROM tbtr_header_pbidm
                    JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
                    ".$command_for_debug."WHERE hpbi_tgltransaksi::date = '$dtTrans'::date
                    ".$command_for_debug."AND HPBI_FLAG <> '5' 
                    ".$debug."AND HPBI_FLAG <> '5' 
                    ";
            if ($mode_omi) {
                $sql .= "AND TKO_KODESBU = 'O'
                        AND EXISTS (
                            SELECT 1
                            FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab
                                FROM dpd_idm_ora
                                ".$command_for_debug." WHERE tglupd::date = '$dtTrans'::date
                                ".$command_for_debug." AND (FMRCID = '1' OR FMRCID = '2')
                                ".$debug." where (FMRCID = '1' OR FMRCID = '2')
                            ) q
                            WHERE fmkcab = HPBI_KODETOKO
                            AND tglpb = HPBI_TGLPB
                            AND fmndoc = HPBI_NOPB
                        ) ";
            } else {
                $sql .= "AND TKO_KODESBU = 'I'
                        AND EXISTS (
                            SELECT 1
                            FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan
                                FROM dpd_idm_ora
                                ".$command_for_debug."WHERE tglupd::date = '$dtTrans'::date
                                ".$command_for_debug."AND (FMRCID = '1' OR FMRCID = '2')
                                ".$debug."where (FMRCID = '1' OR FMRCID = '2')
                            ) q
                            WHERE fmkcab = HPBI_KODETOKO
                            AND tglpb = HPBI_TGLPB
                            AND fmndoc = HPBI_NOPB
                            AND nopicking = hpbi_nopicking
                            AND nosuratjalan = hpbi_nosj
                        ) ";
            }
            $picking = $this->DB_PGSQL->selectOne($sql)->count;

            // Count PB Scanning
            $sql = "SELECT COUNT(1)
                    FROM tbtr_header_pbidm
                    JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
                    WHERE hpbi_tgltransaksi::date = '$dtTrans'::date
                    AND HPBI_FLAG <> '5' ";
            if ($mode_omi) {
                $sql .= "AND TKO_KODESBU = 'O'
                        AND EXISTS (
                            SELECT 1
                            FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab
                                FROM dpd_idm_ora
                                ".$command_for_debug." WHERE tglupd::date = '$dtTrans'::date
                                ".$command_for_debug." AND FMRCID = '3'
                                ".$debug." where FMRCID = '3'
                            ) t
                            WHERE fmkcab = HPBI_KODETOKO
                            AND tglpb = HPBI_TGLPB
                            AND fmndoc = HPBI_NOPB
                        ) ";
            } else {
                $sql .= "AND TKO_KODESBU = 'I'
                        AND EXISTS (
                            SELECT 1
                            FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan
                                FROM dpd_idm_ora
                                ".$command_for_debug."WHERE tglupd::date = '$dtTrans'::date
                                ".$command_for_debug."AND FMRCID = '3'
                                ".$debug." where FMRCID = '3'
                            ) t
                            WHERE fmkcab = HPBI_KODETOKO
                            AND tglpb = HPBI_TGLPB
                            AND fmndoc = HPBI_NOPB
                            AND nopicking = hpbi_nopicking
                            AND nosuratjalan = hpbi_nosj
                        ) ";
            }
            $scanning = $this->DB_PGSQL->selectOne($sql)->count;
            
            // Count PB Loading
            $sql = "SELECT COUNT(1)
                    FROM tbtr_header_pbidm
                    JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
                    WHERE hpbi_tgltransaksi::date = '$dtTrans'::date
                    AND HPBI_FLAG = '4' ";
            if ($mode_omi) {
                $sql .= "AND TKO_KODESBU = 'O' ";
            } else {
                $sql .= "AND TKO_KODESBU = 'I' ";
            }
            $selesaiLoading = $this->DB_PGSQL->selectOne($sql)->count;
            // Count PB DSPB
            $sql = "SELECT COUNT(1)
                    FROM tbtr_header_pbidm
                    JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
                    WHERE hpbi_tgltransaksi::date = '$dtTrans'::date
                    AND HPBI_FLAG = '5' ";
            if ($mode_omi) {
                $sql .= "AND TKO_KODESBU = 'O' ";
            } else {
                $sql .= "AND TKO_KODESBU = 'I' ";
            }
            $selesaiDspb = $this->DB_PGSQL->selectOne($sql)->count;

            // Set labels
            $lblPB = $jmlhPb;
            $lblSendJalur = $sendJalur;
            $lblPicking = $picking;
            $lblScanning = $scanning;
            $lblloading = $selesaiLoading;
            $lblDspb = $selesaiDspb;

            // Additional query
            $sql = "  SELECT ROW_NUMBER() OVER() no, dtl.* 
            FROM ( 
              SELECT 
                toko, 
                nopb, 
                tglpb, 
                nopick, 
                nosj, 
                total, 
                selesai 
              FROM ( 
                SELECT 
                  fmkcab toko, 
                  fmndoc nopb, 
                  tglpb, 
                  nopick, 
                  nosj, 
                  COUNT(1) total, 
                  SUM(tutupkoli) selesai 
                FROM ( 
                  SELECT 
                    kodezona, 
                    dcp, 
                    fmkcab, 
                    fmndoc, 
                    tglpb, 
                    nopick, 
                    nosj, 
                    CASE WHEN dca_flag = '3' 
                      THEN 1 
                      ELSE 0 
                    END tutupkoli 
                  FROM ( 
                    SELECT 
                      kodezona, 
                      MAX(grak) dcp, 
                      fmkcab, 
                      fmndoc, 
                      tglpb, 
                      nopicking nopick, 
                      nosuratjalan nosj 
                    FROM ( 
                      SELECT DISTINCT  
                        fmkcab, 
                        fmndoc, 
                        tglpb, 
                        grak, 
                        kodezona, 
                        nopicking, 
                        nosuratjalan 
                      FROM dpd_idm_ora 
                      WHERE tglupd = '$dtTrans' 
                    ) p, tbtr_header_pbidm, tbmaster_tokoigr  
                   WHERE hpbi_kodetoko = tko_kodeomi 
                    AND hpbi_kodetoko = fmkcab 
                    AND hpbi_nopb = fmndoc 
                    AND hpbi_tglpb = tglpb 
                    AND hpbi_nopicking = nopicking 
                    AND hpbi_nosj = nosuratjalan ";
            if ($mode_omi) {
                $sql .= "AND tko_kodesbu = 'O' ";
            } else {
                $sql .= "AND tko_kodesbu = 'I' ";
            }
            $sql .= "AND hpbi_tgltransaksi = '$dtTrans'
                     AND hpbi_flag <> '5' 
                     GROUP BY kodezona, fmkcab, fmndoc, tglpb, nopicking, nosuratjalan 
                ) hdr 
                LEFT JOIN dcp_antrian
                ON fmkcab = dca_toko 
                AND fmndoc = dca_nopb  
                AND tglpb = dca_tglpb  
                AND dcp = dca_grouprak  
                AND nopick = dca_nopicking 
                AND nosj = dca_nosj 
                ) q 
                GROUP BY fmkcab, fmndoc, tglpb, nopick, nosj 
            ) t 
            WHERE total > 0 
            ORDER BY nopick 
            ".$command_for_debug."limit 1000
            ".$debug."limit 100
            ) dtl ";
            $data = $this->DB_PGSQL->select($sql);


         if ($param == "4" && !$report_zona) {
            //  $dgv->dataSource = $dt2;
            if ($report_zona) {
                $dt =  $data;
            } else {
                $dt = $this->initial_detail($dtTrans,$zona,$report_zona, $flag_detail,$param ,$mode_omi );
            }
            
         } else {
            //  $dgv->dataSource = $dt;
             $dt = $this->initial_detail($dtTrans,$zona,$report_zona, $flag_detail,$param,$mode_omi);
         }


        foreach ($dt as $key => $row) {
            $siapDspb++;
        }
        $lblSDspb = $siapDspb;

        switch ($param) {
            case "":
                $lblMonitoring = count($dt) . " PB Terupload";
                break;
            case "1":
                $lblMonitoring = count($dt) . " Sudah Send Jalur";
                break;
            case "2":
                if ($report_zona) {
                    $lblMonitoring = count($dt) . " ZONA Sedang Picking";
                } else {
                    $lblMonitoring = count($dt) . " Sedang Picking";
                }
                break;
            case "3":
                if ($report_zona) {
                    $lblMonitoring = count($dt) . " ZONA Sedang Scanning";
                } else {
                    $lblMonitoring = count($dt) . " Sedang Scanning";
                }
                break;
            case "4":
                $lblMonitoring = count($dt) . " Siap DSPB";
                break;
            case "5":
                $lblMonitoring = count($dt) . " Selesai LOADING";
                break;
            case "6":
                $lblMonitoring = count($dt) . " Selesai DSPB";
                break;
            default:
                // Handle any other cases if needed
                break;
        }
        

            // Return or process the data as needed
            return [
                'lblPB' => $lblPB,
                'lblSendJalur' => $lblSendJalur,
                'lblPicking' => $lblPicking,
                'lblScanning' => $lblScanning,
                'lblloading' => $lblloading,
                'lblDspb' => $lblDspb,
                'data' => $data,


                "siapDspb" => $siapDspb,
                "jmlhPb" => $jmlhPb,
                "sendJalur" => $sendJalur,
                "picking" => $picking,
                "scanning" => $scanning,
                "selesaiLoading" => $selesaiLoading,
                "selesaiDspb" => $selesaiDspb,
                "lblMonitoring"=> $lblMonitoring,
                "list_data" => $dt 
            ];  
        } catch (\Exception $e) {
            dd($e);
            // Handle the exception
            return (object)['errors' =>true,'messages'=> $e->getMessage()];
        }
    }

    
    public function initial_detail($tanggal = null,$zona = null,$report_zona = null, $flag_detail = null,$param = null,$mode_omi = null){
        
        $dateTrans = $tanggal?date("Y-m-d",strtotime($tanggal)):'';
        $debug_condition = true;
        $command_for_debug = $debug_condition?"-- ":'';
        $debug = !$debug_condition?"-- ":'';
        $data_fmkcab = '';

        // Build the query
        $query = "
            SELECT COALESCE(MAX(CAST(hpbi_tgltransaksi AS DATE) - CAST(hpbi_tglpb AS DATE) + 5), 7) AS datediff
            FROM tbtr_header_pbidm
            JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
            ".$command_for_debug." WHERE hpbi_tgltransaksi::date = '$dateTrans'
        ";

        // Execute the query
        $tmpDt = $this->DB_PGSQL->select($query);

        // Check if the query returned results and set the datediff
        if (!empty($tmpDt)) {
            $datediff = $tmpDt[0]->datediff;
        } else {
            $datediff = 7;
        }

        if (!$report_zona) { // chck1
           

            if (!$flag_detail) {
                $query_1 = " SELECT ROW_NUMBER() OVER() no , DTL.* FROM ( 
                                SELECT 
                                hpbi_kodetoko toko,
                                hpbi_nopb nopb,
                                to_char(to_date(hpbi_tglpb, 'YYYYMMdd'),'dd-MM-YYYY') tglpb, 
                                hPBI_NOPICKING nopick, HPBI_NOSJ nosj, HPBI_GATE gate,
                                to_char(HPBI_ITEMPB,'99,999,999') itempb,
                                to_char(item,'99,999,999') itemvalid,
                                to_char(rupiah ,'9,999,999,999') rupiah ";
                if ($param == "2" || $param == "3") {
                    if ($param == "2") {
                        $query_1 .=  ",(case when total > 0  then round((picking/total ) * 100) else 100 end) '%'
                                        ,picking itmpick , total ttlpick";
                    } else {
                        $query_1 .=    ",(case when pickavail > 0  then round((scanning/pickavail ) * 100) else 100 end) '%'
                                        ,scanning validscan ,pickavail ttlscan";
                    }
                    
                }

                $query_1 .= "FROM tbtr_header_pbidm, TBMASTER_TOKOIGR,  
                                (SELECT  SUM(coalesce(PBO_TTLNILAI,0) + coalesce(PBO_TTLPPN,0)) rupiah,count(1) item,   
                                pbo_nopb, pbo_tglpb, pbo_kodeomi, pbo_nopicking, pbo_nosj 
                                FROM TBMASTER_PBOMI 
                                WHERE PBO_TGLPB >= '$tanggal'::date - coalesce($datediff,7) ";

                if ($param == "6") {
                    $query_1 .=   "AND PBO_NOKOLI IS NOT NULL ";
                } 

                $query_1 .=   "  AND NOT EXISTS ( 
                                        SELECT PMV_PLUIGR 
                                        FROM PLU_MATERAI_VOUCHER 
                                        WHERE PMV_PLUIGR Like SubStr(PBO_PLUIGR,1,6)||'%' 
                                    ) 
                                GROUP BY  PBO_NOPB, PBO_TGLPB,PBO_KODEOMI, PBO_NOPICKING, PBO_NOSJ) t ";

            
                if ($param == "2" || $param == "3") {
                    $query_1 .= ",(SELECT count(1) total,sum(case when fmrcid >= '2' then 1 else 0 end ) picking, 
                        sum(case when fmrcid >= '2' and QTYR > 0  then 1 else 0 end ) pickavail, 
                        sum(case when fmrcid = '3' then 1 else 0 end ) scanning,  
                        tglupd , fmkcab , tglpb, fmndoc, nopicking, nosuratjalan 
                        FROM dpd_idm_ora 
                        WHERE tglupd = '$tanggal'";
                    if ($zona != 'all' && $zona) {
                        $query_1 .= "AND KODEZONA  = '$zona' ";
                    } 
                    $query_1 .="group by  tglupd , fmkcab , tglpb, fmndoc, nopicking, nosuratjalan) persentase ";
                }
                
                $query_1 .=  " ".$command_for_debug."WHERE hpbi_tgltransaksi = '$tanggal'::date 
                               ".$command_for_debug."AND PBO_NOPB = HPBI_NOPB AND PBO_TGLPB = TO_DATE(HPBI_TGLPB, 'YYYYMMdd') 
                               ".$debug."where PBO_NOPB = HPBI_NOPB AND PBO_TGLPB = TO_DATE(HPBI_TGLPB, 'YYYYMMdd') 
                                AND COALESCE(HPBI_NOPICKING, 0) = COALESCE(PBO_NOPICKING, HPBI_NOPICKING, 0) 
                                AND COALESCE(HPBI_NOSJ, 0) = COALESCE(PBO_NOSJ, HPBI_NOSJ, 0) ";
                $query_1 .= "AND PBO_KODEOMI = HPBI_KODETOKO  ";

                if ($param == "2" || $param == "3") {

                    $query_1 .= "AND fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB 
                                 AND HPBI_NOPICKING = COALESCE(nopicking, HPBI_NOPICKING) 
                                 AND HPBI_NOSJ = COALESCE(nosuratjalan, HPBI_NOSJ)";
                }

                if (!$param) {
                    $query_1 .= "AND EXISTS  
                                (SELECT 1 FROM   
                                (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan FROM dpd_idm_ora     
                                 ".$command_for_debug." WHERE tglupd = '$tanggal' 
                                ";
                                if ($param == 2) {
                                    $query_1 .= "AND (FMRCID = '1' or FMRCID = '2') ";
                                }elseif ($param == 3) {
                                    $query_1 .= "AND FMRCID = '3' ";
                                }
                                if ($zona != 'all' && $zona) {
                                    $query_1 .= "AND KODEZONA  = '$zona' ";
                                } 
                    $query_1 .= " )q WHERE fmndoc = hpbi_nopb AND  tglpb = hpbi_tglpb  
                                 AND COALESCE(nopicking, HPBI_NOPICKING) = hpbi_nopicking 
                                 AND COALESCE(nosuratjalan, HPBI_NOSJ) = hpbi_nosj 
                                 AND fmkcab = hpbi_kodetoko  ) 
                                AND HPBI_FLAG <> '5' ";
                }elseif ($param == 1) {
                    $query_1 .= "AND HPBI_FLAG  is not null ";
                }elseif ($param == 5) {
                    $query_1 .= "AND HPBI_FLAG = '4'";
                }elseif ($param == 6) {
                    $query_1 .= "AND HPBI_FLAG = '5'";
                }

                $query_1 .= "  AND PBO_KODEOMI = TKO_KODEOMI
                               AND HPBI_KODETOKO = TKO_KODEOMI ";
                if ($mode_omi) {
                    $query_1 .=  "  AND TKO_KODESBU = 'O' ";
                } else {
                    $query_1 .=  "  AND TKO_KODESBU = 'I' ";
                }
                $query_1 .= "ORDER BY COALESCE(HPBI_NOPICKING, HPBI_NOSJ)
                        ".$command_for_debug."limit 1000
                        ".$debug."limit 100
                ) DTL ";
               


            } else {
                if ($param == '4') {
                    $query_1 = " SELECT kodezona,  dcp,  fmkcab,  fmndoc,  tglpb,  NOPICK,   
                                        NOSJ,  CASE WHEN dca_flag = '3'  THEN 1  ELSE 0  END tutupkoli   
                                 FROM (SELECT   kodezona,  MAX(grak) dcp, fmkcab,  fmndoc, tglpb,   
                                        nopicking nopick, nosuratjalan nosj FROM (SELECT DISTINCT fmkcab, fmndoc, tglpb, grak,   
                                        kodezona, nopicking,  nosuratjalan FROM dpd_idm_ora   
                                        ".$command_for_debug." WHERE tglupd =  '$tanggal' 
                                        ) q,   
                                        tbtr_header_pbidm   
                                        WHERE hpbi_kodetoko = fmkcab   
                                        AND hpbi_nopb = fmndoc   
                                        AND hpbi_tglpb = tglpb   
                                        AND hpbi_nopicking = nopicking   
                                        AND hpbi_nosj = nosuratjalan   
                                        AND HPBI_FLAG <> '5' 
                                         ".$command_for_debug." AND hpbi_tgltransaksi = '$tanggal'::date
                                        GROUP BY kodezona, fmkcab, fmndoc, tglpb, nopicking, nosuratjalan 
                                        ) hdr   
                                LEFT JOIN dcp_antrian   
                                ON fmkcab = dca_toko AND fmndoc = dca_nopb AND tglpb = dca_tglpb   
                                AND nopick = dca_nopicking AND nosj = dca_nosj   
                                ".$command_for_debug."AND dcp = dca_grouprak and fmkcab =  '".$data_fmkcab."' 
                                order by kodezona ";

                }
            }
            


        } else {
           

            if ($param != "2" && $param != "3") {
                //Untuk monitoring Zona
                return ['messagee'=>"Hanya Untuk Monitor Picking Dan Scanning! (".date('d-m-Y').")"];
            } else {
                $query_1 = "SELECT   zon_kode, coalesce(jmlhtoko, 0) toko, coalesce(totaltoko, 0) total_toko 
                                FROM (SELECT   kodezona, count(b.fmkcab) jmlhtoko   
                                FROM tbtr_header_pbidm, 
                                (SELECT fmkcab, tglpb, fmndoc, kodezona, nopicking, nosuratjalan 
                                FROM dpd_idm_ora 
                                WHERE tglupd = '$tanggal' ";
                if ($param == 2) {
                   $query_1 .= "AND fmrcid is not null";
                } elseif ($param == 3) {
                   $query_1 .= "AND fmrcid  = '3'";
                }
                if ($mode_omi) {
                   $query_1 .= "AND coalesce(FMKSBU,'X') = 'O'";
                } else {
                   $query_1 .= "AND coalesce(FMKSBU,'X') = 'I'";
                }
                
                $query_1 .= "GROUP BY tglupd, fmkcab, tglpb, fmndoc, kodezona, nopicking, nosuratjalan) b  
                                WHERE hpbi_tgltransaksi = '$tanggal'::date 
                                AND b.fmkcab = hpbi_kodetoko 
                                AND b.tglpb = hpbi_tglpb 
                                AND b.fmndoc = hpbi_nopb 
                                AND b.nopicking = hpbi_nopicking 
                                AND b.nosuratjalan = hpbi_nosj 
                                GROUP BY kodezona) dtl, 
                                    (SELECT   kodezona,   count(fmkcab) totaltoko  
                                    FROM tbtr_header_pbidm, 
                                        (SELECT   fmkcab, tglpb, fmndoc, kodezona, nopicking, nosuratjalan  
                                        FROM dpd_idm_ora 
                                        WHERE tglupd = '$tanggal' ";  
                                        if ($mode_omi) {
                                            $query_1 .= "AND coalesce(FMKSBU,'X') = 'O'";
                                         } else {
                                            $query_1 .= "AND coalesce(FMKSBU,'X') = 'I'";
                                         }
                $query_1 .= "           GROUP BY tglupd, fmkcab, tglpb, fmndoc, kodezona, nopicking, nosuratjalan) c  
                                    WHERE hpbi_tgltransaksi = '$tanggal'
                                    AND c.fmkcab = hpbi_kodetoko 
                                    AND c.tglpb = hpbi_tglpb 
                                    AND c.fmndoc = hpbi_nopb 
                                    AND c.nopicking = hpbi_nopicking 
                                    AND c.nosuratjalan = hpbi_nosj 
                                    GROUP BY kodezona 
                                    ) dtl2 
                            LEFT JOIN 
                            (SELECT DISTINCT zon_kode 
                            FROM zona_idm 
                            ) hdr 
                            ON hdr.zon_kode = dtl.kodezona 
                            AND hdr.zon_kode = dtl2.kodezona "; 
                if ($zona != 'all' && $zona) {
                    $query_1 .= "AND hdr.zon_kode  = '$zona' ";
                }  
                $query_1 .= "ORDER BY zon_kode";
            }
            
            
        }
        
        $data = $this->DB_PGSQL->select ($query_1);

        return $data;
        

    }

    public function csv_rekon(Request $request)
    {

        $tanggal = date('Y-m-d',strtotime($request->tanggal));
        $array_csv = [];
        $header = [
            "KODEGUDANG",
            "NAMAFILE",
            "KODETOKO",
            "TANGGAL",
        ];
 
        $data_csv =  $this->DB_PGSQL
                          ->table("tbhistory_dspb")
                          ->selectRaw("
                            KODEIGR AS KODEGUDANG, NAMAFILE, KODETOKO, CREATEDT AS TANGGAL
                          ")
                          ->whereRaw("CREATEDT = '$tanggal'::date")
                          ->get();
        foreach ($data_csv as $key => $value) {
            $array_csv[] = [
                "KODEGUDANG"=> $value->kodegudang,
                "NAMAFILE"=> $value->namafile,
                "KODETOKO"=> $value->kodetoko,
                "TANGGAL"=> $value->tanggal,
            ];
        }
        
        
        // return response()->json(['errors'=>true,'messages'=>'Berhasil','download'=>$this->download_csv(null,$array_csv,'REKON_AMS_'.date('dmY',strtotime($tanggal)).'.csv','csv_monitoring/',$header)],200);

        return $this->download_csv(null,$array_csv,'REKON_AMS_'.date('dmY',strtotime($tanggal)).'.csv','csv_monitoring/',$header);
        
    }

    public function cetak_list_paket_pengiriman_idm(Request $request){
        $tanggal = $request->tanggal?date('Y-m-d',strtotime($request->tanggal)):date('Y-m-d');
        
        $header_cetak_custom = false;
        $sql = "
        SELECT
        hpbi_nosj AS no_pengiriman,
        hpbi_kodetoko AS kode_toko,
        hpbi_nopb AS no_pb,
        TO_CHAR(TO_DATE(hpbi_tglpb, 'YYYYMMDD'),'DD-MM-YYYY') AS tgl_pb,
        ikl_idtransaksi AS no_dspb,
        jml_container,
        jml_bronjong,
        jml_kardus,
        ROW_NUMBER() OVER (
            PARTITION BY hpbi_kodetoko, hpbi_nopb
            ORDER BY hpbi_kodetoko, hpbi_nopb, hpbi_nopicking
        ) AS mobil_ke
    FROM tbtr_header_pbidm
    JOIN (
        SELECT
            ikl_kodeidm,
            ikl_nopb,
            ikl_nopick,
            ikl_nosj,
            ikl_tglpb,
            ikl_idtransaksi,
            SUM(CASE WHEN COALESCE(ikl_nokoli, '-') LIKE '01%' AND COALESCE(ikl_kardus,'N') = 'N' THEN 1 ELSE 0 END) AS jml_container,
            SUM(CASE WHEN (COALESCE(ikl_nokoli, '-') LIKE '02%' OR COALESCE(ikl_nokoli, '-') LIKE '08%') AND COALESCE(ikl_kardus,'N') = 'N' THEN 1 ELSE 0 END) AS jml_bronjong,
            SUM(CASE WHEN COALESCE(ikl_kardus,'Y') = 'Y' THEN 1 ELSE 0 END) AS jml_kardus
        FROM tbtr_idmkoli
        WHERE ikl_idtransaksi IS NOT NULL
        AND COALESCE(ikl_recordid,'0') IN ('1','2')
        AND SUBSTR(ikl_nokoli,1,2) IN ('01','02','08','09')
        GROUP BY ikl_kodeidm, ikl_nopb, ikl_nopick, ikl_nosj, ikl_tglpb, ikl_idtransaksi
    ) idmkoli
    ON ikl_kodeidm = hpbi_kodetoko
    AND ikl_nopb = hpbi_nopb
    AND ikl_tglpb = hpbi_tglpb
    AND ikl_nopick = hpbi_nopicking
    AND ikl_nosj = hpbi_nosj
    WHERE EXISTS (
        SELECT no_dspb
        FROM temp_delivery_idm
        WHERE no_pengiriman::INT = hpbi_nosj
        AND kode_toko = hpbi_kodetoko
        AND no_pb = hpbi_nopb
        AND TO_DATE(tgl_pb,'DD-MM-YYYY') = TO_DATE(hpbi_tglpb, 'YYYYMMDD')
    )
    ORDER BY hpbi_nosj ASC, hpbi_nopicking ASC
    limit 10
        ";

        $perusahaan = $this->DB_PGSQL
                           ->table("tbmaster_perusahaan")
                           ->whereRaw("prs_kodeigr = '".$this->kodeigr."'")
                           ->get();
        $perusahaan = $perusahaan[0];
        $data =  $this->DB_PGSQL
                      ->select($sql);
        $temp_data = [];
        foreach ($data as $key => $value) {
            $temp_data[$value->no_pengiriman][] = $value;
        }
        $data = $temp_data;
        $pdf = PDF::loadview('menu.monitoring.report.list_paket_pengiriman_idm', compact('data','tanggal','perusahaan','header_cetak_custom'));
        $pdf->output();
        $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
        $canvas = $dompdf->get_canvas();

        // //make page text in header and right side

        $canvas->page_text(615, 63, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 7, array(0, 0, 0));
    
    
        return $pdf->stream('report_rincian_nomor_referensi_materai '.date('Y-m-d'));
    }
    public function cetak_list_kubikasi_pb_idm(Request $request){
        $tanggal = $request->tanggal?date('Y-m-d',strtotime($request->tanggal)):date('Y-m-d');
        $header_cetak_custom = FALSE;
        $sql = "
            SELECT 
                kodetoko,
                namatoko,
                nopb,
                nopick,
                nosj,
                rupiah,
                cntr_sendjalur,
                brjg_sendjalur,
                ROUND(((COALESCE(cntr_sendjalur, 0) * (SELECT con_volume FROM container_idm WHERE con_jenis = 'CONTAINER' LIMIT 1))
                + (COALESCE(brjg_sendjalur, 0) * (SELECT con_volume FROM container_idm WHERE con_jenis = 'BRONJONG' LIMIT 1))) / 1000000, 4) kbk_sendjalur,
                cntr_scan,
                brjg_scan,
                ROUND(((COALESCE(cntr_scan, 0) * (SELECT con_volume FROM container_idm WHERE con_jenis = 'CONTAINER' LIMIT 1))
                + (COALESCE(brjg_scan, 0) * (SELECT con_volume FROM container_idm WHERE con_jenis = 'BRONJONG' LIMIT 1))) / 1000000, 4) kbk_scan,
                cntr_dspb,
                krds_dspb,
                brjg_dspb,
                ROUND((((COALESCE(cntr_dspb, 0) + COALESCE(krds_dspb, 0)) * (SELECT con_volume FROM container_idm WHERE con_jenis = 'CONTAINER' LIMIT 1))
                + (COALESCE(brjg_dspb, 0) * (SELECT con_volume FROM container_idm WHERE con_jenis = 'BRONJONG' LIMIT 1))) / 1000000, 4) kbk_dspb
            FROM (
                SELECT
                    hpbi_kodetoko kodetoko,
                    tko_namaomi namatoko,
                    hpbi_nopb nopb,
                    hpbi_nopicking nopick,
                    hpbi_nosj nosj,
                    ROUND(hpbi_rphvalid) rupiah,
                    COALESCE(hpbi_jumlahcontainer,0) cntr_sendjalur,
                    COALESCE(hpbi_jumlahbronjong,0) brjg_sendjalur,
                    CASE WHEN COALESCE(ikl_registerrealisasi,'-') <> '0'
                        THEN COALESCE(tot_grak,0) ELSE COALESCE(done_grak,0)
                    END || '/' || COALESCE(tot_grak,0) status_scan,
                    CASE WHEN COALESCE(ikl_registerrealisasi,'-') <> '-' OR COALESCE(done_grak,0) >= COALESCE(tot_grak,0)
                        THEN COALESCE(cntr_scan,0) ELSE 0 END cntr_scan,
                    CASE WHEN COALESCE(ikl_registerrealisasi,'-') <> '-' OR COALESCE(done_grak,0) >= COALESCE(tot_grak,0)
                        THEN COALESCE(brjg_scan,0) ELSE 0 END brjg_scan,
                    COALESCE(ikl_registerrealisasi,'-') no_dspb,
                    CASE WHEN COALESCE(ikl_registerrealisasi,'-') <> '-'
                        THEN COALESCE(cntr_dspb,0) ELSE 0 END cntr_dspb,
                    CASE WHEN COALESCE(ikl_registerrealisasi,'-') <> '-'
                        THEN COALESCE(krds_dspb,0) ELSE 0 END krds_dspb,
                    CASE WHEN COALESCE(ikl_registerrealisasi,'-') <> '-'
                        THEN COALESCE(brjg_dspb,0) ELSE 0 END brjg_dspb
                FROM tbtr_header_pbidm
                JOIN tbmaster_tokoigr
                ON tko_kodeomi = hpbi_kodetoko
                LEFT JOIN (
                    SELECT fmndoc, nopicking, tglpb, fmkcab, nosuratjalan, COUNT(DISTINCT grak) tot_grak, SUM(CASE WHEN COALESCE(dca_flag, '0') = '3' THEN 1 ELSE 0 END) done_grak
                    FROM (
                        SELECT fmndoc, fmkcab, tglpb, nopicking, nosuratjalan, kodezona, MAX(grak) grak
                        FROM dpd_idm_ora
                        GROUP BY fmndoc, fmkcab, tglpb, nopicking, nosuratjalan, kodezona
                    ) picking
                    LEFT JOIN dcp_antrian
                    ON dca_grouprak = grak
                    AND dca_toko = fmkcab
                    AND dca_nopicking = nopicking
                    AND dca_nosj = nosuratjalan
                    AND dca_tglpb = tglpb
                    GROUP BY fmndoc, nopicking, tglpb, fmkcab, nosuratjalan
                ) t
                ON hpbi_kodetoko = fmkcab
                AND hpbi_nopb = fmndoc
                AND hpbi_tglpb = tglpb
                AND hpbi_nopicking = nopicking
                LEFT JOIN (
                    SELECT
                        ikl_kodeidm,
                        ikl_nopb,
                        ikl_tglpb,
                        ikl_registerrealisasi,
                        SUM(CASE WHEN SUBSTR(ikl_nokoli,1,2) IN ('01','09') THEN 1 ELSE 0 END) cntr_scan,
                        SUM(CASE WHEN SUBSTR(ikl_nokoli,1,2) IN ('02','08') THEN 1 ELSE 0 END) brjg_scan,
                        SUM(CASE WHEN SUBSTR(ikl_nokoli,1,2) IN ('01','09') AND COALESCE(ikl_kardus, 'N') <> 'Y' THEN 1 ELSE 0 END) cntr_dspb,
                        SUM(CASE WHEN COALESCE(ikl_kardus, 'N') = 'Y' THEN 1 ELSE 0 END) krds_dspb,
                        SUM(CASE WHEN SUBSTR(ikl_nokoli,1,2) IN ('02','08') AND COALESCE(ikl_kardus, 'N') <> 'Y' THEN 1 ELSE 0 END) brjg_dspb
                    FROM tbtr_idmkoli
                    WHERE SUBSTR(ikl_nokoli,1,2) IN ('01', '02', '08', '09')
                    GROUP BY ikl_kodeidm, ikl_nopb, ikl_tglpb, ikl_registerrealisasi
                ) datakoli
                ON hpbi_kodetoko = ikl_kodeidm
                AND hpbi_nopb = ikl_nopb
                AND hpbi_tglpb = ikl_tglpb
                WHERE hpbi_flag IS NOT NULL
                AND hpbi_tgltransaksi::date = '$tanggal'::date
            ) p
            ORDER BY nosj, nopick
        ";

        $perusahaan = $this->DB_PGSQL
                           ->table("tbmaster_perusahaan")
                           ->whereRaw("prs_kodeigr = '".$this->kodeigr."'")
                           ->get();
        $perusahaan = $perusahaan[0];
        $data =  $this->DB_PGSQL
                      ->select($sql);

        $pdf = PDF::loadview('menu.monitoring.report.list_kubikasi_pb_idm', compact('data','tanggal','perusahaan','header_cetak_custom'));
        $pdf->output();
        $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
        $canvas = $dompdf->get_canvas();

        // //make page text in header and right side

        $canvas->page_text(615, 63, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 7, array(0, 0, 0));
    
    
        return $pdf->stream('report_rincian_nomor_referensi_materai '.date('Y-m-d'));
    }

    public function list_paket_pengiriman_idm(Request $request){

        $tanggal = $request->tanggal?date('Y-m-d',strtotime($request->tanggal)):date('Y-m-d');
        $sql = "
        SELECT
                hpbi_nosj AS no_pengiriman,
                hpbi_kodetoko AS kode_toko,
                hpbi_nopb AS no_pb,
                TO_CHAR(TO_DATE(hpbi_tglpb, 'YYYYMMDD'),'DD-MM-YYYY') AS tgl_pb,
                ikl_idtransaksi AS no_dspb,
                TO_CHAR(hpbi_tglpengiriman,'DD-MM-YYYY') AS tgl_pengiriman,
                CASE WHEN hpbi_tglpengiriman IS NULL THEN 0 ELSE 1 END AS kirim
            FROM tbtr_header_pbidm
            JOIN (
                SELECT DISTINCT
                    ikl_kodeidm,
                    ikl_nopb,
                    ikl_tglpb,
                    ikl_idtransaksi
                FROM tbtr_idmkoli
                WHERE ikl_idtransaksi IS NOT NULL
                AND COALESCE(ikl_recordid,'0') IN ('1','2')
                AND SUBSTR(ikl_nokoli,1,2) IN ('01','02','08','09')
            ) idmkoli
            ON ikl_kodeidm = hpbi_kodetoko
            AND ikl_nopb = hpbi_nopb
            AND ikl_tglpb = hpbi_tglpb
            WHERE COALESCE(hpbi_flag,'0') = '5'
            AND hpbi_tgltransaksi::date = '$tanggal'::date
            AND EXISTS (
                SELECT tko_kodeomi
                FROM tbmaster_tokoigr
                WHERE tko_kodesbu = 'I'
                AND tko_kodeomi = hpbi_kodetoko
            )
            ORDER BY hpbi_nosj ASC, hpbi_nopicking ASC
        ";

        $data =  $this->DB_PGSQL
                      ->select($sql);

        return response()->json(['errors'=>true,'messages'=>'Berhasil','data'=> $data],200); 

    } 
}
