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

    //! SPI - DIPAKE DI KLIK IGR
    private function createTablePSP_SPI(){

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
            $query .= "  ""desc""  Varchar(20), ";
            $query .= "  DESC2     Varchar(75), ";
            $query .= "  QTYO      NUMERIC(7), ";
            $query .= "  STOK      NUMERIC(12), ";
            $query .= "  QTYR      NUMERIC(7), ";
            $query .= "  FMSTS     Varchar(1), ";
            $query .= "  ""time""  Varchar(8), ";
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
            $query .= "  ""DESC""  VARCHAR(20), ";
            $query .= "  DESC2     VARCHAR(75), ";
            $query .= "  QTYO      NUMERIC(7), ";
            $query .= "  STOK      NUMERIC(12), ";
            $query .= "  QTYR      NUMERIC(7), ";
            $query .= "  FMSTS     VARCHAR(1), ";
            $query .= "  ""TIME""  VARCHAR(8), ";
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
            $query .= "  ""DESC""  VARCHAR(20), ";
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
            $query .= "  ""DESC""     VARCHAR(20), ";
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
            $query .= "  ""DESC""     VARCHAR(20), ";
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
            $query .= "  ""DESC""     VARCHAR(20), ";
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
            $query .= "  ""DESC""     VARCHAR(20), ";
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

    private function addColHitungUlang_SPI(){

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
}
