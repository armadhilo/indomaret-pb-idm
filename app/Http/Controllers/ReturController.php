<?php

namespace App\Http\Controllers;

use App\Traits\LibraryPDF;
use Illuminate\Http\Request;
use DB;
use Illuminate\Http\Client\ResponseSequence;

class ReturController extends Controller
{
    use LibraryPDF;
    public $DB_PGSQL;
    public $command_for_debug;
    public $debug;
    public function __construct()
    { 
        $this->DB_PGSQL = DB::connection('pgsql');
        $debug_condition = true;
        $this->command_for_debug = $debug_condition?"-- ":'';
        $this->debug = !$debug_condition?"-- ":'';

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
        return view("menu.retur.index",compact('flag'));
    }
    public function get_data_toko(){
        $data = $this->DB_PGSQL
             ->table("tbmaster_tokoigr")
             ->selectRaw("tko_kodeomi,tko_kodecustomer")
             ->whereRaw("tko_kodeigr = '".session('KODECABANG')."'")
             ->whereRaw("tko_namasbu = 'INDOMARET'")
             ->get();
        return $data;
    }

    public function isiDataNRB(Request $request){
        $kodetoko = $request->kodetoko;
        $KodeIGR = session()->get('KODECABANG');

        $sql = "SELECT DISTINCT DOCNO, TGL1::date, ISTYPE, P_ID, SUBSTR(keterangan, 1, 3) AS ket,SHOP,TOKO
                FROM TBTR_WT_INTERFACE 
                ".$this->command_for_debug."WHERE SHOP = '$kodetoko' AND RECID IN ('A', 'S') 
                ".$this->command_for_debug."AND TOKO = '$KodeIGR' 
                ORDER BY P_ID
                ".$this->debug." limit 10
                ";

        $list = $this->DB_PGSQL->select($sql);

        if (!count($list)) {
            return response()->json(['errors'=>true,'messages'=>"Data NRB Kosong", 'data'=> $list],500);
        }

        return response()->json(['errors'=>false,'messages'=>"Successfully", 'data'=> $list],200);
    }

    public function koliLoad(Request $request)
    {
        $data = json_decode(base64_decode($request->toko));
        $pid = $data->p_id; // Assuming this comes from the request
        $kodeToko = $data->shop; // Assuming this comes from the request

        $sql = "SELECT DISTINCT 
                    IKL_NOKOLI AS NOKOLI, 
                    SUBSTR(rpb_plu2, 1, 6) || '0' AS plu, 
                    rpb_qtyrealisasi AS qty_dspb 
                FROM tbtr_wt_interface 
                LEFT JOIN tbtr_realpb 
                    ON rpb_plu1 = prdcd 
                    AND rpb_kodeomi = shop 
                    AND rpb_idsuratjalan = docno2 
                LEFT JOIN tbtr_idmkoli 
                    ON ikl_nopb = rpb_nodokumen 
                    AND ikl_kodeidm = rpb_kodeomi 
                    AND ikl_nokoli = rpb_nokoli 
                    AND ikl_tglpb::date = rpb_tgldokumen::date 
               ".$this->command_for_debug." WHERE p_id = '$pid' 
               ".$this->command_for_debug."     AND SHOP = '$kodeToko' 
               ".$this->command_for_debug."     AND istype = '01' 
               ".$this->debug."     where istype = '01' 
                    AND recid = 'A' 
                ORDER BY 1, 2";

        $dtKoli = $this->DB_PGSQL->select($sql);

        if (!count($dtKoli)) {
            return response()->json(['errors'=>true,'messages'=>"Data Koli Kosong", 'data'=> $list],500);
        }

        return response()->json(['errors'=>false,'messages'=>"Successfully", 'data'=> $dtKoli],200);
    }

