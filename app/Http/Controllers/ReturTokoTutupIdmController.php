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

        $this->addMasterRTT();
        $this->loadHeaderRTT();

        return view('retur-toko-tutup-idm');
    }

    private function AddMasterRTT(){
        // 'CEK TABLE TBMASTER_RETUR_IDM
        // temp = QueryOra(" SELECT COUNT(1) FROM information_schema.tables WHERE upper(table_name) = 'TBMASTER_RETUR_IDM' ")
        // found = temp.Rows(0).Item(0)

        // If found = 0 Then
        //     Exit Sub
        // End If

        // 'RTT - RT
        // temp = QueryOra("SELECT count(1) FROM tbmaster_retur_idm WHERE rdm_tipe = '05010000' AND rdm_statusbarang = 'RT' ")
        // found = temp.Rows(0).Item(0)

        // If found = 0 Then
        //     sb = New StringBuilder
        //     sb.AppendLine("INSERT INTO tbmaster_retur_idm ( ")
        //     sb.AppendLine("  rdm_tipe, rdm_deskripsi, rdm_statusbarang, rdm_defaultlokasi, rdm_id ")
        //     sb.AppendLine(") ")
        //     sb.AppendLine("SELECT '05010000', 'RETUR TOKO TUTUP', 'RT', '01', max(rdm_id) + 1 FROM tbmaster_retur_idm ")
        //     NonQueryOra(sb.ToString)
        // End If


        // 'RTT - PT
        // temp = QueryOra("SELECT count(1) FROM tbmaster_retur_idm WHERE rdm_tipe = '05010000' AND rdm_statusbarang = 'PT' ")
        // found = temp.Rows(0).Item(0)

        // If found = 0 Then
        //     sb = New StringBuilder
        //     sb.AppendLine("INSERT INTO tbmaster_retur_idm ( ")
        //     sb.AppendLine("  rdm_tipe, rdm_deskripsi, rdm_statusbarang, rdm_defaultlokasi, rdm_id ")
        //     sb.AppendLine(") ")
        //     sb.AppendLine("SELECT '05010000', 'RETUR TOKO TUTUP', 'PT', '01', max(rdm_id) + 1 FROM tbmaster_retur_idm ")
        //     NonQueryOra(sb.ToString)
        // End If


        // 'RTT - TG
        // temp = QueryOra("SELECT count(1) FROM tbmaster_retur_idm WHERE rdm_tipe = '05010000' AND rdm_statusbarang = 'TG' ")
        // found = temp.Rows(0).Item(0)

        // If found = 0 Then
        //     sb = New StringBuilder
        //     sb.AppendLine("INSERT INTO tbmaster_retur_idm ( ")
        //     sb.AppendLine("  rdm_tipe, rdm_deskripsi, rdm_statusbarang, rdm_defaultlokasi, rdm_id ")
        //     sb.AppendLine(") ")
        //     sb.AppendLine("SELECT '05010000', 'RETUR TOKO TUTUP', 'TG', '01', max(rdm_id) + 1 FROM tbmaster_retur_idm ")
        //     NonQueryOra(sb.ToString)
        // End If

        // 'SEQUENCE RTT
        // temp = QueryOra("SELECT count(1) FROM information_schema.sequences WHERE UPPER(sequence_name) = UPPER('SEQ_RTT_IDM') ")
        // found = temp.Rows(0).Item(0)

        // If found = 0 Then
        //     sb = New StringBuilder
        //     sb.AppendLine("CREATE SEQUENCE SEQ_RTT_IDM START WITH 1 MINVALUE 1 MAXVALUE 9999 NOCACHE CYCLE ")
        //     NonQueryOra(sb.ToString)
        // End If

        // 'BERSIH-BERSIH DATA
        // temp = QueryOra(" SELECT COUNT(1) FROM information_schema.tables WHERE upper(table_name) = 'RTT_IDM_INTERFACE' ")
        // found = temp.Rows(0).Item(0)

        // If found > 0 Then
        //     NonQueryOra("DELETE FROM rtt_idm_interface WHERE DATE_TRUNC('DAY',rii_create_dt) < DATE_TRUNC('DAY',current_date) - interval '9 Months' ")
        // End If
    }

    private function loadHeaderRTT(){
        // FlagProcess = True

        // sb = New StringBuilder
        // sb.AppendLine("SELECT DISTINCT docno NO_RTT, ")
        // sb.AppendLine("       TO_CHAR(tanggal,'dd/MM/YYYY') TGL_RTT, ")
        // sb.AppendLine("       shop TOKO_TUTUP, ")
        // sb.AppendLine("       toko TOKO_TUJUAN ")
        // sb.AppendLine("FROM rtt_idm_interface ")
        // sb.AppendLine("WHERE recid IS NULL ")
        // sb.AppendLine("ORDER BY 1 ASC ")
        // list = QueryOra(sb.ToString)

        // If list.Rows.Count > 0 Then
        //     For i = 0 To list.Rows.Count - 1
        //         Lvitem = lvRTT.Items.Add(list.Rows(i)("NO_RTT"))

        //         If i Mod 2 = 0 Then
        //             lvRTT.Items(i).BackColor = Color.FromArgb(0, 192, 192)
        //         Else
        //             lvRTT.Items(i).BackColor = Color.FromArgb(192, 192, 255)
        //         End If

        //         If IsDBNull(list.Rows(i)("TGL_RTT")) = False Then
        //             Lvitem.SubItems.Add(list.Rows(i)("TGL_RTT").ToString)
        //         Else
        //             Lvitem.SubItems.Add("")
        //         End If

        //         If IsDBNull(list.Rows(i)("TOKO_TUTUP")) = False Then
        //             Lvitem.SubItems.Add(list.Rows(i)("TOKO_TUTUP").ToString)
        //         Else
        //             Lvitem.SubItems.Add("")
        //         End If

        //         If IsDBNull(list.Rows(i)("TOKO_TUJUAN")) = False Then
        //             Lvitem.SubItems.Add(list.Rows(i)("TOKO_TUJUAN").ToString)
        //         Else
        //             Lvitem.SubItems.Add("")
        //         End If
        //     Next

        // End If
        // FlagProcess = False
    }

    public function actionUpload(){
        // sb.AppendLine("SELECT count(1) found ")
        // sb.AppendLine("FROM rtt_idm_interface ")
        // sb.AppendLine("WHERE upper(rii_filename) = upper('" & fileRTT & "') ")
        // ExecScalar(sb.ToString, "CEK FILENAME", count)

        // If ColumnNames.Length <> 24 Then
        //     message = "File " & fileRTT & vbNewLine & "Jumlah Kolom Tidak Standard (24)"
        //     Return False
        // End If

        //? check kolom kosong -> TOKO, GUDANG, SHOP -- File " & fileRTT & vbNewLine & "Ada Kolom " & col & " Yang Kosong.
        //? check -> QTY, PRICE = 0 -- "File " & fileRTT & vbNewLine & "Ada Kolom " & col & " Yang Bernilai 0."

        // NonQueryOra("DELETE FROM TEMP_RTT_IDM")

        // sb.AppendLine("INSERT INTO TEMP_RTT_IDM ( ")
        //     sb.AppendLine("docno, ")
        //     sb.AppendLine("docno2 , ")
        //     sb.AppendLine("div, ")
        //     sb.AppendLine("toko,")
        //     sb.AppendLine("toko_1,")
        //     sb.AppendLine("gudang,")
        //     sb.AppendLine("prdcd ,")
        //     sb.AppendLine("qty, ")
        //     sb.AppendLine("price, ")
        //     sb.AppendLine("gross, ")
        //     sb.AppendLine("ppn,")
        //     sb.AppendLine("tanggal ,")
        //     sb.AppendLine("tanggal2 ,")
        //     sb.AppendLine("shop,")
        //     sb.AppendLine("istype, ")
        //     sb.AppendLine("price_idm, ")
        //     sb.AppendLine("ppnbm_idm, ")
        //     sb.AppendLine("ppnrp_idm,")
        //     sb.AppendLine("sctype,")
        //     sb.AppendLine("bkp,")
        //     sb.AppendLine("sub_bkp,")
        //     sb.AppendLine("cabang, ")
        //     sb.AppendLine("tipe_gdg, ")
        //     sb.AppendLine("ppn_rate ")
        //     sb.AppendLine(" ) ")
        //     sb.AppendLine(" VALUES ")
        //     For k As Integer = 0 To dtRTT.Rows.Count - 1
        //         sb.AppendLine("( ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("DOCNO") & "', ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("DOCNO2") & "', ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("DIV") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("TOKO") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("TOKO_1") & "', ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("GUDANG") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("PRDCD") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("QTY") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("PRICE") & "', ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("GROSS") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("PPN") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("TANGGAL") & "', ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("TANGGAL2") & "', ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("SHOP") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("ISTYPE") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("PRICE_IDM") & "', ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("PPNBM_IDM") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("PPNRP_IDM") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("SCTYPE") & "', ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("BKP") & "', ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("SUB_BKP") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("CABANG") & "',")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("TIPE_GDG") & "', ")
        //         sb.AppendLine("'" & dtRTT.Rows(k)("PPN_RATE") & "')")
        //         If counter = 200 Then
        //             ExecQRY(sb.ToString, "INSERT TEMP_RTT_IDM - PENGGANTI BULKCOPY")

        //             counter = 0
        //             sb = New StringBuilder
        //             sb.AppendLine("INSERT INTO TEMP_RTT_IDM ( ")
        //             sb.AppendLine("docno, ")
        //             sb.AppendLine("docno2 , ")
        //             sb.AppendLine("div, ")
        //             sb.AppendLine("toko,")
        //             sb.AppendLine("toko_1,")
        //             sb.AppendLine("gudang,")
        //             sb.AppendLine("prdcd ,")
        //             sb.AppendLine("qty, ")
        //             sb.AppendLine("price, ")
        //             sb.AppendLine("gross, ")
        //             sb.AppendLine("ppn,")
        //             sb.AppendLine("tanggal ,")
        //             sb.AppendLine("tanggal2 ,")
        //             sb.AppendLine("shop,")
        //             sb.AppendLine("istype, ")
        //             sb.AppendLine("price_idm, ")
        //             sb.AppendLine("ppnbm_idm, ")
        //             sb.AppendLine("ppnrp_idm,")
        //             sb.AppendLine("sctype,")
        //             sb.AppendLine("bkp,")
        //             sb.AppendLine("sub_bkp,")
        //             sb.AppendLine("cabang, ")
        //             sb.AppendLine("tipe_gdg, ")
        //             sb.AppendLine("ppn_rate ")
        //             sb.AppendLine(" ) ")
        //             sb.AppendLine(" VALUES ")
        //         Else
        //             sb.Append(", ")
        //             counter += 1
        //         End If
        //     Next
        //     If counter > 0 Then
        //         ExecQRY(Strings.Left(sb.ToString, sb.ToString.Length - 3), "INSERT TBTEMP_MINORTK - PENGGANTI BULKCOPY")
        //     End If

        //     'CEK PRODMAST DAN PRODCRM
        //     sb = New StringBuilder
        //     sb.AppendLine("SELECT DISTINCT prdcd ")
        //     sb.AppendLine("FROM temp_rtt_idm ")
        //     sb.AppendLine("LEFT JOIN tbmaster_prodcrm  ON prc_pluidm = prdcd AND prc_group = 'I' ")
        //     sb.AppendLine("LEFT JOIN tbmaster_prodmast ON prd_prdcd = prc_pluigr ")
        //     sb.AppendLine("WHERE (prc_pluigr IS NULL OR prd_prdcd IS NULL) ")
        //     sb.AppendLine("ORDER BY 1 ASC")
        //     dtPLU = QueryOra(sb.ToString)
        //     'dtPLU = New DataTable 'SIM

        //     If dtPLU.Rows.Count = 0 Then
        //         'INSERT KE RTT_IDM_INTERFACE
        //         sb = New StringBuilder
        //         sb.AppendLine("INSERT INTO rtt_idm_interface ( ")
        //         sb.AppendLine("  DOCNO, ")
        //         sb.AppendLine("  DOCNO2, ")
        //         sb.AppendLine("  DIV, ")
        //         sb.AppendLine("  TOKO, ")
        //         sb.AppendLine("  TOKO_1, ")
        //         sb.AppendLine("  GUDANG, ")
        //         sb.AppendLine("  PRDCD, ")
        //         sb.AppendLine("  PLUIGR, ")
        //         sb.AppendLine("  QTY, ")
        //         sb.AppendLine("  PRICE, ")
        //         sb.AppendLine("  GROSS, ")
        //         sb.AppendLine("  PPN, ")
        //         sb.AppendLine("  TANGGAL, ")
        //         sb.AppendLine("  TANGGAL2, ")
        //         sb.AppendLine("  SHOP, ")
        //         sb.AppendLine("  ISTYPE, ")
        //         sb.AppendLine("  PRICE_IDM, ")
        //         sb.AppendLine("  PPNBM_IDM, ")
        //         sb.AppendLine("  PPNRP_IDM, ")
        //         sb.AppendLine("  SCTYPE, ")
        //         sb.AppendLine("  BKP, ")
        //         sb.AppendLine("  SUB_BKP, ")
        //         sb.AppendLine("  CABANG, ")
        //         sb.AppendLine("  TIPE_GDG, ")
        //         sb.AppendLine("  RII_CREATE_BY, ")
        //         sb.AppendLine("  RII_CREATE_DT, ")
        //         sb.AppendLine("  RII_FILENAME, ")
        //         sb.AppendLine("  PPN_RATE ")
        //         sb.AppendLine(") ")
        //         sb.AppendLine("SELECT  ")
        //         sb.AppendLine("  DOCNO, ")
        //         sb.AppendLine("  DOCNO2, ")
        //         sb.AppendLine("  DIV, ")
        //         sb.AppendLine("  TOKO, ")
        //         sb.AppendLine("  TOKO_1, ")
        //         sb.AppendLine("  GUDANG, ")
        //         sb.AppendLine("  PRDCD, ")
        //         sb.AppendLine("  prc_pluigr PLUIGR, ")
        //         sb.AppendLine("  QTY, ")
        //         sb.AppendLine("  PRICE, ")
        //         sb.AppendLine("  GROSS, ")
        //         sb.AppendLine("  PPN, ")
        //         sb.AppendLine("  COALESCE(TO_DATE(TANGGAL,'DD-MM-YYYY'),NULL) TANGGAL, ")
        //         sb.AppendLine("  COALESCE(TO_DATE(TANGGAL2,'DD-MM-YYYY'),NULL) TANGGAL2, ")
        //         sb.AppendLine("  SHOP, ")
        //         sb.AppendLine("  ISTYPE, ")
        //         sb.AppendLine("  PRICE_IDM, ")
        //         sb.AppendLine("  PPNBM_IDM, ")
        //         sb.AppendLine("  PPNRP_IDM, ")
        //         sb.AppendLine("  SCTYPE, ")
        //         sb.AppendLine("  BKP, ")
        //         sb.AppendLine("  SUB_BKP, ")
        //         sb.AppendLine("  CABANG, ")
        //         sb.AppendLine("  TIPE_GDG, ")
        //         sb.AppendLine("  '" & UserMODUL & "', ")
        //         sb.AppendLine("  NOW(), ")
        //         sb.AppendLine("  '" & fileRTT & "', ")
        //         sb.AppendLine("  PPN_RATE ")
        //         sb.AppendLine("FROM temp_rtt_idm ")
        //         sb.AppendLine("JOIN tbmaster_prodcrm ")
        //         sb.AppendLine("ON prc_pluidm = prdcd ")
        //         sb.AppendLine("AND prc_group = 'I' ")
        //         sb.AppendLine("JOIN tbmaster_prodmast ")
        //         sb.AppendLine("ON prd_prdcd = prc_pluigr ")
        //         sb.AppendLine("WHERE ISTYPE || SCTYPE = '05010000' ")
        //         sb.AppendLine("AND EXISTS ( ")
        //         sb.AppendLine("  SELECT 1 ")
        //         sb.AppendLine("  FROM tbmaster_perusahaan ")
        //         sb.AppendLine("  WHERE 'GI' || prs_kodeigr = gudang ")
        //         sb.AppendLine(")  ")
        //         sb.AppendLine("AND EXISTS ( ")
        //         sb.AppendLine("  SELECT 1 ")
        //         sb.AppendLine("  FROM tbmaster_tokoigr ")
        //         sb.AppendLine("  WHERE tko_kodeomi = toko ")
        //         sb.AppendLine(") ")
        //         sb.AppendLine("AND EXISTS ( ")
        //         sb.AppendLine("  SELECT 1 ")
        //         sb.AppendLine("  FROM tbmaster_tokoigr ")
        //         sb.AppendLine("  WHERE tko_kodeomi = shop ")
        //         sb.AppendLine(") ")
        //         sb.AppendLine("AND NOT EXISTS ( ")
        //         sb.AppendLine("  SELECT 1 ")
        //         sb.AppendLine("  FROM rtt_idm_interface ")
        //         sb.AppendLine("  WHERE rtt_idm_interface.docno = temp_rtt_idm.docno ")
        //         sb.AppendLine("  AND rtt_idm_interface.toko = temp_rtt_idm.toko ")
        //         sb.AppendLine(") ")
    }

    public function actionCetak(){
        // ExecScalar("SELECT DISTINCT rom_nodokumen FROM TBTR_RETUROMI WHERE rom_noreferensi = '" & noRTT & "' AND DATE_TRUNC('DAY',rom_tglreferensi) = TO_DATE('" & tglRTT & "','DD/MM/YYYY') AND rom_kodetoko = '" & shop & "' LIMIT 1 ", _
        //            "GET NONRB", noNrb)

        // Sql = "SELECT prd_kodeigr kode_igr,ROM_NODOKUMEN, ROM_TGLDOKUMEN, ROM_PRDCD,PRD_UNIT,ROM_NOREFERENSI, "
        // Sql += "prd_frac, prd_deskripsipendek, (ROM_QTY+ROM_QTYTLR) qty,"
        // Sql += "ROM_QTY qtyf, ((ROM_QTY+ROM_QTYTLR) - ROM_QTYREALISASI) fisikkrg , "
        // Sql += "(ROM_QTYREALISASI - (ROM_QTYMLJ+ROM_QTYTLJ)) fisiktolak ,ROM_QTYTLR ba, "
        // Sql += "(ROM_QTY * ROM_AVGCOST) ttl_Avg,(ROM_QTYTLR * ROM_HRGSATUAN) ttl, ROM_HRGSATUAN,ROM_AVGCOST, "
        // Sql += "rom_tglreferensi,ROM_TGLREFERENSI, "
        // Sql += "(SELECT BTH_NODOC FROM TBTR_BATOKO_H WHERE BTH_PBR = ROM_NODOKUMEN AND BTH_TGPBR = rom_tgldokumen LIMIT 1 ) noba   "
        // Sql += "FROM  tbtr_returomi, tbmaster_prodmast   "
        // Sql += "WHERE ROM_PRDCD = prd_prdcd AND ROM_KODEIGR = prd_kodeigr  "
        // Sql += "AND ROM_NODOKUMEN =  '" & noNrb & "' "
        // Sql += "AND DATE_TRUNC('DAY',rom_tgldokumen) = DATE_TRUNC('DAY',CURRENT_DATE) "

        // cmd.CommandText = "select prs_kodeigr kode_igr, PRS_NAMACABANG from tbmaster_perusahaan "
        //     myDa.SelectCommand = cmd
        //     myDa.Fill(myDs, "PERUSAHAAN")

        // Sql = "select tko_kodeigr kode_igr, TKO_NAMAOMI , TKO_KODEOMI, "
        //     Sql += "'" & noRTT & "' RETURID, '" & tglRTT & "' TGLNRB "
        //     Sql += "from tbmaster_tokoigr "
        //     Sql += "where TKO_KODEOMI = '" & shop & "'  "
        //     Sql += "and TKO_NAMASBU = 'INDOMARET'"

        //     cmd.CommandText = Sql
        //     myDa.SelectCommand = cmd
        //     myDa.Fill(myDs, "TOKO")
    }
}
