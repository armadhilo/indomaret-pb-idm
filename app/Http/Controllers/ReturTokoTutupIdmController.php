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

class HomeController extends Controller
{

    public function __construct(Request $request){
        DatabaseConnection::setConnection(session('KODECABANG'), "PRODUCTION");
    }

    public function index(){

        $this->addMasterRTT();

        return view('retur-toko-tutup-idm');
    }

    private function AddMasterRTT(){
        //! CEK TABLE TBMASTER_RETUR_IDM
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBMASTER_RETUR_IDM'")
            ->count();

        if($count == 0){
            $message = 'Tidak ada table TBMASTER_RETUR_IDM';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        //! RTT - RT
        $count = DB::table('tbmaster_retur_idm')
            ->where([
                'rdm_tipe' => '05010000',
                'rdm_statusbarang' => 'RT',
            ])
            ->count();

        if($count == 0){
            $query = '';
            $query .= "INSERT INTO tbmaster_retur_idm ( ";
            $query .= "  rdm_tipe, rdm_deskripsi, rdm_statusbarang, rdm_defaultlokasi, rdm_id ";
            $query .= ") ";
            $query .= "SELECT '05010000', 'RETUR TOKO TUTUP', 'RT', '01', max(rdm_id) + 1 FROM tbmaster_retur_idm ";
            DB::insert($query);
        }

        //! RTT - PT
        $count = DB::table('tbmaster_retur_idm')
            ->where([
                'rdm_tipe' => '05010000',
                'rdm_statusbarang' => 'PT',
            ])
            ->count();

        if($count == 0){
            $query = '';
            $query .= "INSERT INTO tbmaster_retur_idm ( ";
            $query .= "  rdm_tipe, rdm_deskripsi, rdm_statusbarang, rdm_defaultlokasi, rdm_id ";
            $query .= ") ";
            $query .= "SELECT '05010000', 'RETUR TOKO TUTUP', 'PT', '01', max(rdm_id) + 1 FROM tbmaster_retur_idm ";
            DB::insert($query);
        }

        //! RTT - TG
        $count = DB::table('tbmaster_retur_idm')
            ->where([
                'rdm_tipe' => '05010000',
                'rdm_statusbarang' => 'TG',
            ])
            ->count();

        if($count == 0){
            $query = '';
            $query .= "INSERT INTO tbmaster_retur_idm ( ";
            $query .= "  rdm_tipe, rdm_deskripsi, rdm_statusbarang, rdm_defaultlokasi, rdm_id ";
            $query .= ") ";
            $query .= "SELECT '05010000', 'RETUR TOKO TUTUP', 'TG', '01', max(rdm_id) + 1 FROM tbmaster_retur_idm ";
            DB::insert($query);
        }

        //! SEQUENCE RTT
        $count = DB::table('information_schema.sequences')
            ->whereRaw("upper(table_name) = 'SEQ_RTT_IDM'")
            ->count();

        if($count == 0){
            DB::insert("CREATE SEQUENCE SEQ_RTT_IDM START WITH 1 MINVALUE 1 MAXVALUE 9999 NOCACHE CYCLE");
        }

        //! BERSIH-BERSIH DATA
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'RTT_IDM_INTERFACE'")
            ->count();

        if($count > 0){
            DB::delete("DELETE FROM rtt_idm_interface WHERE DATE_TRUNC('DAY',rii_create_dt) < DATE_TRUNC('DAY',current_date) - interval '9 Months'");
        }
    }

    //* setiap buka halaman load function ini
    public function datatables(){
        $query = '';
        $query .= "SELECT DISTINCT docno NO_RTT, ";
        $query .= "       TO_CHAR(tanggal,'dd/MM/YYYY') TGL_RTT, ";
        $query .= "       shop TOKO_TUTUP, ";
        $query .= "       toko TOKO_TUJUAN ";
        $query .= "FROM rtt_idm_interface ";
        $query .= "WHERE recid IS NULL ";
        $query .= "ORDER BY 1 ASC ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function datatablesDetail($no_rtt, $toko_tutup, $toko_tujuan){
        $flagPLUIDM = false;
        $kodeDCIDM = '';

        //! CHECK AND GET KODEDC
        $dtCek = DB::table('master_supply_idm')
            ->select('msi_kodedc')
            ->where('msi_kodetoko', $toko_tutup)
            ->first();

        if(!empty($dtCek)){

            $kodeDCIDM = $dtCek->msi_kodedc;

            //! CHECK PLUIDM
            $dtCek = DB::table('tbmaster_pluidm')
                ->select('idm_pluidm')
                ->where('idm_kodeidm', $kodeDCIDM)
                ->count();

            if($dtCek > 0) $flagPLUIDM = true;
        }

        if($flagPLUIDM){
            $query = '';
            $query .= "SELECT row_number() over() NO, data_rtt.*  ";
            $query .= "FROM ( ";
            $query .= "  WITH data_retur AS (  ";
            $query .= "    SELECT *  ";
            $query .= "    FROM rtt_idm_interface ";
            $query .= "    WHERE docno = '" . $no_rtt . "' ";
            $query .= "    AND shop = '" . $toko_tutup . "' ";
            $query .= "    AND toko = '" . $toko_tujuan . "' ";
            $query .= "    AND recid IS NULL ";
            $query .= "  ) ";
            $query .= "  SELECT  ";
            $query .= "     pluigr PLU, ";
            $query .= "     istype || sctype KETERANGAN, ";
            $query .= "     COALESCE(qty, 0) RETUR, ";
            $query .= "     COALESCE(qty, 0) BAIK, ";
            $query .= "     0 BA, ";
            $query .= "     COALESCE(price, 0) PRICE, ";
            $query .= "     ROUND(COALESCE(ppn, 0) / COALESCE(qty,1)) PPN, ";
            $query .= "     UPPER(hgb_statusbarang) STATUS, ";
            $query .= "     IDM_KODETAG TAG_IDM, ";
            $query .= "     CASE  ";
            $query .= "        WHEN COALESCE(ST_AVGCOST,0) > 0  ";
            $query .= "        THEN 1  ";
            $query .= "        ELSE 0  ";
            $query .= "      END AVGCOST, ";
            $query .= "      '01' LOKASI ";
            $query .= "  FROM data_retur ";
            $query .= "  JOIN tbmaster_pluidm  ";
            $query .= "    ON prdcd = idm_pluidm AND idm_kodeidm = '" . $kodeDCIDM . "' ";
            $query .= "  LEFT JOIN tbmaster_hargabeli  ";
            $query .= "    ON pluigr = hgb_prdcd AND hgb_tipe = '2'  ";
            $query .= "  LEFT JOIN tbmaster_stock  ";
            $query .= "    ON pluigr = st_prdcd AND st_lokasi = '01' ";
            $query .= "  ORDER BY PLU ";
            $query .= ") data_rtt ";
            $data = DB::select($query);
        }else{
            //! BACA DARI TBMASTER_PRODCRM
            $query = '';
            $query .= "SELECT row_number() over() NO, data_rtt.*  ";
            $query .= "FROM ( ";
            $query .= "  WITH data_retur AS (  ";
            $query .= "    SELECT *  ";
            $query .= "    FROM rtt_idm_interface ";
            $query .= "    WHERE docno = '" . $no_rtt . "' ";
            $query .= "    AND shop = '" . $toko_tutup . "' ";
            $query .= "    AND toko = '" . $toko_tujuan . "' ";
            $query .= "    AND recid IS NULL ";
            $query .= "  ) ";
            $query .= "  SELECT  ";
            $query .= "     pluigr PLU, ";
            $query .= "     istype || sctype KETERANGAN, ";
            $query .= "     COALESCE(qty, 0) RETUR, ";
            $query .= "     COALESCE(qty, 0) BAIK, ";
            $query .= "     0 BA, ";
            $query .= "     COALESCE(price, 0) PRICE, ";
            $query .= "     ROUND(COALESCE(ppn, 0) / COALESCE(qty,1)) PPN, ";
            $query .= "     UPPER(hgb_statusbarang) STATUS, ";
            $query .= "     PRC_KODETAG TAG_IDM, ";
            $query .= "     CASE  ";
            $query .= "        WHEN COALESCE(ST_AVGCOST,0) > 0  ";
            $query .= "        THEN 1  ";
            $query .= "        ELSE 0  ";
            $query .= "      END AVGCOST, ";
            $query .= "      '01' LOKASI ";
            $query .= "  FROM data_retur ";
            $query .= "  JOIN tbmaster_prodcrm  ";
            $query .= "    ON prdcd = prc_pluidm AND prc_group = 'I'  ";
            $query .= "  LEFT JOIN tbmaster_hargabeli  ";
            $query .= "    ON pluigr = hgb_prdcd AND hgb_tipe = '2'  ";
            $query .= "  LEFT JOIN tbmaster_stock  ";
            $query .= "    ON pluigr = st_prdcd AND st_lokasi = '01' ";
            $query .= "  ORDER BY PLU ";
            $query .= ") data_rtt ";
            $data = DB::select($query);
        }

        //* total retur += ((listRTT.Rows(i)("PRICE") * listRTT.Rows(i)("RETUR")) + listRTT.Rows(i)("PPN"))

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function actionUpload(){

        //* upload bisa multiple file
        //! LOOP
            $query = '';
            $query .= "SELECT count(1) found ";
            $query .= "FROM rtt_idm_interface ";
            $query .= "WHERE upper(rii_filename) = upper('" & fileRTT & "') ";
            $dtCek = DB::select($query);

            if(count($dtCek) > 0){
                return ApiFormatter::error("File " & fileRTT & " Sudah Pernah Diupload");
            }

            //? check kolom kosong -> TOKO, GUDANG, SHOP -- File " & fileRTT & vbNewLine & "Ada Kolom " & col & " Yang Kosong.
            //? check -> QTY, PRICE = 0 -- "File " & fileRTT & vbNewLine & "Ada Kolom " & col & " Yang Bernilai 0."

            //* jika data csv tidak kosong

            DB::table('temp_rtt_idm')->truncate();

            DB::table('temp_rtt_idm')
                ->insert([
                    'docno' => $item['DOCNO'],
                    'docno2' => $item['DOCNO2'],
                    'div' => $item['DIV'],
                    'toko' => $item['TOKO'],
                    'toko_1' => $item['TOKO_1'],
                    'gudang' => $item['GUDANG'],
                    'prdcd' => $item['PRDCD'],
                    'qty' => $item['QTY'],
                    'price' => $item['PRICE'],
                    'gross' => $item['GROSS'],
                    'ppn' => $item['PPN'],
                    'tanggal' => $item['TANGGAL'],
                    'tanggal2' => $item['TANGGAL2'],
                    'shop' => $item['SHOP'],
                    'istype' => $item['ISTYPE'],
                    'price_idm' => $item['PRICE_IDM'],
                    'ppnbm_idm' => $item['PPNBM_IDM'],
                    'ppnrp_idm' => $item['PPNRP_IDM'],
                    'sctype' => $item['SCTYPE'],
                    'bkp' => $item['BKP'],
                    'sub_bkp' => $item['SUB_BKP'],
                    'cabang' => $item['CABANG'],
                    'tipe_gdg' => $item['TIPE_GDG'],
                    'ppn_rate' => $item['PPN_RATE'],
                ]);

            //! CEK PRODMAST DAN PRODCRM
            $query = '';
            $query .= "SELECT DISTINCT prdcd ";
            $query .= "FROM temp_rtt_idm ";
            $query .= "LEFT JOIN tbmaster_prodcrm  ON prc_pluidm = prdcd AND prc_group = 'I' ";
            $query .= "LEFT JOIN tbmaster_prodmast ON prd_prdcd = prc_pluigr ";
            $query .= "WHERE (prc_pluigr IS NULL OR prd_prdcd IS NULL) ";
            $query .= "ORDER BY 1 ASC";
            $dtPlu = DB::select($query);

            if(count($dtPlu) > 0){
                $string = '';
                foreach($dtPlu as $item){
                    $string .= $item['prdcd'];
                }

                //* File " & fileRTT & vbNewLine & dtPLU.Rows.Count & " PLU Tidak Ada Di Master IGR." & vbNewLine & strPLU
                $message = "";
                return ApiFormatter::error(400, $message);
            }

            $query = '';
            $query .= "INSERT INTO rtt_idm_interface ( ";
            $query .= "  DOCNO, ";
            $query .= "  DOCNO2, ";
            $query .= "  DIV, ";
            $query .= "  TOKO, ";
            $query .= "  TOKO_1, ";
            $query .= "  GUDANG, ";
            $query .= "  PRDCD, ";
            $query .= "  PLUIGR, ";
            $query .= "  QTY, ";
            $query .= "  PRICE, ";
            $query .= "  GROSS, ";
            $query .= "  PPN, ";
            $query .= "  TANGGAL, ";
            $query .= "  TANGGAL2, ";
            $query .= "  SHOP, ";
            $query .= "  ISTYPE, ";
            $query .= "  PRICE_IDM, ";
            $query .= "  PPNBM_IDM, ";
            $query .= "  PPNRP_IDM, ";
            $query .= "  SCTYPE, ";
            $query .= "  BKP, ";
            $query .= "  SUB_BKP, ";
            $query .= "  CABANG, ";
            $query .= "  TIPE_GDG, ";
            $query .= "  RII_CREATE_BY, ";
            $query .= "  RII_CREATE_DT, ";
            $query .= "  RII_FILENAME, ";
            $query .= "  PPN_RATE ";
            $query .= ") ";
            $query .= "SELECT  ";
            $query .= "  DOCNO, ";
            $query .= "  DOCNO2, ";
            $query .= "  DIV, ";
            $query .= "  TOKO, ";
            $query .= "  TOKO_1, ";
            $query .= "  GUDANG, ";
            $query .= "  PRDCD, ";
            $query .= "  prc_pluigr PLUIGR, ";
            $query .= "  QTY, ";
            $query .= "  PRICE, ";
            $query .= "  GROSS, ";
            $query .= "  PPN, ";
            $query .= "  COALESCE(TO_DATE(TANGGAL,'DD-MM-YYYY'),NULL) TANGGAL, ";
            $query .= "  COALESCE(TO_DATE(TANGGAL2,'DD-MM-YYYY'),NULL) TANGGAL2, ";
            $query .= "  SHOP, ";
            $query .= "  ISTYPE, ";
            $query .= "  PRICE_IDM, ";
            $query .= "  PPNBM_IDM, ";
            $query .= "  PPNRP_IDM, ";
            $query .= "  SCTYPE, ";
            $query .= "  BKP, ";
            $query .= "  SUB_BKP, ";
            $query .= "  CABANG, ";
            $query .= "  TIPE_GDG, ";
            $query .= "  '" . session('userid') . "', ";
            $query .= "  NOW(), ";
            $query .= "  '" & fileRTT & "', ";
            $query .= "  PPN_RATE ";
            $query .= "FROM temp_rtt_idm ";
            $query .= "JOIN tbmaster_prodcrm ";
            $query .= "ON prc_pluidm = prdcd ";
            $query .= "AND prc_group = 'I' ";
            $query .= "JOIN tbmaster_prodmast ";
            $query .= "ON prd_prdcd = prc_pluigr ";
            $query .= "WHERE ISTYPE || SCTYPE = '05010000' ";
            $query .= "AND EXISTS ( ";
            $query .= "  SELECT 1 ";
            $query .= "  FROM tbmaster_perusahaan ";
            $query .= "  WHERE 'GI' || prs_kodeigr = gudang ";
            $query .= ")  ";
            $query .= "AND EXISTS ( ";
            $query .= "  SELECT 1 ";
            $query .= "  FROM tbmaster_tokoigr ";
            $query .= "  WHERE tko_kodeomi = toko ";
            $query .= ") ";
            $query .= "AND EXISTS ( ";
            $query .= "  SELECT 1 ";
            $query .= "  FROM tbmaster_tokoigr ";
            $query .= "  WHERE tko_kodeomi = shop ";
            $query .= ") ";
            $query .= "AND NOT EXISTS ( ";
            $query .= "  SELECT 1 ";
            $query .= "  FROM rtt_idm_interface ";
            $query .= "  WHERE rtt_idm_interface.docno = temp_rtt_idm.docno ";
            $query .= "  AND rtt_idm_interface.toko = temp_rtt_idm.toko ";
            $query .= ") ";
            DB::insert($query);
        //! ENDLOOP
    }

    public function actionCetak($noRTT,$tglRTT,$shop){
        $noNrb = DB::select("SELECT DISTINCT rom_nodokumen FROM TBTR_RETUROMI WHERE rom_noreferensi = '" & noRTT & "' AND DATE_TRUNC('DAY',rom_tglreferensi) = TO_DATE('" & tglRTT & "','DD/MM/YYYY') AND rom_kodetoko = '" & shop & "' LIMIT 1");

        $query = '';
        $query .= "SELECT prd_kodeigr kode_igr,ROM_NODOKUMEN, ROM_TGLDOKUMEN, ROM_PRDCD,PRD_UNIT,ROM_NOREFERENSI, ";
        $query .= "prd_frac, prd_deskripsipendek, (ROM_QTY+ROM_QTYTLR) qty,";
        $query .= "ROM_QTY qtyf, ((ROM_QTY+ROM_QTYTLR) - ROM_QTYREALISASI) fisikkrg , ";
        $query .= "(ROM_QTYREALISASI - (ROM_QTYMLJ+ROM_QTYTLJ)) fisiktolak ,ROM_QTYTLR ba, ";
        $query .= "(ROM_QTY * ROM_AVGCOST) ttl_Avg,(ROM_QTYTLR * ROM_HRGSATUAN) ttl, ROM_HRGSATUAN,ROM_AVGCOST, ";
        $query .= "rom_tglreferensi,ROM_TGLREFERENSI, ";
        $query .= "(SELECT BTH_NODOC FROM TBTR_BATOKO_H WHERE BTH_PBR = ROM_NODOKUMEN AND BTH_TGPBR = rom_tgldokumen LIMIT 1 ) noba   ";
        $query .= "FROM  tbtr_returomi, tbmaster_prodmast   ";
        $query .= "WHERE ROM_PRDCD = prd_prdcd AND ROM_KODEIGR = prd_kodeigr  ";
        $query .= "AND ROM_NODOKUMEN =  '" . $noNrb . "' ";
        $query .= "AND DATE_TRUNC('DAY',rom_tgldokumen) = DATE_TRUNC('DAY',CURRENT_DATE) ";
        $data['data'] = DB::select($query);

        if(count($data['data']) == 0){
            return ApiFormatter::error(400, 'table tbtr_returomi kosong report gagal ditampilkan');
        }

        $data['namaCabang'] = DB::table('tbmaster_perusahaan')
            ->select('prs_namacabang')
            ->first()->prs_namacabang;

        $query = '';
        $query .= "select tko_kodeigr kode_igr, TKO_NAMAOMI , TKO_KODEOMI, ";
        $query .= "'" . $noRTT . "' RETURID, '" . $tglRTT . "' TGLNRB ";
        $query .= "from tbmaster_tokoigr ";
        $query .= "where TKO_KODEOMI = '" . $shop . "'  ";
        $query .= "and TKO_NAMASBU = 'INDOMARET'";
        $data['toko'] = DB::select($query);

        return view('report', $data);
    }
}
