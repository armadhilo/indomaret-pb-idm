<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MonitoringController extends Controller
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
        return view("menu.monitoring.index");
    }

    public function monitoring_load(){
        $data_card = $this->initial();
        $data_zona = $this->load_zona();
    }

    public function load_zona(){

        $this->initial();
        dd("gk masuk");
        $data = $this->DB_PGSQL
                     ->table("zona_idm")
                     ->select("zon_kode")
                     ->distinct()
                     ->orderBy("zon_kode","asc")
                     ->get();
        return $data;
    }

    public function initial($param = null,$report_zona = false, $flag_detail = false, $tanggal = null,$zona = null,$mode_omi = null){
        $dtTrans  = $tanggal;
        $sendJalur = 0;
        $picking = 0;
        $scanning = 0;
        $siapDspb = 0;
        $selesaiDspb = 0;
        $jmlhPb = 0;
        $selesaiLoading = 0;
        $lblSDspb = 0;
        $lblMonitoring = [];

        $jmlhPb_condition =  " TKO_KODESBU = 'I'";
        $sendJalur_condition =  " TKO_KODESBU = 'I'";
        $packing_condition ="";
        $packing_condition.= " TKO_KODESBU = 'I'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND (FMRCID = '1' OR FMRCID = '2')) q  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj) ";
        $scanning_condition ="";
        $scanning_condition.= " TKO_KODESBU = 'O'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND FMRCID = '3') t  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB) ";
        $selesaiLoading_condition =  " TKO_KODESBU = 'I'";
        $selesaiDspb_condition =  " TKO_KODESBU = 'I'";
        $whereOMI = "AND tko_kodesbu = 'I'";
        $whereDate1 = $tanggal?"'$tanggal'":"";
        $whereDate2 = $tanggal?"hpbi_tgltransaksi =".$tanggal."::date":"";
        // if ($modeProgram == "OMI") {
        //    $jmlhPb_condition =  " TKO_KODESBU = 'O'";
        //    $sendJalur_condition =  " TKO_KODESBU = 'O'";
        //    $packing_condition.= " TKO_KODESBU = 'O'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND (FMRCID = '1' OR FMRCID = '2')) q  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB) ";
        //    $scanning_condition.= " TKO_KODESBU = 'O'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND FMRCID = '3') t  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB) ";
        //    $selesaiLoading_condition =  " TKO_KODESBU = 'O'";
        //    $selesaiDspb_condition =  " TKO_KODESBU = 'O'";
        //    $whereOMI = "AND tko_kodesbu = 'O'";
        // } else {
        //    $jmlhPb_condition =  " TKO_KODESBU = 'I'";
        //    $sendJalur_condition =  " TKO_KODESBU = 'I'";
        //    $packing_condition.= " TKO_KODESBU = 'I'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND (FMRCID = '1' OR FMRCID = '2')) q  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj) ";
        //    $scanning_condition.=  " TKO_KODESBU = 'I'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND FMRCID = '3') t  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj)";
        //    $selesaiLoading_condition =  " TKO_KODESBU = 'I'";
        //    $selesaiDspb_condition =  " TKO_KODESBU = 'I'";
        //    $whereOMI = "AND tko_kodesbu = 'I'";
        // }
        //

        //jmlhPb
        $jmlhPb = $this->DB_PGSQL
                       ->table("tbtr_header_pbidm")
                       ->join("tbmaster_tokoigr",function($join){
                            $join->on("hpbi_kodetoko","=","tko_kodeomi");
                        })
                       ->selectRaw("
                          COUNT(1) 
                       ");
        if($dtTrans){
        $jmlhPb =      $jmlhPb                       
                       ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d", strtotime($dtTrans))."'::date");
                    }
        $jmlhPb =      $jmlhPb
                       ->whereRaw($jmlhPb_condition)
                       ->get();


        //sendJalur                       
        $sendJalur =   $this->DB_PGSQL
                            ->table("tbtr_header_pbidm")
                            ->join("tbmaster_tokoigr",function($join){
                                $join->on("hpbi_kodetoko","=","tko_kodeomi");
                            })
                            ->selectRaw("
                                COUNT(1) 
                            ") ;
        if($dtTrans){
        $sendJalur =      $sendJalur                            
                            ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d", strtotime($dtTrans))."'::date");
                        }
        $sendJalur =      $sendJalur
                            ->whereRaw("HPBI_FLAG IS NOT NULL")
                            ->whereRaw($sendJalur_condition)
                            ->get();



        //picking                            
        $picking =   $this->DB_PGSQL
                            ->table("tbtr_header_pbidm")
                            ->join("tbmaster_tokoigr",function($join){
                                $join->on("hpbi_kodetoko","=","tko_kodeomi");
                            })
                            ->selectRaw("
                                COUNT(1) 
                            ") ;
        if($dtTrans){
        $picking =      $picking                            
                            ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d", strtotime($dtTrans))."'::date");
                        }
        $picking =      $picking
                            ->whereRaw("HPBI_FLAG <> '5'")
                            ->whereRaw($packing_condition)
                            ->get();



        //scanning                            
        $scanning =   $this->DB_PGSQL
                            ->table("tbtr_header_pbidm")
                            ->join("tbmaster_tokoigr",function($join){
                                $join->on("hpbi_kodetoko","=","tko_kodeomi");
                            })
                            ->selectRaw("
                                COUNT(1) 
                            ") ;
        if($dtTrans){
        $scanning =      $scanning                            
                            ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d", strtotime($dtTrans))."'::date");
                        }
        $scanning =      $scanning
                            ->whereRaw("hpbi_flag <> '5'")
                            ->whereRaw($scanning_condition)
                            ->get();
                            


        //selesaiLoading                            
        $selesaiLoading =   $this->DB_PGSQL
                            ->table("tbtr_header_pbidm")
                            ->join("tbmaster_tokoigr",function($join){
                                $join->on("hpbi_kodetoko","=","tko_kodeomi");
                            })
                            ->selectRaw("
                                COUNT(1) 
                            ") ;
        if($dtTrans){
        $selesaiLoading =      $selesaiLoading                            
                            ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d", strtotime($dtTrans))."'::date");
                        }
        $selesaiLoading =      $selesaiLoading
                            ->whereRaw("hpbi_flag = '4'")
                            ->whereRaw($selesaiLoading_condition)
                            ->get();
                            


        //selesaiDspb                            
        $selesaiDspb =   $this->DB_PGSQL
                            ->table("tbtr_header_pbidm")
                            ->join("tbmaster_tokoigr",function($join){
                                $join->on("hpbi_kodetoko","=","tko_kodeomi");
                            })
                            ->selectRaw("
                                COUNT(1) 
                            ") ;
        if($dtTrans){
        $selesaiDspb =      $selesaiDspb                             
                            ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d", strtotime($dtTrans))."'::date");
                        }
        $selesaiDspb =      $selesaiDspb
                            ->whereRaw("hpbi_flag = '5'")
                            ->whereRaw($selesaiDspb_condition)
                            ->get();
                            



                                     
        $jmlhPb = $jmlhPb[0]->count;
        $sendJalur = $sendJalur[0]->count;
        $picking = $picking[0]->count;
        $scanning = $scanning[0]->count;
        $selesaiLoading = $selesaiLoading[0]->count;
        $selesaiDspb = $selesaiDspb[0]->count;
        $query_dt =  "  SELECT ROW_NUMBER() OVER() no, dtl.* 
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
                            ".$whereDate1."
                                ) p, tbtr_header_pbidm, tbmaster_tokoigr  
                            WHERE hpbi_kodetoko = tko_kodeomi 
                                AND hpbi_kodetoko = fmkcab 
                                AND hpbi_nopb = fmndoc 
                                AND hpbi_tglpb = tglpb 
                                AND hpbi_nopicking = nopicking 
                                AND hpbi_nosj = nosuratjalan 
                            ".$whereOMI."
                            ".$whereDate2."
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
                        ) dtl
                        ";


         if ($param == "4" && !$report_zona) {
            //  $dgv->dataSource = $dt2;
            if ($report_zona) {
                $dt2 =  $this->DB_PGSQL->select($query_dt);
            } else {
                $dt = $this->initial_detail($dtTrans,$zona,$report_zona, $flag_detail,$param ,$mode_omi );
            }
            
         } else {
            //  $dgv->dataSource = $dt;
             $dt = $this->initial_detail($dtTrans,$zona,$report_zona = null, $flag_detail,$param,$mode_omi);
         }

         

        foreach ($dt2 as $key => $row) {
            $siapDspb++;
        }
        $lblSDspb = $siapDspb;

        switch ($param) {
            case "":
                $lblMonitoring = count($dt->rows) . " PB Terupload";
                break;
            case "1":
                $lblMonitoring = count($dt->rows) . " Sudah Send Jalur";
                break;
            case "2":
                if ($report_zona) {
                    $lblMonitoring = count($dt->rows) . " ZONA Sedang Picking";
                } else {
                    $lblMonitoring = count($dt->rows) . " Sedang Picking";
                }
                break;
            case "3":
                if ($report_zona) {
                    $lblMonitoring = count($dt->rows) . " ZONA Sedang Scanning";
                } else {
                    $lblMonitoring = count($dt->rows) . " Sedang Scanning";
                }
                break;
            case "4":
                $lblMonitoring = count($dt->rows) . " Siap DSPB";
                break;
            case "5":
                $lblMonitoring = count($dt->rows) . " Selesai LOADING";
                break;
            case "6":
                $lblMonitoring = count($dt->rows) . " Selesai DSPB";
                break;
            default:
                // Handle any other cases if needed
                break;
        }
        dd($lblMonitoring);
        
        // dd($dt2,$query_dt,$whereOMI,$whereDate1,$whereDate2);
     
        //  $dt2 = QueryOra($sql, 10000); // QueryOra is a placeholder for your query execution method
        //  $dt = QueryOra(initial_detail($param, $flagDetail == true ? true : false)->toString(), 10000); // Assuming initial_detail() returns a query string
 
        //  $n = 0;
        //  foreach ($dt2 as $row) {
        //      if ($row['total'] == $row['selesai'] && $row['total'] > 0) {
        //          $n++;
        //      }
        //  }
        //  $siapDspb = $n;
 
        //  $lblSDspb = $_siapDspb;
 
        //  if ($param == "4" && !$chck1) {
        //      $dgv->dataSource = $dt2;
        //  } else {
        //      $dgv->dataSource = $dt;
        //  }
        //  $dgv->refresh();
 
        //  switch ($_jenis) {
        //      case "":
        //          $lblMonitoring = count($dt) . " PB Terupload ";
        //          break;
        //      case "1":
        //          $lblMonitoring = count($dt) . " Sudah Send Jalur ";
        //          break;
        //      case "2":
        //          $lblMonitoring = ($chck1 ? count($dt) . " ZONA Sedang Picking " : count($dt) . " Sedang Picking ");
        //          break;
        //      case "3":
        //          $lblMonitoring = ($chck1 ? count($dt) . " ZONA Sedang Scanning " : count($dt) . " Sedang Scanning ");
        //          break;
        //      case "4":
        //          $lblMonitoring = count($dt) . " Siap DSPB ";
        //          break;
        //      case "5":
        //          $lblMonitoring = count($dt) . " Selesai LOADING ";
        //          break;
        //      case "6":
        //          $lblMonitoring = count($dt) . " Selesai DSPB ";
        //          break;
        //      default:
        //          break;
        //  }
 
        //  if (DB::connection()->getPdo() != null) {
        //      DB::connection()->getPdo()->close();
        //  }




    }

    public function initial_detail($tanggal = null,$zona = null,$report_zona = null, $flag_detail = null,$param = null,$mode_omi = null){
        $tmpDt =  $this->DB_PGSQL
                       ->table("tbtr_header_pbidm")
                       ->join("tbmaster_tokoigr",function($join){
                            $join->on("hpbi_kodetoko","=","tko_kodeomi");
                        })
                       ->selectRaw("COALESCE(MAX(CAST(hpbi_tgltransaksi as DATE) - CAST(hpbi_tglpb as DATE)+5),7) datediff");
        if ($tanggal) {
            $tmpDt =   $tmpDt->whereRaw("hpbi_tgltransaksi = '$tanggal?$tanggal:".date('Y-m-d')."'::date");
            
        }
        $tmpDt =   $tmpDt->get();

        $datediff = $tmpDt ? $tmpDt[0]->datediff : 7;
        

        if (!$report_zona) { // chck1
           

            if (!$flag_detail) {
                $query_1 = " SELECT ROW_NUMBER() OVER() no , DTL.* FROM ( 
                                SELECT hpbi_kodetoko toko, hpbi_nopb nopb, to_char(to_date(hpbi_tglpb, 'YYYYMMdd'),'dd-MM-YYYY') tglpb, 
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
                                WHERE PBO_TGLPB >= '$tanggal?$tanggal:".date('Y-m-d')."'::date - coalesce($datediff,7) ";

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
                
                $query_1 .=  "WHERE hpbi_tgltransaksi = '$tanggal?$tanggal:".date('Y-m-d')."'::date 
                                AND PBO_NOPB = HPBI_NOPB AND PBO_TGLPB = TO_DATE(HPBI_TGLPB, 'YYYYMMdd') 
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
                                WHERE tglupd = '$tanggal' ";
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
                if ($mode_omi == "OMI") {
                    $query_1 .=  "  AND TKO_KODESBU = 'O' ";
                } else {
                    $query_1 .=  "  AND TKO_KODESBU = 'I' ";
                }
                $query_1 .= "ORDER BY COALESCE(HPBI_NOPICKING, HPBI_NOSJ)) DTL ";
               


            } else {
                if ($param == '4') {
                    $query_1 = " SELECT kodezona,  dcp,  fmkcab,  fmndoc,  tglpb,  NOPICK,   
                                        NOSJ,  CASE WHEN dca_flag = '3'  THEN 1  ELSE 0  END tutupkoli   
                                 FROM (SELECT   kodezona,  MAX(grak) dcp, fmkcab,  fmndoc, tglpb,   
                                        nopicking nopick, nosuratjalan nosj FROM (SELECT DISTINCT fmkcab, fmndoc, tglpb, grak,   
                                        kodezona, nopicking,  nosuratjalan FROM dpd_idm_ora   
                                        WHERE tglupd =  '$tanggal' ) q,   
                                        tbtr_header_pbidm   
                                        WHERE hpbi_kodetoko = fmkcab   
                                        AND hpbi_nopb = fmndoc   
                                        AND hpbi_tglpb = tglpb   
                                        AND hpbi_nopicking = nopicking   
                                        AND hpbi_nosj = nosuratjalan   
                                        AND HPBI_FLAG <> '5' 
                                        AND hpbi_tgltransaksi = '$tanggal'::date
                                        GROUP BY kodezona, fmkcab, fmndoc, tglpb, nopicking, nosuratjalan 
                                        ) hdr   
                                LEFT JOIN dcp_antrian   
                                ON fmkcab = dca_toko AND fmndoc = dca_nopb AND tglpb = dca_tglpb   
                                AND nopick = dca_nopicking AND nosj = dca_nosj   
                                AND dcp = dca_grouprak and fmkcab =  '" & dgv.Item(1, dgv.CurrentCell.RowIndex).Value & "' 
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
                if ($mode_omi == "OMI") {
                   $query_1 .= "AND coalesce(FMKSBU,'X') = 'O'";
                } else {
                   $query_1 .= "AND coalesce(FMKSBU,'X') = 'I'";
                }
                
                $query_1 .= "GROUP BY tglupd, fmkcab, tglpb, fmndoc, kodezona, nopicking, nosuratjalan) b  
                                WHERE hpbi_tgltransaksi = '$tanggal?$tanggal:".date('Y-m-d')."'::date 
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
                                        if ($mode_omi == "OMI") {
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
}
