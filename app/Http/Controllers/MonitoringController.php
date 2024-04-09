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

    public function load_zona(){
        $data = $this->DB_PGSQL
                     ->table("zona_idm")
                     ->select("zon_kode")
                     ->distinct()
                     ->orderBy("zon_kode","asc")
                     ->get();
        return $data;
    }

    public function initial(){


        $sendJalur = 0;
        $picking = 0;
        $scanning = 0;
        $siapDspb = 0;
        $selesaiDspb = 0;
        $jmlhPb = 0;
        $selesaiLoading = 0;

        $sql = "SELECT COUNT(1) ";
        $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";

        if ($modeProgram == "OMI") {
            $sql .= "AND TKO_KODESBU = 'O' ";
        } else {
            $sql .= "AND TKO_KODESBU = 'I' ";
        }

        $jmlhPb = DB::select(DB::raw($sql));

        $sql = "SELECT COUNT(1) ";
        $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        $sql .= "AND HPBI_FLAG IS NOT NULL ";

        if ($modeProgram == "OMI") {
            $sql .= "AND TKO_KODESBU = 'O' ";
        } else {
            $sql .= "AND TKO_KODESBU = 'I' ";
        }

        $sendJalur = DB::select(DB::raw($sql));

        $sql = "SELECT COUNT(1) ";
        $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        $sql .= "AND HPBI_FLAG <> '5' ";

        if ($modeProgram == "OMI") {
            $sql .= "AND TKO_KODESBU = 'O' ";
            $sql .= "AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab ";
            $sql .= "FROM dpd_idm_ora ";
            $sql .= "WHERE tglupd = '" . Carbon::parse($dtTrans)->format('Ymd') . "' ";
            $sql .= "AND (FMRCID = '1' OR FMRCID = '2')) q ";
            $sql .= "WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB) ";
        } else {
            $sql .= "AND TKO_KODESBU = 'I' ";
            $sql .= "AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan ";
            $sql .= "FROM dpd_idm_ora ";
            $sql .= "WHERE tglupd = '" . Carbon::parse($dtTrans)->format('Ymd') . "' ";
            $sql .= "AND (FMRCID = '1' OR FMRCID = '2')) q ";
            $sql .= "WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj) ";
        }

        $picking = DB::select(DB::raw($sql)); 





        $sql = "SELECT COUNT(1) ";
        $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        $sql .= "AND HPBI_FLAG <> '5' ";

        if ($modeProgram == "OMI") {
            $sql .= "AND TKO_KODESBU = 'O' ";
            $sql .= "AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab ";
            $sql .= "FROM dpd_idm_ora ";
            $sql .= "WHERE tglupd = '" . Carbon::parse($dtTrans)->format('Ymd') . "' ";
            $sql .= "AND FMRCID = '3') t ";
            $sql .= "WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB) ";
        } else {
            $sql .= "AND TKO_KODESBU = 'I' ";
            $sql .= "AND EXISTS (SELECT 1 FROM (SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan ";
            $sql .= "FROM dpd_idm_ora ";
            $sql .= "WHERE tglupd = '" . Carbon::parse($dtTrans)->format('Ymd') . "' ";
            $sql .= "AND FMRCID = '3') t ";
            $sql .= "WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj) ";
        }

        $scanning = DB::select(DB::raw($sql));

        $sql = "SELECT COUNT(1) ";
        $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        $sql .= "AND HPBI_FLAG = '4' ";

        if ($modeProgram == "OMI") {
            $sql .= "AND TKO_KODESBU = 'O' ";
        } else {
            $sql .= "AND TKO_KODESBU = 'I' ";
        }

        $selesaiLoading = DB::select(DB::raw($sql));

        $sql = "SELECT COUNT(1) ";
        $sql .= "FROM tbtr_header_pbidm JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi ";
        $sql .= "WHERE hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
        $sql .= "AND HPBI_FLAG = '5' ";

        if ($modeProgram == "OMI") {
            $sql .= "AND TKO_KODESBU = 'O' ";
        } else {
            $sql .= "AND TKO_KODESBU = 'I' ";
        }

        $selesaiDspb = DB::select(DB::raw($sql));

        // Further logic here


         // Set values from previous calculations or queries
         $lblPB = $_jmlhPb;
         $lblSendJalur = $_sendJalur;
         $lblPicking = $_picking;
         $lblScanning = $_scanning;
         $lblLoading = $_selesaiLoading;
         $lblDspb = $_selesaiDspb;
 
         $sql = "";
         $sql .= "SELECT ROW_NUMBER() OVER() AS no, dtl.* ";
         $sql .= "FROM ( ";
         $sql .= "   SELECT ";
         $sql .= "     toko, ";
         $sql .= "     nopb, ";
         $sql .= "     tglpb, ";
         $sql .= "     nopick, ";
         $sql .= "     nosj, ";
         $sql .= "     total, ";
         $sql .= "     selesai ";
         $sql .= "   FROM ( ";
         $sql .= "     SELECT ";
         $sql .= "       fmkcab AS toko, ";
         $sql .= "       fmndoc AS nopb, ";
         $sql .= "       tglpb, ";
         $sql .= "       nopick, ";
         $sql .= " 	  nosj, ";
         $sql .= " 	  COUNT(1) AS total, ";
         $sql .= " 	  SUM(tutupkoli) AS selesai ";
         $sql .= "     FROM ( ";
         $sql .= " 	  SELECT ";
         $sql .= " 	    kodezona, ";
         $sql .= " 		dcp, ";
         $sql .= " 		fmkcab, ";
         $sql .= " 		fmndoc, ";
         $sql .= " 		tglpb, ";
         $sql .= " 		nopick, ";
         $sql .= " 		nosj, ";
         $sql .= " 		CASE WHEN dca_flag = '3' ";
         $sql .= " 		  THEN 1 ";
         $sql .= "           ELSE 0 ";
         $sql .= "         END AS tutupkoli ";
         $sql .= "       FROM ( ";
         $sql .= " 	    SELECT ";
         $sql .= " 		  kodezona, ";
         $sql .= " 		  MAX(grak) AS dcp, ";
         $sql .= " 		  fmkcab, ";
         $sql .= " 		  fmndoc, ";
         $sql .= " 		  tglpb, ";
         $sql .= " 		  nopicking AS nopick, ";
         $sql .= " 		  nosuratjalan AS nosj ";
         $sql .= " 		FROM ( ";
         $sql .= " 		  SELECT DISTINCT  ";
         $sql .= " 		    fmkcab, ";
         $sql .= " 			fmndoc, ";
         $sql .= " 			tglpb, ";
         $sql .= " 			grak, ";
         $sql .= " 			kodezona, ";
         $sql .= " 			nopicking, ";
         $sql .= " 			nosuratjalan ";
         $sql .= " 		  FROM dpd_idm_ora ";
         $sql .= " 		  WHERE tglupd = '" . Carbon::parse($dtTrans)->format('Ymd') . "' ";
         $sql .= "         ) p, tbtr_header_pbidm, tbmaster_tokoigr  ";
         $sql .= "        WHERE hpbi_kodetoko = tko_kodeomi ";
         $sql .= " 		AND hpbi_kodetoko = fmkcab ";
         $sql .= " 		AND hpbi_nopb = fmndoc ";
         $sql .= " 		AND hpbi_tglpb = tglpb ";
         $sql .= " 		AND hpbi_nopicking = nopicking ";
         $sql .= " 		AND hpbi_nosj = nosuratjalan ";
         if ($modeProgram == "OMI") {
             $sql .= " 		AND tko_kodesbu = 'O' ";
         } else {
             $sql .= " 		AND tko_kodesbu = 'I' ";
         }
         $sql .= " 		AND hpbi_tgltransaksi = '" . Carbon::parse($dtTrans)->format('Y-m-d') . "' ";
         $sql .= " 		AND hpbi_flag <> '5' ";
         $sql .= " 		GROUP BY kodezona, fmkcab, fmndoc, tglpb, nopicking, nosuratjalan ";
         $sql .= "       ) hdr ";
         $sql .= "       LEFT JOIN dcp_antrian ";
         $sql .= "       ON fmkcab = dca_toko ";
         $sql .= " 	   AND fmndoc = dca_nopb  ";
         $sql .= " 	   AND tglpb = dca_tglpb  ";
         $sql .= " 	   AND dcp = dca_grouprak  ";
         $sql .= " 	   AND nopick = dca_nopicking ";
         $sql .= " 	   AND nosj = dca_nosj ";
         $sql .= " 	) q ";
         $sql .= "     GROUP BY fmkcab, fmndoc, tglpb, nopick, nosj ";
         $sql .= "   ) t ";
         $sql .= "   WHERE total > 0 ";
         $sql .= "   ORDER BY nopick ";
         $sql .= " ) dtl ";
 
         $dt2 = QueryOra($sql, 10000); // QueryOra is a placeholder for your query execution method
         $dt = QueryOra(initial_detail($param, $flagDetail == true ? true : false)->toString(), 10000); // Assuming initial_detail() returns a query string
 
         $n = 0;
         foreach ($dt2 as $row) {
             if ($row['total'] == $row['selesai'] && $row['total'] > 0) {
                 $n++;
             }
         }
         $_siapDspb = $n;
 
         $lblSDspb = $_siapDspb;
 
         if ($param == "4" && !$chck1) {
             $dgv->dataSource = $dt2;
         } else {
             $dgv->dataSource = $dt;
         }
         $dgv->refresh();
 
         switch ($_jenis) {
             case "":
                 $lblMonitoring = count($dt) . " PB Terupload ";
                 break;
             case "1":
                 $lblMonitoring = count($dt) . " Sudah Send Jalur ";
                 break;
             case "2":
                 $lblMonitoring = ($chck1 ? count($dt) . " ZONA Sedang Picking " : count($dt) . " Sedang Picking ");
                 break;
             case "3":
                 $lblMonitoring = ($chck1 ? count($dt) . " ZONA Sedang Scanning " : count($dt) . " Sedang Scanning ");
                 break;
             case "4":
                 $lblMonitoring = count($dt) . " Siap DSPB ";
                 break;
             case "5":
                 $lblMonitoring = count($dt) . " Selesai LOADING ";
                 break;
             case "6":
                 $lblMonitoring = count($dt) . " Selesai DSPB ";
                 break;
             default:
                 break;
         }
 
         if (DB::connection()->getPdo() != null) {
             DB::connection()->getPdo()->close();
         }


        $jmlhPb = DB::select("
            SELECT COUNT(1)
            FROM tbtr_header_pbidm
            JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
            WHERE hpbi_tgltransaksi = ?
            AND (TKO_KODESBU = ? OR TKO_KODESBU = ?)",
            [date('Y-m-d', strtotime($dtTrans)), $modeProgram === 'OMI' ? 'O' : 'I', $modeProgram === 'OMI' ? 'I' : 'O']
        );

        // Query 2
        $sendJalur = DB::select("
            SELECT COUNT(1)
            FROM tbtr_header_pbidm
            JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
            WHERE hpbi_tgltransaksi = ?
            AND HPBI_FLAG IS NOT NULL
            AND (TKO_KODESBU = ? OR TKO_KODESBU = ?)",
            [date('Y-m-d', strtotime($dtTrans)), $modeProgram === 'OMI' ? 'O' : 'I', $modeProgram === 'OMI' ? 'I' : 'O']
        );

        // Query 3
        $picking = DB::select("
            SELECT COUNT(1)
            FROM tbtr_header_pbidm
            JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
            WHERE hpbi_tgltransaksi = ?
            AND HPBI_FLAG <> '5'
            AND (TKO_KODESBU = ? OR TKO_KODESBU = ?)
            AND EXISTS (
                SELECT 1 FROM (
                    SELECT DISTINCT fmndoc, tglpb, fmkcab, nopicking, nosuratjalan
                    FROM dpd_idm_ora
                    WHERE tglupd = ? AND (FMRCID = '1' OR FMRCID = '2')
                ) q
                WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB
                AND nopicking = hpbi_nopicking AND nosuratjalan = hpbi_nosj
            )",
            [date('Y-m-d', strtotime($dtTrans)), $modeProgram === 'OMI' ? 'O' : 'I', $modeProgram === 'OMI' ? 'I' : 'O', date('Ymd', strtotime($dtTrans))]
        );

        // Query 4
        $scanning = DB::select("
            SELECT COUNT(1)
            FROM tbtr_header_pbidm
            JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
            WHERE hpbi_tgltransaksi = ?
            AND HPBI_FLAG <> '5'
            AND (TKO_KODESBU = ? OR TKO_KODESBU = ?)
            AND EXISTS (
                SELECT 1 FROM (
                    SELECT DISTINCT fmndoc, tglpb, fmkcab
                    FROM dpd_idm_ora
                    WHERE tglupd = ? AND FMRCID = '3'
                ) t
                WHERE fmkcab = HPBI_KODETOKO AND tglpb = HPBI_TGLPB AND fmndoc = HPBI_NOPB
            )",
            [date('Y-m-d', strtotime($dtTrans)), $modeProgram === 'OMI' ? 'O' : 'I', $modeProgram === 'OMI' ? 'I' : 'O', date('Ymd', strtotime($dtTrans))]
        );

        // Query 1
        $selesaiLoading = DB::select("
            SELECT COUNT(1)
            FROM tbtr_header_pbidm
            JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
            WHERE hpbi_tgltransaksi = ?
            AND HPBI_FLAG = '4'
            AND TKO_KODESBU = ?",
            [date('Y-m-d', strtotime($dtTrans)), $modeProgram === 'OMI' ? 'O' : 'I']
        );

        // Query 2
        $selesaiDspb = DB::select("
            SELECT COUNT(1)
            FROM tbtr_header_pbidm
            JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
            WHERE hpbi_tgltransaksi = ?
            AND HPBI_FLAG = '5'
            AND TKO_KODESBU = ?",
            [date('Y-m-d', strtotime($dtTrans)), $modeProgram === 'OMI' ? 'O' : 'I']
        );

        // Calculation for _siapDspb
        $sql = "
                    SELECT COUNT(1)
                    FROM (
                        SELECT 
                            fmkcab AS toko, 
                            fmndoc AS nopb, 
                            tglpb, 
                            nopicking AS nopick, 
                            nosuratjalan AS nosj, 
                            COUNT(1) AS total, 
                            SUM(CASE WHEN dca_flag = '3' THEN 1 ELSE 0 END) AS selesai
                        FROM (
                            SELECT 
                                kodezona, 
                                dcp, 
                                fmkcab, 
                                fmndoc, 
                                tglpb, 
                                nopicking, 
                                nosuratjalan, 
                                CASE WHEN dca_flag = '3' THEN 1 ELSE 0 END AS tutupkoli
                            FROM (
                                SELECT 
                                    kodezona, 
                                    MAX(grak) AS dcp, 
                                    fmkcab, 
                                    fmndoc, 
                                    tglpb, 
                                    nopicking, 
                                    nosuratjalan
                                FROM dpd_idm_ora
                                WHERE tglupd = ? 
                                GROUP BY kodezona, fmkcab, fmndoc, tglpb, nopicking, nosuratjalan
                            ) p
                            INNER JOIN tbtr_header_pbidm ON hpbi_kodetoko = fmkcab AND hpbi_nopb = fmndoc AND hpbi_tglpb = tglpb AND hpbi_nopicking = nopicking AND hpbi_nosj = nosuratjalan
                            INNER JOIN tbmaster_tokoigr ON hpbi_kodetoko = tko_kodeomi
                            WHERE hpbi_tgltransaksi = ? AND hpbi_flag <> '5' AND TKO_KODESBU = ?
                        ) hdr
                        LEFT JOIN dcp_antrian ON fmkcab = dca_toko AND fmndoc = dca_nopb AND tglpb = dca_tglpb AND dcp = dca_grouprak AND nopicking = dca_nopicking AND nosj = dca_nosj
                        GROUP BY fmkcab, fmndoc, tglpb, nopicking, nosuratjalan
                    ) t
                    WHERE total > 0
                    ORDER BY nopicking
                ";

        $siapDspbResult = DB::select($sql, [date('Ymd', strtotime($dtTrans)), date('Y-m-d', strtotime($dtTrans)), $modeProgram === 'OMI' ? 'O' : 'I']);
        $siapDspb = 0;
        foreach ($siapDspbResult as $row) {
            if ($row->TOTAL == $row->SELESAI && $row->TOTAL > 0) {
                $siapDspb++;
            }
        }


    }
}
