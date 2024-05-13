<?php

namespace App\Http\Controllers;
ini_set('max_execution_time', '0');

use App\Helper\ApiFormatter;
use App\Helper\DatabaseConnection;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GeneralExcelExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;

class KlikIgrController extends Controller
{

    protected $kodeDGV = 0;

    public function __construct(Request $request){
        DatabaseConnection::setConnection(session('KODECABANG'), "PRODUCTION");
    }

    public function index(){
        // $this->createTableIPP_ONL();
        // // $this->getKonversiItemPerishable(true);

        // if(session('flagSPI')){
        //     $this->createTablePSP_SPI();
        //     $this->addColHitungUlang_SPI();
        //     $this->alterDPDNOIDCTN();

        //     $data['cbAutoSendHH'] = false;
        // }else{
        //     $this->createLogUpdateRealisasiKlik();
        //     $this->alterTablePickingRakToko();

        //     $data['cbAutoSendHH'] = true;
        // }

        // //! Picking Rak Toko hanya IGRBDG
        // $data['cbPickRakToko'] = false;
        // if(session('KODECABANG') == '04'){
        //     $data['cbPickRakToko'] = true;
        // }

        // $this->createAlasanBatalKlik();
        // $this->alterTableSendHHotomatis();
        // $this->alterTableCODVA();
        // $this->alterTableCODPOIN();
        // $this->alterTBTR_TRANSAKSI_VA();

        // if(session('flagSPI')){
        //     $dtUrl = DB::table('tbmaster_webservice')->where('ws_nama', 'WS_SPI')->first();
        // }else{
        //     $dtUrl = DB::table('tbmaster_webservice')->where('ws_nama', 'WS_KLIK')->first();
        // }

        // if(!empty($dtUrl)){
        //     $data['urlUpdateStatusKlik'] = $dtUrl->ws_url . '/updatestatustrx';
        //     $data['urlUpdateRealisasiKlik'] = $dtUrl->ws_url . '/updtqtyrealisasi';
        // }

        // if(session('flagSPI')){
        //     if (str_contains(session('flagHHSPI'), 'H') AND !str_contains(session('flagHHSPI'), 'D')){
        //         $data['statusGroupBox'] = "SPI";
        //         $data['statusSiapPicking'] = "Siap Send HH";
        //         $data['statusSiapPacking'] = "Siap Packing";
        //         $data['btnSendJalur'] = "Send Handheld";
        //     }elseif(str_contains(session('flagHHSPI'), 'H') AND str_contains(session('flagHHSPI'), 'D')){
        //         $data['statusGroupBox'] = "SPI";
        //         $data['statusSiapPicking'] = "Siap Send DPD";
        //         $data['statusSiapPacking'] = "Siap Scanning";
        //         $data['btnSendJalur'] = "Send DPD";
        //     }else{
        //         $data['statusGroupBox'] = "SPI";
        //         $data['statusSiapPicking'] = "Siap Send Jalur";
        //         $data['statusSiapPacking'] = "Siap Scanning";
        //         $data['btnSendJalur'] = "Send Jalur";

        //         $data['btnCetakIIK'] = false;
        //         $data['btnPBBatal'] = 'List PB dan Item Batal';
        //     }
        // }else{
            $data['statusGroupBox'] = "KLIK IGR";
            $data['statusSiapPicking'] = "Siap Send HH";
            $data['statusSiapPacking'] = "Siap packing";
            $data['btnSendJalur'] = "Send Handheld";

            $data['btnPBBatal'] = 'List Item PB Batal';
        // }

        $firstTwoChars = substr(session("KODECABANG"), 0, 2);

        if ($firstTwoChars === "04") {
            $data["cbPickRakTokoVisible"] = true;
        } else {
            $data["cbPickRakTokoVisible"] = false;
        }

        //!! FUNCTION BELUM DIBUAT
        // $this->bersihBersihIntransit();

        $data['FlagProcess'] = False;
        $data['FlagSendHH'] = False;
        $data['alamatOK'] = False;
        $data['memberOK'] = False;
        $data['btnKonfirmasiBayar'] = False;
        $data['dgv_notrans'] = false;

        //!! FUNCTION BELUM DIBUAT
        // $this->updateDataVoid();
        // $this->listObi_H();

        // if(session('flagSPI')){
        //     $this->cekPBAkanBatal();
        // }

        // $this->cekItemBatal(True);

        return view('menu.klik-igr', $data);
    }

    //* function listObi_H
    public function datatables($tanggal_trans, $statusSiapPicking, $statusSiapPacking){
        $query = '';
        $query .= "SELECT row_number() OVER ( ";
        $query .= "  ORDER BY obi_maxdeliverytime, obi_mindeliverytime, ";
        $query .= "    CASE UPPER(COALESCE(obi_shippingservice,'Z'))  ";
        $query .= "      WHEN 'S' THEN 1  ";
        $query .= "      WHEN 'N' THEN 2  ";
        $query .= "      WHEN 'Z' THEN 3  ";
        $query .= "      ELSE 999  ";
        $query .= "    END,  ";
        $query .= "  no_trans  ";
        $query .= "  ) NO, p.*  ";
        $query .= "FROM ( ";
        $query .= "  SELECT CASE WHEN obi_recid IS NULL THEN '" . $statusSiapPicking . "'  ";
        $query .= "              WHEN substr(obi_recid,1,1) = '1' THEN 'Siap Picking'  ";
        $query .= "              WHEN substr(obi_recid,1,1) = '2' THEN '" . $statusSiapPacking . "'  ";
        $query .= "              WHEN substr(obi_recid,1,1) = '3' THEN 'Siap Draft Struk'  ";
        $query .= "              WHEN substr(obi_recid,1,1) = '4' THEN 'Konfirmasi Pembayaran'  ";
        $query .= "              WHEN substr(obi_recid,1,1) = '5' THEN 'Siap Struk'  ";
        $query .= "              WHEN substr(obi_recid,1,1) = '6' THEN 'Selesai Struk'  ";
        $query .= "              WHEN substr(OBI_RECID,1,1) = '7' THEN 'Set Ongkir'  ";
        $query .= "              WHEN substr(OBI_RECID,1,1) = 'B' THEN 'Transaksi Batal'  ";
        $query .= "         END status,  ";
        $query .= "         obi_kdmember kode_member, ";
        $query .= "         CASE WHEN COALESCE(obi_attribute2,'KlikIGR') = 'KlikIGR' THEN ";
        $query .= "              CASE WHEN COALESCE(cus_jenismember,'N') = 'T' THEN 'TMI'  ";
        $query .= "                   WHEN COALESCE(cus_flagmemberkhusus,'N') = 'Y' THEN 'Member Merah' ";
        $query .= "                   ELSE 'Member Umum' ";
        $query .= "              END ";
        $query .= "              WHEN COALESCE(obi_attribute2,'KlikIGR') = 'Corp' THEN 'Corporate' ";
        $query .= "              WHEN COALESCE(obi_attribute2,'KlikIGR') = 'TMI' THEN 'TMI' ";
        $query .= "              WHEN COALESCE(obi_attribute2,'KlikIGR') = 'SPI' THEN 'SPI' ";
        $query .= "              ELSE 'Member Merah' ";
        $query .= "         END tipe_member, ";
        $query .= "         obi_nopb no_pb, ";
        $query .= "         obi_tglpb tgl_pb,  ";
        $query .= "         obi_notrans no_trans,  ";
        $query .= "         COALESCE(obi_nopo, '-') no_po, ";
        $query .= "         CASE WHEN COALESCE(obi_freeongkir, 'N') = 'Y' THEN 'Free Ongkir' ";
        $query .= "              WHEN COALESCE(obi_freeongkir, 'N') = 'N' THEN 'Ongkir' ";
        $query .= "              ELSE 'Ambil Di Toko' ";
        $query .= "         END ongkir, ";
        $query .= "         CASE WHEN COALESCE(obi_tipebayar, 'TRF') = 'COD' THEN 'COD' ";
        $query .= "              WHEN COALESCE(obi_tipebayar, 'TRF') = 'COD-POIN' THEN 'COD-POIN' ";
        $query .= "              WHEN COALESCE(tva_bank, 'TRF') != 'TRF' THEN 'COD-VA' ";
        $query .= "              WHEN UPPER(COALESCE(obi_tipebayar, 'X')) = 'TOP' THEN 'Kredit' ELSE ";
        $query .= "           CASE WHEN COALESCE(cus_flagkredit, '-') = 'Y' AND COALESCE(obi_flagbayar, 'N') <> 'Y' THEN 'Kredit' ";
        $query .= "                ELSE 'Tunai' ";
        $query .= "           END ";
        $query .= "         END TIPE_BAYAR, ";
        $query .= "         obi_notrans notrans,  ";
        $query .= "         to_char(obi_tgltrans,'DD-MM-YYYY') tgltrans,  ";
        $query .= "         COALESCE(obi_attribute2,'KlikIGR') kodeweb, ";
        $query .= "         COALESCE(obi_freeongkir, 'N') free_ongkir, ";
        $query .= "         COALESCE(cus_flagkredit, '-') tipe_kredit,  ";
        $query .= "         COALESCE(obi_alasanbtl,'-') alasan_batal,  ";
        $query .= "         obi_itemorder item_order,  ";
        $query .= "         obi_realitem item_real,  ";
        $query .= "         to_char(ROUND(obi_ttlorder + obi_ttlppn, 0) ,'9,999,999,999') total_order,  ";
        $query .= "         to_char(ROUND(obi_realorder + obi_realppn, 0), '9,999,999,999') total_real,  ";
        $query .= "         to_char(obi_ttlorder, '9,999,999,999') dpp_order,  ";
        $query .= "         to_char(obi_realorder,'9,999,999,999') dpp_real,  ";
        $query .= "         to_char(obi_ttlppn,'9,999,999,999') ppn_order,  ";
        $query .= "         to_char(obi_realppn,'9,999,999,999') ppn_real,  ";
        $query .= "         to_char(obi_ttldiskon,'9,999,999,999') diskon_order,  ";
        $query .= "         to_char(obi_realdiskon,'9,999,999,999') diskon_real,  ";
        $query .= "         to_char(obi_ekspedisi,'99,999,999') ekspedisi, ";
        $query .= "         '-' member_obi, ";
        $query .= "         obi_zona zona, ";
        $query .= "         COALESCE(obi_kdekspedisi, '-') kdekspedisi, ";
        $query .= "         COALESCE(obi_jrkekspedisi, 0) jarakkirim, ";
        $query .= "         COALESCE(obi_flagbayar, 'N') flagbayar, ";
        $query .= "         CASE obi_shippingservice WHEN 'S' THEN 'Sameday' WHEN 'N' THEN 'Nextday' ELSE '' END AS service, ";
        $query .= "         to_char(obi_mindeliverytime,'DD-MM-YYYY HH24:MI:SS') AS \"TGL & JAM PB\", ";
        $query .= "         to_char(obi_maxdeliverytime,'DD-MM-YYYY HH24:MI:SS') AS \"MAX SERAH TERIMA\", ";

        $query .= "         CASE OBI_FLAGSENDHH  ";
        $query .= "             WHEN '1' THEN 'SEND JALUR DPD' ";
        $query .= "             WHEN '2' THEN 'SEND JALUR HH' ";
        $query .= "         ELSE '-' ";
        $query .= "         END AS \"STATUS SEND JALUR\", ";
        $query .= "         obi_shippingservice, obi_maxdeliverytime , obi_mindeliverytime ";
        $query .= "    FROM tbtr_obi_h  ";
        $query .= "    LEFT JOIN tbmaster_customer ON obi_kdmember = cus_kodemember ";
        $query .= "    LEFT JOIN tbtr_transaksi_va ON SUBSTR(obi_nopb, 0, 6)= tva_trxid AND obi_tglpb = tva_tglpb ";
        // $query .= "   WHERE DATE_TRUNC('DAY',obi_tgltrans) = '".Carbon::parse($tanggal_trans)->format('Y-m-d H:i:s')."'  ";

        // Adding conditional clause
        // if (session('flagSPI') == true) {
        //     $query .= "     AND UPPER(obi_nopb) LIKE '%SPI%' ";
        // } else {
        //     $query .= "     AND UPPER(obi_nopb) NOT LIKE '%SPI%' ";
        // }
        $query .= ") p LIMIT 25";


        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function detailTransaksi(Request $request){
        $selectedRow = $request->selectedRow;
        $tmpTotalOrder = 0;
        $tmpTotalReal = 0;
        $tmpOngkir = 0;
        $tmpCashbackOrder = 0;
        $tmpCashbackReal = 0;
        $tmpKupon = 0;

        $query = "SELECT cus_kodemember AS KODE_MEMBER, ";
        $query .= "       COALESCE(amm_namapenerima, cus_namamember) AS nama,  ";
        $query .= "       COALESCE(amm_email, cus_alamatemail, '-') AS email,  ";
        $query .= "       COALESCE(amm_nomorpenerima, '-') AS no_telp, ";
        $query .= "       COALESCE(amm_hp, cus_hpmember, '-') AS No_hp, ";
        $query .= "       COALESCE(amm_namaalamat, '-') AS alamat ";
        $query .= " FROM tbmaster_customer ";
        $query .= " LEFT JOIN tbtr_alamat_mm ";
        $query .= "   ON amm_kodemember = cus_kodemember ";
        $query .= "  AND amm_notrans = '" . $selectedRow["no_trans"] . "' ";
        $query .= "  AND amm_nopb = '" . $selectedRow["no_pb"] . "' ";
        $query .= "WHERE cus_kodemember = '" . $selectedRow["kode_member"] . "' ";
        $tmpMember = DB::select($query);

        if($selectedRow["flagbayar"] == "Y"){
            $query = " SELECT CASE WHEN COALESCE(tipe_bayar,'X') = 'COD' THEN 'CASH ON DELIVERY' ";
            $query .= "             ELSE tipe_bayar END AS tipe_bayar, ";
            $query .= "        TO_CHAR(tgl_bayar,'DD-MM-YYYY HH24:MI:SS') AS tgl_bayar, ";
            $query .= "        no_reference, ";
            $query .= "        COALESCE(admin_fee,0) AS admin_fee, ";
            $query .= "        COALESCE(total,0) AS ttl_bayar, ";
            $query .= "        COALESCE(pot_ongkir,0) AS pot_ongkir, ";
            $query .= "        COALESCE(ongkir,0) + COALESCE(pot_ongkir,0) AS ongkir, ";
            $query .= "        COALESCE(ongkir,0) AS ongkir_bersih, ";
            $query .= "        COALESCE(tipe_bayar,'X') AS tipe ";
            $query .= " FROM payment_klikigr ";
            $query .= " WHERE no_trans = '" . $selectedRow["no_trans"] . "' ";
            $query .= "  AND no_pb = '" . $selectedRow["no_pb"] . "' ";
            $query .= "  AND kode_member = '" . $selectedRow["kode_member"] . "' ";
            $query .= "  AND tipe_bayar <> 'SALDO' ";
            $query .= "  AND tipe_bayar <> 'POIN' ";
            $tmpPayment = DB::select($query);
            
            $query = " SELECT tipe_bayar, ";
            $query .= "        TO_CHAR(tgl_bayar,'DD-MM-YYYY HH24:MI:SS') AS tgl_bayar, ";
            $query .= "        no_reference, ";
            $query .= "        COALESCE(admin_fee,0) AS admin_fee, ";
            $query .= "        COALESCE(total,0) AS ttl_bayar, ";
            $query .= "        COALESCE(pot_ongkir,0) AS pot_ongkir, ";
            $query .= "        COALESCE(ongkir,0) + COALESCE(pot_ongkir,0) AS ongkir, ";
            $query .= "        COALESCE(ongkir,0) AS ongkir_bersih ";
            $query .= " FROM payment_klikigr ";
            $query .= " WHERE no_trans = '" . $selectedRow["no_trans"] . "' ";
            $query .= "  AND no_pb = '" . $selectedRow["no_pb"] . "' ";
            $query .= "  AND kode_member = '" . $selectedRow["kode_member"] . "' ";
            $query .= "  AND tipe_bayar = 'SALDO' ";

            $tmpPaymentSaldo = DB::select($query);

            $query = " SELECT tipe_bayar, ";
            $query .= "        TO_CHAR(tgl_bayar,'DD-MM-YYYY HH24:MI:SS') AS tgl_bayar, ";
            $query .= "        no_reference, ";
            $query .= "        COALESCE(admin_fee,0) AS admin_fee, ";
            $query .= "        COALESCE(total,0) AS ttl_bayar, ";
            $query .= "        COALESCE(pot_ongkir,0) AS pot_ongkir, ";
            $query .= "        COALESCE(ongkir,0) + COALESCE(pot_ongkir,0) AS ongkir, ";
            $query .= "        COALESCE(ongkir,0) AS ongkir_bersih ";
            $query .= " FROM payment_klikigr ";
            $query .= " WHERE no_trans = '" . $selectedRow["no_trans"] . "' ";
            $query .= "  AND no_pb = '" . $selectedRow["no_pb"] . "' ";
            $query .= "  AND kode_member = '" . $selectedRow["kode_member"] . "' ";
            $query .= "  AND tipe_bayar = 'POIN' ";

            $tmpPaymentPoin = DB::select($query);
        }

        $tmpEkspedisi = doubleval(str_replace(',', '', $selectedRow["ekspedisi"]));
        $tmpTotalOrder = doubleval(str_replace(',', '', $selectedRow["total_order"]));
        $tmpTotalReal = doubleval(str_replace(',', '', $selectedRow["total_real"]));

        $tmpTotalOrder = $tmpTotalOrder + $tmpOngkir;
        $tmpTotalReal = $tmpTotalReal + $tmpOngkir;

        $query = "SELECT COALESCE(SUM(COALESCE(cashback_order,0)),0) AS cashback_order, ";
        $query .= "       COALESCE(SUM(COALESCE(cashback_real,0)),0) AS cashback_real ";
        $query .= "FROM promo_klikigr ";
        $query .= "WHERE kode_member = '" . $selectedRow["kode_member"] . "' ";
        $query .= "  AND no_trans = '" . $selectedRow["no_trans"] . "' ";
        $query .= "  AND no_pb = '" . $selectedRow["no_pb"] . "' ";

        $tmpDt = DB::select($query);
        if(count($tmpDt)){
            $tmpCashbackOrder = $tmpDt[0]->cashback_order;
            $tmpCashbackReal = $tmpDt[0]->cashback_real;
        } else {
            $tmpCashbackOrder = 0;
            $tmpCashbackReal = 0;
        }

        $query = "SELECT COALESCE(SUM(COALESCE(nilai_kupon,0)),0) AS nilai_kupon ";
        $query .= "FROM kupon_klikigr ";
        $query .= "WHERE kode_member = '" . $selectedRow["kode_member"] . "' ";
        $query .= "  AND no_trans = '" . $selectedRow["no_trans"] . "' ";
        $query .= "  AND no_pb = '" . $selectedRow["no_pb"] . "' ";

        $tmpDt = DB::select($query);
        if(count($tmpDt)){
            $tmpKupon = $tmpDt[0]->nilai_kupon;
        } else {
            $tmpKupon = 0;
        }

        $query = "SELECT to_char(" . $tmpCashbackOrder . ", '99,999,999') AS total_cashback_order, ";
        $query .= "       to_char(" . $tmpCashbackReal . ", '99,999,999') AS total_cashback_real, ";
        $query .= "       to_char(" . $tmpKupon . ", '99,999,999') AS total_kupon, ";
        $query .= "       to_char(" . ($tmpTotalOrder - $tmpKupon - $tmpCashbackOrder) . ", '99,999,999') AS total_order, ";
        $query .= "       to_char(" . (($tmpTotalReal - $tmpKupon - $tmpCashbackReal) < 0 ? 0 : ($tmpTotalReal - $tmpKupon - $tmpCashbackReal)) . ", '99,999,999') AS total_real ";

        $tmpHarga = DB::select($query);


        $data['status_detail_transaksi_tab1'] = $selectedRow["status"];
        $data['no_po_detail_transaksi_tab1'] = $selectedRow["no_po"];
        $data['no_pb_detail_transaksi_tab1'] = $selectedRow["no_pb"];
        $data['tgl_pb_detail_transaksi_tab1'] = Carbon::parse($selectedRow["tgl_pb"])->format('Y-m-d');

        if(count($tmpMember)){
            $data['kode_member_detail_transaksi_tab1'] = $tmpMember[0]->kode_member;
            $data['nama_member_detail_transaksi_tab1'] = $tmpMember[0]->nama;
            $data['no_member_detail_transaksi_tab1'] = $tmpMember[0]->no_hp;
            $data['no_penerima_detail_transaksi_tab1'] = $tmpMember[0]->no_telp;
            $data['email_detail_transaksi_tab1'] = $tmpMember[0]->email;
            $data['alamat_detail_transaksi_tab1'] = $tmpMember[0]->alamat;
        } else {
            $data['kode_member_detail_transaksi_tab1'] = "-";
            $data['nama_member_detail_transaksi_tab1'] = "-";
            $data['no_member_detail_transaksi_tab1'] = "-";
            $data['no_penerima_detail_transaksi_tab1'] = "-";
            $data['email_detail_transaksi_tab1'] = "-";
            $data['alamat_detail_transaksi_tab1'] = "-";
        }

        //? Perhitungan Payment
        $flagMultiPayment = 0;
        $BayarCash = 0;
        $BayarSaldo = 0;
        $BayarPoin = 0;
        $Adminfee = 0;
        $TotalBayar = 0;
        $ttlOngkir = 0;
        $PotOngkir = 0;

        if(count($tmpPayment)){
            $flagMultiPayment += 1;
            $BayarCash = $tmpPayment[0]->ttl_bayar;
            $Adminfee += $tmpPayment[0]->admin_fee;
            $TotalBayar += $tmpPayment[0]->ttl_bayar;
            $PotOngkir += $tmpPayment[0]->pot_ongkir;
            $ttlOngkir += $tmpPayment[0]->ongkir;
        }

        if(count($tmpPaymentSaldo)){
            $flagMultiPayment += 1;
            $BayarCash = $tmpPaymentSaldo[0]->ttl_bayar;
            $Adminfee += $tmpPaymentSaldo[0]->admin_fee;
            $TotalBayar += $tmpPaymentSaldo[0]->ttl_bayar;
            $PotOngkir += $tmpPaymentSaldo[0]->pot_ongkir;
            $ttlOngkir += $tmpPaymentSaldo[0]->ongkir;
        }

        $query = "SELECT TRIM(to_char(" . $BayarCash . ", '99,999,999')) AS bayar_cash, ";
        $query .= "       TRIM(to_char(" . $BayarSaldo . ", '99,999,999')) AS bayar_saldo, ";
        $query .= "       TRIM(to_char(" . $BayarPoin . ", '99,999,999')) AS bayar_poin, ";
        $query .= "       TRIM(to_char(" . $Adminfee . ", '99,999,999')) AS fee, ";
        $query .= "       TRIM(to_char(" . $TotalBayar . ", '99,999,999')) AS total_bayar, ";
        $query .= "       TRIM(to_char(" . $PotOngkir . ", '99,999,999')) AS pot_ongkir, ";
        $query .= "       TRIM(to_char(" . $ttlOngkir . ", '99,999,999')) AS total_ongkir ";

        $tmpPaymentTTL = DB::select($query);

        if(count($tmpPayment)){
            $data['lbl_pembayaranVA_detail_transaksi_tab3'] = "Pembayaran " . $tmpPayment[0]->tipe;
            $data['tipe_pembayaran_detail_transaksi_tab3'] = $tmpPayment[0]->tipe_bayar;
            $data['tgl_bayar_detail_transaksi_tab3'] = Carbon::parse($tmpPayment[0]->tgl_bayar)->format('Y-m-d');
            $data['no_ref_detail_transaksi_tab3'] = $tmpPayment[0]->no_reference;
        } else {
            $data['lbl_pembayaranVA_detail_transaksi_tab3'] = "Pembayaran VA";
            $data['tipe_pembayaran_detail_transaksi_tab3'] = "-";
            $data['tgl_bayar_detail_transaksi_tab3'] = "-";
            $data['no_ref_detail_transaksi_tab3'] = "-";
        }

        if(count($tmpPaymentTTL)){
            $data['admin_fee_detail_transaksi_tab3'] = $tmpPaymentTTL[0]->fee;
            $data['pembayaranVA_detail_transaksi_tab3'] = $tmpPaymentTTL[0]->bayar_cash;
            $data['pembayaran_saldo_detail_transaksi_tab3'] = $tmpPaymentTTL[0]->bayar_saldo;
            $data['pembayaran_poin_detail_transaksi_tab3'] = $tmpPaymentTTL[0]->bayar_poin;
            $data['total_pembayaran_detail_transaksi_tab3'] = $tmpPaymentTTL[0]->bayar_poin;

            //? PENGIRIMAN
            $data['pot_ongkir_detail_transaksi_tab2'] = $tmpPaymentTTL[0]->pot_ongkir;
            $data['total_ongkir_detail_transaksi_tab2'] = $tmpPaymentTTL[0]->total_ongkir;
        } else {
            $data['admin_fee_detail_transaksi_tab3'] = "";
            $data['pembayaranVA_detail_transaksi_tab3'] = "";
            $data['pembayaran_saldo_detail_transaksi_tab3'] = "";
            $data['pembayaran_poin_detail_transaksi_tab3'] = "";
            $data['total_pembayaran_detail_transaksi_tab3'] = "";

            //? PENGIRIMAN
            $data['pot_ongkir_detail_transaksi_tab2'] = "";
            $data['total_ongkir_detail_transaksi_tab2'] = "";
        }

        $data['flag_pengiriman_detail_transaksi_tab2'] = $selectedRow["ongkir"];

        if(strtoupper($selectedRow["ongkir"]) == "AMBIL DI TOKO"){
            $data['ekspedisi_pengiriman_detail_transaksi_tab2'] = "-";
        } else {
            $data['ekspedisi_pengiriman_detail_transaksi_tab2'] = $selectedRow["kdekspedisi"];
        }
        
        $data['kredit_pembayaran_detail_transaksi_tab3'] = $selectedRow["tipe_bayar"];

        if($flagMultiPayment > 1){
            $data['tipe_pembayaran_detail_transaksi_tab3'] = "MULTI PAYMENT";
        }

        $data['qty_order_detail_transaksi_tab4'] = trim($selectedRow["item_order"]);
        $data['dpp_order_detail_transaksi_tab4'] = trim($selectedRow["dpp_order"]);
        $data['ppn_order_detail_transaksi_tab4'] = trim($selectedRow["ppn_order"]);
        $data['diskon_order_detail_transaksi_tab4'] = trim($selectedRow["diskon_order"]);
        $data['cashback_order_detail_transaksi_tab4'] = trim($tmpHarga[0]->total_cashback_order);
        $data['ongkir_order_detail_transaksi_tab4'] = trim($selectedRow["ekspedisi"]);
        $data['kupon_order_detail_transaksi_tab4'] = trim($tmpHarga[0]->total_kupon);
        $data['total_order_detail_transaksi_tab4'] = trim($selectedRow["total_order"]);
        $data['total_order_detail_transaksi_tab4'] = trim($tmpHarga[0]->total_order);

        $data['qty_real_detail_transaksi_tab4'] = trim($selectedRow["item_real"]);
        $data['dpp_real_detail_transaksi_tab4'] = trim($selectedRow["dpp_real"]);
        $data['ppn_real_detail_transaksi_tab4'] = trim($selectedRow["ppn_real"]);
        $data['diskon_real_detail_transaksi_tab4'] = trim($selectedRow["diskon_real"]);
        $data['cashback_real_detail_transaksi_tab4'] = trim(($data['qty_real_detail_transaksi_tab4'] == "0") ? "0" : $tmpHarga[0]->total_cashback_real);
        $data['ongkir_real_detail_transaksi_tab4'] = trim(($data['qty_real_detail_transaksi_tab4'] == "0") ? "0" : $selectedRow["ekspedisi"]);
        $data['kupon_real_detail_transaksi_tab4'] = trim(($data['qty_real_detail_transaksi_tab4'] == "0") ? "0" : $tmpHarga[0]->total_kupon);
        $data['total_real_detail_transaksi_tab4'] = trim($selectedRow["total_real"]);
        $data['total_real_detail_transaksi_tab4'] = trim(($data['qty_real_detail_transaksi_tab4'] == "0") ? "0" : $tmpHarga[0]->total_real);


        $data['tgl_trans_detail_transaksi_tab5'] = Carbon::parse($request->tanggal_trans)->format('Y-m-d');
        $data['no_trans_detail_transaksi_tab5'] = $selectedRow["notrans"];

        if (session("flagIGR") && !session("flagSPI")) {
            // $query = "SELECT OBR_BARCODE AS NO_KOLI,
            //             OBR_PRINTERNAME AS NAMA_PRINTER,
            //             OBR_CREATE_BY AS CHECKER_ID,
            //             TO_CHAR(OBR_CREATE_DT, 'DD-MM-YYYY') AS TGL_PB
            //         FROM TBMASTER_OBI_BARCODE
            //         WHERE OBR_BARCODE LIKE '%' || substr('" . $request->tanggal_trans . "', -2) || substr('" . $request->tanggal_trans . "', 3, 2) || substr('" . $request->tanggal_trans . "', 0, 2) || str_pad(substr('" . $selectedRow["notrans"] . "', -4), 4, '0', STR_PAD_LEFT) || '%'
            //         ORDER BY obr_barcode DESC";
        } else {
            $query = "SELECT PICO_BARCODEKOLI AS NO_KOLI,
                        PICO_PRINTERNAME AS NAMA_PRINTER,
                        PICO_CHECKERID AS CHECKER_ID,
                        TO_CHAR(obi_tglpb, 'dd-MM-yyyy') AS TGL_PB
                    FROM PICKING_CONTAINER
                    JOIN TBTR_OBI_H ON CAST(OBI_NOPICK AS numeric) = CAST(PICO_NOPICK AS numeric)
                    WHERE TO_CHAR(obi_tglpb, 'dd-MM-yyyy') = '" . $request->tanggal_trans . "'
                        AND obi_notrans = '" . $selectedRow["notrans"] . "'";
        }

        $data["dtKoli"] = DB::select($query);
        return ApiFormatter::success(200, "success", $data);
    }

    public function passwordManager(Request $request){
        if($request->mode === "isOTP"){
            $now = now();
            $passOTP = ($now->day + $now->hour + 10) * 12345;
            $strPassOTP = strval($passOTP);
            if($request->input('password_manager') == $strPassOTP){
                return ApiFormatter::success(200, "Password OTP Berhasil !");
            } else{
                return ApiFormatter::error(400, "Password OTP Salah !" . $strPassOTP);
            }
        } else if($request->mode === "isManager"){
            $query = '';
            $query .= "SELECT userid FROM tbmaster_user WHERE jabatan IN ('1', '2', '3', '4') AND recordid IS NULL AND userpassword = '" . $request->password_manager . "' ";
            $data = DB::select($query);
            if(count($data) > 0){
                return ApiFormatter::success(200, "Password Manager Berhasil !");
            } else {
                if($request->count >= 2){
                    return ApiFormatter::error(400, "Anda Tidak Berhak Melakukan Edit PB !");
                } else {
                    return ApiFormatter::error(400, "Password Manager Salah !");
                }
            }
        } else {
            if($request->input('password_manager') == "adminklikigr"){
                return ApiFormatter::success(200, "Password Manager Berhasil !");
            } else {
                if($request->count >= 2){
                    return ApiFormatter::error(400, "Anda Tidak Berhak Melakukan Edit PB yang sudah keluar DSP!");
                } else {
                    return ApiFormatter::error(400, "Password Admin Salah !");
                }
            }
        }
    }

    //* modal muncul datatable
    //* sekalian download csv -> kalo gabisa di modal tambahin button aja
    public function actionF1(Request $request){
        $query = '';
        $query .= "SELECT obi_prdcd PLU, ";
        $query .= "       COALESCE(prd_deskripsipanjang,'TIDAK ADA DI PRODMAST') Barang, ";
        $query .= "       obi_qtyorder / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) Jumlah, ";
        $query .= "       TO_CHAR(COALESCE(obi_hargaweb, ROUND((obi_hargasatuan + obi_ppn) * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), 0)), '999,999,999') Harga, ";
        $query .= "       TO_CHAR(ROUND(obi_diskon * obi_qtyorder,0), '999,999,999') Diskon, ";
        $query .= "       TO_CHAR((COALESCE(obi_hargaweb, ROUND((obi_hargasatuan + obi_ppn) * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), 0)) * (obi_qtyorder / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END))) - ROUND(obi_diskon * obi_qtyorder,0), '999,999,999') SubTotal, ";
        $query .= "       prd_kodetag tag, *";
        $query .= "  FROM tbtr_obi_h h ";
        $query .= "  JOIN tbtr_obi_d d ";
        $query .= "    ON h.obi_notrans = d.obi_notrans ";
        $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "  LEFT JOIN tbmaster_prodmast ";
        $query .= "   ON obi_prdcd = prd_prdcd ";
        $query .= " WHERE h.obi_notrans = '" . $request->no_trans . "' ";
        $query .= "   AND h.obi_nopb = '" . $request->nopb . "' ";
        $query .= " ORDER BY 2,1";
        $data['data'] = DB::select($query);
        $data['nopb'] = $request->nopb;

        Cache::put('detail_pb', $data, now()->addMinutes(2));

        return DataTables::of($data['data'])
            ->addIndexColumn()
            ->make(true);
    }

    public function actionF1DownloadCSV(){
        $data = Cache::get('detail_pb');
        return Excel::download(new GeneralExcelExport($data['data']), "LIST_PB_OBI_" . explode("/", $data['nopb'])[0] . ".csv");
    }

    public function actionF2(Request $request){
        $query = '';
        $query .= "SELECT kode_kupon as \"KODE PROMO\", ";
        $query .= "       'KUPON' as TIPE, ";
        $query .= "       COALESCE(nama_kupon,kode_kupon) as PROMO, ";
        $query .= "       TO_CHAR(nilai_kupon,'999,999,999') as POTONGAN ";
        $query .= "FROM kupon_klikigr ";
        $query .= "WHERE kode_member = '" . $request->member_igr . "' ";
        $query .= "  AND no_trans = '" . $request->no_trans . "' ";
        $query .= "  AND no_pb = '" . $request->nopb . "' ";
        $query .= "UNION  ";
        $query .= "SELECT kode_promo, ";
        $query .= "       tipe_promo tipe, ";
        $query .= "       CASE WHEN tipe_promo = 'CASHBACK' ";
        $query .= "        THEN  ";
        $query .= "         CASE WHEN id_tipe = '0' ";
        $query .= "         THEN 'CASHBACK' ";
        $query .= "         ELSE 'CASHBACK' ";
        $query .= "         END ";
        $query .= "        ELSE COALESCE(gift_real, gift_order, '-') ";
        $query .= "       END promo, ";
        $query .= "       CASE WHEN tipe_promo = 'CASHBACK' ";
        $query .= "        THEN TO_CHAR(COALESCE(cashback_real, cashback_order, 0),'999,999,999') ";
        $query .= "       END potongan ";
        $query .= "FROM promo_klikigr ";
        $query .= "WHERE kode_member = '" . $request->member_igr . "' ";
        $query .= "  AND no_trans = '" . $request->no_trans . "' ";
        $query .= "  AND no_pb = '" . $request->nopb . "' ";
        $query .= "ORDER BY 2 ASC, 1 ASC ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function actionF3(Request $request){
        $query = '';
        $query .= "SELECT d.obi_prdcd PLU, ";
        $query .= "       prd_deskripsipanjang DESKRIPSI, ";
        $query .= "       (d.obi_qtyorder / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END)) as QTY_ORDER, ";
        $query .= "       ROUND((d.obi_qtyrealisasi / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END)),2) AS QTY_PICKING, ";
        $query .= "       CASE WHEN d.obi_pick_dt IS NOT NULL THEN CASE WHEN d.obi_close_dt IS NOT NULL THEN 'Sudah Close Picking' ELSE 'Sudah Picking' END ELSE 'Belum Picking' END AS STATUS_PICKING, ";
        $query .= "       COALESCE(d.obi_grouppicker, '-') as GROUP_NAME, ";

        $query .= "       COALESCE(d.obi_picker, '-') as PICKER, ";
        $query .= "       ROUND(SUM(p.pobi_qty / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END)),2) as QTY_PACKING ";
        $query .= "  FROM tbtr_obi_h h ";
        $query .= "  JOIN tbtr_obi_d d ";
        $query .= "    ON h.obi_notrans = d.obi_notrans ";
        $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "  LEFT JOIN tbtr_packing_obi p ";
        $query .= "    ON d.obi_prdcd = p.pobi_prdcd ";
        $query .= "   AND d.obi_notrans = p.pobi_notransaksi ";
        $query .= "   AND d.obi_tgltrans = p.pobi_tgltransaksi ";
        $query .= "  LEFT JOIN picker_klik ";
        $query .= "    ON pk_group = COALESCE(d.obi_grouppicker,'-') ";
        $query .= "   AND pk_koderak = COALESCE(d.obi_koderak,'-') ";
        $query .= "   AND pk_kodesubrak = COALESCE(d.obi_kodesubrak,'-') ";
        $query .= "   AND pk_userid = d.obi_picker ";
        $query .= "  JOIN tbmaster_prodmast ";
        $query .= "    ON d.obi_prdcd = prd_prdcd ";
        $query .= " WHERE d.obi_notrans = '" . $request->no_trans . "' ";
        $query .= "   AND h.obi_nopb = '" . $request->nopb . "' ";
        $query .= " GROUP BY d.obi_prdcd, prd_deskripsipanjang, ";
        $query .= "     (d.obi_qtyorder / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END)), ";
        $query .= "     ROUND((d.obi_qtyrealisasi / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END)),2), ";
        $query .= "     COALESCE(d.obi_grouppicker, '-'), COALESCE(d.obi_picker, '-'), ";
        $query .= "     CASE WHEN d.obi_pick_dt IS NOT NULL THEN CASE WHEN d.obi_close_dt IS NOT NULL THEN 'Sudah Close Picking' ELSE 'Sudah Picking' END ELSE 'Belum Picking' END ";
        $query .= " ORDER BY 2,1 ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function actionF4(Request $request){
        //* message -> Edit PB/Validasi Rak untuk Item Batal?
        //* OPEN FORM -> frmEditPB
        if($request->actionSelected === "VALIDASI RAK"){
            $query = '';
            $query .= "SELECT d.obi_prdcd AS PLU, ";
            $query .= "       COALESCE(p.prd_deskripsipanjang,'TIDAK ADA DI PRODMAST') AS Nama_Barang, ";
            $query .= "       d.obi_qtyorder AS QTY_Order, ";
            $query .= "       COALESCE(b.obi_qtyrealisasi,0) AS QTY_Realisasi ";
            $query .= "FROM tbtr_obi_d d, tbmaster_prodmast p, tbhistory_obi_batal b ";
            $query .= "WHERE d.obi_notrans = '" . $request->no_trans . "' ";
            $query .= "AND d.obi_tgltrans = (SELECT obi_tgltrans FROM tbtr_obi_h WHERE obi_nopb = '" . $request->nopb . "') ";
            $query .= "AND d.obi_recid = '1' ";
            $query .= "AND d.obi_prdcd = b.obi_prdcd AND b.obi_prdcd = p.prd_prdcd AND d.obi_notrans = b.obi_notrans";
        } else {
            $query = '';
            $query .= "SELECT obi_prdcd AS PLU, ";
            $query .= "       COALESCE(p.prd_deskripsipanjang,'TIDAK ADA DI PRODMAST') AS Nama_Barang, ";
            $query .= "       obi_qtyorder AS QTY_Order, ";
            $query .= "       COALESCE(obi_qtyrealisasi,0) AS QTY_Realisasi ";
            $query .= "FROM tbtr_obi_d ";
            $query .= "LEFT JOIN tbmaster_prodmast p ON p.prd_prdcd = obi_prdcd ";
            $query .= "WHERE obi_notrans = '" . $request->no_trans . "' ";
            $query .= "AND obi_tgltrans = (SELECT obi_tgltrans FROM tbtr_obi_h WHERE obi_nopb = '" . $request->nopb . "') ";
            $query .= "AND obi_recid IS NULL ";
        }

        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function actionF4ValidasiRak(Request $request){
        DB::beginTransaction();
	    try{
            foreach ($request->datatables as $item) {
                $query = '';
                $query .= "UPDATE tbtr_obi_d ";
                $query .= "SET obi_recid = '2' ";
                $query .= "WHERE obi_notrans = '" . $request->no_trans . "' ";
                $query .= "AND obi_prdcd = '" . $item["plu"] . "' ";
                $query .= "AND obi_tgltrans = (SELECT obi_tgltrans FROM tbtr_obi_h WHERE obi_nopb = '" . $request->nopb . "') ";

                DB::update($query);
            }

            DB::commit();
            return ApiFormatter::success(200, 'Berhasil Validasi Rak');

        } catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            return ApiFormatter::error(400, $message);
        }
    }

    public function actionF4ItemBatal(Request $request){
        DB::beginTransaction();
	    try{
            foreach ($request->datatables as $item){
                $query = "";
                $query .= "INSERT INTO tbhistory_obi_batal ( ";
                $query .= "    obi_notrans, ";
                $query .= "    obi_tgltrans, ";
                $query .= "    obi_prdcd, ";
                $query .= "    obi_qtyorder, ";
                $query .= "    obi_qtyrealisasi, ";
                $query .= "    OBI_CREATE_BY, ";
                $query .= "    OBI_CREATE_DT ";
                $query .= ") ";
                $query .= "SELECT ";
                $query .= "    obi_notrans, ";
                $query .= "    obi_tgltrans, ";
                $query .= "    obi_prdcd, ";
                $query .= "    obi_qtyorder, ";
                $query .= "    obi_qtyrealisasi, ";
                $query .= "    '" . session('userid') . "', ";
                $query .= "    NOW() ";
                $query .= "FROM tbtr_obi_d ";
                $query .= "WHERE obi_notrans = '" . $request->no_trans . "' ";
                $query .= "    AND obi_prdcd = '" . $item["plu"] . "' ";
                $query .= "    AND obi_tgltrans = (SELECT obi_tgltrans FROM tbtr_obi_h WHERE obi_nopb = '" . $request->nopb . "') ";
                DB::insert($query);

                $query = "";
                if ($item["qty_realisasi"] === 0) {
                    $query .= "UPDATE tbtr_obi_d SET obi_recid = '2', ";
                } else {
                    $query .= "UPDATE tbtr_obi_d SET obi_recid = '1', ";
                }
                $query .= "obi_qtyrealisasi = 0 ";
                $query .= "WHERE obi_notrans = '" . $request->no_trans . "' AND obi_prdcd = '" . $item["plu"] . "' ";
                $query .= "AND obi_tgltrans = (SELECT obi_tgltrans FROM tbtr_obi_h WHERE obi_nopb = '" . $request->nopb . "') ";
                DB::update($query);

                $query = "";
                $query .= "UPDATE TBTR_PACKING_OBI ";
                $query .= "SET POBI_QTY = 0, ";
                $query .= "    POBI_MODIFY_BY = '" . session('userid') . "', ";
                $query .= "    POBI_MODIFY_DT = CURRENT_TIMESTAMP  ";
                $query .= "WHERE POBI_NOTRANSAKSI = '" . $request->no_trans . "' ";
                $query .= "    AND POBI_PRDCD = '" . $item["plu"] . "' ";
                $query .= "    AND POBI_TGLTRANSAKSI = (SELECT obi_tgltrans FROM tbtr_obi_h WHERE obi_nopb = '" . $request->nopb . "') ";
                DB::update($query);
            }

            DB::commit();
            return $this->actionF4PrintItemBatal($request);

        } catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            return ApiFormatter::error(400, $message);
        }
    }

