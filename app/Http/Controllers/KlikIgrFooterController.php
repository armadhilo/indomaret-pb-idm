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
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;

class KlikIgrFooterController extends KlikIgrController
{

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
            $query .= "FROM tbtr_obi_d d ";
            $query .= "JOIN tbhistory_obi_batal b on d.obi_prdcd = b.obi_prdcd AND d.obi_notrans = b.obi_notrans ";
            $query .= "JOIN tbmaster_prodmast p on b.obi_prdcd = p.prd_prdcd ";
            $query .= "WHERE d.obi_notrans = '" . $request->no_trans . "' ";
            $query .= "AND d.obi_tgltrans = (SELECT obi_tgltrans FROM tbtr_obi_h WHERE obi_nopb = '" . $request->nopb . "') ";
            $query .= "AND d.obi_recid = '1' ";
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
        $query .= "FROM tbtr_obi_d AS d ";
        $query .= "JOIN tbhistory_obi_batal AS b on d.obi_prdcd = b.obi_prdcd AND d.obi_notrans = b.obi_notrans AND d.obi_tgltrans = b.obi_tgltrans ";
        $query .= "JOIN tbmaster_prodmast AS p on b.obi_prdcd = p.prd_prdcd ";
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
            $query .= "AND DATE_TRUNC('DAY', obi_tgltrans) = TO_DATE('" . $request->tanggal_trans . "','YYYY-MM-DD') ";
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

        if(!isset($request->datatables) || !count($request->datatables)){
            return ApiFormatter::error(400, 'Data pada datatables kosong');
        }

