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
        }else{
            foreach ($list as $key => $value) {
                if (!is_null($value->ket)) {
                    if ($value->ket == "010" && $value->istype == "01") {
                        $value->type = "P";
                    } else {
                        $value->type = "F";
                    }
                } else {
                    $value->type = "F";
                }
            }
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

    public function list_data_retur(Request $request){

        $data = json_decode(base64_decode($request->toko));
        $no = 0;  
        $pluContainer = "";
        $kodeDCIDM = "";
        $flagPLUIDM = false;
        $KodeIGR = session()->get('KODECABANG');

        $FlagProcess = true;
        $FlagAuth = false;
        $flagContainer = false;

        // Get PLU Container
        $pluContainerResult = DB::selectOne("SELECT CONCAT(SUBSTR(pluigr, 1, 6), '0') AS plu_container FROM plu_container_idm");
        
        if ($pluContainerResult) {
            $pluContainer = $pluContainerResult->plu_container;
        }
        
        if (empty($pluContainer)) {
            $pluContainer = "1372800";
        }

        // Check and Get KODEDC
        $dtCek = DB::select("SELECT msi_kodedc FROM master_supply_idm WHERE msi_kodetoko = '".$data->shop."'");

        if (count($dtCek) > 0) {
            $kodeDCIDM = $dtCek[0]->msi_kodedc;

            // Check PLUIDM
            $dtCek = DB::select("SELECT idm_pluidm FROM tbmaster_pluidm WHERE idm_kodeidm = '$kodeDCIDM'");
            
            if (count($dtCek) > 0) {
                $flagPLUIDM = true;
            } else {
                $flagPLUIDM = false;
            }
        } else {
            $flagPLUIDM = false;
        }

        if ($data->type == "F") {
            $cekTable = DB::select("SELECT * FROM information_schema.tables WHERE UPPER(table_name) = 'TBTR_SORTASI_RETUR'");
            if (count($cekTable) <= 0) {
                
                $createTableSQL = "
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
                    )
                ";
                DB::statement($createTableSQL);
            }

            // Check RECID A in tbtr_wt_interface
            $recidQuery = "
                SELECT RECID FROM tbtr_wt_interface
                 ".$this->command_for_debug." WHERE SHOP = '".$data->shop."' 
                 ".$this->command_for_debug." AND DOCNO = '".$data->docno."' 
                 ".$this->command_for_debug." AND TO_CHAR(TGL1,'dd/MM/YYYY') = '".$data->tgl1."' 
                 ".$this->debug."limit 1 
            ";
            $dtCek = DB::select($recidQuery);

            if (!count($dtCek)) {
                return response()->json(["errors"=>true,"messages"=>"Data NRB tidak ditemukan!"],500);
            }

            if($flagPLUIDM){
                $sql_dtPLUTMHB = "SELECT ROW_NUMBER() OVER() NO, a.* 
                                FROM (  SELECT prd_prdcd plu 
                                            FROM tbtr_wt_interface 
                                                LEFT JOIN tbmaster_pluidm ON prdcd = idm_pluidm AND idm_kodeidm = '".$kodeDCIDM."' 
                                                LEFT JOIN tbmaster_prodmast ON prd_prdcd = idm_pluigr OR prdcd = prd_plumcg 
                                                LEFT JOIN tbmaster_hargabeli ON prd_prdcd = hgb_prdcd 
                ".$this->command_for_debug."WHERE     p_id = '".$data->p_id."' 
                ".$this->command_for_debug."        AND istype <> '01' 
                ".$this->command_for_debug."        AND '".$KodeIGR."' = prd_kodeigr 
                ".$this->command_for_debug."        AND recid IN ('A', 'S') 
                ".$this->command_for_debug."        AND SHOP = '".$data->shop."'  
                ".$this->command_for_debug."        AND COALESCE(hgb_tipe, 'N') != '2' 
                ".$this->debug."                    WHERE COALESCE(hgb_tipe, 'N') != '2' 
                                                    AND SUBSTR (PRD_PRDCD, 7, 1) = '0' 
                                        ORDER BY prd_prdcd) a ";

            }else{
                $sql_dtPLUTMHB =  "SELECT ROW_NUMBER() OVER() NO, a.* 
                                    FROM (  SELECT prd_prdcd plu 
                                                FROM tbtr_wt_interface 
                                                    LEFT JOIN tbmaster_prodcrm ON prdcd = prc_pluidm AND prc_group = 'I' 
                                                    LEFT JOIN tbmaster_prodmast ON prd_prdcd = prc_pluigr OR prdcd = prd_plumcg 
                                                    LEFT JOIN tbmaster_hargabeli ON prd_prdcd = hgb_prdcd 
                ".$this->command_for_debug."WHERE     p_id = '".$data->p_id."'  
                ".$this->command_for_debug."        AND istype <> '01' 
                ".$this->command_for_debug."        AND '".$KodeIGR."' = prd_kodeigr 
                ".$this->command_for_debug."        AND recid IN ('A', 'S') 
                ".$this->command_for_debug."        AND SHOP = '".$data->shop."' 
                ".$this->command_for_debug."        AND COALESCE(hgb_tipe, 'N') != '2' 
                ".$this->debug."                    WHERE COALESCE(hgb_tipe, 'N') != '2' 
                                                    AND SUBSTR (PRD_PRDCD, 7, 1) = '0' 
                                            ORDER BY prd_prdcd) a";
            }

            $dtPLUTMHB = DB::select($sql_dtPLUTMHB);

            // if(count($dtPLUTMHB)){
            //     $msg = "";
            //     dd($dtPLUTMHB);
            //     foreach ($dtPLUTMHB as $row) {
            //         // Assuming $row->item(1) retrieves the second column value (adjust as per your structure)
            //         $msg .= $row->plu . ",";
            //     }
            //     $msg = rtrim($msg, ",");
            //     $msg = str_replace(",", ", ", $msg);
                
            //     return response()->json(["errors"=>true,"messages"=>"PLU " . $msg . " tidak memiliki master_hargabeli!"],500);
            // }

            if($flagPLUIDM){
                        $data = [];
                        $data []= (object)[
                            "plu" => 'plu test',
                            "keterangan" => 'keterangan test',
                            "retur" => 'retur test',
                            "fisik" => 'fisik test', 
                            "baik" => 'baik test', 
                            "layakretur" => 'layakretur test', 
                            "ba" => 'ba test', 
                            "price" => 'price test',
                            "ppn" => 'ppn test',
                            "status" => 'status test',
                            "tag_idm" => 'tag_idm test',
                            "avgcost" => 'avgcost test',
                            "retmajalah" => 'retmajalah test',
                            "lokasi" => 'lokasi test',
                            "exp_dt" => 'exp_dt test',
                            "flag_pindah" => 'flag_pindah test' 
                        ];
                //         $listRetur_sql = "SELECT ROW_NUMBER() OVER() NO, a.* 
                //                     FROM (  SELECT prd_prdcd plu, 
                //                                     keterangan, 
                //                                     coalesce (qty, 0) retur, 
                //                                     COALESCE(sor_qty_fisik, 0) fisik,  
                //                                     COALESCE(sor_qty_baik, 0) baik,  
                //                                     COALESCE(sor_qty_layakretur, 0) LayakRetur,  
                //                                     COALESCE(sor_qty_bakurang, 0) + COALESCE(sor_qty_batolak, 0) ba,  
                //                                     coalesce (price, 0) price, 
                //                                     ROUND(coalesce (ppn, 0) / coalesce (qty, 1),2) ppn, 
                //                                     UPPER(hgb_statusbarang) status, 
                //                                     COALESCE(IDM_KODETAG, '-') TAG_IDM, 
                //                                     CASE WHEN coalesce(ST_AVGCOST,0) > 0 THEN 1 ELSE 0 END AVGCOST, 
                //                                     CASE WHEN TGL1 > plm_tgldiskontinu::DATE +7 THEN 0 ELSE 1 END RETMAJALAH, 
                //                                     (SELECT CASE SUBSTR(KETERANGAN,1,8) WHEN '04040000' THEN CASE WHEN PRD_KODETAG <> 'N' AND PRD_FLAGIDM = 'N' THEN '01' ELSE '02' END  ELSE RDM_DEFAULTLOKASI END   FROM TBMASTER_RETUR_IDM  WHERE RDM_TIPE = SUBSTR(KETERANGAN,1,8) AND RDM_STATUSBARANG = hgb_statusbarang) LOKASI, 
                //                                     TO_CHAR(sor_tglexpdate,'DD-MM-YYYY') EXP_DT, 
                //                                     CASE WHEN psu_tglpindah IS NULL THEN '0' ELSE  CASE WHEN DATE_TRUNC('day', tgl1) > DATE_TRUNC('day', psu_tglpindah + 7)  THEN '1'  ELSE '0'  END  END FLAG_PINDAH  
                //                                 FROM tbtr_wt_interface 
                //                                     LEFT JOIN tbmaster_pluidm ON prdcd = idm_pluidm AND idm_kodeidm = '".$kodeDCIDM."' 
                //                                     LEFT JOIN tbmaster_prodmast ON prd_prdcd = idm_pluigr OR prdcd = prd_plumcg 
                //                                     LEFT JOIN tbmaster_hargabeli ON prd_prdcd = hgb_prdcd 
                //                                     LEFT JOIN TBMASTER_STOCK ON prd_prdcd = st_prdcd and ST_LOKASI = '01' 
                //                                     LEFT JOIN TBMASTER_PLUMAJALAH ON PRD_PRDCD = PLM_PRDCD 
                //                                     LEFT JOIN TBTR_SORTASI_RETUR ON PRD_PRDCD = SOR_PRDCD 
                //                                         AND sor_kodetoko = shop AND sor_nonrb = docno AND sor_tglnrb = tgl1 
                //                                     LEFT JOIN TBMASTER_PINDAHSUPPLY ON prd_prdcd = psu_pluigr AND psu_recid IS NULL 
                // ".$this->command_for_debug."WHERE     p_id = '".$data->p_id."' 
                // ".$this->command_for_debug."        AND istype <> '01' 
                // ".$this->command_for_debug."        AND '".$KodeIGR."' = prd_kodeigr 
                // ".$this->command_for_debug."        AND recid IN ('A', 'S') 
                // ".$this->command_for_debug."        AND SHOP = '".$data->shop."' 
                // ".$this->command_for_debug."        AND hgb_tipe = '2' 
                // ".$this->debug."                    WHERE hgb_tipe = '2' 
                //                                     AND SUBSTR (PRD_PRDCD, 7, 1) = '0' 
                //                             ORDER BY prd_prdcd) a ";
                $type = "F_true";                            

            }else{
                        $data = [];
                        $data []= (object)[
                            "plu" => 'test',
                            "keterangan" => 'test',
                            "retur" => 'test',
                            "fisik" => 'test', 
                            "baik" => 'test', 
                            "layakretur" => 'test', 
                            "ba" => 'test', 
                            "price" => 'test',
                            "ppn" => 'test',
                            "status" => 'test',
                            "tag_idm" => 'test',
                            "avgcost" => 'test',
                            "retmajalah" => 'test',
                            "lokasi" => 'test',
                            "exp_dt" => 'test',
                            "flag_pindah" => 'test' 
                        ];
                //         $listRetur_sql = " SELECT ROW_NUMBER() OVER() NO, a.* 
                //                     FROM (  SELECT prd_prdcd plu, 
                //                                     keterangan, 
                //                                     coalesce (qty, 0) retur, 
                //                                     COALESCE(sor_qty_fisik, 0) fisik,  
                //                                     COALESCE(sor_qty_baik, 0) baik,  
                //                                     COALESCE(sor_qty_layakretur, 0) LayakRetur,  
                //                                     COALESCE(sor_qty_bakurang, 0) + COALESCE(sor_qty_batolak, 0) ba,  
                //                                     coalesce (price, 0) price, 
                //                                     ROUND(coalesce (ppn, 0) / coalesce (qty, 1),2) ppn, 
                //                                     UPPER(hgb_statusbarang) status, 
                //                                     COALESCE(PRC_KODETAG, '-') TAG_IDM, 
                //                                     CASE WHEN coalesce(ST_AVGCOST,0) > 0 THEN 1 ELSE 0 END AVGCOST, 
                //                                     CASE WHEN TGL1 > plm_tgldiskontinu+7 THEN 0 ELSE 1 END RETMAJALAH, 
                //                                     (SELECT CASE SUBSTR(KETERANGAN,1,8) WHEN '04040000' THEN  CASE WHEN PRD_KODETAG <> 'N' AND PRD_FLAGIDM   = 'N' THEN '01' ELSE '02' END  ELSE RDM_DEFAULTLOKASI END  FROM TBMASTER_RETUR_IDM WHERE RDM_TIPE = SUBSTR(KETERANGAN,1,8) AND RDM_STATUSBARANG = hgb_statusbarang) LOKASI, 
                //                                     TO_CHAR(sor_tglexpdate,'DD-MM-YYYY') EXP_DT, 
                //                                     CASE WHEN psu_tglpindah IS NULL THEN '0' ELSE  CASE WHEN DATE_TRUNC('day', tgl1) > DATE_TRUNC('day', psu_tglpindah + 7)  THEN '1'  ELSE '0'  END END FLAG_PINDAH  
                //                                 FROM tbtr_wt_interface 
                //                                     LEFT JOIN tbmaster_prodcrm ON prdcd = prc_pluidm AND prc_group = 'I' 
                //                                     LEFT JOIN tbmaster_prodmast ON prd_prdcd = prc_pluigr OR prdcd = prd_plumcg 
                //                                     LEFT JOIN tbmaster_hargabeli ON prd_prdcd = hgb_prdcd 
                //                                     LEFT JOIN TBMASTER_STOCK ON prd_prdcd = st_prdcd and ST_LOKASI = '01' 
                //                                     LEFT JOIN TBMASTER_PLUMAJALAH ON PRD_PRDCD = PLM_PRDCD 
                //                                     LEFT JOIN TBTR_SORTASI_RETUR ON PRD_PRDCD = SOR_PRDCD 
                //                                         AND sor_kodetoko = shop AND sor_nonrb = docno AND sor_tglnrb = tgl1 
                //                                     LEFT JOIN TBMASTER_PINDAHSUPPLY ON prd_prdcd = psu_pluigr AND psu_recid IS NULL 
                // ".$this->command_for_debug."        WHERE     p_id = '".$data->p_id."'  
                // ".$this->command_for_debug."                AND istype <> '01' 
                // ".$this->command_for_debug."        AND '".$KodeIGR."' = prd_kodeigr 
                // ".$this->command_for_debug."        AND recid IN ('A', 'S') 
                // ".$this->command_for_debug."        AND SHOP = '".$data->shop."' 
                // ".$this->command_for_debug."        AND hgb_tipe = '2' 
                // ".$this->debug."                    WHERE hgb_tipe = '2' 
                //                                     AND SUBSTR (PRD_PRDCD, 7, 1) = '0' 
                //                             ORDER BY prd_prdcd) a ";

                $type = "F_false";                            
            }

        }else{
            
            if ($flagPLUIDM) {
                $data = [];
                $data []= (object)[ 
                    "plu" => "plu test",
                    "qty_dspb" => "qty_dspb test",
                    "retur" => "retur test",
                    "qty_ba" => "qty_ba test",
                    "ba_fisik" => "ba_fisik test",
                    "cctv" => "cctv test",
                    "beban_idm" => "beban_idm test",
                    "beban_igr" => "beban_igr test",
                    "price" => "price test",
                    "ppn" => "ppn test",
                    "tot_ref" => "tot_ref test",
                    "tag_idm" => "tag_idm test",
                    "status" => "status test",
                    "avgcost" => "avgcost test",
                    "retmajalah" => "retmajalah test",
                    "keterangan" => "keterangan test",
                    "no_ba" => "no_ba test" 
                ];
                // $listRetur_sql = "SELECT ROW_NUMBER() OVER(), B.* FROM (SELECT PLU,  
                //                     SUM(QTY_DSPB) AS QTY_DSPB,  
                //                     MAX(RETUR) AS RETUR,  
                //                     SUM(QTY_BA) AS QTY_BA,  
                //                     SUM(QTY_BA) AS BA_FISIK,  
                //                     (SUM(QTY_DSPB) - Max(RETUR)) cctv,   
                //                     0 beban_idm,   
                //                     MAX(RETUR) beban_igr,  
                //                     MAX(PRICE) AS PRICE,  
                //                     MAX(PPN) AS PPN,  
                //                     COUNT(IKL_NOKOLI) TOT_REF,  
                //                     MAX(TAG_IDM) AS TAG_IDM,  
                //                     MAX(STATUS) AS STATUS,  
                //                         1 AVGCOST, 
                //                         1 RETMAJALAH, 
                //                     MAX(KETERANGAN) AS KETERANGAN,  
                //                     MAX(LMI_NOBA) AS NO_BA  
                //                 FROM (   
                //                 SELECT DISTINCT prd_prdcd plu,   
                //                                 pbo_qtyrealisasi qty_dspb,  
                //                                 qty AS retur,  
                //                                 CASE WHEN LMI_NOBA IS NULL THEN 0 ELSE pbo_qtyrealisasi END AS QTY_BA,  
                //                                 coalesce (price, 0) price,   
                //                                 ROUND(coalesce (ppn, 0) / coalesce (qty, 1),2) ppn,   
                //                                 PRD_KODETAG TAG_IDM,   
                //                                 (  
                //                                 SELECT HGB_STATUSBARANG   
                //                                     FROM TBMASTER_HARGABELI   
                //                                 WHERE HGB_PRDCD = PRD_PRDCD AND HGB_TIPE = '2'  
                //                                 )   
                //                                     STATUS,   
                //                                 IKL_NOKOLI,   
                //                                 KETERANGAN,  
                //                                 LMI_NOBA  
                //                 FROM tbtr_wt_interface   
                //                 LEFT JOIN  tbmaster_prodmast   
                //                     ON prdcd = prd_plumcg 
                //                     AND SUBSTR (PRD_PRDCD, 7, 1) = '0' 
                //                 LEFT JOIN  tbtr_realpb   
                //                     ON prdcd = rpb_plu1 
                //                     AND shop = rpb_kodeomi 
                //                     AND docno2 = rpb_idsuratjalan 
                //                 LEFT JOIN  tbmaster_pbomi  
                //                     ON rpb_nodokumen = pbo_nopb 
                //                     AND rpb_tgldokumen = pbo_tglpb 
                //                     AND rpb_kodeomi = pbo_kodeomi 
                //                     AND rpb_plu1 = pbo_pluomi 
                //                 LEFT JOIN  tbtr_idmkoli  
                //                     ON rpb_nokoli = ikl_nokoli 
                //                     AND pbo_nopb = ikl_nopb 
                //                     AND pbo_kodeomi = ikl_kodeidm 
                //                     AND pbo_nokoli = ikl_nokoli 
                //                     AND TO_CHAR (pbo_tglpb, 'YYYYMMdd') = ikl_tglpb  
                //                 LEFT JOIN  loading_mobil_idm 
                //                     ON rpb_idsuratjalan =  lmi_nodspb 
                //                     AND rpb_kodeomi = lmi_kodetoko 
                //                     AND rpb_nokoli = lmi_nokoli 
                //                     AND lmi_flag = '2B' 
                //                 ".$this->command_for_debug."WHERE p_id = '".$data->p_id."' 
                //                 ".$this->command_for_debug."AND SHOP = '".$data->shop."' 
                //                 ".$this->command_for_debug."AND istype = '01' 
                //                 ".$this->debug."WHERE istype = '01' 
                //                 AND recid = 'A' 
                //                 ) X   
                //                 GROUP BY PLU 
                //                 ORDER BY 1) B  
                //               ";
                $type = "NonF_true"; 

            } else {
                $data = [];
                $data []= (object)[ 
                    "plu" => "plu test",
                    "qty_dspb" => "qty_dspb test",
                    "retur" => "retur test",
                    "qty_ba" => "qty_ba test",
                    "ba_fisik" => "ba_fisik test",
                    "cctv" => "cctv test",
                    "beban_idm" => "beban_idm test",
                    "beban_igr" => "beban_igr test",
                    "price" => "price test",
                    "ppn" => "ppn test",
                    "tot_ref" => "tot_ref test",
                    "tag_idm" => "tag_idm test",
                    "status" => "status test",
                    "avgcost" => "avgcost test",
                    "retmajalah" => "retmajalah test",
                    "keterangan" => "keterangan test",
                    "no_ba" => "no_ba test"
                ];
                // $listRetur_sql = "SELECT ROW_NUMBER() OVER(), B.* FROM (SELECT PLU,  
                //                     SUM(QTY_DSPB) AS QTY_DSPB,  
                //                     MAX(RETUR) AS RETUR,  
                //                     SUM(QTY_BA) AS QTY_BA,  
                //                     SUM(QTY_BA) AS BA_FISIK,  
                //                     (SUM(QTY_DSPB) - Max(RETUR)) cctv,   
                //                     0 beban_idm,   
                //                     MAX(RETUR) beban_igr,  
                //                     MAX(PRICE) AS PRICE,  
                //                     MAX(PPN) AS PPN,  
                //                     COUNT(IKL_NOKOLI) TOT_REF,  
                //                     MAX(TAG_IDM) AS TAG_IDM,  
                //                     MAX(STATUS) AS STATUS,  
                //                         1 AVGCOST, 
                //                         1 RETMAJALAH, 
                //                     MAX(KETERANGAN) AS KETERANGAN,  
                //                     MAX(LMI_NOBA) AS NO_BA  
                //                 FROM (   
                //                 SELECT DISTINCT prd_prdcd plu,  
                //                                 pbo_qtyrealisasi qty_dspb,  
                //                                 qty AS retur,  
                //                                 CASE WHEN LMI_NOBA IS NULL THEN 0 ELSE pbo_qtyrealisasi END AS QTY_BA,  
                //                                 coalesce (price, 0) price,   
                //                                 ROUND(coalesce (ppn, 0) / coalesce (qty, 1),2) ppn,   
                //                                 PRD_KODETAG TAG_IDM,   
                //                                 (  
                //                                 SELECT HGB_STATUSBARANG   
                //                                     FROM TBMASTER_HARGABELI   
                //                                 WHERE HGB_PRDCD = PRD_PRDCD AND HGB_TIPE = '2'  
                //                                 )   
                //                                     STATUS,   
                //                                 IKL_NOKOLI,   
                //                                 KETERANGAN,  
                //                                 LMI_NOBA  
                //                 FROM tbtr_wt_interface   
                //                 LEFT JOIN  tbmaster_prodmast   
                //                     ON prdcd = prd_plumcg 
                //                     AND SUBSTR (PRD_PRDCD, 7, 1) = '0' 
                //                 LEFT JOIN  tbtr_realpb   
                //                     ON prdcd = rpb_plu1 
                //                     AND shop = rpb_kodeomi 
                //                     AND docno2 = rpb_idsuratjalan 
                //                 LEFT JOIN  tbmaster_pbomi  
                //                     ON rpb_nodokumen = pbo_nopb 
                //                     AND rpb_tgldokumen = pbo_tglpb 
                //                     AND rpb_kodeomi = pbo_kodeomi 
                //                     AND rpb_plu1 = pbo_pluomi 
                //                 LEFT JOIN  tbtr_idmkoli  
                //                     ON rpb_nokoli = ikl_nokoli 
                //                     AND pbo_nopb = ikl_nopb 
                //                     AND pbo_kodeomi = ikl_kodeidm 
                //                     AND pbo_nokoli = ikl_nokoli 
                //                     AND TO_CHAR (pbo_tglpb, 'YYYYMMdd') = ikl_tglpb  
                //                 LEFT JOIN  loading_mobil_idm 
                //                     ON rpb_idsuratjalan =  lmi_nodspb 
                //                     AND rpb_kodeomi = lmi_kodetoko 
                //                     AND rpb_nokoli = lmi_nokoli 
                //                     AND lmi_flag = '2B' 
                //                 ".$this->command_for_debug."WHERE p_id = '".$data->p_id."'  
                //                 ".$this->command_for_debug."AND SHOP = '".$data->shop."' 
                //                 ".$this->command_for_debug."AND istype = '01' 
                //                 ".$this->debug."WHERE istype = '01' 
                //                 AND recid = 'A' 
                //                 ) X   
                //                 GROUP BY PLU, 0, 0  
                //                 ORDER BY 1) B  
                //                 ";
            
                $type = "NonF_false"; 
            }


        }
        $listRetur = $data;
        // $listRetur = DB::select($listRetur_sql);

        return response()->json(['errors'=>false,'messages'=>"Successfully", 'data'=> $listRetur,'type'=>$type],200);


    }

        
    public function print_report(Request $request, $data){
        $data = json_decode(base64_decode($data));
        $jenis_page = null;
        $folder_page = null;
        $filename = $data->filename;
        $title_report = 'default-'.date('Y-m-d');
        $header_cetak_custom = false;
        $postiion_page_number_x = 63;
        $postiion_page_number_y = 615;
        
        switch ($data->filename) {

                case 'cetak-ulang-dsp':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data =$this->data_cetak_ulang_dsp($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'cetak-ulang-sj':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_ulang_sj($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'struk-hadiah':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_struk_hadiah_omi($data->kodetoko,$data->nopb,$data->tglpb,$request);
                    
                    if (isset($data->data->errors)) {
                        $data = null;
                    }

                    break;
                case 'outstanding-dsp':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_outstanding_dsp($data->kodetoko,$data->kodetoko2);
                    $header_cetak_custom = 'upper';  
                    
                    if (isset($data->data->errors)) {
                        $data = null;
                    }

                    break;

            default:
                break;
        }

        $tanggal = date('Y-m-d');
        
        $perusahaan = $this->DB_PGSQL
                           ->table("tbmaster_perusahaan")
                           ->whereRaw("prs_kodeigr = '".session('KODECABANG')."'")
                           ->get();
        $perusahaan = $perusahaan[0];
        if ($jenis_page == 'default-page') {
              
            $pdf = PDF::loadview('menu.rpt.'.$folder_page.'.'.$filename, compact('data','tanggal','perusahaan','header_cetak_custom'));
            $pdf->output();
            $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
            $canvas = $dompdf->get_canvas();
    
            // //make page text in header and right side
    
            $canvas->page_text($postiion_page_number_y,$postiion_page_number_x , "Page {PAGE_NUM} of {PAGE_COUNT}", null, 7, array(0, 0, 0));

            return $pdf->stream($title_report );
            
        } elseif ($jenis_page == 'struk-page') {
           
            $dompdf = new PDF();
            $pdf = PDF::loadview('menu.rpt.'.$folder_page.'.'.$filename,compact(['perusahaan','data']));
            error_reporting(E_ALL ^ E_DEPRECATED);
            $pdf->output();
            $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
            $canvas = $dompdf ->get_canvas();
            $dompdf = $pdf;
            return $pdf->stream($title_report );
        } 
        // else{
            
        // }
        // dd('out');
    }

    public function print_cek_kks(Request $request){
        // $kodetoko = $request->toko;
        // $nopb = explode(" / ",$request->nopb)[0];
        // $tglpb = explode(" / ",$request->nopb)[1];
        
        $data_report =[];
        // dd($data_report);
        // $data_report['kodetoko'] = $kodetoko;
        // $data_report['nopb'] = $nopb;
        // $data_report['tglpb'] = $tglpb;
        $data_report['filename'] = 'cetak-ulang-sj';
        $data_report['jenis_page'] = 'default-page';
        $data_report['folder_page'] = 'omi';
        $data_report['title_report'] = 'CETAK-ULANG-SJ';
        $encrypt_data = base64_encode(json_encode($data_report));
        //    $decrypt_data = json_decode(base64_decode($encrypt_data));
        $link = url('/api/retur/print/report/'.$encrypt_data);

        return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
    }

}