    public function actionF4PrintItemBatal(Request $request){
        $query = "";
        $query .= "SELECT d.obi_prdcd, p.prd_deskripsipendek, b.obi_qtyrealisasi, ";
        $query .= "       d.obi_tiperak, d.obi_koderak, d.obi_kodesubrak, d.obi_shelvingrak ";
        $query .= "FROM tbtr_obi_d AS d, tbmaster_prodmast AS p, tbhistory_obi_batal AS b ";
        $query .= "WHERE d.obi_notrans = '" . $request->no_trans . "' ";
        $query .= "  AND b.obi_qtyrealisasi > 0 ";
        $query .= "  AND d.obi_tgltrans = (SELECT obi_tgltrans FROM tbtr_obi_h WHERE obi_nopb = '" . $request->nopb . "') ";
        $query .= "  AND d.obi_prdcd = b.obi_prdcd AND b.obi_prdcd = p.prd_prdcd AND d.obi_notrans = b.obi_notrans AND d.obi_tgltrans = b.obi_tgltrans ";
        $query .= "ORDER BY d.obi_prdcd ";
        $data = DB::select($query);

        $str = "KODE IGR  : " . str_pad(session('KODECABANG'), 20, " ") . PHP_EOL;
        $str .= "NO PB     : " . str_pad($request->nopb, 20, " ") . PHP_EOL;
        $str .= "TGL PB    : " . str_pad($request->tgl_pb, 20, " ") . PHP_EOL;
        $str .= "JML ITEM  : " . str_pad(count($data), 2, " ", STR_PAD_LEFT) . " ITEM" . PHP_EOL;
        $str .= "TGL CETAK : " . date("Y-m-d H:i:s") . PHP_EOL;
        $str .= "=============================================" . PHP_EOL;
        $str .= " NO. PLU - NAMA BARANG                       " . PHP_EOL;
        $str .= "     QTY   TIPERAK   KODERAK   SUBRAK   SHELV" . PHP_EOL;
        $str .= "=============================================" . PHP_EOL;

        foreach ($data as $index => $row) {
            $str .= str_pad(($index + 1), 4, " ", STR_PAD_LEFT) . " ";
            $str .= $row->obi_prdcd . " - " . $row->prd_deskripsipendek . str_pad($row->obi_qtyrealisasi, 7, " ", STR_PAD_LEFT);
            $str .= str_pad($row->obi_tiperak, 8, " ", STR_PAD_LEFT);
            $str .= str_pad($row->obi_koderak, 10, " ", STR_PAD_LEFT);
            $str .= str_pad($row->obi_kodesubrak, 10, " ", STR_PAD_LEFT);
            $str .= str_pad($row->obi_shelvingrak, 9, " ", STR_PAD_LEFT) . PHP_EOL;
        }


        $str .= "=============================================" . PHP_EOL;
        $str .= "                                             " . PHP_EOL;
        $str .= "                                             " . PHP_EOL;
        $str .= "                                             " . PHP_EOL;
        $str .= "                                             " . PHP_EOL;
        $str .= "                                             " . PHP_EOL;
        $str .= "                                             " . PHP_EOL;
        $str .= "                                             " . PHP_EOL;
        $str .= "                                             " . PHP_EOL;
        $str .= "-----------                        ----------" . PHP_EOL;
        $str .= " ( ADMIN )                         ( PICKER )" . PHP_EOL;
        $str .= "                                             " . PHP_EOL;
        $str .= "                                             " . PHP_EOL;

        $files_content['content'] = $str;
        $files_content['nama_file'] = "ITEM_BATAL_" . session('KODECABANG') . '_' . $request->no_trans . '.txt';
        return ApiFormatter::success(200, "Proses Item Batal Berhasil", $files_content);
    }

    public function actionF5(Request $request){

        if($request->status === 'Transaksi Batal' AND $request->flag_bayar !== 'Y'){
            //* message -> Mengaktifkan Kembali Transaksi No." & dgv_notrans & " yang sudah batal?

            //* nanti ada login manager dengan form -> frmPassword

            $hasil = $this->ReaktivasiPB($request->no_trans, $request->nopb, $request->tanggal_trans);
            if($hasil === true){
                return ApiFormatter::success(200, "PB Berhasil Diaktifkan Kembali");
            } else{
                return ApiFormatter::error(400, "PB Gagal Diaktifkan Kembali");
            }

        }else{
            return ApiFormatter::error(400, 'Bukan data yang bisa diaktifkan kembali!');
        }
    }

    public function actionF6(Request $request){
        if($request->status !== 'Siap Struk'){
            return ApiFormatter::error(400, 'Bukan data yang bisa validasi struk!');
        }

        DB::beginTransaction();
	    try{
            $query = "";
            $query .= "SELECT obi_nopb ";
            $query .= "FROM tbtr_obi_h ";
            $query .= "WHERE obi_notrans = '" . $request->no_trans . "' ";
            $query .= "AND DATE_TRUNC('DAY', obi_tgltrans) = TO_DATE('" . $request->tanggal_trans . "','dd-MM-YYYY') ";
            $query .= "AND UPPER(obi_nopb) = UPPER('" . $request->nopb . "') ";
            $query .= "AND UPPER(obi_kdmember) = UPPER('" . $request->kode_member . "') ";
            $query .= "AND obi_recid = '5' ";
            $cek = DB::select($query);

            if(count($cek) == 0){
                return ApiFormatter::error(400, "Data Transaksi Siap Struk Tidak Ditemukan");
            }

            $query = "";
            $query .= "SELECT jh_transactionno ";
            $query .= "FROM tbtr_jualheader ";
            $query .= "WHERE jh_transactiontype = 'S' ";
            $query .= "AND jh_transactionno = '" . $request->no_struk . "' ";
            $query .= "AND DATE_TRUNC('DAY', jh_transactiondate) = TO_DATE('" . date('Y-m-d', strtotime($request->tanggal_struk)) . "','YYYY-MM-DD') ";
            $query .= "AND jh_cashierstation = '" . $request->station . "' ";
            $query .= "AND jh_cashierid = '" . $request->cashier . "' ";
            $cek = DB::select($query);
            if(count($cek) == 0){
                $query = "";
                $query .= "SELECT jh_transactionno ";
                $query .= "FROM tbtr_jualheader_interface ";
                $query .= "WHERE jh_transactiontype = 'S' ";
                $query .= "AND jh_transactionno = '" . $request->no_struk . "' ";
                $query .= "AND DATE_TRUNC('DAY', jh_transactiondate) = TO_DATE('" . date('Y-m-d', strtotime($request->tanggal_struk)) . "','YYYY-MM-DD') ";
                $query .= "AND jh_cashierstation = '" . $request->station . "' ";
                $query .= "AND jh_cashierid = '" . $request->cashier . "' ";
                $cek = DB::select($query);

                if(count($cek) == 0){
                    return ApiFormatter::error(400, "Data Struk Tidak Ditemukan");
                }
            }

            $query = "";
            $query .= "UPDATE tbtr_obi_h ";
            $query .= "SET obi_recid = '6', ";
            $query .= "    obi_nostruk = '" . $request->no_struk . "', ";
            $query .= "    obi_tglstruk = TO_DATE('" . date('Y-m-d', strtotime($request->tanggal_struk)) . " " . $request->time_struk . "','YYYY-MM-DD HH24:MI:SS'), ";
            $query .= "    obi_tipe = 'S', ";
            $query .= "    obi_kdstation = '" . $request->station . "', ";
            $query .= "    obi_cashierid = '" . $request->cashier . "', ";
            $query .= "    obi_modifyby = '" . $request->cashier . "', ";
            $query .= "    obi_modifydt = TO_DATE('" . date('Y-m-d', strtotime($request->tanggal_struk)) . " " . $request->time_struk . "','YYYY-MM-DD HH24:MI:SS') ";
            $query .= "WHERE obi_notrans = '" . $request->no_trans . "' ";
            $query .= "AND DATE_TRUNC('DAY', obi_tgltrans) = TO_DATE('" . date('Y-m-d', strtotime($request->tanggal_trans)) . "','YYYY-MM-DD') ";
            $query .= "AND UPPER(obi_nopb) = UPPER('" . $request->nopb . "') ";
            $query .= "AND UPPER(obi_kdmember) = UPPER('" . $request->kode_member . "') ";
            $query .= "AND obi_recid = '5' ";

            DB::update($query);

            return ApiFormatter::success(200, "Berhasil Update Data Struk");


        } catch(\Exception $e){

            DB::rollBack();

            $message = "Gagal Update Data Struk";
            return ApiFormatter::error(400, $message);
        }

        //* Validasi Struk untuk Transaksi No." & dgv_notrans & "?

        //* nanti ada login manager dengan form -> frmPassword

        //* open form -> frmValidasiStrukKlik
    }