    public function load_data_selected_npb(Request $request){

        $no = $request->input('no');
        $txtTokoID = $request->input('txtTokoID');
        $lvNrb = $request->input('lvNrb'); // Assuming lvNrb is passed as request parameter.

        $pluContainer = "";
        $kodeDCIDM = "";
        $flagPLUIDM = false;
        $FlagProcess = true;
        $FlagAuth = false;
        $flagContainer = false;

        $pluContainer = DB::selectOne("SELECT SUBSTR(pluigr,1,6) || '0' FROM plu_container_idm")->result ?? "1372800";

        // CHECK AND GET KODEDC
        $dtCek = DB::select("SELECT msi_kodedc FROM master_supply_idm WHERE msi_kodetoko = ?", [$txtTokoID]);
        if (count($dtCek) > 0) {
            $kodeDCIDM = $dtCek[0]->msi_kodedc;

            // CHECK PLUIDM
            $dtCek = DB::select("SELECT idm_pluidm FROM tbmaster_pluidm WHERE idm_kodeidm = ?", [$kodeDCIDM]);
            $flagPLUIDM = count($dtCek) > 0;
        } else {
            $flagPLUIDM = false;
        }

        if ($lvNrb[$no]['SubItems'][2] == "F") {

            // CHECK TBTR_SORTASI_RETUR
            $dtCek1 = DB::select("SELECT table_name FROM information_schema.tables WHERE UPPER(table_name) = 'TBTR_SORTASI_RETUR'");
            if (count($dtCek1) <= 0) {
                $createTableQuery = "
                    CREATE TABLE tbtr_sortasi_retur (
                        sor_nonrb          VARCHAR(14),
                        sor_tglnrb         DATE,
                        sor_kodetoko       VARCHAR(6),
                        sor_prdcd          VARCHAR(9),
                        sor_pluidm         VARCHAR(9),
                        sor_qty_nrb        NUMERIC,
                        sor_qty_fisik      NUMERIC,
                        sor_qty_bakurang   NUMERIC,
                        sor_qty_baik       NUMERIC,
                        sor_qty_layakretur NUMERIC,
                        sor_qty_batolak    NUMERIC,
                        sor_tglexpdate     DATE,
                        sor_userabsen      VARCHAR(5),
                        sor_tglabsen       DATE,
                        sor_usersortasi    VARCHAR(5),
                        sor_tglsortasi     DATE,
                        sor_tglclose       DATE,
                        sor_create_by      VARCHAR(5),
                        sor_create_dt      DATE,
                        sor_modify_by      VARCHAR(5),
                        sor_modify_dt      DATE
                    )";
                DB::statement($createTableQuery);
            }

            // CHECK RECID A
            $dtCek = DB::select("
                SELECT RECID FROM tbtr_wt_interface
                WHERE SHOP = ?
                  AND DOCNO = ?
                  AND TO_CHAR(TGL1, 'dd/MM/YYYY') = ?", [
                $txtTokoID,
                $lvNrb[$no]['SubItems'][0],
                $lvNrb[$no]['SubItems'][1]
            ]);

            if (count($dtCek) == 0) {
                return response()->json(['message' => 'Data NRB tidak ditemukan!'], 404);
            }

            $sql = '';
            if ($flagPLUIDM) {
                $sql = "
                    SELECT ROW_NUMBER() OVER() AS NO, a.*
                    FROM (
                        SELECT prd_prdcd AS plu
                        FROM tbtr_wt_interface
                        LEFT JOIN tbmaster_pluidm ON prdcd = idm_pluidm AND idm_kodeidm = ?
                        LEFT JOIN tbmaster_prodmast ON prd_prdcd = idm_pluigr OR prdcd = prd_plumcg
                        LEFT JOIN tbmaster_hargabeli ON prd_prdcd = hgb_prdcd
                        WHERE p_id = ?
                        AND istype <> '01'
                        AND ? = prd_kodeigr
                        AND recid IN ('A', 'S')
                        AND SHOP = ?
                        AND COALESCE(hgb_tipe, 'N') != '2'
                        AND SUBSTR(PRD_PRDCD, 7, 1) = '0'
                        ORDER BY prd_prdcd
                    ) a";
                $bindings = [$kodeDCIDM, $lvNrb[$no]['SubItems'][3], $KodeIGR, $txtTokoID];
            } else {
                $sql = "
                    SELECT ROW_NUMBER() OVER() AS NO, a.*
                    FROM (
                        SELECT prd_prdcd AS plu
                        FROM tbtr_wt_interface
                        LEFT JOIN tbmaster_prodcrm ON prdcd = prc_pluidm AND prc_group = 'I'
                        LEFT JOIN tbmaster_prodmast ON prd_prdcd = prc_pluigr OR prdcd = prd_plumcg
                        LEFT JOIN tbmaster_hargabeli ON prd_prdcd = hgb_prdcd
                        WHERE p_id = ?
                        AND istype <> '01'
                        AND ? = prd_kodeigr
                        AND recid IN ('A', 'S')
                        AND SHOP = ?
                        AND COALESCE(hgb_tipe, 'N') != '2'
                        AND SUBSTR(PRD_PRDCD, 7, 1) = '0'
                        ORDER BY prd_prdcd
                    ) a";
                $bindings = [$lvNrb[$no]['SubItems'][3], $KodeIGR, $txtTokoID];
            }

            $dtPLUTMHB = DB::select($sql, $bindings);

            if (count($dtPLUTMHB) > 0) {
                $msg = '';
                foreach ($dtPLUTMHB as $row) {
                    $msg .= $row->plu . ', ';
                }

                $msg = rtrim($msg, ', ');
                return response()->json(['message' => "PLU $msg tidak memiliki master_hargabeli!"], 200);
                $FlagProcess = false;
            }

            if ($flagPLUIDM) {
                // BACA DARI TBMASTER_PLUIDM
                $sql = "
                    SELECT ROW_NUMBER() OVER() AS NO, a.*
                    FROM (
                        SELECT prd_prdcd AS plu,
                            keterangan,
                            COALESCE(qty, 0) AS retur,
                            COALESCE(sor_qty_fisik, 0) AS fisik,
                            COALESCE(sor_qty_baik, 0) AS baik,
                            COALESCE(sor_qty_layakretur, 0) AS LayakRetur,
                            COALESCE(sor_qty_bakurang, 0) + COALESCE(sor_qty_batolak, 0) AS ba,
                            COALESCE(price, 0) AS price,
                            ROUND(COALESCE(ppn, 0) / COALESCE(qty, 1), 2) AS ppn,
                            UPPER(hgb_statusbarang) AS status,
                            COALESCE(IDM_KODETAG, '-') AS TAG_IDM,
                            CASE WHEN COALESCE(ST_AVGCOST, 0) > 0 THEN 1 ELSE 0 END AS AVGCOST,
                            CASE WHEN TGL1 > plm_tgldiskontinu + 7 THEN 0 ELSE 1 END AS RETMAJALAH,
                            (
                                SELECT 
                                    CASE SUBSTR(KETERANGAN, 1, 8) 
                                        WHEN '04040000' THEN 
                                            CASE WHEN PRD_KODETAG <> 'N' AND PRD_FLAGIDM = 'N' THEN '01' ELSE '02' END 
                                        ELSE RDM_DEFAULTLOKASI 
                                    END
                                FROM TBMASTER_RETUR_IDM
                                WHERE RDM_TIPE = SUBSTR(KETERANGAN, 1, 8) AND RDM_STATUSBARANG = hgb_statusbarang
                            ) AS LOKASI,
                            TO_CHAR(sor_tglexpdate, 'DD-MM-YYYY') AS EXP_DT,
                            CASE 
                                WHEN psu_tglpindah IS NULL THEN '0'
                                ELSE 
                                    CASE 
                                        WHEN DATE_TRUNC('day', tgl1) > DATE_TRUNC('day', psu_tglpindah + 7) THEN '1'
                                        ELSE '0'
                                    END
                            END AS FLAG_PINDAH
                        FROM tbtr_wt_interface
                        LEFT JOIN tbmaster_pluidm ON prdcd = idm_pluidm AND idm_kodeidm = ?
                        LEFT JOIN tbmaster_prodmast ON prd_prdcd = idm_pluigr OR prdcd = prd_plumcg
                        LEFT JOIN tbmaster_hargabeli ON prd_prdcd = hgb_prdcd
                        LEFT JOIN TBMASTER_STOCK ON prd_prdcd = st_prdcd AND ST_LOKASI = '01'
                        LEFT JOIN TBMASTER_PLUMAJALAH ON PRD_PRDCD = PLM_PRDCD
                        LEFT JOIN TBTR_SORTASI_RETUR ON PRD_PRDCD = SOR_PRDCD
                            AND sor_kodetoko = shop AND sor_nonrb = docno AND sor_tglnrb = tgl1
                        LEFT JOIN TBMASTER_PINDAHSUPPLY ON prd_prdcd = psu_pluigr AND psu_recid IS NULL
                        WHERE p_id = ? 
                            AND istype <> '01' 
                            AND ? = prd_kodeigr 
                            AND recid IN ('A', 'S') 
                            AND SHOP = ? 
                            AND hgb_tipe = '2' 
                            AND SUBSTR(PRD_PRDCD, 7, 1) = '0'
                        ORDER BY prd_prdcd
                    ) a";
                $bindings = [$kodeDCIDM, $lvNrb[$no]['SubItems'][3], $KodeIGR, $txtTokoID];
            } else {
                // BACA DARI TBMASTER_PRODCRM
                $sql = "
                    SELECT ROW_NUMBER() OVER() AS NO, a.*
                    FROM (
                        SELECT prd_prdcd AS plu,
                            keterangan,
                            COALESCE(qty, 0) AS retur,
                            COALESCE(sor_qty_fisik, 0) AS fisik,
                            COALESCE(sor_qty_baik, 0) AS baik,
                            COALESCE(sor_qty_layakretur, 0) AS LayakRetur,
                            COALESCE(sor_qty_bakurang, 0) + COALESCE(sor_qty_batolak, 0) AS ba,
                            COALESCE(price, 0) AS price,
                            ROUND(COALESCE(ppn, 0) / COALESCE(qty, 1), 2) AS ppn,
                            UPPER(hgb_statusbarang) AS status,
                            COALESCE(PRC_KODETAG, '-') AS TAG_IDM,
                            CASE WHEN COALESCE(ST_AVGCOST, 0) > 0 THEN 1 ELSE 0 END AS AVGCOST,
                            CASE WHEN TGL1 > plm_tgldiskontinu + 7 THEN 0 ELSE 1 END AS RETMAJALAH,
                            (
                                SELECT 
                                    CASE SUBSTR(KETERANGAN, 1, 8) 
                                        WHEN '04040000' THEN 
                                            CASE WHEN PRD_KODETAG <> 'N' AND PRD_FLAGIDM = 'N' THEN '01' ELSE '02' END 
                                        ELSE RDM_DEFAULTLOKASI 
                                    END
                                FROM TBMASTER_RETUR_IDM
                                WHERE RDM_TIPE = SUBSTR(KETERANGAN, 1, 8) AND RDM_STATUSBARANG = hgb_statusbarang
                            ) AS LOKASI,
                            TO_CHAR(sor_tglexpdate, 'DD-MM-YYYY') AS EXP_DT,
                            CASE 
                                WHEN psu_tglpindah IS NULL THEN '0'
                                ELSE 
                                    CASE 
                                        WHEN DATE_TRUNC('day', tgl1) > DATE_TRUNC('day', psu_tglpindah + 7) THEN '1'
                                        ELSE '0'
                                    END
                            END AS FLAG_PINDAH
                        FROM tbtr_wt_interface
                        LEFT JOIN tbmaster_prodcrm ON prdcd = prc_pluidm AND prc_group = 'I'
                        LEFT JOIN tbmaster_prodmast ON prd_prdcd = prc_pluigr OR prdcd = prd_plumcg
                        LEFT JOIN tbmaster_hargabeli ON prd_prdcd = hgb_prdcd
                        LEFT JOIN TBMASTER_STOCK ON prd_prdcd = st_prdcd AND ST_LOKASI = '01'
                        LEFT JOIN TBMASTER_PLUMAJALAH ON PRD_PRDCD = PLM_PRDCD
                        LEFT JOIN TBTR_SORTASI_RETUR ON PRD_PRDCD = SOR_PRDCD
                            AND sor_kodetoko = shop AND sor_nonrb = docno AND sor_tglnrb = tgl1
                        LEFT JOIN TBMASTER_PINDAHSUPPLY ON prd_prdcd = psu_pluigr AND psu_recid IS NULL
                        WHERE p_id = ? 
                            AND istype <> '01' 
                            AND ? = prd_kodeigr 
                            AND recid IN ('A', 'S') 
                            AND SHOP = ? 
                            AND hgb_tipe = '2' 
                            AND SUBSTR(PRD_PRDCD, 7, 1) = '0'
                        ORDER BY prd_prdcd
                    ) a";
                $bindings = [$lvNrb[$no]['SubItems'][3], $KodeIGR, $txtTokoID];
            }
    
            $dtPLUTMHB = DB::select($sql, $bindings);
    
            if (count($dtPLUTMHB) > 0) {
                $msg = '';
                foreach ($dtPLUTMHB as $row) {
                    $msg .= $row->plu . ', ';
                }
    
                $msg = rtrim($msg, ', ');
                return response()->json(['message' => "PLU $msg tidak memiliki master_hargabeli!"], 200);
                $FlagProcess = false;
            }
    

        }else{
            if ($flagPLUIDM) {
                // BACA DARI TBMASTER_PLUIDM
                $sql = "
                    SELECT ROW_NUMBER() OVER() AS NO, B.*
                    FROM (
                        SELECT PLU,
                            SUM(QTY_DSPB) AS QTY_DSPB,
                            MAX(RETUR) AS RETUR,
                            SUM(QTY_BA) AS QTY_BA,
                            SUM(QTY_BA) AS BA_FISIK,
                            (SUM(QTY_DSPB) - MAX(RETUR)) AS cctv,
                            0 AS beban_idm,
                            MAX(RETUR) AS beban_igr,
                            MAX(PRICE) AS PRICE,
                            MAX(PPN) AS PPN,
                            COUNT(IKL_NOKOLI) AS TOT_REF,
                            MAX(TAG_IDM) AS TAG_IDM,
                            MAX(STATUS) AS STATUS,
                            1 AS AVGCOST,
                            1 AS RETMAJALAH,
                            MAX(KETERANGAN) AS KETERANGAN,
                            MAX(LMI_NOBA) AS NO_BA
                        FROM (
                            SELECT DISTINCT prd_prdcd AS plu,
                                pbo_qtyrealisasi AS qty_dspb,
                                qty AS retur,
                                CASE WHEN LMI_NOBA IS NULL THEN 0 ELSE pbo_qtyrealisasi END AS QTY_BA,
                                COALESCE(price, 0) AS price,
                                ROUND(COALESCE(ppn, 0) / COALESCE(qty, 1), 2) AS ppn,
                                PRD_KODETAG AS TAG_IDM,
                                (
                                    SELECT HGB_STATUSBARANG
                                    FROM TBMASTER_HARGABELI
                                    WHERE HGB_PRDCD = PRD_PRDCD AND HGB_TIPE = '2'
                                ) AS STATUS,
                                IKL_NOKOLI,
                                KETERANGAN,
                                LMI_NOBA
                            FROM tbtr_wt_interface
                            LEFT JOIN tbmaster_prodmast ON prdcd = prd_plumcg AND SUBSTR(PRD_PRDCD, 7, 1) = '0'
                            LEFT JOIN tbtr_realpb ON prdcd = rpb_plu1 AND shop = rpb_kodeomi AND docno2 = rpb_idsuratjalan
                            LEFT JOIN tbmaster_pbomi ON rpb_nodokumen = pbo_nopb AND rpb_tgldokumen = pbo_tglpb AND rpb_kodeomi = pbo_kodeomi AND rpb_plu1 = pbo_pluomi
                            LEFT JOIN tbtr_idmkoli ON rpb_nokoli = ikl_nokoli AND pbo_nopb = ikl_nopb AND pbo_kodeomi = ikl_kodeidm AND pbo_nokoli = ikl_nokoli AND TO_CHAR(pbo_tglpb, 'YYYYMMdd') = ikl_tglpb
                            LEFT JOIN loading_mobil_idm ON rpb_idsuratjalan = lmi_nodspb AND rpb_kodeomi = lmi_kodetoko AND rpb_nokoli = lmi_nokoli AND lmi_flag = '2B'
                            WHERE p_id = ? AND SHOP = ? AND istype = '01' AND recid = 'A'
                        ) X
                        GROUP BY PLU
                        ORDER BY 1
                    ) B";
                $bindings = [$lvNrb[$no]['SubItems'][3], $txtTokoID];
            } else {
                // BACA DARI TBMASTER_PRODCRM
                $sql = "
                    SELECT ROW_NUMBER() OVER() AS NO, B.*
                    FROM (
                        SELECT PLU,
                            SUM(QTY_DSPB) AS QTY_DSPB,
                            MAX(RETUR) AS RETUR,
                            SUM(QTY_BA) AS QTY_BA,
                            SUM(QTY_BA) AS BA_FISIK,
                            (SUM(QTY_DSPB) - MAX(RETUR)) AS cctv,
                            0 AS beban_idm,
                            MAX(RETUR) AS beban_igr,
                            MAX(PRICE) AS PRICE,
                            MAX(PPN) AS PPN,
                            COUNT(IKL_NOKOLI) AS TOT_REF,
                            MAX(TAG_IDM) AS TAG_IDM,
                            MAX(STATUS) AS STATUS,
                            1 AS AVGCOST,
                            1 AS RETMAJALAH,
                            MAX(KETERANGAN) AS KETERANGAN,
                            MAX(LMI_NOBA) AS NO_BA
                        FROM (
                            SELECT DISTINCT prd_prdcd AS plu,
                                pbo_qtyrealisasi AS qty_dspb,
                                qty AS retur,
                                CASE WHEN LMI_NOBA IS NULL THEN 0 ELSE pbo_qtyrealisasi END AS QTY_BA,
                                COALESCE(price, 0) AS price,
                                ROUND(COALESCE(ppn, 0) / COALESCE(qty, 1), 2) AS ppn,
                                PRD_KODETAG AS TAG_IDM,
                                (
                                    SELECT HGB_STATUSBARANG
                                    FROM TBMASTER_HARGABELI
                                    WHERE HGB_PRDCD = PRD_PRDCD AND HGB_TIPE = '2'
                                ) AS STATUS,
                                IKL_NOKOLI,
                                KETERANGAN,
                                LMI_NOBA
                            FROM tbtr_wt_interface
                            LEFT JOIN tbmaster_prodmast ON prdcd = prd_plumcg AND SUBSTR(PRD_PRDCD, 7, 1) = '0'
                            LEFT JOIN tbtr_realpb ON prdcd = rpb_plu1 AND shop = rpb_kodeomi AND docno2 = rpb_idsuratjalan
                            LEFT JOIN tbmaster_pbomi ON rpb_nodokumen = pbo_nopb AND rpb_tgldokumen = pbo_tglpb AND rpb_kodeomi = pbo_kodeomi AND rpb_plu1 = pbo_pluomi
                            LEFT JOIN tbtr_idmkoli ON rpb_nokoli = ikl_nokoli AND pbo_nopb = ikl_nopb AND pbo_kodeomi = ikl_kodeidm AND pbo_nokoli = ikl_nokoli AND TO_CHAR(pbo_tglpb, 'YYYYMMdd') = ikl_tglpb
                            LEFT JOIN loading_mobil_idm ON rpb_idsuratjalan = lmi_nodspb AND rpb_kodeomi = lmi_kodetoko AND rpb_nokoli = lmi_nokoli AND lmi_flag = '2B'
                            WHERE p_id = ? AND SHOP = ? AND istype = '01' AND recid = 'A'
                        ) X
                        GROUP BY PLU
                        ORDER BY 1
                    ) B";
                $bindings = [$lvNrb[$no]['SubItems'][3], $txtTokoID];
            }
            $listRetur = DB::select($sql, $bindings);
        }



    }
}
