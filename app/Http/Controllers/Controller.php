<?php

namespace App\Http\Controllers;

use App\Exports\GeneralExcelExport;
use App\Helper\ApiFormatter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Maatwebsite\Excel\Facades\Excel;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getIP(){
        return '127.0.0.4'; //! dummy
        return $_SERVER['REMOTE_ADDR'];
    }

    public function getPaketIPP($kodetoko){
        $data = DB::select("select coalesce(CLS_PAKETIPP,'0') as data FROM CLUSTER_IDM WHERE CLS_TOKO = '" . $kodetoko . "'");
        if(count($data) == 0){
            return null;
        }

        return $data[0]['data'];
    }

    //! NOTE KEVIN
    //? INI KARENA HIT ENDPOINT LANGSUNG RETURN TRUE AJA
    public function insertToNPB($cabang, $NamaFile, $dtH, $dtD){
        return true;
    }

    public function writeCSV($tempDir, $nameFile, $data){
        $fileContent = Excel::raw(new GeneralExcelExport($data), \Maatwebsite\Excel\Excel::CSV);
        file_put_contents($tempDir . '/zip/' . $nameFile . ".csv", $fileContent);
    }


    //? use on logUpdateStatus
    public function updateStatus($status, $noPB){
        if(session('flagSPI') == true){
            $dtCek = DB::table('tbmaster_webservice')->where('ws_nama', 'WS_SPI')->first();
            $dtApiKey = DB::table('tbmaster_credential')->selectRaw("cre_name as api_name, cre_key as api_key")->where('cre_type', 'WS_SPI')->first();
        }else{
            $dtCek = DB::table('tbmaster_webservice')->where('ws_nama', 'WS_KLIK')->first();
            $dtApiKey = DB::table('tbmaster_credential')->selectRaw("cre_name as api_name, cre_key as api_key")->where('cre_type', 'WS_KLIK')->first();
        }

        if(empty($dtCek) OR empty($dtApiKey)){
            $message = 'Credential di tbmaster_webservice atau tbmaster_credential tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $url = $dtCek->ws_url . '/updatestatustrx';
        $apiName = $dtApiKey->api_name;
        $apiKey = $dtApiKey->api_key;

        try {

            $noTrans = explode('/', $noPB)[0];

            $postData = [
                'trxid' => substr($noPB, 0, 6),
                'statusid' => $status,
            ];

            $this->ConToWebServiceNew($url, $apiName, $apiKey, $postData);

            return true;

        }catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }
    }

    public function ORADataFound($table, $where){
        $cek = DB::table($table)
            ->whereRaw($where)
            ->count();

        if($cek == 0){
            return false;
        }else{
            return true;
        }
    }

    //! NOTE KEVIN
    //? INFO DARI PIHAK IDM PROSES DI COMMENT AJA
    //? LANGSUNG RETURN TRUE
    //? FR KEVIN (INFO PAK EVAN 02/05/2024)
    public function ConToWebService($endpoint, $postData = []){

        // $headers = [
        //     'X-Authorization' => '4b8bf8518b027f7adbf0e6c367ccb204b397566e',
        // ];

        // $body = $postData;

        // //* Make the HTTP request
        // $response = Http::withHeaders($headers)
        //     ->post($endpoint, $body);

        // //* get data
        // if($response->status() == 200){
        //     return true;
        // }else{
        //     $message = "Proses update status gagal, terjadi kesalahan pada Web Service ($response)";
        //     throw new HttpResponseException(ApiFormatter::error(400, $message));
        // }

        return true;
    }

    //! NOTE KEVIN
    //? INFO DARI PIHAK IDM PROSES DI COMMENT AJA
    //? LANGSUNG RETURN TRUE
    //? FR KEVIN (INFO PAK EVAN 02/05/2024)
    public function ConToWebServiceNew($endpoint, $apiName, $apiKey, $postData = []){

        // $headers = [
        //     'Authorization' => 'Bearer your_access_token',
        //     $apiName => $apiKey,
        // ];

        // $body = $postData;

        // // Make the HTTP request
        // $response = Http::withHeaders($headers)
        //     ->post($endpoint, $body);

        // //get data
        // if($response->status() == 200){
        //     return true;
        // }else{
        //     $message = "Proses update status gagal, terjadi kesalahan pada Web Service ($response)";
        //     throw new HttpResponseException(ApiFormatter::error(400, $message));
        // }

        return true;
    }

    public function logUpdateStatus($notrans, $tgltrans, $nopb, $statusBaru, $statusKlik){

        $dtUrl = DB::table('tbmaster_webservice')->where('ws_nama', 'WS_SPI')->first();
        $urlUpdateStatusKlik = $dtUrl->ws_url . '/updatestatustrx';

        if (str_contains($nopb, 'TMI')) {
            return;
        }

        $noTrx = explode('/', $nopb)[0];

        $flag = '1';
        if($this->updateStatus($statusKlik, $nopb) == true){
            $flag = '0';
        }

        DB::table('log_obi_status')
            ->insert([
                'notrans' => $notrans,
                'tgltrans' => Carbon::parse($tgltrans)->format('Y-m-d H:i:s'),
                'nopb' => $nopb,
                'notrx_klik' => $noTrx,
                'status_baru' => $statusBaru,
                'flag' => $flag,
                'url' => $urlUpdateStatusKlik,
                'status_klik' => $statusKlik,
                'response' => 'response dummy', //! NOTE KEVIN | GET RESPONSE DARI ConToWebServiceNew
                'create_by' => session('userid'),
                'create_dt' => Carbon::now(),
            ]);
    }

    //* SPI - DIPAKE DI KLIK IGR
    public function createTablePSP_SPI(){

        //* CEK SEQUENCE SEQ_PICKING_SPI
        $count = DB::table('information_schema.sequences')
            ->whereRaw("upper(sequence_name) = 'SEQ_PICKING_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE SEQUENCE SEQ_PICKING_SPI ";
            $query .= "  START WITH 1  ";
            $query .= "  MAXVALUE 9999999 ";
            $query .= "  MINVALUE 1 ";
            $query .= "  CYCLE ";
            DB::insert($query);
        }

        //* CREATE TABLE TBMASTER_GROUPRAK
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBMASTER_GROUPRAK'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TBMASTER_GROUPRAK ( ";
            $query .= "  GRR_KODEIGR     VARCHAR(2), ";
            $query .= "  GRR_FLAGCETAKAN VARCHAR(1), ";
            $query .= "  GRR_GROUPRAK    VARCHAR(5), ";
            $query .= "  GRR_NAMAGROUP   VARCHAR(20), ";
            $query .= "  GRR_KODERAK     VARCHAR(7), ";
            $query .= "  GRR_SUBRAK      VARCHAR(3), ";
            $query .= "  GRR_NOURUT      NUMERIC(2), ";
            $query .= "  GRR_CREATE_BY   VARCHAR(3), ";
            $query .= "  GRR_CREATE_DT   DATE, ";
            $query .= "  GRR_MODIFY_BY   VARCHAR(3), ";
            $query .= "  GRR_MODIFY_DT   DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE TEMP_DPD_IDM
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TEMP_DPD_IDM'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TEMP_DPD_IDM ( ";
            $query .= "  FMNDOC    Varchar(9), ";
            $query .= "  FMNBTC    Varchar(1), ";
            $query .= "  TRNRAK    Varchar(36), ";
            $query .= "  FMNTRN    Varchar(7), ";
            $query .= "  PRDCD     Varchar(7), ";
            $query .= "  BARC      Varchar(20), ";
            $query .= "  BAR2      Varchar(20), ";
            $query .= "  BARK      Varchar(20), ";
            $query .= "  GRAK      Varchar(5), ";
            $query .= "  NOUR      NUMERIC(2), ";
            $query .= "  KODERAK   Varchar(18), ";
            $query .= "  NOID      Varchar(5), ";
            $query .= "  SATUAN    Varchar(8), ";
            $query .= "  FMKSBU    Varchar(1), ";
            $query .= "  FMKCAB    Varchar(4), ";
            $query .= "  'desc'  Varchar(20), ";
            $query .= "  DESC2     Varchar(75), ";
            $query .= "  QTYO      NUMERIC(7), ";
            $query .= "  STOK      NUMERIC(12), ";
            $query .= "  QTYR      NUMERIC(7), ";
            $query .= "  FMSTS     Varchar(1), ";
            $query .= "  'time'  Varchar(8), ";
            $query .= "  REQ_ID    Varchar(30) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE DPD_IDM_NOBARCODE
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'DPD_IDM_NOBARCODE'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE DPD_IDM_NOBARCODE ( ";
            $query .= "  FMNDOC    VARCHAR(9), ";
            $query .= "  FMNBTC    VARCHAR(1), ";
            $query .= "  TRNRAK    VARCHAR(36), ";
            $query .= "  FMNTRN    VARCHAR(7), ";
            $query .= "  PRDCD     VARCHAR(7), ";
            $query .= "  BARC      VARCHAR(20), ";
            $query .= "  BAR2      VARCHAR(20), ";
            $query .= "  BARK      VARCHAR(20), ";
            $query .= "  GRAK      VARCHAR(5), ";
            $query .= "  NOUR      NUMERIC(2), ";
            $query .= "  KODERAK   VARCHAR(18), ";
            $query .= "  NOID      VARCHAR(5), ";
            $query .= "  SATUAN    VARCHAR(8), ";
            $query .= "  FMKSBU    VARCHAR(1), ";
            $query .= "  FMKCAB    VARCHAR(4), ";
            $query .= "  'DESC'  VARCHAR(20), ";
            $query .= "  DESC2     VARCHAR(75), ";
            $query .= "  QTYO      NUMERIC(7), ";
            $query .= "  STOK      NUMERIC(12), ";
            $query .= "  QTYR      NUMERIC(7), ";
            $query .= "  FMSTS     VARCHAR(1), ";
            $query .= "  'TIME'  VARCHAR(8), ";
            $query .= "  REQ_ID    VARCHAR(30) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE TEMP_HHELD_IDM
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TEMP_HHELD_IDM'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TEMP_HHELD_IDM ( ";
            $query .= "  FMNDOC    VARCHAR(9), ";
            $query .= "  FMNBTC    VARCHAR(1), ";
            $query .= "  TRNRAK    VARCHAR(36), ";
            $query .= "  FMNTRN    VARCHAR(7), ";
            $query .= "  PRDCD     VARCHAR(7), ";
            $query .= "  BARC      VARCHAR(20), ";
            $query .= "  BAR2      VARCHAR(20), ";
            $query .= "  BARK      VARCHAR(20), ";
            $query .= "  GRAK      VARCHAR(5), ";
            $query .= "  NOUR      NUMERIC(2), ";
            $query .= "  KODERAK   VARCHAR(18), ";
            $query .= "  NOID      VARCHAR(5), ";
            $query .= "  SATUAN    VARCHAR(8), ";
            $query .= "  FMKSBU    VARCHAR(1), ";
            $query .= "  FMKCAB    VARCHAR(4), ";
            $query .= "  'DESC'  VARCHAR(20), ";
            $query .= "  DESC2     VARCHAR(75), ";
            $query .= "  QTYO      NUMERIC(7), ";
            $query .= "  STOK      NUMERIC(12), ";
            $query .= "  QTYR      NUMERIC(7), ";
            $query .= "  REQ_ID    VARCHAR(30) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE HHELD_IDM_ORA
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'HHELD_IDM_ORA'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE HHELD_IDM_ORA ( ";
            $query .= "  FMRCID       VARCHAR(1), ";
            $query .= "  FMNDOC       VARCHAR(9), ";
            $query .= "  FMNBTC       VARCHAR(1), ";
            $query .= "  TRNRAK       VARCHAR(36), ";
            $query .= "  FMNTRN       VARCHAR(7), ";
            $query .= "  PRDCD        VARCHAR(7), ";
            $query .= "  BARC         VARCHAR(14), ";
            $query .= "  BAR2         VARCHAR(14), ";
            $query .= "  BARK         VARCHAR(14), ";
            $query .= "  GRAK         VARCHAR(5), ";
            $query .= "  NOUR         NUMERIC(2), ";
            $query .= "  KODERAK      VARCHAR(18), ";
            $query .= "  NOID         VARCHAR(5), ";
            $query .= "  SATUAN       VARCHAR(8), ";
            $query .= "  FMKSBU       VARCHAR(1), ";
            $query .= "  FMKCAB       VARCHAR(4), ";
            $query .= "  'DESC'     VARCHAR(20), ";
            $query .= "  DESC2        VARCHAR(75), ";
            $query .= "  QTYO         NUMERIC(7), ";
            $query .= "  STOK         NUMERIC(12), ";
            $query .= "  QTYR         NUMERIC(7), ";
            $query .= "  REQ_ID       VARCHAR(30), ";
            $query .= "  TGLUPD       VARCHAR(8), ";
            $query .= "  JAM_UPLOAD   VARCHAR(8), ";
            $query .= "  JAM_PICKING  VARCHAR(8), ";
            $query .= "  USERID       VARCHAR(3), ";
            $query .= "  TGLPB        VARCHAR(8), ";
            $query .= "  NOPICKING    NUMERIC(7), ";
            $query .= "  NOSURATJALAN NUMERIC(5), ";
            $query .= "  PANJANG      NUMERIC(6,2), ";
            $query .= "  LEBAR        NUMERIC(6,2), ";
            $query .= "  TINGGI       NUMERIC(6,2), ";
            $query .= "  KUBIKPICKING NUMERIC(14,2), ";
            $query .= "  KODEZONA     VARCHAR(10) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE HHELD_HISTORY_IDM
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'HHELD_HISTORY_IDM'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE HHELD_HISTORY_IDM ( ";
            $query .= "  FMRCID       VARCHAR(1), ";
            $query .= "  FMNDOC       VARCHAR(9), ";
            $query .= "  TGLPB        VARCHAR(8), ";
            $query .= "  FMNBTC       VARCHAR(1), ";
            $query .= "  TRNRAK       VARCHAR(36), ";
            $query .= "  FMNTRN       VARCHAR(7), ";
            $query .= "  PRDCD        VARCHAR(7), ";
            $query .= "  BARC         VARCHAR(14), ";
            $query .= "  BAR2         VARCHAR(14), ";
            $query .= "  BARK         VARCHAR(14), ";
            $query .= "  GRAK         VARCHAR(5), ";
            $query .= "  NOUR         NUMERIC(2), ";
            $query .= "  KODERAK      VARCHAR(18), ";
            $query .= "  NOID         VARCHAR(5), ";
            $query .= "  SATUAN       VARCHAR(8), ";
            $query .= "  FMKSBU       VARCHAR(1), ";
            $query .= "  FMKCAB       VARCHAR(4), ";
            $query .= "  'DESC'     VARCHAR(20), ";
            $query .= "  DESC2        VARCHAR(75), ";
            $query .= "  QTYO         NUMERIC(7), ";
            $query .= "  STOK         NUMERIC(12), ";
            $query .= "  QTYR         NUMERIC(7), ";
            $query .= "  REQ_ID       VARCHAR(30), ";
            $query .= "  TGLUPD       VARCHAR(8), ";
            $query .= "  JAM_UPLOAD   VARCHAR(8), ";
            $query .= "  JAM_PICKING  VARCHAR(8), ";
            $query .= "  USERID       VARCHAR(3), ";
            $query .= "  NOPICKING    NUMERIC(7), ";
            $query .= "  NOSURATJALAN NUMERIC(5), ";
            $query .= "  PANJANG      NUMERIC(6,2), ";
            $query .= "  LEBAR        NUMERIC(6,2), ";
            $query .= "  TINGGI       NUMERIC(6,2), ";
            $query .= "  KUBIKPICKING NUMERIC(14,2), ";
            $query .= "  KODEZONA     VARCHAR(10) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE DPD_IDM_ORA
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'DPD_IDM_ORA'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE DPD_IDM_ORA ( ";
            $query .= "  FMRCID       VARCHAR(1), ";
            $query .= "  FMNDOC       VARCHAR(9), ";
            $query .= "  FMNBTC       VARCHAR(1), ";
            $query .= "  TRNRAK       VARCHAR(36), ";
            $query .= "  FMNTRN       VARCHAR(7), ";
            $query .= "  PRDCD        VARCHAR(7), ";
            $query .= "  BARC         VARCHAR(14), ";
            $query .= "  BAR2         VARCHAR(14), ";
            $query .= "  BARK         VARCHAR(14), ";
            $query .= "  GRAK         VARCHAR(5), ";
            $query .= "  NOUR         NUMERIC(2), ";
            $query .= "  KODERAK      VARCHAR(18), ";
            $query .= "  NOID         VARCHAR(5), ";
            $query .= "  SATUAN       VARCHAR(8), ";
            $query .= "  FMKSBU       VARCHAR(1), ";
            $query .= "  FMKCAB       VARCHAR(4), ";
            $query .= "  'DESC'     VARCHAR(20), ";
            $query .= "  DESC2        VARCHAR(75), ";
            $query .= "  QTYO         NUMERIC(7), ";
            $query .= "  STOK         NUMERIC(12), ";
            $query .= "  QTYR         NUMERIC(7), ";
            $query .= "  REQ_ID       VARCHAR(30), ";
            $query .= "  TGLUPD       VARCHAR(8), ";
            $query .= "  JAM_UPLOAD   VARCHAR(8), ";
            $query .= "  JAM_PICKING  VARCHAR(8), ";
            $query .= "  USERID       VARCHAR(3), ";
            $query .= "  TGLPB        VARCHAR(8), ";
            $query .= "  NOPICKING    NUMERIC(7), ";
            $query .= "  NOSURATJALAN NUMERIC(5), ";
            $query .= "  PANJANG      NUMERIC(6,2), ";
            $query .= "  LEBAR        NUMERIC(6,2), ";
            $query .= "  TINGGI       NUMERIC(6,2), ";
            $query .= "  KUBIKPICKING NUMERIC(14,2), ";
            $query .= "  KODEZONA     VARCHAR(10) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE DPD_HISTORY_IDM
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'DPD_HISTORY_IDM'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE DPD_HISTORY_IDM ( ";
            $query .= "  FMRCID       VARCHAR(1), ";
            $query .= "  FMNDOC       VARCHAR(9), ";
            $query .= "  TGLPB        VARCHAR(8), ";
            $query .= "  FMNBTC       VARCHAR(1), ";
            $query .= "  TRNRAK       VARCHAR(36), ";
            $query .= "  FMNTRN       VARCHAR(7), ";
            $query .= "  PRDCD        VARCHAR(7), ";
            $query .= "  BARC         VARCHAR(14), ";
            $query .= "  BAR2         VARCHAR(14), ";
            $query .= "  BARK         VARCHAR(14), ";
            $query .= "  GRAK         VARCHAR(5), ";
            $query .= "  NOUR         NUMERIC(2), ";
            $query .= "  KODERAK      VARCHAR(18), ";
            $query .= "  NOID         VARCHAR(5), ";
            $query .= "  SATUAN       VARCHAR(8), ";
            $query .= "  FMKSBU       VARCHAR(1), ";
            $query .= "  FMKCAB       VARCHAR(4), ";
            $query .= "  'DESC'     VARCHAR(20), ";
            $query .= "  DESC2        VARCHAR(75), ";
            $query .= "  QTYO         NUMERIC(7), ";
            $query .= "  STOK         NUMERIC(12), ";
            $query .= "  QTYR         NUMERIC(7), ";
            $query .= "  REQ_ID       VARCHAR(30), ";
            $query .= "  TGLUPD       VARCHAR(8), ";
            $query .= "  JAM_UPLOAD   VARCHAR(8), ";
            $query .= "  JAM_PICKING  VARCHAR(8), ";
            $query .= "  USERID       VARCHAR(3), ";
            $query .= "  NOPICKING    NUMERIC(7), ";
            $query .= "  NOSURATJALAN NUMERIC(5), ";
            $query .= "  PANJANG      NUMERIC(6,2), ";
            $query .= "  LEBAR        NUMERIC(6,2), ";
            $query .= "  TINGGI       NUMERIC(6,2), ";
            $query .= "  KUBIKPICKING NUMERIC(14,2), ";
            $query .= "  KODEZONA     VARCHAR(10) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE ZONA_IDM
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'ZONA_IDM'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE ZONA_IDM ( ";
            $query .= "  ZON_KODE      VARCHAR(10), ";
            $query .= "  ZON_NAMA      VARCHAR(40), ";
            $query .= "  ZON_RAK       VARCHAR(5), ";
            $query .= "  ZON_PRINTER   VARCHAR(50), ";
            $query .= "  ZON_THERMAL   VARCHAR(50) DEFAULT 'PRINTER THERMAL', ";
            $query .= "  ZON_CONTAINER VARCHAR(20), ";
            $query .= "  ZON_ALLOWANCE NUMERIC(5,2), ";
            $query .= "  ZON_CREATE_BY VARCHAR(3), ";
            $query .= "  ZON_CREATE_DT DATE, ";
            $query .= "  ZON_MODIFY_BY VARCHAR(3), ";
            $query .= "  ZON_MODIFY_DT DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE CONTAINER_IDM
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'CONTAINER_IDM'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE CONTAINER_IDM ( ";
            $query .= "  CON_JENIS      VARCHAR(20), ";
            $query .= "  CON_PANJANG    NUMERIC(6,2), ";
            $query .= "  CON_LEBAR      NUMERIC(6,2), ";
            $query .= "  CON_TINGGI     NUMERIC(6,2), ";
            $query .= "  CON_VOLUME     NUMERIC(15), ";
            $query .= "  CON_CREATE_BY  VARCHAR(3), ";
            $query .= "  CON_CREATE_DT  DATE, ";
            $query .= "  CON_MODIFY_BY  VARCHAR(3), ";
            $query .= "  CON_MODIFY_DT  DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CEK DATA CONTAINER_IDM - CONTAINER
        $count = DB::table('container_idm')
            ->whereRaw("upper(con_jenis) = 'CONTAINER'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "INSERT INTO CONTAINER_IDM ( ";
            $query .= "  CON_JENIS, CON_PANJANG, CON_LEBAR, CON_TINGGI, CON_VOLUME, CON_CREATE_BY, CON_CREATE_DT ";
            $query .= ") ";
            $query .= "VALUES ( ";
            $query .= "  'CONTAINER', 60, 40, 31, 74400, 'SYS', NOW() ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CEK DATA CONTAINER_IDM - BRONJONG
        $count = DB::table('container_idm')
            ->whereRaw("upper(con_jenis) = 'BRONJONG'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "INSERT INTO container_idm ( ";
            $query .= "  CON_JENIS, CON_PANJANG, CON_LEBAR, CON_TINGGI, CON_VOLUME, CON_CREATE_BY, CON_CREATE_DT ";
            $query .= ") ";
            $query .= "VALUES ( ";
            $query .= "  'BRONJONG', 74, 57, 141, 594738, 'SYS', NOW() ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE PICKING_ANTRIAN
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'PICKING_ANTRIAN'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE PICKING_ANTRIAN ( ";
            $query .= "  PIA_RECORDID    VARCHAR(1), ";
            $query .= "  PIA_NOPICK      VARCHAR(7), ";
            $query .= "  PIA_TGLPICK     DATE, ";
            $query .= "  PIA_NOSJ        VARCHAR(7), ";
            $query .= "  PIA_KODETOKO    VARCHAR(5), ";
            $query .= "  PIA_KODEZONA    VARCHAR(10), ";
            $query .= "  PIA_GROUPRAK    VARCHAR(5), ";
            $query .= "  PIA_NOURUTPAKET NUMERIC(4), ";
            $query .= "  PIA_NOURUTTOTAL NUMERIC(4), ";
            $query .= "  PIA_JALURKOSONG VARCHAR(1) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE PICKING_CONTAINER
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'PICKING_CONTAINER'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE PICKING_CONTAINER ( ";
            $query .= "  PICO_RECORDID      VARCHAR(1), ";
            $query .= "  PICO_PRINTERNAME   VARCHAR(50), ";
            $query .= "  PICO_NOPICK        VARCHAR(7), ";
            $query .= "  PICO_TGLPICK       VARCHAR(10), ";
            $query .= "  PICO_CONTAINERZONA VARCHAR(20), ";
            $query .= "  PICO_GATE          VARCHAR(10), ";
            $query .= "  PICO_KODETOKO      VARCHAR(5), ";
            $query .= "  PICO_NAMATOKO      VARCHAR(30), ";
            $query .= "  PICO_BARCODEKOLI   VARCHAR(15), ";
            $query .= "  PICO_NOURUTTOKO    VARCHAR(4), ";
            $query .= "  PICO_JUMLAHTOKO    VARCHAR(4), ";
            $query .= "  PICO_REPRINT       NUMERIC(3), ";
            $query .= "  PICO_NOSJ          NUMBER ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE DPD_DATA_IDM
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'DPD_DATA_IDM'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE DPD_DATA_IDM ( ";
            $query .= "  DDI_KODEJALUR  VARCHAR(20), ";
            $query .= "  DDI_NOID       VARCHAR(6), ";
            $query .= "  DDI_NOPB       VARCHAR(9), ";
            $query .= "  DDI_TGLPB      VARCHAR(8), ";
            $query .= "  DDI_KODETOKO   VARCHAR(4), ";
            $query .= "  DDI_BATCH      VARCHAR(1), ";
            $query .= "  DDI_PRDCD1     VARCHAR(7), ";
            $query .= "  DDI_UNIT1      VARCHAR(3), ";
            $query .= "  DDI_FRAC1      NUMERIC(4), ";
            $query .= "  DDI_FLAGBKP1   VARCHAR(1), ";
            $query .= "  DDI_FLAGDF1    VARCHAR(1), ";
            $query .= "  DDI_QTYO1      NUMERIC(8), ";
            $query .= "  DDI_QTYP1      NUMERIC(8), ";
            $query .= "  DDI_PRDCD2     VARCHAR(7), ";
            $query .= "  DDI_UNIT2      VARCHAR(3), ";
            $query .= "  DDI_FRAC2      NUMERIC(4), ";
            $query .= "  DDI_FLAGBKP2   VARCHAR(1), ";
            $query .= "  DDI_FLAGDF2    VARCHAR(1), ";
            $query .= "  DDI_QTYO2      NUMERIC(8), ";
            $query .= "  DDI_QTYP2      NUMERIC(8), ";
            $query .= "  DDI_PRDCD3     VARCHAR(7), ";
            $query .= "  DDI_UNIT3      VARCHAR(3), ";
            $query .= "  DDI_FRAC3      NUMERIC(4), ";
            $query .= "  DDI_FLAGBKP3   VARCHAR(1), ";
            $query .= "  DDI_FLAGDF3    VARCHAR(1), ";
            $query .= "  DDI_QTYO3      NUMERIC(8), ";
            $query .= "  DDI_QTYP3      NUMERIC(8), ";
            $query .= "  DDI_TGLUPDATE  VARCHAR(8), ";
            $query .= "  DDI_JAMUPDATE  VARCHAR(8), ";
            $query .= "  DDI_IP         VARCHAR(30) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE DPD_MASTER_IGR
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'DPD_MASTER_IGR'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE DPD_MASTER_IGR ( ";
            $query .= "  DMI_KODEJALUR  VARCHAR(10), ";
            $query .= "  DMI_PORT       VARCHAR(10), ";
            $query .= "  DMI_PND        VARCHAR(4), ";
            $query .= "  DMI_KOLI       VARCHAR(2), ";
            $query .= "  DMI_DCP        VARCHAR(2), ";
            $query .= "  DMI_IP         VARCHAR(30) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE TBTR_DELIVERY_SPI
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TBTR_DELIVERY_SPI ( ";
            $query .= "  del_tglkirim   DATE          NOT NULL, ";
            $query .= "  del_tipebayar  VARCHAR(20)  NOT NULL, ";
            $query .= "  del_kodemember VARCHAR(30)  NOT NULL, ";
            $query .= "  del_namamember VARCHAR(100) NOT NULL, ";
            $query .= "  del_alamat     VARCHAR(500) NOT NULL, ";
            $query .= "  del_nopb       VARCHAR(50)  NOT NULL, ";
            $query .= "  del_tglpb      DATE          NOT NULL, ";
            $query .= "  del_nosp       VARCHAR(10), ";
            $query .= "  del_nilaisp    NUMERIC        NOT NULL, ";
            $query .= "  del_create_by  VARCHAR(5), ";
            $query .= "  del_create_dt  DATE, ";
            $query .= "  del_modify_by  VARCHAR(5), ";
            $query .= "  del_modify_dt  DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CEK COLUMN TBTR_DELIVERY_SPI - DEL_NOPOL
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_NOPOL'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_NOPOL VARCHAR(100)");
        }

        //* CEK COLUMN TBTR_DELIVERY_SPI - DEL_DRIVER
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_DRIVER'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD DEL_DRIVER VARCHAR(100)");
        }

        //* CEK COLUMN TBTR_DELIVERY_SPI - E
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_DELIVERYMAN'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_DELIVERYMAN VARCHAR(100)");
        }

        //* CEK COLUMN TBTR_DELIVERY_SPI - DEL_NOLISTING
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_NOLISTING'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_NOLISTING VARCHAR(20)");
        }

        //* CEK COLUMN TBTR_DELIVERY_SPI - DEL_FLAGBATAL
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_FLAGBATAL'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_FLAGBATAL VARCHAR(2)");
        }

        //* CREATE TABLE TEMP_DELIVERY_SPI
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TEMP_DELIVERY_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TEMP_DELIVERY_SPI ( ";
            $query .= "  tipe_bayar  VARCHAR(30), ";
            $query .= "  no_pb       VARCHAR(50), ";
            $query .= "  tgl_pb      VARCHAR(30), ";
            $query .= "  kode_member VARCHAR(30), ";
            $query .= "  ip          VARCHAR(30) ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE TBTR_KONVERSI_SPI
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBTR_KONVERSI_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE TBTR_KONVERSI_SPI ( ";
            $query .= "  kvi_tgltrans     DATE, ";
            $query .= "  kvi_notrans      Varchar(5), ";
            $query .= "  kvi_prdcd        Varchar(10), ";
            $query .= "  kvi_hargasatuan  NUMERIC, ";
            $query .= "  kvi_qtyorder     NUMERIC, ";
            $query .= "  kvi_qtyrealisasi NUMERIC, ";
            $query .= "  kvi_ppn          NUMERIC, ";
            $query .= "  kvi_diskon       NUMERIC, ";
            $query .= "  kvi_hpp          NUMERIC, ";
            $query .= "  kvi_kodealamat   Varchar(3), ";
            $query .= "  kvi_hargaweb     NUMERIC, ";
            $query .= "  kvi_create_by    Varchar(5), ";
            $query .= "  kvi_create_dt    DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        //* CREATE TABLE TBTR_BAREFUND_SPI
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBTR_BAREFUND_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= " CREATE TABLE TBTR_BAREFUND_SPI ( ";
            $query .= "  brs_tglba       DATE          NOT NULL, ";
            $query .= "  brs_noba        VARCHAR(30)  NOT NULL, ";
            $query .= "  brs_tipebayar   VARCHAR(100) NOT NULL, ";
            $query .= "  brs_nopb        VARCHAR(50)  NOT NULL, ";
            $query .= "  brs_tglpb       DATE          NOT NULL, ";
            $query .= "  brs_kodemember  VARCHAR(30)  NOT NULL, ";
            $query .= "  brs_nilairefund NUMERIC        NOT NULL, ";
            $query .= "  brs_create_by   VARCHAR(5), ";
            $query .= "  brs_create_dt   DATE, ";
            $query .= "  brs_modify_by   VARCHAR(5), ";
            $query .= "  brs_modify_dt   DATE ";
            $query .= " ) ";
            DB::insert($query);
        }

        //* CREATE TABLE TEMP_BAREFUND_SPI
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TEMP_BAREFUND_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= " CREATE TABLE TEMP_BAREFUND_SPI ( ";
            $query .= "  tipebayar   VARCHAR(100), ";
            $query .= "  nopb        VARCHAR(50), ";
            $query .= "  tglpb       VARCHAR(30), ";
            $query .= "  kodemember  VARCHAR(30), ";
            $query .= "  nilairefund NUMERIC, ";
            $query .= "  IP          VARCHAR(30) ";
            $query .= " ) ";
            DB::insert($query);
        }

        //* CREATE TABLE TBTR_BARUSAK_SPI
        $count = DB::table('information_schema.tables')
            ->whereRaw("upper(table_name) = 'TBTR_BARUSAK_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= " CREATE TABLE TBTR_BARUSAK_SPI ( ";
            $query .= "   brk_tglba       DATE         NOT NULL, ";
            $query .= "   brk_noba        Varchar(30), ";
            $query .= "   brk_statusba    Varchar(10) NOT NULL, "; //-- DRAFT / DONE / BATAL;
            $query .= "   brk_tipebayar   Varchar(30) NOT NULL, ";
            $query .= "   brk_nopb        Varchar(50) NOT NULL, ";
            $query .= "   brk_tglpb       DATE         NOT NULL, ";
            $query .= "   brk_kodemember  Varchar(20) NOT NULL, ";
            $query .= "   brk_prdcd       Varchar(10) NOT NULL, ";
            $query .= "   brk_qtyba       NUMERIC       NOT NULL, ";
            $query .= "   brk_alasan      Varchar(500), ";
            $query .= "   brk_tglapprove  DATE, ";
            $query .= "   brk_userapprove Varchar(5), ";
            $query .= "   brk_create_by   Varchar(5), ";
            $query .= "   brk_create_dt   DATE, ";
            $query .= "   brk_modify_by   Varchar(5), ";
            $query .= "   brk_modify_dt   DATE ";
            $query .= " ) ";
            DB::insert($query);
        }

        //* CEK SEQUENCE SEQ_BA_REFUND_SPI
        $count = DB::table('information_schema.sequences')
            ->whereRaw("upper(sequence_name) = 'SEQ_BA_REFUND_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE SEQUENCE SEQ_BA_REFUND_SPI ";
            $query .= "  MINVALUE 1 ";
            $query .= "  MAXVALUE 99999 ";
            $query .= "  START WITH 1 ";
            $query .= "  INCREMENT BY 1 ";
            $query .= "  CYCLE ";
            DB::insert($query);
        }

        //* CEK SEQUENCE SEQ_BA_RUSAK_SPI
        $count = DB::table('information_schema.sequences')
            ->whereRaw("upper(sequence_name) = 'SEQ_BA_RUSAK_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE SEQUENCE SEQ_BA_RUSAK_SPI ";
            $query .= "  MINVALUE 1 ";
            $query .= "  MAXVALUE 99999 ";
            $query .= "  START WITH 1 ";
            $query .= "  INCREMENT BY 1 ";
            $query .= "  CYCLE ";
            DB::insert($query);
        }

        //* CEK SEQUENCE SEQ_LIST_DELIVERY_SPI
        $count = DB::table('information_schema.sequences')
            ->whereRaw("upper(sequence_name) = 'SEQ_LIST_DELIVERY_SPI'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE SEQUENCE SEQ_LIST_DELIVERY_SPI ";
            $query .= "  MINVALUE 1 ";
            $query .= "  MAXVALUE 99999 ";
            $query .= "  START WITH 1 ";
            $query .= "  INCREMENT BY 1 ";
            $query .= "  CYCLE ";
            DB::insert($query);
        }

        //* CEK COLUMN TBTR_DELIVERY_SPI - DEL_NOLISTING
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_NOLISTING'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_NOLISTING VARCHAR(20)");
        }

        //* CEK COLUMN TBTR_DSP_SPI - DSP_NOLISTING
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DSP_SPI'")
            ->whereRaw("upper(column_name) = 'DSP_NOLISTING'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DSP_SPI ADD COLUMN DSP_NOLISTING VARCHAR(20)");
        }
    }

    public function addColHitungUlang_SPI(){

        //* ADD COLUMN TBTR_OBI_D - OBI_QTY_HITUNGULANG
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_OBI_D'")
            ->whereRaw("upper(column_name) = 'OBI_QTY_HITUNGULANG'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_OBI_D ADD COLUMN OBI_QTY_HITUNGULANG NUMERIC");
        }

        //* ADD COLUMN PROMO_KLIKIGR - CASHBACK_HITUNGULANG
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'PROMO_KLIKIGR'")
            ->whereRaw("upper(column_name) = 'CASHBACK_HITUNGULANG'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE PROMO_KLIKIGR ADD COLUMN CASHBACK_HITUNGULANG NUMERIC");
        }

        //* ADD COLUMN PROMO_KLIKIGR - KELIPATAN_HITUNGULANG
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'PROMO_KLIKIGR'")
            ->whereRaw("upper(column_name) = 'KELIPATAN_HITUNGULANG'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE PROMO_KLIKIGR ADD COLUMN KELIPATAN_HITUNGULANG NUMERIC");
        }

        //* ADD COLUMN PROMO_KLIKIGR - REWARD_PER_PROMO_HITUNGULANG
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'PROMO_KLIKIGR'")
            ->whereRaw("upper(column_name) = 'REWARD_PER_PROMO_HITUNGULANG'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE PROMO_KLIKIGR ADD COLUMN REWARD_PER_PROMO_HITUNGULANG NUMERIC");
        }

        //* ADD COLUMN PROMO_KLIKIGR - REWARD_NOMINAL_HITUNGULANG
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'PROMO_KLIKIGR'")
            ->whereRaw("upper(column_name) = 'REWARD_NOMINAL_HITUNGULANG'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE PROMO_KLIKIGR ADD COLUMN REWARD_NOMINAL_HITUNGULANG NUMERIC");
        }

        //* ADD COLUMN TBTR_OBI_D - OBI_QTYBA
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_OBI_D'")
            ->whereRaw("upper(column_name) = 'OBI_QTYBA'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_OBI_D ADD COLUMN OBI_QTYBA NUMERIC");
        }
    }

    public function sendJalur_SPI($kodeMember, $noPB, $noTrans, $tglTrans){

        $KodeToko = 'SPI0';
        $PSP_NoPB = explode("/", $noPB)[0];
        $PSP_TglPB = $tglTrans;
        $FMKSBU = '5';
        $PSP_GATE = 'GT00';
        $IP = $this->getIP();
        $PSP_NamaToko = $kodeMember;
        $PSP_KodeToko = 'SPI0';

        //* TAMBAHAN CEGATAN SUDAH PERNAH SEND JALUR
        $query = '';
        $query .= " SELECT DISTINCT nopicking, nosuratjalan  ";
        $query .= " FROM dpd_idm_ora ";
        $query .= " WHERE fmndoc = '" . $PSP_NoPB . "' ";
        $query .= " AND fmkcab = 'SPI0' ";
        //? DI DB MEMANG TYPE DATA tglpb ADALAH 20230607
        $query .= " AND tglpb = TO_CHAR(TO_DATE('" . date('Y-m-d', strtotime($PSP_TglPB)) . "','YYYY-MM-DD'), 'YYYYMMDD') ";
        $dtCek = DB::select($query);

        if(count($dtCek)){
            throw new HttpResponseException(ApiFormatter::error(400, "Sudah pernah send jalur noPick
            noPick " . $dtCek[0]->nopicking . " | noSJ " . $dtCek[0]->nosuratjalan));
        }

        if($this->konversi_SPI($noTrans, $tglTrans) == false){
            return true;
        }

        //* GET NO PICK
        // ExecScalar("select nextval('SEQ_PICKING_SPI')", "GET NO PICK", PSP_NoPick)
        // PSP_NoSJ = PSP_NoPick

        // noPick = PSP_NoPick
        // noSJ = PSP_NoSJ

        $noPick = DB::select("select nextval('SEQ_PICKING_SPI')")[0]->nextval;
        $PSP_NoPick = $noPick;
        $PSP_NoSJ = $noPick;

        // ExecScalar("SELECT TO_CHAR(current_timestamp, 'YYYYMMDD;HH24:MI:SS;') FROM DUAL", "GET NO PICK", PSP_TglPick)
        $PSP_TglPick = Carbon::now();


        //* DELETE TEMP_DPD_IDM
        DB::table('temp_dpd_idm')->where('req_id', $this->getIP());

        //* DELETE DPD_IDM_NOBARCODE
        DB::table('dpd_idm_nobarcode')->where('req_id', $this->getIP());

        //* DELETE TEMP_HHELD_IDM
        DB::table('temp_hheld_idm')->where('req_id', $this->getIP());

        //* INSERT INTO TEMP_DPD_IDM - BULKY
        $query = '';
        $query .= "INSERT INTO TEMP_DPD_IDM ( ";
        $query .= "  FMNDOC, ";
        $query .= "  FMNBTC, ";
        $query .= "  TRNRAK, ";
        $query .= "  FMNTRN, ";
        $query .= "  PRDCD, ";
        $query .= "  BARC, ";
        $query .= "  BAR2, ";
        $query .= "  BARK, ";
        $query .= "  GRAK, ";
        $query .= "  NOUR, ";
        $query .= "  KODERAK, ";
        $query .= "  NOID, ";
        $query .= "  SATUAN, ";
        $query .= "  FMKSBU, ";
        $query .= "  FMKCAB, ";
        $query .= "  'desc', ";
        $query .= "  DESC2, ";
        $query .= "  QTYO, ";
        $query .= "  STOK, ";
        $query .= "  QTYR, ";
        $query .= "  FMSTS, ";
        $query .= "  'time', ";
        $query .= "  REQ_ID ";
        $query .= ") ";
        $query .= "SELECT ";
        $query .= "  fmndoc, ";
        $query .= "  fmnbtc, ";
        $query .= "  trnrak, ";
        $query .= "  fmntrn, ";
        $query .= "  prdcd, ";
        $query .= "  MIN(brc_barcode) AS barc, ";
        $query .= "  CASE WHEN MIN(brc_barcode) = MAX(brc_barcode) ";
        $query .= "    THEN NULL ";
        $query .= "    ELSE MAX(brc_barcode) ";
        $query .= "  END AS bar2, ";
        $query .= "  bark, ";
        $query .= "  grak, ";
        $query .= "  nour, ";
        $query .= "  koderak, ";
        $query .= "  noid, ";
        $query .= "  satuan, ";
        $query .= "  fmksbu, ";
        $query .= "  fmkcab, ";
        $query .= "  'desc', ";
        $query .= "  desc2, ";
        $query .= "  qtyo, ";
        $query .= "  stok, ";
        $query .= "  qtyr, ";
        $query .= "  fmsts, ";
        $query .= "  'time', ";
        $query .= "  '" . $IP . "' ";
        $query .= "FROM ( ";
        $query .= "  SELECT ";
        $query .= "    '" . $PSP_NoPB . "' AS fmndoc, ";
        $query .= "    NULL AS fmnbtc, ";
        $query .= "    SUBSTR( ";
        $query .= "      RPAD('" . $PSP_NoPB . "', 9, ' ') ";
        $query .= "      || SUBSTR(GRR_GROUPRAK, 1, 5) ";
        $query .= "      || LPAD(grr_nourut::text, 2, ' ') ";
        $query .= "      || RPAD(LKS_KodeRak, 7, ' ') ";
        $query .= "      || RPAD(LKS_KodeSubRak, 3, ' ') ";
        $query .= "      || RPAD(LKS_TipeRak, 3, ' ') ";
        $query .= "      || RPAD(LKS_ShelvingRak, 2, ' ') ";
        $query .= "      || obi_prdcd, ";
        $query .= "      1, 36 ";
        $query .= "    ) AS trnrak, ";
        $query .= "    NULL AS fmntrn, ";
        $query .= "    obi_prdcd AS prdcd, ";
        $query .= "    NULL AS bark, ";
        $query .= "    grr_grouprak AS grak, ";
        $query .= "    grr_nourut AS nour, ";
        $query .= "    SUBSTR( ";
        $query .= "      RPAD(lks_koderak, 7, ' ') ";
        $query .= "      || RPAD(lks_kodesubrak, 3, ' ') ";
        $query .= "      || RPAD(lks_tiperak, 3, ' ')  ";
        $query .= "      || RPAD(lks_shelvingrak, 2, ' ') ";
        $query .= "      || LPAD( ";
        $query .= "           CASE WHEN SUBSTR(lks_koderak, 1, 1) = 'D' ";
        $query .= "             THEN '0' ";
        $query .= "             ELSE lks_nourut::text ";
        $query .= "           END, 3, ' ' ";
        $query .= "         ),  ";
        $query .= "      1, 18 ";
        $query .= "    ) AS koderak, ";
        $query .= "    lks_noid AS noid, ";
        $query .= "    prd_unit||'/'||prd_frac AS satuan, ";
        $query .= "    '" . $FMKSBU . "' AS fmksbu, ";
        $query .= "    '" . $KodeToko . "' AS fmkcab, ";
        $query .= "    prd_deskripsipendek AS 'desc', ";
        $query .= "    prd_deskripsipanjang AS desc2, ";
        $query .= "    d.obi_qtyorder/prd_frac AS qtyo, ";
        $query .= "    st_saldoakhir AS stok, ";
        $query .= "    d.obi_qtyrealisasi/prd_frac AS qtyr, ";
        $query .= "    NULL AS fmsts, ";
        $query .= "    NULL AS 'time' ";
        $query .= "  FROM tbtr_obi_h h ";
        $query .= "  JOIN tbtr_obi_d d ";
        $query .= "       ON h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "       AND h.obi_notrans = d.obi_notrans ";
        $query .= "  JOIN tbmaster_prodmast  ";
        $query .= "       ON prd_prdcd = d.obi_prdcd ";
        $query .= "  JOIN tbmaster_lokasi  ";
        $query .= "       ON lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "  JOIN tbmaster_grouprak ";
        $query .= "       ON grr_koderak = lks_koderak ";
        $query .= "       AND grr_subrak = lks_kodesubrak ";
        $query .= "  JOIN tbmaster_stock ";
        $query .= "       ON st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "  WHERE h.obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
        $query .= "  AND h.obi_notrans = '" . $noTrans . "' ";
        $query .= "  AND h.obi_nopb = '" . $noPB . "' ";
        $query .= "  AND MOD(d.obi_qtyorder, prd_frac) = 0 ";
        $query .= "  AND d.obi_recid IS NULL ";
        $query .= "  AND st_lokasi = '01' ";
        $query .= "  AND lks_noid LIKE '%B' ";
        $query .= "  AND lks_tiperak NOT LIKE 'S%' ";
        $query .= "  AND COALESCE(grr_flagcetakan,'X') <> 'Y' ";
        $query .= "  AND grr_grouprak NOT LIKE 'H%' ";
        $query .= ") A  ";
        $query .= "LEFT JOIN tbmaster_barcode ";
        $query .= "ON brc_prdcd = prdcd ";
        $query .= "GROUP BY ";
        $query .= "  fmndoc, ";
        $query .= "  fmnbtc, ";
        $query .= "  trnrak, ";
        $query .= "  fmntrn, ";
        $query .= "  prdcd, ";
        $query .= "  bark, ";
        $query .= "  grak, ";
        $query .= "  nour, ";
        $query .= "  koderak, ";
        $query .= "  noid, ";
        $query .= "  satuan, ";
        $query .= "  fmksbu, ";
        $query .= "  fmkcab, ";
        $query .= "  'desc', ";
        $query .= "  desc2, ";
        $query .= "  qtyo, ";
        $query .= "  stok, ";
        $query .= "  qtyr, ";
        $query .= "  fmsts, ";
        $query .= "  'time' ";
        $query .= "ORDER BY koderak, nour ";
        DB::insert($query);

        //* INSERT INTO DPD_IDM_NOBARCODE - BULKY
        $query = '';
        $query .= "INSERT INTO DPD_IDM_NOBARCODE ( ";
        $query .= "  FMNDOC, ";
        $query .= "  FMNBTC, ";
        $query .= "  TRNRAK, ";
        $query .= "  FMNTRN, ";
        $query .= "  PRDCD, ";
        $query .= "  BARC, ";
        $query .= "  BAR2, ";
        $query .= "  BARK, ";
        $query .= "  GRAK, ";
        $query .= "  NOUR, ";
        $query .= "  KODERAK, ";
        $query .= "  NOID, ";
        $query .= "  SATUAN, ";
        $query .= "  FMKSBU, ";
        $query .= "  FMKCAB, ";
        $query .= "  'desc', ";
        $query .= "  DESC2, ";
        $query .= "  QTYO, ";
        $query .= "  STOK, ";
        $query .= "  QTYR, ";
        $query .= "  FMSTS, ";
        $query .= "  'time', ";
        $query .= "  REQ_ID ";
        $query .= ") ";
        $query .= "SELECT ";
        $query .= "  fmndoc, ";
        $query .= "  fmnbtc, ";
        $query .= "  trnrak, ";
        $query .= "  fmntrn, ";
        $query .= "  prdcd, ";
        $query .= "  '' AS barc, ";
        $query .= "  '' AS bar2, ";
        $query .= "  bark, ";
        $query .= "  grak, ";
        $query .= "  nour, ";
        $query .= "  koderak, ";
        $query .= "  noid, ";
        $query .= "  satuan, ";
        $query .= "  fmksbu, ";
        $query .= "  fmkcab, ";
        $query .= "  'desc', ";
        $query .= "  desc2, ";
        $query .= "  qtyo, ";
        $query .= "  stok, ";
        $query .= "  qtyr, ";
        $query .= "  fmsts, ";
        $query .= "  'time', ";
        $query .= "  '" . $IP . "' ";
        $query .= "FROM ( ";
        $query .= "  SELECT ";
        $query .= "    '" . $PSP_NoPB . "' AS fmndoc, ";
        $query .= "    NULL AS fmnbtc, ";
        $query .= "    SUBSTR( ";
        $query .= "      RPAD('" . $PSP_NoPB . "', 9, ' ') ";
        $query .= "      || SUBSTR(GRR_GROUPRAK, 1, 5) ";
        $query .= "      || LPAD(grr_nourut::text, 2, ' ') ";
        $query .= "      || RPAD(LKS_KodeRak, 7, ' ') ";
        $query .= "      || RPAD(LKS_KodeSubRak, 3, ' ') ";
        $query .= "      || RPAD(LKS_TipeRak, 3, ' ') ";
        $query .= "      || RPAD(LKS_ShelvingRak, 2, ' ') ";
        $query .= "      || obi_prdcd, ";
        $query .= "      1, 36 ";
        $query .= "    ) AS trnrak, ";
        $query .= "    NULL AS fmntrn, ";
        $query .= "    obi_prdcd AS prdcd, ";
        $query .= "    NULL AS bark, ";
        $query .= "    grr_grouprak AS grak, ";
        $query .= "    grr_nourut AS nour, ";
        $query .= "    SUBSTR( ";
        $query .= "      RPAD(lks_koderak, 7, ' ') ";
        $query .= "      || RPAD(lks_kodesubrak, 3, ' ') ";
        $query .= "      || RPAD(lks_tiperak, 3, ' ')  ";
        $query .= "      || RPAD(lks_shelvingrak, 2, ' ') ";
        $query .= "      || LPAD( ";
        $query .= "           CASE WHEN SUBSTR(lks_koderak, 1, 1) = 'D' ";
        $query .= "             THEN '0' ";
        $query .= "             ELSE lks_nourut::text ";
        $query .= "           END, 3, ' ' ";
        $query .= "         ),  ";
        $query .= "      1, 18 ";
        $query .= "    ) AS koderak, ";
        $query .= "    lks_noid AS noid, ";
        $query .= "    prd_unit||'/'||prd_frac AS satuan, ";
        $query .= "    '" . $FMKSBU . "' AS fmksbu, ";
        $query .= "    '" . $KodeToko . "' AS fmkcab, ";
        $query .= "    prd_deskripsipendek AS 'desc', ";
        $query .= "    prd_deskripsipanjang AS desc2, ";
        $query .= "    d.obi_qtyorder/prd_frac AS qtyo, ";
        $query .= "    st_saldoakhir AS stok, ";
        $query .= "    d.obi_qtyrealisasi/prd_frac AS qtyr, ";
        $query .= "    NULL AS fmsts, ";
        $query .= "    NULL AS 'time' ";
        $query .= "  FROM tbtr_obi_h h ";
        $query .= "  JOIN tbtr_obi_d d ";
        $query .= "       ON h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "       AND h.obi_notrans = d.obi_notrans ";
        $query .= "  JOIN tbmaster_prodmast  ";
        $query .= "       ON prd_prdcd = d.obi_prdcd ";
        $query .= "  JOIN tbmaster_lokasi  ";
        $query .= "       ON lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "  JOIN tbmaster_grouprak ";
        $query .= "       ON grr_koderak = lks_koderak ";
        $query .= "       AND grr_subrak = lks_kodesubrak ";
        $query .= "  JOIN tbmaster_stock ";
        $query .= "       ON st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "  WHERE h.obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
        $query .= "  AND h.obi_notrans = '" . $noTrans . "' ";
        $query .= "  AND h.obi_nopb = '" . $noPB . "' ";
        $query .= "  AND MOD(d.obi_qtyorder, prd_frac) = 0 ";
        $query .= "  AND d.obi_recid IS NULL ";
        $query .= "  AND st_lokasi = '01' ";
        $query .= "  AND lks_noid LIKE '%B' ";
        $query .= "  AND lks_tiperak NOT LIKE 'S%' ";
        $query .= "  AND COALESCE(grr_flagcetakan,'X') <> 'Y' ";
        $query .= "  AND grr_grouprak NOT LIKE 'H%' ";
        $query .= ") A  ";
        $query .= "WHERE NOT EXISTS ( ";
        $query .= "  SELECT brc_barcode ";
        $query .= "  FROM tbmaster_barcode ";
        $query .= "  WHERE brc_prdcd = prdcd ";
        $query .= ") ";
        $query .= "ORDER BY koderak, nour ";
        DB::insert($query);

        //* INSERT INTO TEMP_DPD_IDM - PIECES
        $query = '';
        $query .= "INSERT INTO TEMP_DPD_IDM ( ";
        $query .= "  FMNDOC, ";
        $query .= "  FMNBTC, ";
        $query .= "  TRNRAK, ";
        $query .= "  FMNTRN, ";
        $query .= "  PRDCD, ";
        $query .= "  BARC, ";
        $query .= "  BAR2, ";
        $query .= "  BARK, ";
        $query .= "  GRAK, ";
        $query .= "  NOUR, ";
        $query .= "  KODERAK, ";
        $query .= "  NOID, ";
        $query .= "  SATUAN, ";
        $query .= "  FMKSBU, ";
        $query .= "  FMKCAB, ";
        $query .= "  'desc', ";
        $query .= "  DESC2, ";
        $query .= "  QTYO, ";
        $query .= "  STOK, ";
        $query .= "  QTYR, ";
        $query .= "  FMSTS, ";
        $query .= "  'time', ";
        $query .= "  REQ_ID ";
        $query .= ") ";
        $query .= "SELECT ";
        $query .= "  fmndoc, ";
        $query .= "  fmnbtc, ";
        $query .= "  trnrak, ";
        $query .= "  fmntrn, ";
        $query .= "  prdcd, ";
        $query .= "  MIN(brc_barcode) AS barc, ";
        $query .= "  CASE WHEN MIN(brc_barcode) = MAX(brc_barcode) ";
        $query .= "    THEN NULL ";
        $query .= "    ELSE MAX(brc_barcode) ";
        $query .= "  END AS bar2, ";
        $query .= "  bark, ";
        $query .= "  grak, ";
        $query .= "  nour, ";
        $query .= "  koderak, ";
        $query .= "  noid, ";
        $query .= "  satuan, ";
        $query .= "  fmksbu, ";
        $query .= "  fmkcab, ";
        $query .= "  'desc', ";
        $query .= "  desc2, ";
        $query .= "  qtyo, ";
        $query .= "  stok, ";
        $query .= "  qtyr, ";
        $query .= "  fmsts, ";
        $query .= "  'time', ";
        $query .= "  '" . $IP . "' ";
        $query .= "FROM ( ";
        $query .= "  SELECT ";
        $query .= "    '" . $PSP_NoPB . "' AS fmndoc, ";
        $query .= "    NULL AS fmnbtc, ";
        $query .= "    SUBSTR( ";
        $query .= "      RPAD('" . $PSP_NoPB . "', 9, ' ') ";
        $query .= "      || SUBSTR(GRR_GROUPRAK, 1, 5) ";
        $query .= "      || LPAD(grr_nourut::text, 2, ' ') ";
        $query .= "      || RPAD(LKS_KodeRak, 7, ' ') ";
        $query .= "      || RPAD(LKS_KodeSubRak, 3, ' ') ";
        $query .= "      || RPAD(LKS_TipeRak, 3, ' ') ";
        $query .= "      || RPAD(LKS_ShelvingRak, 2, ' ') ";
        $query .= "      || obi_prdcd, ";
        $query .= "      1, 36 ";
        $query .= "    ) AS trnrak, ";
        $query .= "    NULL AS fmntrn, ";
        $query .= "    obi_prdcd AS prdcd, ";
        $query .= "    NULL AS bark, ";
        $query .= "    grr_grouprak AS grak, ";
        $query .= "    grr_nourut AS nour, ";
        $query .= "    SUBSTR( ";
        $query .= "      RPAD(lks_koderak, 7, ' ') ";
        $query .= "      || RPAD(lks_kodesubrak, 3, ' ') ";
        $query .= "      || RPAD(lks_tiperak, 3, ' ')  ";
        $query .= "      || RPAD(lks_shelvingrak, 2, ' ') ";
        $query .= "      || LPAD( ";
        $query .= "           CASE WHEN SUBSTR(lks_koderak, 1, 1) = 'D' ";
        $query .= "             THEN '0' ";
        $query .= "             ELSE lks_nourut::text ";
        $query .= "           END, 3, ' ' ";
        $query .= "         ),  ";
        $query .= "      1, 18 ";
        $query .= "    ) AS koderak, ";
        $query .= "    lks_noid AS noid, ";
        $query .= "    prd_unit||'/'||prd_frac AS satuan, ";
        $query .= "    '" . $FMKSBU . "' AS fmksbu, ";
        $query .= "    '" . $KodeToko . "' AS fmkcab, ";
        $query .= "    prd_deskripsipendek AS 'desc', ";
        $query .= "    prd_deskripsipanjang AS desc2, ";
        $query .= "    d.obi_qtyorder / CASE WHEN prd_unit = 'KG' THEN prd_frac ELSE 1 END AS qtyo, ";
        $query .= "    st_saldoakhir AS stok, ";
        $query .= "    d.obi_qtyrealisasi / CASE WHEN prd_unit = 'KG' THEN prd_frac ELSE 1 END AS qtyr, ";
        $query .= "    NULL AS fmsts, ";
        $query .= "    NULL AS 'time' ";
        $query .= "  FROM tbtr_obi_h h ";
        $query .= "  JOIN tbtr_obi_d d ";
        $query .= "       ON h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "       AND h.obi_notrans = d.obi_notrans ";
        $query .= "  JOIN tbmaster_prodmast  ";
        $query .= "       ON prd_prdcd = d.obi_prdcd ";
        $query .= "  JOIN tbmaster_lokasi  ";
        $query .= "       ON lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "  JOIN tbmaster_grouprak ";
        $query .= "       ON grr_koderak = lks_koderak ";
        $query .= "       AND grr_subrak = lks_kodesubrak ";
        $query .= "  JOIN tbmaster_stock ";
        $query .= "       ON st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "  WHERE h.obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
        $query .= "  AND h.obi_notrans = '" . $noTrans . "' ";
        $query .= "  AND h.obi_nopb = '" . $noPB . "' ";
        $query .= "  AND d.obi_recid IS NULL ";
        $query .= "  AND st_lokasi = '01' ";
        $query .= "  AND lks_noid LIKE '%P' ";
        $query .= "  AND lks_tiperak NOT LIKE 'S%' ";
        $query .= "  AND COALESCE(grr_flagcetakan,'X') <> 'Y' ";
        $query .= "  AND grr_grouprak NOT LIKE 'H%' ";
        $query .= ") A  ";
        $query .= "LEFT JOIN tbmaster_barcode ";
        $query .= "ON brc_prdcd = prdcd ";
        $query .= "GROUP BY ";
        $query .= "  fmndoc, ";
        $query .= "  fmnbtc, ";
        $query .= "  trnrak, ";
        $query .= "  fmntrn, ";
        $query .= "  prdcd, ";
        $query .= "  bark, ";
        $query .= "  grak, ";
        $query .= "  nour, ";
        $query .= "  koderak, ";
        $query .= "  noid, ";
        $query .= "  satuan, ";
        $query .= "  fmksbu, ";
        $query .= "  fmkcab, ";
        $query .= "  'desc', ";
        $query .= "  desc2, ";
        $query .= "  qtyo, ";
        $query .= "  stok, ";
        $query .= "  qtyr, ";
        $query .= "  fmsts, ";
        $query .= "  'time' ";
        $query .= "ORDER BY koderak, nour ";
        DB::insert($query);

        //* INSERT INTO DPD_IDM_NOBARCODE - PIECES
        $query = '';
        $query .= "INSERT INTO DPD_IDM_NOBARCODE ( ";
        $query .= "  FMNDOC, ";
        $query .= "  FMNBTC, ";
        $query .= "  TRNRAK, ";
        $query .= "  FMNTRN, ";
        $query .= "  PRDCD, ";
        $query .= "  BARC, ";
        $query .= "  BAR2, ";
        $query .= "  BARK, ";
        $query .= "  GRAK, ";
        $query .= "  NOUR, ";
        $query .= "  KODERAK, ";
        $query .= "  NOID, ";
        $query .= "  SATUAN, ";
        $query .= "  FMKSBU, ";
        $query .= "  FMKCAB, ";
        $query .= "  'desc', ";
        $query .= "  DESC2, ";
        $query .= "  QTYO, ";
        $query .= "  STOK, ";
        $query .= "  QTYR, ";
        $query .= "  FMSTS, ";
        $query .= "  'time', ";
        $query .= "  REQ_ID ";
        $query .= ") ";
        $query .= "SELECT ";
        $query .= "  fmndoc, ";
        $query .= "  fmnbtc, ";
        $query .= "  trnrak, ";
        $query .= "  fmntrn, ";
        $query .= "  prdcd, ";
        $query .= "  '' AS barc, ";
        $query .= "  '' AS bar2, ";
        $query .= "  bark, ";
        $query .= "  grak, ";
        $query .= "  nour, ";
        $query .= "  koderak, ";
        $query .= "  noid, ";
        $query .= "  satuan, ";
        $query .= "  fmksbu, ";
        $query .= "  fmkcab, ";
        $query .= "  'desc', ";
        $query .= "  desc2, ";
        $query .= "  qtyo, ";
        $query .= "  stok, ";
        $query .= "  qtyr, ";
        $query .= "  fmsts, ";
        $query .= "  'time', ";
        $query .= "  '" . $IP . "' ";
        $query .= "FROM ( ";
        $query .= "  SELECT ";
        $query .= "    '" . $PSP_NoPB . "' AS fmndoc, ";
        $query .= "    NULL AS fmnbtc, ";
        $query .= "    SUBSTR( ";
        $query .= "      RPAD('" . $PSP_NoPB . "', 9, ' ') ";
        $query .= "      || SUBSTR(GRR_GROUPRAK, 1, 5) ";
        $query .= "      || LPAD(grr_nourut::text, 2, ' ') ";
        $query .= "      || RPAD(LKS_KodeRak, 7, ' ') ";
        $query .= "      || RPAD(LKS_KodeSubRak, 3, ' ') ";
        $query .= "      || RPAD(LKS_TipeRak, 3, ' ') ";
        $query .= "      || RPAD(LKS_ShelvingRak, 2, ' ') ";
        $query .= "      || obi_prdcd, ";
        $query .= "      1, 36 ";
        $query .= "    ) AS trnrak, ";
        $query .= "    NULL AS fmntrn, ";
        $query .= "    obi_prdcd AS prdcd, ";
        $query .= "    NULL AS bark, ";
        $query .= "    grr_grouprak AS grak, ";
        $query .= "    grr_nourut AS nour, ";
        $query .= "    SUBSTR( ";
        $query .= "      RPAD(lks_koderak, 7, ' ') ";
        $query .= "      || RPAD(lks_kodesubrak, 3, ' ') ";
        $query .= "      || RPAD(lks_tiperak, 3, ' ')  ";
        $query .= "      || RPAD(lks_shelvingrak, 2, ' ') ";
        $query .= "      || LPAD( ";
        $query .= "           CASE WHEN SUBSTR(lks_koderak, 1, 1) = 'D' ";
        $query .= "             THEN '0' ";
        $query .= "             ELSE lks_nourut::text ";
        $query .= "           END, 3, ' ' ";
        $query .= "         ),  ";
        $query .= "      1, 18 ";
        $query .= "    ) AS koderak, ";
        $query .= "    lks_noid AS noid, ";
        $query .= "    prd_unit||'/'||prd_frac AS satuan, ";
        $query .= "    '" . $FMKSBU . "' AS fmksbu, ";
        $query .= "    '" . $KodeToko . "' AS fmkcab, ";
        $query .= "    prd_deskripsipendek AS 'desc', ";
        $query .= "    prd_deskripsipanjang AS desc2, ";
        $query .= "    d.obi_qtyorder / CASE WHEN prd_unit = 'KG' THEN prd_frac ELSE 1 END AS qtyo, ";
        $query .= "    st_saldoakhir AS stok, ";
        $query .= "    d.obi_qtyrealisasi / CASE WHEN prd_unit = 'KG' THEN prd_frac ELSE 1 END AS qtyr, ";
        $query .= "    NULL AS fmsts, ";
        $query .= "    NULL AS 'time' ";
        $query .= "  FROM tbtr_obi_h h ";
        $query .= "  JOIN tbtr_obi_d d ";
        $query .= "       ON h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "       AND h.obi_notrans = d.obi_notrans ";
        $query .= "  JOIN tbmaster_prodmast  ";
        $query .= "       ON prd_prdcd = d.obi_prdcd ";
        $query .= "  JOIN tbmaster_lokasi  ";
        $query .= "       ON lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "  JOIN tbmaster_grouprak ";
        $query .= "       ON grr_koderak = lks_koderak ";
        $query .= "       AND grr_subrak = lks_kodesubrak ";
        $query .= "  JOIN tbmaster_stock ";
        $query .= "       ON st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "  WHERE h.obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
        $query .= "  AND h.obi_notrans = '" . $noTrans . "' ";
        $query .= "  AND h.obi_nopb = '" . $noPB . "' ";
        $query .= "  AND d.obi_recid IS NULL ";
        $query .= "  AND st_lokasi = '01' ";
        $query .= "  AND (lks_noid like '%P' OR COALESCE(prd_frac,0) = 1) ";
        $query .= "  AND lks_tiperak NOT LIKE 'S%' ";
        $query .= "  AND COALESCE(grr_flagcetakan,'X') <> 'Y' ";
        $query .= "  AND grr_grouprak NOT LIKE 'H%' ";
        $query .= ") A  ";
        $query .= "WHERE NOT EXISTS ( ";
        $query .= "  SELECT brc_barcode ";
        $query .= "  FROM tbmaster_barcode ";
        $query .= "  WHERE brc_prdcd = prdcd ";
        $query .= ") ";
        $query .= "ORDER BY koderak, nour ";
        DB::insert($query);

        //* INSERT INTO TEMP_HHELD_IDM
        $query = '';
        $query .= "INSERT INTO TEMP_HHELD_IDM ( ";
        $query .= "  FMNDOC, ";
        $query .= "  FMNBTC, ";
        $query .= "  TRNRAK, ";
        $query .= "  FMNTRN, ";
        $query .= "  PRDCD, ";
        $query .= "  BARC, ";
        $query .= "  BAR2, ";
        $query .= "  BARK, ";
        $query .= "  GRAK, ";
        $query .= "  NOUR, ";
        $query .= "  KODERAK, ";
        $query .= "  NOID, ";
        $query .= "  SATUAN, ";
        $query .= "  FMKSBU, ";
        $query .= "  FMKCAB, ";
        $query .= "  'desc', ";
        $query .= "  DESC2, ";
        $query .= "  QTYO, ";
        $query .= "  STOK, ";
        $query .= "  QTYR, ";
        $query .= "  REQ_ID ";
        $query .= ") ";
        $query .= "SELECT ";
        $query .= "  fmndoc, ";
        $query .= "  fmnbtc, ";
        $query .= "  trnrak, ";
        $query .= "  fmntrn, ";
        $query .= "  prdcd, ";
        $query .= "  MIN(brc_barcode) AS barc, ";
        $query .= "  CASE WHEN MIN(brc_barcode) = MAX(brc_barcode) ";
        $query .= "    THEN NULL ";
        $query .= "    ELSE MAX(brc_barcode) ";
        $query .= "  END AS bar2, ";
        $query .= "  bark, ";
        $query .= "  grak, ";
        $query .= "  nour, ";
        $query .= "  koderak, ";
        $query .= "  noid, ";
        $query .= "  satuan, ";
        $query .= "  fmksbu, ";
        $query .= "  fmkcab, ";
        $query .= "  'desc', ";
        $query .= "  desc2, ";
        $query .= "  qtyo, ";
        $query .= "  stok, ";
        $query .= "  qtyr, ";
        $query .= "  '" . $IP . "' ";
        $query .= "FROM ( ";
        $query .= "  SELECT ";
        $query .= "    '" . $PSP_NoPB . "' AS fmndoc, ";
        $query .= "    NULL AS fmnbtc, ";
        $query .= "    NULL AS trnrak, ";
        $query .= "    NULL AS fmntrn, ";
        $query .= "    obi_prdcd AS prdcd, ";
        $query .= "    NULL AS bark, ";
        $query .= "    grr_grouprak AS grak, ";
        $query .= "    grr_nourut AS nour, ";
        $query .= "    SUBSTR( ";
        $query .= "      RPAD(lks_koderak, 7, ' ') ";
        $query .= "      || RPAD(lks_kodesubrak, 3, ' ') ";
        $query .= "      || RPAD(lks_tiperak, 3, ' ')  ";
        $query .= "      || RPAD(lks_shelvingrak, 2, ' ') ";
        $query .= "      || LPAD( ";
        $query .= "           CASE WHEN SUBSTR(lks_koderak, 1, 1) = 'D' ";
        $query .= "             THEN '0' ";
        $query .= "             ELSE lks_nourut::text ";
        $query .= "           END, 3, ' ' ";
        $query .= "         ),  ";
        $query .= "      1, 18 ";
        $query .= "    ) AS koderak, ";
        $query .= "    lks_noid AS noid, ";
        $query .= "    prd_unit||'/'||prd_frac AS satuan, ";
        $query .= "    '" . $FMKSBU . "' AS fmksbu, ";
        $query .= "    '" . $KodeToko . "' AS fmkcab, ";
        $query .= "    prd_deskripsipendek AS 'desc', ";
        $query .= "    prd_deskripsipanjang AS desc2, ";
        $query .= "    d.obi_qtyorder AS qtyo, ";
        $query .= "    st_saldoakhir AS stok, ";
        $query .= "    d.obi_qtyrealisasi AS qtyr ";
        $query .= "  FROM tbtr_obi_h h ";
        $query .= "  JOIN tbtr_obi_d d ";
        $query .= "       ON h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "       AND h.obi_notrans = d.obi_notrans ";
        $query .= "  JOIN tbmaster_prodmast  ";
        $query .= "       ON prd_prdcd = d.obi_prdcd ";
        $query .= "  JOIN tbmaster_lokasi  ";
        $query .= "       ON lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "  JOIN tbmaster_grouprak ";
        $query .= "       ON grr_koderak = lks_koderak ";
        $query .= "       AND grr_subrak = lks_kodesubrak ";
        $query .= "  JOIN tbmaster_stock ";
        $query .= "       ON st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "  WHERE h.obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
        $query .= "  AND h.obi_notrans = '" . $noTrans . "' ";
        $query .= "  AND h.obi_nopb = '" . $noPB . "' ";
        $query .= "  AND d.obi_recid IS NULL ";
        $query .= "  AND st_lokasi = '01' ";
        $query .= "  AND lks_noid IS NULL ";
        $query .= "  AND lks_tiperak NOT LIKE 'S%' ";
        $query .= "  AND COALESCE(grr_flagcetakan,'X') <> 'Y' ";
        $query .= "  AND grr_grouprak LIKE 'H%' ";
        $query .= ") A  ";
        $query .= "LEFT JOIN tbmaster_barcode ";
        $query .= "ON brc_prdcd = prdcd ";
        $query .= "GROUP BY ";
        $query .= "  fmndoc, ";
        $query .= "  fmnbtc, ";
        $query .= "  trnrak, ";
        $query .= "  fmntrn, ";
        $query .= "  prdcd, ";
        $query .= "  bark, ";
        $query .= "  grak, ";
        $query .= "  nour, ";
        $query .= "  koderak, ";
        $query .= "  noid, ";
        $query .= "  satuan, ";
        $query .= "  fmksbu, ";
        $query .= "  fmkcab, ";
        $query .= "  'desc', ";
        $query .= "  desc2, ";
        $query .= "  qtyo, ";
        $query .= "  stok, ";
        $query .= "  qtyr ";
        $query .= "ORDER BY koderak, nour ";
        DB::insert($query);

        //* HHELD_IDM_ORA
        $cek = DB::table('hheld_idm_ora')
            ->where('req_id', $this->getIP())
            ->whereNull('fmrcid')
            ->whereDate('tglupd', Carbon::now())
            ->where([
                'fmkcab' => $KodeToko,
                'fmndoc' => $PSP_NoPB,
            ])->count();

        if($cek == 0){
            $query = '';
            $query .= "INSERT INTO HHELD_IDM_ORA ( ";
            $query .= "  FMRCID, ";
            $query .= "  FMNDOC, ";
            $query .= "  FMNBTC, ";
            $query .= "  TRNRAK, ";
            $query .= "  FMNTRN, ";
            $query .= "  PRDCD, ";
            $query .= "  BARC, ";
            $query .= "  BAR2, ";
            $query .= "  BARK, ";
            $query .= "  GRAK, ";
            $query .= "  NOUR, ";
            $query .= "  KODERAK, ";
            $query .= "  NOID, ";
            $query .= "  SATUAN, ";
            $query .= "  FMKSBU, ";
            $query .= "  FMKCAB, ";
            $query .= "  'desc', ";
            $query .= "  DESC2, ";
            $query .= "  QTYO, ";
            $query .= "  STOK, ";
            $query .= "  QTYR, ";
            $query .= "  REQ_ID, ";
            $query .= "  TGLUPD, ";
            $query .= "  JAM_UPLOAD, ";
            $query .= "  JAM_PICKING, ";
            $query .= "  USERID  ";
            $query .= ") ";
            $query .= "SELECT  ";
            $query .= "  NULL, ";
            $query .= "  FMNDOC, ";
            $query .= "  FMNBTC, ";
            $query .= "  TRNRAK, ";
            $query .= "  FMNTRN, ";
            $query .= "  PRDCD, ";
            $query .= "  BARC, ";
            $query .= "  BAR2, ";
            $query .= "  BARK, ";
            $query .= "  GRAK, ";
            $query .= "  NOUR, ";
            $query .= "  KODERAK, ";
            $query .= "  NOID, ";
            $query .= "  SATUAN, ";
            $query .= "  FMKSBU, ";
            $query .= "  FMKCAB, ";
            $query .= "  'desc', ";
            $query .= "  DESC2, ";
            $query .= "  QTYO, ";
            $query .= "  STOK, ";
            $query .= "  QTYR, ";
            $query .= "  REQ_ID, ";
            $query .= "  TO_CHAR(CURRENT_DATE,'YYYYMMDD'), ";
            $query .= "  TO_CHAR(current_timestamp,'HH24:MI:SS'), ";
            $query .= "  NULL, ";
            $query .= "  NULL ";
            $query .= "FROM TEMP_HHELD_IDM ";
            $query .= "WHERE REQ_ID = '" . $IP . "' ";
            $query .= "ORDER BY KODERAK ";
            DB::insert($query);
        }

        //* DPD_IDM_ORA
        $cek = DB::table('dpd_idm_ora')
            ->where('req_id', $this->getIP())
            ->whereNull('fmrcid')
            ->whereDate('tglupd', Carbon::now())
            ->where([
                'fmkcab' => $KodeToko,
                'fmndoc' => $PSP_NoPB,
            ])->count();

        if($cek > 0){
            $query = '';
            $query .= "INSERT INTO DPD_IDM_ORA ( ";
            $query .= "  FMRCID, ";
            $query .= "  FMNDOC, ";
            $query .= "  TGLPB, ";
            $query .= "  FMNBTC, ";
            $query .= "  TRNRAK, ";
            $query .= "  FMNTRN, ";
            $query .= "  PRDCD, ";
            $query .= "  BARC, ";
            $query .= "  BAR2, ";
            $query .= "  BARK, ";
            $query .= "  GRAK, ";
            $query .= "  NOUR, ";
            $query .= "  KODERAK, ";
            $query .= "  NOID, ";
            $query .= "  SATUAN, ";
            $query .= "  FMKSBU, ";
            $query .= "  FMKCAB, ";
            $query .= "  'desc', ";
            $query .= "  DESC2, ";
            $query .= "  QTYO, ";
            $query .= "  STOK, ";
            $query .= "  QTYR, ";
            $query .= "  REQ_ID, ";
            $query .= "  TGLUPD, ";
            $query .= "  JAM_UPLOAD, ";
            $query .= "  JAM_PICKING, ";
            $query .= "  USERID, ";
            $query .= "  NOPICKING, ";
            $query .= "  NOSURATJALAN, ";
            $query .= "  PANJANG, ";
            $query .= "  LEBAR, ";
            $query .= "  TINGGI, ";
            $query .= "  KUBIKPICKING, ";
            $query .= "  KODEZONA ";
            $query .= ") ";
            $query .= "SELECT ";
            $query .= "  NULL, ";
            $query .= "  FMNDOC, ";
            $query .= "  TO_CHAR(TO_DATE('" . $tglTrans . "','DD-MM-YYYY'), 'YYYYMMDD') TGLPB, ";
            $query .= "  FMNBTC, ";
            $query .= "  TRNRAK, ";
            $query .= "  FMNTRN, ";
            $query .= "  PRDCD, ";
            $query .= "  BARC, ";
            $query .= "  BAR2, ";
            $query .= "  BARK, ";
            $query .= "  GRAK, ";
            $query .= "  NOUR, ";
            $query .= "  KODERAK, ";
            $query .= "  NOID, ";
            $query .= "  SATUAN, ";
            $query .= "  FMKSBU, ";
            $query .= "  FMKCAB, ";
            $query .= "  'desc', ";
            $query .= "  DESC2, ";
            $query .= "  QTYO, ";
            $query .= "  STOK, ";
            $query .= "  QTYR, ";
            $query .= "  REQ_ID, ";
            $query .= "  TO_CHAR(CURRENT_DATE,'YYYYMMDD'), ";
            $query .= "  TO_CHAR(current_timestamp,'HH24:MI:SS'), ";
            $query .= "  NULL, ";
            $query .= "  NULL, ";
            $query .= "  '" . $PSP_NoPick . "' NOPICKING, ";
            $query .= "  '" . $PSP_NoSJ . "' NOSURATJALAN, ";
            $query .= "  COALESCE(OBI_PANJANG,PRD_DIMENSIPANJANG,1) PANJANG, ";
            $query .= "  COALESCE(OBI_LEBAR,PRD_DIMENSILEBAR,1) LEBAR, ";
            $query .= "  COALESCE(OBI_TINGGI,PRD_DIMENSITINGGI,1) TINGGI, ";
            $query .= "  NULL, ";
            $query .= "  ZON_KODE ";
            $query .= "FROM TEMP_DPD_IDM, TBMASTER_PRODMAST, ZONA_IDM, TBTR_OBI_D ";
            $query .= "WHERE REQ_ID = '" . $IP . "'  ";
            $query .= "AND OBI_TGLTRANS = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
            $query .= "AND OBI_NOTRANS = '" . $noTrans . "' ";
            $query .= "AND OBI_PRDCD = PRD_PRDCD ";
            $query .= "AND PRD_PRDCD = CASE WHEN SUBSTR(NOID,-1,1) = 'B' THEN SUBSTR(PRDCD,1,6)||'0' ELSE PRDCD END ";
            $query .= "AND ZON_RAK = SUBSTR(KODERAK,1,POSITION(' ' IN KODERAK)-1) ";
            DB::insert($query);
        }

        //* CETAKIN LABEL CONTAINER / BRONJONG UNTUK TOKO YANG PERTAMA HARI ITU
        $noContainer = 1;
        $noBronjong = 1;

        //* CEK PICKING_ANTRIAN
        $query = '';
        $query .= "SELECT COALESCE(COUNT(PIA_KODETOKO),0)  ";
        $query .= "FROM PICKING_ANTRIAN ";
        $query .= "WHERE PIA_TGLPICK >= CURRENT_DATE - 7 ";
        $query .= "AND PIA_NOPICK = '" . $PSP_NoPick . "' ";
        $query .= "AND PIA_NOSJ = '" . $PSP_NoSJ . "' ";
        $query .= "AND PIA_KODETOKO = '" . $KodeToko . "' ";
        $jum = DB::select($query)[0]->coalesce;

        if($jum == 0){
            $query = '';
            $query .= "SELECT  ";
            $query .= "  zon_kode, ";
            $query .= "  CASE WHEN UPPER(jeniscontainer) <> 'BRONJONG'  ";
            $query .= "    THEN SUM(CEIL(kubikasi_order/((con_panjang * con_lebar * con_tinggi * allowance) / 100))) ";
            $query .= "	  ELSE 0 ";
            $query .= "	END AS JumlahContainer,  ";
            $query .= "  CASE WHEN UPPER(jeniscontainer) = 'BRONJONG'  ";
            $query .= "    THEN SUM(CEIL(kubikasi_order/((con_panjang * con_lebar * con_tinggi * allowance) / 100))) ";
            $query .= "    ELSE 0 ";
            $query .= "  END AS JumlahBronjong, ";
            $query .= "  zon_printer ";
            $query .= "FROM (         ";
            $query .= "	SELECT  ";
            $query .= "    zon_kode, ";
            $query .= "   	SUM(COALESCE(obi_lebar,prd_dimensilebar,1) * COALESCE(obi_panjang,prd_dimensipanjang,1) * COALESCE(obi_tinggi,prd_dimensitinggi,1) * obi_qtyorder  ";
            $query .= "        / CASE WHEN PRD_UNIT = 'KG' THEN PRD_FRAC ELSE 1 END ";
            $query .= "    ) AS kubikasi_order, ";
            $query .= "    zon_container AS jeniscontainer, ";
            $query .= "    zon_allowance AS allowance, ";
            $query .= "    zon_Printer ";
            $query .= "  FROM tbtr_obi_h h ";
            $query .= "  JOIN tbtr_obi_d d ";
            $query .= "       ON h.obi_tgltrans = d.obi_tgltrans ";
            $query .= "       AND h.obi_notrans = d.obi_notrans ";
            $query .= "  LEFT JOIN tbmaster_prodmast ";
            $query .= "       ON prd_prdcd = d.obi_prdcd ";
            $query .= "  LEFT JOIN tbmaster_lokasi ";
            $query .= "       ON SUBSTR(d.obi_prdcd, 1, 6) || '0' = lks_prdcd ";
            $query .= "       AND lks_noid IS NOT NULL ";
            $query .= "  LEFT JOIN zona_idm ";
            $query .= "       ON zon_rak = lks_koderak ";
            $query .= "  WHERE h.obi_nopb = '" & $noPB & "' ";
            $query .= "  AND h.obi_notrans = '" & $noTrans & "' ";
            $query .= "  AND h.obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
            $query .= "  AND d.obi_recid IS NULL ";
            $query .= "  AND zon_container IS NOT NULL  ";
            $query .= "  GROUP BY zon_kode, zon_container, zon_allowance, zon_printer ";
            $query .= ") AS OBI ";
            $query .= "LEFT JOIN container_idm ";
            $query .= "ON con_jenis = jeniscontainer  ";
            $query .= "GROUP BY zon_kode, jeniscontainer, zon_printer ";
            $query .= "ORDER BY zon_kode ";
            $dt = DB::select($query);

            $SudahAdaCetakBarcodeHariIni = false;

            $query = '';
            $query .= "SELECT COALESCE(COUNT(PIA_KODETOKO),0)  ";
            $query .= "FROM PICKING_ANTRIAN ";
            $query .= "WHERE DATE_TRUNC('DAY',PIA_TGLPICK) = DATE_TRUNC('DAY',CURRENT_DATE) ";
            $cek = DB::select($query)[0]->count;

            if($cek > 0){
                $SudahAdaCetakBarcodeHariIni = true;
            }

            $i = 0;
            foreach($dt as $key => $item){
                //* JIKA ADA CONTAINER
                if($item->jumlahcontainer > 0){

                    // BarcodeContainer = "01" & Strings.Right("0000000" & PSP_NoPick, 7) & Strings.Right("000" & noContainer.ToString, 3)
                    $BarcodeContainer = "01" . str_pad($PSP_NoPick, 7, "0", STR_PAD_LEFT) . str_pad($noContainer, 3, "0", STR_PAD_LEFT);

                    //* INSERT INTO PICKING CONTAINER - Container
                    $query = '';
                    $query .= "INSERT INTO PICKING_CONTAINER ( ";
                    $query .= "  PICO_PrinterName, ";
                    $query .= "  PICO_NoPick, ";
                    $query .= "  PICO_TglPick, ";
                    $query .= "  PICO_ContainerZona, ";
                    $query .= "  PICO_Gate, ";
                    $query .= "  PICO_KodeToko, ";
                    $query .= "  PICO_NamaToko, ";
                    $query .= "  PICO_BarcodeKoli, ";
                    $query .= "  PICO_NoUrutToko, ";
                    $query .= "  PICO_JumlahToko, ";
                    $query .= "  PICO_NoSJ ";
                    $query .= ") ";
                    $query .= "VALUES ( ";
                    $query .= "  '" . $item->zon_printer . "', ";
                    $query .= "  '" . $PSP_NoPick . "', ";
                    $query .= "  '" . date("Ymd") . "', ";
                    $query .= "  '" . str_pad($noContainer, 3, "0", STR_PAD_LEFT) . "-" . $item->zon_kode . "', ";
                    $query .= "  '" . $PSP_GATE . "', ";
                    $query .= "  '" . $KodeToko . "', ";
                    $query .= "  '" . $PSP_NamaToko . "', ";
                    $query .= "  '" . $BarcodeContainer . "', ";
                    $query .= "  '" . (strlen($PSP_KodeToko) - $i) . "', ";
                    $query .= "  '" . strlen($PSP_KodeToko) . "', ";
                    $query .= "  '" . $PSP_NoSJ . "' ";
                    $query .= ") ";
                    DB::insert($query);

                    if($SudahAdaCetakBarcodeHariIni == false){

                        $this->CetakContainerPSP($item->zon_printer,
                            $PSP_NoPick,
                            date("Ymd"),
                            str_pad($noContainer, 3, "0", STR_PAD_LEFT) . "-" . $item->zon_kode,
                            $PSP_GATE,
                            $PSP_NamaToko,
                            $PSP_NoPB,
                            $BarcodeContainer,
                            (strlen($PSP_KodeToko) - $i),
                            strlen($PSP_KodeToko),
                            '');
                        // CetakContainerPSP(row.Item(3), _
                        //                           PSP_NoPick, _
                        //                           Strings.Format(Now, "dd-MM-yyyy"), _
                        //                           Strings.Right("000" & noContainer.ToString, 3) & "-" & row.Item(0), _
                        //                           PSP_GATE, _
                        //                           PSP_NamaToko, _
                        //                           PSP_NoPB, _
                        //                           BarcodeContainer, _
                        //                           (PSP_KodeToko.Length - i).ToString, _
                        //                           PSP_KodeToko.Length.ToString, _
                        //                           Errmsg)

                        //* UPDATE PICKING CONTAINER RECID 1 MENANDAKAN SUDAH DI PRINT
                        $query = '';
                        $query .= "UPDATE Picking_Container ";
                        $query .= "SET Pico_RecordID = '1' ";
                        $query .= "WHERE PICO_NoPICK = '" . $PSP_NoPick . "' ";
                        //? karena bentuk data PICO_TglPick = 20240116
                        $query .= "AND TO_DATE(PICO_TglPick, 'YYYYMMDD') >= CURRENT_DATE - INTERVAL '7 days' ";
                        $query .= "AND PICO_BarcodeKoli = '" . $BarcodeContainer . "' ";
                        DB::update($query);
                    }

                    $noContainer += 1;
                }

                //* JIKA ADA BROJONG
                if($item->JumlahBronjong > 0){

                    // BarcodeContainer = "02" & Strings.Right("0000000" & PSP_NoPick, 7) & Strings.Right("000" & noBronjong.ToString, 3)
                    $BarcodeContainer = "02" . str_pad($PSP_NoPick, 7, "0", STR_PAD_LEFT) . str_pad($noBronjong, 3, "0", STR_PAD_LEFT);

                    $query = '';
                    $query .= "INSERT INTO PICKING_CONTAINER ( ";
                    $query .= "  PICO_PrinterName, ";
                    $query .= "  PICO_NoPick, ";
                    $query .= "  PICO_TglPick, ";
                    $query .= "  PICO_ContainerZona, ";
                    $query .= "  PICO_Gate, ";
                    $query .= "  PICO_KodeToko, ";
                    $query .= "  PICO_NamaToko, ";
                    $query .= "  PICO_BarcodeKoli, ";
                    $query .= "  PICO_NoUrutToko, ";
                    $query .= "  PICO_JumlahToko, ";
                    $query .= "  PICO_NoSJ ";
                    $query .= ") ";
                    $query .= "VALUES ( ";
                    $query .= "  '" . $item->zon_printer . "', ";
                    $query .= "  '" . $PSP_NoPick . "', ";
                    $query .= "  '" . date("Ymd") . "', ";
                    $query .= "  '" . str_pad($noBronjong, 3, "0", STR_PAD_LEFT) . "-" . $item->zon_kode . "', ";
                    $query .= "  '" . $PSP_GATE . "', ";
                    $query .= "  '" . $PSP_KodeToko . "', ";
                    $query .= "  '" . $PSP_NamaToko . "', ";
                    $query .= "  '" . $BarcodeContainer . "', ";
                    $query .= "  '" . (strlen($PSP_KodeToko) - $i) . "', ";
                    $query .= "  '" . strlen($PSP_KodeToko) . "', ";
                    $query .= "  '" . $PSP_NoSJ . "' ";
                    $query .= ") ";

                    if($SudahAdaCetakBarcodeHariIni == false){

                        $this->CetakBronjongPSP($item->zon_printer,
                            $PSP_NoPick,
                            date("Ymd"),
                            str_pad($noBronjong, 3, "0", STR_PAD_LEFT) . "-" . $item->zon_kode,
                            $PSP_GATE,
                            $PSP_NamaToko,
                            $PSP_NoPB,
                            $BarcodeContainer,
                            (strlen($PSP_KodeToko) - $i),
                            strlen($PSP_KodeToko),
                            '');

                        // CetakBronjongPSP(row.Item(3), _
                        //                          PSP_NoPick, _
                        //                          Strings.Format(Now, "dd-MM-yyyy"), _
                        //                          Strings.Right("000" & noBronjong.ToString, 3) & "-" & row.Item(0), _
                        //                          PSP_GATE, _
                        //                          PSP_NamaToko, _
                        //                          PSP_NoPB, _
                        //                          BarcodeContainer, _
                        //                          (PSP_KodeToko.Length - i).ToString, _
                        //                          PSP_KodeToko.Length.ToString, _
                        //                          Errmsg)

                        //* UPDATE PICKING CONTAINER RECID 1 MENANDAKAN SUDAH DI PRINT
                        $query = '';
                        $query .= "UPDATE Picking_Container ";
                        $query .= "SET Pico_RecordID = '1' ";
                        $query .= "WHERE PICO_NoPICK = '" . $PSP_NoPick . "' ";
                        //? karena bentuk data PICO_TglPick = 20240116
                        $query .= "AND TO_DATE(PICO_TglPick, 'YYYYMMDD') >= CURRENT_DATE - INTERVAL '7 days' ";
                        $query .= "AND PICO_BarcodeKoli = '" . $BarcodeContainer . "' ";
                        DB::update($query);
                    }

                    $noBronjong += 1;
                }
            }
        }

        //* ISI PICKING_ANTRIAN
        $NoUrutPaket = 1;
        $NoUrutTotal = 1;

        $query = '';
        $query .= "INSERT INTO PICKING_ANTRIAN ( ";
        $query .= "  PIA_NoPick, ";
        $query .= "  PIA_TglPick, ";
        $query .= "  PIA_NoSJ, ";
        $query .= "  PIA_KodeToko, ";
        $query .= "  PIA_KodeZona, ";
        $query .= "  PIA_GroupRak, ";
        $query .= "  PIA_NoUrutPaket, ";
        $query .= "  PIA_NoUrutTotal ";
        $query .= ") ";
        $query .= "SELECT DISTINCT ";
        $query .= "  '" . $PSP_NoPick . "' AS NoPick, ";
        $query .= "  TO_DATE('" . $PSP_TglPick . "', 'YYYYMMDD;HH24:MI:SS;') AS TglPick, ";
        $query .= "  '" . $PSP_NoSJ . "' AS NoSJ, ";
        $query .= "  '" . $KodeToko . "' AS KodeToko, ";
        $query .= "  ZON_Kode AS KodeZona,  ";
        $query .= "  grr_GroupRak AS GroupRak, ";
        $query .= "  " . $NoUrutPaket . " AS NoUrutPaket, ";
        $query .= "  " . $NoUrutTotal . " AS NoUrutTotal   ";
        $query .= "FROM tbtr_obi_h h ";
        $query .= "JOIN tbtr_obi_d d ";
        $query .= "     ON h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "     AND h.obi_notrans = d.obi_notrans ";
        $query .= "JOIN tbmaster_lokasi ";
        $query .= "     ON lks_prdcd = SUBSTR(d.obi_prdcd, 1, 6) || '0' ";
        $query .= "     AND lks_noid IS NOT NULL ";
        $query .= "JOIN tbmaster_grouprak ";
        $query .= "     ON grr_koderak = lks_koderak ";
        $query .= "     AND grr_subrak = lks_kodesubrak ";
        $query .= "JOIN zona_idm ";
        $query .= "     ON zon_rak = lks_koderak ";
        $query .= "     AND zon_rak = grr_koderak ";
        $query .= "WHERE h.obi_nopb = '" . $noPB . "' ";
        $query .= "AND h.obi_notrans = '" . $noTrans . "' ";
        $query .= "AND h.obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
        $query .= "AND d.obi_recid IS NULL ";
        $query .= "AND COALESCE(grr_flagcetakan,'?') <> 'Y' ";

        return true;
    }

    //! NOTE KEVIN
    //? INI DIRETURN TRUE AJA KARENA HARUS KE PRINTER
    public function CetakContainerPSP($nmPrnter, $NoPick, $TglPick, $ContainerZona, $Gate, $KodeToko, $NamaToko, $BarcodeKoli, $NoUrutToko, $JumlahToko){
        return true;
    }

    //! NOTE KEVIN
    //? INI DIRETURN TRUE AJA KARENA HARUS KE PRINTER
    public function CetakBronjongPSP($nmPrnter, $NoPick, $TglPick, $ContainerZona, $Gate, $KodeToko, $NamaToko, $BarcodeKoli, $NoUrutToko, $JumlahToko){
        return true;
    }

    public function konversi_SPI($noTrans, $tglTrans){

        try {

            //* DELETE TBTR_KONVERSI_SPI
            $query = '';
            $query .= "DELETE FROM tbtr_konversi_spi ";
            $query .= "WHERE kvi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
            $query .= "AND kvi_notrans = '" . $noTrans . "' ";
            DB::delete($query);

            //* INSERT INTO TBTR_KONVERSI_SPI
            $query = '';
            $query .= "INSERT INTO tbtr_konversi_spi ( ";
            $query .= "  kvi_tgltrans, ";
            $query .= "  kvi_notrans, ";
            $query .= "  kvi_prdcd, ";
            $query .= "  kvi_hargasatuan, ";
            $query .= "  kvi_qtyorder, ";
            $query .= "  kvi_qtyrealisasi, ";
            $query .= "  kvi_ppn, ";
            $query .= "  kvi_diskon, ";
            $query .= "  kvi_hpp, ";
            $query .= "  kvi_kodealamat, ";
            $query .= "  kvi_hargaweb, ";
            $query .= "  kvi_create_by, ";
            $query .= "  kvi_create_dt ";
            $query .= ") ";
            $query .= " ";
            $query .= "SELECT  ";
            $query .= "  obi_tgltrans,  ";
            $query .= "  obi_notrans,  ";
            $query .= "  obi_prdcd,  ";
            $query .= "  obi_hargasatuan,  ";
            $query .= "  obi_qtyorder,  ";
            $query .= "  0 obi_qtyrealisasi,  ";
            $query .= "  obi_ppn,  ";
            $query .= "  obi_diskon,  ";
            $query .= "  obi_hpp,  ";
            $query .= "  000 obi_kodealamat, ";
            $query .= "  obi_hargaweb, ";
            $query .= "  '" . session('userid') . "' create_by, ";
            $query .= "  NOW() create_dt ";
            $query .= "FROM tbtr_obi_d ";
            $query .= "WHERE obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
            $query .= "AND obi_notrans = '" . $noTrans . "' ";
            $query .= "AND obi_recid IS NULL ";
            DB::insert($query);

            //* DELETE TBTR_OBI_D
            $query = '';
            $query .= "DELETE FROM tbtr_obi_d ";
            $query .= "WHERE obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
            $query .= "AND obi_notrans = '" . $noTrans . "' ";
            $query .= "AND obi_recid IS NULL ";
            DB::delete($query);

            //* INSERT INTO TBTR_OBI_D - NEW KONVERSI
            $query = '';
            $query .= "INSERT INTO tbtr_obi_d ( ";
            $query .= "  obi_tgltrans, ";
            $query .= "  obi_notrans, ";
            $query .= "  obi_prdcd, ";
            $query .= "  obi_hargasatuan, ";
            $query .= "  obi_qtyorder, ";
            $query .= "  obi_qtyrealisasi, ";
            $query .= "  obi_ppn, ";
            $query .= "  obi_diskon, ";
            $query .= "  obi_hpp, ";
            $query .= "  obi_kodealamat, ";
            $query .= "  obi_hargaweb ";
            $query .= ") ";
            $query .= "SELECT ";
            $query .= "    kvi_tgltrans, ";
            $query .= "    kvi_notrans, ";
            $query .= "	   CASE SUBSTR(kvi_prdcd, LENGTH(kvi_prdcd), 1) WHEN '0' THEN  ";
            $query .= "        CASE COALESCE(lks_noidctn, 'XXX') WHEN 'XXX' THEN plu ELSE kvi_prdcd END ";
            $query .= "    ELSE plu END kvi_prdcd, ";
            $query .= "    MIN(kvi_hargasatuan) kvi_hargasatuan, ";
            $query .= "    SUM(kvi_qtyorder) kvi_qtyorder, ";
            $query .= "    0 kvi_qtyrealisasi, ";
            $query .= "    MIN(kvi_ppn) obi_ppn, ";
            $query .= "    ROUND(SUM(ROUND(kvi_diskon * kvi_qtyorder)) / SUM(kvi_qtyorder),2) kvi_diskon, ";
            $query .= "    0 kvi_hpp, ";
            $query .= "    '000' kvi_kodealamat, ";
            $query .= "    MIN(kvi_hargasatuan + kvi_ppn) kvi_hargaweb ";
            $query .= "FROM tbtr_konversi_spi ";
            $query .= "JOIN tbmaster_prodmast ";
            $query .= "ON prd_prdcd = kvi_prdcd ";
            $query .= "JOIN ( ";
            $query .= "  SELECT plu, frac ";
            $query .= "  FROM ( ";
            $query .= "    SELECT prd_prdcd plu, prd_frac frac, ";
            $query .= "           substr(prd_prdcd,-1,1), ";
            $query .= "           ROW_NUMBER() OVER( ";
            $query .= "             PARTITION BY substr(prd_prdcd,1,6)  ";
            $query .= "             ORDER BY substr(prd_prdcd,-1,1) ASC ";
            $query .= "           ) AS rn ";
            $query .= "    FROM tbmaster_prodmast ";
            $query .= "  ) datas ";
            $query .= "  WHERE rn = 2 ";
            $query .= ") plu_kecil ";
            $query .= "ON SUBSTR(plu,1,6) = SUBSTR(kvi_prdcd,1,6) ";
            $query .= "WHERE kvi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
            $query .= "AND kvi_notrans = '" . $noTrans . "' ";
            $query .= "GROUP BY kvi_tgltrans, kvi_notrans, plu ";
            $query .= "ORDER BY plu ";
            DB::insert($query);

            //* UPDATE TBTR_OBI_H - OBI_ITEMORDER
            $query = '';
            $query .= "SELECT * FROM tbtr_obi_d ";
            $query .= "WHERE obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
            $query .= "AND obi_notrans = '" . $noTrans . "' ";
            $query .= "AND obi_recid IS NULL ";
            $cek = DB::select($query);

            if(count($cek)){
                $query = '';
                $query .= "UPDATE tbtr_obi_h ";
                $query .= "SET obi_itemorder = " . count($cek) . " ";
                $query .= "WHERE obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
                $query .= "AND obi_notrans = '" . $noTrans . "' ";
                $query = DB::update($query);
            }

            $query = '';
            $query .= " UPDATE TBTR_OBI_D ";
            $query .= " SET obi_hargaweb = (obi_hargasatuan+obi_ppn) * ( ";
            $query .= "                      SELECT COALESCE(prd_frac,1)  ";
            $query .= "                      FROM tbmaster_prodmast  ";
            $query .= "                      WHERE prd_prdcd = obi_prdcd ";
            $query .= "                      LIMIT 1) ";
            $query .= "WHERE obi_tgltrans = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
            $query .= "AND obi_notrans = '" . $noTrans . "' ";
            $query .= "AND obi_recid IS NULL ";
            DB::update($query);

            return true;

        } catch(\Exception $e){

            return false;
        }
    }
}