    public function actionF7(Request $request){
        if($request->status == 'Siap Picking' OR $request->status == $request->statusSiapPacking){
            $data = $this->rptJalurKertasPerishable($request->tanggal_trans, $request->no_trans, $request->nopb, $request->kode_member);
            if($data["status"] === false){
                return ApiFormatter::error(400, $data["message"]);
            }
            $pdf = PDF::loadView('pdf.rpt-jalur-kertas', $data['data']);
            return response($pdf->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="RPT-JALUR-KERTAS.pdf"');
        }else{
            return ApiFormatter::error(400, 'Bukan data yang dipicking/dipacking!');
        }
    }

    public function actionF8(Request $request){
        //* Cetak Laporan Penyusutan Harian?

        $data = $this->rptPenyusutanHarianPerishable($request->tanggal_trans);
        
        $data['request'] = $request;
        $pdf = PDF::loadView('pdf.rpt-penyusutan-harian', $data);    
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="RPT-PENYUSUTAN-HARIAN.pdf"');
    }

    public function actionF9(Request $request){
        if(session('flagSPI') == true AND session('flagIGR') == false){
            if($request->status == 'Siap Picking' OR $request->status == $request->status_siap_packing){
                $files_content = $this->rptPickingList999($request->tanggal_trans, $request->no_trans, $request->nopb, $request->kode_member, False);
                return ApiFormatter::success(200, 'Cetak Ulang Picking List ke Printer Thermal ?', $files_content);
            }else{
                return ApiFormatter::error(400, 'Bukan PB yang sedang dipicking / dipacking');
            }
        }else{
            return ApiFormatter::error(400, 'Khusus cabang SPI Picking DPD!');
        }
    }

    public function actionF10(Request $request){
        $nama_member = DB::table('tbmaster_customer')
            ->where('cus_kodemember', $request->kode_member)
            ->value(DB::raw('UPPER(cus_namamember)'));
        $request->merge(['nama_member' => $nama_member]);
        if(session('flagSPI') == true){
            if(($request->status == 'Siap Struk' AND $request->tipe_bayar == 'COD') OR ($request->status == 'Selesai Struk' AND $request->tipe_bayar != 'COD')){
                //* open FORM -> frmHitungUlangSPI
                $data['data'] = $this->actionF10Datatables(session('flagSPI'), $request->nopb, $request->no_trans, $request->kode_member, $request->tanggal_trans);
                $data['request'] = $request->all();
                return ApiFormatter::success(200, "Success", $data);
            }else{
                if (str_contains($request->status, 'Batal')) {
                    return ApiFormatter::error(400, 'Transaksi sudah dibatalkan!');
                }else{
                    if($request->tipe_bayar == 'COD'){
                        if($request->status == 'Selesai Struk'){
                            return ApiFormatter::error(400, 'Transaksi COD sudah distruk, Tidak dapat dihitung ulang!');
                        }else{
                            return ApiFormatter::error(400, 'Transaksi COD belum DSP, Belum dapat dihitung ulang!');
                        }
                    }else{
                        return ApiFormatter::error(400, 'Transaksi belum distruk, Belum dapat dihitung ulang!');
                    }
                }
            }
        }else{
            if($request->status == 'Siap Struk' AND $request->tipe_bayar == 'COD'){
                //* open FORM -> frmHitungUlangSPI
                $data['data'] = $this->actionF10Datatables(session('flagSPI'), $request->nopb, $request->no_trans, $request->kode_member, $request->tanggal_trans);
                $data['request'] = $request->all();
                return ApiFormatter::success(200, "Success", $data);
            }else{
                if($request->tipe_bayar == 'COD'){
                    if($request->status == 'Selesai Struk'){
                        return ApiFormatter::error(400, 'Transaksi COD sudah distruk, Tidak dapat dihitung ulang!');
                    }else{
                        return ApiFormatter::error(400, 'Transaksi COD belum DSP, Belum dapat dihitung ulang!');
                    }
                }else{
                    return ApiFormatter::error(400, 'Khusus Transaksi COD!');
                }
            }
        }
    }

    public function actionF10HitungUlang(Request $request){
        DB::beginTransaction(); 
        $selectedRow = $request->selectedRow;

        try {
            foreach ($request->datatables as $item){
                $prdcd = $item["plu"];
                $qtyBaru = $item["qtyinput"] * (session('flagSPI') ? 1 : $item["frac"]);
                $result = $this->updateQtyHitungUlang($request->tanggal_trans, $selectedRow["no_trans"], $prdcd, $qtyBaru);
                if(!$result){
                    throw new \Exception("Gagal Update Qty PLU " . $prdcd);
                }
            }

            if($request->tipe_bayar == "COD"){
                $query = "SELECT kode_promo ";
                $query .= "FROM promo_klikigr ";
                $query .= "WHERE kode_member = '" . $selectedRow["kode_member"] . "' ";
                $query .= "AND no_trans = '" . $selectedRow["no_trans"] . "' ";
                $query .= "AND no_pb = '" . $selectedRow["no_pb"] . "' ";

                $check = DB::select($query);
                if(count($check) > 0){
                    $query = "SELECT SUBSTR(obi_prdcd,1,6) || '0' PLU, ";
                    $query .= "       SUM(coalesce(obi_qtyorder,0)) orderr, ";
                    $query .= "       SUM(coalesce(obi_qty_hitungulang,0)) realisasi ";
                    $query .= "  FROM tbtr_obi_h h ";
                    $query .= "  JOIN tbtr_obi_d d ";
                    $query .= "    ON h.obi_notrans = d.obi_notrans ";
                    $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
                    $query .= " WHERE h.obi_kdmember = '" . $selectedRow["kode_member"] . "' "; 
                    $query .= "   AND h.obi_notrans = '" . $selectedRow["no_trans"] . "' "; 
                    $query .= "   AND h.obi_nopb = '" . $selectedRow["no_pb"] . "' "; 
                    $query .= "   AND d.obi_recid IS NULL ";
                    $query .= "   AND d.obi_qtyorder <> d.obi_qty_hitungulang ";
                    $query .= " GROUP BY SUBSTR(obi_prdcd,1,6) || '0' ";
                    $query .= " ORDER BY 1 ";

                    $check = DB::select($query);

                    if(count($check) > 0){
                        $splitTrans = explode("/", $selectedRow["no_pb"]);
                        $notrx = $splitTrans[0];
                        $transKlik = [];

                        $query = "SELECT SUBSTR(obi_prdcd,1,6) || '0' as PLU, ";
                        $query .= "       SUM(coalesce(obi_qtyorder,0)) as orderr, ";
                        $query .= "       SUM(coalesce(obi_qty_hitungulang,0)) as realisasi, ";
                        $query .= "       SUM( ";
                        $query .= "         ROUND(d.obi_hargaweb * COALESCE(obi_qtyorder,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ";
                        $query .= "         - ROUND(d.obi_diskon * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * COALESCE(obi_qtyorder,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END),-1) ";
                        $query .= "       ) as orderinrp, ";
                        $query .= "       SUM( ";
                        $query .= "         ROUND(d.obi_hargaweb * COALESCE(obi_qty_hitungulang,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ";
                        $query .= "         - ROUND(d.obi_diskon * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * COALESCE(obi_qty_hitungulang,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END),-1) ";
                        $query .= "       ) as realisasiiinrp ";
                        $query .= "  FROM tbtr_obi_h h ";
                        $query .= "  JOIN tbtr_obi_d d ";
                        $query .= "    ON h.obi_notrans = d.obi_notrans ";
                        $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
                        $query .= "  JOIN tbmaster_prodmast p ON p.prd_prdcd = d.obi_prdcd ";
                        $query .= " WHERE h.obi_kdmember = '" . $selectedRow["kode_member"] . "' ";
                        $query .= "   AND h.obi_notrans = '" . $selectedRow["no_trans"] . "' ";
                        $query .= "   AND h.obi_nopb = '" . $selectedRow["no_pb"] . "' ";
                        $query .= "   AND d.obi_recid IS NULL ";
                        $query .= " GROUP BY SUBSTR(obi_prdcd,1,6) || '0' ";
                        $query .= " ORDER BY 1 ";

                        $dt = DB::select($query);

                        foreach($dt as $item){
                            $transKlik[] = [
                                'PLU' => $item->PLU,
                                'orderr' => $item->orderr,
                                'realisasi' => $item->realisasi,
                                'orderinrp' => $item->orderinrp,
                                'realisasiiinrp' => $item->realisasiiinrp,
                            ];
                        }

                        $api = $this->requestPromo($transKlik, $selectedRow["kode_member"], $selectedRow["no_trans"], $selectedRow["no_pb"]);
                        if($api != true){
                            $message = 'Gagal Hitung Ulang Promosi Klik Indogrosir';
                            throw new HttpResponseException(ApiFormatter::error(400, $message));
                        }
                    }else {
                        $query = "UPDATE promo_klikigr ";
                        $query .= "SET cashback_hitungulang = cashback_order, ";
                        $query .= "    kelipatan_hitungulang = kelipatan, ";
                        $query .= "    reward_per_promo_hitungulang = reward_per_promo, ";
                        $query .= "    reward_nominal_hitungulang = reward_nominal ";
                        $query .= "WHERE kode_member = '" . $selectedRow["kode_member"] . "' ";
                        $query .= "  AND no_trans = '" . $selectedRow["no_trans"] . "' ";
                        $query .= "  AND no_pb = '" . $selectedRow["no_pb"] . "' ";
                        $query .= "  AND tipe_promo = 'CASHBACK' ";

                        // Execute the query
                        DB::update($query);
                    }
                }
            } else {
                $query = "UPDATE promo_klikigr ";
                $query .= "SET cashback_hitungulang = cashback_order, ";
                $query .= "    kelipatan_hitungulang = kelipatan, ";
                $query .= "    reward_per_promo_hitungulang = reward_per_promo, ";
                $query .= "    reward_nominal_hitungulang = reward_nominal ";
                $query .= "WHERE kode_member = '" . $selectedRow["kode_member"] . "' ";
                $query .= "  AND no_trans = '" . $selectedRow["no_trans"] . "' ";
                $query .= "  AND no_pb = '" . $selectedRow["no_pb"] . "' ";
                $query .= "  AND tipe_promo = 'CASHBACK' ";
            }

            if(session("flagSPI")){
                $txtContent = $this->PrintNotaHitungUlangKlikSPI("HITUNGULANG", "SPI", $request->tipe_kredit, $request->selectedRow);
            } else {
                $txtContent = $this->PrintNotaHitungUlangKlikSPI("HITUNGULANG", "KlikIGR", $request->tipe_kredit, $request->selectedRow);
            };

            DB::commit();
            return ApiFormatter::success(200, "Proses Hitung Ulang Berhasil", $txtContent);
        } catch (QueryException $e) {
            DB::rollBack();
            return ApiFormatter::error(500, "Error Hitung Ulang");
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiFormatter::error(400, $e->getMessage());
        }
    }

    private function PrintNotaHitungUlangKlikSPI($judul, $type, $flagKredit, $selectedRow){

        $count = DB::select("SELECT MAX(COALESCE(prs_nilaippn,0)/100) FROM tbmaster_perusahaan");
        $ppnRate = $count[0]->max;
        $nominal_voucher = $this->getNominalVoucher($selectedRow["no_trans"], $selectedRow["kode_member"]);

        if(count($count) == 0){
            throw new \Exception("Error executing query Nilai PPN");
        }

        if($flagKredit == "Y"){
            $fk = "K";
        } else {
            $fk = "T";
        }

        $sql = "";
        $sql .= "SELECT hdr.obi_tglpb obi_tglpb, ";
        $sql .= "       hdr.obi_nopb obi_nopb, ";
        $sql .= "       obi_kdmember, ";
        $sql .= "       obi_prdcd, ";
        $sql .= "       prd_deskripsipendek, ";
        $sql .= "       hdr.obi_notrans obi_notrans,  ";
        $sql .= "       hdr.obi_tgltrans obi_tgltrans,  ";
        $sql .= "       obi_realorder, ";
        $sql .= "       obi_realppn, ";
        $sql .= "       obi_realdiskon, ";
        $sql .= "       (obi_ekspedisi) obi_ekspedisi, ";
        $sql .= "       obi_qtyorder, ";
        $sql .= "       obi_qty_hitungulang obi_qtyrealisasi, ";
        $sql .= "       obi_hargasatuan, ";
        $sql .= "       obi_ppn, ";
        $sql .= "       ROUND(obi_hargasatuan + obi_ppn) obi_hargafix, ";
        $sql .= "       COALESCE(obi_hargaweb, ROUND((obi_hargasatuan + obi_ppn) * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), 0)) obi_hargaweb, ";
        $sql .= "       obi_diskon,  ";
        $sql .= "       obi_nostruk, ";
        $sql .= "       obi_tglstruk,  ";
        $sql .= "       obi_kdstation,  ";
        $sql .= "       obi_kdmember, ";
        $sql .= "       hdr.obi_createby obi_createby,  ";
        $sql .= "       COALESCE(hdr.obi_nopo,'-') nopo, ";
        $sql .= "       hdr.obi_realcashback realcashback,  ";
        $sql .= "       dtl.obi_kd_promosi kdpromo,  ";
        $sql .= "       dtl.obi_cashback nominal_csb,  ";
        $sql .= "       (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) prd_frac,  ";
        $sql .= "       (COALESCE(prd_ppn,0)/100) prd_ppn,  ";
        $sql .= "       COALESCE(prd_flagbkp1, 'N') || COALESCE(prd_flagbkp2, 'N') plubkp, ";
        $sql .= "       COALESCE(pobi_nocontainer, '-') nocon,  ";
        $sql .= "       dtl.obi_scan_dt obi_scan_dt, ";
        $sql .= "       COALESCE(amm_namapenerima, '-') amm_namapenerima,  ";
        $sql .= "       obi_cashierid,  ";
        $sql .= "       COALESCE(hdr.obi_pointbasic, 0) point_basic, ";
        $sql .= "       COALESCE(hdr.obi_pointbonus, 0) point_bonus, ";
        $sql .= "       COALESCE(obi_tipebayar,'x') tipe_bayar, ";
        $sql .= "       ROUND(sat_gram / COALESCE(sat_pcs,1)) konversi, ";
        $sql .= "       TO_CHAR(COALESCE(obi_draftstruk,CURRENT_TIMESTAMP),'DD-MM-YYYY HH24:MI:SS') tgldsp ";
        $sql .= "FROM tbtr_obi_h hdr ";
        $sql .= "JOIN tbtr_obi_d dtl ";
        $sql .= "  ON hdr.obi_notrans = dtl.obi_notrans ";
        $sql .= " AND hdr.obi_tgltrans = dtl.obi_tgltrans ";
        $sql .= "JOIN tbtr_alamat_mm ";
        $sql .= "  ON hdr.obi_notrans = amm_notrans ";
        $sql .= " AND hdr.obi_nopb = amm_nopb ";
        $sql .= " AND hdr.obi_kdmember = amm_kodemember ";
        $sql .= "JOIN tbmaster_prodmast ";
        $sql .= "  ON hdr.obi_kodeigr = prd_kodeigr ";
        $sql .= " AND dtl.obi_prdcd = prd_prdcd   ";
        $sql .= "LEFT JOIN tbtr_packing_obi ";
        $sql .= "  ON dtl.obi_notrans = pobi_notransaksi ";
        $sql .= " AND dtl.obi_tgltrans = pobi_tgltransaksi ";
        $sql .= " AND dtl.obi_prdcd = pobi_prdcd ";
        $sql .= "LEFT JOIN konversi_item_klikigr knv ";
        $sql .= "  ON SUBSTR(dtl.obi_prdcd,1,6) || '0' = SUBSTR(knv.pluigr,1,6) || '0' ";
        $sql .= "WHERE hdr.obi_tglpb = TO_DATE('" . date('d-m-Y', strtotime($selectedRow["tgl_pb"])) . "','DD-MM-YYYY')  ";
        $sql .= "AND hdr.obi_nopb = '" . $selectedRow["no_pb"] . "'  ";
        $sql .= "AND hdr.obi_kdmember = '" . $selectedRow["kode_member"] . "'  ";
        $sql .= "AND hdr.obi_notrans = '" . $selectedRow["no_trans"] . "' ";
        $sql .= "AND COALESCE(obi_qty_hitungulang,0) > 0 ";
        $sql .= "AND dtl.obi_recid IS NULL ";
        $sql .= "ORDER BY pobi_nocontainer ASC, dtl.obi_scan_dt DESC, dtl.obi_prdcd ASC ";

        $dt = DB::select($sql);

        if(count($dt) <= 0){
            throw new \Exception("Data Hitung Ulang Tidak Ditemukan");
        }

        if($type == "SPI"){
            $fracbarang = 0;
        }
        $hargabarang = $qtybarang = $diskonbarang = $cashbackbarang = $ppnRateBarang = $hargajual = $dppbarang = $ppnbarang = $ttlPPN = $nonPPN = $cukai = 0;
        $itemPPN = $itemNonPPN = $itemCukai = $itemBBS = $itemDTP = 0;
        $totaldiskon = $total = $counter = $andahemat = $dppAll = $ppnAll = $dppTemp = $ttlDPPBBS = $ttlPPNBBS = $dppTemp2 = $ttlDPPDTP = $ttlPPNDTP = $dppTemp3 = $tempPotGab = $ttlCashback = $adminfee = 0;
        $tgl = Carbon::parse($dt[0]->obi_tgltrans);
        $notrans = $dt[0]->obi_notrans;
        $nocon = '';

        $splitTrans = explode("/", $dt[0]->obi_nopb);
        $notrx = $splitTrans[0];

        //* KUPON
        $sql = "";
        $sql .= "SELECT COALESCE(SUM(COALESCE(nilai_kupon,0)),0) NILAI_KUPON ";
        $sql .= "FROM kupon_klikigr ";
        $sql .= "WHERE kode_member = '" . $selectedRow["kode_member"] . "' ";
        $sql .= "  AND no_trans = '" . $selectedRow["no_trans"] . "' ";
        $sql .= "  AND no_pb = '" . $selectedRow["no_pb"] . "' ";
        $dtKupon = DB::select($sql);

        //* CASHBACK LANGSUNG
        $sql = "";
        $sql .= "SELECT COALESCE(SUBSTR(ch.cbh_namapromosi, 1, 20), h.kode_promo) NAMA, ";
        $sql .= "       h.prdcd PRDCD, ";
        $sql .= "       h.kelipatan_hitungulang KELIPATAN, ";
        $sql .= "       h.reward_per_promo_hitungulang REWARD_PER_PROMO, ";
        $sql .= "       h.cashback_hitungulang REWARD, ";
        $sql .= "       0 flag ";
        $sql .= "FROM promo_klikigr h ";
        $sql .= "LEFT JOIN tbtr_cashback_hdr ch ";
        $sql .= "ON h.kode_promo = ch.cbh_kodepromosi ";
        $sql .= "WHERE h.tipe_promo = 'CASHBACK' ";
        $sql .= "AND h.id_tipe = '0' ";
        $sql .= "AND COALESCE(h.cashback_hitungulang,0) > 0 ";
        $sql .= "AND h.kode_member = '" . $selectedRow["kode_member"] . "' ";
        $sql .= "AND h.no_trans = '" . $selectedRow["no_trans"] . "' ";
        $sql .= "AND h.no_pb = '" . $selectedRow["no_pb"] . "' ";
        $dtCashback = DB::select($sql);

        //* CASHBACK GABUNGAN
        $sql = "";
        $sql .= "SELECT COALESCE(SUBSTR(ch.cbh_namapromosi, 1, 20), h.kode_promo) NAMA, ";
        $sql .= "       h.cashback_hitungulang REWARD ";
        $sql .= "FROM promo_klikigr h ";
        $sql .= "LEFT JOIN tbtr_cashback_hdr ch ";
        $sql .= "ON h.kode_promo = ch.cbh_kodepromosi ";
        $sql .= "WHERE h.tipe_promo = 'CASHBACK' ";
        $sql .= "AND h.id_tipe = '1' ";
        $sql .= "AND COALESCE(h.cashback_hitungulang,0) > 0 ";
        $sql .= "AND h.kode_member = '" . $selectedRow["kode_member"] . "' ";
        $sql .= "AND h.no_trans = '" . $selectedRow["no_trans"] . "' ";
        $sql .= "AND h.no_pb = '" . $selectedRow["no_pb"] . "' ";
        $dtGabungan = DB::select($sql);

        //* GIFT
        $sql = "";
        $sql .= "SELECT COALESCE(gh.gfh_namapromosi, h.kode_promo) KODE, ";
        $sql .= "       h.id_tipe TIPE, ";
        $sql .= "       h.gift_real GIFT ";
        $sql .= "FROM promo_klikigr h ";
        $sql .= "LEFT JOIN tbtr_gift_hdr gh ";
        $sql .= "ON h.kode_promo = gh.gfh_kodepromosi ";
        $sql .= "WHERE h.tipe_promo = 'GIFT' ";
        $sql .= "AND COALESCE(h.reward_nominal,0) > 0 ";
        $sql .= "AND h.kode_member = '" . $selectedRow["kode_member"] . "' ";
        $sql .= "AND h.no_trans = '" . $selectedRow["no_trans"] . "' ";
        $sql .= "AND h.no_pb = '" . $selectedRow["no_pb"] . "' ";
        $sql .= "ORDER BY h.id_tipe DESC, h.kode_promo ASC";
        $dtGift = DB::select($sql);

        //* POIN
        $sql = "";
        $sql .= "SELECT h.kode_promo KODE, ";
        $sql .= "       h.id_tipe TIPE, ";
        $sql .= "       COALESCE(h.gift_real, h.gift_order) GIFT ";
        $sql .= "FROM promo_klikigr h ";
        $sql .= "LEFT JOIN tbtr_gift_hdr gh ";
        $sql .= "ON h.kode_promo = gh.gfh_kodepromosi ";
        $sql .= "WHERE UPPER(h.tipe_promo) LIKE '%POIN%' ";
        $sql .= "AND COALESCE(h.reward_nominal, 0) > 0 ";
        //$sql .= "AND h.gift_real IS NOT NULL ";
        $sql .= "AND h.kode_member = '" . $selectedRow["kode_member"] . "' ";
        $sql .= "AND h.no_trans = '" . $selectedRow["no_trans"] . "' ";
        $sql .= "AND h.no_pb = '" . $selectedRow["no_pb"] . "' ";
        $sql .= "ORDER BY h.id_tipe DESC, h.kode_promo ASC";
        $dtPoin = DB::select($sql);

        //* PAYMENT
        $sql = "";
        $sql .= "SELECT tipe_bayar, total, admin_fee ";
        $sql .= "FROM payment_klikigr ";
        $sql .= "WHERE kode_member = '" . $selectedRow["kode_member"] . "' ";
        $sql .= "AND no_pb = '" . $selectedRow["no_pb"] . "' ";
        $sql .= "AND no_trans = '" . $selectedRow["no_trans"] . "' ";
        $sql .= "ORDER BY id_bayar";
        $dtPayment = DB::select($sql);

        //* DSP
        $sql = "";
        $sql .= "SELECT dsp_totalbayar ";
        $sql .= "FROM tbtr_dsp_spi ";
        $sql .= "WHERE dsp_nopb = '" . $selectedRow["no_pb"] . "' ";
        $sql .= "AND dsp_notrans = '" . $selectedRow["no_trans"] . "' ";
        $sql .= "AND dsp_kodemember = '" . $selectedRow["kode_member"] . "'";
        $dtDSP = DB::select($sql);

        $splitTrans = explode("/", $dt[0]->obi_nopb);
        
        //* TXT 
        $str = "";
        $str .= "\n";
        $str .= "          HITUNG ULANG DSP/SP           " . PHP_EOL;
        $str .= "========================================" . PHP_EOL;
        $str .= str_pad("", 22, " ") . "No.PO  :" . $dt[0]->nopo . PHP_EOL;
        $str .= str_pad("", 22, " ") . "Trx Id :TRX" . $splitTrans[0] . PHP_EOL;
        $str .= "Tgl.DSP :" . $dt[0]->tgldsp . PHP_EOL;
        $str .= "----------------------------------------" . PHP_EOL;
        $str .= "No. NAMA BARANG / PLU                   " . PHP_EOL;
        if($type == "SPI"){
            $str .= "   QTY/FRAC   H.SATUAN    DISC.    TOTAL" . PHP_EOL;
        }else {
            $str .= "    QTY    H.SATUAN       DISC.    TOTAL" . PHP_EOL;
        }
        $str .= "========================================" . PHP_EOL;

        foreach ($dt as $key => $row) {
            $qtybarang = $row->obi_qtyrealisasi / $row->prd_frac;
            if($type == "SPI"){
                $row->prd_frac;
                $fracbarang = $row->prd_frac;
                $prd_prdcd = substr($row->obi_prdcd, 0, 6) . '0';
                $query = "SELECT COALESCE(prd_frac, 1) FROM tbmaster_prodmast WHERE prd_prdcd LIKE '$prd_prdcd'";
                $check = DB::select($query);
                if(count($check)){
                    $fracbarang = $check[0]->coalesce;
                }
            }

            $hargabarang = round($row->obi_hargaweb, 2);
            $diskonbarang = round($row->obi_diskon * $row->prd_frac, 0);
            $ppnRateBarang = $row->prd_ppn;
            $hargajual += round($qtybarang * $hargabarang, 0);
            $totaldiskon += $qtybarang * $diskonbarang;
            $andahemat += $qtybarang * $diskonbarang;

            if ($nocon != $row->nocon) {
                if ($counter != 0) $str .= PHP_EOL;
                $counter = 0;
                $nocon = $row->nocon;
                $str .= "No.Container : " . $nocon . PHP_EOL;
            }

            $str .= str_pad(($counter + 1), 2, " ", STR_PAD_LEFT) . " ";
            $str .= str_pad(substr($row->prd_deskripsipendek, 0, 24), 24, " ") . "(" . str_pad($row->obi_prdcd, 7, " ") . ")";

            $resultCheckPPN = $this->checkPPN($row->plubkp);
            $dtPPN = $resultCheckPPN['dtPPN'];
            if ($resultCheckPPN['response'] !== "OK") {
                throw new \Exception("Gagal Buat Struk");
            }

            if ($dtPPN[0]->status == "KENA PPN") {
                $str .= "    ";
                $ttlPPN += ($qtybarang * $hargabarang) - ($qtybarang * $diskonbarang);
                $dppTemp = ($qtybarang * $hargabarang) - ($qtybarang * $diskonbarang);
                $itemPPN += 1;
            } elseif ($dtPPN[0]->status == "BEBAS PPN") {
                if (session("flagFTZ")) $str .= "****";
                $dppTemp2 = ($qtybarang * $hargabarang) - ($qtybarang * $diskonbarang);
                $itemBBS += 1;
            } elseif ($dtPPN[0]->status == "PPN DTP") {
                if (session("flagFTZ")) $str .= "*** ";
                $dppTemp3 = ($qtybarang * $hargabarang) - ($qtybarang * $diskonbarang);
                $itemDTP += 1;
            } elseif ($dtPPN[0]->status == "CUKAI") {
                if (session("flagFTZ")) $str .= "**  ";
                $cukai += ($qtybarang * $hargabarang) - ($qtybarang * $diskonbarang);
                $itemCukai += 1;
            } else {
                if (session("flagFTZ")) $str .= "*   ";
                $nonPPN += ($qtybarang * $hargabarang) - ($qtybarang * $diskonbarang);
                $itemNonPPN += 1;
            }
            $str .= PHP_EOL;

            // Qty - Harga Satuan - Qty * Harga Satuan
            if($type == "SPI"){
                $str .= str_pad(number_format($qtybarang, 0), 6, " ", STR_PAD_LEFT) . "/";
                $str .= str_pad(number_format($fracbarang, 0), 3, " ", STR_PAD_RIGHT);
                $str .= str_pad(number_format($hargabarang, 0), 9, " ", STR_PAD_LEFT);
            }else {
                $str .= str_pad(number_format($qtybarang, 0), 7, " ", STR_PAD_LEFT);
                $str .= str_pad(number_format($hargabarang, 0), 12, " ", STR_PAD_LEFT);
            }

            $str .= str_pad(number_format($qtybarang * $hargabarang, 0), 21, " ");
            $str .= PHP_EOL;

            // Pot.Member --> Potongan MD
            if ($diskonbarang > 0) {
                $str .= "   Pot. Member : ";
                $str .= str_pad(number_format($qtybarang, 0), 3, " ", STR_PAD_LEFT) . " X ";
                $str .= "-" . str_pad(number_format($diskonbarang, 0), 8, " ", STR_PAD_LEFT);
                $str .= "-" . str_pad(number_format($diskonbarang * $qtybarang, 0), 9, " ", STR_PAD_LEFT);
                $str .= PHP_EOL;
            }


            if (count($dtCashback) > 0) {
                $tempPLU = $dt[$key]->obi_prdcd;
                $tempPLU = substr($tempPLU, 0, 6);
                foreach ($dtCashback as $row) {
                    if (strpos($row->nama, $tempPLU) !== false) {
                        if ($row->flag == 0) {
                            $str .= "   Potongan    : " . 
                                str_pad(number_format($row->kelipatan, 0), 3, " ", STR_PAD_LEFT) . " X " . 
                                "-" . str_pad(number_format($row->reward_per_promo, 0), 8, " ", STR_PAD_LEFT) . 
                                "-" . str_pad(number_format($row->reward, 0), 9, " ", STR_PAD_LEFT);
                            $str .= PHP_EOL;
                            $row->flag = 1;

                            if ($dtPPN[0]->status == "KENA PPN") {
                                $cashbackbarang += $row->reward;
                                $dppTemp -= $row->reward;
                            } elseif ($dtPPN[0]->status == "BEBAS PPN") {
                                $dppTemp2 -= $row->reward;
                            } elseif ($dtPPN[0]->status == "PPN DTP") {
                                $dppTemp3 -= $row->reward;
                            } elseif ($dtPPN[0]->status == "CUKAI") {
                                $cukai -= $row->reward;
                            } else {
                                $nonPPN -= $row->reward;
                            }
                            $ttlCashback += $row->reward;
                            $andahemat += $row->reward;
                        }
                    }
                }
            }

            if ($dtPPN[0]->status == "KENA PPN") {
                $dppbarang += round($dppTemp / (1 + $ppnRateBarang), 0, PHP_ROUND_HALF_UP);
            } elseif ($dtPPN[0]->status == "BEBAS PPN") {
                $ttlDPPBBS += round($dppTemp2, 0, PHP_ROUND_HALF_UP);
            } elseif ($dtPPN[0]->status == "PPN DTP") {
                $ttlDPPDTP += round($dppTemp3, 0, PHP_ROUND_HALF_UP);
            }

            $counter += 1;
        }

        if (count($dtGabungan) > 0) {
            $str .= "========================================" . PHP_EOL;
            foreach ($dtGabungan as $row) {
                $str .= "   Potongan " . $row->nama . str_repeat(" ", 32 - strlen("   Potongan " . $row->nama)) . 
                    " -" . number_format($row->reward, 0) . str_repeat(" ", 8 - strlen(number_format($row->reward, 0))) . PHP_EOL;
                $cashbackbarang += $row->reward;
                $ttlCashback += $row->reward;
                $andahemat += $row->reward;

                $tempPotGab += $row->reward;
            }
        }


        $total = round($hargajual - $totaldiskon, 0, PHP_ROUND_HALF_UP);
        $hargajual -= $andahemat;
        $ttlPPN -= $cashbackbarang;
        $ttlPPNBBS = round($ttlDPPBBS * $ppnRateBarang, 0, PHP_ROUND_HALF_UP);
        $ttlPPNDTP = round($ttlDPPDTP * $ppnRateBarang, 0, PHP_ROUND_HALF_UP);

        $adminfee = 0;
        if (!empty($dtPayment)) {
            foreach ($dtPayment as $row) {
                $adminfee += $row->admin_fee;
            }
        }

        $ttlPPN += $dt[0]->obi_ekspedisi + $adminfee;
        $dppbarang += round(($dt[0]->obi_ekspedisi + $adminfee) / (1 + $ppnRate), 0, PHP_ROUND_HALF_UP);
        $ppnbarang = round($dppbarang * $ppnRateBarang, 0, PHP_ROUND_HALF_UP);

        if ($tempPotGab > 0) {
            $temp = 0;
            $tempTotalDPPPPN = $dppbarang + $ppnbarang;

            if ($tempTotalDPPPPN > $tempPotGab) {
                $temp = $tempPotGab;
                $tempPotGab -= $temp;
                $tempTotalDPPPPN -= $temp;

                $dppbarang = round($tempTotalDPPPPN / (1 + $ppnRateBarang), 0, PHP_ROUND_HALF_UP);
                $ppnbarang = round($tempTotalDPPPPN - $dppbarang, 0, PHP_ROUND_HALF_UP);
            } elseif ($tempTotalDPPPPN < $tempPotGab) {
                $temp = $tempTotalDPPPPN;
                $tempPotGab -= $temp;
                $dppbarang -= $temp;
                $ppnbarang = 0;
            }

            if ($ttlDPPBBS > $tempPotGab && $tempPotGab > 0) {
                $temp = $tempPotGab;
                $tempPotGab -= $temp;
                $ttlDPPBBS -= $temp;
                $ttlPPNBBS = $ttlDPPBBS * $ppnRateBarang;
            } elseif ($ttlDPPBBS < $tempPotGab && $tempPotGab > 0) {
                $temp = $ttlDPPBBS;
                $tempPotGab -= $temp;
                $ttlDPPBBS -= $temp;
                $ttlPPNBBS = 0;
            }

            if ($ttlDPPDTP > $tempPotGab && $tempPotGab > 0) {
                $temp = $tempPotGab;
                $tempPotGab -= $temp;
                $ttlDPPDTP -= $temp;
                $ttlPPNDTP = $ttlDPPDTP * $ppnRateBarang;
            } elseif ($ttlDPPDTP < $tempPotGab && $tempPotGab > 0) {
                $temp = $ttlDPPDTP;
                $tempPotGab -= $temp;
                $ttlDPPDTP -= $temp;
            }
        }

        $dppAll = $dppbarang;
        $ppnAll = $ppnbarang;

        $ttlDPPBBS = round($ttlDPPBBS, 0, PHP_ROUND_HALF_UP);
        $ttlDPPDTP = round($ttlDPPDTP, 0, PHP_ROUND_HALF_UP);

        $nonPPN = round($nonPPN, 0, PHP_ROUND_HALF_UP);
        $cukai = round($cukai, 0, PHP_ROUND_HALF_UP);

        // JIKA MINUS
        $hargajual = $hargajual < 0 ? 0 : $hargajual;

        $str .= "========================================" . PHP_EOL;
        $str .= "HARGA JUAL..................:" . str_pad(number_format(round($hargajual, 0, PHP_ROUND_HALF_UP), 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
        $str .= "BIAYA LAYANAN...............:" . str_pad(number_format(round($dt[0]->obi_ekspedisi + $adminfee, 0, PHP_ROUND_HALF_UP), 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
        $str .= "                              __________" . PHP_EOL;
        $str .= PHP_EOL;

        $totalBelanja = $total
            - $dt[0]->realcashback
            + $dt[0]->obi_ekspedisi
            + $adminfee
            - $ttlCashback
            - (count($dtKupon) > 0 ? $dtKupon[0]->nilai_kupon : 0);

        // JIKA MINUS
        $totalBelanja = $totalBelanja < 0 ? 0 : $totalBelanja;
        $tipeBayar = strtoupper($dt[0]->tipe_bayar);
        $pembayaranNonPoin = 0;

        if (count($dtPayment) > 0 && strtoupper($tipeBayar) !== "TOP") {
            $totalBayar = 0;
            $str .= str_pad("PEMBAYARAN", 28, ".") . ":" . PHP_EOL;
            foreach ($dtPayment as $row) {
                $str .= str_pad(" -" . strtoupper($row->tipe_bayar), 28, ".") . ":" . str_pad(number_format($row->total, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                $totalBayar += $row->total;

                if (!strpos(strtoupper($row->tipe_bayar), "POIN")) {
                    $pembayaranNonPoin = $row->total;
                }
            }
        } else {
            $totalBayar = $totalBelanja;

            if ($nominal_voucher > $totalBelanja || $nominal_voucher === $totalBelanja) {
                $str .= "TOTAL YANG DIBAYAR..........:" . str_pad(number_format(0, 0), 11, " ") . PHP_EOL;
                if ($tipeBayar !== "X") {
                    $str .= str_pad("PEMBAYARAN " . $tipeBayar, 28, ".") . ":" . str_pad(number_format(0, 0), 11, " ") . PHP_EOL;
                }
            } elseif ($nominal_voucher < $totalBelanja) {
                $str .= "TOTAL YANG DIBAYAR..........:" . str_pad(number_format(($totalBelanja - $nominal_voucher), 0), 11, " ") . PHP_EOL;
                if ($tipeBayar !== "X") {
                    $str .= str_pad("PEMBAYARAN " . $tipeBayar, 28, ".") . ":" . str_pad(number_format(($totalBayar - $nominal_voucher), 0), 11, " ") . PHP_EOL;
                }
            } else {
                $str .= "TOTAL YANG DIBAYAR..........:" . str_pad(number_format($totalBelanja, 0), 11, " ") . PHP_EOL;
                if ($tipeBayar !== "X") {
                    $str .= str_pad("PEMBAYARAN " . $tipeBayar, 28, ".") . ":" . str_pad(number_format($totalBayar, 0), 11, " ") . PHP_EOL;
                }
            }
        }

        // Kembalian ke Saldo KlikIndogrosir
        $ttlKembalian = 0;
        $kembalianSaldo = 0;
        $kembalianPoin = 0;
        if ($totalBayar - $totalBelanja > 0) {
            $ttlKembalian = $totalBayar - $totalBelanja;
            if ($ttlKembalian > $pembayaranNonPoin) {
                $kembalianSaldo = $pembayaranNonPoin;
                $kembalianPoin = $ttlKembalian - $pembayaranNonPoin;
            } else {
                $kembalianSaldo = $ttlKembalian;
            }
            $str .= PHP_EOL;
            $str .= str_pad("KEMBALIAN ", 28, ".", STR_PAD_RIGHT) . ":" . str_pad(number_format($ttlKembalian, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
            // if ($kembalianSaldo > 0) $str .= str_pad(" -KE SALDO KLIK", 28, ".", STR_PAD_RIGHT) . ":" . str_pad(number_format($kembalianSaldo, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
            // if ($kembalianPoin > 0) $str .= str_pad(" -KE POIN", 28, ".", STR_PAD_RIGHT) . ":" . str_pad(number_format($kembalianPoin, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
        }
        $str .= PHP_EOL;
        $str .= "  ANDA HEMAT................:" . str_pad(number_format($andahemat, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
        $str .= PHP_EOL;

        if (count($dtDSP) > 0) {
            $totalDSP = $dtDSP[0]->dsp_totalbayar;
        } else {
            $totalDSP = $totalBayar;
        }
        $str .= "------------- HITUNG ULANG -------------" . PHP_EOL;
        $str .= "NILAI BARANG YG DIKEMBALIKAN:" . str_pad(number_format($totalDSP - $totalBayar, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
        $str .= "NILAI YG HARUS DIBAYAR......:" . str_pad(number_format($totalBayar, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
        $str .= "----------------------------------------" . PHP_EOL;
        $str .= PHP_EOL;

        // JIKA MINUS
        $dppAll = $dppAll < 0 ? 0 : $dppAll;
        $ppnAll = $ppnAll < 0 ? 0 : $ppnAll;
        $ttlDPPBBS = $ttlDPPBBS < 0 ? 0 : $ttlDPPBBS;
        $ttlPPNBBS = $ttlPPNBBS < 0 ? 0 : $ttlPPNBBS;
        $ttlDPPDTP = $ttlDPPDTP < 0 ? 0 : $ttlDPPDTP;
        $ttlPPNDTP = $ttlPPNDTP < 0 ? 0 : $ttlPPNDTP;
        $itemNonPPN = $itemNonPPN < 0 ? 0 : $itemNonPPN;
        $itemCukai = $itemCukai < 0 ? 0 : $itemCukai;
        $itemBBS = $itemBBS < 0 ? 0 : $itemBBS;
        $itemDTP = $itemDTP < 0 ? 0 : $itemDTP;

        if (!session("flagFTZ")) {
            if ($itemPPN > 0) {
                $str .= "      " . str_pad(number_format($itemPPN, 0), 4, " ", STR_PAD_LEFT);
                $str .= " Item PPN.........:" . str_pad(number_format($dppAll + $ppnAll, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                $str .= "         DPP=" . str_pad(number_format($dppAll, 0), 12, " ", STR_PAD_LEFT) . 
                        " PPN=" . str_pad(number_format($ppnAll, 0), 10, " ", STR_PAD_LEFT) . PHP_EOL;
            }

            if ($itemNonPPN > 0) {
                $str .= "    *:" . str_pad(number_format($itemNonPPN, 0), 4, " ", STR_PAD_LEFT);
                $str .= " Item Tanpa PPN...:" . str_pad(number_format($nonPPN, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
            }

            if ($itemCukai > 0) {
                $str .= "   **:" . str_pad(number_format($itemCukai, 0), 4, " ", STR_PAD_LEFT);
                $str .= " Item Kena Cukai..:" . str_pad(number_format($cukai, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
            }

            if ($itemDTP > 0) {
                $str .= "  ***:" . str_pad(number_format($itemDTP, 0), 4, " ", STR_PAD_LEFT);
                $str .= " Item PPN DTP.....:" . str_pad(number_format($ttlDPPDTP, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                $str .= "         DPP=" . str_pad(number_format($ttlDPPDTP, 0), 12, " ", STR_PAD_LEFT) . 
                        " PPN=" . str_pad(number_format($ttlPPNDTP, 0), 10, " ", STR_PAD_LEFT) . PHP_EOL;
            }

            if ($itemBBS > 0) {
                $str .= " ****:" . str_pad(number_format($itemBBS, 0), 4, " ", STR_PAD_LEFT);
                $str .= " Item PPN Bebas...:" . str_pad(number_format($ttlDPPBBS, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                $str .= "         DPP=" . str_pad(number_format($ttlDPPBBS, 0), 12, " ", STR_PAD_LEFT) . 
                        " PPN=" . str_pad(number_format($ttlPPNBBS, 0), 10, " ", STR_PAD_LEFT) . PHP_EOL;
            }
        }

        $str .= "             * TERIMA KASIH *           " . PHP_EOL;
        $str .= "Kode/Nama Member : " . $dt[0]->obi_kdmember . "/" . $dt[0]->amm_namapenerima . PHP_EOL;
        $str .= PHP_EOL;

        $str .= "Anda Memperoleh : " . PHP_EOL;
        $str .= " " . str_pad(number_format($dt[0]->point_basic, 0), 5, " ", STR_PAD_LEFT) . " Poin Igr. Basic" . PHP_EOL;
        $str .= " " . str_pad(number_format($dt[0]->point_bonus, 0), 5, " ", STR_PAD_LEFT) . " Poin Igr. Bonus" . PHP_EOL;

        if (count($dtPoin) > 0) {
            foreach ($dtPoin as $row) {
                $str .= "=>" . $row->gift . PHP_EOL;
            }
        }

        $str .= "========================================" . PHP_EOL;

        if (!is_null($dt[0]->obi_nostruk)) {
            $str .= str_pad("No.SP  :" . $dt[0]->obi_nostruk, 20, " ") . 
                    str_pad("Tgl.SP  :" . date("d-m-Y", strtotime($dt[0]->obi_tglstruk)), 20, " ") . PHP_EOL;
        }

        $str .= "Kode/Nama Member : " . $dt[0]->obi_kdmember . "/" . $dt[0]->amm_namapenerima . PHP_EOL;

        if (count($dtGift) > 0) {
            $str .= PHP_EOL;
            $str .= "Anda Memperoleh :" . PHP_EOL;
            foreach ($dtGift as $row) {
                $str .= $row->kode . PHP_EOL;
                $str .= "=>" . str_replace("GIFT berupa ", "", $row->gift) . PHP_EOL;
            }
        }

        $nama_file = "HITUNGULANG_" . $notrx .'.txt';

        return [
            "str" => $str,
            "nama_file" => $nama_file
        ];
    }

    private function updateQtyHitungUlang($tgltrans, $notrans, $prdcd, $qtyBaru){
        $query = "UPDATE tbtr_obi_d ";
        $query .= "SET obi_qty_hitungulang = " . $qtyBaru . " ";
        $query .= "WHERE obi_tgltrans = TO_DATE('" . date('d-m-Y', strtotime($tgltrans)) . "','DD-MM-YYYY') ";
        $query .= "AND obi_notrans = '" . $notrans . "' ";
        $query .= "WHERE obi_prdcd = '" . $prdcd . "' ";
        
        $affectedRows = DB::update($query);

        if ($affectedRows === 0) {
            return false; 
        }

        return true; 
    }

    private function actionF10Datatables($flagSPI, $nopb, $notrans, $kodemember, $tgltrans){
        $query = "SELECT ";
        $query .= "    obi_prdcd AS PLU, ";
        $query .= "    prd_deskripsipanjang AS DESKRIPSI, ";
        $query .= "    COALESCE(prd_frac, 1) AS FRAC, ";
        if ($flagSPI) {
            $query .= "    obi_qtyorder AS QTYPB, ";
            $query .= "    d.obi_qtyrealisasi AS QTYREAL ";
        } else {
            $query .= "    obi_qtyorder / COALESCE(prd_frac, 1) AS QTYPB, ";
            $query .= "    d.obi_qtyrealisasi / COALESCE(prd_frac, 1) AS QTYREAL ";
        }
        $query .= "FROM tbtr_obi_h AS h ";
        $query .= "JOIN tbtr_obi_d AS d ON d.obi_tgltrans = h.obi_tgltrans ";
        $query .= "AND d.obi_notrans = h.obi_notrans ";
        $query .= "JOIN tbmaster_prodmast ON prd_prdcd = obi_prdcd ";
        $query .= "WHERE h.obi_nopb = '" . $nopb . "' ";
        $query .= "AND h.obi_notrans = '" . $notrans . "' ";
        $query .= "AND h.obi_kdmember = '" . $kodemember . "' ";
        $query .= "AND TO_CHAR(h.obi_tgltrans, 'YYYY-MM-DD') = '" . date('Y-m-d', strtotime($tgltrans)) . "' ";
        $query .= "AND d.obi_recid IS NULL ";
        $query .= "AND d.obi_qtyrealisasi > 0 ";
        $query .= "ORDER BY prd_deskripsipanjang LIMIT 25";

        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
        
    }

    public function actionF12(Request $request){
        if($request->status == 'Konfirmasi Pembayaran' OR $request->status == 'Siap Struk'){
            $query = '';
            $query .= "SELECT * FROM TBTR_TRANSAKSI_VA ";
            $query .= "WHERE TVA_TRXID = '" . substr($request->nopb, 0, 6) . "' ";
            $query .= "    AND TVA_TGLPB = '" . Carbon::parse($request->tanggal_pb)->format('Y-m-d H:i:s') . "' ";
            $query .= "    AND COALESCE(TVA_BANK, 'BANG') != 'BANG' ";

            $data = DB::select($query);

            //! Kalo transaksi COD-VA tidak bisa batal DSP
            if(!count($data)){
                //* nanti ada login manager dengan form -> frmPassword
                if( json_decode($request->pass_password_manager) === false){
                    return ApiFormatter::success(201, "Edit Transaksi No." . $request->no_trans . " yang sudah keluar DSP?", "isManager");
                }
                $this->batalDSP($request->tanggal_trans, $request->no_trans, $request->nopb, $request->kode_member);

                return ApiFormatter::success(200, 'DSP Berhasil Dibatalkan!');
            }
        }elseif($request->status == $request->status_siap_packing){
            //* nanti ada login manager dengan form -> frmPassword
            if(json_decode($request->pass_password_manager) === false){
                return ApiFormatter::success(201, "Kembali ke Proses Picking untuk Transaksi No." . $request->no_trans . "?", "isAdmin");
            }

            $this->ulangPicking($request->no_trans, $request->nopb);

            return ApiFormatter::success(200, 'Proses Picking Transaksi ' . $request->no_trans);

        }else{
            return ApiFormatter::error(400, 'Bukan data yang bisa dibatalkan!');
        }
    }

    public function actionDelete(Request $request){
        $strHasil = "";
        $noPb = "";
        DB::beginTransaction(); 
        try {
            $query = "SELECT obi_nopb, SUBSTRING(COALESCE(obi_recid,'0'), -1, 1) AS obi_recid ";
            $query .= "FROM tbtr_obi_h AS h ";
            $query .= "WHERE SUBSTRING(COALESCE(obi_recid, '0'), -1, 1) IN ('4','5') ";
            $query .= "AND h.obi_notrans = '" . $request->no_trans . "' ";
            $query .= "AND h.obi_nopb = '" . $request->nopb . "' ";
            $query .= "AND DATE_FORMAT(h.obi_tgltrans, '%d-%m-%Y') = '" . $request->tanggal_trans . "' ";
            $query .= "AND UPPER(obi_attribute2) IN ('KLIKIGR', 'CORP', 'SPI') ";

            $check = DB::select($query);

            if(count($check) > 0){
                if($check[0]->obi_recid == "5"){
                    $tempHasil = "";
                    if(session("flagSPI")){
                        //hasil = cancelPickup_SPI(row.Cells(1).Value.ToString, row.Cells(2).Value.ToString, row.Cells(0).Value.ToString)
                    } else {
                        $tempHasil = $this->cancelPickup_KLIK($request->no_trans, $request->tanggal_trans, $request->nopb);
                    }
                    if ($tempHasil !== "SUCCESS") {
                        throw new \Exception("PB " . $request->nopb . " Gagal Cancel Pickup IPP");
                    }
                }

                //! PADA VB DICOMMENT 
                // BATAL SETELAH DRAFT STRUK (RECID 4) - KURANG INTRANSIT
                // sb = New StringBuilder
                // sb.AppendLine("MERGE INTO ( ")
                // sb.AppendLine("  SELECT * FROM tbmaster_stock ")
                // sb.AppendLine("  WHERE st_lokasi = '01' ")
                // sb.AppendLine("  AND EXISTS ( ")
                // sb.AppendLine("	   SELECT 1 ")
                // sb.AppendLine("	   FROM tbtr_obi_d ")
                // sb.AppendLine("	   WHERE SUBSTR(obi_prdcd, 1, 6) || '0' = st_prdcd ")
                // sb.AppendLine("	   AND obi_notrans = '" & row.Cells(1).Value.ToString & "' ")
                // sb.AppendLine("	   AND DATE_TRUNC('day', obi_tgltrans) = TO_DATE('" & row.Cells(2).Value.ToString & "','DD-MM-YYYY') ")
                // sb.AppendLine(") ")
                // sb.AppendLine(") t ")
                // sb.AppendLine("USING ( ")
                // sb.AppendLine("  SELECT SUBSTR(obi_prdcd, 1, 6) || '0' obi_prdcd, ")
                // sb.AppendLine("		    SUM (coalesce(obi_qtyrealisasi,0)) obi_qtyrealisasi ")
                // sb.AppendLine("  FROM tbtr_obi_d ")
                // sb.AppendLine("  WHERE obi_notrans = '" & row.Cells(1).Value.ToString & "' ")
                // sb.AppendLine("  AND DATE_TRUNC('day', obi_tgltrans) = TO_DATE('" & row.Cells(2).Value.ToString & "','DD-MM-YYYY')  ")
                // sb.AppendLine("  AND obi_recid is null ")
                // sb.AppendLine("  GROUP BY SUBSTR(obi_prdcd, 1, 6) || '0' ")
                // sb.AppendLine(") s ")
                // sb.AppendLine("ON ( ")
                // sb.AppendLine("  t.st_prdcd = s.obi_prdcd ")
                // sb.AppendLine(") ")
                // sb.AppendLine("WHEN MATCHED THEN ")
                // sb.AppendLine("  UPDATE SET t.st_intransit = t.st_intransit + s.obi_qtyrealisasi, ")
                // sb.AppendLine("             t.st_saldoakhir = COALESCE(t.st_saldoawal, 0)  ")
                // sb.AppendLine("		                          + COALESCE(t.st_trfin, 0)  ")
                // sb.AppendLine("						          - COALESCE(t.st_trfout, 0)  ")
                // sb.AppendLine("						          - COALESCE(t.st_sales, 0)  ")
                // sb.AppendLine("						          + COALESCE(t.st_retur, 0)  ")
                // sb.AppendLine("						          + COALESCE(t.st_adj, 0)  ")
                // sb.AppendLine("						          + COALESCE(t.st_selisih_so, 0)  ")
                // sb.AppendLine("						          + COALESCE(t.st_selisih_soic, 0)  ")
                // sb.AppendLine("						          + COALESCE(t.st_intransit, 0) ")
                // sb.AppendLine("						          + s.obi_qtyrealisasi, ")
                // sb.AppendLine("			 t.st_modify_by = '" & UserMODUL & "', ")
                // sb.AppendLine("			 t.st_modify_dt = NOW() ")

                $query = "UPDATE tbmaster_stock t ";
                $query .= "SET st_intransit = st_intransit + s.obi_qtyrealisasi, ";
                $query .= "    st_saldoakhir = COALESCE(st_saldoawal, 0) ";
                $query .= "                   + COALESCE(st_trfin, 0) ";
                $query .= "                   - COALESCE(st_trfout, 0) ";
                $query .= "                   - COALESCE(st_sales, 0) ";
                $query .= "                   + COALESCE(st_retur, 0) ";
                $query .= "                   + COALESCE(st_adj, 0) ";
                $query .= "                   + COALESCE(st_selisih_so, 0) ";
                $query .= "                   + COALESCE(st_selisih_soic, 0) ";
                $query .= "                   + COALESCE(st_intransit, 0) ";
                $query .= "                   + s.obi_qtyrealisasi, ";
                $query .= "    st_modify_by = '" . session("userid") . "', ";
                $query .= "    st_modify_dt = NOW() ";
                $query .= "FROM ( ";
                $query .= "        SELECT SUBSTR(obi_prdcd, 1, 6) || '0' obi_prdcd, ";
                $query .= "               SUM (COALESCE(obi_qtyrealisasi, 0)) obi_qtyrealisasi ";
                $query .= "        FROM tbtr_obi_d ";
                $query .= "        WHERE obi_notrans = '" . $request->no_trans . "' ";
                $query .= "          AND DATE_TRUNC('DAY', obi_tgltrans) = TO_DATE('" . $request->tanggal_trans . "', 'DD-MM-YYYY') ";
                $query .= "          AND obi_recid IS NULL ";
                $query .= "        GROUP BY SUBSTR(obi_prdcd, 1, 6) || '0' ";
                $query .= ") s ";
                $query .= "WHERE t.st_prdcd = s.obi_prdcd ";
                $query .= "AND t.st_lokasi = '01'";

                DB::update($query);

                $query = "UPDATE tbtr_obi_d ";
                $query .= "SET obi_qtyintransit = 0 ";
                $query .= "WHERE obi_notrans = '" . $request->no_trans . "' ";
                $query .= "AND DATE_TRUNC('DAY', obi_tgltrans) = TO_DATE('" . $request->tanggal_trans . "', 'DD-MM-YYYY') ";
                $query .= "AND obi_recid IS NULL";

                DB::update($query);
            }

            $splitTrans = explode('/', $request->nopb);

            $notrx = $splitTrans[0];

            $query = "INSERT INTO log_obi_status ( ";
            $query .= "  notrans, ";
            $query .= "  tgltrans, ";
            $query .= "  nopb, ";
            $query .= "  notrx_klik, ";
            $query .= "  status_baru, ";
            $query .= "  flag, ";
            $query .= "  create_by, ";
            $query .= "  create_dt ";
            $query .= ") VALUES ( ";
            $query .= "  '" . $request->no_trans . "', ";
            $query .= "  TO_DATE('" . $request->tanggal_trans . "','DD-MM-YYYY'), ";
            $query .= "  '" . $request->nopb . "', ";
            $query .= "  '" . $notrx . "', ";
            $query .= "  'B', ";
            $query .= "  0, ";
            $query .= "  '" . session("userid") . "', ";
            $query .= "  NOW() ";
            $query .= ") ";

            DB::insert($query);

            if(session("flagSPI")){
                //hasil = cancelPickup_SPI(row.Cells(1).Value.ToString, row.Cells(2).Value.ToString, row.Cells(0).Value.ToString)
            } else {
                $tempHasil = $this->alasanbatalKlik($request->no_trans, $request->tanggal_trans, $request->nopb);
            }

            if ($tempHasil !== "SUCCESS") {
                throw new \Exception("PB " . $request->nopb . " Gagal Update Alasan Batal.");
            }

            $sql = "UPDATE TBTR_OBI_H SET obi_recid = CONCAT('B', COALESCE(obi_recid, '')), obi_alasanbtl = '" . $request->alasanValue . "' ";
            $sql .= " WHERE obi_notrans = '" . $request->no_trans . "'";
            $sql .= " AND obi_nopb = '" . $request->nopb . "'";
            $sql .= " AND DATE_TRUNC('DAY', obi_tgltrans) = TO_DATE('" . $request->tanggal_trans . "', 'DD-MM-YYYY')";
            $hasil = DB::update($sql);

            if ($hasil) {
                $strHasil .= "1";
            } else {
                $strHasil .= "0";
                $noPb .= "- " . $request->nopb . " ,";
            }


            $this->logUpdateStatus($request->no_trans, $request->tanggal_trans, $request->nopb, "B", "9");

            if (strpos($strHasil, "0") !== false) {
                throw new \Exception("Ada PB Yang Gagal Dibatalkan. \n" . $noPb);
            }

            DB::commit();

            return ApiFormatter::success(200, "PB Berhasil Dibatalkan");
        } catch (QueryException $e) {
            DB::rollBack();
            return ApiFormatter::error(500, "Error Pembatalan PB");
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiFormatter::error(400, $e->getMessage());
        }
    }

    public function getAlasanPembatalanPB(){
        $query = "SELECT sb_no no, sb_alasan alasan FROM serba_batal ORDER BY sb_no";
        $data = DB::select($query);
        return ApiFormatter::success(200, "success", $data);
    }

    //* btnSendJalur_Click
    public function actionSendHandHelt(Request $request){
        //* Send Jalur No Trans = " & dgv_notrans & " Ini?
        //! dgv_notrans tidak boleh null atau '';

        // if($request->status != $request->statusSiapPicking){
        //     return ApiFormatter::error(400, 'Bukan Data Yang Siap Send Jalur!');
        // }

        $cbPickRakTokoVisible = $request->pickRakToko == 1 ? true : false;

        if(session('flagSPI') == true AND session('flagIGR') == false){
            if (str_contains(session('flagHHSPI'), 'H') AND str_contains(session('flagHHSPI'), 'D') ) {
                //? kalo susah form ini tampil diawal aja karena cuma buat dapet variable $pilihan
                //! open form -> frmOpsiPickSPI

                if($request->pilihan == 1){
                    $this->sendSPI($request->nopb, $request->no_trans, $request->kode_member, $request->tanggal_trans);
                }elseif($request->pilihan == 2){
                    $this->sendHH($request->nopb, $request->no_trans, $request->kode_member, $request->tanggal_trans, $request->pickRakToko);
                }else{
                    return ApiFormatter::error(400, 'Send Jalur dibatalkan!');
                }
            }elseif(str_contains(session('flagHHSPI'), 'H') AND !str_contains(session('flagHHSPI'), 'D')){
                $this->sendHH($request->nopb, $request->no_trans, $request->kode_member, $request->tanggal_trans, $request->pickRakToko);
            }else{
                $this->sendSPI($request->nopb, $request->no_trans, $request->kode_member, $request->tanggal_trans);
            }
        }else{
            $this->sendHH($request->nopb, $request->no_trans, $request->kode_member, $request->tanggal_trans, $request->pickRakToko);
        }
    }

    //* btnOngkir_Click
    public function actionOngkosKirim(Request $request){
        // if(!isset($request->no_trans)){
        //     return ApiFormatter::error(400, 'Pilih Data Dahulu!');
        // }

        // if(strtolower($request->status) != 'Set Ongkir'){
        //     return ApiFormatter::error(400, 'Belum Dapat Melakukan Set Ongkos Kirim!');
        // }

        $this->setOngkir($request->flagBayar, $request->nopb, $request->tanggal_pb, $request->no_trans, $request->freeOngkir, $request->jarakKirim, $request->kode_member);
    }

    //* Lanjutan Form Ongkir
    public function actionHitungUlang(Request $request){
        $ongkos = 0;
        $zona = "1";
        $flagEks = ($request->pengiriman == "EKSPEDISI") ? true : false;
        
    }

    //* btnStruk_Click
    public function actionDraftStruk($dgv_notrans, $dgv_nopb, $dgv_status, $dgv_kodeweb, $dgv_memberigr, $dgv_tglpb, $dtTrans, $dgv_freeongkir){
        $itemReal = $this->cekItemRealisasi($dgv_notrans, $dgv_nopb);
        if(!isset($dgv_notrans)){
            return ApiFormatter::error(400, 'Pilih Data Dahulu!');
        }

        if(strtolower($dgv_status) != 'set ongkir'){
            return ApiFormatter::error(400, 'Bukan Data Yang Siap Draft Struk!');
        }

        if($itemReal == 0){
            return ApiFormatter::error(400, 'Tidak Ada Data Realisasi!');
        }

        $this->draftStruk($dgv_kodeweb, $dgv_memberigr, $dgv_notrans, $dgv_nopb, $dgv_tglpb, $dtTrans, $dgv_freeongkir);
    }

    //* btnPembayaranVA_Click
    public function actionPembayaranVirtualAccount($dgv_tipebayar, $dgv_tglpb, $dgv_nopb, $dgv_notrans, $dgv_memberigr){
        if (str_contains($dgv_tipebayar, 'COD')) {
            $query = '';
            $query .= " SELECT TO_CHAR(COALESCE(dsp_totalbayar,0),'9,999,999,999') TOTAL_BAYAR  ";
            $query .= " FROM tbtr_dsp_spi ";
            $query .= " WHERE dsp_notrans = '" . $dgv_notrans . "' ";
            $query .= " AND dsp_nopb = '" . $dgv_nopb . "' ";
            $query .= " AND dsp_kodemember = '" . $dgv_memberigr . "' ";
            $dt = DB::select($query);

            // If flagSPI Then
            //     Sql = "SELECT ws_url FROM tbmaster_webservice WHERE ws_nama = 'WS_SPI'"
            // Else
            //     Sql = "SELECT ws_url FROM tbmaster_webservice WHERE ws_nama = 'WS_KLIK'"
            // End If

            // Dim urlCODVA = dtWebService.Rows(0).Item("WS_URL").ToString

            // urlMasterPayment = urlCODVA & "/getmasterpayment"
            // urlCreatePaymentChange = urlCODVA & "/createpaymentchange"
            // urlCekPaymentChangeStatus = urlCODVA & "/cekpaymentchangestatus"

            // frm.labelTglPB.Text = dgv_tglpb.ToString
            // frm.labelNoPB.Text = dgv_nopb.ToString
            // frm.labelNoTrans.Text = dgv_notrans.ToString
            // frm.labelAmount.Text = dt.Rows(0).Item("TOTAL_BAYAR").ToString

            //* open form -> frmVirtualAccount
        }
    }

    //* btnKonfirmasiBayar_Click_1
    public function actionKonfirmasiPembayaran($dgv_notrans, $dgv_status, $dgv_nopb, $dgv_memberigr){
        if(!isset($dgv_notrans)){
            return ApiFormatter::error(400, 'Pilih Data Dahulu!');
        }

        if(strtolower($dgv_status) != 'konfirmasi pembayaran'){
            return ApiFormatter::error(400, 'Bukan Data Yang Siap Konfirmasi Pembayaran!');
        }

        $this->konfirmasiBayar($dgv_nopb, $dgv_memberigr, $dgv_notrans);
    }

    //* btnSales_Click
    public function actionSales($dgv_notrans, $dgv_status, $dgv_nopb, $dgv_tipebayar, $dgv_tglpb, $dgv_kodeweb, $dgv_memberigr, $dgv_tipe_kredit, $dtTrans, $urlUpdateRealisasiKlik){
        $trxid = substr($dgv_nopb, 0, 6);

        if(!isset($dgv_notrans)){
            return ApiFormatter::error(400, 'Pilih Data Dahulu!');
        }

        if($dgv_status == 'Siap Struk'){

            if($dgv_tipebayar == 'COD'){
                return ApiFormatter::error(400, 'Pembayaran Transaksi COD menggunakan Program POS !');

            }elseif($dgv_tipebayar == 'COD-VA'){
                if($this->CheckTransaksiVALunas($trxid, $dgv_tglpb) == false){
                    return ApiFormatter::error(400, 'Pembayaran Transaksi Virtual Account belum lunas !');
                }

                goto PrintStruk;

            }else{
                PrintStruk:

                $this->InsertTransaksi($dgv_kodeweb, $dgv_nopb, $dgv_memberigr,$dgv_notrans, $dgv_tglpb, $dgv_tipe_kredit, $dtTrans, $dgv_tipebayar, $urlUpdateRealisasiKlik);
            }

        }else{
            if($dgv_status == 'Selesai Struk'){
                return ApiFormatter::error(400, 'Sudah Selesai Struk!');
            }else{
                return ApiFormatter::error(400, 'Belum Siap Struk!, Barang masih dipacking');
            }
        }
    }

    //! btnCetakSJ_Click
    public function actionCetakSuratJalan($dgv_notrans, $dgv_status, $dtTrans, $dgv_freeongkir, $dgv_nopb, $dgv_memberigr, $dgv_kodeWeb, $dgv_flagBayar){
        if(!isset($dgv_notrans)){
            return ApiFormatter::error(400, 'Pilih Data Dahulu!');
        }

        if(!$dgv_status != 'Siap Struk' AND $dgv_status != 'Selesai Struk'){
            return ApiFormatter::error(400, 'Bukan Data yang Sudah Selesai Struk!');
        }

        if(session('flagSPI') == true){
            if($dgv_freeongkir == 'T'){
                if(session('flagIGR')){
                    return ApiFormatter::error(400, 'Pesanan diambil di Toko IGR!');
                }else{
                    return ApiFormatter::error(400, 'Pesanan diambil di Toko SPI!');
                }
            }
        }

        $koliFound = DB::table('TBTR_PACKING_OBI')
            ->whereRaw("POBI_NOTRANSAKSI='" & $dgv_notrans & "' and DATE_TRUNC('DAY',pobi_tgltransaksi) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')." ")
            ->first();

        if(!empty($koliFound)){
            if(session('flagSPI')){
                $this->rptSuratJalanSPI($dgv_nopb, $dgv_notrans, $dgv_memberigr, $dtTrans, $dgv_flagBayar);
            }else{
                $this->rptSuratJalan($dgv_notrans, $dgv_kodeWeb, $dgv_nopb, $dgv_memberigr, $dgv_freeongkir, $dgv_flagBayar, $dtTrans);
            }
        }
    }

    //! btnCetakIIK_Click
    public function actionCetakIKK($dgv_notrans, $dgv_status, $dgv_nopb, $dgv_kodeWeb, $dgv_tglpb){

        //* Cetak Informasi Koli " & vbCrLf & "No Trans = " & dgv_notrans & " Ini?

        if($this->kodeDGV != 1){
            return ApiFormatter::error(400, 'Kembali ke list utama!');
        }

        if(!isset($dgv_notrans)){
            return ApiFormatter::error(400, 'Pilih Data Dahulu!');
        }

        if(!$dgv_status != 'Siap Struk' AND $dgv_status != 'Selesai Struk' AND $dgv_status != 'Konfirmasi Pembayaran'){
            return ApiFormatter::error(400, 'Bukan Data Yang Siap Struk atau Selesai Struk!');
        }

        $dtBarc = DB::select("SELECT DISTINCT pobi_nocontainer from tbtr_packing_obi join tbtr_obi_h on pobi_notransaksi = obi_notrans and pobi_tgltransaksi = obi_tgltrans WHERE pobi_notransaksi = '" . $dgv_notrans . "' AND obi_nopb = '" . $dgv_nopb . "'");
        if(count($dtBarc) == 0){
            return ApiFormatter::error(400, 'Tidak ada Data!');
        }

        //! DOWNLOAD DALAM BENTUK ZIP
        foreach($dtBarc as $item){
            $this->PrintNotaIIK($dgv_notrans, $item->pobi_nocontainer, $dgv_kodeWeb, $dgv_nopb, $dgv_tglpb);
        }
    }

    //! btnPBBatal_Click
    public function actionListItemPBBatal(){
        if(session('flagSPI') == true){
            $this->cekPBAkanBatal();
        }else{
            $this->cekItemBatal(true);
        }
    }

    //! btnIntransit_Click
    public function actionItemPickingBelumTransit(){
        $query = '';
        $query .= "WITH tbtr_obi AS ( ";
        $query .= "  SELECT substr(obi_prdcd, 1,6) || '0' obi_prdcd,  ";
        $query .= "         SUM(COALESCE(obi_qtyrealisasi,0)) obi_qtyrealisasi ";
        $query .= "  FROM tbtr_obi_d d ";
        $query .= "  JOIN tbtr_obi_h h ";
        $query .= "  ON h.obi_notrans = d.obi_notrans ";
        $query .= "  AND DATE_TRUNC('DAY',h.obi_tgltrans) = DATE_TRUNC('DAY',d.obi_tgltrans) ";
        $query .= "  WHERE DATE_TRUNC('DAY',h.obi_tgltrans) >= DATE_TRUNC('DAY',CURRENT_DATE - 30)  ";

        if(session('flagSPI') == true){
            $query .= "  AND UPPER(obi_nopb) LIKE '%SPI%' ";
        }else{
            $query .= "  AND UPPER(obi_nopb) NOT LIKE '%SPI%' ";
        }

        $query .= "  AND (    ";
        $query .= "        (COALESCE(h.obi_recid,'0') IN ('2','3','7') AND upper(h.obi_attribute2) IN ('KLIKIGR','CORP','SPI')) ";
        $query .= "        OR (COALESCE(h.obi_recid,'0') IN ('2','3','7','4','5') AND upper(h.obi_attribute2) IN ('TMI')) ";
        $query .= "      ) ";
        $query .= "  AND d.obi_recid IS NULL ";
        $query .= "  AND COALESCE(d.obi_qtyrealisasi,0) > 0  ";
        $query .= "  GROUP BY substr(obi_prdcd, 1,6) || '0' ";
        $query .= "  ORDER BY substr(obi_prdcd, 1,6) || '0' ";
        $query .= ") ";
        $query .= "SELECT ROWNUM NO, ";
        $query .= "       prd_kodedivisi DIV, ";
        $query .= "       prd_kodedepartement DEPT, ";
        $query .= "       prd_kodekategoribarang KAT, ";
        $query .= "       obi_prdcd PLU, ";
        $query .= "       prd_deskripsipanjang DESKRIPSI, ";
        $query .= "       (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) frac, ";
        $query .= "       obi_qtyrealisasi total ";
        $query .= "FROM tbtr_obi ";
        $query .= "JOIN tbmaster_prodmast ";
        $query .= "ON prd_prdcd = obi_prdcd ";
        $query .= "WHERE prd_unit <> 'KG' ";
        $dtItem = DB::select($query);

        if(count($dtItem) == 0){
            return ApiFormatter::error(400, 'Tidak ada data item belum DSP!');
        }

        //* buka form -> rptItemBelumDSP
    }

    //! btnLOPP_Click
    public function actionLoppCod($kolomSortBy){

        //* sebelum itu buka form -> frmSortingLOPP untuk mendapatkan value $kolomSortBy

        $query = '';
        $query .= "SELECT  ";
        $query .= "  obi_kdmember kode_member, ";
        $query .= "  obi_nopb kode_pesanan, ";
        $query .= "  TO_CHAR(obi_draftstruk,'DD-MM-YYYY') tgl_dsp, ";
        $query .= "  dsp_totalbayar nilai_dsp ";
        $query .= "FROM tbtr_obi_h ";
        $query .= "LEFT JOIN tbtr_dsp_spi ";
        $query .= " ON dsp_nopb = obi_nopb ";
        $query .= " AND dsp_notrans = obi_notrans ";
        $query .= " AND DATE_TRUNC('DAY', dsp_tglpb) = DATE_TRUNC('DAY', obi_tglpb) ";
        $query .= " AND dsp_kodemember = obi_kdmember ";
        $query .= "WHERE obi_tipebayar = 'COD' ";
        $query .= " AND COALESCE(obi_recid,'0') = '5' ";
        if(session('flagSPI') == true){
            $query .= " AND UPPER(obi_nopb) LIKE '%SPI%' ";
        }else{
            $query .= " AND UPPER(obi_nopb) NOT LIKE '%SPI%' ";
        }

        switch ($kolomSortBy) {
            case "Kode Member":
                $query .= " ORDER BY obi_kdmember ";
                break;
            case "No PB":
                $query .= " ORDER BY obi_nopb ";
                break;
            case "Tanggal DSP":
                $query .= " ORDER BY obi_draftstruk ";
                break;
            case "Nilai DSP":
                $query .= " ORDER BY dsp_totalbayar ";
                break;
            default:
                $query .= " ORDER BY obi_draftstruk ASC, kode_pesanan ASC, kode_member ASC ";
                break;
        }

        $myDa = DB::select($query);

        if(count($myDa) == 0){
            return ApiFormatter::error(400, 'Tidak ada data LOPP - COD!');
        }

        //* form reportnya -> rptOutsCODSPI
    }

    //! btnMaxSertim_Click
    public function actionListPBLebihDariMaxSerahTerima(){
        $this->cekNotifMaxSerahTerima(true);
    }

    //! btnPicker_Click
    public function actionMasterPickerHH(){
        //* show form -> frmPickerKlik
    }

    //! btnDelivery_Click_New
    public function actionListingDelivery($dgv_notrans, $dgv_status, $dgv_freeongkir){
        //* Cetak Listing Delivery NoPB " & dgv_nopb & " ?

        if(!isset($dgv_notrans)){
            return ApiFormatter::error(400, 'Pilih Data Dahulu!');
        }

        if($dgv_status == 'Siap Struk' AND $dgv_status == 'Selesai Struk'){
            return ApiFormatter::error(400, 'Bukan Data yang Sudah Selesai Struk!');
        }

        if($dgv_freeongkir == 'T'){
            if(session('flagIGR')){
                return ApiFormatter::error(400, 'Pesanan diambil di Toko IGR!');
            }else{
                return ApiFormatter::error(400, 'Pesanan diambil di Toko SPI!');
            }
        }

        //* open form -> frmDeliverySPI_New

        // nopol = fDelivery.noPol
        // driver = fDelivery.driver
        // deliveryman = fDelivery.deliveryman

        //* jika ada data $noListing dan $tglListing pada frmDeliverySPI_New
        // noListing = fDelivery.NoListingHistory
        // tglListing = fDelivery.TglListingHistory

        // If fDelivery.isHistory Then
        //     noListing = fDelivery.NoListingHistory
        //     tglListing = fDelivery.TglListingHistory
        // Else


            $noListing = DB::select("SELECT TO_CHAR(CURRENT_DATE, 'YYMMDD') || LPAD(nextval('seq_list_delivery_spi')::text, 5, '0') as noListing")[0]->noListing;
            $tglListing = Carbon::now();

            $query = '';
            $query .= "INSERT INTO tbtr_delivery_spi ( ";
            $query .= "  del_nolisting, ";
            $query .= "  del_tglkirim, ";
            $query .= "  del_tipebayar, ";
            $query .= "  del_kodemember, ";
            $query .= "  del_namamember, ";
            $query .= "  del_alamat, ";
            $query .= "  del_nopb, ";
            $query .= "  del_tglpb, ";
            $query .= "  del_nosp, ";
            $query .= "  del_nilaisp, ";
            $query .= "  del_nilaicod, ";
            $query .= "  del_nopol, ";
            $query .= "  del_driver, ";
            $query .= "  del_deliveryman, ";
            $query .= "  del_pincod, ";
            $query .= "  del_create_by, ";
            $query .= "  del_create_dt ";
            $query .= ") ";
            $query .= "SELECT  ";
            $query .= "  '" . $noListing . "' no_kirim, ";
            $query .= "  DATE_TRUNC('DAY', CURRENT_DATE) tgl_kirim, ";
            $query .= "  CASE WHEN tipe_bayar = 'COD-POIN' THEN 'POIN' ";
            $query .= "       WHEN tipe_bayar = 'COD-SALDO' THEN 'SALDO' ";
            $query .= "  ELSE ";
            $query .= "        ( ";
            $query .= "            SELECT tipe_bayar2 ";
            $query .= "            FROM ( ";
            $query .= "                SELECT no_pb, tgl_trans, LISTAGG(tipe_bayar, ' . ') WITHIN GROUP (ORDER BY tipe_bayar) tipe_bayar2 ";
            $query .= "                FROM payment_klikigr ";
            $query .= "                GROUP BY no_pb, tgl_trans ";
            $query .= "                ORDER BY tgl_trans DESC ";
            $query .= "            ) ";
            $query .= "            WHERE no_pb = obi_nopb ";
            $query .= "                AND TO_CHAR(tgl_trans, 'dd-MM-YYYY') = TO_CHAR(obi_tglpb, 'dd-MM-YYYY') ";
            $query .= "        ) ";
            $query .= "  End tipe_bayar, ";
            $query .= "  obi_kdmember kode_member, ";
            $query .= "  amm_namapenerima nama_member, ";
            $query .= "  amm_namaalamat alamat, ";
            $query .= "  obi_nopb no_pb, ";
            $query .= "  obi_tglpb tgl_pb, ";
            $query .= "  COALESCE(obi_nostruk,'-') no_sp, ";
            $query .= "  dsp_totaldsp nilai_sp, ";
            $query .= "  CASE WHEN tipe_bayar <> 'COD' THEN 0 ELSE dsp_totalbayar END nilai_cod, ";
            $query .= "  '" . $nopol . "' nopol, ";
            $query .= "  '" . $driver . "' driver, ";
            $query .= "  '" . $deliveryman . "' deliveryman, ";
            $query .= "  CASE WHEN obi_tipebayar = 'COD' THEN obi_cod_pincode ELSE '-' END pincod, ";
            $query .= "  '" . session('userid') . "' create_by, ";
            $query .= "  NOW() create_dt ";
            $query .= "FROM temp_delivery_spi ";
            $query .= "JOIN tbtr_obi_h ";
            $query .= " ON obi_nopb = no_pb ";
            $query .= " AND obi_tglpb = TO_DATE(tgl_pb,'DD-MM-YYYY') ";
            $query .= " AND obi_kdmember = kode_member ";
            $query .= "JOIN tbtr_alamat_mm ";
            $query .= " ON amm_nopb = no_pb ";
            $query .= " AND amm_tglpb = TO_DATE(tgl_pb,'DD-MM-YYYY') ";
            $query .= " AND amm_kodemember = kode_member ";
            $query .= "LEFT JOIN tbtr_dsp_spi ";
            $query .= " ON dsp_nopb = no_pb ";
            $query .= " AND DATE_TRUNC('DAY',dsp_tglpb) = TO_DATE(tgl_pb,'DD-MM-YYYY') ";
            $query .= " AND dsp_kodemember = kode_member ";
            $query .= "WHERE ip = '" . $this->getIP() . "' ";
            DB::insert($query);

            //* UPDATE TBTR_DSP_SPI
            $query = '';
            $query .= " MERGE INTO tbtr_dsp_spi t ";
            $query .= " USING ( ";
            $query .= "  SELECT DISTINCT  ";
            $query .= "    del_nolisting, ";
            $query .= "    del_nopb, ";
            $query .= "    del_tglpb, ";
            $query .= "    del_kodemember ";
            $query .= "  FROM tbtr_delivery_spi ";
            $query .= "  WHERE del_nolisting = '" . $noListing . "' ";
            $query .= "  AND DATE_TRUNC('DAY',del_tglkirim) = '" . Carbon::parse($tglListing)->format('Y-m-d H:i:s') . "' ";
            $query .= " ) s ";
            $query .= " ON ( ";
            $query .= "       t.dsp_nopb = s.del_nopb ";
            $query .= "   AND DATE_TRUNC('DAY',t.dsp_tglpb) = DATE_TRUNC('DAY',s.del_tglpb) ";
            $query .= "   AND t.dsp_kodemember = s.del_kodemember ";
            $query .= " ) ";
            $query .= " WHEN MATCHED THEN ";
            $query .= "   UPDATE SET t.dsp_nolisting = s.del_nolisting, ";
            $query .= "              t.dsp_modify_by = '" . session('userid') . "', ";
            $query .= "              t.dsp_modify_dt = CURRENT_DATE ";
            DB::insert($query);
        // End If

        $query = '';
        $query .= " SELECT DISTINCT ";
        $query .= "   del_tipebayar tipebayar, ";
        $query .= "   del_kodemember kode_member, ";
        $query .= "   del_namamember nama_member, ";
        $query .= "   del_nopb kode_pesanan, ";
        $query .= "   del_alamat alamat, ";
        $query .= "   del_nopb kode_pesanan, ";
        $query .= "   del_nosp no_sp, ";
        $query .= "   del_nilaisp nilai_sp, ";
        $query .= "   del_nilaicod nilai_cod, ";
        $query .= "   del_pincod pin_cod ";
        $query .= " FROM tbtr_delivery_spi ";
        $query .= " WHERE del_nolisting = '" . $noListing . "' ";
        $query .= " AND DATE_TRUNC('DAY',del_tglkirim) = '" . Carbon::parse($tglListing)->format('Y-m-d H:i:s') . "' ";
        $dtItem = DB::select($query);

        if(count($dtItem) == 0){
            return ApiFormatter::error(400, 'List Delivery tidak ditemukan.');
        }

        // rptDelivery.SetParameterValue("cabang", IIf(flagIGR, "INDOGROSIR", "STOCK POINT INDOGROSIR"))
        // rptDelivery.SetParameterValue("kdIGR", KodeIGR & " - " & NamaIGR)
        // rptDelivery.SetParameterValue("user_id", UserMODUL)
        // rptDelivery.SetParameterValue("nopol", nopol)
        // rptDelivery.SetParameterValue("driver", driver)
        // rptDelivery.SetParameterValue("deliveryman", deliveryman)
        // rptDelivery.SetParameterValue("noListing", noListing)
        // rptDelivery.SetParameterValue("tglListing", tglListing)

        //* form report -> rptListDeliverySPI
    }

    //! btnReCreateAWB_Click
    public function actionReCreateAWB(){

        $query = '';
        $query .= " SELECT  ";
        $query .= "   obi_nopb nopb, ";
        $query .= "   obi_notrans notrans, ";
        $query .= "   TO_CHAR(obi_tgltrans,'DD-MM-YYYY') tgltrans, ";
        $query .= "   obi_kdmember kdmember, ";
        $query .= "   obi_tgltrans ";
        $query .= " FROM tbtr_obi_h  ";
        $query .= " WHERE COALESCE(obi_recid,'0') IN ('5','6') ";
        $query .= " AND DATE_TRUNC('DAY',obi_tgltrans) >= DATE_TRUNC('DAY',CURRENT_DATE-30) ";
        $query .= " AND obi_trxidnew IS NULL ";
        $query .= " AND EXISTS ( ";
        $query .= "   SELECT sti_pin ";
        $query .= "   FROM tbtr_serahterima_ipp ";
        $query .= "   WHERE sti_noorder = obi_trxid ";
        $query .= "   AND UPPER(sti_tipeproses) = 'TITIP' ";
        $query .= " ) ";
        $query .= " ORDER BY obi_tgltrans DESC, obi_notrans ASC";
        $dtPB = DB::select($query);

        if(count($dtPB) == 0){
            return ApiFormatter::error(400, 'Tidak ada PB yang gagal serah terima!');
        }

        //* ADD PILIHAN ALASAN BATAL KIRIM
        $alasanBtl = DB::select("SELECT ROW_NUMBER() OVER () AS NO, ALASAN FROM (SELECT abk_alasan AS alasan FROM tbmaster_alasan_batal_kirim ORDER BY 1) AS alasan");
        $alasanBatal = $alasanBtl[0]->alasan;

        //! show form -> FrmReCreateAWB
        //! nanti mungkin formnya milih dari $dtPB terus ditampung di variable $fRecreateAWB

        $fRecreateAWB = [];

        if(count($fRecreateAWB) == 0){
            return ApiFormatter::error(400, 'Belum ada PB yang dipilih');
        }

        foreach($fRecreateAWB as $item){
            if(session('flagSPI') == true){
                $this->reCreateAWB_SPI($item->kdmember, $item->notrans, $item->tgltrans, $item->nopb, $alasanBatal);
            }else{
                $this->reCreateAWB_KLIK($item->kdmember, $item->notrans, $item->tgltrans, $item->nopb, $alasanBatal);
            }
        }

        return ApiFormatter::success(200, 'Selesai Proses Re-Create AWB IPP');

    }

    //! btnAlasanBatalKirim_Click
    public function actionMasterAlasanBatalKirim(){
        // frmIns.Text = "Master Alasan Batal Kirim"
        // frmIns.lblTitle.Text = "Master Alasan Batal Kirim"
        // frmIns.flagMode = "AlasanBatalKirim"
        // frmIns.ShowDialog()

        //! open form -> frmMasterData
    }

    //! btnBASPI_Click
    public function actionBAPengembalianDana(){
        if(session('flagSPI') == false){
            return ApiFormatter::error(400, 'Khusus SPI');
        }

        //! open form -> frmBARefundSPI

        //* Query Tampilan Datagridview
        $query = '';
        $query .= " SELECT   ";
        $query .= "   tipe_bayar tipe_bayar, ";
        $query .= "   obi_nopb no_pb,  ";
        $query .= "   TO_CHAR(obi_tglpb,'DD-MM-YYYY') tgl_pb,  ";
        $query .= "   obi_kdmember kode_member,  ";
        $query .= "   total, ";
        $query .= "   0 ba ";
        $query .= " FROM tbtr_obi_h  ";
        $query .= " JOIN payment_klikigr ";
        $query .= " ON kode_igr = obi_kodeigr ";
        $query .= " AND kode_member = obi_kdmember ";
        $query .= " AND no_pb = obi_nopb ";
        $query .= " AND no_trans = obi_notrans ";
        $query .= " AND DATE_TRUNC('DAY',tgl_trans) = DATE_TRUNC('DAY',obi_tgltrans) ";
        $query .= " WHERE UPPER(COALESCE(obi_tipebayar, 'X')) <> 'COD'  ";
        $query .= " AND UPPER(COALESCE(obi_tipebayar, 'X')) <> 'TOP'  ";
        $query .= " AND COALESCE(obi_recid,'0') LIKE 'B%' ";

        if(session('flagSPI') == true){
            $query .= " AND UPPER(obi_nopb) LIKE '%SPI%' ";
        }else{
            $query .= " AND UPPER(obi_nopb) NOT LIKE '%SPI%' ";
        }

        $query .= " AND NOT EXISTS (  ";
        $query .= "  SELECT brs_nopb  ";
        $query .= "  FROM tbtr_barefund_spi ";
        $query .= "  WHERE brs_kodemember = obi_kdmember  ";
        $query .= "  AND brs_nopb = obi_nopb  ";
        $query .= "  AND brs_tglpb = obi_tglpb ";
        $query .= " )  ";
        $query .= " ORDER BY tipe_bayar, obi_tglpb, obi_nopb ";
        $dt = DB::select($query);

        if(count($dt) == 0){
            return ApiFormatter::error(400, 'Tidak ada Transaksi yang batal..');
        }

        //! action yang ada di form frmBARefundSPI
        //! dummy variable
        $isHistory = true;
        $NoBAHistory = '';
        $tglBAHistory = '';

        if($isHistory == true){
            $seqBA = $NoBAHistory;
            $tglBA = $tglBAHistory;
        }else{
            $seqBA = '';
            $tglBA = DB::select("SELECT TO_CHAR(NOW(), 'YYMM') || LPAD(nextval('seq_ba_refund_spi')::text, 6, '0') as value")[0]->value;

            $query = '';
            $query .= "INSERT INTO tbtr_barefund_spi ( ";
            $query .= "  brs_tglba, ";
            $query .= "  brs_noba, ";
            $query .= "  brs_tipebayar, ";
            $query .= "  brs_nopb, ";
            $query .= "  brs_tglpb, ";
            $query .= "  brs_kodemember, ";
            $query .= "  brs_nilairefund, ";
            $query .= "  brs_create_by, ";
            $query .= "  brs_create_dt ";
            $query .= ") ";
            $query .= "SELECT  ";
            $query .= "  TO_DATE('" . $tglBA . "','DD-MM-YYYY') tglba, ";
            $query .= "  '" . $seqBA . "' noba, ";
            $query .= "  tipebayar, ";
            $query .= "  nopb, ";
            $query .= "  TO_DATE(tglpb,'DD-MM-YYYY') tglpb, ";
            $query .= "  kodemember, ";
            $query .= "  nilairefund, ";
            $query .= "  '" . session('userid') . "' create_by, ";
            $query .= "  NOW() create_dt ";
            $query .= "FROM temp_barefund_spi ";
            $query .= "JOIN tbtr_obi_h ";
            $query .= "ON obi_nopb = nopb ";
            $query .= "AND DATE_TRUNC('DAY',obi_tglpb) = TO_DATE(tglpb,'DD-MM-YYYY') ";
            $query .= "AND obi_kdmember = kodemember ";
            $query .= "WHERE IP = '" . $this->getIP() . "' ";
            DB::insert($query);
        }

        $query = '';
        $query .= " SELECT  ";
        $query .= "   brs_tipebayar tipeBayar, ";
        $query .= "   COUNT(DISTINCT brs_nopb) jmlTrans, ";
        $query .= "   SUM(brs_nilairefund) jmlRefund ";
        $query .= " FROM tbtr_barefund_spi ";
        $query .= " WHERE brs_noba = '" . $seqBA . "' ";
        $query .= " GROUP BY brs_tipebayar ";
        $query .= " ORDER BY brs_tipebayar ";
        $dt = DB::select($query);

        if(count($dt) == 0){
            return ApiFormatter::error(400, 'BA Pengembalian Dana SPI tidak ditemukan.');
        }

        //! open report -> rptBA

        // rptBA.SetParameterValue("tglBA", tglBA)
        // rptBA.SetParameterValue("noBA", seqBA)
        // rptBA.SetParameterValue("namaSPI", NamaIGR)
        // rptBA.SetParameterValue("namaInduk", "INDUK " & NamaIGR)
    }

    //! btnBARusakSPI_Click
    public function actionBARusakKemasan($dgv_status, $dgv_tipebayar){
        if(session('flagSPI') == true){
            if((($dgv_status == 'Siap Struk' OR $dgv_status == 'Selesai Struk') AND $dgv_tipebayar == 'COD') OR ($dgv_status == 'Selesai Struk' AND $dgv_tipebayar <> "COD")){
                //! open form -> frmBARusakSPI
                //! dgv_nopb, dgv_notrans, dtTrans.Value.ToString("dd-MM-yyyy"), dgv_memberigr, dgv_tipebayar, dgv_status

            }else{
                if(str_contains(strtoupper($dgv_status), 'BATAL')){
                    return ApiFormatter::error(400, 'Transaksi sudah dibatalkan!');
                }else{
                    if($dgv_tipebayar == 'COD'){
                        return ApiFormatter::error(400, 'Transaksi COD belum DSP, Belum dapat Input BA Rusak!');
                    }else{
                        return ApiFormatter::error(400, 'Transaksi belum distruk, Belum dapat Input BA Rusak!');
                    }
                }
            }

        }else{
            if($dgv_status == 'Siap Struk' AND $dgv_tipebayar == 'COD'){
                //! open form -> frmBARusakSPI
                //! dgv_nopb, dgv_notrans, dtTrans.Value.ToString("dd-MM-yyyy"), dgv_memberigr, dgv_tipebayar, dgv_status

            }else{
                if($dgv_tipebayar == 'COD'){
                    $message = $dgv_status == 'Selesai Struk' ? 'Transaksi sudah distruk!' : 'Transaksi COD belum DSP, Belum dapat Input BA Rusak!';
                    return ApiFormatter::error(400, $message);
                }else{
                    return ApiFormatter::error(400, 'Inputan BA RK Khusus Transaksi COD!');
                }
            }
        }
    }

    //! btnFormPengembalianBarang_Click
    public function actionCetakFormPengembalianBarang(){
        $this->cetakFormPengembalianBarang('','','','');
    }

    //! btnLaporanPenyusutan_Click
    public function actionLaporanPenyusutanHarian($dtTrans){

        //? Cetak Laporan Penyusutan Harian?

        if(session('flagSPI')){
            return ApiFormatter::error(400, 'Khusus Cabang Indogrosir');
        }

        $this->rptPenyusutanHarianPerishable($dtTrans);
    }

    //! btnPesananExpired_Click
    public function actionLaporanPesananExpired(){
        //! open form -> frmPeriodePesanan

        //! action dari frmPeriodePesanan
        //! dummy variable
        $isCetak = true;
        $periodeAwal = '';
        $periodeAkhir = '';
        $namaPT = '';

        $query = '';
        $query .= " SELECT  ";
        $query .= "   obi_kdmember kode_member, ";
        $query .= "   cus_namamember nama_member, ";
        $query .= "   TO_CHAR(obi_tglpb,'DD-MM-YYYY') tgl_pb, ";
        $query .= "   obi_nopb no_pb, ";
        $query .= "   total_pay nilai_pb ";
        $query .= " FROM tbtr_obi_h  ";
        $query .= " JOIN tbmaster_customer ";
        $query .= "   ON cus_kodemember = obi_kdmember ";
        $query .= " JOIN ( ";
        $query .= "   SELECT kode_member, no_pb, no_trans, tgl_trans, SUM(total) total_pay ";
        $query .= "   FROM payment_klikigr ";
        $query .= "   GROUP BY kode_member, no_pb, no_trans, tgl_trans ";
        $query .= " ) payment ";
        $query .= "   ON no_pb = obi_nopb ";
        $query .= "  AND no_trans = obi_notrans ";
        $query .= "  AND tgl_trans = obi_tgltrans ";
        $query .= "  AND kode_member = obi_kdmember ";
        $query .= " WHERE obi_recid LIKE 'B%'  ";
        $query .= "   AND UPPER(obi_alasanbtl) LIKE 'WAKTU PROSES LEBIH DARI % HARI' ";
        $query .= "   AND DATE_TRUNC('DAY',obi_tglpb) BETWEEN TO_DATE('" & $periodeAwal & "','DD-MM-YYYY') ";
        $query .= "                            AND TO_DATE('" & $periodeAkhir & "','DD-MM-YYYY') ";
        $query .= " ORDER BY obi_nopb ASC ";
        $data['dtItem'] = DB::select($query);

        if(count($data['dtItem']) == 0){
            return ApiFormatter::error(400, 'Pesanan Expired tidak ditemukan.');
        }

        if(session('flagSPI') == true){
            $query = '';
            $query .= " SELECT cab_kodecabang || ' - ' || cab_namacabang nama ";
            $query .= " FROM tbmaster_cabang ";
            $query .= " JOIN tbmaster_spi  ";
            $query .= " ON spi_kodeigr = cab_kodecabang ";
            $query .= " WHERE spi_kodespi = '" & session('KODECABANG') & "' ";
            $query .= " LIMIT 1 ";
            $data['namaInduk'] = DB::select($query);
        }

        // rptPE.SetParameterValue("cabang", namaPT)
        // If flagIGR Then
        //     rptPE.SetParameterValue("kdIGR", KodeIGR & " - " & NamaIGR)
        // Else
        //     rptPE.SetParameterValue("kdIGR", namaInduk & vbNewLine & KodeIGR & " - " & NamaIGR)
        // End If
        // rptPE.SetParameterValue("user_id", UserMODUL)
        // rptPE.SetParameterValue("periode", periodeAwal & " s/d " & periodeAkhir)
        // rptPE.SetParameterValue("jenisCabang", IIf(flagIGR, "Member KlikIgr", "MMS"))

        //! open report -> rptPE
    }

    //! btnSTKardus_Click
    public function actionBuktiSerahTerimaKardus(Request $request){
        if($request->isShowDatatables == true){
            if(session('flagSPI')){
                return ApiFormatter::error(400, 'Khusus Cabang Indogrosir');
            }
    
            //! open form -> frmSerahTerimaKardus
    
            //! tampil di datatable frmSerahTerimaKardus
            //* Query Tampilan Datagridview
            $query = '';
            $query .= "SELECT  ";
            $query .= "  obi_nopb no_pb, ";
            $query .= "  TO_CHAR(obi_tglpb,'DD-MM-YYYY') tgl_pb, ";
            $query .= "  obi_kdmember kode_member, ";
            $query .= "  0 cetak ";
            $query .= "FROM tbtr_obi_h ";
            $query .= "WHERE UPPER(obi_nopb) LIKE '%/500/%' ";
            $query .= "AND UPPER(COALESCE(obi_tipebayar, 'X')) = 'TOP' ";
            $query .= "AND COALESCE(obi_recid,'0') = '6' ";
            $query .= "AND NOT EXISTS ( ";
            $query .= " SELECT stk_nopb ";
            $query .= " FROM tbtr_serah_terima_kardus ";
            $query .= " WHERE stk_kodemember = obi_kdmember ";
            $query .= " AND stk_nopb = obi_nopb ";
            $query .= " AND stk_tglpb = obi_tglpb ";
            $query .= ") ";
            $query .= "ORDER BY obi_tglpb, obi_nopb ";
            $data["datatables"] = DB::select($query);

            $query = "SELECT DISTINCT stk_noserahterima nostk, TO_CHAR(stk_tglserahterima, 'DD-MM-YYYY') tglstk ";
            $query .= "FROM tbtr_serah_terima_kardus ";
            $query .= "ORDER BY stk_noserahterima DESC ";
            $data["cbSTK"] = DB::select($query);

            return ApiFormatter::success(200, "success", $data);
        } else {
            $isCetak = true;
            $isHistory = true;
            $NoSTKHistory = '';
            $tglSTKHistory = '';
            if($request->ckHistory == 1){
                if($request->ckHistory !== ''){
                    $NoSTKHistory = $request->select_history_stk;
                    $dictSTKHistory = $request->tglStkHistory;
                    $isCetak = true;
                    $isHistory = true;
                } else {
                    return ApiFormatter::error(400, "Belum Ada STK yg dipilih!");
                }
            }
            //! action dari frmPeriodePesanan
            //! dummy variable
    
            if($isHistory == true){
                $seqSerahTerimaKardus = $NoSTKHistory;
                $tglSTK = $tglSTKHistory;
    
            }else{
                $seqSerahTerimaKardus = '';
                $tglSTK = DB::select("SELECT TO_CHAR(NOW(), 'YYMM') || LPAD(nextval('seq_serah_terima_kardus')::text, 6, '0') as value")[0]->value;
    
                $query = '';
                $query .= "INSERT INTO tbtr_serah_terima_kardus ( ";
                $query .= "  stk_nopb, ";
                $query .= "  stk_tglpb, ";
                $query .= "  stk_kodemember, ";
                $query .= "  stk_notrans, ";
                $query .= "  stk_tgltrans, ";
                $query .= "  stk_nostruk, ";
                $query .= "  stk_noserahterima, ";
                $query .= "  stk_tglserahterima, ";
                $query .= "  stk_create_by, ";
                $query .= "  stk_create_dt ";
                $query .= ") ";
                $query .= "SELECT  ";
                $query .= "  obi_nopb, ";
                $query .= "  obi_tglpb, ";
                $query .= "  obi_kdmember, ";
                $query .= "  obi_notrans, ";
                $query .= "  obi_tgltrans, ";
                $query .= "  obi_cashierid || '/' || obi_kdstation || '/' || obi_nostruk nostruk, ";
                $query .= "  '" . $seqSerahTerimaKardus . "' noserahterima, ";
                $query .= "  NOW() tglserahterima, ";
                $query .= "  '" . session('userid') . "' create_by, ";
                $query .= "  NOW() create_dt ";
                $query .= "FROM temp_serah_terima_kardus ";
                $query .= "JOIN tbtr_obi_h ";
                $query .= "ON obi_nopb = no_pb ";
                $query .= "AND obi_tglpb = TO_DATE(tgl_pb,'DD-MM-YYYY') ";
                $query .= "AND obi_kdmember = kode_member ";
                $query .= "WHERE IP = '" . $this->getIP() . "' ";
            }
    
            $query = '';
            $query .= "SELECT  ";
            $query .= "  stk_nopb no_pesanan, ";
            $query .= "  pobi_nocontainer no_koli, ";
            $query .= "  stk_nostruk no_struk, ";
            $query .= "  COUNT(DISTINCT pobi_prdcd) jml_item ";
            $query .= "FROM tbtr_serah_terima_kardus ";
            $query .= "JOIN tbtr_obi_h h ";
            $query .= "ON h.obi_nopb = stk_nopb ";
            $query .= "AND h.obi_tglpb = stk_tglpb ";
            $query .= "AND h.obi_kdmember = stk_kodemember ";
            $query .= "JOIN tbtr_obi_d d ";
            $query .= "ON d.obi_notrans = h.obi_notrans ";
            $query .= "AND d.obi_tgltrans = h.obi_tgltrans ";
            $query .= "JOIN tbtr_packing_obi ";
            $query .= "ON pobi_notransaksi = d.obi_notrans ";
            $query .= "AND pobi_tgltransaksi = d.obi_tgltrans ";
            $query .= "AND pobi_prdcd = d.obi_prdcd ";
            $query .= "WHERE stk_noserahterima = '" . $seqSerahTerimaKardus . "' ";
            $query .= "AND d.obi_recid IS NULL ";
            $query .= "AND d.obi_qtyrealisasi > 0 ";
            $query .= "GROUP BY stk_nopb, pobi_nocontainer, stk_nostruk ";
            $query .= "ORDER BY no_pesanan ASC, no_koli ASC ";
            $dtItem = DB::select($query);
    
            if(count($dtItem) == 0){
                return ApiFormatter::error(400, 'List Delivery tidak ditemukan.');
            }
    
            dd($dtItem);
    
            //! open form -> rptSTKardus
    
            // rptSTKardus.SetParameterValue("kdIGR", NamaIGR)
            // rptSTKardus.SetParameterValue("user_id", UserMODUL)
            // rptSTKardus.SetParameterValue("noSTKrat", seqSerahTerimaKardus)
            // rptSTKardus.SetParameterValue("tglSTKrat", tglSTK)
        }

    }

    private function cetakFormPengembalianBarang($nopb, $notrans, $kdmember, $tgltrans){
        if(session('flagSPI') == true){
            //! open form -> rptFPBSPI
        }else{
            //! open form -> rptFPBKlik
        }

        // rptFPB.SetParameterValue("noba", "")
        // rptFPB.SetParameterValue("tglba", "")
        // rptFPB.SetParameterValue("cabang", KodeIGR & " / " & NamaIGR)
        // rptFPB.SetParameterValue("noAWB", "")
        // rptFPB.SetParameterValue("alasanPengembalian", "")
    }

    private function reCreateAWB_SPI($kdMember, $noTrans, $tglTrans, $noPB, $alasanBatal){
        //* GET API IPP x SPI
        $dt = DB::select("SELECT ws_url FROM tbmaster_webservice WHERE ws_nama = 'IPP_SPI'");
        if(count($dt) == 0 || $dt[0]->ws_url == ''){
            $message = 'API IPP SPI tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        //* GET CREDENTIAL API IPP x SPI
        $dt = DB::select("SELECT cre_name, cre_key FROM tbmaster_credential WHERE cre_type = 'IPP_SPI'");
        if(count($dt) == 0){
            $message = 'CREDENTIAL API IPP x SPI tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $apiName = $dt[0]->cre_name;
        $apiKey = $dt[0]->cre_key;

        $splitTrans = explode("/", $noPB);
        $trxid = $splitTrans[0];
        $newTrxid = "A" . $trxid;

        //* HIT API RE-CREATE AWB
        $urlSPI = '/recreateawb';

        $postData = [
            'trxid' => $trxid,
            'newTrxid' => $newTrxid,
        ];

        $strResponse = $this->ConToWebServiceNew($urlSPI, $apiName, $apiKey, $postData);

        $strPostData = null;
        $strResponse = null;

        $query = '';
        $query .= "INSERT INTO log_createawb ( ";
        $query .= " nopb,  ";
        $query .= " notrans,  ";
        $query .= " url,  ";
        $query .= " param,  ";
        $query .= " response,  ";
        $query .= " create_dt ";
        $query .= ") ";
        $query .= "VALUES ( ";
        $query .= " '" . $noPB . "', ";
        $query .= " '" . $newTrxid . "', ";
        $query .= " '" . $urlSPI . "', ";
        $query .= " '" . $strPostData . "', "; //! dummy result dari ConToWebServiceNew
        $query .= " '" . $strResponse . "', "; //! dummy result dari ConToWebServiceNew
        $query .= " NOW() ";
        $query .= ") ";
        DB::insert($query);

        try{

            //! dummy result dari ConToWebServiceNew
            $response_code = null;
            $response_message = '';
            $noAWB = null;
            $cost = null;
            $pincode = null;

            $this->updateDeliveryInfo_SPI($kdMember, $noTrans, $tglTrans, $noPB, $newTrxid);

            if($response_code == 200){
                $query = '';
                $query .= " SELECT awi_noawb ";
                $query .= " FROM tbtr_awb_ipp ";
                $query .= " WHERE awi_nopb = '" . $noPB . "' ";
                $query .= " AND awi_noorder = '" . $newTrxid . "' ";
                $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= " AND awi_kodemember = '" . $kdMember . "' ";
                $dt = DB::select($query);

                $UserMODUL = session('userid');
                if(count($dt) == 0){
                    $query = '';
                    $query .= " INSERT INTO tbtr_awb_ipp ( ";
                    $query .= "   awi_noawb, ";
                    $query .= "   awi_nopb, ";
                    $query .= "   awi_noorder, ";
                    $query .= "   awi_tglorder, ";
                    $query .= "   awi_kodemember, ";
                    $query .= "   awi_cost, ";
                    $query .= "   awi_ref_noorder, ";
                    $query .= "   awi_pincode, ";
                    $query .= "   awi_tipetransaksi, ";
                    $query .= "   awi_create_by, ";
                    $query .= "   awi_create_dt ";
                    $query .= " ) ";
                    $query .= " VALUES ( ";
                    $query .= "   '" . $noAWB . "', ";
                    $query .= "   '" . $noPB . "', ";
                    $query .= "   '" . $newTrxid . "', ";
                    $query .= "   TO_DATE('" . $tglTrans . "','DD-MM-YYYY'), ";
                    $query .= "   '" . $kdMember . "', ";
                    $query .= "   " . $cost . ", ";
                    $query .= "   '" . $trxid . "', ";
                    $query .= "   '" . $pincode . "', ";
                    $query .= "   '" . session('flagSPI') == true ? 'SPI': 'KLIK IGR' . "', ";
                    $query .= "   '" . $UserMODUL . "', ";
                    $query .= "   NOW() ";
                    $query .= " ) ";
                    DB::insert($query);

                }else{
                    $query = '';
                    $query .= " UPDATE tbtr_awb_ipp ";
                    $query .= " SET awi_cost = " . $cost . ", ";
                    $query .= "     awi_pincode = '" . $pincode . "', ";
                    $query .= "     awi_modify_by = '" . $UserMODUL . "', ";
                    $query .= "     awi_modify_dt = NOW() ";
                    $query .= " WHERE awi_nopb = '" . $noPB . "' ";
                    $query .= " AND awi_noorder = '" . $newTrxid . "' ";
                    $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                    $query .= " AND awi_kodemember = '" . $kdMember . "' ";
                    DB::insert($query);
                }

                //* UPDATE TBTR_ALAMAT_MM
                $query = '';
                $query .= "UPDATE tbtr_alamat_mm ";
                $query .= "   SET amm_noawb = '" & $noAWB & "' ";
                $query .= " WHERE amm_nopb = '" & $noPB & "' ";
                $query .= "   AND amm_notrans = '" & $noTrans & "' ";

                //UPDATE TBTR_OBI_H - OBI_TRXIDNEW
                $query = '';
                $query .= "UPDATE tbtr_obi_h ";
                $query .= "   SET obi_trxidnew = '" & $newTrxid & "' ";
                $query .= " WHERE obi_nopb = '" & $noPB & "' ";
                $query .= "   AND obi_notrans = '" & $noTrans & "' ";
                $query .= "   AND DATE_TRUNC('DAY',obi_tgltrans) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= "   AND obi_kdmember = '" & $kdMember & "' ";
                DB::update($query);

                //UPDATE FLAG BATAL DELIVERY
                $query = '';
                $query .= " UPDATE TBTR_DELIVERY_SPI ";
                $query .= " SET del_flagbatal = 'Y' ";
                $query .= " WHERE del_nopb = '" & $noPB & "' ";
                $query .= " AND del_kodemember = '" & $kdMember & "' ";
                DB::update($query);

                //UPDATE STATUS DSP SPI
                $query = '';
                $query .= " UPDATE TBTR_DSP_SPI ";
                $query .= " SET dsp_status = 'DSP', ";
                $query .= "     dsp_nolisting = 'NULL', ";
                $query .= "     dsp_modify_by = '" & $UserMODUL & "', ";
                $query .= "     dsp_modify_dt = NOW() ";
                $query .= " WHERE dsp_nopb = '" & $noPB & "' ";
                $query .= " AND dsp_kodemember = '" & $kdMember & "' ";
                DB::update($query);

                //UPDATE TBTR_AWB_IPP - TRXID
                $query = '';
                $query .= "UPDATE tbtr_awb_ipp ";
                $query .= "   SET awi_status = 'BATAL', ";
                $query .= "       awi_alasanbatal = '" & $alasanBatal & "', ";
                $query .= "       awi_modify_by = '" & $UserMODUL & "', ";
                $query .= "       awi_modify_dt = NOW() ";
                $query .= " WHERE awi_nopb = '" & $noPB & "' ";
                $query .= "   AND awi_noorder = '" & $trxid & "' ";
                $query .= "   AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= "   AND awi_kodemember = '" & $kdMember & "' ";
                DB::update($query);

            }elseif($response_code == 400 AND str_contains(strtoupper($response_message), 'BATAS MAKSIMAL PEMBUATAN AWB')){
                $query = '';
                $query .= " SELECT awi_noawb, awi_cost, awi_pincode ";
                $query .= " FROM tbtr_awb_ipp ";
                $query .= " WHERE awi_nopb = '" & $noPB & "' ";
                $query .= " AND awi_noorder = '" & $newTrxid & "' ";
                $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= " AND awi_kodemember = '" & $kdMember & "' ";
                $dt = DB::select($query);

                if(count($dt) > 0 ){

                    //UPDATE TBTR_ALAMAT_MM
                    $query = '';
                    $query .= "UPDATE tbtr_alamat_mm ";
                    $query .= "   SET amm_noawb = '" & $noAWB & "' ";
                    $query .= " WHERE amm_nopb = '" & $noPB & "' ";
                    $query .= "   AND amm_notrans = '" & $noTrans & "' ";
                    DB::update($query);

                    //UPDATE TBTR_OBI_H - OBI_TRXIDNEW
                    $query = '';
                    $query .= "UPDATE tbtr_obi_h ";
                    $query .= "   SET obi_trxidnew = '" & $newTrxid & "' ";
                    $query .= " WHERE obi_nopb = '" & $noPB & "' ";
                    $query .= "   AND obi_notrans = '" & $noTrans & "' ";
                    $query .= "   AND DATE_TRUNC('DAY',obi_tgltrans) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                    $query .= "   AND obi_kdmember = '" & $kdMember & "' ";
                    DB::update($query);

                    //UPDATE FLAG BATAL DELIVERY
                    $query = '';
                    $query .= " UPDATE TBTR_DELIVERY_SPI ";
                    $query .= " SET del_flagbatal = 'Y' ";
                    $query .= " WHERE del_nopb = '" & $noPB & "' ";
                    $query .= " AND del_kodemember = '" & $kdMember & "' ";
                    DB::update($query);

                    //UPDATE STATUS DSP SPI
                    $query = '';
                    $query .= " UPDATE TBTR_DSP_SPI ";
                    $query .= " SET dsp_status = 'DSP', ";
                    $query .= "     dsp_nolisting = 'NULL', ";
                    $query .= "     dsp_modify_by = '" & session('userid') & "', ";
                    $query .= "     dsp_modify_dt = NOW() ";
                    $query .= " WHERE dsp_nopb = '" & $noPB & "' ";
                    $query .= " AND dsp_kodemember = '" & $kdMember & "' ";
                    DB::update($query);

                    //UPDATE TBTR_AWB_IPP - TRXID
                    $query = '';
                    $query .= "UPDATE tbtr_awb_ipp ";
                    $query .= "   SET awi_status = 'BATAL', ";
                    $query .= "       awi_alasanbatal = '" & $alasanBatal & "', ";
                    $query .= "       awi_modify_by = '" & session('userid') & "', ";
                    $query .= "       awi_modify_dt = NOW() ";
                    $query .= " WHERE awi_nopb = '" & $noPB & "' ";
                    $query .= "   AND awi_noorder = '" & $trxid & "' ";
                    $query .= "   AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                    $query .= "   AND awi_kodemember = '" & $kdMember & "' ";
                    DB::update($query);
                }
            }

        }catch(\Exception $e){

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }


    }

    private function reCreateAWB_KLIK($kdMember, $noTrans, $tglTrans, $noPB, $alasanBatal){
        //* GET API IPP x SPI
        $dt = DB::select("SELECT ws_url FROM tbmaster_webservice WHERE ws_nama = 'IPP_KLIK'");
        if(count($dt) == 0 || $dt[0]->ws_url == ''){
            $message = 'API IPP SPI tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        //* GET CREDENTIAL API IPP x SPI
        $dt = DB::select("SELECT cre_name, cre_key FROM tbmaster_credential WHERE cre_type = 'IPP_KLIK'");
        if(count($dt) == 0){
            $message = 'CREDENTIAL API IPP x SPI tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $apiName = $dt[0]->cre_name;
        $apiKey = $dt[0]->cre_key;

        $splitTrans = explode("/", $noPB);
        $trxid = $splitTrans[0];
        $newTrxid = "A" . $trxid;

        //* HIT API RE-CREATE AWB
        $urlKLIK = '/recreateawb';

        $postData = [
            'trxid' => $trxid,
            'newTrxid' => $newTrxid,
        ];

        $strResponse = $this->ConToWebServiceNew($urlKLIK, $apiName, $apiKey, $postData);

        $strPostData = null;
        $strResponse = null;

        $query = '';
        $query .= "INSERT INTO log_createawb ( ";
        $query .= " nopb,  ";
        $query .= " notrans,  ";
        $query .= " url,  ";
        $query .= " param,  ";
        $query .= " response,  ";
        $query .= " create_dt ";
        $query .= ") ";
        $query .= "VALUES ( ";
        $query .= " '" . $noPB . "', ";
        $query .= " '" . $newTrxid . "', ";
        $query .= " '" . $urlKLIK . "', ";
        $query .= " '" . $strPostData . "', "; //! dummy result dari ConToWebServiceNew
        $query .= " '" . $strResponse . "', "; //! dummy result dari ConToWebServiceNew
        $query .= " NOW() ";
        $query .= ") ";
        DB::insert($query);

        try{

            //! dummy result dari ConToWebServiceNew
            $response_code = null;
            $response_message = '';
            $noAWB = null;
            $cost = null;
            $pincode = null;

            $this->updateDeliveryInfo_KLIK($kdMember, $noTrans, $tglTrans, $noPB, $newTrxid);

            if($response_code == 200){
                $query = '';
                $query .= " SELECT awi_noawb ";
                $query .= " FROM tbtr_awb_ipp ";
                $query .= " WHERE awi_nopb = '" . $noPB . "' ";
                $query .= " AND awi_noorder = '" . $newTrxid . "' ";
                $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= " AND awi_kodemember = '" . $kdMember . "' ";
                $dt = DB::select($query);

                $UserMODUL = session('userid');
                if(count($dt) == 0){
                    $query = '';
                    $query .= " INSERT INTO tbtr_awb_ipp ( ";
                    $query .= "   awi_noawb, ";
                    $query .= "   awi_nopb, ";
                    $query .= "   awi_noorder, ";
                    $query .= "   awi_tglorder, ";
                    $query .= "   awi_kodemember, ";
                    $query .= "   awi_cost, ";
                    $query .= "   awi_ref_noorder, ";
                    $query .= "   awi_pincode, ";
                    $query .= "   awi_tipetransaksi, ";
                    $query .= "   awi_create_by, ";
                    $query .= "   awi_create_dt ";
                    $query .= " ) ";
                    $query .= " VALUES ( ";
                    $query .= "   '" . $noAWB . "', ";
                    $query .= "   '" . $noPB . "', ";
                    $query .= "   '" . $newTrxid . "', ";
                    $query .= "   TO_DATE('" . $tglTrans . "','DD-MM-YYYY'), ";
                    $query .= "   '" . $kdMember . "', ";
                    $query .= "   " . $cost . ", ";
                    $query .= "   '" . $trxid . "', ";
                    $query .= "   '" . $pincode . "', ";
                    $query .= "   '" . session('flagSPI') == true ? 'SPI': 'KLIK IGR' . "', ";
                    $query .= "   '" . $UserMODUL . "', ";
                    $query .= "   NOW() ";
                    $query .= " ) ";
                    DB::insert($query);

                }else{
                    $query = '';
                    $query .= " UPDATE tbtr_awb_ipp ";
                    $query .= " SET awi_cost = " . $cost . ", ";
                    $query .= "     awi_pincode = '" . $pincode . "', ";
                    $query .= "     awi_modify_by = '" . $UserMODUL . "', ";
                    $query .= "     awi_modify_dt = NOW() ";
                    $query .= " WHERE awi_nopb = '" . $noPB . "' ";
                    $query .= " AND awi_noorder = '" . $newTrxid . "' ";
                    $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                    $query .= " AND awi_kodemember = '" . $kdMember . "' ";
                    DB::insert($query);
                }

                //* UPDATE TBTR_ALAMAT_MM
                $query = '';
                $query .= "UPDATE tbtr_alamat_mm ";
                $query .= "   SET amm_noawb = '" & $noAWB & "' ";
                $query .= " WHERE amm_nopb = '" & $noPB & "' ";
                $query .= "   AND amm_notrans = '" & $noTrans & "' ";

                //UPDATE TBTR_OBI_H - OBI_TRXIDNEW
                $query = '';
                $query .= "UPDATE tbtr_obi_h ";
                $query .= "   SET obi_trxidnew = '" & $newTrxid & "' ";
                $query .= " WHERE obi_nopb = '" & $noPB & "' ";
                $query .= "   AND obi_notrans = '" & $noTrans & "' ";
                $query .= "   AND DATE_TRUNC('DAY',obi_tgltrans) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= "   AND obi_kdmember = '" & $kdMember & "' ";
                DB::update($query);

                //UPDATE FLAG BATAL DELIVERY
                $query = '';
                $query .= " UPDATE TBTR_DELIVERY_SPI ";
                $query .= " SET del_flagbatal = 'Y' ";
                $query .= " WHERE del_nopb = '" & $noPB & "' ";
                $query .= " AND del_kodemember = '" & $kdMember & "' ";
                DB::update($query);

                //UPDATE STATUS DSP SPI
                $query = '';
                $query .= " UPDATE TBTR_DSP_SPI ";
                $query .= " SET dsp_status = 'DSP', ";
                $query .= "     dsp_nolisting = 'NULL', ";
                $query .= "     dsp_modify_by = '" & $UserMODUL & "', ";
                $query .= "     dsp_modify_dt = NOW() ";
                $query .= " WHERE dsp_nopb = '" & $noPB & "' ";
                $query .= " AND dsp_kodemember = '" & $kdMember & "' ";
                DB::update($query);

                //UPDATE TBTR_AWB_IPP - TRXID
                $query = '';
                $query .= "UPDATE tbtr_awb_ipp ";
                $query .= "   SET awi_status = 'BATAL', ";
                $query .= "       awi_alasanbatal = '" & $alasanBatal & "', ";
                $query .= "       awi_modify_by = '" & $UserMODUL & "', ";
                $query .= "       awi_modify_dt = NOW() ";
                $query .= " WHERE awi_nopb = '" & $noPB & "' ";
                $query .= "   AND awi_noorder = '" & $trxid & "' ";
                $query .= "   AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= "   AND awi_kodemember = '" & $kdMember & "' ";
                DB::update($query);

            }elseif($response_code == 400 AND str_contains(strtoupper($response_message), 'BATAS MAKSIMAL PEMBUATAN AWB')){
                $query = '';
                $query .= " SELECT awi_noawb, awi_cost, awi_pincode ";
                $query .= " FROM tbtr_awb_ipp ";
                $query .= " WHERE awi_nopb = '" & $noPB & "' ";
                $query .= " AND awi_noorder = '" & $newTrxid & "' ";
                $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= " AND awi_kodemember = '" & $kdMember & "' ";
                $dt = DB::select($query);

                if(count($dt) > 0 ){

                    //UPDATE TBTR_ALAMAT_MM
                    $query = '';
                    $query .= "UPDATE tbtr_alamat_mm ";
                    $query .= "   SET amm_noawb = '" & $noAWB & "' ";
                    $query .= " WHERE amm_nopb = '" & $noPB & "' ";
                    $query .= "   AND amm_notrans = '" & $noTrans & "' ";
                    DB::update($query);

                    //UPDATE TBTR_OBI_H - OBI_TRXIDNEW
                    $query = '';
                    $query .= "UPDATE tbtr_obi_h ";
                    $query .= "   SET obi_trxidnew = '" & $newTrxid & "' ";
                    $query .= " WHERE obi_nopb = '" & $noPB & "' ";
                    $query .= "   AND obi_notrans = '" & $noTrans & "' ";
                    $query .= "   AND DATE_TRUNC('DAY',obi_tgltrans) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                    $query .= "   AND obi_kdmember = '" & $kdMember & "' ";
                    DB::update($query);

                    //UPDATE FLAG BATAL DELIVERY
                    $query = '';
                    $query .= " UPDATE TBTR_DELIVERY_SPI ";
                    $query .= " SET del_flagbatal = 'Y' ";
                    $query .= " WHERE del_nopb = '" & $noPB & "' ";
                    $query .= " AND del_kodemember = '" & $kdMember & "' ";
                    DB::update($query);

                    //UPDATE STATUS DSP SPI
                    $query = '';
                    $query .= " UPDATE TBTR_DSP_SPI ";
                    $query .= " SET dsp_status = 'DSP', ";
                    $query .= "     dsp_nolisting = 'NULL', ";
                    $query .= "     dsp_modify_by = '" & session('userid') & "', ";
                    $query .= "     dsp_modify_dt = NOW() ";
                    $query .= " WHERE dsp_nopb = '" & $noPB & "' ";
                    $query .= " AND dsp_kodemember = '" & $kdMember & "' ";
                    DB::update($query);

                    //UPDATE TBTR_AWB_IPP - TRXID
                    $query = '';
                    $query .= "UPDATE tbtr_awb_ipp ";
                    $query .= "   SET awi_status = 'BATAL', ";
                    $query .= "       awi_alasanbatal = '" & $alasanBatal & "', ";
                    $query .= "       awi_modify_by = '" & session('userid') & "', ";
                    $query .= "       awi_modify_dt = NOW() ";
                    $query .= " WHERE awi_nopb = '" & $noPB & "' ";
                    $query .= "   AND awi_noorder = '" & $trxid & "' ";
                    $query .= "   AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                    $query .= "   AND awi_kodemember = '" & $kdMember & "' ";
                    DB::update($query);
                }
            }

        }catch(\Exception $e){

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }


    }

    private function updateDeliveryInfo_SPI($kdMember, $noTrans, $tglTrans, $noPB, $trxidnew = ''){
        //* GET API IPP x SPI
        $dt = DB::select("SELECT ws_url, ws_aktif FROM tbmaster_webservice WHERE ws_nama = 'IPP_SPI'");
        if(count($dt) == 0 || $dt[0]->ws_url == ''){
            $message = 'API IPP SPI tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $urlSPI = $dt[0]->ws_url;
        $flagAktif = $dt[0]->ws_aktif;

        if($flagAktif == 0){
            return true;
        }

        //* GET CREDENTIAL API IPP x SPI
        $dt = DB::select("SELECT cre_name, cre_key FROM tbmaster_credential WHERE cre_type = 'IPP_SPI'");
        if(count($dt) == 0){
            $message = 'CREDENTIAL API IPP x SPI tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $apiName = $dt[0]->cre_name;
        $apiKey = $dt[0]->cre_key;

        $splitTrans = explode("/", $noPB);
        $trxid = $splitTrans[0];

        $query = '';
        $query .= " SELECT  ";
        $query .= "   TRIM(obi_tipebayar) tipebayar,  ";
        $query .= "   COALESCE(dsp_totalbayar,0) nilaidsp, ";
        $query .= "   TO_CHAR(COALESCE(obi_draftstruk,current_timestamp),'YYYY-MM-DD HH24:MI:SS') tgldsp ";
        $query .= " FROM tbtr_obi_h ";
        $query .= " LEFT JOIN tbtr_dsp_spi ";
        $query .= " ON dsp_nopb = obi_nopb ";
        $query .= " AND dsp_tglpb = obi_tglpb ";
        $query .= " AND dsp_notrans = obi_notrans ";
        $query .= " AND dsp_kodemember = obi_kdmember ";
        $query .= " WHERE obi_nopb = '" . $noPB . "' ";
        $query .= " AND obi_notrans = '" . $noTrans . "' ";
        $query .= " AND DATE_TRUNC('DAY',obi_tgltrans) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
        $query .= " AND obi_kdmember = '" . $kdMember . "' ";
        $dt = DB::select($query);

        if(count($dt) == 0){
            $message = "Data PB $noPB Tidak ditemukan.";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $codvalue = '';
        $codpaymentcode = '';
        $flagnonpaid = '0';
        if(str_contains(strtoupper($dt[0]->tipebayar), 'COD')){
            $codvalue = $dt[0]->nilaidsp;
            $codpaymentcode = $noPB;

            if($dt[0]->nilaidsp <= 0){
                $flagnonpaid = '1';
            }
        }

        $postData = [
            'trxid' => $trxid,
            'codvalue' => $codvalue,
            'codpaymentcode' => $codpaymentcode,
            'flagnonpaid' => $flagnonpaid,
        ];

        //* HIT API UPDATE DELIVERY INFO
        $urlSPI = "/updatedeliveryinfo";

        $strResponse = $this->ConToWebServiceNew($urlSPI, $apiName, $apiKey, $postData);

        //! dummy result dari ConToWebServiceNew
        $strPostData = null;
        $strResponse = '';

        $query = '';
        $query .= "INSERT INTO log_createawb ( ";
        $query .= " nopb,  ";
        $query .= " notrans,  ";
        $query .= " url,  ";
        $query .= " param,  ";
        $query .= " response,  ";
        $query .= " create_dt ";
        $query .= ") ";
        $query .= "VALUES ( ";
        $query .= " '" . $noPB . "', ";
        $query .= " '" . $trxid . "', ";
        $query .= " '" . $urlSPI . "', ";
        $query .= " '" . $strPostData . "', ";
        $query .= " '" . substr(str_replace("'", "''", $strResponse), 0, 2000) . "', ";
        $query .= " NOW() ";
        $query .= ") ";
        DB::insert($query);

        try{

             //! dummy result dari ConToWebServiceNew
             $response_code = null;
             $noAWB = null;
             $cost = null;
             $pincode = null;

             if($response_code == 200){

                //* UPDATE TBTR_ALAMAT_MM
                $query = '';
                $query .= "UPDATE tbtr_alamat_mm ";
                $query .= "   SET amm_noawb = '" . $noAWB . "' ";
                $query .= " WHERE amm_nopb = '" . $noPB . "' ";
                $query .= "   AND amm_notrans = '" . $noTrans . "' ";
                DB::update($query);

                //* UPDATE TBTR_OBI_H - OBI_TRXID
                $query = '';
                $query .= "UPDATE tbtr_obi_h ";
                if($trxidnew == ''){
                    $query .= "   SET obi_trxid = '" . $trxid . "' ";
                }else{
                    $query .= "   SET obi_trxidnew = '" . $trxid . "' ";
                }
                $query .= " WHERE obi_nopb = '" . $noPB . "' ";
                $query .= "   AND obi_notrans = '" . $noTrans . "' ";
                $query .= "   AND DATE_TRUNC('DAY',obi_tgltrans) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= "   AND obi_kdmember = '" . $kdMember . "' ";
                DB::update($query);

                //* INSERT INTO TBTR_AWB_IPP
                $query = '';
                $query .= " SELECT awi_noawb ";
                $query .= " FROM tbtr_awb_ipp ";
                $query .= " WHERE awi_nopb = '" . $noPB . "' ";
                $query .= " AND awi_noorder = '" . $trxid . "' ";
                $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= " AND awi_kodemember = '" . $kdMember . "' ";
                $dt = DB::select($query);

                if(count($dt) == 0){
                    $query = '';
                    $query .= " INSERT INTO tbtr_awb_ipp ( ";
                    $query .= "   awi_noawb, ";
                    $query .= "   awi_nopb, ";
                    $query .= "   awi_noorder, ";
                    $query .= "   awi_tglorder, ";
                    $query .= "   awi_kodemember, ";
                    $query .= "   awi_cost, ";
                    $query .= "   awi_pincode, ";
                    $query .= "   awi_tipetransaksi, ";
                    $query .= "   awi_create_by, ";
                    $query .= "   awi_create_dt ";
                    $query .= " ) ";
                    $query .= " VALUES ( ";
                    $query .= "   '" . $noAWB . "', ";
                    $query .= "   '" . $noPB . "', ";
                    $query .= "   '" . $trxid . "', ";
                    $query .= "   TO_DATE('" . $tglTrans . "','DD-MM-YYYY'), ";
                    $query .= "   '" . $kdMember . "', ";
                    $query .= "   " . $cost . ", ";
                    $query .= "   '" . $pincode . "', ";
                    $query .= "   '" . session('flagSPI') == true ? 'SPI' : 'KLIKIGR' . "', ";
                    $query .= "   '" . session('userid') . "', ";
                    $query .= "   NOW() ";
                    $query .= " ) ";
                    DB::insert($query);

                }else{
                    $query = '';
                    $query .= " UPDATE tbtr_awb_ipp ";
                    $query .= " SET awi_cost = " . $cost . ", ";
                    $query .= "     awi_pincode = '" . $pincode . "', ";
                    $query .= "     awi_modify_by = '" . session('userid') . "', ";
                    $query .= "     awi_modify_dt = NOW() ";
                    $query .= " WHERE awi_nopb = '" . $noPB . "' ";
                    $query .= " AND awi_noorder = '" . $trxid . "' ";
                    $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                    $query .= " AND awi_kodemember = '" . $kdMember . "' ";
                    DB::update($query);
                }
             }

        }catch(\Exception $e){

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

    }

    private function updateDeliveryInfo_KLIK($kdMember, $noTrans, $tglTrans, $noPB, $trxidnew = ''){
        //* GET API IPP x SPI
        $dt = DB::select("SELECT ws_url, ws_aktif FROM tbmaster_webservice WHERE ws_nama = 'IPP_KLIK'");
        if(count($dt) == 0 || $dt[0]->ws_url == ''){
            $message = 'API IPP Klik tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $urlSPI = $dt[0]->ws_url;
        $flagAktif = $dt[0]->ws_aktif;

        if($flagAktif == 0){
            return true;
        }

        //* GET CREDENTIAL API IPP x SPI
        $dt = DB::select("SELECT cre_name, cre_key FROM tbmaster_credential WHERE cre_type = 'IPP_KLIK'");
        if(count($dt) == 0){
            $message = 'CREDENTIAL API IPP x SPI tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $apiName = $dt[0]->cre_name;
        $apiKey = $dt[0]->cre_key;

        $splitTrans = explode("/", $noPB);
        $trxid = $splitTrans[0];

        $query = '';
        $query .= " SELECT  ";
        $query .= "   TRIM(obi_tipebayar) tipebayar,  ";
        $query .= "   COALESCE(dsp_totalbayar,0) nilaidsp, ";
        $query .= "   TO_CHAR(COALESCE(obi_draftstruk,current_timestamp),'YYYY-MM-DD HH24:MI:SS') tgldsp ";
        $query .= " FROM tbtr_obi_h ";
        $query .= " LEFT JOIN tbtr_dsp_spi ";
        $query .= " ON dsp_nopb = obi_nopb ";
        $query .= " AND dsp_tglpb = obi_tglpb ";
        $query .= " AND dsp_notrans = obi_notrans ";
        $query .= " AND dsp_kodemember = obi_kdmember ";
        $query .= " WHERE obi_nopb = '" . $noPB . "' ";
        $query .= " AND obi_notrans = '" . $noTrans . "' ";
        $query .= " AND DATE_TRUNC('DAY',obi_tgltrans) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
        $query .= " AND obi_kdmember = '" . $kdMember . "' ";
        $dt = DB::select($query);

        if(count($dt) == 0){
            $message = "Data PB $noPB Tidak ditemukan.";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $codvalue = '';
        $codpaymentcode = '';
        $flagnonpaid = '0';
        if(str_contains(strtoupper($dt[0]->tipebayar), 'COD')){
            $codvalue = $dt[0]->nilaidsp;
            $codpaymentcode = $noPB;

            if($dt[0]->nilaidsp <= 0){
                $flagnonpaid = '1';
            }
        }

        $postData = [
            'trxid' => $trxid,
            'codvalue' => $codvalue,
            'codpaymentcode' => $codpaymentcode,
            'flagnonpaid' => $flagnonpaid,
        ];

        //* HIT API UPDATE DELIVERY INFO
        $urlKlik = "/updatedeliveryinfo";

        $strResponse = $this->ConToWebServiceNew($urlKlik, $apiName, $apiKey, $postData);

        //! dummy result dari ConToWebServiceNew
        $strPostData = null;
        $strResponse = '';

        $query = '';
        $query .= "INSERT INTO log_createawb ( ";
        $query .= " nopb,  ";
        $query .= " notrans,  ";
        $query .= " url,  ";
        $query .= " param,  ";
        $query .= " response,  ";
        $query .= " create_dt ";
        $query .= ") ";
        $query .= "VALUES ( ";
        $query .= " '" . $noPB . "', ";
        $query .= " '" . $trxid . "', ";
        $query .= " '" . $urlKlik . "', ";
        $query .= " '" . $strPostData . "', ";
        $query .= " '" . substr(str_replace("'", "''", $strResponse), 0, 2000) . "', ";
        $query .= " NOW() ";
        $query .= ") ";
        DB::insert($query);

        try{

             //! dummy result dari ConToWebServiceNew
             $response_code = null;
             $noAWB = null;
             $cost = null;
             $pincode = null;

             if($response_code == 200){

                //* UPDATE TBTR_ALAMAT_MM
                $query = '';
                $query .= "UPDATE tbtr_alamat_mm ";
                $query .= "   SET amm_noawb = '" . $noAWB . "' ";
                $query .= " WHERE amm_nopb = '" . $noPB . "' ";
                $query .= "   AND amm_notrans = '" . $noTrans . "' ";
                DB::update($query);

                //* UPDATE TBTR_OBI_H - OBI_TRXID
                $query = '';
                $query .= "UPDATE tbtr_obi_h ";
                if($trxidnew == ''){
                    $query .= "   SET obi_trxid = '" . $trxid . "' ";
                }else{
                    $query .= "   SET obi_trxidnew = '" . $trxid . "' ";
                }
                $query .= " WHERE obi_nopb = '" . $noPB . "' ";
                $query .= "   AND obi_notrans = '" . $noTrans . "' ";
                $query .= "   AND DATE_TRUNC('DAY',obi_tgltrans) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= "   AND obi_kdmember = '" . $kdMember . "' ";
                DB::update($query);

                //* INSERT INTO TBTR_AWB_IPP
                $query = '';
                $query .= " SELECT awi_noawb ";
                $query .= " FROM tbtr_awb_ipp ";
                $query .= " WHERE awi_nopb = '" . $noPB . "' ";
                $query .= " AND awi_noorder = '" . $trxid . "' ";
                $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                $query .= " AND awi_kodemember = '" . $kdMember . "' ";
                $dt = DB::select($query);

                if(count($dt) == 0){
                    $query = '';
                    $query .= " INSERT INTO tbtr_awb_ipp ( ";
                    $query .= "   awi_noawb, ";
                    $query .= "   awi_nopb, ";
                    $query .= "   awi_noorder, ";
                    $query .= "   awi_tglorder, ";
                    $query .= "   awi_kodemember, ";
                    $query .= "   awi_cost, ";
                    $query .= "   awi_pincode, ";
                    $query .= "   awi_tipetransaksi, ";
                    $query .= "   awi_create_by, ";
                    $query .= "   awi_create_dt ";
                    $query .= " ) ";
                    $query .= " VALUES ( ";
                    $query .= "   '" . $noAWB . "', ";
                    $query .= "   '" . $noPB . "', ";
                    $query .= "   '" . $trxid . "', ";
                    $query .= "   TO_DATE('" . $tglTrans . "','DD-MM-YYYY'), ";
                    $query .= "   '" . $kdMember . "', ";
                    $query .= "   " . $cost . ", ";
                    $query .= "   '" . $pincode . "', ";
                    $query .= "   '" . session('flagSPI') == true ? 'SPI' : 'KLIKIGR' . "', ";
                    $query .= "   '" . session('userid') . "', ";
                    $query .= "   NOW() ";
                    $query .= " ) ";
                    DB::insert($query);

                }else{
                    $query = '';
                    $query .= " UPDATE tbtr_awb_ipp ";
                    $query .= " SET awi_cost = " . $cost . ", ";
                    $query .= "     awi_pincode = '" . $pincode . "', ";
                    $query .= "     awi_modify_by = '" . session('userid') . "', ";
                    $query .= "     awi_modify_dt = NOW() ";
                    $query .= " WHERE awi_nopb = '" . $noPB . "' ";
                    $query .= " AND awi_noorder = '" . $trxid . "' ";
                    $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
                    $query .= " AND awi_kodemember = '" . $kdMember . "' ";
                    DB::update($query);
                }
             }

        }catch(\Exception $e){

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

    }


    private function cekNotifMaxSerahTerima($flagManual = false){

        $query = '';
        $query .= " SELECT ";
        $query .= "   TO_CHAR(obi_mindeliverytime,'DD-MM-YYYY HH24:MI:SS') TGL_PB, ";
        $query .= "   obi_kdmember MEMBER, ";
        $query .= "   obi_nopb NO_PB, ";
        $query .= "   obi_notrans NO_TRANS, ";
        $query .= "   TO_CHAR(obi_maxdeliverytime,'DD-MM-YYYY HH24:MI:SS') MAX_SERAHTERIMA, ";
        $query .= "   obi_kdekspedisi EKSPEDISI ";
        $query .= " FROM tbtr_obi_h ";
        $query .= " JOIN tbmaster_customer ";
        $query .= "   ON cus_kodemember = obi_kdmember ";
        $query .= " LEFT JOIN tbtr_serahterima_ipp ";
        $query .= "   ON sti_codpaymentcode = obi_nopb ";
        $query .= "  AND sti_tipeproses = 'PICKUP' ";
        $query .= " WHERE SUBSTR(COALESCE(obi_recid,'0'),1,1) IN ('0','1','2','3') ";
        $query .= "   AND obi_freeongkir <> 'T' ";
        $query .= "   AND obi_kdekspedisi IS NOT NULL ";
        $query .= "   AND UPPER(obi_kdekspedisi) LIKE '%INDOPAKET%' ";
        $query .= "   AND obi_maxdeliverytime IS NOT NULL ";
        // '$query .= "   AND TO_DATE( ";
        // '$query .= "         TO_CHAR(obi_maxdeliverytime,'DD-MM-YYYY') || ' 12:30:00',  ";
        // '$query .= "         'DD-MM-YYYY HH24:MI:SS' ";
        // '$query .= "       ) <= NOW() ";
        $query .= "   AND (obi_maxdeliverytime - interval '90' minute) <= NOW() ";
        $query .= "   AND DATE_TRUNC('DAY',obi_tgltrans) >= TO_DATE('01-07-2023','DD-MM-YYYY') ";
        $query .= "   AND sti_tglserahterima IS NULL ";

        if(session('flagSPI') == true){
            $query .= "   AND UPPER(obi_nopb) LIKE '%SPI%' ";
        }else{
            $query .= "   AND UPPER(obi_nopb) NOT LIKE '%SPI%' ";
        }

        $query .= " ORDER BY DATE_TRUNC('DAY',obi_mindeliverytime) DESC, no_trans ASC ";

        $mydt = DB::select($query);

        if(count($mydt) == 0 AND $flagManual == true){
            $message = 'Tidak ada data PB yang lebih dari Tgl Max Serah Terima.';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        if(count($mydt) > 0){
            //* form -> LstNotifMaxSerahTerima
        }
    }

    private function cekPBAkanBatal(){
        $query = '';
        $query .= " SELECT obi_kdmember KodeMember, obi_nopb NoPB, obi_tgltrans TglPB ";
        $query .= " FROM tbtr_obi_h ";
        $query .= " WHERE obi_tgltrans <= DATE_TRUNC('DAY',CURRENT_DATE) - INTERVAL '1 day' ";
        $query .= "     AND COALESCE(obi_recid,'0') IN ('0') "; //('0','1','2','3','7'

        if(session('flagSPI') == true){
            $query .= "     AND UPPER(obi_nopb) LIKE '%SPI%' ";
        }else{
            $query .= "     AND UPPER(obi_nopb) NOT LIKE '%SPI%' ";
        }

        $query .= " ORDER BY obi_tgltrans DESC, obi_nopb ASC ";

        $mydt = DB::select($query);

        if(count($mydt) > 0){
            //* buka form -> LstPBAkanBatal
        }
    }

    private function cekItemBatal($showFrm){
        $query = '';
        $query .= "select distinct d.obi_notrans NO_TRANSAKSI, d.obi_tgltrans TGL_TRANSAKSI";
        $query .= "from tbtr_obi_d d, tbhistory_obi_batal b ";
        $query .= "where d.obi_recid = '1'";
        $query .= "and b.obi_qtyrealisasi > 0";
        $query .= "and d.obi_notrans = b.obi_notrans";
        $query .= "and d.obi_tgltrans = b.obi_tgltrans";
        $query .= "group by d.obi_notrans, d.obi_tgltrans";
        $mydt = DB::select($query);

        if($showFrm == true){
            if(count($mydt) > 0){
                //* form -> LstNonValidasi

                // labelItemBatal.Visible = True
                // btnPBBatal.Visible = True
            }else{
                // labelItemBatal.Visible = False
                // btnPBBatal.Visible = False
            }

        }else{
            if(count($mydt) > 0){
                // labelItemBatal.Visible = True
                // btnPBBatal.Visible = True
            }else{
                // labelItemBatal.Visible = False
                // btnPBBatal.Visible = False
            }
        }
    }

    private function PrintNotaIIK($NoTrans, $noContainer, $kodeWeb, $nopb, $tglpb){
        $query = '';
        $query .= "SELECT pr.prd_prdcd plu, pr.prd_deskripsipendek desk, pr.prd_unit unit, ";
        $query .= "     (po.pobi_qty / (CASE WHEN pr.prd_unit = 'KG' THEN 1 ELSE pr.prd_frac END)) as qty ";
        $query .= "FROM TBTR_PACKING_OBI po, TBTR_OBI_D d, TBMASTER_PRODMAST pr, tbtr_obi_h h ";
        $query .= "WHERE pobi_prdcd = obi_prdcd ";
        $query .= "    AND d.obi_notrans = h.obi_notrans ";
        $query .= "    AND d.obi_tgltrans = h.obi_tgltrans ";
        $query .= "    AND pr.prd_prdcd = po.pobi_prdcd ";
        $query .= "    AND pobi_notransaksi = d.obi_notrans ";
        $query .= "    AND pobi_nocontainer = '" . $noContainer . "' ";
        $query .= "    AND pobi_notransaksi = '" . $NoTrans . "' ";
        $query .= "    AND h.obi_nopb = '" . $nopb . "' ";
        $query .= "    AND pobi_qty > 0 ";
        $dtDetailBrg = DB::select($query);

        $query = '';
        $query .= "SELECT amm_kodemember kdmember, ";
        $query .= "       CASE WHEN LENGTH(COALESCE(amm_namapenerima, cus_namamember)) > 30 THEN SUBSTR(COALESCE(amm_namapenerima, cus_namamember),0,27) || '...' ELSE COALESCE(amm_namapenerima, cus_namamember) END nama, ";
        $query .= "       COALESCE(amm_hp, COALESCE(cus_tlpmember, cus_hpmember)) telp, ";
        $query .= "       amm_namaalamat alamat, ";
        $query .= "       COALESCE(amm_noawb, 'SJ' || TO_CHAR(obi_tgltrans, 'YYMMDD') || COALESCE(obi_kdstation,'00') || COALESCE(obi_nostruk,'00000')) no_awb, ";

        if(session('flagIGR') == true AND session('flagSPI') == false){
            $query .= "       COALESCE(TO_CHAR(obi_notrans), '-') as nopik ";
        }else{
            $query .= "       COALESCE(TO_CHAR(obi_nopick), '-') as nopik ";
        }
        $query .= "FROM tbtr_obi_h ";
        $query .= "JOIN tbtr_alamat_mm ";
        $query .= "  ON obi_nopb = amm_nopb ";
        $query .= "  AND DATE_TRUNC('DAY',obi_tglpb) = DATE_TRUNC('DAY',amm_tglpb) ";
        $query .= "  AND obi_kdmember = amm_kodemember ";
        $query .= "JOIN tbmaster_customer ";
        $query .= "  ON amm_kodemember = cus_kodemember ";
        $query .= "WHERE obi_notrans = '" . $NoTrans . "' ";
        $query .= "  AND obi_nopb = '" . $nopb . "' ";
        $dtAlamat = DB::select($query);

        // str &= "           INFORMASI ISI KOLI           " & vbCrLf
        // str &= "========================================" & vbCrLf
        // str &= vbNewLine

        // str &= "Member :" & _dtAlamat.Rows(0).Item("kdmember").ToString & vbCrLf
        // str &= "        " & _dtAlamat.Rows(0).Item("nama").ToString & vbCrLf
        // str &= "No.PB  :" & nopb & vbCrLf
        // str &= "Tgl.PB :" & tglpb & vbCrLf
        // str &= vbNewLine

        // str &= "Ref. No SJ :" & _dtAlamat.Rows(0).Item("no_awb").ToString & vbCrLf
        // str &= "No.Pick    :" & _dtAlamat.Rows(0).Item("nopik").ToString & vbCrLf
        // str &= "No.Koli    :" & noContainer & vbCrLf
        // str &= vbNewLine

        // str &= "========================================" & vbCrLf
        // str &= "No.   PLU   Nama Barang          SAT QTY" & vbCrLf
        // str &= "________________________________________" & vbCrLf
        // str &= vbNewLine

        // Dim counter As Integer = 0
        // For i As Integer = 0 To _dtDetailBrg.Rows.Count - 1
        //     str &= "" & (counter + 1).ToString.PadLeft(3, " ").ToString & " "
        //     str &= _dtDetailBrg.Rows(i).Item("plu").ToString & " "
        //     str &= _dtDetailBrg.Rows(i).Item("desk").ToString.PadRight(20, " ") & " "
        //     str &= _dtDetailBrg.Rows(i).Item("unit").ToString.PadLeft(3, " ")
        //     str &= _dtDetailBrg.Rows(i).Item("qty").ToString.PadLeft(4, " ")
        //     str &= vbNewLine

        //     counter += 1
        // Next

        // str &= "________________________________________" & vbCrLf
        // str &= vbNewLine
        // str &= "Total : " & counter & " item " & vbCrLf

        // Dim str3 As String = ""

        // str3 &= "========================================" & vbCrLf
        // str3 &= vbNewLine

        //! ADA CETAK PRINTER CUMA BINGUNG (FR KEVIN) 03/05/2024
    }

    private function rptSuratJalan($dgv_notrans, $dgv_kodeWeb, $dgv_nopb, $dgv_memberigr, $dgv_freeongkir, $dgv_flagBayar, $dtTrans){
        $notrans = $dgv_notrans;
        $kodeWeb = $dgv_kodeWeb;
        $nopb = $dgv_nopb;
        $kodeMember = $dgv_memberigr;

        $urlAWB = '';
        $noContainer = '';
        $flagAktif = 0;
        $flagSkipIPP = false;

        //* CHECK KURIR INDOGROSIR
        $query = '';
        $query .= " SELECT obi_kdekspedisi  ";
        $query .= " FROM tbtr_obi_h ";
        $query .= " WHERE obi_nopb = '" . $nopb . "' ";
        $query .= " AND obi_notrans = '" . $notrans . "' ";
        $query .= " AND obi_kdmember = '" . $kodeMember . "' ";
        $dtCek = DB::select($query);

        if(count($dtCek) == 0){
            $message = 'Data PB Tidak Ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        if($kodeWeb == 'CORP' OR $kodeWeb == 'TMI' OR $dgv_freeongkir == 'T'){
            $flagSkipIPP = true;
        }

        if($flagSkipIPP == false){
            $dt = DB::select("SELECT ws_aktif FROM tbmaster_webservice WHERE ws_nama = 'IPP_KLIK'");
            if(count($dt) AND $dt[0]->ws_aktif != ''){
                $flagAktif = $dt[0]->ws_aktif;
            }

            if ($flagAktif == 0 ||
                strpos(strtoupper($dtCek[0]->obi_kdekspedisi), "KURIR INDOGROSIR") !== false ||
                strpos(strtoupper($dtCek[0]->obi_kdekspedisi), "AMBIL DI") !== false ||
                strpos(strtoupper($dtCek[0]->obi_kdekspedisi), "EKSPEDISI LAINNYA") !== false ||
                strpos(strtoupper($dtCek[0]->obi_kdekspedisi), "MOBIL ENGKEL/DOUBLE") !== false)
            {
                //* CREATE AWB KALAU KURIR INDOGROSIR
                if($dgv_flagBayar == 'Y' AND session('server') == 'PRODUCTION'){
                    if($this->ORADataFound('tbtr_alamat_mm', "amm_nopb = '$nopb' AND amm_notrans = '$notrans' AND amm_noawb IS NULL") == false){
                        $dtCek = DB::select("SELECT ws_url FROM tbmaster_webservice WHERE ws_nama = 'KLIKIGR'");

                        if(count($dtCek) > 0){
                            $urlAWB = $dtCek[0]->ws_url;

                            $splitTrans = explode("/", $dgv_nopb);
                            $notrx = $splitTrans[0];

                            $postData = [
                                'trxid' => $notrx,
                            ];

                            $ret = $this->ConToWebService($urlAWB, $postData);

                            //* LOG_CREATEAWB
                            $query = '';
                            $query .= "INSERT INTO log_createawb ( ";
                            $query .= " nopb,  ";
                            $query .= " notrans,  ";
                            $query .= " url,  ";
                            $query .= " param,  ";
                            $query .= " response,  ";
                            $query .= " create_dt ";
                            $query .= ") ";
                            $query .= "VALUES ( ";
                            $query .= " '" . $dgv_nopb . "', ";
                            $query .= " '" . $notrx . "', ";
                            $query .= " '" . $urlAWB . "', ";
                            $query .= " '" . "trxid=" . $notrx . "', ";
                            $query .= " '" . $ret . " ', ";
                            $query .= " NOW() ";
                            $query .= ") ";
                            DB::insert($query);

                            $noAWB = null; //! berasal dari return ConToWebService

                            $query = '';
                            $query .= "UPDATE tbtr_alamat_mm ";
                            $query .= "   SET amm_noawb = '" . $noAWB . "' ";
                            $query .= " WHERE amm_nopb = '" . $dgv_nopb . "' ";
                            $query .= "   AND amm_notrans = '" . $dgv_notrans . "' ";
                            $query .= "   AND amm_noawb IS NULL ";
                            DB::update($query);

                            $query = '';
                            $query .= " SELECT  ";
                            $query .= "   obi_ekspedisi ongkir,  ";
                            $query .= "   CASE WHEN COALESCE(cus_jenismember,'N') = 'T'  ";
                            $query .= "     THEN 'TMI'  ";
                            $query .= "     ELSE UPPER(obi_attribute2)  ";
                            $query .= "   END tipe ";
                            $query .= " FROM tbtr_obi_h ";
                            $query .= " JOIN tbmaster_customer ON obi_kdmember = cus_kodemember ";
                            $query .= " WHERE obi_nopb = '" . $nopb . "' ";
                            $query .= " AND obi_notrans = '" . $notrans . "' ";
                            $query .= " AND obi_kdmember = '" . $kodeMember . "' ";
                            $dtCOST = DB::select($query);

                            $cost = 0;
                            $tipe = 'KLIK';
                            if(count($dtCOST) > 0){
                                $cost = $dtCOST[0]->ongkir;
                                $tipe = $dtCOST[0]->tipe;
                            }

                            $this->insertAWBIPP($noAWB, $nopb, $notrx, $dtTrans, $kodeMember, "", $cost, "", "", "", $tipe);
                        }
                    }
                }

            }else{
                //* CHECK NO AWB SPI x IPP
                if($this->ORADataFound('tbtr_alamat_mm', "amm_nopb = '$nopb' AND amm_notrans = '$notrans' AND amm_noawb IS NOT NULL") == false){
                    $message = 'Belum Ada No. AWB, Silahkan DSP ulang';
                    throw new HttpResponseException(ApiFormatter::error(400, $message));
                }
            }

        }

        //* UPDATE STATUS GURIH - 6
        if(str_contains($nopb, '/500/')){
            $this->updateStatusGurih($nopb, '6');
        }

        $query = '';
        $query .= "select DISTINCT pobi_nocontainer";
        $query .= "from tbtr_packing_obi join tbtr_obi_h on pobi_notransaksi = obi_notrans and pobi_tgltransaksi = obi_tgltrans";
        $query .= "WHERE obi_notrans = '" . $notrans . "'";
        $query .= "AND obi_nopb = '" . $nopb . "'";
        $query .= "ORDER BY pobi_nocontainer ASC ";
        $data['dtKoli'] = DB::select($query);

        $query = '';
        $query .= "SELECT ROW_NUMBER() OVER() || '. ' || COALESCE(gh.gfh_namapromosi, h.kode_promo) || '=>' || h.gift_real GIFT ";
        $query .= "FROM promo_klikigr h LEFT JOIN tbtr_gift_hdr gh ON h.kode_promo = gh.gfh_kodepromosi ";
        $query .= "WHERE h.tipe_promo = 'GIFT' ";
        $query .= "AND h.gift_real IS NOT NULL ";
        $query .= "AND h.kode_member = '" . $kodeMember . "' ";
        $query .= "AND h.no_trans = '" . $notrans . "' ";
        $query .= "AND h.no_pb = '" . $nopb . "' ";
        $query .= "ORDER BY row_NUMBER() OVER() ASC";
        $data['dtHadiah'] = DB::select($query);

        $query = '';
        $query .= " SELECT ";
        $query .= "   COALESCE(amm_namapenerima, cus_namamember) nama, ";
        $query .= "   obi_nopb nopb, ";
        $query .= "   amm_namaalamat alamat, ";
        $query .= "   COALESCE(amm_nomorpenerima, amm_hp, cus_tlpmember, cus_hpmember) telp, ";
        $query .= "   obi_nostruk nostruk, ";
        $query .= "   obi_tgltrans tgltrans, ";
        $query .= "   TO_CHAR(obi_tgltrans,'DD-MM-YYYY') tgl_pesan, ";
        $query .= "   TO_CHAR( ";
        $query .= "    DATE_TRUNC('DAY',COALESCE(obi_mindeliverytime, obi_tglstruk, obi_draftstruk)) +  ";
        $query .= "    CASE  ";
        $query .= "     WHEN COALESCE(obi_shippingservice,'X') = 'S'  ";
        $query .= "      THEN CASE WHEN TO_NUMBER(TO_CHAR(obi_mindeliverytime, 'HH24')) < 12 THEN 0 ELSE 1 END ";
        $query .= "     WHEN COALESCE(obi_shippingservice,'X') = 'N'  ";
        $query .= "      THEN CASE WHEN TO_NUMBER(TO_CHAR(obi_mindeliverytime, 'HH24')) < 12 THEN 1 ELSE 2 END ";
        $query .= "     ELSE 1 ";
        $query .= "    END, ";
        $query .= "   'DD-MM-YYYY') tgl_maks_kirim, ";
        $query .= "   COALESCE(obi_nopo,'-') nopo, ";
        $query .= "   COALESCE(amm_noawb, 'SJ' || TO_CHAR(obi_tgltrans, 'YYMMDD') || COALESCE(obi_kdstation,'00') || COALESCE(obi_nostruk,'00000')) no_awb, ";
        $query .= "   CASE WHEN obi_freeongkir = 'T' ";
        $query .= "    THEN 'AMBIL DI TOKO' ";
        $query .= "    ELSE COALESCE(obi_kdekspedisi, '-') ";
        $query .= "   END ekspedisi, ";
        $query .= "   TRIM(TO_CHAR(COALESCE(obi_ekspedisi, 0),'999,999,999')) ongkir, ";
        $query .= "   COALESCE(obi_shippingservice,'-') shippingservice ";
        $query .= " FROM tbtr_obi_h ";
        $query .= " JOIN tbtr_alamat_mm ";
        $query .= "   ON obi_nopb = amm_nopb ";
        $query .= "  AND DATE_TRUNC('DAY',obi_tglpb) = DATE_TRUNC('DAY',amm_tglpb) ";
        $query .= "  AND obi_kdmember = amm_kodemember ";
        $query .= " JOIN tbmaster_customer ";
        $query .= "   ON amm_kodemember = cus_kodemember ";
        $query .= " WHERE obi_notrans = '" . $notrans . "' ";
        $query .= "   AND obi_nopb = '" . $nopb . "' ";
        $data['dtDetailSJ'] = DB::select($query);

        if(str_contains($nopb, '/500/')){
            //* form -> frmSuratJalanGurih
        }else{
            //* form -> frmSuratJalan
        }
    }

    private function updateStatusGurih($noPB, $statusID){
        $dtGet = DB::select("SELECT url, api_key FROM web_status_gurih ORDER BY create_dt DESC");

        $urlGurih = 'https://klikigrsim.mitraindogrosir.co.id/api/update_trx';
        $apiKey = "123456789";
        if(count($dtGet) > 0){
            $urlGurih = $dtGet[0]->url;
            $apiKey = $dtGet[0]->api_key;
        }

        $splitTrans = explode("/", $noPB);
        $notrx = $splitTrans[0];

        $postData = [
            'trxid' => $notrx,
            'status_id' => $statusID,
            'key' => $apiKey,
        ];

        $response = $this->ConToWebService($urlGurih, $postData);

        $query = '';
        $query .= "INSERT INTO LOG_STATUS_GURIH ( ";
        $query .= " nopb, ";
        $query .= " notrans, ";
        $query .= " status_id, ";
        $query .= " url, ";
        $query .= " post_data, ";
        $query .= " response, ";
        $query .= " create_dt ";
        $query .= ") ";
        $query .= "VALUES ( ";
        $query .= " '" . $noPB . "', ";
        $query .= " '" . $notrx . "', ";
        $query .= " '" . $statusID . "', ";
        $query .= " '" . $urlGurih . "', ";
        $query .= " '" . $postData . "', "; //! dummy -> harusnya response dari ConToWebService
        $query .= " '" . $response . " ', "; //! dummy -> harusnya response dari ConToWebService
        $query .= " now() ";
        $query .= ") ";
    }

    private function insertAWBIPP($noAWB, $noPB, $noOrder, $tglOrder, $kdMember, $kdToko, $cost, $pincode, $refNoOrder, $status, $tipe){
        $UserMODUL = session('userid');

        $query = '';
        $query .= " SELECT awi_noawb ";
        $query .= " FROM tbtr_awb_ipp ";
        $query .= " WHERE awi_nopb = '" . $noPB . "' ";
        $query .= " AND awi_noorder = '" . $noOrder . "' ";
        $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglOrder)->format('Y-m-d H:i:s') . "' ";
        $query .= " AND awi_kodemember = '" . $kdMember . "' ";
        $dt = DB::select($query);

        if(count($dt) == 0){

            $query = '';
            $query .= " INSERT INTO tbtr_awb_ipp ( ";
            $query .= "   awi_noawb, ";
            $query .= "   awi_nopb, ";
            $query .= "   awi_noorder, ";
            $query .= "   awi_tglorder, ";
            $query .= "   awi_kodemember, ";
            $query .= "   awi_kodetoko, ";
            $query .= "   awi_cost, ";
            $query .= "   awi_ref_noorder, ";
            $query .= "   awi_pincode, ";
            $query .= "   awi_tipetransaksi, ";
            $query .= "   awi_create_by, ";
            $query .= "   awi_create_dt ";
            $query .= " ) ";
            $query .= " VALUES ( ";
            $query .= "   '" . $noAWB . "', ";
            $query .= "   '" . $noPB . "', ";
            $query .= "   '" . $noOrder . "', ";
            $query .= "   TO_DATE('" . $tglOrder . "','DD-MM-YYYY'), ";
            $query .= "   '" . $kdMember . "', ";
            $query .= "   '" . $kdToko . "', ";
            $query .= "   " . $cost . ", ";
            $query .= "   '" . $refNoOrder . "', ";
            $query .= "   '" . $pincode . "', ";
            $query .= "   '" . $tipe . "', ";
            $query .= "   '" . $UserMODUL . "', ";
            $query .= "   NOW() ";
            $query .= " ) ";
        }else{
            $query = '';
            $query .= " UPDATE tbtr_awb_ipp ";
            $query .= " SET awi_cost = " . $cost . ", ";
            $query .= "     awi_pincode = '" . $pincode . "', ";
            $query .= "     awi_modify_by = '" . $UserMODUL . "', ";
            $query .= "     awi_modify_dt = NOW() ";
            $query .= " WHERE awi_nopb = '" . $noPB . "' ";
            $query .= " AND awi_noorder = '" . $noOrder . "' ";
            $query .= " AND DATE_TRUNC('DAY',awi_tglorder) = '" . Carbon::parse($tglOrder)->format('Y-m-d H:i:s') . "' ";
            $query .= " AND awi_kodemember = '" . $kdMember . "' ";
        }

        return true;
    }

    private function rptSuratJalanSPI($dgv_nopb, $dgv_notrans, $dgv_memberigr, $dtTrans, $dgv_flagBayar){

        $query = '';
        $query .= " SELECT obi_kdekspedisi  ";
        $query .= " FROM tbtr_obi_h ";
        $query .= " WHERE obi_nopb = '" . $dgv_nopb . "' ";
        $query .= " AND obi_notrans = '" . $dgv_notrans . "' ";
        $query .= " AND DATE_TRUNC('DAY',obi_tgltrans) = '" . Carbon::parse($dtTrans)->format('Y-m-d H:i:s') . "' ";
        $query .= " AND obi_kdmember = '" . $dgv_memberigr . "' ";
        $dtCek = DB::select($query);

        if(count($dtCek) == 0){
            $message = 'Data PB Tidak Ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        if (strpos(strtoupper($dtCek[0]->obi_kdekspedisi), "KURIR INDOGROSIR") !== false ||
            strpos(strtoupper($dtCek[0]->obi_kdekspedisi), "AMBIL DI STOCK POINT INDOGROSIR") !== false ||
            strpos(strtoupper($dtCek[0]->obi_kdekspedisi), "AMBIL DI TOKO INDOGROSIR") !== false ||
            strpos(strtoupper($dtCek[0]->obi_kdekspedisi), "EKSPEDISI LAINNYA") !== false ||
            strpos(strtoupper($dtCek[0]->obi_kdekspedisi), "MOBIL ENGKEL/DOUBLE") !== false)
        {
            if($dgv_flagBayar == 'Y' AND session('SERVER') == 'PRODUCTION'){
                $cek = DB::table('tbtr_alamat_mm')
                    ->where([
                        'amm_nopb' => $dgv_nopb,
                        'amm_notrans' => $dgv_notrans,
                    ])
                    ->whereNull('amm_noawb')
                    ->count();

                if($cek > 0){
                    $dtCek = DB::select("SELECT ws_url FROM tbmaster_webservice WHERE ws_nama = 'KLIKIGR'");

                    if(count($dtCek) > 0){
                        $urlAWB = $dtCek[0]->ws_url;

                        $splitTrans = explode("/", $dgv_nopb);
                        $notrx = $splitTrans[0];

                        $postData = [
                            'trxid' => $notrx,
                        ];

                        $ret = $this->ConToWebService($urlAWB, $postData);

                        //* LOG_CREATEAWB
                        $query = '';
                        $query .= "INSERT INTO log_createawb ( ";
                        $query .= " nopb,  ";
                        $query .= " notrans,  ";
                        $query .= " url,  ";
                        $query .= " param,  ";
                        $query .= " response,  ";
                        $query .= " create_dt ";
                        $query .= ") ";
                        $query .= "VALUES ( ";
                        $query .= " '" . $dgv_nopb . "', ";
                        $query .= " '" . $notrx . "', ";
                        $query .= " '" . $urlAWB . "', ";
                        $query .= " '" . "trxid=" . $notrx . "', ";
                        $query .= " '" . $ret . " ', ";
                        $query .= " NOW() ";
                        $query .= ") ";
                        DB::insert($query);

                        $noAWB = null; //! berasal dari return ConToWebService

                        $query = '';
                        $query .= "UPDATE tbtr_alamat_mm ";
                        $query .= "   SET amm_noawb = '" . $noAWB . "' ";
                        $query .= " WHERE amm_nopb = '" . $dgv_nopb . "' ";
                        $query .= "   AND amm_notrans = '" . $dgv_notrans . "' ";
                        $query .= "   AND amm_noawb IS NULL ";
                        DB::update($query);
                    }
                }
            }

        }else{
            $cek = DB::table('tbtr_alamat_mm')
                ->where([
                    'amm_nopb' => $dgv_nopb,
                    'amm_notrans' => $dgv_notrans,
                ])
                ->whereNull('amm_noawb')
                ->count();

            if($cek == 0){
                $message = 'Belum Ada No. AWB, Silahkan DSP ulang';
                throw new HttpResponseException(ApiFormatter::error(400, $message));
            }
        }

        $query = '';
        $query .= "SELECT  ";
        $query .= "  amm_kodemember kode_member,  ";
        $query .= "  amm_namapenerima nama_member, ";
        $query .= "  amm_namaalamat alamat, ";
        $query .= "  amm_hp no_hp, ";
        $query .= "  amm_nopb no_pb,  ";
        $query .= "  amm_noawb no_awb ";
        $query .= "FROM tbtr_alamat_mm ";
        $query .= "WHERE amm_nopb = '" . $dgv_nopb . "' ";
        $query .= " AND amm_notrans = '" . $dgv_notrans . "' ";
        $query .= " AND amm_kodemember = '" . $dgv_memberigr . "' ";
        $dtDetailSJ = DB::select($query);

        $query = '';
        $query .= "SELECT del_nopol nopol, del_driver driver, del_deliveryman deliveryman";
        $query .= "FROM tbtr_delivery_spi ";
        $query .= "WHERE del_nopb = '" . $dgv_nopb . "' ";
        $query .= " AND del_kodemember = '" . $dgv_memberigr . "' ";
        $query .= " AND del_flagbatal IS NULL ";
        $_dtDetailSJ2 = DB::select($query);

        $query = '';
        $query .= " SELECT  ";
        $query .= "   nama_barang, ";
        $query .= "   satuan, ";
        $query .= "   harga, ";
        $query .= "   qty, ";
        $query .= "   total, ";
        $query .= "   frac ";
        $query .= " FROM ( ";
        $query .= "   SELECT  ";
        $query .= "     prd_deskripsipanjang nama_barang, ";
        $query .= "     prd_unit satuan, ";
        $query .= "     d.obi_hargaweb harga, ";
        $query .= "     SUM(COALESCE(pobi_qty,0)) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) qty, ";
        $query .= "     SUM(COALESCE(pobi_qty,0)) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * d.obi_hargaweb total, ";
        $query .= "     ctn_frac frac ";
        $query .= "   FROM tbtr_obi_h h ";
        $query .= "   JOIN tbtr_obi_d d ";
        $query .= "     ON h.obi_notrans = d.obi_notrans ";
        $query .= "    AND h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "   JOIN tbmaster_prodmast ";
        $query .= "     ON prd_prdcd = d.obi_prdcd ";
        $query .= "   LEFT JOIN ( ";
        $query .= "          SELECT prd_prdcd ctn_prdcd,  ";
        $query .= "                 prd_frac ctn_frac, ";
        $query .= "                 prd_unit ctn_unit ";
        $query .= "            FROM tbmaster_prodmast ";
        $query .= "           WHERE prd_prdcd LIKE '%0' ";
        $query .= "        ) tbmaster_ctn ";
        $query .= "     ON ctn_prdcd = SUBSTR(d.obi_prdcd,1, 6) || '0'    ";
        $query .= "   LEFT JOIN tbtr_packing_obi ";
        $query .= "     ON pobi_notransaksi = d.obi_notrans ";
        $query .= "    AND pobi_tgltransaksi = d.obi_tgltrans ";
        $query .= "    AND pobi_prdcd = d.obi_prdcd ";
        $query .= "   WHERE d.obi_recid IS NULL ";
        $query .= "    AND COALESCE(d.obi_qtyrealisasi,0) > 0 ";
        $query .= "    AND COALESCE(pobi_qty,0) > 0 ";
        $query .= "   AND h.obi_nopb = '" . $dgv_nopb . "' ";
        $query .= "   AND h.obi_notrans = '" . $dgv_notrans . "' ";
        $query .= "   AND DATE_TRUNC('DAY',h.obi_tgltrans) = '" . Carbon::parse($dtTrans)->format('Y-m-d H:i:s') . "' ";
        $query .= "   AND h.obi_kdmember = '" . $dgv_memberigr . "' ";
        $query .= "   GROUP BY prd_deskripsipanjang, prd_unit, d.obi_hargaweb, (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), ctn_frac ";
        $query .= "   ORDER BY prd_deskripsipanjang ";
        $query .= " ) AS SURATJLANSPI ";
        $data = DB::select($query);

        //* form -> frmSuratJalanSPI_NEW

        // rpt.SetParameterValue("kdIGR", KodeIGR & " - " & NamaIGR)
        // _rpt.SetParameterValue("penerima", _dtDetailSJ.Rows(0).Item("kode_member").ToString & " - " &
        //                                     _dtDetailSJ.Rows(0).Item("nama_member").ToString)
        // _rpt.SetParameterValue("alamat", _dtDetailSJ.Rows.Item(0)("alamat").ToString)
        // _rpt.SetParameterValue("nohp", _dtDetailSJ.Rows.Item(0)("no_hp").ToString)
        // _rpt.SetParameterValue("noAWB", _dtDetailSJ.Rows.Item(0)("no_awb").ToString)
        // _rpt.SetParameterValue("noPB", nopb)
        // _rpt.SetParameterValue("tglPB", tgltrans)
        // _rpt.SetParameterValue("barcodeAWB", code128(_dtDetailSJ.Rows.Item(0)("no_awb").ToString))
    }

    private function InsertTransaksi($dgv_kodeweb, $dgv_nopb, $dgv_memberigr,$dgv_notrans, $dgv_tglpb, $dgv_tipe_kredit, $dtTrans, $dgv_tipebayar, $urlUpdateRealisasiKlik){

        //* CHECK SUDAH PENAH SALES
        $query = '';
        $query .= " SELECT obi_nopb  ";
        $query .= " FROM tbtr_obi_h  ";
        $query .= " WHERE obi_nopb = '" . $dgv_nopb . "' ";
        $query .= " AND obi_kdmember = '" . $dgv_memberigr . "' ";
        $query .= " AND obi_notrans = '" . $dgv_notrans . "' ";
        $query .= " AND obi_nostruk IS NOT NULL ";
        $query .= " AND obi_tglstruk IS NOT NULL ";
        $query .= " AND obi_kdstation IS NOT NULL ";
        $dtCek = DB::select($query);

        if(count($dtCek) > 0){
            $query = '';
            $query .= " UPDATE tbtr_obi_h  ";
            $query .= " SET obi_recid = '6' ";
            $query .= " WHERE obi_nopb = '" . $dgv_nopb . "' ";
            $query .= " AND obi_kdmember = '" . $dgv_memberigr . "' ";
            $query .= " AND obi_notrans = '" . $dgv_notrans . "' ";
            DB::update($query);

            $message = "PB " . $dgv_nopb . " Sudah Pernah Distruk.";
            throw new HttpResponseException(ApiFormatter::error(500, $message));
        }

        try{
            $kodeigr = session('KODECABANG');
            $UserMODUL = session('userid');

            $master_comp = DB::table('tbmaster_computer')
                ->where([
                    'kodeigr' => $kodeigr,
                    'ip' => $this->getIP(),
                ])->first();

            $procedure = DB::select("call sp_create_sales_obi('$dgv_nopb', '$dgv_tglpb', '$dgv_memberigr', '$master_comp->station', '$UserMODUL', '$kodeigr', '$dgv_notrans', '')");
            $procedure = $procedure[0]->p_Status;

            if (str_contains($procedure, 'Sukses')) {

                if (!str_contains($dgv_nopb, 'TMI') AND session('flagSPI') == false) {
                    $this->updateReal($dgv_nopb, $dgv_notrans, $dgv_tglpb, $dgv_memberigr, $urlUpdateRealisasiKlik);
                }

                if(session('flagSPI') == true){
                    //! BELUM SELESAI
                    $this->PrintNotaNewSPI('N','STRUK', $dgv_kodeweb, $dgv_tipe_kredit);
                }else{
                    //! BELUM SELESAI
                    $this->PrintNotaNew('N','STRUK', $dgv_kodeweb, $dgv_tipe_kredit);
                }

                if($dgv_tipebayar == 'COD-VA'){
                    $this->logUpdateStatus($dgv_notrans, $dtTrans, $dgv_nopb, "6", "8");
                }else{
                    $this->logUpdateStatus($dgv_notrans, $dtTrans, $dgv_nopb, "6", "6");
                }
            }
        }

        catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            return ApiFormatter::error(400, $message);
        }
    }

    private function PrintNotaNew(){

    }

    private function PrintNotaNewSPI(){

    }

    private function updateReal($dgv_nopb, $dgv_notrans, $dgv_tglpb, $dgv_memberigr, $urlUpdateRealisasiKlik){
        try{
            $query = '';
            $query .= "SELECT plu, ";
            $query .= " ROUND(qty_real) qty_real, ";
            $query .= " ROUND(CASE ";
            $query .= "     WHEN qty_real > 0 ";
            $query .= "     THEN harga - ROUND(hpp / prd_ppn,2) ";
            $query .= "     ELSE 0 ";
            $query .= " END, 2) margin, ";
            $query .= " ROUND(harga / prd_ppn,2) dpp, ";
            $query .= " ROUND(harga - ROUND(harga / prd_ppn,2),2) ppn ";
            $query .= "FROM ";
            $query .= "(SELECT obi_prdcd plu, ";
            $query .= "(d.obi_qtyrealisasi / p.prd_frac) qty_real, ";
            $query .= "CASE WHEN d.obi_qtyrealisasi = 0 THEN 0 ELSE ";
            $query .= "d.obi_hargaweb - (ROUND(d.obi_qtyrealisasi * d.obi_diskon) / (d.obi_qtyrealisasi / p.prd_frac)) END harga, ";
            $query .= "ROUND(COALESCE(jd.trjd_baseprice, d.obi_hpp, 0),2) hpp, ";
            $query .= "CASE ";
            $query .= " WHEN COALESCE(prd_flagbkp1,'N') = 'Y' AND COALESCE(prd_flagbkp2,'N')  = 'Y' ";
            $query .= " THEN 1 + (COALESCE(prd_ppn,0) / 100) ";
            $query .= " ELSE 1 ";
            $query .= "END prd_ppn ";
            $query .= "FROM tbtr_obi_h h ";
            $query .= "JOIN tbtr_obi_d d ";
            $query .= "ON h.obi_notrans = d.obi_notrans AND DATE_TRUNC('DAY',h.obi_tgltrans) = DATE_TRUNC('DAY',d.obi_tgltrans) ";
            $query .= "JOIN tbmaster_prodmast p ";
            $query .= "ON p.prd_prdcd = d.obi_prdcd ";
            $query .= "LEFT JOIN tbtr_jualdetail jd ";
            $query .= "ON h.obi_nostruk = jd.trjd_transactionno ";
            $query .= "AND DATE_TRUNC('DAY',h.obi_tglstruk) = DATE_TRUNC('DAY',jd.trjd_transactiondate) ";
            $query .= "AND h.obi_kdmember        = jd.trjd_cus_kodemember ";
            $query .= "AND h.obi_kdstation       = jd.trjd_cashierstation ";
            $query .= "AND d.obi_prdcd           = jd.trjd_prdcd ";
            $query .= "AND h.obi_tipe            = jd.trjd_transactiontype ";
            $query .= "WHERE h.obi_nopb LIKE '%" . $dgv_nopb . "%' ";
            $query .= "AND h.obi_notrans = '" . $dgv_notrans . "' ";
            $query .= "AND DATE_TRUNC('DAY',h.obi_tgltrans) = ".Carbon::parse($dgv_tglpb)->format('Y-m-d H:i:s')." ";
            $query .= "AND h.obi_kdmember = '" . $dgv_memberigr . "' ";
            //$query .= "AND d.obi_qtyrealisasi <> 0 ";
            $query .= ") ";
            $query .= "ORDER BY 1 ASC";
            $dtDetailOrder = DB::select($query);

            $query .= "SELECT id_tipe idpromo, ";
            $query .= " kode_promo promo_code, ";
            $query .= " tipe_promo promo_type, ";
            $query .= " prdcd plupromo, ";
            $query .= " COALESCE(cashback_real, 0) reward_realisasi ";
            $query .= "FROM promo_klikigr ";
            $query .= "WHERE id_tipe IN (0,1) ";
            $query .= "AND prdcd IS NOT NULL ";
            $query .= "AND no_pb = '" . $dgv_nopb . "' ";
            $query .= "AND no_trans = '" . $dgv_notrans . "' ";
            $query .= "AND DATE_TRUNC('DAY',tgl_trans) = ".Carbon::parse($dgv_tglpb)->format('Y-m-d H:i:s')." ";
            $query .= "AND kode_member = '" . $dgv_memberigr . "'";
            $dtPromotion = DB::select($query);

            if(session('flagSPI') == true){
                $cre = DB::select("SELECT CRE_NAME, CRE_KEY FROM TBMASTER_CREDENTIAL WHERE CRE_TYPE = 'WS_SPI'");
            }else{
                $cre = DB::select("SELECT CRE_NAME, CRE_KEY FROM TBMASTER_CREDENTIAL WHERE CRE_TYPE = 'WS_KLIK'");
            }

            $postData = [
                'trxid' => substr($dgv_nopb, 0, 6),
                'order_details' => $dtDetailOrder,
                'promotions' => $dtPromotion,
            ];

            $apiName = $cre[0]->cre_name;
            $apiKey = $cre[0]->cre_key;
            $this->ConToWebServiceNew($urlUpdateRealisasiKlik, $apiName, $apiKey, $postData);

            //! dummy harusnya return dari ConToWebServiceNew
            $json = null;
            $ret = null;

            $query = '';
            $query .= "INSERT INTO log_obi_realisasi ( ";
            $query .= "  notrans, ";
            $query .= "  tgltrans, ";
            $query .= "  nopb, ";
            $query .= "  create_by, ";
            $query .= "  create_dt, ";
            $query .= "  url, ";
            $query .= "  parameter, ";
            $query .= "  response ";
            $query .= ") VALUES ( ";
            $query .= "  '" . $dgv_notrans . "', ";
            $query .= "  ".Carbon::parse($dgv_tglpb)->format('Y-m-d H:i:s').", ";
            $query .= "  '" . $dgv_nopb . "', ";
            $query .= "  '" . $dgv_memberigr . "', ";
            $query .= "  NOW(), ";
            $query .= "  '" . $urlUpdateRealisasiKlik . "', ";
            $query .= "  '" . $json . "', ";
            $query .= "  '" . $ret . "' ";
            $query .= ") ";

            return true;

        }catch(\Exception $e){

            $message = "Oops! Something wrong ( $e )";
            return ApiFormatter::error(400, $message);
        }
    }

    private function CheckTransaksiVALunas($trxid, $dgv_tglpb){
        $query = '';
        $query .= "SELECT * FROM TBTR_TRANSAKSI_VA ";
        $query .= "WHERE TVA_TRXID = '" . $trxid . "' ";
        $query .= "    AND TVA_TGLPB = TO_DATE('" . $dgv_tglpb . "','DD-MM-YYYY') ";
        $query .= "    AND UPPER(TVA_STATUS) LIKE '%SUDAH%' ";
        $dt = DB::select($query);

        if(count($dt) == 0){
            return false;
        }

        return true;
    }

    private function konfirmasiBayar($dgv_nopb, $dgv_memberigr, $dgv_notrans){
        $dtOBI_H = DB::select("SELECT * FROM TBTR_OBI_H WHERE OBI_NOPB = '" . $dgv_nopb . "' AND OBI_KDMEMBER = '" . $dgv_memberigr . "' AND OBI_NOTRANS = '" . $dgv_notrans . "'");
        if(!count($dtOBI_H)){
            $message = 'data table TBTR_OBI_H tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        if (str_contains(strtoupper($dgv_nopb), 'OMM') OR str_contains(strtoupper($dgv_nopb), 'TMI') ) {
            $query = '';
            $query .= "MERGE INTO tbtr_obi_d d ";
            $query .= "USING ( ";
            $query .= " SELECT DISTINCT obi_notrans, ";
            $query .= "        obi_tgltrans, ";
            $query .= "        obi_prdcd, ";
            $query .= "        CASE WHEN COALESCE(prd_flagbkp1,'N') = 'Y' ";
            $query .= "         THEN round(obi_hargasatuan / (1+(COALESCE(PRD_PPN,0)/100)), 2)  ";
            $query .= "         ELSE obi_hargasatuan ";
            $query .= "        END hrgsatuan ";
            $query .= " FROM tbhistory_obi_d ";
            $query .= " JOIN tbmaster_prodmast ";
            $query .= " ON prd_prdcd = obi_prdcd ";
            $query .= ") dh ";
            $query .= "ON ( ";
            $query .= "    d.obi_notrans = dh.obi_notrans ";
            $query .= "AND d.obi_tgltrans = dh.obi_tgltrans ";
            $query .= "AND d.obi_prdcd = dh.obi_prdcd ";
            $query .= ") ";
            $query .= "WHEN MATCHED THEN ";
            $query .= "  UPDATE SET d.obi_hargasatuan = dh.hrgsatuan ";
            $query .= "  WHERE d.obi_notrans = '" . $dtOBI_H[0]->obi_notrans . "'  ";
            $query .= "  AND DATE_TRUNC('DAY',d.OBI_TGLTRANS) = '" . Carbon::parse($dtOBI_H[0]->obi_tgltrans)->format('Y-m-d H:i:s') ."' ";
            DB::insert($query);
        }

        //! GET TRANSAKSI
        $query = '';
        $query .= "SELECT obi_notrans notrans, obi_tgltrans tgltrans, obi_prdcd prdcd, ";
        $query .= "       prd_deskripsipendek deskripsi, ";
        $query .= "       COALESCE(obi_hargaweb, ROUND((obi_hargasatuan + obi_ppn) * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), 0)) hrgjual, ";
        $query .= "       obi_qtyrealisasi / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) qty, ";
        $query .= "       0 diskon, "; //ROUND(obi_diskon * obi_qtyrealisasi,0) diskon
        $query .= "       COALESCE(obi_hargaweb, ROUND((obi_hargasatuan + obi_ppn) * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), 0))*(obi_qtyrealisasi / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END)) substotal, ";
        $query .= "       prd_flagbkp1 bkp1, ";
        $query .= "       prd_flagbkp2 bkp2, ";
        $query .= "       prd_kodedivisi div, ";
        $query .= "       prd_kodedepartement || prd_kodekategoribarang depkat, ";
        $query .= "       (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) frac, ";
        $query .= "       prd_minjual minjual ";
        $query .= "FROM TBTR_OBI_D, TBMASTER_PRODMAST  ";
        $query .= "WHERE OBI_PRDCD = PRD_PRDCD  ";
        $query .= "AND COALESCE(OBI_QTYREALISASI, 0) > 0 ";
        $query .= "AND OBI_NOTRANS = '" . $dtOBI_H[0]->obi_notrans . "'  ";
        $query .= "AND DATE_TRUNC('DAY',OBI_TGLTRANS) = '" . Carbon::parse($dtOBI_H[0]->obi_tgltrans)->format('Y-m-d H:i:s') ."' ";
        $query .= "ORDER BY OBI_SCAN_DT ASC ";
        $dtOBI_D = DB::select($query);

        // dtOBI_D = QueryOra(sb.ToString)
        // If dtOBI_D.Rows.Count = 0 Then
        //     btnKonfirmasiBayar.Visible = True
        //     MessageDialog.Show(EnumMessageType.Information, EnumCommonButtonMessage.Ok, "Gagal Konfirmasi Pembayaran." & vbCrLf & "Detail transaksi tidak ditemukan", "INDOGROSIR")
        //     Exit Sub
        // End If

        if(!count($dtOBI_D)){
            //* nanti disable button btnKonfirmasiBayar
            $message = 'Gagal Konfirmasi Pembayaran, Detail transaksi tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $query = '';
        $query .= "UPDATE TBTR_OBI_H SET OBI_RECID = '5' ";
        $query .= "Where OBI_NOPB = '" . $dgv_nopb . "' ";
        $query .= "AND OBI_KDMEMBER = '" . $dgv_memberigr . "' ";
        $query .= "AND OBI_NOTRANS = '" . $dgv_notrans . "' ";
        $query .= "AND OBI_RECID = '4' ";
        DB::update($query);


        $this->logUpdateStatus($dgv_notrans, $dtOBI_H[0]->obi_tgltrans, $dgv_nopb, "5", "5");

        $this->WRITE_SSO($dtOBI_D, $dgv_memberigr, $dgv_notrans);

    }

    private function WRITE_SSO($dtOBI_D, $dgv_memberigr, $NoTrans){
        $dt = DB::table('information_schema.tables')->where('UPPER(table_name)', 'TBTR_SSO_WEB')->count();
        if($dt == 0){
            $query = '';
            $query .= "  CREATE TABLE TBTR_SSO_WEB ";
            $query .= "   ( SSW_RECID VARCHAR(1), ";
            $query .= "	SSW_KODEIGR VARCHAR(5), ";
            $query .= "	SSW_KODESSO VARCHAR(50) PRIMARY KEY, ";
            $query .= "	SSW_NOTRANS VARCHAR(50), ";
            $query .= "	SSW_TGLTRANS DATE, ";
            $query .= "	SSW_CREATE_BY VARCHAR(5), ";
            $query .= "	SSW_CREATE_DT DATE, ";
            $query .= "	SSW_MODIFY_BY VARCHAR(5), ";
            $query .= "	SSW_MODIFY_DT DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        $SubTotal = 0;

        foreach($dtOBI_D as $key => $item){
            if($item->qty > 0){
                DB::table('tbtr_kasir_sso')
                    ->insert([
                        'kode_trans' => $this->GetNoTrans(),
                        'seqno' => $key + 1,
                        'prdcd' => $item->prdcd,
                        'deskripsipendek' => $item->deskripsi,
                        'hrgjual' => $item->hrgjual,
                        'qty' => $item->qty,
                        'disc' => $item->diskon,
                        'total' => $item->substotal,
                        'flagbkp1' => $item->bkp1,
                        'flagbkp2' => $item->bkp2,
                        'kodedivisi' => $item->div,
                        'divisi' => $item->depkat,
                        'free_charge' => 'F',
                        'frac' => $item->frac,
                        'min_jual' => $item->minjual,
                    ]);

                $SubTotal += $item->substotal;
            }
        }

        $query = '';
        $query .= "INSERT INTO TBTR_KASIR_SSO_H (KODEIGR,KODE_TRANS,TOTAL,";
        $query .= "PAYMENT,MEMBER,KASIR,TGL_TRANS) ";
        $query .= "VALUES('" . session('KODECABANG') . "','" . $NoTrans . "',";
        $query .= "'" . $SubTotal . "',";
        $query .= "'N','" . $dgv_memberigr . "',";
        $query .= "'" . session('userid') . "',DATE_TRUNC('DAY',CURRENT_DATE)) ";
        DB::insert($query);

        $query = '';
        $query .= "INSERT INTO TBTR_SSO_WEB (";
        $query .= "SSW_KODEIGR, ";
        $query .= "SSW_KODESSO, ";
        $query .= "SSW_NOTRANS, ";
        $query .= "SSW_TGLTRANS, ";
        $query .= "SSW_CREATE_BY, ";
        $query .= "SSW_CREATE_DT ) ";
        $query .= "VALUES (";
        $query .= "'" . session('KODECABANG') . "', ";
        $query .= "'" . $NoTrans . "', ";
        $query .= "'" . $dtOBI_D[0]->notrans . "', ";
        $query .= "'" . Carbon::parse($dtOBI_D[0]->tgltrans)->format('Y-m-d H:i:s') . "' ";
        $query .= "'" . session('userid') . "', ";
        $query .= "NOW())";

        //! BELUM SELESAI (GIMANA BENTUK NOTANYA)
        $this->PrintNotaSSO($dtOBI_D, $NoTrans, $dgv_memberigr);
    }

    private function GetNoTrans(){
        $Kode = DB::select("select nextval('igr_kode_sso')")[0]->nextval;

        //* Permintaan Agus 10-07-2019
        if (strlen($Kode) > 6) {
            $Kode = substr($Kode, 0, 6);
        }

        $Kode = "SSO" . str_pad($Kode, 6, "0", STR_PAD_LEFT);
        $Kode = strtoupper($Kode);

        return $Kode;
    }

    private function draftStruk($dgv_kodeweb, $dgv_memberigr, $dgv_notrans, $dgv_nopb, $dgv_tglpb, $dtTrans, $dgv_freeongkir){
        $kdWeb = $dgv_kodeweb;
        $kdMember = $dgv_memberigr;
        $noTrans = $dgv_notrans;
        $noPB = $dgv_nopb;
        $tglPB = $dgv_tglpb;
        $flagSkipIPP = false;
        $skipStatus = false;
        $kurir = '';
        $memberOK = true;
        $alamatOK = true;
        $transKlik = [];

        if($kdWeb != 'WebMM'){
            //! BELUM SLESAI
            $memberOK = $this->validasiDataMember($noTrans, $dtTrans);
        }

        $query = '';
        $query .= "SELECT obi_kdekspedisi, obi_freeongkir ";
        $query .= "  FROM tbtr_obi_h h ";
        $query .= " WHERE h.obi_kdmember = '" . $kdMember . "' ";
        $query .= "   AND h.obi_notrans = '" . $noTrans . "' ";
        $query .= "   AND h.obi_nopb = '" . $noPB . "' ";
        $dt = DB::select($query);

        if(count($dt) > 0){
            $kurir = $dt[0]->obi_kdekspedisi;
        }else{
            $message = "Data Kurir Tidak ditemukan";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        if($kdWeb == 'CORP' OR $kdWeb == 'TMI' OR $dgv_freeongkir == 'T'){
            $flagSkipIPP = true;
        }

        $query = '';
        $query .= "SELECT kode_promo ";
        $query .= "FROM promo_klikigr ";
        $query .= "WHERE kode_member = '" . $kdMember . "' ";
        $query .= "  AND no_trans = '" . $noTrans . "' ";
        $query .= "  AND no_pb = '" . $noPB . "' ";
        $dt = DB::select($query);

        if(count($dt)){
            $query = '';
            $query .= "SELECT SUBSTR(obi_prdcd,1,6) || '0' PLU, ";
            $query .= "       SUM(COALESCE(obi_qtyorder,0)) orderr, ";
            $query .= "       SUM(COALESCE(obi_qtyrealisasi,0)) realisasi ";
            $query .= "  FROM tbtr_obi_h h ";
            $query .= "  JOIN tbtr_obi_d d ";
            $query .= "    ON h.obi_notrans = d.obi_notrans ";
            $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
            $query .= "  JOIN tbmaster_prodmast p ON p.prd_prdcd = d.obi_prdcd ";
            $query .= " WHERE h.obi_kdmember = '" . $kdMember . "' ";
            $query .= "   AND h.obi_notrans = '" . $noTrans . "' ";
            $query .= "   AND h.obi_nopb = '" . $noPB . "' ";
            $query .= "   AND d.obi_qtyorder <> d.obi_qtyrealisasi ";
            $query .= " GROUP BY SUBSTR(obi_prdcd,1,6) || '0' ";
            $query .= " ORDER BY 1 ";
            $dt = DB::select($query);

            if(count($dt)){
                $query = '';
                $query .= "SELECT SUBSTR(obi_prdcd,1,6) || '0' PLU, ";
                $query .= "       SUM(COALESCE(obi_qtyorder,0)) orderr, ";
                $query .= "       SUM(COALESCE(obi_qtyrealisasi,0)) realisasi, ";
                $query .= "       SUM( ";
                $query .= "         ROUND(d.obi_hargaweb * COALESCE(obi_qtyorder,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ";
                $query .= "         - ROUND(d.obi_diskon * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * COALESCE(obi_qtyorder,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END),-1) ";
                $query .= "       ) orderinrp, ";
                $query .= "       SUM( ";
                $query .= "         ROUND(d.obi_hargaweb * COALESCE(obi_qtyrealisasi,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ";
                $query .= "         - ROUND(d.obi_diskon * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * COALESCE(obi_qtyrealisasi,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END),-1) ";
                $query .= "       ) realisasiiinrp ";
                $query .= "  FROM tbtr_obi_h h ";
                $query .= "  JOIN tbtr_obi_d d ";
                $query .= "    ON h.obi_notrans = d.obi_notrans ";
                $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
                $query .= "  JOIN tbmaster_prodmast p ON p.prd_prdcd = d.obi_prdcd ";
                $query .= " WHERE h.obi_kdmember = '" . $kdMember . "' ";
                $query .= "   AND h.obi_notrans = '" . $noTrans . "' ";
                $query .= "   AND h.obi_nopb = '" . $noPB . "' ";
                //$query .= "   AND d.obi_recid IS NULL ";
                $query .= " GROUP BY SUBSTR(obi_prdcd,1,6) || '0' ";
                $query .= " ORDER BY 1 ";
                $dt = DB::select($query);

                foreach($dt as $item){
                    $transKlik[] = [
                        'PLU' => $item->PLU,
                        'orderr' => $item->orderr,
                        'realisasi' => $item->realisasi,
                        'orderinrp' => $item->orderinrp,
                        'realisasiiinrp' => $item->realisasiiinrp,
                    ];
                }

                //! BELUM SELESAI
                $api = $this->requestPromo($transKlik, $kdMember, $noTrans, $noPB);
                if($api != true){
                    $message = 'Gagal Hitung Ulang Promosi Klik Indogrosir';
                    throw new HttpResponseException(ApiFormatter::error(400, $message));
                }

            }else{
                //! KALAU TIDAK ADA YANG SELISIH, FULL SEMUA
                $query = '';
                $query .= "UPDATE promo_klikigr ";
                $query .= "   SET cashback_real = cashback_order, kelipatan = cashback_order / reward_per_promo ";
                $query .= "WHERE kode_member = '" . $kdMember . "' ";
                $query .= "  AND no_trans = '" . $noTrans . "' ";
                $query .= "  AND no_pb = '" . $noPB . "' ";
                $query .= "  AND cashback_real IS NULL ";
                $query .= "  AND tipe_promo = 'CASHBACK' ";
                DB::update($query);

                $query = '';
                $query .= "UPDATE promo_klikigr ";
                $query .= "   SET gift_real = gift_order ";
                $query .= "WHERE kode_member = '" . $kdMember . "' ";
                $query .= "  AND no_trans = '" . $noTrans . "' ";
                $query .= "  AND no_pb = '" . $noPB . "' ";
                $query .= "  AND gift_real IS NULL ";
                $query .= "  AND tipe_promo <> 'CASHBACK' ";
                DB::update($query);
            }
        }

        //! JALANKAN FUNGSI DRAFT STRUK
        if($memberOK == true AND $alamatOK == true){
            $userMODUL = session('userid');
            $KodeIGR = session('KODECABANG');

            if($kdWeb = 'WebMM'){
                //! BELUM SELESAI (PROCEDURE INI TIDAK ADA)
                DB::select("sp_create_draftstruk_mm ('$noPB','$tglPB','$noTrans', '$userMODUL', '$KodeIGR', '')");
            }else{
                DB::select("sp_create_draftstrukobi ('$noPB','$tglPB','$noTrans', '$userMODUL', '$KodeIGR', '')");
            }

            if($flagSkipIPP == false){
                if(!(str_contains(strtoupper($kurir), 'KURIR INDOGROSIR') OR
                    str_contains(strtoupper($kurir), 'AMBIL DI') OR
                    str_contains(strtoupper($kurir), 'EKSPEDISI LAINNYA') OR
                    str_contains(strtoupper($kurir), 'MOBIL ENGKEL/DOUBLE')
                )){

                    //! BELUM SELESAI
                    if(session('flagSPI') == true){
                        // suksesDeliveryInfo = updateDeliveryInfo_SPI(kdMember, noTrans, dtTrans.Value.ToString("dd-MM-yyyy"), noPB)
                    }else{
                        // suksesDeliveryInfo = updateDeliveryInfo_KLIK(kdMember, noTrans, dtTrans.Value.ToString("dd-MM-yyyy"), noPB)
                    }

                    if($suksesDeliveryInfo == false){
                        $query = '';
                        $query .= "update tbtr_obi_h set obi_realorder = 0, ";
                        $query .= "obi_realppn = 0, ";
                        $query .= "obi_realdiskon = 0, ";
                        $query .= "obi_realitem = 0, ";
                        $query .= "obi_recid = CASE WHEN obi_flagbayar = 'Y' THEN '3' ELSE CASE WHEN obi_freeongkir = 'T' THEN '3' ELSE '7' END END, ";
                        $query .= "obi_ekspedisi = CASE WHEN obi_flagbayar = 'Y' THEN obi_ekspedisi ELSE 0 END, ";
                        $query .= "obi_zona = '1', ";
                        $query .= "obi_kdekspedisi = CASE WHEN obi_flagbayar = 'Y' THEN obi_kdekspedisi ELSE null END, ";
                        $query .= "obi_realcashback = 0 ";
                        $query .= "where obi_notrans = '" . $noTrans . "' ";
                        $query .= "and obi_nopb =  '" . $noPB . "' ";
                        DB::update($query);


                        //! Kalo COD-POIN balikin ke COD
                        $query = '';
                        $query .= "UPDATE TBTR_OBI_H ";
                        $query .= "SET OBI_TIPEBAYAR = 'COD' ";
                        $query .= "WHERE OBI_NOPB = '" . $noPB . "' ";
                        $query .= "    AND OBI_NOTRANS = '" . $noTrans . "' ";
                        $query .= "    AND OBI_TIPEBAYAR = 'COD-POIN' ";
                        DB::update($query);

                        //! BATALIN Realisasi Promo Klikigr
                        $query = '';
                        $query .= "UPDATE promo_klikigr ";
                        $query .= "   SET cashback_real = NULL, gift_real = NULL, kelipatan = 1 ";
                        $query .= " WHERE kode_member = '" . $kdMember . "' ";
                        $query .= "  AND no_trans = '" . $noTrans . "' ";
                        $query .= "  AND no_pb = '" . $noPB . "' ";
                        DB::update($query);

                        //! BATALIN DSP SPI
                        $query = '';
                        $query .= "DELETE FROM tbtr_dsp_spi ";
                        $query .= " WHERE dsp_kodemember = '" . $kdMember . "' ";
                        $query .= "  AND dsp_notrans = '" . $noTrans . "' ";
                        $query .= "  AND dsp_nopb = '" . $noPB . "' ";
                        DB::delete($query);

                        //! BATALIN DRAFT STRUK - KURANG INTRANSIT
                        $query = '';
                        $query .= "SELECT obi_nopb ";
                        $query .= "FROM tbtr_obi_h ";
                        $query .= " WHERE obi_notrans = '" . $noTrans . "' ";
                        $query .= " AND obi_nopb = '" . $noPB . "' ";
                        $query .= " AND UPPER(obi_attribute2) IN ('KLIKIGR','CORP','SPI') ";
                        $dtCek = DB::select($query);

                        if(count($dtCek) > 0){
                            //! BELUM SELESAI
                            // updateIntransit(False, noTrans, dtTrans.Value.ToString("dd-MM-yyyy"))
                        }
                    }
                }
            }
        }

    }

    private function requestPromo($transKlik, $kdMember, $noTrans, $noPB){
        //! BELUM SELESAI
        //! INI GAPAHAM SUSAH DIBACA
        //! TANYAKAN AKSES API UNTUK BODYNYA FORMAT DATANYA SEPERTI APA

        // $query ".= SELECT ws_url, cre_name, cre_key FROM tbmaster_webservice  ";
        // sb.AppendLine(" LEFT JOIN tbmaster_credential ON ws_nama = cre_type ")
        // If flagSPI Then
        //     sb.AppendLine(" WHERE ws_nama = 'HIT_PROMO_SPI' ")
        // Else
        //     sb.AppendLine(" WHERE ws_nama = 'HIT_PROMO_KLIK' ")
        // End If
        // dtGet = QueryOra(sb.ToString)

        // If dtGet.Rows.Count > 0 Then
        //     urlPromo = dtGet.Rows(0).Item(0).ToString
        //     creName = dtGet.Rows(0).Item(1).ToString
        //     creKey = dtGet.Rows(0).Item(2).ToString
        // Else
        //     urlPromo = "https://api.mitraindogrosir.co.id/dataportal/klik-igr/flex-promo/recalculate-new"
        //     creName = "x-api-key"
        //     creKey = "zEebxEx3Y44V8UdNJdkOE79HdYEMKDER1eEvYg2T"
        // End If


    }

    private function validasiDataMember($noTrans, $dtTrans){
        $cekAlamatMember = DB::select("SELECT DISTINCT OBI_KODEALAMAT FROM TBTR_OBI_D WHERE OBI_NOTRANS = '" . $noTrans . "' AND obi_tgltrans=".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')." ");
        if(!count($cekAlamatMember)){
            $message = 'Data OBI_KODEALAMAT pada TBTR_OBI_D tidak ditemukan. no trans : ' . $noTrans .' | tanggal :' . Carbon::parse($dtTrans)->format('Y-m-d H:i:s');
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        //! CEK DATA ALAMAT
        // oraFoundAlamat = ORADataFound("Tbtr_ALAMAT_MM, TBTR_OBI_H",
        //                                           "amm_kodemember = obi_kdmember " &
        //                                           " AND amm_nopb = obi_nopb " &
        //                                           " AND amm_notrans = obi_notrans " &
        //                                           " AND amm_tglpb = obi_tglpb " &
        //                                           " AND obi_notrans = '" & notrans & "' " &
        //                                           " AND obi_tgltrans = TO_DATE('" & dtTrans.Value.ToString("dd-MM-yyyy") & "','dd-MM-yyyy')")

        //* Kode alamat tidak terdaftar. Apabila data tidak lengkap, tidak dapat melakukan Draft Struk

        //! CEK DATA PERSONAL MEMBER
        // oraMemberFound = ORADataFound("TBMASTER_CUSTOMER, TBTR_OBI_H",
        //                                       "cus_kodemember = obi_kdmember " &
        //                                       " AND obi_notrans = '" & dgv_notrans & "'" &
        //                                       " AND DATE_TRUNC('DAY',obi_tgltrans) = TO_DATE('" & dtTrans.Value.ToString("dd-MM-yyyy") & "','dd-MM-yyyy')")

        //* "data member untuk no transaksi " & dgv_notrans & tidak ditemukan. Silahkan update data member melalui menu MEMBER.", "INDOGROSIR")
    }

    private function cekItemRealisasi($dgv_notrans, $dgv_nopb){
        $data = DB::select("SELECT SUM(OBI_QTYREALISASI) FROM TBTR_OBI_D WHERE OBI_NOTRANS = '" . $dgv_notrans . "' and obi_tgltrans = (select obi_tgltrans from tbtr_obi_h where obi_nopb = '" . $dgv_nopb . "')");
        return $data[0]->sum;
    }

    private function setOngkir($dgv_flagBayar, $dgv_nopb, $dgv_tglpb, $dgv_notrans, $dgv_freeongkir, $dgv_jarakkirim, $dgv_memberigr){

        $flagLockJarak = false;

        // if($dgv_flagBayar == 'Y'){
        //     DB::table('tbtr_obi_h')
        //         ->where([
        //             'obi_nopb' => $dgv_nopb,
        //             'obi_tglpb' => $dgv_tglpb,
        //             'obi_notrans' => $dgv_notrans,
        //         ])
        //         ->update([
        //             'obi_recid' => 3,
        //         ]);

        //         $message = 'Skip Ongkos Kirim Karena Pembayaran Di Web';
        //         throw new HttpResponseException(ApiFormatter::success(200, $message));
        // }else{

            if($dgv_freeongkir != 'T'){
                if($dgv_freeongkir == 'Y'){
                    $data["flagFree"] = true;
                }else{
                    $data["flagFree"] = false;
                }

                $jarakKlik = $dgv_jarakkirim;

                if($jarakKlik > 0){
                    $data["jarakKirim"] = $jarakKlik;
                    $flagLockJarak = true;
                }else{
                    $query = '';
                    $query .= "SELECT COALESCE(hj_jarak, 0) jarakkirim  ";
                    $query .= "  FROM history_jarak ";
                    $query .= " WHERE hj_kodeigr = '" . session('KODECABANG') . "' ";
                    $query .= "   AND hj_kdmember = '" . $dgv_memberigr . "' ";
                    $query .= "   AND hj_alamat = ( ";
                    $query .= "                    SELECT amm_namaalamat ";
                    $query .= "                      FROM tbtr_alamat_mm ";
                    $query .= "                     WHERE amm_kodemember = '" . $dgv_memberigr . "' ";
                    $query .= "                       AND amm_notrans = '" . $dgv_notrans . "' ";
                    $query .= "                       AND amm_nopb = '" . $dgv_nopb . "' ";
                    $dtJarak = DB::select($query);

                    if(count($dtJarak)){
                        $jarakKlik = $dtJarak[0]->jarakkirim;
                        $data["jarakKirim"] = $jarakKlik;
                        $flagLockJarak = true;
                    }else{
                        $data["jarakKirim"] = 0;
                        $flagLockJarak = false;
                    }
                }
                
                $query = "SELECT eks_namaekspedisi, eks_kodeekspedisi ";
                $query .= "FROM tbmaster_obi_ekspedisi WHERE eks_kodeigr = '" . session('KODECABANG') . "' order by 2";
                $data['namaEkspedisi1'] = DB::select($query);

                $query = "SELECT id, title FROM master_shipping_klikigr ";
                $query .= " WHERE UPPER(title) NOT LIKE '%AMBIL%TOKO%' ";
                // $query += " AND UPPER(title) NOT LIKE '%KURIR%INDOGROSIR%' ";
                $query .= " ORDER BY id ASC ";

                $data['namaEkspedisi2'] = DB::select($query);

                throw new HttpResponseException(ApiFormatter::success(201, "showFormEkspedisi", $data));

                //* SHOW FORM -> frmEkspedisi
                // FlagEkspedisi = frmEkspedisi.Flag
                // _biayaEkspedisi = frmEkspedisi.Ongkos
                // _zona = frmEkspedisi.Zona
                // _kdEkspedisi = frmEkspedisi.kdEksp
                // _beratEkspedisi = frmEkspedisi.Jarak
            }else{
                $FlagEkspedisi = "YY";
                $biayaEkspedisi = 0;
                $zona = "1";
                $kdEkspedisi = 0;
                $beratEkspedisi = 0;
            }

            if($FlagEkspedisi == 'Y' OR $FlagEkspedisi == 'YY'){
                $nmEks = $kdEkspedisi;
                if($kdEkspedisi > 1){
                    $nmEks = DB::select("select eks_namaekspedisi from tbmaster_obi_ekspedisi where eks_kodeekspedisi")[0]->eks_namaekspedisi;
                }else{
                    $nmEks = '-';
                }

                $query = "UPDATE tbtr_obi_h ";
                $query .= "   SET obi_ekspedisi = '" . $biayaEkspedisi . "', ";
                $query .= "       obi_jrkekspedisi = '" . $beratEkspedisi . "', ";
                $query .= "       obi_kdekspedisi = '" . $nmEks . "', ";
                $query .= "       obi_zona = '" . $zona . "', ";
                $query .= "       obi_recid = '3' ";
                $query .= " WHERE obi_nopb = '" . $dgv_nopb . "', ";
                $query .= "   AND obi_tglpb = '" . Carbon::parse($dgv_tglpb)->format('Y-m-d H:i:s') . "', ";
                $query .= "   AND obi_notrans = '" . $dgv_notrans . "'";

                if($flagLockJarak == false){
                    $query .= "INSERT INTO history_jarak ";
                    $query .= "SELECT obi_kodeigr, ";
                    $query .= "       obi_kdmember, ";
                    $query .= "       amm_namaalamat, ";
                    $query .= "       obi_jrkekspedisi ";
                    $query .= "  FROM tbtr_obi_h ";
                    $query .= "  JOIN tbtr_alamat_mm ";
                    $query .= "    ON amm_kodemember = obi_kdmember ";
                    $query .= "   AND amm_notrans = obi_notrans ";
                    $query .= "   AND amm_nopb = obi_nopb ";
                    $query .= " WHERE obi_kdmember = '" . $dgv_memberigr . "' ";
                    $query .= "   AND obi_notrans = '" . $dgv_notrans . "' ";
                    $query .= "   AND obi_nopb = '" . $dgv_nopb . "' ";
                }

                $message = 'Data Ongkos Kirim Berhasil Disimpan.';
                throw new HttpResponseException(ApiFormatter::error(200, $message));
            }

        // }



    }

    private function sendSPI($dgv_nopb, $dgv_notrans, $dgv_memberigr, $dtTrans){
        $nopb = $dgv_nopb;
        $notrans = $dgv_notrans;
        $tglTrans = $dtTrans;
        $memberigr = $dgv_memberigr;
        $noPick = '';
        $noSJ = '';

        //! TOLAKAN ZONA, JALUR, LOKASI, NOID
        $query = '';
        $query .= "SELECT obi_prdcd  ";
        $query .= "FROM tbtr_obi_d  ";
        $query .= " WHERE DATE_TRUNC('DAY', obi_tgltrans) = '" . Carbon::parse($tglTrans)->format('Y-m-d H:i:s') . "' ";
        $query .= "AND obi_notrans = '" . $notrans . "' ";
        $query .= "AND obi_recid IS NULL ";
        $query .= "AND NOT EXISTS ( ";
        $query .= "  SELECT lks_noid ";
        $query .= "    FROM tbmaster_lokasi ";
        $query .= "    JOIN tbmaster_grouprak ";
        $query .= "      ON grr_koderak = lks_koderak ";
        $query .= "     AND grr_subrak = lks_kodesubrak ";
        $query .= "    JOIN tbmaster_stock ";
        $query .= "      ON st_prdcd = lks_prdcd ";
        $query .= "   WHERE st_lokasi = '01' ";
        $query .= "     AND lks_tiperak NOT LIKE 'S%' ";
        $query .= "     AND lks_noid IS NOT NULL ";
        $query .= "     AND COALESCE(grr_flagcetakan,'X') <> 'Y' ";
        $query .= "     AND grr_grouprak NOT LIKE 'H%' ";
        $query .= "     AND lks_prdcd = SUBSTR(obi_prdcd,1,6) || '0' ";
        $query .= ") ";
        $dtCek = DB::select($query);

        $txtPlu = '';
        foreach($dtCek as $item){
            $txtPlu .= $item->obi_prdcd . ',';
        }

        if($txtPlu != ''){
            //* Terdapat PLU yang tidak memiliki lokasi DPD :
            $message = 'Terdapat PLU yang tidak memiliki lokasi DPD : ' . rtrim($txtPlu, ",");
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        //! TOLAKAN STOCK
        $query = '';
        $query .= "SELECT obi_prdcd ";
        $query .= "FROM tbtr_obi_d ";
        $query .= "LEFT JOIN tbmaster_stock ";
        $query .= "ON st_prdcd = SUBSTR(obi_prdcd,1,6) || '0' ";
        $query .= "WHERE DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')." ";
        $query .= "AND obi_notrans = '" . $notrans . "' ";
        $query .= "AND obi_recid IS NULL ";
        $query .= "AND st_lokasi = '01' ";
        $query .= "AND COALESCE(st_saldoakhir,0) < obi_qtyorder ";
        $query .= "ORDER BY obi_prdcd ASC ";
        $dtCek = DB::select($query);

        $txtPlu = '';
        foreach($dtCek as $item){
            $txtPlu .= $item->obi_prdcd . ',';
        }

        if($txtPlu != ''){
            //* Stock Item berikut tidak mencukupi :
            $message = 'Stock Item berikut tidak mencukupi : ' . rtrim($txtPlu, ",");
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        //! TOLAKAN QTY PLANO
        $query = '';
        $query .= "SELECT plu ";
        $query .= "FROM ( ";
        $query .= "    SELECT SUBSTR(obi_prdcd, 1, 6) || '0' plu, ";
        $query .= "        SUM(obi_qtyorder) qtyorder ";
        $query .= "    FROM tbtr_obi_d ";
        $query .= "    WHERE DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
        $query .= "        AND obi_notrans = '" . $notrans . "' ";
        $query .= "        AND obi_recid IS NULL ";
        $query .= "    GROUP BY SUBSTR(obi_prdcd, 1, 6) ";
        $query .= ") ";
        $query .= "LEFT JOIN ( ";
        $query .= "    SELECT lks_prdcd, ";
        $query .= "        SUM(lks_qty) qtyLPP ";
        $query .= "    FROM tbmaster_lokasi ";
        $query .= "    JOIN tbtr_obi_d ON lks_prdcd = obi_prdcd ";
        $query .= "    WHERE DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
        $query .= "        AND obi_notrans = '" . $notrans . "' ";
        $query .= "        AND obi_recid IS NULL     ";
        $query .= "    GROUP BY lks_prdcd ";
        $query .= ") ON lks_prdcd = plu ";
        $query .= "WHERE qtyLPP < qtyorder ";
        $query .= "ORDER BY plu ASC ";
        $dtCek = DB::select($query);

        $txtPlu = '';
        foreach($dtCek as $item){
            $txtPlu .= $item->plu . ',';
        }

        if($txtPlu != ''){
            //* Item berikut Qty Plano tidak memenuhi Qty PB :
            $message = 'Item berikut Qty Plano tidak memenuhi Qty PB : ' . rtrim($txtPlu, ",");
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        DB::beginTransaction();
        try {

            $sukses = $this->sendJalur_SPI($memberigr, $nopb, $notrans, $tglTrans, $noPick, $noSJ);

            if($sukses == true){
                $query = '';
                $query .= "UPDATE TBTR_OBI_H SET OBI_RECID = '1', OBI_SENDPICK = NOW(), ";
                $query .= " OBI_NOPICK = '" . $noPick . "', OBI_NOSJ = '" . $noSJ . "', OBI_FLAGSENDHH = '1' ";
                $query .= "WHERE DATE_TRUNC('DAY',OBI_TGLTRANS) = TO_DATE('" . $tglTrans . "','dd-MM-yyyy') ";
                $query .= "AND OBI_NOPB = '" . $nopb . "' ";
                $query .= "AND OBI_KDMEMBER = '" . $memberigr . "' ";
                $query .= "AND OBI_NOTRANS = '" . $notrans . "' ";
                $query .= "AND OBI_RECID IS NULL ";
                DB::update($query);

                $this->rptPickingList999($tglTrans, $notrans, $nopb, $memberigr, True);

                $this->logUpdateStatus($notrans, $tglTrans, $nopb, "1", "2");

                throw new HttpResponseException(ApiFormatter::success(200, 'Selesai Send Jalur SPI'));
            }else{
                throw new HttpResponseException(ApiFormatter::error(400, 'GAGAL Send Jalur SPI'));

            }

        } catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            return ApiFormatter::error(400, $message);
        }
    }

    private function sendHH($dgv_nopb, $dgv_notrans, $dgv_memberigr, $dtTrans, $cbPickRakToko){
        $nopb = $dgv_nopb;
        $notrans = $dgv_notrans;
        $memberigr = $dgv_memberigr;
        $listPLUMasalah = [];

        //! TOLAKAN TAG TIDAK BOLEH JUAL
        $query = '';
        $query .= "SELECT obi_prdcd  ";
        $query .= "FROM tbtr_obi_d  ";
        $query .= "WHERE DATE_TRUNC('DAY', obi_tgltrans) = '" . Carbon::parse($dtTrans)->format('Y-m-d H:i:s') . "' ";
        $query .= "AND obi_notrans = '" . $notrans . "' ";
        $query .= "AND obi_recid IS NULL ";
        $query .= "AND EXISTS ( ";
        $query .= "  SELECT tag_kodetag ";
        $query .= "  FROM tbmaster_prodmast ";
        $query .= "  JOIN tbmaster_tag  ";
        $query .= "  ON tag_kodetag = prd_kodetag ";
        $query .= "  AND COALESCE(tag_tidakbolehjual,'N') = 'Y' ";
        $query .= "  WHERE prd_prdcd = obi_prdcd ";
        $query .= ") ";
        $dtCek = DB::select($query);

        $txtPlu = '';
        foreach($dtCek as $item){
            $listPLUMasalah[] = $item->obi_prdcd;
            $txtPlu .= $item->obi_prdcd . ',';
        }

        if($txtPlu != ''){
            //* Terdapat PLU dengan Tag Tidak Boleh Jual :
            $message = 'Terdapat PLU dengan Tag Tidak Boleh Jual : ' . rtrim($txtPlu, ",");
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        //! CEK ITEM UNIT KG
        $query = '';
        $query .= "SELECT obi_prdcd  ";
        $query .= "FROM tbtr_obi_d  ";
        $query .= "JOIN tbmaster_prodmast ";
        $query .= "ON prd_prdcd = obi_prdcd ";
        $query .= "WHERE DATE_TRUNC('DAY', obi_tgltrans) = '" . Carbon::parse($dtTrans)->format('Y-m-d H:i:s') . "' ";
        $query .= "AND obi_notrans = '" . $notrans . "' ";
        $query .= "AND obi_recid IS NULL ";
        $query .= "AND COALESCE(prd_unit,'PCS') = 'KG' ";
        $dt = DB::select($query);

        $flagKG = True;
        if(count($dt) == 0){
            $flagKG = false;
        }else{
            //! CEK DATA KONVERSI ITEM UNIT KG
            $query = '';
            $query .= "SELECT obi_prdcd  ";
            $query .= "FROM tbtr_obi_d  ";
            $query .= "JOIN tbmaster_prodmast ";
            $query .= "ON prd_prdcd = obi_prdcd ";
            $query .= "WHERE DATE_TRUNC('DAY', obi_tgltrans) = '" . Carbon::parse($dtTrans)->format('Y-m-d H:i:s') . "' ";
            $query .= "AND obi_notrans = '" . $notrans . "' ";
            $query .= "AND obi_recid IS NULL ";
            $query .= "AND COALESCE(prd_unit,'PCS') = 'KG' ";
            $query .= "AND NOT EXISTS ( ";
            $query .= "  SELECT 1 ";
            $query .= "  FROM konversi_item_klikigr ";
            $query .= "  WHERE substr(obi_prdcd, 1, 6) || '0' = substr(pluigr, 1, 6) || '0' ";
            $query .= ") ";
            $dtCek = DB::select($query);

            $txtPlu = '';
            foreach($dtCek as $item){
                $listPLUMasalah[] = $item->obi_prdcd;
                $txtPlu .= $item->obi_prdcd . ',';
            }

            if($txtPlu != ''){
                //* Terdapat PLU yang tidak ada data Konversi-nya: " & plu & " pada nomor pb " & nopb
                $message = 'Terdapat PLU yang tidak ada data Konversi-nya: ' .  rtrim($txtPlu, ",") . ' pada nomor pb : ' . $nopb;
                throw new HttpResponseException(ApiFormatter::error(400, $message));
            }

            //! KASIH FLAG OBI_ITEMKG DI TBTR_OBI_D
            $query = '';
            $query .= "UPDATE tbtr_obi_d ";
            $query .= "SET obi_itemkg = 1, ";
            $query .= "obi_qtyrealisasi = obi_qtyorder, ";
            $query .= "obi_pick_dt = NOW(), ";
            $query .= "obi_close_dt = NOW(), ";
            $query .= "obi_urut_pick = 1, ";
            $query .= "obi_picker = 'PER' ";
            $query .= "WHERE DATE_TRUNC('DAY', obi_tgltrans) = '" . Carbon::parse($dtTrans)->format('Y-m-d H:i:s') . "' ";
            $query .= "AND obi_notrans = '" . $notrans . "' ";
            $query .= "AND obi_recid IS NULL ";
            $query .= "AND EXISTS ( ";
            $query .= " SELECT prd_unit ";
            $query .= " FROM tbmaster_prodmast ";
            $query .= " WHERE prd_prdcd = obi_prdcd ";
            $query .= " AND COALESCE(prd_unit,'PCS') = 'KG' ";
            $query .= ") ";
            DB::update($query);
        }

        //! GROUP PICKING
        $query = '';
        $query .= "SELECT pk_group ";
        $query .= "FROM ( ";
        $query .= "  SELECT pk_group, count(1) jml ";
        $query .= "  FROM picker_klik ";
        $query .= "  WHERE pk_group IS NOT NULL ";
        $query .= "  GROUP BY pk_group ";
        $query .= "  ORDER BY jml DESC ";
        $query .= ") AS dt ";
        $query .= "limit 1 ";
        $groupPickingTerbanyak = DB::select($query)[0]->pk_group;

        if($groupPickingTerbanyak == ''){
            throw new HttpResponseException(ApiFormatter::error(400, 'Master Group Picking Belum Disetting'));
        }

        $query = '';
        $query .= "SELECT DISTINCT rak ";
        $query .= "FROM ( ";
        $query .= "    SELECT DISTINCT MIN(lks_koderak || '.' || lks_kodesubrak) rak, lks_prdcd ";
        $query .= "    FROM tbmaster_lokasi ";

        if($cbPickRakToko){
            $query .= "    WHERE (lks_koderak LIKE 'R%' OR lks_koderak LIKE 'O%' OR lks_koderak LIKE 'DKLIK%' OR lks_koderak LIKE 'P%') ";
        }else{
            $query .= "    WHERE (lks_koderak LIKE 'R%' OR lks_koderak LIKE 'O%' OR lks_koderak LIKE 'D%' OR lks_koderak LIKE 'P%') ";
        }

        $query .= "    AND (lks_tiperak LIKE 'B%' OR lks_tiperak LIKE 'I%' OR lks_tiperak LIKE 'N%') ";
        $query .= "    AND COALESCE(lks_noid,'99999') NOT LIKE ( ";
        $query .= "        CASE WHEN ( ";
        $query .= "            SELECT COUNT(1) ";
        $query .= "            FROM tbmaster_lokasi ";

        if($cbPickRakToko){
            $query .= "            WHERE (lks_koderak LIKE 'R%' OR lks_koderak LIKE 'O%' OR lks_koderak LIKE 'DKLIK%' OR lks_koderak LIKE 'P%') ";
        }else{
            $query .= "            WHERE (lks_koderak LIKE 'R%' OR lks_koderak LIKE 'O%' OR lks_koderak LIKE 'D%' OR lks_koderak LIKE 'P%') ";
        }

        $query .= "            AND (lks_tiperak LIKE 'B%' OR lks_tiperak LIKE 'I%' OR lks_tiperak LIKE 'N%') ";
        $query .= "            AND COALESCE(lks_noid,'99999') NOT LIKE '%B' ";
        $query .= "            AND EXISTS ( ";
        $query .= "                SELECT obi_prdcd ";
        $query .= "                FROM tbtr_obi_d ";
        $query .= "                WHERE obi_recid IS NULL AND coalesce(obi_itemkg,0) = 0 ";
        $query .= "                    AND DATE_TRUNC('DAY',obi_tgltrans) = '".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."'  ";
        $query .= "                    AND obi_notrans = '" . $notrans . "' ";
        $query .= "                    AND substr(obi_prdcd,1,6) || '0' = lks_prdcd ";
        $query .= "            ) ";
        $query .= "        ) != '0' THEN '%B' ELSE '99999' END ";
        $query .= "    ) ";
        $query .= "    AND EXISTS ( ";
        $query .= "        SELECT obi_prdcd ";
        $query .= "        FROM tbtr_obi_d ";
        $query .= "        WHERE obi_recid IS NULL AND coalesce(obi_itemkg,0) = 0 ";
        $query .= "            AND DATE_TRUNC('DAY',obi_tgltrans) = '".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."'  ";
        $query .= "            AND obi_notrans = '" . $notrans . "' ";
        $query .= "            AND substr(obi_prdcd,1,6) || '0' = lks_prdcd ";
        $query .= "    ) ";
        $query .= "    GROUP BY lks_prdcd ";
        $query .= ") list_rak ";
        $query .= "WHERE NOT EXISTS ( ";
        $query .= "	   SELECT pk_userid ";
        $query .= "	   FROM picker_klik ";
        $query .= "	   WHERE pk_koderak || '.' || pk_kodesubrak  = rak ";
        $query .= "		   AND pk_group = '" . $groupPickingTerbanyak . "' ";
        $query .= ") ORDER BY 1 ";
        $dt = DB::select($query);

        $lok = '';
        foreach($dt as $item){
            $lok += $item->rak . ',';
        }

        if($lok != ''){
            //* Master Picking Belum Disetting Untuk: " & lok & " pada nomor pb " & nopb
            $message = 'Master Picking Belum Disetting Untuk : ' . rtrim($lok, ",") . ' pada nomor pb : ' . $nopb;
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $query = '';
        $query .= "SELECT COUNT(1) FROM ( ";
        $query .= "    SELECT D.*, COALESCE( ";
        $query .= "        ( ";
        $query .= "            SELECT COUNT(1) ";
        $query .= "            FROM tbmaster_lokasi AS tb1 ";

        if($cbPickRakToko){
            $query .= "            WHERE (tb1.lks_koderak LIKE 'R%' OR tb1.lks_koderak LIKE 'O%' OR tb1.lks_koderak LIKE 'DKLIK%' OR tb1.lks_koderak LIKE 'P%') ";
        }else{
            $query .= "            WHERE (tb1.lks_koderak LIKE 'R%' OR tb1.lks_koderak LIKE 'O%' OR tb1.lks_koderak LIKE 'D%' OR tb1.lks_koderak LIKE 'P%') ";
        }

        $query .= "                AND (tb1.lks_tiperak LIKE 'B%' OR tb1.lks_tiperak LIKE 'I%' OR tb1.lks_tiperak LIKE 'N%') ";
        $query .= "                AND COALESCE(tb1.lks_noid,'99999') NOT LIKE ( ";
        $query .= "                    CASE WHEN ( ";
        $query .= "                        SELECT COUNT(1) ";
        $query .= "                        FROM tbmaster_lokasi AS tb2 ";

        if($cbPickRakToko){
            $query .= "                        WHERE (tb2.lks_koderak LIKE 'R%' OR tb2.lks_koderak LIKE 'O%' OR tb2.lks_koderak LIKE 'DKLIK%' OR tb2.lks_koderak LIKE 'P%') ";
        }else{
            $query .= "                        WHERE (tb2.lks_koderak LIKE 'R%' OR tb2.lks_koderak LIKE 'O%' OR tb2.lks_koderak LIKE 'D%' OR tb2.lks_koderak LIKE 'P%') ";
        }

        $query .= "                            AND (tb2.lks_tiperak LIKE 'B%' OR tb2.lks_tiperak LIKE 'I%' OR tb2.lks_tiperak LIKE 'N%') ";
        $query .= "                            AND COALESCE(tb2.lks_noid,'99999') NOT LIKE '%B' ";
        $query .= "                            AND LKS_PRDCD = SUBSTR(OBI_PRDCD,1,6) || '0' ";
        $query .= "                    ) != '0' THEN '%B' ELSE '99999' END ";
        $query .= "                ) ";
        $query .= "                AND LKS_PRDCD = SUBSTR(OBI_PRDCD,1,6) || '0' ";
        $query .= "        ),0 ";
        $query .= "    ) LOKASI FROM TBTR_OBI_D D ";
        $query .= "    WHERE OBI_TGLTRANS = :OBI_TGLTRANS ";
        $query .= "		   AND OBI_NOTRANS = :OBI_NOTRANS ";
        $query .= "		   AND OBI_RECID IS NULL ";
        $query .= "		   AND COALESCE(OBI_ITEMKG,0) = 0 ";
        $query .= ") AS subquery WHERE LOKASI = 0 ";

        $pluDouble = DB::select($query);

        if(count($pluDouble) > 0){
            $query = '';
            $query .= "SELECT obi_prdcd FROM ( ";
            $query .= "    SELECT D.*, COALESCE( ";
            $query .= "        ( ";
            $query .= "            SELECT COUNT(1) ";
            $query .= "            FROM tbmaster_lokasi ";

            if($cbPickRakToko){
                $query .= "            WHERE (lks_koderak LIKE 'R%' OR lks_koderak LIKE 'O%' OR lks_koderak LIKE 'DKLIK%' OR lks_koderak LIKE 'P%') ";
            }else{
                $query .= "            WHERE (lks_koderak LIKE 'R%' OR lks_koderak LIKE 'O%' OR lks_koderak LIKE 'D%' OR lks_koderak LIKE 'P%') ";
            }

            $query .= "                AND (lks_tiperak LIKE 'B%' OR lks_tiperak LIKE 'I%' OR lks_tiperak LIKE 'N%') ";
            $query .= "                AND COALESCE(lks_noid,'99999') NOT LIKE ( ";
            $query .= "                    CASE WHEN ( ";
            $query .= "                        SELECT COUNT(1) ";
            $query .= "                        FROM tbmaster_lokasi ";

            if($cbPickRakToko){
                $query .= "                        WHERE (lks_koderak LIKE 'R%' OR lks_koderak LIKE 'O%' OR lks_koderak LIKE 'DKLIK%' OR lks_koderak LIKE 'P%') ";
            }else{
                $query .= "                        WHERE (lks_koderak LIKE 'R%' OR lks_koderak LIKE 'O%' OR lks_koderak LIKE 'D%' OR lks_koderak LIKE 'P%') ";
            }

            $query .= "                            AND (lks_tiperak LIKE 'B%' OR lks_tiperak LIKE 'I%' OR lks_tiperak LIKE 'N%') ";
            $query .= "                            AND COALESCE(lks_noid,'99999') NOT LIKE '%B' ";
            $query .= "                            AND LKS_PRDCD = SUBSTR(OBI_PRDCD,1,6) || '0' ";
            $query .= "                    ) != '0' THEN '%B' ELSE '99999' END ";
            $query .= "                ) ";
            $query .= "                AND LKS_PRDCD = SUBSTR(OBI_PRDCD,1,6) || '0' ";
            $query .= "        ),0 ";
            $query .= "    ) LOKASI FROM TBTR_OBI_D D ";
            $query .= "    WHERE OBI_TGLTRANS = '".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."'  ";
            $query .= "		   AND OBI_NOTRANS = '" . $notrans . "' ";
            $query .= "		   AND OBI_RECID IS NULL ";
            $query .= "		   AND COALESCE(OBI_ITEMKG,0) = 0 ";
            $query .= ") WHERE LOKASI = 0 ";
            $dt = DB::select($query);

            //! FUNCTION INI BELUM SELESAI
            // logPLUtanpaLokasi(nopb, dtprdcd)

            $txtPlu = '';
            foreach($dt as $item2){
                $listPLUMasalah[] = $item2->obi_prdcd;
                $txtPlu .= $item->obi_prdcd;
            }

            //* Ada " & pluDouble & " Plu: " & plu & " Yang Tidak Punya Lokasi!
            $message = 'Ada ' . $pluDouble . 'plu : ' . rtrim($txtPlu, ",") . ' yang tidak punya Lokasi!';
            throw new HttpResponseException(ApiFormatter::error(400, $message));

        }else{

            $query = '';
            $query .= "SELECT obi_prdcd, obi_itemkg ";
            $query .= "FROM tbtr_obi_d ";
            $query .= "WHERE OBI_TGLTRANS = '".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."'  ";
            $query .= "AND OBI_NOTRANS = '" . $notrans . "' ";
            $query .= "AND OBI_RECID IS NULL ";
            $query .= "AND OBI_ITEMKG IS NULL ";
            $query .= "AND NOT EXISTS ( ";
            $query .= "  SELECT pluigr ";
            $query .= "  FROM konversi_item_klikigr ";
            $query .= "  WHERE SUBSTR(PLUIGR,1,6) || '0' = SUBSTR(OBI_PRDCD,1,6) || '0' ";
            $query .= "  AND SAT_JUAL = 'KG' ";
            $query .= ")";
            $dtNonPerishable = DB::select($query);

            $query = '';
            $query .= "SELECT obi_prdcd, obi_itemkg, sat_jual ";
            $query .= "FROM tbtr_obi_d ";
            $query .= "JOIN konversi_item_klikigr ";
            $query .= "ON SUBSTR(PLUIGR,1,6) || '0' = SUBSTR(OBI_PRDCD,1,6) || '0' ";
            $query .= "WHERE OBI_TGLTRANS = '".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."'  ";
            $query .= "AND OBI_NOTRANS = '" . $notrans . "' ";
            $query .= "AND OBI_RECID IS NULL ";
            $query .= "AND OBI_ITEMKG = 1 ";
            $query .= "AND SAT_JUAL = 'KG' ";
            $dtPerishable = DB::select($query);

            $query .= "UPDATE TBTR_OBI_H ";
            if(count($dtNonPerishable) == 0 AND count($dtPerishable) > 0){
                $query .= "SET OBI_RECID = '2', ";
            }else{
                $query .= "SET OBI_RECID = '1', ";
            }

            $query .= "    OBI_SENDPICK = NOW(), ";
            $query .= "    OBI_FLAGSENDHH = '2' ";
            if(count($dtNonPerishable) == 0 AND count($dtPerishable) > 0){
                $query .= ", obi_selesaipick = NOW() ";
            }
            $query .= "WHERE OBI_TGLTRANS = '".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."'  ";
            $query .= "AND OBI_NOPB ='" . $nopb . "' ";
            $query .= "AND OBI_KDMEMBER ='" . $memberigr . "' ";
            $query .= "AND OBI_NOTRANS ='" . $notrans . "'";
            $query .= "AND OBI_RECID IS NULL ";
            DB::update($query);

            if(count($dtNonPerishable) == 0 AND count($dtPerishable) > 0){
                $query = '';
                $query .= "UPDATE tbtr_obi_d ";
                $query .= "   SET obi_close_dt = Current_date ";
                $query .= "WHERE DATE_TRUNC('DAY',OBI_TGLTRANS) = '".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."'  ";
                $query .= "AND OBI_NOTRANS = '" & $notrans & "' ";
                $query .= "AND OBI_RECID IS NULL ";
                DB::update($query);
            }

            //! Update lokasi picking rak biasa
            $query .= "UPDATE tbtr_obi_d ";
            $query .= "SET (obi_koderak, obi_kodesubrak, obi_tiperak, obi_shelvingrak, obi_nourut) ";
            $query .= "  = ( ";
            $query .= "	SELECT lks_koderak, lks_kodesubrak, lks_tiperak, lks_shelvingrak, lks_nourut ";
            $query .= "	FROM ( ";
            $query .= "		SELECT lks_koderak, lks_kodesubrak, lks_tiperak, lks_shelvingrak, lks_nourut, lks_prdcd ";
            $query .= "		FROM tbmaster_lokasi ";
            $query .= "		JOIN picker_klik ";
            $query .= "		ON pk_koderak = lks_koderak ";
            $query .= "			AND pk_kodesubrak = lks_kodesubrak ";
            $query .= "			AND pk_group = '" . $groupPickingTerbanyak . "' ";
            $query .= "		WHERE ( ";

            if($cbPickRakToko){
                $query .= "		lks_koderak LIKE 'R%' OR lks_koderak LIKE 'O%' OR lks_koderak LIKE 'DKLIK%' OR lks_koderak LIKE 'P%' ";
            }else{
                $query .= "		lks_koderak LIKE 'R%' OR lks_koderak LIKE 'O%' OR lks_koderak LIKE 'D%' OR lks_koderak LIKE 'P%' ";
            }

            $query .= "		) ";
            $query .= "			AND ( ";
            $query .= "			lks_tiperak LIKE 'B%' OR lks_tiperak LIKE 'I%' OR lks_tiperak LIKE 'N%' ";
            $query .= "			) ";
            $query .= "			AND COALESCE(lks_noid,'99999') NOT LIKE '%B' ";
            $query .= "		ORDER BY coalesce(lks_noid,'99999') ASC, SUBSTR(lks_koderak,0,1) ASC, pk_urutan ASC ";
            $query .= "		) AS datas";
            $query .= "	WHERE lks_prdcd = SUBSTR(obi_prdcd, 1, 6) || '0' ";
            $query .= "	LIMIT 1 ";
            $query .= "  ) ";
            $query .= "WHERE OBI_RECID IS NULL AND COALESCE(OBI_ITEMKG,0) = 0 ";
            $query .= "AND DATE_TRUNC('DAY',OBI_TGLTRANS) = '".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."'  ";
            $query .= "AND OBI_NOTRANS = '" . $notrans . "' ";
        }
    }

    private function ulangPicking($dgv_notrans, $dgv_nopb){

        DB::table('tbtr_obi_h')->where([
            'obi_notrans' => $dgv_notrans,
            'obi_nopb' => $dgv_nopb,
        ])
        ->update([
           'obi_recid' => '1',
        ]);
    }

    private function batalDSP($dtTrans, $dgv_notrans, $dgv_nopb, $dgv_memberigr){
        $query = '';
        $query .= "update tbtr_obi_h set obi_realorder = 0, ";
        $query .= "obi_realppn = 0, ";
        $query .= "obi_realdiskon = 0, ";
        $query .= "obi_realitem = 0, ";
        $query .= "obi_recid = CASE WHEN obi_flagbayar = 'Y' THEN '3' ELSE CASE WHEN obi_freeongkir = 'T' THEN '3' ELSE '7' END END, ";
        $query .= "obi_ekspedisi = CASE WHEN obi_flagbayar = 'Y' THEN obi_ekspedisi ELSE 0 END, ";
        $query .= "obi_zona = '1', ";
        $query .= "obi_kdekspedisi = CASE WHEN obi_flagbayar = 'Y' THEN obi_kdekspedisi ELSE null END, ";
        $query .= "obi_realcashback = 0 ";
        $query .= "where obi_notrans = '" . $dgv_notrans . "' ";
        $query .= "and obi_nopb =  '" . $dgv_nopb . "' ";
        DB::insert($query);

        //! BATALIN Realisasi Promo Klikigr
        $query = '';
        $query .= "UPDATE promo_klikigr ";
        $query .= "   SET cashback_real = NULL, gift_real = NULL, kelipatan = 1 ";
        $query .= "WHERE kode_member = '" . $dgv_memberigr . "' ";
        $query .= "  AND no_trans = '" . $dgv_notrans . "' ";
        $query .= "  AND no_pb = '" . $dgv_nopb . "' ";
        DB::insert($query);

        //! BATALIN DSP SPI
        $query = '';
        $query .= "DELETE FROM tbtr_dsp_spi ";
        $query .= "WHERE dsp_kodemember = '" . $dgv_memberigr . "' ";
        $query .= "  AND dsp_notrans = '" . $dgv_notrans . "' ";
        $query .= "  AND dsp_nopb = '" . $dgv_nopb . "' ";
        DB::insert($query);

        //! BATALIN DRAFT STRUK - KURANG INTRANSIT
        $query = '';
        $query .= "SELECT obi_nopb ";
        $query .= "FROM tbtr_obi_h ";
        $query .= "WHERE obi_notrans = '" . $dgv_notrans . "' ";
        $query .= " AND obi_nopb = '" . $dgv_nopb . "' ";
        $query .= " AND UPPER(obi_attribute2) IN ('KLIKIGR','CORP','SPI') ";
        $cek = DB::select($query);

        if(count($cek) > 0){
            $this->updateIntransit(false, $dgv_notrans, $dtTrans);
        }

        $query = '';
        $query .= "UPDATE tbtr_obi_h ";
        $query .= "SET OBI_TIPEBAYAR = 'COD' ";
        $query .= "WHERE obi_nopb = '" . $dgv_nopb . "' ";
        $query .= "	AND obi_notrans = '" . $dgv_notrans . "' ";
        $query .= "	AND OBI_TIPEBAYAR = 'COD-POIN' ";
        DB::insert($query);

        $query = '';
        $query .= "UPDATE PAYMENT_KLIKIGR ";
        $query .= "SET COD_NONPAID = NULL ";
        $query .= "WHERE NO_PB = '" . $dgv_nopb . "' ";
        $query .= "	AND NO_TRANS = '" . $dgv_notrans . "' ";
        $query .= "	AND TIPE_BAYAR = 'COD' ";
        DB::insert($query);
    }

    private function rptPickingList999($tgltrans, $notrans, $nopb, $kodemember, $print){
        $query = '';
        $query .= " SELECT  ";
        $query .= "   h.obi_nopb NOPB, ";
        $query .= "   TO_CHAR(COALESCE(h.obi_sendpick,h.obi_tgltrans),'DD-MM-YYYY') TGLPICK, ";
        $query .= "   COALESCE(h.obi_nopick,0) NOPICK, ";
        $query .= "   h.obi_kdmember KDMEMBER, ";
        $query .= "   d.obi_prdcd PLU, ";
        $query .= "   p.prd_deskripsipendek DESKRIPSI, ";
        $query .= "   lks_koderak || '.' || lks_kodesubrak || '.' || lks_tiperak || ";
        $query .= "   '.' || lks_shelvingrak || '.' || lks_nourut  ALAMAT, ";
        $query .= "   d.obi_qtyorder QTYORDER ";
        $query .= " FROM tbtr_obi_h h ";
        $query .= " JOIN tbtr_obi_d d ";
        $query .= " ON h.obi_tgltrans = d.obi_tgltrans ";
        $query .= " AND h.obi_notrans = d.obi_notrans ";
        $query .= " JOIN tbmaster_lokasi l ";
        $query .= " ON l.lks_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= " AND l.lks_noid IS NOT NULL ";
        $query .= " AND SUBSTR(l.lks_koderak,1,1) IN ('D', 'G') ";
        $query .= " JOIN tbmaster_prodmast p ";
        $query .= " ON p.prd_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= " WHERE h.obi_tgltrans = TO_DATE('" . Carbon::parse($tgltrans)->format('d-m-Y') . "','DD-MM-YYYY') ";
        $query .= " AND h.obi_notrans = '" . $notrans . "' ";
        $query .= " AND h.obi_nopb = '" . $nopb . "' ";
        $query .= " AND h.obi_kdmember = '" . $kodemember . "' ";
        $query .= " AND d.obi_qtyorder > 999 ";
        $data = DB::select($query);

        if(!count($data)){
            throw new HttpResponseException(ApiFormatter::error(400, 'Tidak ada item PB yang melebihi 999'));
        }

        $str = "";
        $str .= PHP_EOL;
        $str .= "            PICKING LIST SPI            " . PHP_EOL;
        $str .= "========================================" . PHP_EOL;
        $str .= "No.PB   : " . $nopb . PHP_EOL;
        $str .= "No.Pick : " . str_pad($data[0]->nopick, 8, " ", STR_PAD_RIGHT) . " Tgl.Pick : " . $data[0]->tglpick . PHP_EOL;
        $str .= "KD Member : " . $kodemember . PHP_EOL;
        $str .= "========================================" . PHP_EOL;
        $str .= "No PLU - NAMA BARANG                    " . PHP_EOL;
        $str .= "   ALAMAT PICKING - QTYPB " . PHP_EOL;
        $str .= "========================================" . PHP_EOL;

        foreach ($data as $key => $item) {
            $nourut = $key + 1;
            $plu = $item->plu;
            $deskripsi = $item->deskripsi;
            $alamat = $item->alamat;
            $qty = $item->qtyorder;

            $str .= str_pad($nourut, 2, " ", STR_PAD_LEFT) . " ";
            $str .= str_pad($plu, 7, " ", STR_PAD_RIGHT) . " - " . str_pad($deskripsi, 25, " ", STR_PAD_RIGHT) . PHP_EOL;
            $str .= "   " . str_pad($alamat, 15, " ", STR_PAD_RIGHT) . "- " . $qty . PHP_EOL;
        }

        $str .= "========================================" . PHP_EOL;
        $str .= PHP_EOL;
        $str .= chr(29) . "V" . chr(66) . chr(0);

        $files_content['content'] = $str;
        $files_content['nama_file'] = "NOPICK_" . $data[0]->nopick . '.txt';
        return $files_content;

        //! REPORTNYA
        // Dim tglPicking As String = dtPicking.Rows(0).Item("TGLPICK").ToString.ToString
        // Dim nopick As String = dtPicking.Rows(0).Item("NOPICK").ToString
        // Dim str As String = ""
        // str &= vbCrLf
        // str &= "            PICKING LIST SPI            " & vbCrLf
        // str &= "========================================" & vbCrLf
        // str &= "No.PB   : " & nopb & vbCrLf
        // str &= "No.Pick : " & nopick.PadRight(8, " ") & " Tgl.Pick : " & tglPicking & vbCrLf
        // str &= "KD Member : " & kodemember & vbCrLf
        // str &= "========================================" & vbCrLf
        // str &= "No PLU - NAMA BARANG                    " & vbCrLf
        // str &= "   ALAMAT PICKING - QTYPB " & vbCrLf
        // str &= "========================================" & vbCrLf
        // For i As Integer = 0 To dtPicking.Rows.Count - 1
        //     Dim nourut As Integer = i + 1
        //     Dim plu As String = dtPicking(i).Item("PLU").ToString
        //     Dim deskripsi As String = dtPicking(i).Item("DESKRIPSI").ToString
        //     Dim alamat As String = dtPicking(i).Item("ALAMAT").ToString
        //     Dim qty As String = dtPicking(i).Item("QTYORDER").ToString

        //     str &= (nourut).ToString.PadLeft(2, " ").ToString & " "
        //     str &= plu.PadRight(7, " ") & " - " & deskripsi.ToString.PadRight(25, " ")
        //     str &= vbNewLine

        //     str &= "   " & alamat.PadRight(15, " ") & "- " & qty
        //     str &= vbNewLine
        // Next
        // str &= "========================================" & vbCrLf
        // str &= vbNewLine
        // str &= Chr(&H1D) & "V" & Chr(66) & Chr(0)
    }

    private function rptPenyusutanHarianPerishable($tanggal_trans){
        //* report -> rptPenyusutanHarianKlikIGR

        $query = '';
        $query .= "SELECT ROW_NUMBER() OVER() AS no, ";
        $query .= "t.* FROM ( ";
        $query .= " SELECT pluigr, ";
        $query .= "       prd_deskripsipanjang deskripsi, ";
        $query .= "       SUM(qty_order) qty_order, ";
        $query .= "       SUM(qty_barcode) qty_real, ";
        $query .= "       SUM(penyusutan) penyusutan ";
        $query .= " FROM penyusutan_klikigr ";
        $query .= " JOIN tbmaster_prodmast ";
        $query .= " ON prd_prdcd = pluigr ";
        $query .= " WHERE DATE_TRUNC('DAY', tgl_trans) = '" . Carbon::parse($tanggal_trans)->format('Y-m-d H:i:s') . "'";
        $query .= " GROUP BY pluigr, prd_deskripsipanjang ";
        $query .= " ORDER BY pluigr ASC ";
        $query .= ") t";

        $data['data'] = DB::select($query);

        $data['perusahaan'] = DB::table('tbmaster_perusahaan')->select("prs_namacabang")->first();

        if(!count($data['data'])){
            throw new HttpResponseException(ApiFormatter::error(400, "Tidak ditemukan item Penyusutan Harian"));
        }

        return $data;
    }

    private function rptJalurKertasPerishable($tanggal_trans, $no_trans, $nopb, $kodemember){
        $data['nopb'] = $nopb;
        $data['kodemember'] = $kodemember;
        $data['tanggaltrans'] = $tanggal_trans;
        $data['notrans'] = $no_trans;

        $query = '';
        $query .= "SELECT row_number() OVER () as no, ";
        $query .= "       plu, ";
        $query .= "       deskripsi, ";
        $query .= "       konversi_pcs, ";
        $query .= "       konversi_gram, ";
        $query .= "       qty_pcs, ";
        $query .= "       (qty_pcs * konversi) qty_gram, ";
        $query .= "       (qty_pcs * konversi) - (konversi - toleransi_awal) toleransi_awal, ";
        $query .= "       (qty_pcs * konversi) + (toleransi_akhir - konversi) toleransi_akhir ";
        $query .= "FROM ( ";
        $query .= "  SELECT obi_prdcd plu, ";
        $query .= "         prd_deskripsipanjang deskripsi, ";
        $query .= "         ROUND(obi_qtyorder / COALESCE((CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END),1000)) qty_pcs, ";
        $query .= "         ROUND(sat_gram / COALESCE(sat_pcs,1)) konversi, ";
        $query .= "         sat_pcs konversi_pcs, ";
        $query .= "         sat_gram konversi_gram, ";
        $query .= "         toleransi_awal, ";
        $query .= "         toleransi_akhir ";
        $query .= "  FROM tbtr_obi_d ";
        $query .= "  JOIN tbmaster_prodmast ";
        $query .= "  ON prd_prdcd = obi_prdcd ";
        $query .= "  JOIN konversi_item_klikigr ";
        $query .= "  ON substr(pluigr,1,6) || '0' = substr(obi_prdcd, 1, 6) || '0' ";
        $query .= "  WHERE DATE_TRUNC('DAY',obi_tgltrans) = TO_DATE('" . $tanggal_trans . "','YYYY-MM-DD') ";
        $query .= "  AND obi_notrans = '" . $no_trans . "' ";
        $query .= "  AND obi_recid IS NULL ";
        $query .= "  AND COALESCE(obi_itemkg, 0) = 1 ";
        $query .= ") AS subquery_alias";
        $query .= " ORDER BY deskripsi ASC ";

        
        $data['data'] = DB::select($query);

        
        if(!count($data['data'])){
            return [
                "status" => false,
                "message" => "Tidak ditemukan item picking jalur kertas",
                "data" => null
            ];
        }

        return [
            "status" => true,
            "message" => "Success",
            "data" => $data
        ];
        return ApiFormatter::success(200, 'Success.', $data);
        //* report -> rptJalurKertasKlikIGR
    }

    private function ReaktivasiPB($no_trans, $nopb, $tanggal_trans){

        DB::beginTransaction();
        try{

            $query = '';
            $query .= "INSERT INTO log_reaktifpb_kigr ( ";
            $query .= "  lr_kodeigr, lr_nopb, lr_tglpb, ";
            $query .= "  lr_notrans, lr_tgltrans, ";
            $query .= "  lr_reaktifby, lr_reaktifdt, ";
            $query .= "  lr_expireddt_lama, ";
            $query .= "  lr_status_lama ";
            $query .= " )";
            $query .= " SELECT obi_kodeigr, obi_nopb, obi_tglpb, ";
            $query .= "        obi_notrans, obi_tgltrans,  ";
            $query .= "        '" . session('KODECABANG') . "', NOW(),  ";
            $query .= "        obi_expireddt, obi_recid ";
            $query .= "  FROM tbtr_obi_h ";
            $query .= "  WHERE obi_notrans = '" . $no_trans . "' AND obi_nopb = '" . $nopb . "' ";
            DB::insert($query);

            $query = '';
            $query .= "UPDATE tbtr_obi_h ";
            $query .= " SET obi_recid = CASE WHEN length(obi_recid) = 1 THEN null ";
            $query .= "                      ELSE substr(obi_recid,-1,1) END, ";
            $query .= "     obi_reaktifby = '" . session('KODECABANG') . "', ";
            $query .= "     obi_reaktifdt = CURRENT_DATE, ";
            $query .= "     obi_expireddt = CURRENT_DATE + 3, ";
            $query .= "     obi_alasanbtl = null ";
            $query .= " WHERE obi_notrans = '" . $no_trans . "' ";
            $query .= " AND obi_nopb =  '" . $nopb . "' ";
            DB::insert($query);

            $query = '';
            $query .= "UPDATE tbtr_obi_d SET obi_recid = null ";
            $query .= "WHERE obi_notrans = '" . $no_trans . "' ";
            $query .= "AND obi_tgltrans = (SELECT obi_tgltrans FROM tbtr_obi_h WHERE obi_nopb = '" . $nopb . "' AND obi_notrans = '" . $no_trans . "') ";
            DB::insert($query);

            $query = '';
            $query .= "UPDATE log_reaktifpb_kigr  ";
            $query .= "   SET (lr_expireddt_baru, lr_status_baru) = (SELECT obi_expireddt, obi_recid FROM tbtr_obi_h WHERE obi_notrans = '" . $no_trans . "' AND obi_nopb = '" . $nopb . "') ";
            $query .= " WHERE lr_notrans = '" . $no_trans . "' ";
            $query .= "   AND lr_nopb =  '" . $nopb . "' ";
            DB::insert($query);

            //! REAKTIVASI PB SESUDAH DRAFT STRUK (RECID 4) - TAMBAH INTRANSIT
            $query = '';
            $query .= "SELECT obi_nopb ";
            $query .= "FROM tbtr_obi_h ";
            $query .= "WHERE COALESCE(obi_recid,'0') LIKE '%4' ";
            $query .= " AND obi_notrans = '" . $no_trans . "' ";
            $query .= " AND obi_nopb = '" . $nopb . "' ";
            $query .= " AND UPPER(obi_attribute2) IN ('KLIKIGR','CORP','SPI') ";
            $cek = DB::select($query);

            if(count($cek)){
                $this->updateIntransit(true, $no_trans, $tanggal_trans);
            }

            // DB::commit();

            return true;

        }catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            return ApiFormatter::error(400, $message);
        }
    }

    private function updateIntransit($flagTambah, $noTrans, $tglTrans){
        $query = '';
        $query .= "MERGE INTO ( ";
        $query .= "  SELECT * FROM tbmaster_stock ";
        $query .= "  WHERE st_lokasi = '01' ";
        $query .= "  AND EXISTS ( ";
        $query .= "	   SELECT 1 ";
        $query .= "	   FROM tbtr_obi_d ";
        $query .= "	   WHERE SUBSTR(obi_prdcd, 1, 6) || '0' = st_prdcd ";
        $query .= "	   AND obi_notrans = '" . $noTrans . "' ";
        $query .= "	   AND DATE_TRUNC('DAY',obi_tgltrans) = TO_DATE('" . $tglTrans . "','DD-MM-YYYY') ";
        $query .= ") ";
        $query .= ") t ";
        $query .= "USING ( ";
        $query .= "  SELECT SUBSTR(obi_prdcd, 1, 6) || '0' obi_prdcd, ";
        $query .= "		    SUM (COALESCE(obi_qtyrealisasi,0)) obi_qtyrealisasi ";
        $query .= "  FROM tbtr_obi_d  ";
        $query .= "  WHERE obi_notrans = '" . $noTrans . "'  ";
        $query .= "  AND DATE_TRUNC('DAY',obi_tgltrans) = TO_DATE('" . $tglTrans . "','DD-MM-YYYY')  ";
        $query .= "  AND obi_recid is null  ";
        $query .= "  GROUP BY SUBSTR(obi_prdcd, 1, 6) || '0' ";
        $query .= ") s ";
        $query .= "ON ( ";
        $query .= "  t.st_prdcd = s.obi_prdcd ";
        $query .= ") ";
        $query .= "WHEN MATCHED THEN ";
        if($flagTambah){
            $query .= "  UPDATE SET t.st_intransit = t.st_intransit - s.obi_qtyrealisasi, ";
            $query .= "             t.st_saldoakhir = COALESCE(t.st_saldoawal, 0)  ";
            $query .= "		                          + COALESCE(t.st_trfin, 0)  ";
            $query .= "						          - COALESCE(t.st_trfout, 0)  ";
            $query .= "						          - COALESCE(t.st_sales, 0)  ";
            $query .= "						          + COALESCE(t.st_retur, 0)  ";
            $query .= "						          + COALESCE(t.st_adj, 0)  ";
            $query .= "						          + COALESCE(t.st_selisih_so, 0)  ";
            $query .= "						          + COALESCE(t.st_selisih_soic, 0)  ";
            $query .= "						          + COALESCE(t.st_intransit, 0) ";
            $query .= "						          - s.obi_qtyrealisasi, ";

        }else{
            $query .= "  UPDATE SET t.st_intransit = t.st_intransit + s.obi_qtyrealisasi, ";
            $query .= "             t.st_saldoakhir = COALESCE(t.st_saldoawal, 0)  ";
            $query .= "		                          + COALESCE(t.st_trfin, 0)  ";
            $query .= "						          - COALESCE(t.st_trfout, 0)  ";
            $query .= "						          - COALESCE(t.st_sales, 0)  ";
            $query .= "						          + COALESCE(t.st_retur, 0)  ";
            $query .= "						          + COALESCE(t.st_adj, 0)  ";
            $query .= "						          + COALESCE(t.st_selisih_so, 0)  ";
            $query .= "						          + COALESCE(t.st_selisih_soic, 0)  ";
            $query .= "						          + COALESCE(t.st_intransit, 0) ";
            $query .= "						          + s.obi_qtyrealisasi, ";
        }
        $query .= "			 t.st_modify_by = '" . session('KODECABANG') . "', ";
        $query .= "			 t.st_modify_dt = NOW() ";
        DB::insert($query);

        $query .= "UPDATE tbtr_obi_d ";
        if($flagTambah){
            $query .= "SET obi_qtyintransit = obi_qtyrealisasi ";
        }else{
            $query .= "SET obi_qtyintransit = 0 ";
        }
        $query .= "WHERE obi_notrans = '" . $noTrans . "' ";
        $query .= "AND DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')." ";
        $query .= "AND obi_recid is null ";
        DB::update($query);

        return true;
    }

    private function checkPPN($flagbkp) {
        $resp = "";
        $dtPPN = DB::select("SELECT DISTINCT
                                kfp_statuspajak AS STATUS,
                                kfp_kodefp AS KODEFP,
                                CONCAT(kfp_kodefp, kfp_kodereferensi_ef) AS REF,
                                CONCAT(kfp_kodefp, kfp_kodereferensi_ef) AS REF2
                            FROM tbmaster_kodefp
                            WHERE CONCAT(kfp_flagbkp1, kfp_flagbkp2) = ?", [$flagbkp]);

        if (empty($dtPPN)) {
            $resp = "Data PPN tidak ditemukan (TBMASTER_KODEFP)";
        } elseif (count($dtPPN) > 1) {
            $resp = "Data PPN lebih dari 1 (TBMASTER_KODEFP)";
        } else {
            $resp = "OK";
        }

        return ["response" => $resp, "dtPPN" => $dtPPN];
    }

    private function getNominalVoucher($no_trans, $kode_member){
        $nominal = 0.0;
        try {
            $query = "
                SELECT SUM(vki_nominal)
                FROM tbtr_obi_voucher, tbtr_obi_h
                WHERE vki_notrans = obi_notrans
                AND vki_nopb = obi_nopb
                AND DATE_TRUNC('DAY', vki_tgltrans) = DATE_TRUNC('DAY', obi_tgltrans)
                AND vki_kdmember = obi_kdmember
                AND vki_notrans = '$no_trans'
                AND vki_kdmember = '$kode_member'
            ";

            $nominal = DB::select($query)[0]->sum;
            return $nominal;
        } catch(\Exception $e){
            return 0;
        }
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
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        //! CEK URL KONVERSI_KLIKIGR DI TBMASTER_WEBSERVICE
        $cek = DB::table('tbmaster_webservice')
            ->whereRaw("WHERE upper(ws_nama) = 'KONVERSI_KLIKIGR'")
            ->first();
        if(empty($cek) || $cek->ws_url == null){
            $message = 'Webservice Konversi Item Klikigr belum terdaftar';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
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
            DB::statement("ALTER TABLE PAYMENT_KLIKIGR ADD COD_NONPAID VARCHAR(2)");
        }

        //! TBTR_DSP_SPI
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DSP_SPI'")
            ->whereRaw("upper(column_name) = 'DSP_TOTALDSP'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DSP_SPI ADD DSP_TOTALDSP NUMERIC");
        }

        //! TBTR_DELIVERY_SPI
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'TBTR_DELIVERY_SPI'")
            ->whereRaw("upper(column_name) = 'DEL_NILAICOD'")
            ->count();

        if($count == 0){
            DB::select("ALTER TABLE TBTR_DELIVERY_SPI ADD DEL_NILAICOD NUMERIC");
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
