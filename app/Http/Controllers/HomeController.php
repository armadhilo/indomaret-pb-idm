<?php

namespace App\Http\Controllers;

use App\Helper\DatabaseConnection;
use App\Http\Requests\DetailKasirRequest;
use App\Http\Requests\TableRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{

    public function __construct(Request $request){
        DatabaseConnection::setConnection(session('KODECABANG'), "PRODUCTION");
    }

    public function index(){

        //!! Add later
        $this->checkICM();
        $this->addMonitoringWT();
        $this->addReturTT();
        $this->addSortasi();
        $this->addHistoryContainer_Idm_Omi();
        $this->addColKardus();

        // $this->updateMenuSPI();
        // $this->CreateMenuAll();

        return view('home');
    }

    private function checkICM(){
        //! GET PRS_SINGKATANCABANG
        $singCaICM = DB::table('tbmaster_perusahaan')
            ->selectRaw("UPPER(REPLACE(prs_singkatancabang,' ',''))")
            ->first()->upper;

        if (str_contains($singCaICM, 'ICM')) {
            $flagIGR = false;
            $flagPunyaICM = false;

            if(session('server') == 'SIMULASI'){
                $skemaICM = str_replace("ICM", "SIMICM", $singCaICM);
            }

        }elseif(str_contains($singCaICM, 'SPI')){
            $flagIGR = false;
            $flagPunyaICM = false;
        }else{
            $flagIGR = true;

            if(session('server') == 'SIMULASI'){
                $skemaICM = str_replace("IGR", "SIMICM", $singCaICM);
            }else{
                $skemaICM = str_replace("IGR", "ICM", $singCaICM);
            }

            //! CREATE TABLE tbtr_bypass_pbicm
            $count = DB::table('information_schema.tables')
                ->whereRaw("upper(table_name) = 'TBTR_BYPASS_PBICM'")
                ->count();

            if($count == 0){
                $query = '';
                $query .= "CREATE TABLE tbtr_bypass_pbicm ( ";
                $query .= "  byp_kodeigr     VARCHAR(4), ";
                $query .= "  byp_kodetoko    VARCHAR(5), ";
                $query .= "  byp_jenistoko   VARCHAR(4), ";
                $query .= "  byp_nopb        VARCHAR(20), ";
                $query .= "  byp_tglpb       DATE, ";
                $query .= "  byp_nopbicm     VARCHAR(20), ";
                $query .= "  byp_tglpbicm    DATE, ";
                $query .= "  byp_nopickicm   VARCHAR(20), ";
                $query .= "  byp_nosjicm     VARCHAR(20), ";
                $query .= "  byp_userapprove VARCHAR(5), ";
                $query .= "  byp_tglapprove  DATE, ";
                $query .= "  byp_alasan      VARCHAR(1000), ";
                $query .= "  byp_create_by   VARCHAR(5), ";
                $query .= "  byp_create_dt   DATE, ";
                $query .= "  byp_modify_by   VARCHAR(5), ";
                $query .= "  byp_modify_dt   DATE ";
                $query .= ") ";
                DB::insert($query);
            }

            $count = DB::table('information_schema.tables')
                ->whereRaw("upper(table_name) = 'TBMASTER_ICM'")
                ->count();

            if($count == 0){
                $flagPunyaICM = false;
            }else{
                $cek = DB::table('tbmaster_icm')
                    ->where('icm_kodeigr', session('KODECABANG'))
                    ->whereNull('icm_recordid')
                    ->count();

                if($cek == 0){
                    $flagPunyaICM = false;
                }else{
                    $flagPunyaICM = true;
                }

                //! CREATE TABLE tbtr_bypass_pbicm
                $count = DB::table('information_schema.tables')
                    ->whereRaw("upper(table_name) = 'TBTR_BYPASS_PBICM'")
                    ->count();

                if($count == 0){
                    $query = '';
                    $query .= "CREATE TABLE tbtr_bypass_pbicm ( ";
                    $query .= "  byp_kodeigr     VARCHAR(4), ";
                    $query .= "  byp_kodetoko    VARCHAR(5), ";
                    $query .= "  byp_jenistoko   VARCHAR(4), ";
                    $query .= "  byp_nopb        VARCHAR(20), ";
                    $query .= "  byp_tglpb       DATE, ";
                    $query .= "  byp_nopbicm     VARCHAR(20), ";
                    $query .= "  byp_tglpbicm    DATE, ";
                    $query .= "  byp_nopickicm   VARCHAR(20), ";
                    $query .= "  byp_nosjicm     VARCHAR(20), ";
                    $query .= "  byp_userapprove VARCHAR(5), ";
                    $query .= "  byp_tglapprove  DATE, ";
                    $query .= "  byp_alasan      VARCHAR(1000), ";
                    $query .= "  byp_create_by   VARCHAR(5), ";
                    $query .= "  byp_create_dt   DATE, ";
                    $query .= "  byp_modify_by   VARCHAR(5), ";
                    $query .= "  byp_modify_dt   DATE ";
                    $query .= ") ";
                    DB::insert($query);
                }

                $count = DB::table('information_schema.tables')
                    ->whereRaw("upper(table_name) = 'TBMASTER_ICM'")
                    ->count();

                if($count == 0){
                    $flagPunyaICM = false;
                }else{
                    $cek = DB::table('tbmaster_icm')
                        ->where('icm_kodeigr', session('KODECABANG'))
                        ->whereNull('icm_recordid')
                        ->count();

                    if($cek == 0){
                        $flagPunyaICM = false;
                    }else{
                        $flagPunyaICM = true;
                    }
                }
            }
        }
    }

    private function addMonitoringWT(){
        $dtCek = DB::table('tbmaster_access')
            ->where([
                'accesscode' => '1511000000',
                'accessname' => 'MONITORING WT WEB SERVICE'
            ])
            ->count();

        if($dtCek > 0){
            DB::table('tbmaster_access')
            ->where([
                'accesscode' => '1511000000',
                'accessname' => 'MONITORING WT WEB SERVICE'
            ])
            ->delete();
        }

        $dtCek = DB::table('tbmaster_access')
            ->where([
                'accesscode' => '1515000000',
            ])
            ->count();

        if($dtCek == 0){
            $query = '';
            $query .= "INSERT INTO tbmaster_access ( ";
            $query .= "  accessgroup, ";
            $query .= "  accesscode, ";
            $query .= "  accessname, ";
            $query .= "  accesslevel, ";
            $query .= "  rootid, ";
            $query .= "  url, ";
            $query .= "  description, ";
            $query .= "  create_by, ";
            $query .= "  create_dt, ";
            $query .= "  kodeigr, ";
            $query .= "  fomenu ";
            $query .= ")  ";
            $query .= "SELECT 'IDM', ";
            $query .= "       '1515000000', ";
            $query .= "       'MONITORING WT WEB SERVICE', ";
            $query .= "       2, ";
            $query .= "       '1500000000', ";
            $query .= "       'MONITORINGWT', ";
            $query .= "       'MONITORING WT WEB SERVICE', ";
            $query .= "       'ADM', ";
            $query .= "       now(), ";
            $query .= "       prs_kodeigr, ";
            $query .= "       '3' ";
            $query .= "  FROM tbmaster_perusahaan ";
            DB::insert($query);
        }

        $dtCek = DB::table('tbmaster_useraccess')
            ->where([
                'accesscode' => '1515000000',
            ])
            ->count();

        if($dtCek == 0){
            $query = '';
            $query .= "INSERT INTO tbmaster_useraccess ";
            $query .= "SELECT DISTINCT userid, ";
            $query .= "       accessgroup, ";
            $query .= "       '1515000000', ";
            $query .= "       '1', ";
            $query .= "       '1', ";
            $query .= "       '1', ";
            $query .= "       '1', ";
            $query .= "       kodeigr, ";
            $query .= "       'ADM', ";
            $query .= "       now(), ";
            $query .= "       NULL, ";
            $query .= "       null ";
            $query .= "  FROM tbmaster_useraccess  ";
            $query .= " WHERE accessgroup = 'IDM' ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'TEMP_WT_BTB'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TEMP_WT_BTB ( ";
            $query .= "  IP          VarChar(20), ";
            $query .= "  NAMA_FILE   VarChar(50), ";
            $query .= "  STAT_GET    VarChar(30), ";
            $query .= "  STAT_PROSES VarChar(30), ";
            $query .= "  NOBPB       NUMERIC, ";
            $query .= "  JML_ITEMBPB NUMERIC, ";
            $query .= "  DPP         NUMERIC, ";
            $query .= "  PPN         NUMERIC, ";
            $query .= "  PKP         VarChar(5), ";
            $query .= "  STATUS      VarChar(10) ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'TEMP_WT_RETUR'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TEMP_WT_RETUR ( ";
            $query .= "  IP          Varchar(20),  ";
            $query .= "  KODE_DC     Varchar(5), ";
            $query .= "  SHOP        Varchar(5), ";
            $query .= "  NO_NRB      NUMERIC, ";
            $query .= "  TGL_NRB     Varchar(30), ";
            $query .= "  ISTYPE      Varchar(5), ";
            $query .= "  SCTYPE      Varchar(8), ";
            $query .= "  DIV         Varchar(2), ";
            $query .= "  PRDCD       Varchar(10), ";
            $query .= "  QTY         NUMERIC, ";
            $query .= "  PRICE       NUMERIC, ";
            $query .= "  PPN         NUMERIC, ";
            $query .= "  GROSS       NUMERIC, ";
            $query .= "  BKP         NUMERIC(2), ";
            $query .= "  SUB_BKP     Varchar(2), ";
            $query .= "  KETER       Varchar(50), ";
            $query .= "  FLAG_PROSES Varchar(10),  ";
            $query .= "  NO_NPB_DC   NUMERIC, ";
            $query .= "  TGL_NPB_DC  Varchar(30), ";
            $query .= "  PRICE_IDM   NUMERIC, ";
            $query .= "  PPNBM_IDM   NUMERIC,  ";
            $query .= "  PPNRP_IDM   NUMERIC, ";
            $query .= "  TOT_REC     NUMERIC ";
            $query .= ") ";
            DB::insert($query);
        }


        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'LOG_BTB'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE LOG_BTB ( ";
            $query .= "  WTB_TGL_PROSES  DATE, ";
            $query .= "  WTB_NAMAWT      Varchar(20), ";
            $query .= "  WTB_FILENPB     Varchar(100), ";
            $query .= "  WTB_NOBPB       Varchar(20), ";
            $query .= "  WTB_STATUS      Varchar(1000), ";
            $query .= "  WTB_CREATE_BY   Varchar(3), ";
            $query .= "  WTB_CREATE_DT   DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'LOG_BTB_STATUS'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE LOG_BTB_STATUS ( ";
            $query .= "  WTB_TGL_PROSES  DATE, ";
            $query .= "  WTB_NAMAWT      VARCHAR(20), ";
            $query .= "  WTB_FILENPB     VARCHAR(100), ";
            $query .= "  WTB_NOBPB       VARCHAR(20), ";
            $query .= "  WTB_STATUS      VARCHAR(1000), ";
            $query .= "  WTB_CREATE_BY   VARCHAR(3), ";
            $query .= "  WTB_CREATE_DT   DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'LOG_WEBSERVICE_IGR'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE LOG_WEBSERVICE_IGR ( ";
            $query .= "  WSI_TGL_PROSES  DATE,  ";
            $query .= "  WSI_IP_CLIENT   VARCHAR(30), ";
            $query .= "  WSI_NAMAWT      VARCHAR(50), ";
            $query .= "  WSI_JENIS       VARCHAR(20), ";
            $query .= "  WSI_PATH      VARCHAR(500), ";
            $query .= "  WSI_RESPONSE    VARCHAR(500), ";
            $query .= "  WSI_CREATE_BY   VARCHAR(5), ";
            $query .= "  WSI_CREATE_DT   DATE ";
            $query .= ") ";
            DB::insert($query);
        }
    }

    private function addReturTT(){
        $dtCek = DB::table('tbmaster_access')
            ->where([
                'accesscode' => '1516000000',
            ])
            ->count();

        if($dtCek == 0){
            $query = '';
            $query .= "INSERT INTO tbmaster_access ( ";
            $query .= "  accessgroup, ";
            $query .= "  accesscode, ";
            $query .= "  accessname, ";
            $query .= "  accesslevel, ";
            $query .= "  rootid, ";
            $query .= "  url, ";
            $query .= "  description, ";
            $query .= "  create_by, ";
            $query .= "  create_dt, ";
            $query .= "  kodeigr, ";
            $query .= "  fomenu ";
            $query .= ")  ";
            $query .= "SELECT 'IDM', ";
            $query .= "       '1516000000', ";
            $query .= "       'RETUR TOKO TUTUP IDM', ";
            $query .= "       2, ";
            $query .= "       '1500000000', ";
            $query .= "       'RETURRTT', ";
            $query .= "       'PROSES RETUR TUTUP IDM', ";
            $query .= "       'ADM', ";
            $query .= "       now(), ";
            $query .= "       prs_kodeigr, ";
            $query .= "       '3' ";
            $query .= "  FROM tbmaster_perusahaan ";
            DB::insert($query);
        }

        $dtCek = DB::table('tbmaster_access')
            ->where([
                'accesscode' => '1516000000',
            ])
            ->count();

        if($dtCek == 0){
            $query = '';
            $query .= "INSERT INTO tbmaster_useraccess ";
            $query .= "SELECT DISTINCT userid, ";
            $query .= "       accessgroup, ";
            $query .= "       '1516000000', ";
            $query .= "       '1', ";
            $query .= "       '1', ";
            $query .= "       '1', ";
            $query .= "       '1', ";
            $query .= "       kodeigr, ";
            $query .= "       'ADM', ";
            $query .= "       now(), ";
            $query .= "       NULL, ";
            $query .= "       null ";
            $query .= "  FROM tbmaster_useraccess  ";
            $query .= " WHERE accessgroup = 'IDM' ";
            $query .= "   AND accesscode  = '1503000000' ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'RTT_IDM_INTERFACE'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE RTT_IDM_INTERFACE( ";
            $query .= "  RECID         Varchar(1), ";
            $query .= "  DOCNO         Varchar(20), ";
            $query .= "  DOCNO2        Varchar(20), ";
            $query .= "  DIV           Varchar(4), ";
            $query .= "  TOKO          Varchar(6), ";
            $query .= "  TOKO_1        Varchar(6), ";
            $query .= "  GUDANG        Varchar(6), ";
            $query .= "  PRDCD         Varchar(10), ";
            $query .= "  PLUIGR        Varchar(10), ";
            $query .= "  QTY           NUMERIC, ";
            $query .= "  PRICE         NUMERIC, ";
            $query .= "  GROSS         NUMERIC, ";
            $query .= "  PPN           NUMERIC, ";
            $query .= "  TANGGAL       DATE, ";
            $query .= "  TANGGAL2      DATE, ";
            $query .= "  SHOP          VARCHAR(6), ";
            $query .= "  ISTYPE        VARCHAR(4), ";
            $query .= "  PRICE_IDM     NUMERIC, ";
            $query .= "  PPNBM_IDM     NUMERIC, ";
            $query .= "  PPNRP_IDM     NUMERIC, ";
            $query .= "  SCTYPE        Varchar(8), ";
            $query .= "  BKP           Varchar(2), ";
            $query .= "  SUB_BKP       Varchar(2), ";
            $query .= "  CABANG        Varchar(6), ";
            $query .= "  TIPE_GDG      Varchar(10), ";
            $query .= "  RII_CREATE_BY Varchar(5), ";
            $query .= "  RII_CREATE_DT DATE, ";
            $query .= "  RII_MODIFY_BY Varchar(5), ";
            $query .= "  RII_MODIFY_DT DATE, ";
            $query .= "  RII_FILENAME  Varchar(200) ";
            $query .= ") ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'TEMP_RTT_IDM'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TEMP_RTT_IDM ( ";
            $query .= "  DOCNO     Varchar(20), ";
            $query .= "  DOCNO2    Varchar(20), ";
            $query .= "  DIV       Varchar(4), ";
            $query .= "  TOKO      Varchar(6), ";
            $query .= "  TOKO_1    Varchar(6), ";
            $query .= "  GUDANG    Varchar(6), ";
            $query .= "  PRDCD     Varchar(10), ";
            $query .= "  QTY       NUMERIC, ";
            $query .= "  PRICE     NUMERIC, ";
            $query .= "  GROSS     NUMERIC, ";
            $query .= "  PPN       NUMERIC, ";
            $query .= "  TANGGAL   Varchar(30), ";
            $query .= "  TANGGAL2  Varchar(30), ";
            $query .= "  SHOP      Varchar(6), ";
            $query .= "  ISTYPE    Varchar(4), ";
            $query .= "  PRICE_IDM NUMERIC, ";
            $query .= "  PPNBM_IDM NUMERIC, ";
            $query .= "  PPNRP_IDM NUMERIC, ";
            $query .= "  SCTYPE    Varchar(8), ";
            $query .= "  BKP       Varchar(2), ";
            $query .= "  SUB_BKP   Varchar(2), ";
            $query .= "  CABANG    Varchar(6), ";
            $query .= "  TIPE_GDG  Varchar(10) ";
            $query .= ") ";
            DB::insert($query);
        }

        $data = DB::select("SELECT COUNT(*) FROM information_schema.columns WHERE upper(table_name) = 'TEMP_RTT_IDM' AND upper(column_name) = 'KETERANGAN'");
        if(count($data)){
            DB::select("ALTER TABLE TEMP_RTT_IDM ADD COLUMN KETERANGAN VARCHAR(100)");
        }

        $data = DB::select("SELECT COUNT(*) FROM information_schema.columns WHERE upper(table_name) = 'TEMP_RTT_IDM' AND upper(column_name) = 'PPN_RATE'");
        if(count($data)){
            DB::select("ALTER TABLE TEMP_RTT_IDM ADD COLUMN PPN_RATE NUMERIC");
        }
    }

    private function addSortasi(){
        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'TBTR_SORTASI_RETUR'")->count();
        if($count == 0){
            $query = '';
            $query .= " CREATE TABLE TBTR_SORTASI_RETUR (    ";
            $query .= "   sor_nonrb          Varchar(14),   ";
            $query .= "   sor_tglnrb         DATE,           ";
            $query .= "   sor_kodetoko       Varchar(6),    ";
            $query .= "   sor_prdcd          Varchar(9),    ";
            $query .= "   sor_pluidm         Varchar(9),    ";
            $query .= "   sor_qty_nrb        NUMERIC,         ";
            $query .= "   sor_qty_fisik      NUMERIC,         ";
            $query .= "   sor_qty_bakurang   NUMERIC,         ";
            $query .= "   sor_qty_baik       NUMERIC,         ";
            $query .= "   sor_qty_layakretur NUMERIC,         ";
            $query .= "   sor_qty_batolak    NUMERIC,         ";
            $query .= "   sor_tglexpdate     DATE,           ";
            $query .= "   sor_userabsen      Varchar(5),    ";
            $query .= "   sor_tglabsen       DATE,           ";
            $query .= "   sor_usersortasi    Varchar(5),    ";
            $query .= "   sor_tglsortasi     DATE,           ";
            $query .= "   sor_tglclose       DATE,           ";
            $query .= "   sor_create_by      Varchar(5),    ";
            $query .= "   sor_create_dt      DATE,           ";
            $query .= "   sor_modify_by      Varchar(5),    ";
            $query .= "   sor_modify_dt      DATE            ";
            $query .= " ) ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'TEMP_RTT_IDM'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE SEQUENCE SEQ_KKS ";
            $query .= "  MINVALUE 1 ";
            $query .= "  MAXVALUE 9999 ";
            $query .= "  START WITH 1 ";
            $query .= "  INCREMENT BY 1 ";
            $query .= "  CYCLE ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'TBHISTORY_LIST_KKS'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TBHISTORY_LIST_KKS ( ";
            $query .= "kks_nokks     VARCHAR(20), ";
            $query .= "kks_tglkks    DATE, ";
            $query .= "kks_kodetoko  VARCHAR(5), ";
            $query .= "kks_nonrb     VARCHAR(20), ";
            $query .= "kks_tglnrb    DATE, ";
            $query .= "kks_create_by VARCHAR(5), ";
            $query .= "kks_create_dt DATE ";
            $query .= ") ";
            DB::insert($query);
        }
    }

    private function addHistoryContainer_Idm_Omi(){
        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'TBHISTORY_CONTAINER_IDM'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TBHISTORY_CONTAINER_IDM ( ";
            $query .= "  KODEIGR    Varchar(2), ";
            $query .= "  KODETOKO   Varchar(5), ";
            $query .= "  NOPB       Varchar(10), ";
            $query .= "  TGLPB      DATE, ";
            $query .= "  JENISPB    Varchar(20), ";
            $query .= "  PLUIDM     Varchar(12), ";
            $query .= "  PLUIGR     Varchar(8), ";
            $query .= "  DESKRIPSI  Varchar(100), ";
            $query .= "  NODSPB     Varchar(30), ";
            $query .= "  TGLDSPB    DATE, ";
            $query .= "  QTYDSPB    NUMERIC, ";
            $query .= "  NORETUR    Varchar(30), ";
            $query .= "  TGLRETUR   DATE, ";
            $query .= "  QTYRETUR   NUMERIC, ";
            $query .= "  CREATE_BY  Varchar(3), ";
            $query .= "  CREATE_DT  DATE, ";
            $query .= "  MODIFY_BY  Varchar(3), ";
            $query .= "  MODIFY_DT  DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'PLU_CONTAINER_IDM'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE PLU_CONTAINER_IDM ( ";
            $query .= "  pluigr    VARCHAR(7), ";
            $query .= "  pluidm    VARCHAR(12), ";
            $query .= "  create_by VARCHAR(3), ";
            $query .= "  create_dt DATE, ";
            $query .= "  modify_by VARCHAR(3), ";
            $query .= "  modify_dt DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        $count = DB::table('PLU_CONTAINER_IDM')->count();
        if($count == 0){
            $query = '';
            $query .= "INSERT INTO plu_container_idm ( ";
            $query .= " pluigr, pluidm, create_by, create_dt  ";
            $query .= ") VALUES ( ";
            $query .= " '1372800', '20052236', 'IDM', now() ";
            $query .= ") ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.sequences')->whereRaw("upper(sequence_name) = 'SEQ_NOKOLI_CONTAINER'")->count();
        if($count == 0){
            DB::select("CREATE SEQUENCE SEQ_NOKOLI_CONTAINER START WITH 1 INCREMENT BY 1 MINVALUE 1 MAXVALUE 9999 CYCLE");
        }

        $count = DB::table('information_schema.sequences')->whereRaw("upper(sequence_name) = 'SEQ_BA_BRONJONG'")->count();
        if($count == 0){
            DB::select("CREATE SEQUENCE SEQ_BA_BRONJONG START WITH 1 INCREMENT BY 1 MINVALUE 1 MAXVALUE 99999 CYCLE");
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'TBTR_BA_BRONJONG'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TBTR_BA_BRONJONG ( ";
            $query .= "  BAB_ID           Varchar(7), ";
            $query .= "  BAB_NODOC        Varchar(20), ";
            $query .= "  BAB_TGLDOC       DATE, ";
            $query .= "  BAB_KODETOKO     Varchar(4), ";
            $query .= "  BAB_KODEMEMBER   Varchar(10), ";
            $query .= "  BAB_NONRB        NUMERIC, ";
            $query .= "  BAB_TGLNRB       DATE, ";
            $query .= "  BAB_PBR          Varchar(8), ";
            $query .= "  BAB_TGLPBR       DATE, ";
            $query .= "  BAB_NODSPB       Varchar(30), ";
            $query .= "  BAB_TGLDSPB      DATE, ";
            $query .= "  BAB_QTYBRONJONG  NUMERIC, ";
            $query .= "  BAB_QTYDOLLY     NUMERIC, ";
            $query .= "  BAB_CREATE_BY    Varchar(3), ";
            $query .= "  BAB_CREATE_DT    DATE, ";
            $query .= "  BAB_MODIFY_BY    Varchar(3), ";
            $query .= "  BAB_MODIFY_DT    DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        $count = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'PLU_CONTAINER_OMI'")->count();
        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE PLU_CONTAINER_OMI ( ";
            $query .= "  pluigr    VARCHAR(7), ";
            $query .= "  pluomi    VARCHAR(12), ";
            $query .= "  create_by VARCHAR(3), ";
            $query .= "  create_dt DATE, ";
            $query .= "  modify_by VARCHAR(3), ";
            $query .= "  modify_dt DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        $count = DB::table('PLU_CONTAINER_OMI')->count();
        if($count == 0){
            $query = '';
            $query .= "INSERT INTO plu_container_omi ( ";
            $query .= " pluigr, pluomi, create_by, create_dt  ";
            $query .= ") VALUES ( ";
            $query .= " '1372800', '1372800', 'OMI', now() ";
            $query .= ") ";
            DB::insert($query);
        }
    }

    private function addColKardus(){
        $count1 = DB::table('information_schema.tables')->whereRaw("upper(table_name) = 'TBTR_IDMKOLI'")->count();
        $count2 = DB::table('information_schema.columns')->whereRaw("upper(table_name) = 'TBTR_IDMKOLI' AND upper(column_name) = 'IKL_KARDUS'")->count();
        if($count1 > 0 AND $count2 > 0){
            DB::select("ALTER TABLE tbtr_idmkoli ADD COLUMN IKL_KARDUS VARCHAR(2) DEFAULT 'N'");
        }
    }

    //! UPDATE MENU NAME
    // Private Sub updateMenuSPI()
    //     If flagSPI Then
    //         If flagIGR Then
    //             NonQueryOra("UPDATE tbmaster_access SET accessname = 'SPI-IN-IGR' WHERE url = 'OBI'")
    //         Else
    //             NonQueryOra("UPDATE tbmaster_access SET accessname = 'SPI' WHERE url = 'OBI'")
    //         End If
    //     Else
    //         NonQueryOra("UPDATE tbmaster_access SET accessname = 'KLIK IGR' WHERE url = 'OBI' ")
    //     End If
    // End Sub
}