        try {
            foreach ($request->datatables as $item){
                $prdcd = $item["plu"];
                $qtyBaru = $item["qtyinput"] * (session('flagSPI') ? 1 : $item["frac"]);

                $result = $this->updateQtyHitungUlang($request->tanggal_trans, $selectedRow["no_trans"], $prdcd, $qtyBaru);
                if(!$result){
                    return ApiFormatter::error(400, 'Gagal Update Qty PLU ' . $prdcd);
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
                                'order' => $item->orderr,
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
            return $e;
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
            throw new HttpResponseException(ApiFormatter::error(400, 'Error executing query Nilai PPN'));
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
        $sql .= "WHERE hdr.obi_tglpb = TO_DATE('" . date('Y-m-d', strtotime($selectedRow["tgl_pb"])) . "','YYYY-MM-DD')  ";
        $sql .= "AND hdr.obi_nopb = '" . $selectedRow["no_pb"] . "'  ";
        $sql .= "AND hdr.obi_kdmember = '" . $selectedRow["kode_member"] . "'  ";
        $sql .= "AND hdr.obi_notrans = '" . $selectedRow["no_trans"] . "' ";
        $sql .= "AND COALESCE(obi_qty_hitungulang,0) > 0 ";
        $sql .= "AND dtl.obi_recid IS NULL ";
        $sql .= "ORDER BY pobi_nocontainer ASC, dtl.obi_scan_dt DESC, dtl.obi_prdcd ASC ";

        $dt = DB::select($sql);

        if(count($dt) <= 0){
            throw new HttpResponseException(ApiFormatter::error(400, 'Data Hitung Ulang Tidak Ditemukan'));
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
                throw new HttpResponseException(ApiFormatter::error(400, 'Gagal Buat Struk'));
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

        if (count($dtPayment) > 0 AND strtoupper($tipeBayar) != "COD" AND strtoupper($tipeBayar) != "TOP") {
            $totalBayar = 0;
            $str .= str_pad("PEMBAYARAN", 28, ".") . ":" . PHP_EOL;
            foreach ($dtPayment as $row) {
                $str .= str_pad(" -" . strtoupper($row->tipe_bayar), 28, ".") . ":" . str_pad(number_format($row->total, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                $totalBayar += $row->total;

                if (!strpos(strtoupper($row->tipe_bayar), "POIN")) {
                    $pembayaranNonPoin = $row->total;
                }
            }
        }elseif(strtoupper($tipeBayar) == "COD"){
            $totalBayar = 0;

            $str .= str_pad("PEMBAYARAN", 28, ".") . ":" . PHP_EOL;
            for ($i = 0; $i < $dtPayment; $i++) {
                if ($dtPayment > 1) {
                    if ($i == 0) {
                        if (!strpos(strtoupper($dtPayment[0]->tipe_bayar), "POIN") !== false) {
                            if (!strpos(strtoupper($dtPayment[1]->tipe_bayar), "POIN") !== false) {
                                $str .= str_pad(" -" . strtoupper($dtPayment[0]->tipe_bayar), 28, ".") . ":" . str_pad(number_format($dtPayment[0]->total, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                                $totalBayar += $dtPayment[0]->total;
                            } else {
                                if ($totalBelanja > $dtPayment[1]->total) {
                                    $str .= str_pad(" -" . strtoupper($dtPayment[0]->tipe_bayar), 28, ".") . ":" . str_pad(number_format($totalBelanja - $dtPayment[1]->total, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                                    $totalBayar += $dtPayment[0]->total;
                                }
                            }
                        } else {
                            $str .= str_pad(" -" . strtoupper($dtPayment[0]->tipe_bayar), 28, ".") . ":" . str_pad(number_format($dtPayment[0]->total, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                            $totalBayar += $dtPayment[0]->total;
                        }
                    } else {
                        if (!strpos(strtoupper($dtPayment[0]->tipe_bayar), "POIN") !== false) {
                            $str .= str_pad(" -" . strtoupper($dtPayment[$i]->tipe_bayar), 28, ".") . ":" . str_pad(number_format($dtPayment[$i]->total, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                            $totalBayar += $dtPayment[$i]->total;
                        } else {
                            $str .= str_pad(" -" . strtoupper($dtPayment[0]->tipe_bayar), 28, ".") . ":" . str_pad(number_format($totalBelanja - $dtPayment[0]->total, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                            $totalBayar += $dtPayment[$i]->total;
                        }
                    }
                } else {
                    $str .= str_pad(" -" . strtoupper($dtPayment[0]->tipe_bayar), 28, ".") . ":" . str_pad(number_format($dtPayment[0]->total, 0), 11, " ", STR_PAD_LEFT) . PHP_EOL;
                    $totalBayar += $dtPayment[0]->total;
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
        $query .= "WHERE obi_tgltrans = TO_DATE('" . date('Y-m-d', strtotime($tgltrans)) . "','YYYY-MM-DD') ";
        $query .= "AND obi_notrans = '" . $notrans . "' ";
        $query .= "AND obi_prdcd = '" . $prdcd . "' ";

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
        $query .= "ORDER BY prd_deskripsipanjang ";

        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);

    }

    public function actionF12(Request $request){

        DB::beginTransaction();
        try{

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

                    DB::commit();

                    return ApiFormatter::success(200, 'DSP Berhasil Dibatalkan!');
                }
            }elseif($request->status == $request->status_siap_packing){
                //* nanti ada login manager dengan form -> frmPassword
                if(json_decode($request->pass_password_manager) === false){
                    return ApiFormatter::success(201, "Kembali ke Proses Picking untuk Transaksi No." . $request->no_trans . "?", "isAdmin");
                }

                $this->ulangPicking($request->no_trans, $request->nopb);

                DB::commit();

                return ApiFormatter::success(200, 'Proses Picking Transaksi ' . $request->no_trans);

            }else{
                return ApiFormatter::error(400, 'Bukan data yang bisa dibatalkan!');
            }


        } catch (HttpResponseException $e) {
            // Handle the custom response exception
            throw new HttpResponseException($e->getResponse());

        }catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
            return ApiFormatter::error(400, $message);
        }
    }

    private function alasanbatalKlik($no_trans, $tanggal_trans, $nopb, $alasan){
        $apiName = '';
        $apiKey = '';
        $dt = DB::select("SELECT ws_url, ws_aktif FROM tbmaster_webservice WHERE ws_nama = 'WS_KLIK'");
        if(count($dt) < 1){
            throw new HttpResponseException(ApiFormatter::error(400, "API Tidak Ditemukan"));
        }

        if($dt[0]->ws_url !== ''){
            $urlKlik = $dt[0]->ws_url;
            $flagAktif = $dt[0]->ws_aktif;
        } else {
            throw new HttpResponseException(ApiFormatter::error(400, "API KLIK Kosong"));
        }

        if($flagAktif == "0"){
            return "SUCCESS";
        }

        $dt = DB::table('tbmaster_credential')->selectRaw("cre_name as api_name, cre_key as api_key")->first();
        if(count($dt) > 0){
            $apiName = $dt[0]->api_name;
            $apiKey = $dt[0]->api_key;
        }

        $splitTrans = explode('/', $nopb);
        $trxid = $splitTrans[0];

        $postData = [
            'trx_id' => $trxid,
            'desc' => $alasan
        ];

        // Convert postData to JSON
        try {
            $strPostData = json_encode($postData);
        } catch (\Exception $ex) {
            throw new HttpResponseException(ApiFormatter::error(400, "Failed to create JSON Request"));
        }

        $urlKlik .= "/getcancelinfo";

        $strResponse = $this->ConToWebServiceNew($urlKlik, $apiName, $apiKey, $strPostData);

        $query = "";
        $query .= "INSERT INTO log_alasan_batal ( ";
        $query .= "  notrans, ";
        $query .= "  tgltrans, ";
        $query .= "  nopb, ";
        $query .= "  alasan_batal, ";
        $query .= "  url, ";
        $query .= "  response, ";
        $query .= "  create_by, ";
        $query .= "  create_dt ";
        $query .= ") VALUES ( ";
        $query .= "  '" . $no_trans . "', ";
        $query .= "  TO_DATE('" . $tanggal_trans . "','YYYY-MM-DD'), ";
        $query .= "  '" . $nopb . "', ";
        $query .= "  '" . $alasan . "', ";
        $query .= "  '" . $urlKlik . "', ";
        $query .= "  '" . $strResponse . "', ";
        $query .= "  '" . session("USERID") . "', ";
        $query .= "  NOW() ";
        $query .= ") ";

        DB::insert($query);

        if (strtoupper($strResponse) === "CONNECTION FAILED") {
            return "CONNECTION FAILED";
        }

        try {
            $cResponse = json_decode($strResponse);
            if ($cResponse->response_code === "200") {
                return "SUCCESS";
            } else {
                return $cResponse->response_message;
            }
        } catch (Exception $ex) {
            return "Gagal Read JSON Response";
        }




    }

    public function actionDelete(Request $request){
        $strHasil = "";
        $noPb = "";
        DB::beginTransaction();
        try {
            $check = DB::table('tbtr_obi_h as h')
                ->select('obi_nopb', DB::raw("SUBSTRING(COALESCE(obi_recid,'0'), -1, 1) AS obi_recid"))
                ->whereRaw("SUBSTRING(COALESCE(obi_recid, '0'), -1, 1) IN ('4','5')")
                ->where('h.obi_notrans', $request->no_trans)
                ->where('h.obi_nopb', $request->nopb)
                ->whereDate('h.obi_tgltrans', '=', date('Y-m-d', strtotime($request->tanggal_trans)))
                ->whereIn(DB::raw('UPPER(obi_attribute2)'), ['KLIKIGR', 'CORP', 'SPI'])
                ->get();

            if(count($check) > 0){
                if($check[0]->obi_recid == "5"){
                    $tempHasil = "";
                    if(session("flagSPI")){
                        $tempHasil = "SUCCESS";
                        //hasil = cancelPickup_SPI(row.Cells(1).Value.ToString, row.Cells(2).Value.ToString, row.Cells(0).Value.ToString)
                    } else {
                        $tempHasil = $this->cancelPickup_KLIK($request->no_trans, $request->tanggal_trans, $request->nopb);
                    }
                    if ($tempHasil !== "SUCCESS") {
                        return ApiFormatter::error(400, "PB " . $request->nopb . " Gagal Cancel Pickup IPP");
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
                $query .= "          AND DATE_TRUNC('DAY', obi_tgltrans) = TO_DATE('" . $request->tanggal_trans . "', 'YYYY-MM-DD') ";
                $query .= "          AND obi_recid IS NULL ";
                $query .= "        GROUP BY SUBSTR(obi_prdcd, 1, 6) || '0' ";
                $query .= ") s ";
                $query .= "WHERE t.st_prdcd = s.obi_prdcd ";
                $query .= "AND t.st_lokasi = '01'";

                DB::update($query);

                $query = "UPDATE tbtr_obi_d ";
                $query .= "SET obi_qtyintransit = 0 ";
                $query .= "WHERE obi_notrans = '" . $request->no_trans . "' ";
                $query .= "AND DATE_TRUNC('DAY', obi_tgltrans) = TO_DATE('" . $request->tanggal_trans . "', 'YYYY-MM-DD') ";
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
            $query .= "  TO_DATE('" . $request->tanggal_trans . "','YYYY-MM-DD'), ";
            $query .= "  '" . $request->nopb . "', ";
            $query .= "  '" . $notrx . "', ";
            $query .= "  'B', ";
            $query .= "  0, ";
            $query .= "  '" . session("userid") . "', ";
            $query .= "  NOW() ";
            $query .= ") ";

            DB::insert($query);

            if(session("flagSPI")){
                $tempHasil = "SUCCESS";
                //hasil = cancelPickup_SPI(row.Cells(1).Value.ToString, row.Cells(2).Value.ToString, row.Cells(0).Value.ToString)
            } else {
                $tempHasil = $this->alasanbatalKlik($request->no_trans, $request->tanggal_trans, $request->nopb, $request->alasanValue);
            }

            if ($tempHasil !== "SUCCESS") {
                return ApiFormatter::error(400, "PB " . $request->nopb . " Gagal Update Alasan Batal.");
            }

            $sql = "UPDATE TBTR_OBI_H SET obi_recid = CONCAT('B', COALESCE(obi_recid, '')), obi_alasanbtl = '" . $request->alasanValue . "' ";
            $sql .= " WHERE obi_notrans = '" . $request->no_trans . "'";
            $sql .= " AND obi_nopb = '" . $request->nopb . "'";
            $sql .= " AND DATE_TRUNC('DAY', obi_tgltrans) = TO_DATE('" . $request->tanggal_trans . "', 'YYYY-MM-DD')";
            $hasil = DB::update($sql);

            if ($hasil) {
                $strHasil .= "1";
            } else {
                $strHasil .= "0";
                $noPb .= "- " . $request->nopb . " ,";
            }


            $this->logUpdateStatus($request->no_trans, $request->tanggal_trans, $request->nopb, "B", "9");

            if (strpos($strHasil, "0") !== false) {
                return ApiFormatter::error(400, "Ada PB Yang Gagal Dibatalkan. \n" . $noPb);
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
}
