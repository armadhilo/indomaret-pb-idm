<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

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

    public function insertToNPB($cabang, $NamaFile, $dtH, $dtD){

    }

    public function logUpdateStatus($notrans, $tgltrans, $nopb, $statusBaru, $statusKlik){
        if (str_contains($nopb, 'TMI')) {
            return;
        }

        $noTrx = explode('/', $nopb)[0];

        $flag = '1';
        //! INI AGAK MEMBINGUNGKAN
        // If respKlik("response_code").ToString <> "200" Then
        //     flag = "0"
        // Else

        sb.AppendLine("INSERT INTO log_obi_status ( ")
        sb.AppendLine("  notrans, ")
        sb.AppendLine("  tgltrans, ")
        sb.AppendLine("  nopb, ")
        sb.AppendLine("  notrx_klik, ")
        sb.AppendLine("  status_baru, ")
        sb.AppendLine("  flag, ")
        sb.AppendLine("  url, ")
        sb.AppendLine("  status_klik, ")
        sb.AppendLine("  response, ")
        sb.AppendLine("  create_by, ")
        sb.AppendLine("  create_dt ")
        sb.AppendLine(") VALUES ( ")
        sb.AppendLine("  '" & notrans & "', ")
        sb.AppendLine("  TO_DATE('" & tgltrans & "','DD-MM-YYYY'), ")
        sb.AppendLine("  '" & nopb & "', ")
        sb.AppendLine("  '" & notrx & "', ")
        sb.AppendLine("  '" & statusBaru & "', ")
        sb.AppendLine("  '" & flag & "', ")
        sb.AppendLine("  '" & urlUpdateStatusKlik & "', ")
        sb.AppendLine("  '" & statusKlik & "', ")
        sb.AppendLine("  '" & respKlik.ToString & "', ")
        sb.AppendLine("  '" & UserMODUL & "', ")
        sb.AppendLine("  NOW() ")
        sb.AppendLine(") ")
    }

    //! SPI - DIPAKE DI KLIK IGR
    public function createTablePSP_SPI(){

        //! CEK SEQUENCE SEQ_PICKING_SPI
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

        //! CREATE TABLE TBMASTER_GROUPRAK
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

        //! CREATE TABLE TEMP_DPD_IDM
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

        //! CREATE TABLE DPD_IDM_NOBARCODE
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

        //! CREATE TABLE TEMP_HHELD_IDM
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

        //! CREATE TABLE HHELD_IDM_ORA
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

        //! CREATE TABLE HHELD_HISTORY_IDM
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

        //! CREATE TABLE DPD_IDM_ORA
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

        //! CREATE TABLE DPD_HISTORY_IDM
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

        //! CREATE TABLE ZONA_IDM
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

        //! CREATE TABLE CONTAINER_IDM
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

        //! CEK DATA CONTAINER_IDM - CONTAINER
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

        //! CEK DATA CONTAINER_IDM - BRONJONG
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

        //! CREATE TABLE PICKING_ANTRIAN
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

        //! CREATE TABLE PICKING_CONTAINER
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

        //! CREATE TABLE DPD_DATA_IDM
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

        //! CREATE TABLE DPD_MASTER_IGR
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

        //! CREATE TABLE TBTR_DELIVERY_SPI
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

        //! CEK COLUMN TBTR_DELIVERY_SPI - DEL_NOPOL
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_NOPOL'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_NOPOL VARCHAR(100)");
        }

        //! CEK COLUMN TBTR_DELIVERY_SPI - DEL_DRIVER
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_DRIVER'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD DEL_DRIVER VARCHAR(100)");
        }

        //! CEK COLUMN TBTR_DELIVERY_SPI - E
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_DELIVERYMAN'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_DELIVERYMAN VARCHAR(100)");
        }

        //! CEK COLUMN TBTR_DELIVERY_SPI - DEL_NOLISTING
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_NOLISTING'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_NOLISTING VARCHAR(20)");
        }

        //! CEK COLUMN TBTR_DELIVERY_SPI - DEL_FLAGBATAL
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_FLAGBATAL'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_FLAGBATAL VARCHAR(2)");
        }

        //! CREATE TABLE TEMP_DELIVERY_SPI
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

        //! CREATE TABLE TBTR_KONVERSI_SPI
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

        //! CREATE TABLE TBTR_BAREFUND_SPI
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

        //! CREATE TABLE TEMP_BAREFUND_SPI
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

        //! CREATE TABLE TBTR_BARUSAK_SPI
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

        //! CEK SEQUENCE SEQ_BA_REFUND_SPI
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

        //! CEK SEQUENCE SEQ_BA_RUSAK_SPI
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

        //! CEK SEQUENCE SEQ_LIST_DELIVERY_SPI
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

        //! CEK COLUMN TBTR_DELIVERY_SPI - DEL_NOLISTING
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_NOLISTING'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD COLUMN DEL_NOLISTING VARCHAR(20)");
        }

        //! CEK COLUMN TBTR_DSP_SPI - DSP_NOLISTING
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DSP_SPI'")
            ->whereRaw("upper(column_name) = 'DSP_NOLISTING'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DSP_SPI ADD COLUMN DSP_NOLISTING VARCHAR(20)");
        }
    }

    public function addColHitungUlang_SPI(){

        //! ADD COLUMN TBTR_OBI_D - OBI_QTY_HITUNGULANG
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_OBI_D'")
            ->whereRaw("upper(column_name) = 'OBI_QTY_HITUNGULANG'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_OBI_D ADD COLUMN OBI_QTY_HITUNGULANG NUMERIC");
        }

        //! ADD COLUMN PROMO_KLIKIGR - CASHBACK_HITUNGULANG
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'PROMO_KLIKIGR'")
            ->whereRaw("upper(column_name) = 'CASHBACK_HITUNGULANG'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE PROMO_KLIKIGR ADD COLUMN CASHBACK_HITUNGULANG NUMERIC");
        }

        //! ADD COLUMN PROMO_KLIKIGR - KELIPATAN_HITUNGULANG
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'PROMO_KLIKIGR'")
            ->whereRaw("upper(column_name) = 'KELIPATAN_HITUNGULANG'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE PROMO_KLIKIGR ADD COLUMN KELIPATAN_HITUNGULANG NUMERIC");
        }

        //! ADD COLUMN PROMO_KLIKIGR - REWARD_PER_PROMO_HITUNGULANG
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'PROMO_KLIKIGR'")
            ->whereRaw("upper(column_name) = 'REWARD_PER_PROMO_HITUNGULANG'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE PROMO_KLIKIGR ADD COLUMN REWARD_PER_PROMO_HITUNGULANG NUMERIC");
        }

        //! ADD COLUMN PROMO_KLIKIGR - REWARD_NOMINAL_HITUNGULANG
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'PROMO_KLIKIGR'")
            ->whereRaw("upper(column_name) = 'REWARD_NOMINAL_HITUNGULANG'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE PROMO_KLIKIGR ADD COLUMN REWARD_NOMINAL_HITUNGULANG NUMERIC");
        }

        //! ADD COLUMN TBTR_OBI_D - OBI_QTYBA
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

        //! TAMBAHAN CEGATAN SUDAH PERNAH SEND JALUR
        $query = '';
        $query .= " SELECT DISTINCT nopicking, nosuratjalan  ";
        $query .= " FROM dpd_idm_ora ";
        $query .= " WHERE fmndoc = '" . $PSP_NoPB . "' ";
        $query .= " AND fmkcab = 'SPI0' ";
        $query .= " AND tglpb = TO_CHAR(TO_DATE('" . $PSP_TglPB . "','DD-MM-YYYY'), 'YYYYMMDD') ";
        $dtCek = DB::select($query);

        if(count($dtCek)){
            throw new HttpResponseException(ApiFormatter::error(400, "Sudah pernah send jalur noPick
            noPick " . $dtCek[0]->nopicking . " | noSJ " . $dtCek[0]->nosuratjalan));
        }

        if($this->konversi_SPI($noTrans, $tglTrans) == false){
            return;
        }

        // ExecScalar("select nextval('SEQ_PICKING_SPI')", "GET NO PICK", PSP_NoPick)
        // PSP_NoSJ = PSP_NoPick

        // noPick = PSP_NoPick
        // noSJ = PSP_NoSJ

        // ExecScalar("SELECT TO_CHAR(current_timestamp, 'YYYYMMDD;HH24:MI:SS;') FROM DUAL", "GET NO PICK", PSP_TglPick)

        //! DELETE TEMP_DPD_IDM
        DB::table('temp_dpd_idm')->where('req_id', $this->getIP());

        //! DELETE DPD_IDM_NOBARCODE
        DB::table('dpd_idm_nobarcode')->where('req_id', $this->getIP());

        //! DELETE TEMP_HHELD_IDM
        DB::table('temp_hheld_idm')->where('req_id', $this->getIP());

        //! INSERT INTO TEMP_DPD_IDM - BULKY
        sb.AppendLine("INSERT INTO TEMP_DPD_IDM ( ")
        sb.AppendLine("  FMNDOC, ")
        sb.AppendLine("  FMNBTC, ")
        sb.AppendLine("  TRNRAK, ")
        sb.AppendLine("  FMNTRN, ")
        sb.AppendLine("  PRDCD, ")
        sb.AppendLine("  BARC, ")
        sb.AppendLine("  BAR2, ")
        sb.AppendLine("  BARK, ")
        sb.AppendLine("  GRAK, ")
        sb.AppendLine("  NOUR, ")
        sb.AppendLine("  KODERAK, ")
        sb.AppendLine("  NOID, ")
        sb.AppendLine("  SATUAN, ")
        sb.AppendLine("  FMKSBU, ")
        sb.AppendLine("  FMKCAB, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  DESC2, ")
        sb.AppendLine("  QTYO, ")
        sb.AppendLine("  STOK, ")
        sb.AppendLine("  QTYR, ")
        sb.AppendLine("  FMSTS, ")
        sb.AppendLine("  ""time"", ")
        sb.AppendLine("  REQ_ID ")
        sb.AppendLine(") ")
        sb.AppendLine("SELECT ")
        sb.AppendLine("  fmndoc, ")
        sb.AppendLine("  fmnbtc, ")
        sb.AppendLine("  trnrak, ")
        sb.AppendLine("  fmntrn, ")
        sb.AppendLine("  prdcd, ")
        sb.AppendLine("  MIN(brc_barcode) AS barc, ")
        sb.AppendLine("  CASE WHEN MIN(brc_barcode) = MAX(brc_barcode) ")
        sb.AppendLine("    THEN NULL ")
        sb.AppendLine("    ELSE MAX(brc_barcode) ")
        sb.AppendLine("  END AS bar2, ")
        sb.AppendLine("  bark, ")
        sb.AppendLine("  grak, ")
        sb.AppendLine("  nour, ")
        sb.AppendLine("  koderak, ")
        sb.AppendLine("  noid, ")
        sb.AppendLine("  satuan, ")
        sb.AppendLine("  fmksbu, ")
        sb.AppendLine("  fmkcab, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  desc2, ")
        sb.AppendLine("  qtyo, ")
        sb.AppendLine("  stok, ")
        sb.AppendLine("  qtyr, ")
        sb.AppendLine("  fmsts, ")
        sb.AppendLine("  ""time"", ")
        sb.AppendLine("  '" & IP & "' ")
        sb.AppendLine("FROM ( ")
        sb.AppendLine("  SELECT ")
        sb.AppendLine("    '" & PSP_NoPB & "' AS fmndoc, ")
        sb.AppendLine("    NULL AS fmnbtc, ")
        sb.AppendLine("    SUBSTR( ")
        sb.AppendLine("      RPAD('" & PSP_NoPB & "', 9, ' ') ")
        sb.AppendLine("      || SUBSTR(GRR_GROUPRAK, 1, 5) ")
        sb.AppendLine("      || LPAD(grr_nourut::text, 2, ' ') ")
        sb.AppendLine("      || RPAD(LKS_KodeRak, 7, ' ') ")
        sb.AppendLine("      || RPAD(LKS_KodeSubRak, 3, ' ') ")
        sb.AppendLine("      || RPAD(LKS_TipeRak, 3, ' ') ")
        sb.AppendLine("      || RPAD(LKS_ShelvingRak, 2, ' ') ")
        sb.AppendLine("      || obi_prdcd, ")
        sb.AppendLine("      1, 36 ")
        sb.AppendLine("    ) AS trnrak, ")
        sb.AppendLine("    NULL AS fmntrn, ")
        sb.AppendLine("    obi_prdcd AS prdcd, ")
        sb.AppendLine("    NULL AS bark, ")
        sb.AppendLine("    grr_grouprak AS grak, ")
        sb.AppendLine("    grr_nourut AS nour, ")
        sb.AppendLine("    SUBSTR( ")
        sb.AppendLine("      RPAD(lks_koderak, 7, ' ') ")
        sb.AppendLine("      || RPAD(lks_kodesubrak, 3, ' ') ")
        sb.AppendLine("      || RPAD(lks_tiperak, 3, ' ')  ")
        sb.AppendLine("      || RPAD(lks_shelvingrak, 2, ' ') ")
        sb.AppendLine("      || LPAD( ")
        sb.AppendLine("           CASE WHEN SUBSTR(lks_koderak, 1, 1) = 'D' ")
        sb.AppendLine("             THEN '0' ")
        sb.AppendLine("             ELSE lks_nourut::text ")
        sb.AppendLine("           END, 3, ' ' ")
        sb.AppendLine("         ),  ")
        sb.AppendLine("      1, 18 ")
        sb.AppendLine("    ) AS koderak, ")
        sb.AppendLine("    lks_noid AS noid, ")
        sb.AppendLine("    prd_unit||'/'||prd_frac AS satuan, ")
        sb.AppendLine("    '" & FMKSBU & "' AS fmksbu, ")
        sb.AppendLine("    '" & KodeToko & "' AS fmkcab, ")
        sb.AppendLine("    prd_deskripsipendek AS ""desc"", ")
        sb.AppendLine("    prd_deskripsipanjang AS desc2, ")
        sb.AppendLine("    d.obi_qtyorder/prd_frac AS qtyo, ")
        sb.AppendLine("    st_saldoakhir AS stok, ")
        sb.AppendLine("    d.obi_qtyrealisasi/prd_frac AS qtyr, ")
        sb.AppendLine("    NULL AS fmsts, ")
        sb.AppendLine("    NULL AS ""time"" ")
        sb.AppendLine("  FROM tbtr_obi_h h ")
        sb.AppendLine("  JOIN tbtr_obi_d d ")
        sb.AppendLine("       ON h.obi_tgltrans = d.obi_tgltrans ")
        sb.AppendLine("       AND h.obi_notrans = d.obi_notrans ")
        sb.AppendLine("  JOIN tbmaster_prodmast  ")
        sb.AppendLine("       ON prd_prdcd = d.obi_prdcd ")
        sb.AppendLine("  JOIN tbmaster_lokasi  ")
        sb.AppendLine("       ON lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("  JOIN tbmaster_grouprak ")
        sb.AppendLine("       ON grr_koderak = lks_koderak ")
        sb.AppendLine("       AND grr_subrak = lks_kodesubrak ")
        sb.AppendLine("  JOIN tbmaster_stock ")
        sb.AppendLine("       ON st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("  WHERE h.obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
        sb.AppendLine("  AND h.obi_notrans = '" & noTrans & "' ")
        sb.AppendLine("  AND h.obi_nopb = '" & noPB & "' ")
        sb.AppendLine("  AND MOD(d.obi_qtyorder, prd_frac) = 0 ")
        sb.AppendLine("  AND d.obi_recid IS NULL ")
        sb.AppendLine("  AND st_lokasi = '01' ")
        sb.AppendLine("  AND lks_noid LIKE '%B' ")
        sb.AppendLine("  AND lks_tiperak NOT LIKE 'S%' ")
        sb.AppendLine("  AND COALESCE(grr_flagcetakan,'X') <> 'Y' ")
        sb.AppendLine("  AND grr_grouprak NOT LIKE 'H%' ")
        sb.AppendLine(") A  ")
        sb.AppendLine("LEFT JOIN tbmaster_barcode ")
        sb.AppendLine("ON brc_prdcd = prdcd ")
        sb.AppendLine("GROUP BY ")
        sb.AppendLine("  fmndoc, ")
        sb.AppendLine("  fmnbtc, ")
        sb.AppendLine("  trnrak, ")
        sb.AppendLine("  fmntrn, ")
        sb.AppendLine("  prdcd, ")
        sb.AppendLine("  bark, ")
        sb.AppendLine("  grak, ")
        sb.AppendLine("  nour, ")
        sb.AppendLine("  koderak, ")
        sb.AppendLine("  noid, ")
        sb.AppendLine("  satuan, ")
        sb.AppendLine("  fmksbu, ")
        sb.AppendLine("  fmkcab, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  desc2, ")
        sb.AppendLine("  qtyo, ")
        sb.AppendLine("  stok, ")
        sb.AppendLine("  qtyr, ")
        sb.AppendLine("  fmsts, ")
        sb.AppendLine("  ""time"" ")
        sb.AppendLine("ORDER BY koderak, nour ")

        //! INSERT INTO DPD_IDM_NOBARCODE - BULKY
        sb.AppendLine("INSERT INTO DPD_IDM_NOBARCODE ( ")
        sb.AppendLine("  FMNDOC, ")
        sb.AppendLine("  FMNBTC, ")
        sb.AppendLine("  TRNRAK, ")
        sb.AppendLine("  FMNTRN, ")
        sb.AppendLine("  PRDCD, ")
        sb.AppendLine("  BARC, ")
        sb.AppendLine("  BAR2, ")
        sb.AppendLine("  BARK, ")
        sb.AppendLine("  GRAK, ")
        sb.AppendLine("  NOUR, ")
        sb.AppendLine("  KODERAK, ")
        sb.AppendLine("  NOID, ")
        sb.AppendLine("  SATUAN, ")
        sb.AppendLine("  FMKSBU, ")
        sb.AppendLine("  FMKCAB, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  DESC2, ")
        sb.AppendLine("  QTYO, ")
        sb.AppendLine("  STOK, ")
        sb.AppendLine("  QTYR, ")
        sb.AppendLine("  FMSTS, ")
        sb.AppendLine("  ""time"", ")
        sb.AppendLine("  REQ_ID ")
        sb.AppendLine(") ")
        sb.AppendLine("SELECT ")
        sb.AppendLine("  fmndoc, ")
        sb.AppendLine("  fmnbtc, ")
        sb.AppendLine("  trnrak, ")
        sb.AppendLine("  fmntrn, ")
        sb.AppendLine("  prdcd, ")
        sb.AppendLine("  '' AS barc, ")
        sb.AppendLine("  '' AS bar2, ")
        sb.AppendLine("  bark, ")
        sb.AppendLine("  grak, ")
        sb.AppendLine("  nour, ")
        sb.AppendLine("  koderak, ")
        sb.AppendLine("  noid, ")
        sb.AppendLine("  satuan, ")
        sb.AppendLine("  fmksbu, ")
        sb.AppendLine("  fmkcab, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  desc2, ")
        sb.AppendLine("  qtyo, ")
        sb.AppendLine("  stok, ")
        sb.AppendLine("  qtyr, ")
        sb.AppendLine("  fmsts, ")
        sb.AppendLine("  ""time"", ")
        sb.AppendLine("  '" & IP & "' ")
        sb.AppendLine("FROM ( ")
        sb.AppendLine("  SELECT ")
        sb.AppendLine("    '" & PSP_NoPB & "' AS fmndoc, ")
        sb.AppendLine("    NULL AS fmnbtc, ")
        sb.AppendLine("    SUBSTR( ")
        sb.AppendLine("      RPAD('" & PSP_NoPB & "', 9, ' ') ")
        sb.AppendLine("      || SUBSTR(GRR_GROUPRAK, 1, 5) ")
        sb.AppendLine("      || LPAD(grr_nourut::text, 2, ' ') ")
        sb.AppendLine("      || RPAD(LKS_KodeRak, 7, ' ') ")
        sb.AppendLine("      || RPAD(LKS_KodeSubRak, 3, ' ') ")
        sb.AppendLine("      || RPAD(LKS_TipeRak, 3, ' ') ")
        sb.AppendLine("      || RPAD(LKS_ShelvingRak, 2, ' ') ")
        sb.AppendLine("      || obi_prdcd, ")
        sb.AppendLine("      1, 36 ")
        sb.AppendLine("    ) AS trnrak, ")
        sb.AppendLine("    NULL AS fmntrn, ")
        sb.AppendLine("    obi_prdcd AS prdcd, ")
        sb.AppendLine("    NULL AS bark, ")
        sb.AppendLine("    grr_grouprak AS grak, ")
        sb.AppendLine("    grr_nourut AS nour, ")
        sb.AppendLine("    SUBSTR( ")
        sb.AppendLine("      RPAD(lks_koderak, 7, ' ') ")
        sb.AppendLine("      || RPAD(lks_kodesubrak, 3, ' ') ")
        sb.AppendLine("      || RPAD(lks_tiperak, 3, ' ')  ")
        sb.AppendLine("      || RPAD(lks_shelvingrak, 2, ' ') ")
        sb.AppendLine("      || LPAD( ")
        sb.AppendLine("           CASE WHEN SUBSTR(lks_koderak, 1, 1) = 'D' ")
        sb.AppendLine("             THEN '0' ")
        sb.AppendLine("             ELSE lks_nourut::text ")
        sb.AppendLine("           END, 3, ' ' ")
        sb.AppendLine("         ),  ")
        sb.AppendLine("      1, 18 ")
        sb.AppendLine("    ) AS koderak, ")
        sb.AppendLine("    lks_noid AS noid, ")
        sb.AppendLine("    prd_unit||'/'||prd_frac AS satuan, ")
        sb.AppendLine("    '" & FMKSBU & "' AS fmksbu, ")
        sb.AppendLine("    '" & KodeToko & "' AS fmkcab, ")
        sb.AppendLine("    prd_deskripsipendek AS ""desc"", ")
        sb.AppendLine("    prd_deskripsipanjang AS desc2, ")
        sb.AppendLine("    d.obi_qtyorder/prd_frac AS qtyo, ")
        sb.AppendLine("    st_saldoakhir AS stok, ")
        sb.AppendLine("    d.obi_qtyrealisasi/prd_frac AS qtyr, ")
        sb.AppendLine("    NULL AS fmsts, ")
        sb.AppendLine("    NULL AS ""time"" ")
        sb.AppendLine("  FROM tbtr_obi_h h ")
        sb.AppendLine("  JOIN tbtr_obi_d d ")
        sb.AppendLine("       ON h.obi_tgltrans = d.obi_tgltrans ")
        sb.AppendLine("       AND h.obi_notrans = d.obi_notrans ")
        sb.AppendLine("  JOIN tbmaster_prodmast  ")
        sb.AppendLine("       ON prd_prdcd = d.obi_prdcd ")
        sb.AppendLine("  JOIN tbmaster_lokasi  ")
        sb.AppendLine("       ON lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("  JOIN tbmaster_grouprak ")
        sb.AppendLine("       ON grr_koderak = lks_koderak ")
        sb.AppendLine("       AND grr_subrak = lks_kodesubrak ")
        sb.AppendLine("  JOIN tbmaster_stock ")
        sb.AppendLine("       ON st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("  WHERE h.obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
        sb.AppendLine("  AND h.obi_notrans = '" & noTrans & "' ")
        sb.AppendLine("  AND h.obi_nopb = '" & noPB & "' ")
        sb.AppendLine("  AND MOD(d.obi_qtyorder, prd_frac) = 0 ")
        sb.AppendLine("  AND d.obi_recid IS NULL ")
        sb.AppendLine("  AND st_lokasi = '01' ")
        sb.AppendLine("  AND lks_noid LIKE '%B' ")
        sb.AppendLine("  AND lks_tiperak NOT LIKE 'S%' ")
        sb.AppendLine("  AND COALESCE(grr_flagcetakan,'X') <> 'Y' ")
        sb.AppendLine("  AND grr_grouprak NOT LIKE 'H%' ")
        sb.AppendLine(") A  ")
        sb.AppendLine("WHERE NOT EXISTS ( ")
        sb.AppendLine("  SELECT brc_barcode ")
        sb.AppendLine("  FROM tbmaster_barcode ")
        sb.AppendLine("  WHERE brc_prdcd = prdcd ")
        sb.AppendLine(") ")
        sb.AppendLine("ORDER BY koderak, nour ")

        //! INSERT INTO TEMP_DPD_IDM - PIECES
        sb.AppendLine("INSERT INTO TEMP_DPD_IDM ( ")
        sb.AppendLine("  FMNDOC, ")
        sb.AppendLine("  FMNBTC, ")
        sb.AppendLine("  TRNRAK, ")
        sb.AppendLine("  FMNTRN, ")
        sb.AppendLine("  PRDCD, ")
        sb.AppendLine("  BARC, ")
        sb.AppendLine("  BAR2, ")
        sb.AppendLine("  BARK, ")
        sb.AppendLine("  GRAK, ")
        sb.AppendLine("  NOUR, ")
        sb.AppendLine("  KODERAK, ")
        sb.AppendLine("  NOID, ")
        sb.AppendLine("  SATUAN, ")
        sb.AppendLine("  FMKSBU, ")
        sb.AppendLine("  FMKCAB, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  DESC2, ")
        sb.AppendLine("  QTYO, ")
        sb.AppendLine("  STOK, ")
        sb.AppendLine("  QTYR, ")
        sb.AppendLine("  FMSTS, ")
        sb.AppendLine("  ""time"", ")
        sb.AppendLine("  REQ_ID ")
        sb.AppendLine(") ")
        sb.AppendLine("SELECT ")
        sb.AppendLine("  fmndoc, ")
        sb.AppendLine("  fmnbtc, ")
        sb.AppendLine("  trnrak, ")
        sb.AppendLine("  fmntrn, ")
        sb.AppendLine("  prdcd, ")
        sb.AppendLine("  MIN(brc_barcode) AS barc, ")
        sb.AppendLine("  CASE WHEN MIN(brc_barcode) = MAX(brc_barcode) ")
        sb.AppendLine("    THEN NULL ")
        sb.AppendLine("    ELSE MAX(brc_barcode) ")
        sb.AppendLine("  END AS bar2, ")
        sb.AppendLine("  bark, ")
        sb.AppendLine("  grak, ")
        sb.AppendLine("  nour, ")
        sb.AppendLine("  koderak, ")
        sb.AppendLine("  noid, ")
        sb.AppendLine("  satuan, ")
        sb.AppendLine("  fmksbu, ")
        sb.AppendLine("  fmkcab, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  desc2, ")
        sb.AppendLine("  qtyo, ")
        sb.AppendLine("  stok, ")
        sb.AppendLine("  qtyr, ")
        sb.AppendLine("  fmsts, ")
        sb.AppendLine("  ""time"", ")
        sb.AppendLine("  '" & IP & "' ")
        sb.AppendLine("FROM ( ")
        sb.AppendLine("  SELECT ")
        sb.AppendLine("    '" & PSP_NoPB & "' AS fmndoc, ")
        sb.AppendLine("    NULL AS fmnbtc, ")
        sb.AppendLine("    SUBSTR( ")
        sb.AppendLine("      RPAD('" & PSP_NoPB & "', 9, ' ') ")
        sb.AppendLine("      || SUBSTR(GRR_GROUPRAK, 1, 5) ")
        sb.AppendLine("      || LPAD(grr_nourut::text, 2, ' ') ")
        sb.AppendLine("      || RPAD(LKS_KodeRak, 7, ' ') ")
        sb.AppendLine("      || RPAD(LKS_KodeSubRak, 3, ' ') ")
        sb.AppendLine("      || RPAD(LKS_TipeRak, 3, ' ') ")
        sb.AppendLine("      || RPAD(LKS_ShelvingRak, 2, ' ') ")
        sb.AppendLine("      || obi_prdcd, ")
        sb.AppendLine("      1, 36 ")
        sb.AppendLine("    ) AS trnrak, ")
        sb.AppendLine("    NULL AS fmntrn, ")
        sb.AppendLine("    obi_prdcd AS prdcd, ")
        sb.AppendLine("    NULL AS bark, ")
        sb.AppendLine("    grr_grouprak AS grak, ")
        sb.AppendLine("    grr_nourut AS nour, ")
        sb.AppendLine("    SUBSTR( ")
        sb.AppendLine("      RPAD(lks_koderak, 7, ' ') ")
        sb.AppendLine("      || RPAD(lks_kodesubrak, 3, ' ') ")
        sb.AppendLine("      || RPAD(lks_tiperak, 3, ' ')  ")
        sb.AppendLine("      || RPAD(lks_shelvingrak, 2, ' ') ")
        sb.AppendLine("      || LPAD( ")
        sb.AppendLine("           CASE WHEN SUBSTR(lks_koderak, 1, 1) = 'D' ")
        sb.AppendLine("             THEN '0' ")
        sb.AppendLine("             ELSE lks_nourut::text ")
        sb.AppendLine("           END, 3, ' ' ")
        sb.AppendLine("         ),  ")
        sb.AppendLine("      1, 18 ")
        sb.AppendLine("    ) AS koderak, ")
        sb.AppendLine("    lks_noid AS noid, ")
        sb.AppendLine("    prd_unit||'/'||prd_frac AS satuan, ")
        sb.AppendLine("    '" & FMKSBU & "' AS fmksbu, ")
        sb.AppendLine("    '" & KodeToko & "' AS fmkcab, ")
        sb.AppendLine("    prd_deskripsipendek AS ""desc"", ")
        sb.AppendLine("    prd_deskripsipanjang AS desc2, ")
        sb.AppendLine("    d.obi_qtyorder / CASE WHEN prd_unit = 'KG' THEN prd_frac ELSE 1 END AS qtyo, ")
        sb.AppendLine("    st_saldoakhir AS stok, ")
        sb.AppendLine("    d.obi_qtyrealisasi / CASE WHEN prd_unit = 'KG' THEN prd_frac ELSE 1 END AS qtyr, ")
        sb.AppendLine("    NULL AS fmsts, ")
        sb.AppendLine("    NULL AS ""time"" ")
        sb.AppendLine("  FROM tbtr_obi_h h ")
        sb.AppendLine("  JOIN tbtr_obi_d d ")
        sb.AppendLine("       ON h.obi_tgltrans = d.obi_tgltrans ")
        sb.AppendLine("       AND h.obi_notrans = d.obi_notrans ")
        sb.AppendLine("  JOIN tbmaster_prodmast  ")
        sb.AppendLine("       ON prd_prdcd = d.obi_prdcd ")
        sb.AppendLine("  JOIN tbmaster_lokasi  ")
        sb.AppendLine("       ON lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("  JOIN tbmaster_grouprak ")
        sb.AppendLine("       ON grr_koderak = lks_koderak ")
        sb.AppendLine("       AND grr_subrak = lks_kodesubrak ")
        sb.AppendLine("  JOIN tbmaster_stock ")
        sb.AppendLine("       ON st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("  WHERE h.obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
        sb.AppendLine("  AND h.obi_notrans = '" & noTrans & "' ")
        sb.AppendLine("  AND h.obi_nopb = '" & noPB & "' ")
        sb.AppendLine("  AND d.obi_recid IS NULL ")
        sb.AppendLine("  AND st_lokasi = '01' ")
        sb.AppendLine("  AND lks_noid LIKE '%P' ")
        sb.AppendLine("  AND lks_tiperak NOT LIKE 'S%' ")
        sb.AppendLine("  AND COALESCE(grr_flagcetakan,'X') <> 'Y' ")
        sb.AppendLine("  AND grr_grouprak NOT LIKE 'H%' ")
        sb.AppendLine(") A  ")
        sb.AppendLine("LEFT JOIN tbmaster_barcode ")
        sb.AppendLine("ON brc_prdcd = prdcd ")
        sb.AppendLine("GROUP BY ")
        sb.AppendLine("  fmndoc, ")
        sb.AppendLine("  fmnbtc, ")
        sb.AppendLine("  trnrak, ")
        sb.AppendLine("  fmntrn, ")
        sb.AppendLine("  prdcd, ")
        sb.AppendLine("  bark, ")
        sb.AppendLine("  grak, ")
        sb.AppendLine("  nour, ")
        sb.AppendLine("  koderak, ")
        sb.AppendLine("  noid, ")
        sb.AppendLine("  satuan, ")
        sb.AppendLine("  fmksbu, ")
        sb.AppendLine("  fmkcab, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  desc2, ")
        sb.AppendLine("  qtyo, ")
        sb.AppendLine("  stok, ")
        sb.AppendLine("  qtyr, ")
        sb.AppendLine("  fmsts, ")
        sb.AppendLine("  ""time"" ")
        sb.AppendLine("ORDER BY koderak, nour ")

        //! INSERT INTO DPD_IDM_NOBARCODE - PIECES
        sb.AppendLine("INSERT INTO DPD_IDM_NOBARCODE ( ")
        sb.AppendLine("  FMNDOC, ")
        sb.AppendLine("  FMNBTC, ")
        sb.AppendLine("  TRNRAK, ")
        sb.AppendLine("  FMNTRN, ")
        sb.AppendLine("  PRDCD, ")
        sb.AppendLine("  BARC, ")
        sb.AppendLine("  BAR2, ")
        sb.AppendLine("  BARK, ")
        sb.AppendLine("  GRAK, ")
        sb.AppendLine("  NOUR, ")
        sb.AppendLine("  KODERAK, ")
        sb.AppendLine("  NOID, ")
        sb.AppendLine("  SATUAN, ")
        sb.AppendLine("  FMKSBU, ")
        sb.AppendLine("  FMKCAB, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  DESC2, ")
        sb.AppendLine("  QTYO, ")
        sb.AppendLine("  STOK, ")
        sb.AppendLine("  QTYR, ")
        sb.AppendLine("  FMSTS, ")
        sb.AppendLine("  ""time"", ")
        sb.AppendLine("  REQ_ID ")
        sb.AppendLine(") ")
        sb.AppendLine("SELECT ")
        sb.AppendLine("  fmndoc, ")
        sb.AppendLine("  fmnbtc, ")
        sb.AppendLine("  trnrak, ")
        sb.AppendLine("  fmntrn, ")
        sb.AppendLine("  prdcd, ")
        sb.AppendLine("  '' AS barc, ")
        sb.AppendLine("  '' AS bar2, ")
        sb.AppendLine("  bark, ")
        sb.AppendLine("  grak, ")
        sb.AppendLine("  nour, ")
        sb.AppendLine("  koderak, ")
        sb.AppendLine("  noid, ")
        sb.AppendLine("  satuan, ")
        sb.AppendLine("  fmksbu, ")
        sb.AppendLine("  fmkcab, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  desc2, ")
        sb.AppendLine("  qtyo, ")
        sb.AppendLine("  stok, ")
        sb.AppendLine("  qtyr, ")
        sb.AppendLine("  fmsts, ")
        sb.AppendLine("  ""time"", ")
        sb.AppendLine("  '" & IP & "' ")
        sb.AppendLine("FROM ( ")
        sb.AppendLine("  SELECT ")
        sb.AppendLine("    '" & PSP_NoPB & "' AS fmndoc, ")
        sb.AppendLine("    NULL AS fmnbtc, ")
        sb.AppendLine("    SUBSTR( ")
        sb.AppendLine("      RPAD('" & PSP_NoPB & "', 9, ' ') ")
        sb.AppendLine("      || SUBSTR(GRR_GROUPRAK, 1, 5) ")
        sb.AppendLine("      || LPAD(grr_nourut::text, 2, ' ') ")
        sb.AppendLine("      || RPAD(LKS_KodeRak, 7, ' ') ")
        sb.AppendLine("      || RPAD(LKS_KodeSubRak, 3, ' ') ")
        sb.AppendLine("      || RPAD(LKS_TipeRak, 3, ' ') ")
        sb.AppendLine("      || RPAD(LKS_ShelvingRak, 2, ' ') ")
        sb.AppendLine("      || obi_prdcd, ")
        sb.AppendLine("      1, 36 ")
        sb.AppendLine("    ) AS trnrak, ")
        sb.AppendLine("    NULL AS fmntrn, ")
        sb.AppendLine("    obi_prdcd AS prdcd, ")
        sb.AppendLine("    NULL AS bark, ")
        sb.AppendLine("    grr_grouprak AS grak, ")
        sb.AppendLine("    grr_nourut AS nour, ")
        sb.AppendLine("    SUBSTR( ")
        sb.AppendLine("      RPAD(lks_koderak, 7, ' ') ")
        sb.AppendLine("      || RPAD(lks_kodesubrak, 3, ' ') ")
        sb.AppendLine("      || RPAD(lks_tiperak, 3, ' ')  ")
        sb.AppendLine("      || RPAD(lks_shelvingrak, 2, ' ') ")
        sb.AppendLine("      || LPAD( ")
        sb.AppendLine("           CASE WHEN SUBSTR(lks_koderak, 1, 1) = 'D' ")
        sb.AppendLine("             THEN '0' ")
        sb.AppendLine("             ELSE lks_nourut::text ")
        sb.AppendLine("           END, 3, ' ' ")
        sb.AppendLine("         ),  ")
        sb.AppendLine("      1, 18 ")
        sb.AppendLine("    ) AS koderak, ")
        sb.AppendLine("    lks_noid AS noid, ")
        sb.AppendLine("    prd_unit||'/'||prd_frac AS satuan, ")
        sb.AppendLine("    '" & FMKSBU & "' AS fmksbu, ")
        sb.AppendLine("    '" & KodeToko & "' AS fmkcab, ")
        sb.AppendLine("    prd_deskripsipendek AS ""desc"", ")
        sb.AppendLine("    prd_deskripsipanjang AS desc2, ")
        sb.AppendLine("    d.obi_qtyorder / CASE WHEN prd_unit = 'KG' THEN prd_frac ELSE 1 END AS qtyo, ")
        sb.AppendLine("    st_saldoakhir AS stok, ")
        sb.AppendLine("    d.obi_qtyrealisasi / CASE WHEN prd_unit = 'KG' THEN prd_frac ELSE 1 END AS qtyr, ")
        sb.AppendLine("    NULL AS fmsts, ")
        sb.AppendLine("    NULL AS ""time"" ")
        sb.AppendLine("  FROM tbtr_obi_h h ")
        sb.AppendLine("  JOIN tbtr_obi_d d ")
        sb.AppendLine("       ON h.obi_tgltrans = d.obi_tgltrans ")
        sb.AppendLine("       AND h.obi_notrans = d.obi_notrans ")
        sb.AppendLine("  JOIN tbmaster_prodmast  ")
        sb.AppendLine("       ON prd_prdcd = d.obi_prdcd ")
        sb.AppendLine("  JOIN tbmaster_lokasi  ")
        sb.AppendLine("       ON lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("  JOIN tbmaster_grouprak ")
        sb.AppendLine("       ON grr_koderak = lks_koderak ")
        sb.AppendLine("       AND grr_subrak = lks_kodesubrak ")
        sb.AppendLine("  JOIN tbmaster_stock ")
        sb.AppendLine("       ON st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("  WHERE h.obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
        sb.AppendLine("  AND h.obi_notrans = '" & noTrans & "' ")
        sb.AppendLine("  AND h.obi_nopb = '" & noPB & "' ")
        sb.AppendLine("  AND d.obi_recid IS NULL ")
        sb.AppendLine("  AND st_lokasi = '01' ")
        sb.AppendLine("  AND (lks_noid like '%P' OR COALESCE(prd_frac,0) = 1) ")
        sb.AppendLine("  AND lks_tiperak NOT LIKE 'S%' ")
        sb.AppendLine("  AND COALESCE(grr_flagcetakan,'X') <> 'Y' ")
        sb.AppendLine("  AND grr_grouprak NOT LIKE 'H%' ")
        sb.AppendLine(") A  ")
        sb.AppendLine("WHERE NOT EXISTS ( ")
        sb.AppendLine("  SELECT brc_barcode ")
        sb.AppendLine("  FROM tbmaster_barcode ")
        sb.AppendLine("  WHERE brc_prdcd = prdcd ")
        sb.AppendLine(") ")
        sb.AppendLine("ORDER BY koderak, nour ")

        //! INSERT INTO TEMP_HHELD_IDM
        sb.AppendLine("INSERT INTO TEMP_HHELD_IDM ( ")
        sb.AppendLine("  FMNDOC, ")
        sb.AppendLine("  FMNBTC, ")
        sb.AppendLine("  TRNRAK, ")
        sb.AppendLine("  FMNTRN, ")
        sb.AppendLine("  PRDCD, ")
        sb.AppendLine("  BARC, ")
        sb.AppendLine("  BAR2, ")
        sb.AppendLine("  BARK, ")
        sb.AppendLine("  GRAK, ")
        sb.AppendLine("  NOUR, ")
        sb.AppendLine("  KODERAK, ")
        sb.AppendLine("  NOID, ")
        sb.AppendLine("  SATUAN, ")
        sb.AppendLine("  FMKSBU, ")
        sb.AppendLine("  FMKCAB, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  DESC2, ")
        sb.AppendLine("  QTYO, ")
        sb.AppendLine("  STOK, ")
        sb.AppendLine("  QTYR, ")
        sb.AppendLine("  REQ_ID ")
        sb.AppendLine(") ")
        sb.AppendLine("SELECT ")
        sb.AppendLine("  fmndoc, ")
        sb.AppendLine("  fmnbtc, ")
        sb.AppendLine("  trnrak, ")
        sb.AppendLine("  fmntrn, ")
        sb.AppendLine("  prdcd, ")
        sb.AppendLine("  MIN(brc_barcode) AS barc, ")
        sb.AppendLine("  CASE WHEN MIN(brc_barcode) = MAX(brc_barcode) ")
        sb.AppendLine("    THEN NULL ")
        sb.AppendLine("    ELSE MAX(brc_barcode) ")
        sb.AppendLine("  END AS bar2, ")
        sb.AppendLine("  bark, ")
        sb.AppendLine("  grak, ")
        sb.AppendLine("  nour, ")
        sb.AppendLine("  koderak, ")
        sb.AppendLine("  noid, ")
        sb.AppendLine("  satuan, ")
        sb.AppendLine("  fmksbu, ")
        sb.AppendLine("  fmkcab, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  desc2, ")
        sb.AppendLine("  qtyo, ")
        sb.AppendLine("  stok, ")
        sb.AppendLine("  qtyr, ")
        sb.AppendLine("  '" & IP & "' ")
        sb.AppendLine("FROM ( ")
        sb.AppendLine("  SELECT ")
        sb.AppendLine("    '" & PSP_NoPB & "' AS fmndoc, ")
        sb.AppendLine("    NULL AS fmnbtc, ")
        sb.AppendLine("    NULL AS trnrak, ")
        sb.AppendLine("    NULL AS fmntrn, ")
        sb.AppendLine("    obi_prdcd AS prdcd, ")
        sb.AppendLine("    NULL AS bark, ")
        sb.AppendLine("    grr_grouprak AS grak, ")
        sb.AppendLine("    grr_nourut AS nour, ")
        sb.AppendLine("    SUBSTR( ")
        sb.AppendLine("      RPAD(lks_koderak, 7, ' ') ")
        sb.AppendLine("      || RPAD(lks_kodesubrak, 3, ' ') ")
        sb.AppendLine("      || RPAD(lks_tiperak, 3, ' ')  ")
        sb.AppendLine("      || RPAD(lks_shelvingrak, 2, ' ') ")
        sb.AppendLine("      || LPAD( ")
        sb.AppendLine("           CASE WHEN SUBSTR(lks_koderak, 1, 1) = 'D' ")
        sb.AppendLine("             THEN '0' ")
        sb.AppendLine("             ELSE lks_nourut::text ")
        sb.AppendLine("           END, 3, ' ' ")
        sb.AppendLine("         ),  ")
        sb.AppendLine("      1, 18 ")
        sb.AppendLine("    ) AS koderak, ")
        sb.AppendLine("    lks_noid AS noid, ")
        sb.AppendLine("    prd_unit||'/'||prd_frac AS satuan, ")
        sb.AppendLine("    '" & FMKSBU & "' AS fmksbu, ")
        sb.AppendLine("    '" & KodeToko & "' AS fmkcab, ")
        sb.AppendLine("    prd_deskripsipendek AS ""desc"", ")
        sb.AppendLine("    prd_deskripsipanjang AS desc2, ")
        sb.AppendLine("    d.obi_qtyorder AS qtyo, ")
        sb.AppendLine("    st_saldoakhir AS stok, ")
        sb.AppendLine("    d.obi_qtyrealisasi AS qtyr ")
        sb.AppendLine("  FROM tbtr_obi_h h ")
        sb.AppendLine("  JOIN tbtr_obi_d d ")
        sb.AppendLine("       ON h.obi_tgltrans = d.obi_tgltrans ")
        sb.AppendLine("       AND h.obi_notrans = d.obi_notrans ")
        sb.AppendLine("  JOIN tbmaster_prodmast  ")
        sb.AppendLine("       ON prd_prdcd = d.obi_prdcd ")
        sb.AppendLine("  JOIN tbmaster_lokasi  ")
        sb.AppendLine("       ON lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("  JOIN tbmaster_grouprak ")
        sb.AppendLine("       ON grr_koderak = lks_koderak ")
        sb.AppendLine("       AND grr_subrak = lks_kodesubrak ")
        sb.AppendLine("  JOIN tbmaster_stock ")
        sb.AppendLine("       ON st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("  WHERE h.obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
        sb.AppendLine("  AND h.obi_notrans = '" & noTrans & "' ")
        sb.AppendLine("  AND h.obi_nopb = '" & noPB & "' ")
        sb.AppendLine("  AND d.obi_recid IS NULL ")
        sb.AppendLine("  AND st_lokasi = '01' ")
        sb.AppendLine("  AND lks_noid IS NULL ")
        sb.AppendLine("  AND lks_tiperak NOT LIKE 'S%' ")
        sb.AppendLine("  AND COALESCE(grr_flagcetakan,'X') <> 'Y' ")
        sb.AppendLine("  AND grr_grouprak LIKE 'H%' ")
        sb.AppendLine(") A  ")
        sb.AppendLine("LEFT JOIN tbmaster_barcode ")
        sb.AppendLine("ON brc_prdcd = prdcd ")
        sb.AppendLine("GROUP BY ")
        sb.AppendLine("  fmndoc, ")
        sb.AppendLine("  fmnbtc, ")
        sb.AppendLine("  trnrak, ")
        sb.AppendLine("  fmntrn, ")
        sb.AppendLine("  prdcd, ")
        sb.AppendLine("  bark, ")
        sb.AppendLine("  grak, ")
        sb.AppendLine("  nour, ")
        sb.AppendLine("  koderak, ")
        sb.AppendLine("  noid, ")
        sb.AppendLine("  satuan, ")
        sb.AppendLine("  fmksbu, ")
        sb.AppendLine("  fmkcab, ")
        sb.AppendLine("  ""desc"", ")
        sb.AppendLine("  desc2, ")
        sb.AppendLine("  qtyo, ")
        sb.AppendLine("  stok, ")
        sb.AppendLine("  qtyr ")
        sb.AppendLine("ORDER BY koderak, nour ")

        //! HHELD_IDM_ORA
        $cek = DB::table('hheld_idm_ora')
            ->where('req_id', $this->getIP())
            ->whereNull('fmrcid')
            ->whereDate('tglupd', Carbon::now())
            ->where([
                'fmkcab' => $KodeToko,
                'fmndoc' => $PSP_NoPB,
            ])->count();

        if($cek == 0){
            sb.AppendLine("INSERT INTO HHELD_IDM_ORA ( ")
            sb.AppendLine("  FMRCID, ")
            sb.AppendLine("  FMNDOC, ")
            sb.AppendLine("  FMNBTC, ")
            sb.AppendLine("  TRNRAK, ")
            sb.AppendLine("  FMNTRN, ")
            sb.AppendLine("  PRDCD, ")
            sb.AppendLine("  BARC, ")
            sb.AppendLine("  BAR2, ")
            sb.AppendLine("  BARK, ")
            sb.AppendLine("  GRAK, ")
            sb.AppendLine("  NOUR, ")
            sb.AppendLine("  KODERAK, ")
            sb.AppendLine("  NOID, ")
            sb.AppendLine("  SATUAN, ")
            sb.AppendLine("  FMKSBU, ")
            sb.AppendLine("  FMKCAB, ")
            sb.AppendLine("  ""desc"", ")
            sb.AppendLine("  DESC2, ")
            sb.AppendLine("  QTYO, ")
            sb.AppendLine("  STOK, ")
            sb.AppendLine("  QTYR, ")
            sb.AppendLine("  REQ_ID, ")
            sb.AppendLine("  TGLUPD, ")
            sb.AppendLine("  JAM_UPLOAD, ")
            sb.AppendLine("  JAM_PICKING, ")
            sb.AppendLine("  USERID  ")
            sb.AppendLine(") ")
            sb.AppendLine("SELECT  ")
            sb.AppendLine("  NULL, ")
            sb.AppendLine("  FMNDOC, ")
            sb.AppendLine("  FMNBTC, ")
            sb.AppendLine("  TRNRAK, ")
            sb.AppendLine("  FMNTRN, ")
            sb.AppendLine("  PRDCD, ")
            sb.AppendLine("  BARC, ")
            sb.AppendLine("  BAR2, ")
            sb.AppendLine("  BARK, ")
            sb.AppendLine("  GRAK, ")
            sb.AppendLine("  NOUR, ")
            sb.AppendLine("  KODERAK, ")
            sb.AppendLine("  NOID, ")
            sb.AppendLine("  SATUAN, ")
            sb.AppendLine("  FMKSBU, ")
            sb.AppendLine("  FMKCAB, ")
            sb.AppendLine("  ""desc"", ")
            sb.AppendLine("  DESC2, ")
            sb.AppendLine("  QTYO, ")
            sb.AppendLine("  STOK, ")
            sb.AppendLine("  QTYR, ")
            sb.AppendLine("  REQ_ID, ")
            sb.AppendLine("  TO_CHAR(CURRENT_DATE,'YYYYMMDD'), ")
            sb.AppendLine("  TO_CHAR(current_timestamp,'HH24:MI:SS'), ")
            sb.AppendLine("  NULL, ")
            sb.AppendLine("  NULL ")
            sb.AppendLine("FROM TEMP_HHELD_IDM ")
            sb.AppendLine("WHERE REQ_ID = '" & IP & "' ")
            sb.AppendLine("ORDER BY KODERAK ")
        }

        //! DPD_IDM_ORA
        $cek = DB::table('dpd_idm_ora')
            ->where('req_id', $this->getIP())
            ->whereNull('fmrcid')
            ->whereDate('tglupd', Carbon::now())
            ->where([
                'fmkcab' => $KodeToko,
                'fmndoc' => $PSP_NoPB,
            ])->count();

        if($cek > 0){
            sb.AppendLine("INSERT INTO DPD_IDM_ORA ( ")
            sb.AppendLine("  FMRCID, ")
            sb.AppendLine("  FMNDOC, ")
            sb.AppendLine("  TGLPB, ")
            sb.AppendLine("  FMNBTC, ")
            sb.AppendLine("  TRNRAK, ")
            sb.AppendLine("  FMNTRN, ")
            sb.AppendLine("  PRDCD, ")
            sb.AppendLine("  BARC, ")
            sb.AppendLine("  BAR2, ")
            sb.AppendLine("  BARK, ")
            sb.AppendLine("  GRAK, ")
            sb.AppendLine("  NOUR, ")
            sb.AppendLine("  KODERAK, ")
            sb.AppendLine("  NOID, ")
            sb.AppendLine("  SATUAN, ")
            sb.AppendLine("  FMKSBU, ")
            sb.AppendLine("  FMKCAB, ")
            sb.AppendLine("  ""desc"", ")
            sb.AppendLine("  DESC2, ")
            sb.AppendLine("  QTYO, ")
            sb.AppendLine("  STOK, ")
            sb.AppendLine("  QTYR, ")
            sb.AppendLine("  REQ_ID, ")
            sb.AppendLine("  TGLUPD, ")
            sb.AppendLine("  JAM_UPLOAD, ")
            sb.AppendLine("  JAM_PICKING, ")
            sb.AppendLine("  USERID, ")
            sb.AppendLine("  NOPICKING, ")
            sb.AppendLine("  NOSURATJALAN, ")
            sb.AppendLine("  PANJANG, ")
            sb.AppendLine("  LEBAR, ")
            sb.AppendLine("  TINGGI, ")
            sb.AppendLine("  KUBIKPICKING, ")
            sb.AppendLine("  KODEZONA ")
            sb.AppendLine(") ")
            sb.AppendLine("SELECT ")
            sb.AppendLine("  NULL, ")
            sb.AppendLine("  FMNDOC, ")
            sb.AppendLine("  TO_CHAR(TO_DATE('" & tglTrans & "','DD-MM-YYYY'), 'YYYYMMDD') TGLPB, ")
            sb.AppendLine("  FMNBTC, ")
            sb.AppendLine("  TRNRAK, ")
            sb.AppendLine("  FMNTRN, ")
            sb.AppendLine("  PRDCD, ")
            sb.AppendLine("  BARC, ")
            sb.AppendLine("  BAR2, ")
            sb.AppendLine("  BARK, ")
            sb.AppendLine("  GRAK, ")
            sb.AppendLine("  NOUR, ")
            sb.AppendLine("  KODERAK, ")
            sb.AppendLine("  NOID, ")
            sb.AppendLine("  SATUAN, ")
            sb.AppendLine("  FMKSBU, ")
            sb.AppendLine("  FMKCAB, ")
            sb.AppendLine("  ""desc"", ")
            sb.AppendLine("  DESC2, ")
            sb.AppendLine("  QTYO, ")
            sb.AppendLine("  STOK, ")
            sb.AppendLine("  QTYR, ")
            sb.AppendLine("  REQ_ID, ")
            sb.AppendLine("  TO_CHAR(CURRENT_DATE,'YYYYMMDD'), ")
            sb.AppendLine("  TO_CHAR(current_timestamp,'HH24:MI:SS'), ")
            sb.AppendLine("  NULL, ")
            sb.AppendLine("  NULL, ")
            sb.AppendLine("  '" & PSP_NoPick & "' NOPICKING, ")
            sb.AppendLine("  '" & PSP_NoSJ & "' NOSURATJALAN, ")
            sb.AppendLine("  COALESCE(OBI_PANJANG,PRD_DIMENSIPANJANG,1) PANJANG, ")
            sb.AppendLine("  COALESCE(OBI_LEBAR,PRD_DIMENSILEBAR,1) LEBAR, ")
            sb.AppendLine("  COALESCE(OBI_TINGGI,PRD_DIMENSITINGGI,1) TINGGI, ")
            sb.AppendLine("  NULL, ")
            sb.AppendLine("  ZON_KODE ")
            sb.AppendLine("FROM TEMP_DPD_IDM, TBMASTER_PRODMAST, ZONA_IDM, TBTR_OBI_D ")
            sb.AppendLine("WHERE REQ_ID = '" & IP & "'  ")
            sb.AppendLine("AND OBI_TGLTRANS = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
            sb.AppendLine("AND OBI_NOTRANS = '" & noTrans & "' ")
            sb.AppendLine("AND OBI_PRDCD = PRD_PRDCD ")
            sb.AppendLine("AND PRD_PRDCD = CASE WHEN SUBSTR(NOID,-1,1) = 'B' THEN SUBSTR(PRDCD,1,6)||'0' ELSE PRDCD END ")
            sb.AppendLine("AND ZON_RAK = SUBSTR(KODERAK,1,POSITION(' ' IN KODERAK)-1) ")
        }

        //! CETAKIN LABEL CONTAINER / BRONJONG UNTUK TOKO YANG PERTAMA HARI ITU
        $noContainer = 1;
        $noBronjong = 1;

        //! CEK PICKING_ANTRIAN
        sb.AppendLine("SELECT COALESCE(COUNT(PIA_KODETOKO),0)  ")
        sb.AppendLine("FROM PICKING_ANTRIAN ")
        sb.AppendLine("WHERE PIA_TGLPICK >= CURRENT_DATE - 7 ")
        sb.AppendLine("AND PIA_NOPICK = '" & PSP_NoPick & "' ")
        sb.AppendLine("AND PIA_NOSJ = '" & PSP_NoSJ & "' ")
        sb.AppendLine("AND PIA_KODETOKO = '" & KodeToko & "' ")

        $jum = 0;

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
            $query .= "  WHERE h.obi_nopb = '" & noPB & "' ";
            $query .= "  AND h.obi_notrans = '" & noTrans & "' ";
            $query .= "  AND h.obi_tgltrans = TO_DATE('" & tglTrans & "', 'DD-MM-YYYY') ";
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

            foreach($dt as $item){
                //! JIKA ADA CONTAINER
                if($item->JumlahContainer > 0){

                    // BarcodeContainer = "01" & Strings.Right("0000000" & PSP_NoPick, 7) & Strings.Right("000" & noContainer.ToString, 3)

                    //! INSERT INTO PICKING CONTAINER - Container
                    sb.AppendLine("INSERT INTO PICKING_CONTAINER ( ")
                    sb.AppendLine("  PICO_PrinterName, ")
                    sb.AppendLine("  PICO_NoPick, ")
                    sb.AppendLine("  PICO_TglPick, ")
                    sb.AppendLine("  PICO_ContainerZona, ")
                    sb.AppendLine("  PICO_Gate, ")
                    sb.AppendLine("  PICO_KodeToko, ")
                    sb.AppendLine("  PICO_NamaToko, ")
                    sb.AppendLine("  PICO_BarcodeKoli, ")
                    sb.AppendLine("  PICO_NoUrutToko, ")
                    sb.AppendLine("  PICO_JumlahToko, ")
                    sb.AppendLine("  PICO_NoSJ ")
                    sb.AppendLine(") ")
                    sb.AppendLine("VALUES ( ")
                    sb.AppendLine("  '" & row.Item(3) & "', ")
                    sb.AppendLine("  '" & PSP_NoPick & "', ")
                    sb.AppendLine("  '" & Strings.Format(Now, "dd-MM-yyyy") & "', ")
                    sb.AppendLine("  '" & Strings.Right("000" & noContainer.ToString, 3) & "-" & row.Item(0) & "', ")
                    sb.AppendLine("  '" & PSP_GATE & "', ")
                    sb.AppendLine("  '" & KodeToko & "', ")
                    sb.AppendLine("  '" & PSP_NamaToko & "', ")
                    sb.AppendLine("  '" & BarcodeContainer & "', ")
                    sb.AppendLine("  '" & (PSP_KodeToko.Length - i).ToString & "', ")
                    sb.AppendLine("  '" & PSP_KodeToko.Length.ToString & "', ")
                    sb.AppendLine("  '" & PSP_NoSJ & "' ")
                    sb.AppendLine(") ")

                    if($SudahAdaCetakBarcodeHariIni == false){
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

                        //! UPDATE PICKING CONTAINER RECID 1 MENANDAKAN SUDAH DI PRINT
                        sb.AppendLine("UPDATE Picking_Container ")
                        sb.AppendLine("SET Pico_RecordID = '1' ")
                        sb.AppendLine("WHERE PICO_NoPICK = '" & PSP_NoPick & "' ")
                        sb.AppendLine("AND TO_DATE(PICO_TglPick,'DD-MM-YYYY') >= CURRENT_DATE - 7 ")
                        sb.AppendLine("AND PICO_BarcodeKoli = '" & BarcodeContainer & "' ")
                    }

                    $noContainer += 1;
                }

                //! JIKA ADA BROJONG
                if($item->JumlahBronjong > 0){
                    // BarcodeContainer = "02" & Strings.Right("0000000" & PSP_NoPick, 7) & Strings.Right("000" & noBronjong.ToString, 3)

                    sb.AppendLine("INSERT INTO PICKING_CONTAINER ( ")
                    sb.AppendLine("  PICO_PrinterName, ")
                    sb.AppendLine("  PICO_NoPick, ")
                    sb.AppendLine("  PICO_TglPick, ")
                    sb.AppendLine("  PICO_ContainerZona, ")
                    sb.AppendLine("  PICO_Gate, ")
                    sb.AppendLine("  PICO_KodeToko, ")
                    sb.AppendLine("  PICO_NamaToko, ")
                    sb.AppendLine("  PICO_BarcodeKoli, ")
                    sb.AppendLine("  PICO_NoUrutToko, ")
                    sb.AppendLine("  PICO_JumlahToko, ")
                    sb.AppendLine("  PICO_NoSJ ")
                    sb.AppendLine(") ")
                    sb.AppendLine("VALUES ( ")
                    sb.AppendLine("  '" & row.Item(3) & "', ")
                    sb.AppendLine("  '" & PSP_NoPick & "', ")
                    sb.AppendLine("  '" & Strings.Format(Now, "dd-MM-yyyy") & "', ")
                    sb.AppendLine("  '" & Strings.Right("000" & noBronjong.ToString, 3) & "-" & row.Item(0) & "', ")
                    sb.AppendLine("  '" & PSP_GATE & "', ")
                    sb.AppendLine("  '" & PSP_KodeToko(i) & "', ")
                    sb.AppendLine("  '" & PSP_NamaToko & "', ")
                    sb.AppendLine("  '" & BarcodeContainer & "', ")
                    sb.AppendLine("  '" & (PSP_KodeToko.Length - i).ToString & "', ")
                    sb.AppendLine("  '" & PSP_KodeToko.Length.ToString & "', ")
                    sb.AppendLine("  '" & PSP_NoSJ & "' ")
                    sb.AppendLine(") ")

                    if($SudahAdaCetakBarcodeHariIni == false){
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

                        //! UPDATE PICKING CONTAINER RECID 1 MENANDAKAN SUDAH DI PRINT
                        sb.AppendLine("UPDATE Picking_Container ")
                        sb.AppendLine("SET Pico_RecordID = '1' ")
                        sb.AppendLine("WHERE PICO_NoPICK = '" & PSP_NoPick & "' ")
                        sb.AppendLine("AND TO_DATE(PICO_TglPick,'DD-MM-YYYY') >= CURRENT_DATE - 7  ")
                        sb.AppendLine("AND PICO_BarcodeKoli = '" & BarcodeContainer & "' ")
                    }

                    $noBronjong += 1;
                }
            }
        }

        //! ISI PICKING_ANTRIAN
        $NoUrutPaket = 1;
        $NoUrutTotal = 1;

        sb.AppendLine("INSERT INTO PICKING_ANTRIAN ( ")
        sb.AppendLine("  PIA_NoPick, ")
        sb.AppendLine("  PIA_TglPick, ")
        sb.AppendLine("  PIA_NoSJ, ")
        sb.AppendLine("  PIA_KodeToko, ")
        sb.AppendLine("  PIA_KodeZona, ")
        sb.AppendLine("  PIA_GroupRak, ")
        sb.AppendLine("  PIA_NoUrutPaket, ")
        sb.AppendLine("  PIA_NoUrutTotal ")
        sb.AppendLine(") ")
        sb.AppendLine("SELECT DISTINCT ")
        sb.AppendLine("  '" & PSP_NoPick & "' AS NoPick, ")
        sb.AppendLine("  TO_DATE('" & PSP_TglPick & "', 'YYYYMMDD;HH24:MI:SS;') AS TglPick, ")
        sb.AppendLine("  '" & PSP_NoSJ & "' AS NoSJ, ")
        sb.AppendLine("  '" & KodeToko & "' AS KodeToko, ")
        sb.AppendLine("  ZON_Kode AS KodeZona,  ")
        sb.AppendLine("  grr_GroupRak AS GroupRak, ")
        sb.AppendLine("  " & NoUrutPaket & " AS NoUrutPaket, ")
        sb.AppendLine("  " & NoUrutTotal & " AS NoUrutTotal   ")
        sb.AppendLine("FROM tbtr_obi_h h ")
        sb.AppendLine("JOIN tbtr_obi_d d ")
        sb.AppendLine("     ON h.obi_tgltrans = d.obi_tgltrans ")
        sb.AppendLine("     AND h.obi_notrans = d.obi_notrans ")
        sb.AppendLine("JOIN tbmaster_lokasi ")
        sb.AppendLine("     ON lks_prdcd = SUBSTR(d.obi_prdcd, 1, 6) || '0' ")
        sb.AppendLine("     AND lks_noid IS NOT NULL ")
        sb.AppendLine("JOIN tbmaster_grouprak ")
        sb.AppendLine("     ON grr_koderak = lks_koderak ")
        sb.AppendLine("     AND grr_subrak = lks_kodesubrak ")
        sb.AppendLine("JOIN zona_idm ")
        sb.AppendLine("     ON zon_rak = lks_koderak ")
        sb.AppendLine("     AND zon_rak = grr_koderak ")
        sb.AppendLine("WHERE h.obi_nopb = '" & noPB & "' ")
        sb.AppendLine("AND h.obi_notrans = '" & noTrans & "' ")
        sb.AppendLine("AND h.obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
        sb.AppendLine("AND d.obi_recid IS NULL ")
        sb.AppendLine("AND COALESCE(grr_flagcetakan,'?') <> 'Y' ")
    }

    private function konversi_SPI($noTrans, $tglTrans){

        try {

            //! DELETE TBTR_KONVERSI_SPI
            sb.AppendLine("DELETE FROM tbtr_konversi_spi ")
            sb.AppendLine("WHERE kvi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
            sb.AppendLine("AND kvi_notrans = '" & noTrans & "' ")

            //! INSERT INTO TBTR_KONVERSI_SPI
            sb.AppendLine("INSERT INTO tbtr_konversi_spi ( ")
            sb.AppendLine("  kvi_tgltrans, ")
            sb.AppendLine("  kvi_notrans, ")
            sb.AppendLine("  kvi_prdcd, ")
            sb.AppendLine("  kvi_hargasatuan, ")
            sb.AppendLine("  kvi_qtyorder, ")
            sb.AppendLine("  kvi_qtyrealisasi, ")
            sb.AppendLine("  kvi_ppn, ")
            sb.AppendLine("  kvi_diskon, ")
            sb.AppendLine("  kvi_hpp, ")
            sb.AppendLine("  kvi_kodealamat, ")
            sb.AppendLine("  kvi_hargaweb, ")
            sb.AppendLine("  kvi_create_by, ")
            sb.AppendLine("  kvi_create_dt ")
            sb.AppendLine(") ")
            sb.AppendLine(" ")
            sb.AppendLine("SELECT  ")
            sb.AppendLine("  obi_tgltrans,  ")
            sb.AppendLine("  obi_notrans,  ")
            sb.AppendLine("  obi_prdcd,  ")
            sb.AppendLine("  obi_hargasatuan,  ")
            sb.AppendLine("  obi_qtyorder,  ")
            sb.AppendLine("  0 obi_qtyrealisasi,  ")
            sb.AppendLine("  obi_ppn,  ")
            sb.AppendLine("  obi_diskon,  ")
            sb.AppendLine("  obi_hpp,  ")
            sb.AppendLine("  000 obi_kodealamat, ")
            sb.AppendLine("  obi_hargaweb, ")
            sb.AppendLine("  '" & UserMODUL & "' create_by, ")
            sb.AppendLine("  NOW() create_dt ")
            sb.AppendLine("FROM tbtr_obi_d ")
            sb.AppendLine("WHERE obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
            sb.AppendLine("AND obi_notrans = '" & noTrans & "' ")
            sb.AppendLine("AND obi_recid IS NULL ")

            //! DELETE TBTR_OBI_D
            sb.AppendLine("DELETE FROM tbtr_obi_d ")
            sb.AppendLine("WHERE obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
            sb.AppendLine("AND obi_notrans = '" & noTrans & "' ")
            sb.AppendLine("AND obi_recid IS NULL ")

            //! INSERT INTO TBTR_OBI_D - NEW KONVERSI
            sb.AppendLine("INSERT INTO tbtr_obi_d ( ")
            sb.AppendLine("  obi_tgltrans, ")
            sb.AppendLine("  obi_notrans, ")
            sb.AppendLine("  obi_prdcd, ")
            sb.AppendLine("  obi_hargasatuan, ")
            sb.AppendLine("  obi_qtyorder, ")
            sb.AppendLine("  obi_qtyrealisasi, ")
            sb.AppendLine("  obi_ppn, ")
            sb.AppendLine("  obi_diskon, ")
            sb.AppendLine("  obi_hpp, ")
            sb.AppendLine("  obi_kodealamat, ")
            sb.AppendLine("  obi_hargaweb ")
            sb.AppendLine(") ")
            sb.AppendLine("SELECT ")
            sb.AppendLine("    kvi_tgltrans, ")
            sb.AppendLine("    kvi_notrans, ")
            sb.AppendLine("	   CASE SUBSTR(kvi_prdcd, LENGTH(kvi_prdcd), 1) WHEN '0' THEN  ")
            sb.AppendLine("        CASE COALESCE(lks_noidctn, 'XXX') WHEN 'XXX' THEN plu ELSE kvi_prdcd END ")
            sb.AppendLine("    ELSE plu END kvi_prdcd, ")
            sb.AppendLine("    MIN(kvi_hargasatuan) kvi_hargasatuan, ")
            sb.AppendLine("    SUM(kvi_qtyorder) kvi_qtyorder, ")
            sb.AppendLine("    0 kvi_qtyrealisasi, ")
            sb.AppendLine("    MIN(kvi_ppn) obi_ppn, ")
            sb.AppendLine("    ROUND(SUM(ROUND(kvi_diskon * kvi_qtyorder)) / SUM(kvi_qtyorder),2) kvi_diskon, ")
            sb.AppendLine("    0 kvi_hpp, ")
            sb.AppendLine("    '000' kvi_kodealamat, ")
            sb.AppendLine("    MIN(kvi_hargasatuan + kvi_ppn) kvi_hargaweb ")
            sb.AppendLine("FROM tbtr_konversi_spi ")
            sb.AppendLine("JOIN tbmaster_prodmast ")
            sb.AppendLine("ON prd_prdcd = kvi_prdcd ")
            sb.AppendLine("JOIN ( ")
            sb.AppendLine("  SELECT plu, frac ")
            sb.AppendLine("  FROM ( ")
            sb.AppendLine("    SELECT prd_prdcd plu, prd_frac frac, ")
            sb.AppendLine("           substr(prd_prdcd,-1,1), ")
            sb.AppendLine("           ROW_NUMBER() OVER( ")
            sb.AppendLine("             PARTITION BY substr(prd_prdcd,1,6)  ")
            sb.AppendLine("             ORDER BY substr(prd_prdcd,-1,1) ASC ")
            sb.AppendLine("           ) AS rn ")
            sb.AppendLine("    FROM tbmaster_prodmast ")
            sb.AppendLine("  ) datas ")
            sb.AppendLine("  WHERE rn = 2 ")
            sb.AppendLine(") plu_kecil ")
            sb.AppendLine("ON SUBSTR(plu,1,6) = SUBSTR(kvi_prdcd,1,6) ")
            sb.AppendLine("WHERE kvi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY')  ")
            sb.AppendLine("AND kvi_notrans = '" & noTrans & "' ")
            sb.AppendLine("GROUP BY kvi_tgltrans, kvi_notrans, plu ")
            sb.AppendLine("ORDER BY plu ")

            //! UPDATE TBTR_OBI_H - OBI_ITEMORDER
            $query = '';
            $query .= "SELECT * FROM tbtr_obi_d ";
            $query .= "WHERE obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ";
            $query .= "AND obi_notrans = '" & noTrans & "' ";
            $query .= "AND obi_recid IS NULL ";
            $cek = DB::select($query);

            if(count($cek)){
                sb.AppendLine("UPDATE tbtr_obi_h ")
                sb.AppendLine("SET obi_itemorder = " & dt.Rows.Count & " ")
                sb.AppendLine("WHERE obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
                sb.AppendLine("AND obi_notrans = '" & noTrans & "' ")
            }

            sb.AppendLine(" UPDATE TBTR_OBI_D ")
            sb.AppendLine(" SET obi_hargaweb = (obi_hargasatuan+obi_ppn) * ( ")
            sb.AppendLine("                      SELECT COALESCE(prd_frac,1)  ")
            sb.AppendLine("                      FROM tbmaster_prodmast  ")
            sb.AppendLine("                      WHERE prd_prdcd = obi_prdcd ")
            sb.AppendLine("                      LIMIT 1) ")
            sb.AppendLine("WHERE obi_tgltrans = TO_DATE('" & tglTrans & "','DD-MM-YYYY') ")
            sb.AppendLine("AND obi_notrans = '" & noTrans & "' ")
            sb.AppendLine("AND obi_recid IS NULL ")

            return true;

        } catch(\Exception $e){

            return false;
        }
    }
}
