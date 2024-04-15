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

    public function initial($tanggal = null){
        $dtTrans  = $tanggal;
        $sendJalur = 0;
        $picking = 0;
        $scanning = 0;
        $siapDspb = 0;
        $selesaiDspb = 0;
        $jmlhPb = 0;
        $selesaiLoading = 0;

        $jmlhPb_condition =  " TKO_KODESBU = 'I'";
        $sendJalur_condition =  " TKO_KODESBU = 'I'";
        $packing_condition ="";
        $packing_condition.= " TKO_KODESBU = 'I'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND (FMRCID = '1' OR FMRCID = '2')) q  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj) ";
        $scanning_condition ="";
        $scanning_condition.= " TKO_KODESBU = 'O'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND FMRCID = '3') t  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB) ";
        $selesaiLoading_condition =  " TKO_KODESBU = 'I'";
        $selesaiDspb_condition =  " TKO_KODESBU = 'I'";
        // if ($modeProgram == "OMI") {
        //    $jmlhPb_condition =  " TKO_KODESBU = 'O'";
        //    $sendJalur_condition =  " TKO_KODESBU = 'O'";
        //    $packing_condition.= " TKO_KODESBU = 'O'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND (FMRCID = '1' OR FMRCID = '2')) q  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB) ";
        //    $scanning_condition.= " TKO_KODESBU = 'O'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND FMRCID = '3') t  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB) ";
        //    $selesaiLoading_condition =  " TKO_KODESBU = 'O'";
        //    $selesaiDspb_condition =  " TKO_KODESBU = 'O'";
        // } else {
        //    $jmlhPb_condition =  " TKO_KODESBU = 'I'";
        //    $sendJalur_condition =  " TKO_KODESBU = 'I'";
        //    $packing_condition.= " TKO_KODESBU = 'I'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND (FMRCID = '1' OR FMRCID = '2')) q  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj) ";
        //    $scanning_condition.=  " TKO_KODESBU = 'I'  AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan  FROM dpd_idm_ora  WHERE tglupd = '" .date("Y-m-d")."'  AND FMRCID = '3') t  WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj)";
        //    $selesaiLoading_condition =  " TKO_KODESBU = 'I'";
        //    $selesaiDspb_condition =  " TKO_KODESBU = 'I'";
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
                       ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d" strtotime($dtTrans))."'::date");
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
                            ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d" strtotime($dtTrans))."'::date");
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
                            ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d" strtotime($dtTrans))."'::date");
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
                            ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d" strtotime($dtTrans))."'::date");
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
                            ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d" strtotime($dtTrans))."'::date");
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
                            ->whereRaw("hpbi_tgltransaksi = '".date("Y-m-d" strtotime($dtTrans))."'::date");
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
        dd($picking);
        // $sql = "SELECT COUNT(1) ";
        // $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        // $sql .= "WHERE hpbi_tgltransaksi = '" .date("Y-m-d"). "' ";

       

        // $jmlhPb = DB::select(DB::raw($sql));

        // $sql = "SELECT COUNT(1) ";
        // $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        // $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        // $sql .= "AND HPBI_FLAG IS NOT NULL ";

        // if ($modeProgram == "OMI") {
        //     $sql .= "AND TKO_KODESBU = 'O' ";
        // } else {
        //     $sql .= "AND TKO_KODESBU = 'I' ";
        // }

        // $sendJalur = DB::select(DB::raw($sql));

        // $sql = "SELECT COUNT(1) ";
        // $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        // $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        // $sql .= "AND HPBI_FLAG <> '5' ";

        // if ($modeProgram == "OMI") {
        //     $sql .= "AND TKO_KODESBU = 'O' ";
        //     $sql .= "AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab ";
        //     $sql .= "FROM dpd_idm_ora ";
        //     $sql .= "WHERE tglupd = '" . Carbon::parse($dtTrans)->format('Ymd') . "' ";
        //     $sql .= "AND (FMRCID = '1' OR FMRCID = '2')) q ";
        //     $sql .= "WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB) ";
        // } else {
        //     $sql .= "AND TKO_KODESBU = 'I' ";
        //     $sql .= "AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan ";
        //     $sql .= "FROM dpd_idm_ora ";
        //     $sql .= "WHERE tglupd = '" . Carbon::parse($dtTrans)->format('Ymd') . "' ";
        //     $sql .= "AND (FMRCID = '1' OR FMRCID = '2')) q ";
        //     $sql .= "WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj) ";
        // }

        // $picking = DB::select(DB::raw($sql)); 





        // $sql = "SELECT COUNT(1) ";
        // $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        // $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        // $sql .= "AND HPBI_FLAG <> '5' ";

        // if ($modeProgram == "OMI") {
        //     $sql .= "AND TKO_KODESBU = 'O' ";
        //     $sql .= "AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab ";
        //     $sql .= "FROM dpd_idm_ora ";
        //     $sql .= "WHERE tglupd = '" . Carbon::parse($dtTrans)->format('Ymd') . "' ";
        //     $sql .= "AND FMRCID = '3') t ";
        //     $sql .= "WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB) ";
        // } else {
        //     $sql .= "AND TKO_KODESBU = 'I' ";
        //     $sql .= "AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan ";
        //     $sql .= "FROM dpd_idm_ora ";
        //     $sql .= "WHERE tglupd = '" . Carbon::parse($dtTrans)->format('Ymd') . "' ";
        //     $sql .= "AND FMRCID = '3') t ";
        //     $sql .= "WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj) ";
        // }

        // $scanning = DB::select(DB::raw($sql));

        // $sql = "SELECT COUNT(1) ";
        // $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        // $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        // $sql .= "AND HPBI_FLAG = '4' ";

        // if ($modeProgram == "OMI") {
        //     $sql .= "AND TKO_KODESBU = 'O' ";
        // } else {
        //     $sql .= "AND TKO_KODESBU = 'I' ";
        // }

        // $selesaiLoading = DB::select(DB::raw($sql));

        // $sql = "SELECT COUNT(1) ";
        // $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        // $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        // $sql .= "AND HPBI_FLAG = '5' ";

        // if ($modeProgram == "OMI") {
        //     $sql .= "AND TKO_KODESBU = 'O' ";
        // } else {
        //     $sql .= "AND TKO_KODESBU = 'I' ";
        // }

        // $selesaiDspb = DB::select(DB::raw($sql));

        // // Further logic here


        //  // Set values from previous calculations or queries
        //  $lblPB = $_jmlhPb;
        //  $lblSendJalur = $_sendJalur;
        //  $lblPicking = $_picking;
        //  $lblScanning = $_scanning;
        //  $lblLoading = $_selesaiLoading;
        //  $lblDspb = $_selesaiDspb;
 
        //  $sql = "";
        //  $sql .= "SELECT ROW_NUMBER() OVER() AS no, dtl.* ";
        //  $sql .= "FROM ( ";
        //  $sql .= "   SELECT ";
        //  $sql .= "     toko, ";
        //  $sql .= "     nopb, ";
        //  $sql .= "     tglpb, ";
        //  $sql .= "     nopick, ";
        //  $sql .= "     nosj, ";
        //  $sql .= "     total, ";
        //  $sql .= "     selesai ";
        //  $sql .= "   FROM ( ";
        //  $sql .= "     SELECT ";
        //  $sql .= "       fmkcab AS toko, ";
        //  $sql .= "       fmndoc AS nopb, ";
        //  $sql .= "       tglpb, ";
        //  $sql .= "       nopick, ";
        //  $sql .= " 	  nosj, ";
        //  $sql .= " 	  COUNT(1) AS total, ";
        //  $sql .= " 	  SUM(tutupkoli) AS selesai ";
        //  $sql .= "     FROM ( ";
        //  $sql .= " 	  SELECT ";
        //  $sql .= " 	    kodezona, ";
        //  $sql .= " 		dcp, ";
        //  $sql .= " 		fmkcab, ";
        //  $sql .= " 		fmndoc, ";
        //  $sql .= " 		tglpb, ";
        //  $sql .= " 		nopick, ";
        //  $sql .= " 		nosj, ";
        //  $sql .= " 		CASE WHEN dca_flag = '3' ";
        //  $sql .= " 		  THEN 1 ";
        //  $sql .= "           ELSE 0 ";
        //  $sql .= "         END AS tutupkoli ";
        //  $sql .= "       FROM ( ";
        //  $sql .= " 	    SELECT ";
        //  $sql .= " 		  kodezona, ";
        //  $sql .= " 		  MAX(grak) AS dcp, ";
        //  $sql .= " 		  fmkcab, ";
        //  $sql .= " 		  fmndoc, ";
        //  $sql .= " 		  tglpb, ";
        //  $sql .= " 		  nopicking AS nopick, ";
        //  $sql .= " 		  nosuratjalan AS nosj ";
        //  $sql .= " 		FROM ( ";
        //  $sql .= " 		  SELECT DISTINCT  ";
        //  $sql .= " 		    fmkcab, ";
        //  $sql .= " 			fmndoc, ";
        //  $sql .= " 			tglpb, ";
        //  $sql .= " 			grak, ";
        //  $sql .= " 			kodezona, ";
        //  $sql .= " 			nopicking, ";
        //  $sql .= " 			nosuratjalan ";
        //  $sql .= " 		  FROM dpd_idm_ora ";
        //  $sql .= " 		  WHERE tglupd = '" . Carbon::parse($dtTrans)->format('Ymd') . "' ";
        //  $sql .= "         ) p, tbtr_header_pbidm, tbmaster_tokoigr  ";
        //  $sql .= "        WHERE hpbi_kodetoko = tko_kodeomi ";
        //  $sql .= " 		AND hpbi_kodetoko = fmkcab ";
        //  $sql .= " 		AND hpbi_nopb = fmndoc ";
        //  $sql .= " 		AND hpbi_tglpb = tglpb ";
        //  $sql .= " 		AND hpbi_nopicking = nopicking ";
        //  $sql .= " 		AND hpbi_nosj = nosuratjalan ";
        //  if ($modeProgram == "OMI") {
        //      $sql .= " 		AND tko_kodesbu = 'O' ";
        //  } else {
        //      $sql .= " 		AND tko_kodesbu = 'I' ";
        //  }
        //  $sql .= " 		AND hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        //  $sql .= " 		AND hpbi_flag <> '5' ";
        //  $sql .= " 		GROUP BY kodezona, fmkcab, fmndoc, tglpb, nopicking, nosuratjalan ";
        //  $sql .= "       ) hdr ";
        //  $sql .= "       LEFT JOIN dcp_antrian ";
        //  $sql .= "       ON fmkcab = dca_toko ";
        //  $sql .= " 	   AND fmndoc = dca_nopb  ";
        //  $sql .= " 	   AND tglpb = dca_tglpb  ";
        //  $sql .= " 	   AND dcp = dca_grouprak  ";
        //  $sql .= " 	   AND nopick = dca_nopicking ";
        //  $sql .= " 	   AND nosj = dca_nosj ";
        //  $sql .= " 	) q ";
        //  $sql .= "     GROUP BY fmkcab, fmndoc, tglpb, nopick, nosj ";
        //  $sql .= "   ) t ";
        //  $sql .= "   WHERE total > 0 ";
        //  $sql .= "   ORDER BY nopick ";
        //  $sql .= " ) dtl ";
 
        //  $dt2 = QueryOra($sql, 10000); // QueryOra is a placeholder for your query execution method
        //  $dt = QueryOra(initial_detail($param, $flagDetail == true ? true : false)->toString(), 10000); // Assuming initial_detail() returns a query string
 
        //  $n = 0;
        //  foreach ($dt2 as $row) {
        //      if ($row['total'] == $row['selesai'] && $row['total'] > 0) {
        //          $n++;
        //      }
        //  }
        //  $_siapDspb = $n;
 
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
}
