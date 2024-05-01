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

    //* function listObi_H
    public function datatables($dtTrans, $statusSiapPicking, $statusSiapPacking){
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
        $query .= "         CASE obi_shippingservice WHEN 'S' THEN 'Sameday' WHEN 'N' THEN 'Nextday' ELSE '' END service, ";
        $query .= "         to_char(obi_mindeliverytime,'DD-MM-YYYY HH24:MI:SS') 'TGL & JAM PB', ";
        $query .= "         to_char(obi_maxdeliverytime,'DD-MM-YYYY HH24:MI:SS') 'MAX SERAH TERIMA', ";
        $query .= "         CASE OBI_FLAGSENDHH  ";
        $query .= "             WHEN '1' THEN 'SEND JALUR DPD' ";
        $query .= "             WHEN '2' THEN 'SEND JALUR HH' ";
        $query .= "         ELSE '-' ";
        $query .= "         END 'STATUS SEND JALUR', ";
        $query .= "         obi_shippingservice, obi_maxdeliverytime , obi_mindeliverytime ";
        $query .= "    FROM tbtr_obi_h  ";
        $query .= "    LEFT JOIN tbmaster_customer ON obi_kdmember = cus_kodemember ";
        $query .= "    LEFT JOIN tbtr_transaksi_va ON SUBSTR(obi_nopb, 0, 6)= tva_trxid AND obi_tglpb = tva_tglpb ";
        $query .= "   WHERE DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
        if(session('flagSPI') == true){
            $query .= "     AND UPPER(obi_nopb) LIKE '%SPI%' ";
        }else{
            $query .= "     AND UPPER(obi_nopb) NOT LIKE '%SPI%' ";
        }
        $query .= ") p ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    //* modal muncul datatable
    //* sekalian download csv -> kalo gabisa di modal tambahin button aja
    public function actionF1($dgv_notrans, $dgv_nopb){
        $query = '';
        $query .= "SELECT obi_prdcd PLU, ";
        $query .= "       COALESCE(prd_deskripsipanjang,'TIDAK ADA DI PRODMAST') Barang, ";
        $query .= "       obi_qtyorder / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) Jumlah, ";
        $query .= "       TO_CHAR(COALESCE(obi_hargaweb, ROUND((obi_hargasatuan + obi_ppn) * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), 0)), '999,999,999') Harga, ";
        $query .= "       TO_CHAR(ROUND(obi_diskon * obi_qtyorder,0), '999,999,999') Diskon, ";
        $query .= "       TO_CHAR((COALESCE(obi_hargaweb, ROUND((obi_hargasatuan + obi_ppn) * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), 0)) * (obi_qtyorder / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END))) - ROUND(obi_diskon * obi_qtyorder,0), '999,999,999') SubTotal, ";
        $query .= "       prd_kodetag tag ";
        $query .= "  FROM tbtr_obi_h h ";
        $query .= "  JOIN tbtr_obi_d d ";
        $query .= "    ON h.obi_notrans = d.obi_notrans ";
        $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "  LEFT JOIN tbmaster_prodmast ";
        $query .= "   ON obi_prdcd = prd_prdcd ";
        $query .= " WHERE h.obi_notrans = '" . $dgv_notrans . "' ";
        $query .= "   AND h.obi_nopb = '" . $dgv_nopb . "' ";
        $query .= " ORDER BY 2,1 ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function actionF1DownloadCSV(){
        $this->WriteToCSV("LIST_PB_OBI_" & tmpPB(0), _dtListPB, txtpath.Text);
    }

    public function actionF2($dgv_memberigr,$dgv_notrans,$dgv_nopb){
        $query = '';
        $query .= "SELECT kode_kupon as 'KODE PROMO', ";
        $query .= "       'KUPON' as TIPE, ";
        $query .= "       COALESCE(nama_kupon,kode_kupon) as PROMO, ";
        $query .= "       TO_CHAR(nilai_kupon,'999,999,999') as POTONGAN ";
        $query .= "FROM kupon_klikigr ";
        $query .= "WHERE kode_member = '" . $dgv_memberigr . "' ";
        $query .= "  AND no_trans = '" . $dgv_notrans . "' ";
        $query .= "  AND no_pb = '" . $dgv_nopb . "' ";
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
        $query .= "WHERE kode_member = '" . $dgv_memberigr . "' ";
        $query .= "  AND no_trans = '" . $dgv_notrans . "' ";
        $query .= "  AND no_pb = '" . $dgv_nopb . "' ";
        $query .= "ORDER BY 2 ASC, 1 ASC ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function actionF3($dgv_notrans, $dgv_nopb){
        $query = '';
        $query .= "SELECT d.obi_prdcd PLU, ";
        $query .= "       prd_deskripsipanjang DESKRIPSI, ";
        $query .= "       (d.obi_qtyorder / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END)) as QTY_ORDER, ";
        $query .= "       ROUND((d.obi_qtyrealisasi / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END)),2) AS QTY_PICKING, ";
        $query .= "       CASE WHEN d.obi_pick_dt IS NOT NULL THEN CASE WHEN d.obi_close_dt IS NOT NULL THEN 'Sudah Close Picking' ELSE 'Sudah Picking' END ELSE 'Belum Picking' END AS STATUS_PICKING, ";
        $query .= "       COALESCE(d.obi_grouppicker, '-') as 'GROUP', ";
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
        $query .= " WHERE d.obi_notrans = '" . $dgv_notrans . "' ";
        $query .= "   AND h.obi_nopb = '" . $dgv_nopb . "' ";
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

    public function actionF4($dgv_notrans, $dgv_nopb, $dgv_tglpb){
        //* message -> Edit PB/Validasi Rak untuk Item Batal?
        //* OPEN FORM -> frmEditPB
    }

    public function actionF5($dgv_status, $dgv_flagBayar, $dgv_notrans, $dgv_nopb, $dtTrans){

        if($dgv_status == 'Transaksi Batal' AND $dgv_flagBayar != 'Y'){
            //* message -> Mengaktifkan Kembali Transaksi No." & dgv_notrans & " yang sudah batal?

            //* nanti ada login manager dengan form -> frmPassword

            $this->ReaktivasiPB($dgv_notrans, $dgv_nopb, $dtTrans);

        }else{
            return ApiFormatter::error(400, 'Bukan data yang bisa diaktifkan kembali!');
        }
    }

    public function actionF6($dgv_status){
        if($dgv_status == 'Siap Struk'){
            //* Validasi Struk untuk Transaksi No." & dgv_notrans & "?

            //* nanti ada login manager dengan form -> frmPassword

            //* open form -> frmValidasiStrukKlik

        }else{
            return ApiFormatter::error(400, 'Bukan data yang bisa validasi struk!');
        }
    }

    public function actionF7($dgv_status, $statusSiapPacking, $dtTrans, $dgv_notrans, $dgv_nopb, $dgv_memberigr){
        if($dgv_status == 'Siap Picking' OR $dgv_status == $statusSiapPacking){
            $this->rptJalurKertasPerishable($dtTrans, $dgv_notrans, $dgv_nopb, $dgv_memberigr);
        }else{
            return ApiFormatter::error(400, 'Bukan data yang dipicking/dipacking!');
        }
    }

    public function actionF8($dtTrans){
        //* Cetak Laporan Penyusutan Harian?

        $this->rptPenyusutanHarianPerishable($dtTrans);
    }

    public function actionF9($dgv_status, $statusSiapPacking, $dtTrans, $dgv_notrans, $dgv_nopb, $dgv_memberigr){
        if(session('flagSPI') == true AND session('flagIGR') == false){
            if($dgv_status == 'Siap Picking' OR $dgv_status == $statusSiapPacking){
                $this->rptPickingList999($dtTrans, $dgv_notrans, $dgv_nopb, $dgv_memberigr, False);
            }else{
                return ApiFormatter::error(400, 'Bukan PB yang sedang dipicking / dipacking');
            }
        }else{
            return ApiFormatter::error(400, 'Khusus cabang SPI Picking DPD!');
        }
    }

    public function actionF10($dgv_status, $dgv_tipebayar, $dtTrans, $dgv_notrans, $dgv_nopb, $dgv_tglpb, $dgv_memberigr, $dgv_tipekredit){

        if(session('flagSPI') == true){
            if(($dgv_status == 'Siap Struk' AND $dgv_tipebayar == 'COD') OR ($dgv_status == 'Selesai Struk' AND $dgv_tipebayar != 'COD')){
                //* open FORM -> frmHitungUlangSPI
            }else{
                if (str_contains($dgv_status, 'Batal')) {
                    return ApiFormatter::error(400, 'Transaksi sudah dibatalkan!');
                }else{
                    if($dgv_tipebayar == 'COD'){
                        if($dgv_status == 'Selesai Struk'){
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
            if($dgv_status == 'Siap Struk' AND $dgv_tipebayar == 'COD'){
                //* open FORM -> frmHitungUlangSPI
            }else{
                if($dgv_tipebayar == 'COD'){
                    if($dgv_status == 'Selesai Struk'){
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

    public function actionF12($dgv_status, $dgv_tglpb, $statusSiapPacking, $dgv_notrans, $dgv_nopb, $dgv_memberigr, $dtTrans){
        if($dgv_status == 'Konfirmasi Pembayaran' OR $dgv_status == 'Siap Struk'){
            $query = '';
            $query .= "SELECT * FROM TBTR_TRANSAKSI_VA ";
            $query .= "WHERE TVA_TRXID = '" . substr($dgv_nopb, 0, 6) . "' ";
            $query .= "    AND TVA_TGLPB = ".Carbon::parse($dgv_tglpb)->format('Y-m-d H:i:s')." ";
            $query .= "    AND COALESCE(TVA_BANK, 'BANG') != 'BANG' ";
            $data = DB::select($query);

            //! Kalo transaksi COD-VA tidak bisa batal DSP
            if(!count($data)){
                //* nanti ada login manager dengan form -> frmPassword
                $this->batalDSP($dtTrans, $dgv_notrans, $dgv_nopb, $dgv_memberigr);

                return ApiFormatter::success(200, 'DSP Berhasil Dibatalkan!');
            }
        }elseif($dgv_status == $statusSiapPacking){
            //* nanti ada login manager dengan form -> frmPassword

            $this->ulangPicking($dgv_notrans, $dgv_nopb);

            return ApiFormatter::success(200, 'Proses Picking Transaksi ' . $dgv_notrans);

        }else{
            return ApiFormatter::error(400, 'Bukan data yang bisa dibatalkan!');
        }
    }

    //* btnSendJalur_Click
    public function actionSendHandHelt($dgv_notrans, $dgv_status, $statusSiapPicking, $pilihan, $dgv_nopb, $dgv_memberigr, $dtTrans, $cbPickRakToko){
        //* Send Jalur No Trans = " & dgv_notrans & " Ini?
        //! dgv_notrans tidak boleh null atau '';

        if($dgv_notrans != $statusSiapPicking){
            return ApiFormatter::error(400, 'Bukan Data Yang Siap Send Jalur!');
        }

        if(session('flagSPI') == true AND session('flagIGR') == false){
            if (str_contains(session('flagHHSPI'), 'H') AND str_contains(session('flagHHSPI'), 'D') ) {
                //? kalo susah form ini tampil diawal aja karena cuma buat dapet variable $pilihan
                //! open form -> frmOpsiPickSPI

                if($pilihan == 1){
                    $this->sendSPI($dgv_nopb, $dgv_notrans, $dgv_memberigr, $dtTrans);
                }elseif($pilihan == 2){
                    $this->sendHH($dgv_nopb, $dgv_notrans, $dgv_memberigr, $dtTrans, $cbPickRakToko);
                }else{
                    return ApiFormatter::error(400, 'Send Jalur dibatalkan!');
                }
            }elseif(str_contains(session('flagHHSPI'), 'H') AND !str_contains(session('flagHHSPI'), 'D')){
                $this->sendHH($dgv_nopb, $dgv_notrans, $dgv_memberigr, $dtTrans, $cbPickRakToko);
            }else{
                $this->sendSPI($dgv_nopb, $dgv_notrans, $dgv_memberigr, $dtTrans);
            }
        }else{
            $this->sendHH($dgv_nopb, $dgv_notrans, $dgv_memberigr, $dtTrans, $cbPickRakToko);
        }
    }

    //* btnOngkir_Click
    public function actionOngkosKirim($dgv_notrans, $dgv_status, $dgv_flagBayar, $dgv_nopb, $dgv_tglpb, $dgv_freeongkir, $dgv_jarakkirim, $dgv_memberigr){
        if(!isset($dgv_notrans)){
            return ApiFormatter::error(400, 'Pilih Data Dahulu!');
        }

        if(strtolower($dgv_status) != 'set ongkir'){
            return ApiFormatter::error(400, 'Belum Dapat Melakukan Set Ongkos Kirim!');
        }

        $this->setOngkir($dgv_flagBayar, $dgv_nopb, $dgv_tglpb, $dgv_notrans, $dgv_freeongkir, $dgv_jarakkirim, $dgv_memberigr);
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
    public function actionSales($dgv_notrans, $dgv_status, $dgv_nopb, $dgv_tipebayar, $dgv_tglpb, $dgv_kodeweb, $dgv_memberigr, $dgv_tipe_kredit, $dtTrans){
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

                $this->InsertTransaksi($dgv_kodeweb, $dgv_nopb, $dgv_memberigr,$dgv_notrans, $dgv_tglpb, $dgv_tipe_kredit, $dtTrans, $dgv_tipebayar);
            }

        }else{
            if($dgv_status == 'Selesai Struk'){
                return ApiFormatter::error(400, 'Sudah Selesai Struk!');
            }else{
                return ApiFormatter::error(400, 'Belum Siap Struk!, Barang masih dipacking');
            }
        }
    }

    private function InsertTransaksi($dgv_kodeweb, $dgv_nopb, $dgv_memberigr,$dgv_notrans, $dgv_tglpb, $dgv_tipe_kredit, $dtTrans, $dgv_tipebayar){

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

                    //! BELUM SELESAI
                    $this->updateReal($dgv_nopb, $dgv_notrans, $dgv_tglpb, $dgv_memberigr);
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
        //! INI GAPAHAM SUSAH DIBACA
        //! TANYAKAN AKSES API UNTUK BODYNYA FORMAT DATANYA SEPERTI APA

        // sb.AppendLine(" SELECT ws_url, cre_name, cre_key FROM tbmaster_webservice  ")
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

        if($dgv_flagBayar == 'Y'){
            DB::table('tbtr_obi_h')
                ->where([
                    'obi_nopb' => $dgv_nopb,
                    'obi_tglpb' => $dgv_tglpb,
                    'obi_notrans' => $dgv_notrans,
                ])
                ->update([
                    'obi_recid' => 3,
                ]);

                $message = 'Skip Ongkos Kirim Karena Pembayaran Di Web';
                throw new HttpResponseException(ApiFormatter::success(200, $message));
        }else{

            if($dgv_freeongkir != 'T'){
                if($dgv_freeongkir == 'Y'){
                    $flagFree = true;
                }else{
                    $flagFree = false;
                }

                $jarakKlik = $dgv_jarakkirim;

                if($jarakKlik > 0){
                    $jarakKirim = $jarakKlik;
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
                        $jarakKirim = $jarakKlik;
                        $flagLockJarak = true;
                    }else{
                        $jarakKirim = 0;
                        $flagLockJarak = false;
                    }
                }

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

                $query .= "UPDATE tbtr_obi_h ";
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

        }



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
        $query .= "WHERE DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($tglTrans)->format('Y-m-d H:i:s')."  ";
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
        $query .= "WHERE DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
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
        $query .= "WHERE DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
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
            $query .= "WHERE DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
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
            $query .= "WHERE DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
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
        $query .= "                    AND DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
        $query .= "                    AND obi_notrans = '" . $notrans . "' ";
        $query .= "                    AND substr(obi_prdcd,1,6) || '0' = lks_prdcd ";
        $query .= "            ) ";
        $query .= "        ) != '0' THEN '%B' ELSE '99999' END ";
        $query .= "    ) ";
        $query .= "    AND EXISTS ( ";
        $query .= "        SELECT obi_prdcd ";
        $query .= "        FROM tbtr_obi_d ";
        $query .= "        WHERE obi_recid IS NULL AND coalesce(obi_itemkg,0) = 0 ";
        $query .= "            AND DATE_TRUNC('DAY',obi_tgltrans) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
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
        $query .= "    WHERE OBI_TGLTRANS = :OBI_TGLTRANS ";
        $query .= "		   AND OBI_NOTRANS = :OBI_NOTRANS ";
        $query .= "		   AND OBI_RECID IS NULL ";
        $query .= "		   AND COALESCE(OBI_ITEMKG,0) = 0 ";
        $query .= ") WHERE LOKASI = 0 ";
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
            $query .= "    WHERE OBI_TGLTRANS = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
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
            $query .= "WHERE OBI_TGLTRANS = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
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
            $query .= "WHERE OBI_TGLTRANS = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
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
            $query .= "WHERE OBI_TGLTRANS = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
            $query .= "AND OBI_NOPB ='" . $nopb . "' ";
            $query .= "AND OBI_KDMEMBER ='" . $memberigr . "' ";
            $query .= "AND OBI_NOTRANS ='" . $notrans . "'";
            $query .= "AND OBI_RECID IS NULL ";
            DB::update($query);

            if(count($dtNonPerishable) == 0 AND count($dtPerishable) > 0){
                $query = '';
                $query .= "UPDATE tbtr_obi_d ";
                $query .= "   SET obi_close_dt = Current_date ";
                $query .= "WHERE DATE_TRUNC('DAY',OBI_TGLTRANS) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
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
            $query .= "AND DATE_TRUNC('DAY',OBI_TGLTRANS) = ".Carbon::parse($dtTrans)->format('Y-m-d H:i:s')."  ";
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
        $query .= " WHERE h.obi_tgltrans = TO_DATE('" . $tgltrans . "','DD-MM-YYYY') ";
        $query .= " AND h.obi_notrans = '" . $notrans . "' ";
        $query .= " AND h.obi_nopb = '" . $nopb . "' ";
        $query .= " AND h.obi_kdmember = '" . $kodemember . "' ";
        $query .= " AND d.obi_qtyorder > 999 ";
        $data = DB::select($query);

        if(!count($data)){
            throw new HttpResponseException(ApiFormatter::error(400, 'Tidak ada item PB yang melebihi 999'));
        }

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

    private function rptPenyusutanHarianPerishable($dtTrans){
        //* report -> rptPenyusutanHarianKlikIGR

        $query = '';
        $query .= "SELECT ROWNUM no, ";
        $query .= "t.* FROM ( ";
        $query .= " SELECT pluigr, ";
        $query .= "       prd_deskripsipanjang deskripsi, ";
        $query .= "       SUM(qty_order) qty_order, ";
        $query .= "       SUM(qty_barcode) qty_real, ";
        $query .= "       SUM(penyusutan) penyusutan ";
        $query .= " FROM penyusutan_klikigr ";
        $query .= " JOIN tbmaster_prodmast ";
        $query .= " ON prd_prdcd = pluigr ";
        $query .= " WHERE DATE_TRUNC('DAY',tgl_trans) = ". Carbon::parse($dtTrans)->format('Y-m-d H:i:s') ." ";
        $query .= " GROUP BY pluigr, prd_deskripsipanjang ";
        $query .= " ORDER BY pluigr asc ";
        $query .= ") t";
    }

    private function rptJalurKertasPerishable($tgltrans, $notrans, $nopb, $kodemember){

        $data['nopb'] = $nopb;
        $data['kodemember'] = $kodemember;

        $query = '';
        $query .= "SELECT ROWNUM no, ";
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
        $query .= "  WHERE DATE_TRUNC('DAY',obi_tgltrans) = TO_DATE('" . $tgltrans . "','dd-MM-yyyy') ";
        $query .= "  AND obi_notrans = '" . $notrans . "' ";
        $query .= "  AND obi_recid IS NULL ";
        $query .= "  AND COALESCE(obi_itemkg, 0) = 1 ";
        $query .= "  ORDER BY deskripsi ASC ";
        $query .= ") ";
        $data['data'] = DB::select($query);

        if(!count($data)){
            throw new HttpResponseException(ApiFormatter::error(400, 'Tidak ditemukan item picking jalur kertas.'));
        }

        //* report -> rptJalurKertasKlikIGR
    }

    private function ReaktivasiPB($dgv_notrans, $dgv_nopb, $dtTrans){

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
            $query .= "  WHERE obi_notrans = '" . $dgv_notrans . "' AND obi_nopb = '" . $dgv_nopb . "' ";
            DB::insert($query);

            $query = '';
            $query .= "UPDATE tbtr_obi_h ";
            $query .= " SET obi_recid = CASE WHEN length(obi_recid) = 1 THEN null ";
            $query .= "                      ELSE substr(obi_recid,-1,1) END, ";
            $query .= "     obi_reaktifby = '" . session('KODECABANG') . "', ";
            $query .= "     obi_reaktifdt = CURRENT_DATE, ";
            $query .= "     obi_expireddt = CURRENT_DATE + 3, ";
            $query .= "     obi_alasanbtl = null ";
            $query .= " WHERE obi_notrans = '" . $dgv_notrans . "' ";
            $query .= " AND obi_nopb =  '" . $dgv_nopb . "' ";
            DB::insert($query);

            $query = '';
            $query .= "UPDATE tbtr_obi_d SET obi_recid = null ";
            $query .= "WHERE obi_notrans = '" . $dgv_notrans . "' ";
            $query .= "AND obi_tgltrans = (SELECT obi_tgltrans FROM tbtr_obi_h WHERE obi_nopb = '" . $dgv_nopb . "' AND obi_notrans = '" . $dgv_notrans . "') ";
            DB::insert($query);

            $query = '';
            $query .= "UPDATE log_reaktifpb_kigr  ";
            $query .= "   SET (lr_expireddt_baru, lr_status_baru) = (SELECT obi_expireddt, obi_recid FROM tbtr_obi_h WHERE obi_notrans = '" . $dgv_notrans . "' AND obi_nopb = '" . $dgv_nopb . "') ";
            $query .= " WHERE lr_notrans = '" . $dgv_notrans . "' ";
            $query .= "   AND lr_nopb =  '" . $dgv_nopb . "' ";
            DB::insert($query);

            //! REAKTIVASI PB SESUDAH DRAFT STRUK (RECID 4) - TAMBAH INTRANSIT
            $query = '';
            $query .= "SELECT obi_nopb ";
            $query .= "FROM tbtr_obi_h ";
            $query .= "WHERE COALESCE(obi_recid,'0') LIKE '%4' ";
            $query .= " AND obi_notrans = '" . $dgv_notrans . "' ";
            $query .= " AND obi_nopb = '" . $dgv_nopb . "' ";
            $query .= " AND UPPER(obi_attribute2) IN ('KLIKIGR','CORP','SPI') ";
            $cek = DB::select($query);

            if(count($cek)){
                $this->updateIntransit(true, $dgv_notrans, $dtTrans);
            }

            DB::commit();

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
