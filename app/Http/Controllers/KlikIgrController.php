<?php

namespace App\Http\Controllers;

use App\Helper\ApiFormatter;
use App\Helper\DatabaseConnection;
use App\Http\Requests\DetailKasirRequest;
use App\Http\Requests\TableRequest;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class KlikIgrController extends Controller
{

    public function __construct(Request $request){
        DatabaseConnection::setConnection(session('KODECABANG'), "PRODUCTION");
    }

    public function index(){

        $this->createTableIPP_ONL();
        $this->getKonversiItemPerishable(true);

        if(session('flagSPI')){
            $this->createTablePSP_SPI();
            $this->addColHitungUlang_SPI();
            $this->alterDPDNOIDCTN();

            $cbAutoSendHH = false;
        }else{
            $this->createLogUpdateRealisasiKlik();
            $this->alterTablePickingRakToko();

            $cbAutoSendHH = true;
        }

        //! Picking Rak Toko hanya IGRBDG
        $cbPickRakToko = false;
        if(session('KODECABANG') == '04'){
            $cbPickRakToko = true;
        }

        $this->createAlasanBatalKlik();
        $this->alterTableSendHHotomatis();
        $this->alterTableCODVA();
        $this->alterTableCODPOIN();
        $this->alterTBTR_TRANSAKSI_VA();

        if(session('flagSPI')){
            $dtUrl = DB::table('tbmaster_webservice')->where('ws_nama', 'WS_SPI')->first();
        }else{
            $dtUrl = DB::table('tbmaster_webservice')->where('ws_nama', 'WS_KLIK')->first();
        }

        if(!empty($dtUrl)){
            $urlUpdateStatusKlik = $dtUrl->ws_url . '/updatestatustrx';
            $urlUpdateRealisasiKlik = $dtUrl->ws_url . '/updtqtyrealisasi';
        }

        if(session('flagSPI')){
            if (str_contains(session('flagHHSPI'), 'H') AND !str_contains(session('flagHHSPI'), 'D')){
                $statusGroupBox = "SPI";
                $statusSiapPicking = "Siap Send HH";
                $statusSiapPacking = "Siap Packing";
                $btnSendJalur = "Send Handheld";
            }elseif(str_contains(session('flagHHSPI'), 'H') AND str_contains(session('flagHHSPI'), 'D')){
                $statusGroupBox = "SPI";
                $statusSiapPicking = "Siap Send DPD";
                $statusSiapPacking = "Siap Scanning";
                $btnSendJalur = "Send DPD";
            }else{
                $statusGroupBox = "SPI";
                $statusSiapPicking = "Siap Send Jalur";
                $statusSiapPacking = "Siap Scanning";
                $btnSendJalur = "Send Jalur";

                $btnCetakIIK = false;
                $btnPBBatal = 'List PB dan Item Batal';
            }
        }else{
            $statusGroupBox = "KLIK IGR";
            $statusSiapPicking = "Siap Send HH";
            $statusSiapPacking = "Siap packing";
            $btnSendJalur = "Send Handheld";

            $btnPBBatal = 'List Item PB Batal';
        }

        $this->bersihBersihIntransit();

        $FlagProcess = False;
        $FlagSendHH = False;
        $alamatOK = False;
        $memberOK = False;
        $btnKonfirmasiBayar = False;
        $dgv_notrans = false;

        $this->updateDataVoid();
        $this->listObi_H();

        if(session('flagSPI')){
            $this->cekPBAkanBatal();
        }

        $this->cekItemBatal(True);

        return view('menu.monitoring-web-service');
    }

    private function createTableIPP_ONL(){
        //! CREATE TABLE TBTR_DSP_SPI
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBTR_DSP_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TBTR_DSP_SPI ( ";
            $query .= "  dsp_nopb       VARCHAR(50) NOT NULL, ";
            $query .= "  dsp_tglpb      DATE         NOT NULL, ";
            $query .= "  dsp_notrans    VARCHAR(50) NOT NULL, ";
            $query .= "  dsp_kodemember VARCHAR(30) NOT NULL, ";
            $query .= "  dsp_totalbayar NUMERIC       NOT NULL, ";
            $query .= "  dsp_status     VARCHAR(10) NOT NULL, ";
            $query .= "  dsp_create_by  VARCHAR(5), ";
            $query .= "  dsp_create_dt  DATE, ";
            $query .= "  dsp_modify_by  VARCHAR(5), ";
            $query .= "  dsp_modify_dt  DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        //! CREATE TABLE TBTR_AWB_IPP
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBTR_AWB_IPP'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= " CREATE TABLE TBTR_AWB_IPP ( ";
            $query .= "   awi_noawb          VARCHAR(50), ";
            $query .= "   awi_nopb           VARCHAR(50), ";
            $query .= "   awi_noorder        VARCHAR(20), ";
            $query .= "   awi_tglorder       DATE, ";
            $query .= "   awi_kodemember     VARCHAR(20), ";
            $query .= "   awi_kodetoko       VARCHAR(4), ";
            $query .= "   awi_cost           NUMERIC, ";
            $query .= "   awi_nostruk        VARCHAR(5), ";
            $query .= "   awi_tglstruk       DATE, ";
            $query .= "   awi_cashierstation VARCHAR(2), ";
            $query .= "   awi_cashierid      VARCHAR(3), ";
            $query .= "   awi_status         VARCHAR(20), ";
            $query .= "   awi_pincode        VARCHAR(10), ";
            $query .= "   awi_ref_noorder    VARCHAR(20), ";
            $query .= "   awi_tipetransaksi  VARCHAR(10), ";
            $query .= "   awi_alasanbatal    VARCHAR(500), ";
            $query .= "   awi_attribute1     VARCHAR(500), ";
            $query .= "   awi_attribute2     VARCHAR(500), ";
            $query .= "   awi_create_by      VARCHAR(5), ";
            $query .= "   awi_create_dt      DATE, ";
            $query .= "   awi_modify_by      VARCHAR(5), ";
            $query .= "   awi_modify_dt      DATE ";
            $query .= " ) ";
            DB::insert($query);
        }

        //! CREATE TABLE TBTR_SERAHTERIMA_IPP
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBTR_SERAHTERIMA_IPP'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= " CREATE TABLE tbtr_serahterima_ipp ( ";
            $query .= "   sti_noawb        VARCHAR(50), -- TrackNum ";
            $query .= "   sti_noorder      VARCHAR(50), -- OrderNo ";
            $query .= "   sti_tipeproses   VARCHAR(20), -- ProcessType (PICKUP, RETURN, CANCEL, KEEP) ";
            $query .= "   sti_tipeorder    VARCHAR(20), -- OrderType (GROCERY) ";
            $query .= "   sti_detailorder  VARCHAR(20), -- OrderTypeDetail (KLIKINDOGROSIR) ";
            $query .= "   sti_tipekirim    VARCHAR(20), -- ExpressType (EXPRESS) ";
            $query .= "   sti_pengirim     VARCHAR(100), -- ShName ";
            $query .= "   sti_penerima     VARCHAR(100), -- CoName ";
            $query .= "   sti_flagbulky    VARCHAR(1), -- FlgBulky ";
            $query .= "   sti_pin          VARCHAR(20), -- Inputan PIN ";
            $query .= "  ";
            $query .= "   sti_noserahterima  VARCHAR(20), -- NoSerahTerima ";
            $query .= "   sti_tglserahterima DATE, ";
            $query .= "   sti_senderCompany  VARCHAR(10), -- IGR, klo pihak IPP di-NULL-in ";
            $query .= "   sti_senderType     VARCHAR(10), -- STO, klo pihak IPP di-NULL-in ";
            $query .= "   sti_senderCode     VARCHAR(10), -- IGRXX (XX = KodeIgr), klo pihak IPP di-NULL-in ";
            $query .= "   sti_senderNIK      VARCHAR(20), -- NIK Petugas yang menyerahkan ";
            $query .= "   sti_senderName     VARCHAR(50), -- Nama Petugas yang menyerahkan   ";
            $query .= "  ";
            $query .= "   sti_receiverCompany  VARCHAR(10), -- IGR, klo pihak IPP di-NULL-in ";
            $query .= "   sti_receiverType     VARCHAR(10), -- STO, klo pihak IPP di-NULL-in ";
            $query .= "   sti_receiverCode     VARCHAR(10), -- IGRXX (XX = KodeIgr), klo pihak IPP di-NULL-in ";
            $query .= "   sti_receiverNIK      VARCHAR(20), -- NIK Petugas yang menerima ";
            $query .= "   sti_receiverName     VARCHAR(50), -- Nama Petugas yang menerima   ";
            $query .= "   sti_codvalue         NUMBER, ";
            $query .= "   sti_codpaymentcode   VARCHAR(100), ";
            $query .= "   sti_codpaymentbiller VARCHAR(100),   ";
            $query .= "  ";
            $query .= "   sti_create_by    VARCHAR(3), ";
            $query .= "   sti_create_dt    DATE, ";
            $query .= "   sti_modify_by    VARCHAR(3), ";
            $query .= "   sti_modify_dt    DATE ";
            $query .= " ) ";
            DB::insert($query);
        }

        //! ADD COLUMN TBTR_SERAHTERIMA_IPP - STI_CODVALUE
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_SERAHTERIMA_IPP'")
            ->whereRaw("upper(column_name) = 'STI_CODVALUE'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_SERAHTERIMA_IPP ADD COLUMN STI_CODVALUE NUMERIC");
        }

        //! ADD COLUMN TBTR_SERAHTERIMA_IPP - STI_CODPAYMENTCODE
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_SERAHTERIMA_IPP'")
            ->whereRaw("upper(column_name) = 'STI_CODPAYMENTCODE'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_SERAHTERIMA_IPP ADD COLUMN STI_CODPAYMENTCODE VARCHAR(100)");
        }

        //! ADD COLUMN TBTR_SERAHTERIMA_IPP - STI_CODPAYMENTBILLER
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_SERAHTERIMA_IPP'")
            ->whereRaw("upper(column_name) = 'STI_CODPAYMENTBILLER'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_SERAHTERIMA_IPP ADD COLUMN STI_CODPAYMENTBILLER VARCHAR(100)");
        }

        //! ADD COLUMN TBTR_SERAHTERIMA_IPP - STI_PAYMENTTYPE
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_SERAHTERIMA_IPP'")
            ->whereRaw("upper(column_name) = 'STI_PAYMENTTYPE'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_SERAHTERIMA_IPP ADD COLUMN STI_PAYMENTTYPE VARCHAR(100)");
        }

        //! ADD COLUMN TBTR_SERAHTERIMA_IPP - STI_DRIVERID
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_SERAHTERIMA_IPP'")
            ->whereRaw("upper(column_name) = 'STI_DRIVERID'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_SERAHTERIMA_IPP ADD COLUMN STI_DRIVERID VARCHAR(20)");
        }

        //! ADD COLUMN TBTR_SERAHTERIMA_IPP - STI_DRIVERNAME
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_SERAHTERIMA_IPP'")
            ->whereRaw("upper(column_name) = 'STI_DRIVERNAME'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_SERAHTERIMA_IPP ADD COLUMN STI_DRIVERNAME VARCHAR(50)");
        }

        //! ADD COLUMN TBTR_SERAHTERIMA_IPP - STI_DRIVERPHONE
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_SERAHTERIMA_IPP'")
            ->whereRaw("upper(column_name) = 'STI_DRIVERPHONE'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_SERAHTERIMA_IPP ADD COLUMN STI_DRIVERPHONE VARCHAR(20)");
        }

        //! ADD COLUMN TBTR_SERAHTERIMA_IPP - STI_VEHICLENO
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_SERAHTERIMA_IPP'")
            ->whereRaw("upper(column_name) = 'STI_VEHICLENO'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_SERAHTERIMA_IPP ADD COLUMN STI_VEHICLENO VARCHAR(20)");
        }

        //! CREATE TABLE TBTR_SERAHTERIMA_IPP
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBTR_SERAHTERIMA_IPP'")
            ->count();
        if($count == 0){

        }

        //! CREATE TABLE LOG_SERAHTERIMA_IPP
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'LOG_SERAHTERIMA_IPP'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= " CREATE TABLE LOG_SERAHTERIMA_IPP ( ";
            $query .= "   pin       VARCHAR(20), -- Pin IPP ";
            $query .= "   jenis     VARCHAR(50), -- CheckTransaction / UpdateStatus ";
            $query .= "   url       VARCHAR(200), -- Url ";
            $query .= "   parameter VARCHAR(4000), -- parameter in string ";
            $query .= "   response  VARCHAR(4000), -- response in string ";
            $query .= "   create_by VARCHAR(3), ";
            $query .= "   create_dt DATE ";
            $query .= " ) ";
            DB::insert($query);
        }

        //! CREATE TABLE TBMASTER_BTTB
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBMASTER_BTTB'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= " CREATE TABLE TBMASTER_BTTB ( ";
            $query .= "   bttb_kodeigr   VARCHAR(10), ";
            $query .= "   bttb_namaigr   VARCHAR(100), ";
            $query .= "   bttb_transaksi VARCHAR(10), ";
            $query .= "   bttb_noorder   VARCHAR(100), ";
            $query .= "   bttb_noawb     VARCHAR(100), ";
            $query .= "   bttb_create_dt DATE, ";
            $query .= "   bttb_create_by VARCHAR(5) ";
            $query .= " ) ";
            DB::insert($query);
        }

        //! CREATE TABLE TBMASTER_CREDENTIAL
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBMASTER_CREDENTIAL'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= " CREATE TABLE TBMASTER_CREDENTIAL ( ";
            $query .= "   CRE_TYPE   VARCHAR(100), ";
            $query .= "   CRE_NAME   VARCHAR(100), ";
            $query .= "   CRE_KEY    VARCHAR(200) ";
            $query .= " ) ";
            DB::insert($query);
        }

        //! CEK URL IPP_SPI DI TBMASTER_WEBSERVICE
        $count = DB::table('tbmaster_webservice')
            ->whereRaw("upper(ws_nama) = 'IPP_SPI'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= "INSERT INTO TBMASTER_WEBSERVICE ( ";
            $query .= "  ws_id, ws_nama, ws_url, ws_aktif, ws_gudang, ws_create_by, ws_create_dt, ws_dc, ws_itdp ";
            $query .= ") ";
            $query .= "SELECT '18', ";
            $query .= "       'IPP_SPI', ";
            $query .= "       'https://apispi-dev.klikindogrosir.com/api', ";
            $query .= "       1, ";
            $query .= "       ws_gudang, ";
            $query .= "       ws_create_by, ";
            $query .= "       NOW(), ";
            $query .= "       ws_dc, ";
            $query .= "       ws_itdp ";
            $query .= "FROM tbmaster_webservice ";
            $query .= "LIIT 1 ";
            DB::insert($query);
        }

        //! INSERT SPI_IPP - TBMASTER_CREDENTIAL
        $count = DB::table('tbmaster_credential')
            ->whereRaw("upper(cre_type) = 'IPP_SPI'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= " INSERT INTO tbmaster_credential ( ";
            $query .= "   cre_type,  ";
            $query .= "   cre_name,  ";
            $query .= "   cre_key ";
            $query .= " )  ";
            $query .= " VALUES ( ";
            $query .= "   'IPP_SPI',  ";
            $query .= "   'X-api-key',  ";
            $query .= "   'p2lbgWkFrykA4QyUmpHihzmc5BNAi3s' ";
            $query .= " ) ";
            DB::insert($query);
        }

        //! CEK URL IPP_KLIK DI TBMASTER_WEBSERVICE
        $count = DB::table('tbmaster_webservice')
            ->whereRaw("upper(ws_nama) = 'IPP_KLIK'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= "INSERT INTO TBMASTER_WEBSERVICE ( ";
            $query .= "  ws_id, ws_nama, ws_url, ws_aktif, ws_gudang, ws_create_by, ws_create_dt, ws_dc, ws_itdp ";
            $query .= ") ";
            $query .= "SELECT '19', ";
            $query .= "       'IPP_KLIK', ";
            $query .= "       'https://klikigrsim.mitraindogrosir.co.id/api', ";
            $query .= "       1, ";
            $query .= "       ws_gudang, ";
            $query .= "       ws_create_by, ";
            $query .= "       NOW(), ";
            $query .= "       ws_dc, ";
            $query .= "       ws_itdp ";
            $query .= "FROM tbmaster_webservice ";
            $query .= "LIMIT 1 ";
            DB::insert($query);
        }

        //! INSERT IPP_KLIK - TBMASTER_CREDENTIAL
        $count = DB::table('tbmaster_credential')
            ->whereRaw("upper(cre_type) = 'IPP_KLIK'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= " INSERT INTO tbmaster_credential ( ";
            $query .= "   cre_type,  ";
            $query .= "   cre_name,  ";
            $query .= "   cre_key ";
            $query .= " )  ";
            $query .= " VALUES ( ";
            $query .= "   'IPP_KLIK',  ";
            $query .= "   'X-api-key',  ";
            $query .= "   'cDJsYmdXa0ZyeWtBNFF5VW1wSGloe' ";
            $query .= " ) ";
            DB::insert($query);
        }

        //! CEK URL IPP_SPI DI TBMASTER_WEBSERVICE
        $count = DB::table('tbmaster_webservice')
            ->whereRaw("upper(ws_nama) = 'WS_SPI'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= "INSERT INTO TBMASTER_WEBSERVICE ( ";
            $query .= "  ws_id, ws_nama, ws_url, ws_aktif, ws_gudang, ws_create_by, ws_create_dt, ws_dc, ws_itdp ";
            $query .= ") ";
            $query .= "SELECT '23', ";
            $query .= "       'WS_SPI', ";
            $query .= "       'https://apispi-dev.klikindogrosir.com/api', ";
            $query .= "       1, ";
            $query .= "       ws_gudang, ";
            $query .= "       ws_create_by, ";
            $query .= "       NOW(), ";
            $query .= "       ws_dc, ";
            $query .= "       ws_itdp ";
            $query .= "FROM tbmaster_webservice ";
            $query .= "LIMIT 1 ";
            DB::insert($query);
        }

        //! INSERT WS_IPP - TBMASTER_CREDENTIAL
        $count = DB::table('tbmaster_credential')
            ->whereRaw("upper(cre_type) = 'WS_SPI'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= " INSERT INTO tbmaster_credential ( ";
            $query .= "   cre_type,  ";
            $query .= "   cre_name,  ";
            $query .= "   cre_key ";
            $query .= " )  ";
            $query .= " VALUES ( ";
            $query .= "   'WS_SPI',  ";
            $query .= "   'X-api-key',  ";
            $query .= "   'p2lbgWkFrykA4QyUmpHihzmc5BNAi3s' ";
            $query .= " ) ";
            DB::insert($query);
        }

        //! CEK URL WS_KLIK DI TBMASTER_WEBSERVICE
        $count = DB::table('tbmaster_webservice')
            ->whereRaw("upper(ws_nama) = 'WS_KLIK'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= "INSERT INTO TBMASTER_WEBSERVICE ( ";
            $query .= "  ws_id, ws_nama, ws_url, ws_aktif, ws_gudang, ws_create_by, ws_create_dt, ws_dc, ws_itdp ";
            $query .= ") ";
            $query .= "SELECT '24', ";
            $query .= "       'WS_KLIK', ";
            $query .= "       'https://klikigrsim.mitraindogrosir.co.id/api', ";
            $query .= "       1, ";
            $query .= "       ws_gudang, ";
            $query .= "       ws_create_by, ";
            $query .= "       NOW(), ";
            $query .= "       ws_dc, ";
            $query .= "       ws_itdp ";
            $query .= "FROM tbmaster_webservice ";
            $query .= "LIMIT 1 ";
            DB::insert($query);
        }

        //! INSERT WS_IPP - TBMASTER_CREDENTIAL
        $count = DB::table('tbmaster_credential')
            ->whereRaw("upper(cre_type) = 'WS_KLIK'")
            ->count();
        if($count == 0){
            $query = '';
            $query .= " INSERT INTO tbmaster_credential ( ";
            $query .= "   cre_type,  ";
            $query .= "   cre_name,  ";
            $query .= "   cre_key ";
            $query .= " )  ";
            $query .= " VALUES ( ";
            $query .= "   'WS_KLIK',  ";
            $query .= "   'X-api-key',  ";
            $query .= "   'cDJsYmdXa0ZyeWtBNFF5VW1wSGloe' ";
            $query .= " ) ";
            DB::insert($query);
        }
    }

    //! BELUM SELESAI
    private function getKonversiItemPerishable($flagMsg){

        //! CEK HARI INI UDAH PERNAH GET DATA KONVERSI ITEM PERISHABLE
        $cek =  DB::table('log_konversi_klikigr')
            ->whereRaw("WHERE DATE_TRUNC('DAY',create_dt) = DATE_TRUNC('DAY',CURRENT_DATE)")
            ->count();
        if($cek > 0 AND $flagMsg == true){
            $message = 'Hari ini sudah udah pernah get data konversi item perishable';
            throw new HttpResponseException(ApiFormatter::error(500, $message));
        }

        //! CEK URL KONVERSI_KLIKIGR DI TBMASTER_WEBSERVICE
        $cek = DB::table('tbmaster_webservice')
            ->whereRaw("WHERE upper(ws_nama) = 'KONVERSI_KLIKIGR'")
            ->first();
        if(empty($cek) || $cek->ws_url == null){
            $message = 'Webservice Konversi Item Klikigr belum terdaftar';
            throw new HttpResponseException(ApiFormatter::error(500, $message));
        }

        $url = $cek->ws_url;

        //! BELUM DI MIGRATE
        $this->ConToWebService($url);

        //! INSERT LOG_KONVERSI_KLIKIGR
        DB::table('log_konversi_klikigr')
            ->insert([
                'kodeigr' => session('KODECABANG'),
                'url' => $url,
                'response' => '', //! harusnya dari response ConToWebService
                'ip' => $this->getIP(),
                'create_by' => session('userid'),
                'create_dt' => Carbon::now(),
            ]);

        //! BELUM BERES MASIH AGAK BINGUNG
    }

    private function alterDPDNOIDCTN(){
        //! ADD COLUMN NO_NOIDCTN
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBMASTER_NOID'")
            ->whereRaw("upper(column_name) = 'NO_NOIDCTN'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBMASTER_NOID ADD COLUMN NO_NOIDCTN VARCHAR(10)");
        }

        //! ADD COLUMN LKS_NOIDCTN
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBMASTER_LOKASI'")
            ->whereRaw("upper(column_name) = 'LKS_NOIDCTN'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBMASTER_LOKASI ADD COLUMN LKS_NOIDCTN VARCHAR(10)");
        }
    }

    private function createLogUpdateRealisasiKlik(){
        //! CREATE NEW LOG_ALASAN_BATAL
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'LOG_OBI_REALISASI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE LOG_OBI_REALISASI ( ";
            $query .= "    NOTRANS      VARCHAR(10), ";
            $query .= "    TGLTRANS     DATE, ";
            $query .= "    NOPB         VARCHAR(50), ";
            $query .= "    CREATE_BY    VARCHAR(5), ";
            $query .= "    CREATE_DT    DATE, ";
            $query .= "    MODIFY_BY    VARCHAR(5), ";
            $query .= "    MODIFY_DT    DATE, ";
            $query .= "    URL          VARCHAR(500), ";
            $query .= "    PARAMETER    VARCHAR(500), ";
            $query .= "    RESPONSE     VARCHAR(2000) ";
            $query .= ") ";
            DB::select($query);
        }
    }

    private function alterTablePickingRakToko(){
        //! ADD COLUMN PRS_FLAG_PICKINGKLIK
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBMASTER_PERUSAHAAN'")
            ->whereRaw("upper(column_name) = 'PRS_FLAG_PICKINGKLIK'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBMASTER_PERUSAHAAN ADD COLUMN PRS_FLAG_PICKINGKLIK VARCHAR(1) DEFAULT 'N'");
        }
    }

    private function createAlasanBatalKlik(){
        //! CREATE TABLE LOG_ALASAN_BATAL
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'LOG_ALASAN_BATAL'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE LOG_ALASAN_BATAL ( ";
            $query .= "    NOTRANS      VARCHAR(10), ";
            $query .= "    TGLTRANS     DATE, ";
            $query .= "    NOPB         VARCHAR(50), ";
            $query .= "    ALASAN_BATAL VARCHAR(300), ";
            $query .= "    CREATE_BY    VARCHAR(5), ";
            $query .= "    CREATE_DT    DATE, ";
            $query .= "    MODIFY_BY    VARCHAR(5), ";
            $query .= "    MODIFY_DT    DATE, ";
            $query .= "    URL          VARCHAR(500), ";
            $query .= "    RESPONSE     VARCHAR(2000) ";
            $query .= ") ";
            DB::select($query);
        }
    }

    private function alterTableSendHHotomatis(){
        //! ADD COLUMN OBI_FLAGSENDHH
        $count = DB::table('information_schema.columns')
        ->whereRaw("upper(table_name) = 'TBTR_OBI_H'")
        ->whereRaw("upper(column_name) = 'OBI_FLAGSENDHH'")
        ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_OBI_H ADD COLUMN OBI_FLAGSENDHH VARCHAR(2) DEFAULT 'N'");
        }
    }

    private function alterTableCODVA(){
        //! ADD COLUMN DEL_PINCOD
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_PINCOD'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_PINCOD VARCHAR(7)");
        }
    }

    private function alterTableCODPOIN(){
        //! PAYMENT_KLIKIGR
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'PAYMENT_KLIKIGR'")
            ->whereRaw("upper(column_name) = 'COD_NONPAID'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE PAYMENT_KLIKIGR ADD COD_NONPAID VARCHAR2(2)");
        }

        //! TBTR_DSP_SPI
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DSP_SPI'")
            ->whereRaw("upper(column_name) = 'DSP_TOTALDSP'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DSP_SPI ADD DSP_TOTALDSP NUMBER");
        }

        //! TBTR_DELIVERY_SPI
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_NILAICOD'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD DEL_NILAICOD NUMBER");
        }
    }

    private function alterTBTR_TRANSAKSI_VA(){
        //! COLUMN TVA_URL DI TBTR_TRANSAKSI_VA
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_TRANSAKSI_VA'")
            ->whereRaw("upper(column_name) = 'TVA_URL'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_TRANSAKSI_VA ADD COLUMN TVA_URL VARCHAR(100)");
        }
    }
}
