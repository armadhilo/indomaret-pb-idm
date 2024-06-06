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
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\select;

class FormBaRusakController extends KlikIgrController
{

    private $tglStruk;
    private $cashierID;
    private $cashierStation;
    private $noStruk;

    public function actionPrep(Request $request){
        $selectedRow = $request->selectedRow;
        $dt = DB::select("SELECT DISTINCT brk_statusba, brk_noba, TO_CHAR(brk_tglba,'DD-MM-YYYY'), brk_alasan FROM tbtr_barusak_spi WHERE brk_nopb = '" . $selectedRow["no_pb"] . "' AND brk_kodemember = '" . $selectedRow["kode_member"] . "'");
        //!! IRVAN | DUMMY DATA
        // $dt = DB::select("SELECT DISTINCT brk_statusba, brk_noba, TO_CHAR(brk_tglba,'DD-MM-YYYY'), brk_alasan FROM tbtr_barusak_spi LIMIT 15");

        if(count($dt) > 0){
            $data['statusBA'] = $dt[0]->brk_statusba;
            $data['noBA'] = $dt[0]->brk_noba;
            $data['tglBA'] = $dt[0]->to_char;
            $data['txtAlasan2'] = $dt[0]->brk_alasan;
            $data['txtAlasan3'] = $dt[0]->brk_alasan;
        } else {
            $data['statusBA'] = "NEW";
            $data['noBA'] = "";
            $data['tglBA'] = "";
            $data['txtAlasan'] = "";
        }

        return ApiFormatter::success(200, "success", $data);
    }

    public function loadItem(Request $request){
        $selectedRow = $request->selectedRow;
        $query = "";
        $query .= " SELECT ";
        $query .= "   obi_prdcd PLU, ";
        $query .= "   prd_deskripsipanjang DESKRIPSI, ";
        $query .= "   CASE WHEN prd_unit = 'KG' THEN COALESCE(sat_gram, prd_frac) ELSE COALESCE(prd_frac, 1) END FRAC, ";
        $query .= "   0 QTYBA, ";
        if (session("flagSPI")) {
            $query .= "   COALESCE(obi_qtyrealisasi, 0) QTYREAL ";
        } else {
            $query .= "   CASE WHEN prd_unit = 'KG' THEN COALESCE(obi_qtyrealisasi, 0) * COALESCE(sat_gram, prd_frac) ELSE COALESCE(obi_qtyrealisasi, 0) / COALESCE(prd_frac, 1) END QTYREAL ";
        }
        $query .= " FROM tbtr_obi_h h ";
        $query .= " JOIN tbtr_obi_d d ";
        $query .= " ON d.obi_tgltrans = h.obi_tgltrans ";
        $query .= " AND d.obi_notrans = h.obi_notrans ";
        $query .= " JOIN tbmaster_prodmast ";
        $query .= " ON prd_prdcd = obi_prdcd ";
        $query .= " LEFT JOIN konversi_item_klikigr ";
        $query .= " ON substr(pluigr, 1, 6) || '0' = substr(obi_prdcd, 1, 6) || '0' ";
        // $query .= " WHERE h.obi_nopb = '" . $selectedRow["no_pb"] . "' ";
        // $query .= " AND h.obi_notrans = '" . $selectedRow["no_trans"] . "' ";
        // $query .= " AND h.obi_kdmember = '" . $selectedRow["kode_member"] . "' ";
        // $query .= " AND DATE_TRUNC('DAY', h.obi_tgltrans) = TO_DATE('" . $selectedRow["tgltrans"] . "', 'DD-MM-YYYY') ";
        $query .= " AND d.obi_recid IS NULL ";
        $query .= " AND d.obi_qtyrealisasi > 0 ";
        $query .= " ORDER BY prd_deskripsipanjang LIMIT 15";

        $data['data'] = DB::select($query);

        return ApiFormatter::success(200, "success", $data);
    }

    public function LoadBA(Request $request){
        $selectedRow = $request->selectedRow;
        $query = "";
        $query .= " SELECT ";
        $query .= "   brk_prdcd PLU, ";
        $query .= "   prd_deskripsipanjang DESKRIPSI, ";
        $query .= "   CASE WHEN prd_unit = 'KG' THEN COALESCE(sat_gram, prd_frac) ELSE COALESCE(prd_frac, 1) END FRAC, ";
        if (session("flagSPI")) {
            $query .= "   brk_qtyba QTYBA ";
        } else {
            $query .= "   CASE WHEN prd_unit = 'KG' THEN brk_qtyba * COALESCE(sat_gram, prd_frac) ELSE brk_qtyba / COALESCE(prd_frac, 1) END QTYBA ";
        }
        $query .= " FROM tbtr_barusak_spi ";
        $query .= " JOIN tbmaster_prodmast ";
        $query .= " ON prd_prdcd = brk_prdcd ";
        $query .= " LEFT JOIN konversi_item_klikigr ";
        $query .= " ON substr(pluigr, 1, 6) || '0' = substr(brk_prdcd, 1, 6) || '0' ";
        // $query .= " WHERE brk_nopb = '" . $selectedRow["no_pb"] . "'";
        // $query .= " AND brk_kodemember = '" . $selectedRow["kode_member"] . "'";
        $query .= " ORDER BY prd_deskripsipanjang LIMIT 15";

        $data['data'] = DB::select($query);

        return ApiFormatter::success(200, "success", $data);
    }

    public function actionHitungUlang(Request $request){
        DB::beginTransaction();
        try{
            $selectedRow = $request->selectedRow;
            foreach ($request->datatable as $data){
                $prdcd = $data["plu"];
                $query = "";
                $query .= " SELECT prd_unit ";
                $query .= " FROM tbtr_obi_h h ";
                $query .= " JOIN tbtr_obi_d d ";
                $query .= " ON d.obi_tgltrans = h.obi_tgltrans ";
                $query .= " AND d.obi_notrans = h.obi_notrans ";
                $query .= " JOIN tbmaster_prodmast ";
                $query .= " ON prd_prdcd = obi_prdcd ";
                $query .= " WHERE h.obi_nopb = '" . $selectedRow["no_pb"] . "' ";
                $query .= " AND h.obi_notrans = '" . $selectedRow["no_trans"] . "' ";
                $query .= " AND h.obi_kdmember = '" . $selectedRow["kode_member"] . "' ";
                $query .= " AND DATE_TRUNC('day', h.obi_tgltrans) = TO_DATE('" . $selectedRow["tgltrans"] . "', 'DD-MM-YYYY') ";
                $query .= " AND d.obi_recid IS NULL ";
                $query .= " AND d.obi_qtyrealisasi > 0 ";
                $query .= " AND d.obi_prdcd = '" . $prdcd . "' ";
                $query .= " ORDER BY prd_deskripsipanjang LIMIT 1";

                $dt = DB::select($query);

                if(count($dt) > 0){
                    if($dt[0]->prd_unit == "KG"){
                        $qtyBA = $data['qtyba'] / $data['frac'];
                    } else {
                        $qtyBA = $data['qtyba'] * (session("flagSPI") ? 1 : $data['frac']);
                    }
                }

                if(!$this->updateQtyBA($prdcd, $qtyBA)){
                    throw new HttpResponseException(ApiFormatter::error(400, 'Gagal Update Qty PLU ' . $prdcd . ' !'));
                }
            }

            if($selectedRow["tipe_bayar"] == "COD"){
                $dt = DB::select("SELECT kode_promo FROM promo_klikigr WHERE kode_member = '" . $selectedRow["kode_member"] . "' AND no_trans = '" . $selectedRow["no_trans"] . "' AND no_pb = '" . $selectedRow["no_pb"] . "'");

                if(count($dt) > 0){
                    $query = "";
                    $query .= "SELECT SUBSTR(obi_prdcd, 1, 6) || '0' PLU, ";
                    $query .= "       SUM(COALESCE(obi_qtyorder, 0)) orderr, ";
                    $query .= "       SUM(COALESCE(obi_qty_hitungulang, 0)) realisasi ";
                    $query .= "  FROM tbtr_obi_h h ";
                    $query .= "  JOIN tbtr_obi_d d ";
                    $query .= "    ON h.obi_notrans = d.obi_notrans ";
                    $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
                    $query .= " WHERE h.obi_kdmember = '" . $selectedRow["kode_member"] . "' ";
                    $query .= "   AND h.obi_notrans = '" . $selectedRow["no_trans"] . "' ";
                    $query .= "   AND h.obi_nopb = '" . $selectedRow["no_pb"] . "' ";
                    $query .= "   AND d.obi_recid IS NULL ";
                    $query .= "   AND d.obi_qtyorder <> d.obi_qty_hitungulang ";
                    $query .= " GROUP BY SUBSTR(obi_prdcd, 1, 6) || '0' ";
                    $query .= " ORDER BY 1 ";

                    $dt = DB::select($query);

                    if(count($dt) > 0){
                        $splitTrans = explode('/', $selectedRow["no_pb"]);
                        $notrx = $splitTrans[0];

                        $query = "";
                        $query .= "SELECT SUBSTR(obi_prdcd, 1, 6) || '0' PLU, ";
                        $query .= "       SUM(COALESCE(obi_qtyorder, 0)) orderr, ";
                        $query .= "       SUM(COALESCE(obi_qty_hitungulang, 0)) realisasi, ";
                        $query .= "       SUM( ";
                        $query .= "         ROUND(d.obi_hargaweb * COALESCE(obi_qtyorder, 0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ";
                        $query .= "         - ROUND(d.obi_diskon * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * COALESCE(obi_qtyorder, 0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ";
                        $query .= "       ) orderinrp, ";
                        $query .= "       SUM( ";
                        $query .= "         ROUND(d.obi_hargaweb * COALESCE(obi_qty_hitungulang, 0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ";
                        $query .= "         - ROUND(d.obi_diskon * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * COALESCE(obi_qty_hitungulang, 0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ";
                        $query .= "       ) realisasiiinrp ";
                        $query .= "  FROM tbtr_obi_h h ";
                        $query .= "  JOIN tbtr_obi_d d ";
                        $query .= "    ON h.obi_notrans = d.obi_notrans ";
                        $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
                        $query .= "  JOIN tbmaster_prodmast p ON p.prd_prdcd = d.obi_prdcd ";
                        $query .= " WHERE h.obi_kdmember = '" . $selectedRow["kode_member"] . "' ";
                        $query .= "   AND h.obi_notrans = '" . $selectedRow["no_trans"] . "' ";
                        $query .= "   AND h.obi_nopb = '" . $selectedRow["no_pb"] . "' ";
                        $query .= "   AND d.obi_recid IS NULL ";
                        $query .= " GROUP BY SUBSTR(obi_prdcd, 1, 6) || '0' ";
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
                    } else {
                        $query = "";
                        $query .= "UPDATE promo_klikigr ";
                        $query .= "   SET cashback_hitungulang = cashback_order, ";
                        $query .= "       kelipatan_hitungulang = kelipatan, ";
                        $query .= "       reward_per_promo_hitungulang = reward_per_promo, ";
                        $query .= "       reward_nominal_hitungulang = reward_nominal ";
                        $query .= " WHERE kode_member = '" .$selectedRow["kdmember"] . "' ";
                        $query .= "   AND no_trans = '" .$selectedRow["notrans"] . "' ";
                        $query .= "   AND no_pb = '" .$selectedRow["nopb"] . "' ";
                        $query .= "   AND tipe_promo = 'CASHBACK' ";
                        DB::update($query);
                    }
                }
            } else {
                $query = "";
                $query .= "UPDATE promo_klikigr ";
                $query .= "   SET cashback_hitungulang = cashback_order, ";
                $query .= "       kelipatan_hitungulang = kelipatan, ";
                $query .= "       reward_per_promo_hitungulang = reward_per_promo, ";
                $query .= "       reward_nominal_hitungulang = reward_nominal ";
                $query .= " WHERE kode_member = '" .$selectedRow["kdmember"] . "' ";
                $query .= "   AND no_trans = '" .$selectedRow["notrans"] . "' ";
                $query .= "   AND no_pb = '" .$selectedRow["nopb"] . "' ";
                $query .= "   AND tipe_promo = 'CASHBACK' ";
                DB::update($query);
            }

            if(session("flagSPI")){
                $txtContent = $this->PrintNotaHitungUlangKlikSPI("HITUNGULANG", "SPI", $request->tipe_kredit, $request->selectedRow);
            } else {
                $txtContent = $this->PrintNotaHitungUlangKlikSPI("HITUNGULANG", "KlikIGR", $request->tipe_kredit, $request->selectedRow);
            };

            //! IRVAN COMMIT COMMENT
            // DB::commit();
            return ApiFormatter::success(200, "Proses Hitung Ulang Berhasil", $txtContent);

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

    //! NOTE KEVIN
    //* dgvItem2 itu bentuknya array bisa di cek di vb nya
    public function btnApprove_Click($tipeBayar,$nopb,$notrans,$kdmember,$tgltrans,$noBA,$dgvItem2 = []){
        //* buka form approval -> frmApproval
        // frmApproval.UserLevel = 991
        // frmApproval.Keterangan = "Approval Str Mgr./Jr.Mgr. - BA Rusak Kemasan"

        DB::beginTransaction();
        try{

            $userApproval = ''; //! dummy dari username frmApproval

            $seq = "";
            $nodoc = "";
            $noret = "";
            $_TransactionNo = $noret;
            $total = 0;
            $count = 0;

            if($tipeBayar <> 'COD'){
                $seq = $this->GetSeqNodoc();

                // Generating transaction numbers
                $nodoc = "RD" . date("y") . str_pad($seq, 4, "0", STR_PAD_LEFT);
                $noret = "D" . date("y") . str_pad($seq, 4, "0", STR_PAD_LEFT);
                $_TransactionNo = $noret;

                $this->getDataStruk($nopb,$notrans,$kdmember);
            }

            foreach($dgvItem2 as $key => $item){
                $plu = $item->plu;
                $frac = $item->frac;
                $qty = $item->qtyba;

                //* Cek Unit KG
                $query = '';
                $query .= " SELECT prd_unit ";
                $query .= " FROM tbtr_obi_h h ";
                $query .= " JOIN tbtr_obi_d d ";
                $query .= " ON d.obi_tgltrans = h.obi_tgltrans ";
                $query .= " AND d.obi_notrans = h.obi_notrans ";
                $query .= " JOIN tbmaster_prodmast ";
                $query .= " ON prd_prdcd = obi_prdcd ";
                $query .= " WHERE h.obi_nopb = '" . $nopb . "' ";
                $query .= " AND h.obi_notrans = '" . $notrans . "' ";
                $query .= " AND h.obi_kdmember = '" . $kdmember . "' ";
                $query .= " AND DATE_TRUNC('day', h.obi_tgltrans) = TO_DATE('" . $tgltrans . "','DD-MM-YYYY') ";
                $query .= " AND d.obi_recid IS NULL ";
                $query .= " AND d.obi_qtyrealisasi > 0 ";
                $query .= " AND d.obi_prdcd = '" . $plu . "' ";
                $query .= " ORDER BY prd_deskripsipanjang ";
                $dt = DB::select($query);

                if(count($dt) > 0){
                    if($dt[0]->prd_unit == 'KG'){
                        $qty = $qty / $frac;
                    }else{
                        $frac = session('flagSPI') == true ? 1 : $frac;
                        $qty = $qty * $frac;
                    }
                }

                if($qty > 0){
                    if($tipeBayar == 'COD'){
                        $this->prosesBA_COD($plu, $qty);
                    }else{
                        $this->prosesBPBR($nodoc, $noret, $plu, $qty);
                        $this->insertMstranD($nodoc, $noret, $plu, $qty, $count, $total);
                    }
                }

                $count++;
            }

            if($tipeBayar == 'COD'){
                $this->prosesDSPUlang_COD($tipeBayar, $nopb, $notrans, $tgltrans, $kdmember);
            }else{
                $this->insertMstranH($nodoc, $noret);
                $this->insertVCHRetur($nodoc, $noret, $total);
            }

            //* UPDATE TBTR_BARUSAK_SPI
            $query = '';
            $query .= " UPDATE tbtr_barusak_spi  ";
            $query .= " SET brk_statusba = 'DONE', ";
            $query .= "     brk_noba = '" . $noBA . "', ";
            $query .= "     brk_tglba = CURRENT_DATE, ";
            $query .= "     brk_userapprove = '" . $userApproval . "', ";
            $query .= "     brk_tglapprove = CURRENT_DATE, ";
            $query .= "     brk_modify_by = '" . session('userid') . "', ";
            $query .= "     brk_modify_dt = NOW() ";
            $query .= " WHERE brk_nopb = '" . $nopb . "' ";
            $query .= " AND brk_kodemember = '" . $kdmember . "' ";
            DB::update($query);

            if($tipeBayar <> 'COD'){
                $query = '';
                $query .= " SELECT COALESCE(vcrt_nominal,0) nominal ";
                $query .= " FROM TBTR_VCH_RETUR ";
                $query .= " WHERE vcrt_transactionno = '" . $noret . "' ";
                $query .= " AND vcrt_cashierid = '" . session('userid') . "' ";
                $query .= " AND vcrt_station = '" . session("SPI_STATION") . "' ";
                $query .= " AND vcrt_kodemember = '" . $kdmember . "' ";
                $dtBR = DB::select($query);

                if(count($dtBR) > 0){
                    $nominalBR = $dtBR[0]->nominal;
                    $nominalBRCustom = str_replace(",", "", $nominalBR); // Remove commas
                    $nominalBRCustom = preg_replace('/\.(?=.*\.)/', '', $nominalBRCustom); // Remove all but the last dot

                    if($nominalBR > 0){
                        $stringNominalBR = DB::select("SELECT TRIM(TO_CHAR(CAST('" . $nominalBRCustom . "' AS numeric), '999G999G999'))");
                    }

                    if(session('flagSPI')){
                        $this->sendNotif_SPIKLIK('SPI', $nopb, 'Nominal Barang Rusak Rp ' . str_replace(",", ".", $stringNominalBR));
                    }else{
                        $this->sendNotif_SPIKLIK('KLIK', $nopb, 'Nominal Barang Rusak Rp ' . str_replace(",", ".", $stringNominalBR));
                    }
                }
            }

            $this->CetakBARK();

            if($tipeBayar <> "COD"){
                $this->CetakBPBR();
            }


	        dd('done comment commit');
            //DB::commit();

            return ApiFormatter::success(200, 'btn Approve Success');

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

    private function prosesBA_COD($plu, $qty, $tgltrans, $notrans){
        //* UPDATE TBTR_OBI_D - OBI_QTYREALISASI
        $query = '';
        $query .= " UPDATE tbtr_obi_d ";
        $query .= " SET obi_qtyrealisasi = obi_qtyrealisasi - " . $qty . " ";
        $query .= " WHERE obi_tgltrans = TO_DATE('" . $tgltrans . "','DD-MM-YYYY') ";
        $query .= " AND obi_notrans = '" . $notrans . "' ";
        $query .= " AND obi_prdcd = '" . $plu . "' ";
        DB::update($query);

        //* UPDATE TBTR_PACKING_OBI - POBI_QTY
        $query = '';
        $query .= " UPDATE tbtr_packing_obi ";
        $query .= " SET pobi_qty = pobi_qty - " . $qty . " ";
        $query .= " WHERE DATE_TRUNC('DAY',pobi_tgltransaksi) = TO_DATE('" . $tgltrans . "','DD-MM-YYYY') ";
        $query .= " AND pobi_notransaksi = '" . $notrans . "' ";
        $query .= " AND pobi_prdcd = '" . $plu . "' ";
        DB::update($query);

        return true;
    }

    //! ADA NOTE KEVIN
    private function prosesBPBR($nodoc, $noret, $plu, $qty, $nopb, $notrans){

        //! NOTE KEVIN
        //? cari variable ini darimana
        $kdmember = '';

        $UserMODUL = session('userid');
        $KodeIGR = session('KODECABANG');
        $StationMODUL = session("SPI_STATION");
        $tglStruk = $this->tglStruk;
        $cashierID = $this->cashierID;
        $cashierStation = $this->cashierStation;
        $noStruk = $this->noStruk;

        $query = '';
        $query .= "SELECT DISTINCT ";
        $query .= "  trjd_flagtax1 tax1, ";
        $query .= "  trjd_flagtax2 tax2, ";
        $query .= "  " . $qty . " qty, ";
        $query .= "  Coalesce(ROUND(st_avgcost::INT,2),0) avgCost, ";
        $query .= "  Coalesce(ROUND(st_avgcost::INT,2),0) * " . $qty . " ttlCost, ";
        $query .= "  Coalesce(ROUND(trjd_unitprice,2),0) price, ";
        $query .= "  Coalesce(ROUND(trjd_unitprice,2),0) * " . $qty . " ttlPrice, ";
        $query .= "  prd_ppn persenPpn ";
        $query .= "FROM tbtr_obi_h h ";
        $query .= "JOIN tbtr_obi_d d ";
        $query .= "ON d.obi_notrans = h.obi_notrans ";
        $query .= "AND d.obi_tgltrans = h.obi_tgltrans ";
        $query .= "JOIN tbmaster_prodmast ";
        $query .= "ON prd_prdcd = d.obi_prdcd ";
        $query .= "JOIN tbtr_jualdetail ";
        $query .= "ON trjd_transactionno = h.obi_nostruk ";
        $query .= "AND DATE_TRUNC('DAY',trjd_transactiondate) = DATE_TRUNC('DAY',h.obi_tglstruk) ";
        $query .= "AND trjd_cus_kodemember = h.obi_kdmember ";
        $query .= "AND trjd_cashierstation = h.obi_kdstation ";
        $query .= "AND trjd_prdcd = d.obi_prdcd ";
        $query .= "AND trjd_transactiontype = 'S' ";
        $query .= "JOIN tbmaster_stock ";
        $query .= "ON st_lokasi = '01' ";
        $query .= "AND st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "WHERE h.obi_nopb = '" . $nopb . "' ";
        $query .= "AND h.obi_notrans = '" . $notrans . "' ";
        $query .= "AND h.obi_kdmember = '" . $kdmember . "' ";
        $query .= "AND d.obi_prdcd = '" . $plu . "' ";
        $query .= "AND d.obi_recid IS NULL ";
        $dtStruk = DB::select($query);

        if(count($dtStruk) == 0){
            $message = "Data Struk Item " . $plu . " tidak ditemukan.";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $query .= "INSERT INTO TBTR_RETUROMI ( ";
        $query .= "  ROM_KODEIGR,  ";
        $query .= "  ROM_RECORDID,  ";
        $query .= "  ROM_NODOKUMEN,  ";
        $query .= "  ROM_TGLDOKUMEN,  ";
        $query .= "  ROM_REFERENSISTRUK,  ";
        $query .= "  ROM_MEMBER,  ";
        $query .= "  ROM_KODEKASIR,  ";
        $query .= "  ROM_STATION,  ";
        $query .= "  ROM_PRDCD,  ";
        $query .= "  ROM_FLAGBKP,  ";
        $query .= "  ROM_FLAGBKP2,  ";
        $query .= "  ROM_QTY,  ";
        $query .= "  ROM_QTYREALISASI,  ";
        $query .= "  ROM_QTYSELISIH,  ";
        $query .= "  ROM_HRGSATUAN,  ";
        $query .= "  ROM_AVGCOST,  ";
        $query .= "  ROM_TTLNILAI,  ";
        $query .= "  ROM_TTLCOST,  ";
        $query .= "  ROM_HRG,  ";
        $query .= "  ROM_TTL,  ";
        $query .= "  ROM_PERSENPPN,  ";
        $query .= "  ROM_CREATE_BY,  ";
        $query .= "  ROM_CREATE_DT  ";
        $query .= ")  ";
        $query .= "VALUES (  ";
        $query .= "  '" . $KodeIGR . "',  ";
        $query .= "  '2',  ";
        $query .= "  '" . $noret . "',  ";
        $query .= "  CURRENT_DATE,  ";
        $query .= "  '" . $tglStruk .'_'. $cashierID .'_'. $cashierStation .'_'. $noStruk . "',  ";
        $query .= "  '" . $kdmember . "',  ";
        $query .= "  '" . $UserMODUL . "',  ";
        $query .= "  '" . $StationMODUL . "',  ";
        $query .= "  '" . $plu . "',  ";
        $query .= "  '" . $dtStruk[0]->tax1 . "',  ";
        $query .= "  '" . $dtStruk[0]->tax2 . "',  ";
        $query .= "  '" . str_replace(",", ".", $qty) . "',  ";
        $query .= "  '" . str_replace(",", ".", $qty) . "',  ";
        $query .= "  '0',  ";
        $query .= "  '0',  ";
        $query .= "  '" . str_replace(",", ".", $dtStruk[0]->avgCost) . "',  ";
        $query .= "  '0',  ";
        $query .= "  '" . str_replace(",", ".", $dtStruk[0]->ttlCost) . "',  ";
        $query .= "  '" . str_replace(",", ".", $dtStruk[0]->price) . "',  ";
        $query .= "  '" . str_replace(",", ".", $dtStruk[0]->ttlPrice) . "',  ";
        $query .= "  '" . str_replace(",", ".", $dtStruk[0]->persenPpn) . "',  ";
        $query .= "  '" . $UserMODUL . "',  ";
        $query .= "  NOW() ";
        $query .= ")  ";

        return true;
    }

    //! ADA NOTE KEVIN
    private function insertMstranD($nodoc, $noret, $plu, $qty, $count, $total){

        //! NOTE KEVIN
        //? VARIABLE INI CARI DEFINE DARI MANA
        $notrans = '';
        $kdmember = '';
        $nopb = '';

        $KodeIGR = session('KODECABANG');
        $UserMODUL = session('userid');
        $tglStruk = $this->tglStruk;
        $cashierID = $this->cashierID;
        $cashierStation = $this->cashierStation;
        $noStruk = $this->noStruk;

        $query = '';
        $query .= "SELECT DISTINCT ";
        $query .= "  d.obi_prdcd plu, ";
        $query .= "  hgb_statusbarang statusbarang, ";
        $query .= "  hgb_kodesupplier kodeSup, ";
        $query .= "  sup_pkp pkpSup, ";
        $query .= "  prd_kodedivisi kodedivisi, ";
        $query .= "  prd_kodedepartement kodedepartement, ";
        $query .= "  prd_kodekategoribarang kodekategoribarang, ";
        $query .= "  prd_flagbkp1 flagbkp1, ";
        $query .= "  prd_flagbkp2 flagbkp2, ";
        $query .= "  prd_unit unit, ";
        $query .= "  prd_frac frac, ";
        $query .= "  " . $qty . " qty, ";
        $query .= "  COALESCE(ROUND(st_avgcost::int,2),0) avgCost, ";
        $query .= "  COALESCE(ROUND(st_lastcost::int,2),0) lastCost, ";
        $query .= "  COALESCE(ROUND(st_avgcost::int,2),0) * " . $qty . " ttlCost, ";
        $query .= "  COALESCE(trjd_unitprice,0) hrgPrice, ";
        $query .= "  COALESCE(trjd_unitprice,0) * " . $qty . " ttlPrice, ";
        $query .= "  COALESCE(st_saldoakhir,0) saldoakhir, ";
        $query .= "  prd_ppn persenPpn ";
        $query .= "FROM tbtr_obi_h h ";
        $query .= "JOIN tbtr_obi_d d ";
        $query .= " ON  d.obi_notrans = h.obi_notrans ";
        $query .= " AND d.obi_tgltrans = h.obi_tgltrans ";
        $query .= "JOIN tbmaster_prodmast ";
        $query .= " ON  prd_prdcd = d.obi_prdcd ";
        $query .= "JOIN tbtr_jualdetail ";
        $query .= " ON  trjd_transactionno = h.obi_nostruk ";
        $query .= " AND DATE_TRUNC('DAY',trjd_transactiondate) = DATE_TRUNC('DAY',h.obi_tglstruk) ";
        $query .= " AND trjd_cus_kodemember = h.obi_kdmember ";
        $query .= " AND trjd_cashierstation = h.obi_kdstation ";
        $query .= " AND trjd_prdcd = d.obi_prdcd ";
        $query .= " AND trjd_transactiontype = 'S' ";
        $query .= "LEFT JOIN tbmaster_stock ";
        $query .= " ON  st_lokasi = '01' ";
        $query .= " AND st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "JOIN tbmaster_hargabeli ";
        $query .= " ON  hgb_tipe = '2' ";
        $query .= " AND hgb_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ";
        $query .= "JOIN tbmaster_supplier ";
        $query .= " ON  sup_kodesupplier = hgb_kodesupplier ";
        $query .= "WHERE h.obi_nopb = '" . $nopb . "' ";
        $query .= " AND h.obi_notrans = '" . $notrans . "' ";
        $query .= " AND h.obi_kdmember = '" . $kdmember . "' ";
        $query .= " AND d.obi_prdcd = '" . $plu . "' ";
        $query .= " AND d.obi_recid IS NULL ";
        $dtItem = DB::select($query);

        $fdisc2 = "";
        $keter = "";
        if(count($dtItem) == 0){
            $message = 'Data item ' . $plu . ' tidak ditemukan pada insertMstranD';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        if(strtoupper($dtItem[0]->statusbarang) == 'PT'){
            $fdisc2 = "R";
            $keter = "RETUR PT BARANG RUSAK SPI";

        }elseif(strtoupper($dtItem[0]->statusbarang) == 'RT' OR strtoupper($dtItem[0]->statusbarang) == 'TG'){
            $fdisc2 = "T";
            $keter = "RETUR " . strtoupper($dtItem[0]->statusbarang) . " BARANG RUSAK SPI";
        }

        $lcostlama = doubleval($dtItem[0]->lastCost);
        $cosbaik = doubleval($dtItem[0]->avgCost);
        $qtybaik = doubleval($dtItem[0]->saldoakhir);

        $query = '';
        $query .= "INSERT INTO tbtr_mstran_d ( ";
        $query .= "  mstd_kodeigr,  ";
        $query .= "  mstd_typetrn,  ";
        $query .= "  mstd_nodoc,  ";
        $query .= "  mstd_tgldoc,  ";
        $query .= "  mstd_nopo,  ";
        $query .= "  mstd_tglpo,  ";
        $query .= "  mstd_nofaktur,  ";
        $query .= "  mstd_tglfaktur,  ";
        $query .= "  mstd_kodesupplier,  ";
        $query .= "  mstd_pkp,  ";
        $query .= "  mstd_seqno,  ";
        $query .= "  mstd_prdcd,  ";
        $query .= "  mstd_kodedivisi,  ";
        $query .= "  mstd_kodedepartement,  ";
        $query .= "  mstd_kodekategoribrg,  ";
        $query .= "  mstd_bkp,  ";
        $query .= "  mstd_fobkp,  ";
        $query .= "  mstd_unit,  ";
        $query .= "  mstd_frac,  ";
        $query .= "  mstd_loc,  ";
        $query .= "  mstd_qty,  ";
        $query .= "  mstd_hrgsatuan,  ";
        $query .= "  mstd_flagdisc1,  ";
        $query .= "  mstd_flagdisc2,  ";
        $query .= "  mstd_gross,  ";
        $query .= "  mstd_avgcost,  ";
        $query .= "  mstd_ocost,  ";
        $query .= "  mstd_posqty,  ";
        $query .= "  mstd_keterangan,  ";
        $query .= "  mstd_create_dt,  ";
        $query .= "  mstd_create_by,  ";
        $query .= "  mstd_qtybonus1,  ";
        $query .= "  mstd_qtybonus2,  ";
        $query .= "  mstd_persendisc1,  ";
        $query .= "  mstd_rphdisc1,  ";
        $query .= "  mstd_persendisc2,  ";
        $query .= "  mstd_rphdisc2,  ";
        $query .= "  mstd_rphdisc2ii,  ";
        $query .= "  mstd_rphdisc2iii,  ";
        $query .= "  mstd_persendisc3,  ";
        $query .= "  mstd_rphdisc3,  ";
        $query .= "  mstd_persendisc4,  ";
        $query .= "  mstd_rphdisc4,  ";
        $query .= "  mstd_dis4cp,  ";
        $query .= "  mstd_dis4cr,  ";
        $query .= "  mstd_dis4rp,  ";
        $query .= "  mstd_dis4rr,  ";
        $query .= "  mstd_dis4jp,  ";
        $query .= "  mstd_dis4jr,  ";
        $query .= "  mstd_discrph,  ";
        $query .= "  mstd_ppnrph,  ";
        $query .= "  mstd_ppnbmrph,  ";
        $query .= "  mstd_ppnbtlrph,  ";
        $query .= "  mstd_persendisc2ii,  ";
        $query .= "  mstd_persendisc2iii,  ";
        $query .= "  mstd_persenppn,  ";
        $query .= "  mstd_cterm ";
        $query .= ")  ";
        $query .= "VALUES (  ";
        $query .= "  '" . $KodeIGR . "',  ";
        $query .= "  'Z',  ";
        $query .= "  '" . $nodoc . "',  ";
        $query .= "  CURRENT_DATE,  ";
        $query .= "  '" . $noret . "',  ";
        $query .= "  CURRENT_DATE,  ";
        $query .= "  '" . $tglStruk . $cashierID . $cashierStation . $noStruk . "',  ";
        $query .= "  TO_DATE('" . $tglStruk . "', 'YYYYMMDD'),  ";
        $query .= "  '" . $dtItem[0]->kodeSup . "',  ";
        $query .= "  '" . $dtItem[0]->pkpSup . "',  ";
        $query .= "  '" . $count + 1 . "',  ";
        $query .= "  '" . $plu . "',  ";
        $query .= "  '" . $dtItem[0]->kodedivisi . "',  ";
        $query .= "  '" . $dtItem[0]->kodedepartement . "',  ";
        $query .= "  '" . $dtItem[0]->kodekategoribarang . "',  ";
        $query .= "  '" . $dtItem[0]->flagbkp1 . "',  ";
        $query .= "  '" . $dtItem[0]->flagbkp2 . "',  ";
        $query .= "  '" . $dtItem[0]->unit . "',  ";
        $query .= "  '" . $dtItem[0]->frac . "',  ";
        $query .= "  '" . $KodeIGR . "',  ";
        $query .= "  '" . str_replace(",", ".", $dtItem[0]->qty) . "',  ";
        $query .= "  '" . ((int)$dtItem[0]->avgCost * str_replace(",", ".", $dtItem[0]->frac)) . "',  ";
        $query .= "  'B',  ";
        $query .= "  '" . str_replace(",", ".", $fdisc2) . "',  ";
        $query .= "  '" . str_replace(",", ".", $dtItem[0]->ttlCost) . "',  ";
        $query .= "  '" . strtoupper($dtItem[0]->unit) == 'KG' ? $dtItem[0]->avgCost : $dtItem[0]->avgCost * str_replace(",", ".", $dtItem[0]->frac) . "',  ";
        $query .= "  '" . strtoupper($dtItem[0]->unit) == 'KG' ? $dtItem[0]->avgCost : $dtItem[0]->avgCost * str_replace(",", ".", $dtItem[0]->frac) . "',  ";
        $query .= "  '" . str_replace(",", ".", $dtItem[0]->saldoakhir) . "',  ";
        $query .= "  '" . $keter . "', ";
        $query .= "  NOW(), ";
        $query .= "  '" . $UserMODUL . "',  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  0,  ";
        $query .= "  '" . str_replace(",", ".", $dtItem[0]->persenPpn) . "',  ";
        $query .= "  0  ";
        $query .= ")  ";
        DB::insert($query);

        if(strtoupper($dtItem[0]->statusbarang) == 'PT'){
            //* JIKA STATUS BARANG PT (PUTUS) MAKA MASUKAN KE LOKASI BAIK BUAT DIPROSES HJK MANUAL
            $query = '';
            $query .= " UPDATE TBMASTER_STOCK ";
            $query .= " SET ST_SALES = coalesce(ST_SALES, 0) - '" . $qty . "',  ";
            $query .= "     ST_SALDOAKHIR = coalesce(ST_SALDOAKHIR, 0) + '" . $qty . "',  ";
            $query .= "     ST_MODIFY_BY = '" . $UserMODUL . "',  ";
            $query .= "     ST_MODIFY_DT = NOW()  ";
            $query .= " WHERE ST_PRDCD = SUBSTR('" . $plu . "',1,6) || '0' AND ST_LOKASI = '01' ";
            DB::update($query);

        }else{ //* JIKA STATUS BARANG BUKAN PT (PUTUS) MAKA MASUKAN KE LOKASI RETUR
            //* POTONG ST_SALES DAN TAMBAH ST_TRFOUT DI BRG BAIK
            $query = '';
            $query .= " UPDATE TBMASTER_STOCK ";
            $query .= " SET ST_SALES = coalesce(ST_SALES, 0) - '" . $qty . "',  ";
            $query .= "     ST_TRFOUT = coalesce(ST_TRFOUT, 0) + '" . $qty . "',  ";
            $query .= "     ST_MODIFY_BY = '" . $UserMODUL . "',  ";
            $query .= "     ST_MODIFY_DT = NOW()  ";
            $query .= " WHERE ST_PRDCD = SUBSTR('" . $plu . "',1,6) || '0' AND ST_LOKASI = '01' ";
            DB::update($query);

            //* CEK APAKAH ADA RECORD STOCK BARANG RETUR, KALO GA ADA YAH DIINSERT
            $dtSTK = DB::select("SELECT coalesce(COUNT(1), 0) FROM tbmaster_stock WHERE st_kodeigr = '" . $KodeIGR . "' AND st_lokasi = '02' AND st_prdcd = SUBSTR('" . $plu . "',1,6) || '0'")[0]->coalesce;
            if($dtSTK == 0){
                $query = '';
                $query .= " INSERT INTO tbmaster_stock ( ";
                $query .= "   st_kodeigr, ";
                $query .= "   st_lokasi, ";
                $query .= "   st_prdcd, ";
                $query .= "   st_trfin, ";
                $query .= "   st_saldoakhir, ";
                $query .= "   st_create_dt, ";
                $query .= "   st_create_by, ";
                $query .= "   st_saldoawal, ";
                $query .= "   st_trfout, ";
                $query .= "   st_sales, ";
                $query .= "   st_retur, ";
                $query .= "   st_adj, ";
                $query .= "   st_intransit, ";
                $query .= "   st_min, ";
                $query .= "   st_max, ";
                $query .= "   st_avgcostmonthend, ";
                $query .= "   st_rpsaldoawal, ";
                $query .= "   st_rpsaldoawal2 ";
                $query .= " ) ";
                $query .= " VALUES ( ";
                $query .= "   '" . $KodeIGR . "', ";
                $query .= "   '02', ";
                $query .= "   SUBSTR('" . $plu . "',1,6) || '0', ";
                $query .= "   '0', ";
                $query .= "   '0', ";
                $query .= "   CURRENT_DATE, ";
                $query .= "   '" . $UserMODUL . "', ";
                $query .= "   0, ";
                $query .= "   0, ";
                $query .= "   0, ";
                $query .= "   0, ";
                $query .= "   0, ";
                $query .= "   0, ";
                $query .= "   0, ";
                $query .= "   0, ";
                $query .= "   0, ";
                $query .= "   0, ";
                $query .= "   0 ";
                $query .= " ) ";
                DB::insert($query);

                $osret = 0;
                $qtyret = 0;

            }else{
                $query = '';
                $query .= " SELECT st_avgcost, st_saldoakhir ";
                $query .= " FROM tbmaster_stock ";
                $query .= " WHERE st_kodeigr = '" . $KodeIGR . "' ";
                $query .= " AND st_lokasi = '02' ";
                $query .= " AND st_prdcd = SUBSTR('" . $plu . "',1,6) || '0' ";
                $dtStk = DB::select($query);

                $cosret = doubleval($dtStk[0]->st_avgcost);
                $qtyret = doubleval($dtStk[0]->st_saldoakhir);
            }

            //* TAMBAH ST_TRFIN DAN SALDOAKHIR DI BRG RETUR
            $query = '';
            $query .= " UPDATE TBMASTER_STOCK ";
            $query .= " SET ST_TRFIN = coalesce(ST_TRFIN, 0) + '" . $qty . "', ";
            $query .= "     ST_SALDOAKHIR = coalesce(ST_SALDOAKHIR, 0) + '" . $qty . "', ";
            $query .= "     ST_MODIFY_BY = '" . $UserMODUL . "', ";
            $query .= "     ST_MODIFY_DT = NOW() ";
            $query .= " WHERE ST_PRDCD = SUBSTR('" . $plu . "',1,6) || '0' AND ST_LOKASI = '02' ";
            DB::update($query);

            //* INSERT HISTORY COST
            if ($qtyret > 0) {
                $cosstk = round((($qtyret * $cosret) + ($qty * $cosbaik)) / ($qtyret + $qty), 2);
            } else {
                $cosstk = round((($qty * $cosbaik) / $qty), 2);
            }

            $query = '';
            $query .= "INSERT INTO tbhistory_cost ( ";
            $query .= "  hcs_kodeigr, ";
            $query .= "  hcs_typetrn, ";
            $query .= "  hcs_lokasi, ";
            $query .= "  hcs_prdcd, ";
            $query .= "  hcs_tglbpb, ";
            $query .= "  hcs_nodocbpb, ";
            $query .= "  hcs_avglama, ";
            $query .= "  hcs_avgbaru, ";
            $query .= "  hcs_qtybaru, ";
            $query .= "  hcs_qtylama, ";
            $query .= "  hcs_lastqty, ";
            $query .= "  hcs_lastcostbaru, ";
            $query .= "  hcs_lastcostlama, ";
            $query .= "  hcs_create_by, ";
            $query .= "  hcs_create_dt ";
            $query .= ") ";
            $query .= "VALUES ( ";
            $query .= "  '" . $KodeIGR . "', ";
            $query .= "  'Z', ";
            $query .= "  '02', ";
            $query .= "  SUBSTR('" . $plu . "',1,6) || '0', ";
            $query .= "  CURRENT_DATE, ";
            $query .= "  '" . $noret . "', ";
            if($dtItem[0]->unit == 'KG'){
                $query .= "  '" . str_replace(",", ".", $cosret) . "', ";
            }else{
                $query .= "  '" . str_replace(",", ".", $cosret * $dtItem[0]->frac) . "', ";
            }

            if($dtItem[0]->unit == 'KG'){
                $query .= "  '" . str_replace(",", ".", $cosstk) . "', ";
            }else{
                $query .= "  '" . str_replace(",", ".", $cosstk * $dtItem[0]->frac) . "', ";
            }
            $query .= "  '" . $qty . "', ";
            $query .= "  '" . $qtyret . "', ";
            $query .= "  '" . ($qty + $qtyret) . "', ";
            $query .= "  '" . str_replace(",", ".", $cosbaik * $dtItem[0]->frac) . "', ";
            $query .= "  '" . str_replace(",", ".", $lcostlama * $dtItem[0]->frac) . "', ";
            $query .= "  '" . $UserMODUL . "',";
            $query .= "  NOW() ";
            $query .= ") ";

            //* UPDATE AVGCOST SAMA LASTCOST BARANG RETUR
            $query .= " UPDATE tbmaster_stock ";
            $query .= " SET st_avgcost = '" . str_replace(",", ".", $dtItem[0]->cosstk) . "', ";
            $query .= "     st_lastcost = '" . str_replace(",", ".", $dtItem[0]->cosbaik) . "' ";
            $query .= " WHERE st_kodeigr = '" . $KodeIGR . "' ";
            $query .= " AND st_lokasi = '02' ";
            $query .= " AND st_prdcd = SUBSTR('" . $plu . "',1,6) || '0' ";
        }

        $total += $dtItem[0]->ttlPrice;

        return true;
    }

    private function prosesDSPUlang_COD($tipeBayar, $nopb, $notrans, $tgltrans, $kdmember){

        if($tipeBayar == 'COD'){
            $query = '';
            $query .= "UPDATE promo_klikigr ";
            $query .= "   SET cashback_real = NULL ";
            $query .= "WHERE kode_member = '" . $kdmember . "' ";
            $query .= "  AND no_trans = '" . $notrans . "' ";
            $query .= "  AND no_pb = '" . $nopb . "' ";
            DB::update($query);

            $query .= "SELECT kode_promo ";
            $query .= "FROM promo_klikigr ";
            $query .= "WHERE kode_member = '" . $kdmember . "' ";
            $query .= "  AND no_trans = '" . $notrans . "' ";
            $query .= "  AND no_pb = '" . $nopb . "' ";
            $dt = DB::select($query);

            if(count($dt) > 0){
                $query = '';
                $query .= "SELECT SUBSTR(obi_prdcd,1,6) || '0' PLU, ";
                $query .= "       SUM(coalesce(obi_qtyorder,0)) orderr, ";
                $query .= "       SUM(coalesce(obi_qtyrealisasi,0) - coalesce(obi_qtyba,0)) realisasi ";
                $query .= "  FROM tbtr_obi_h h ";
                $query .= "  JOIN tbtr_obi_d d ";
                $query .= "    ON h.obi_notrans = d.obi_notrans ";
                $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
                $query .= " WHERE h.obi_kdmember = '" . $kdmember . "' ";
                $query .= "   AND h.obi_notrans = '" . $notrans . "' ";
                $query .= "   AND h.obi_nopb = '" . $nopb . "' ";
                $query .= "   AND d.obi_recid IS NULL";
                $query .= "   AND d.obi_qtyorder <> d.obi_qtyrealisasi - coalesce(d.obi_qtyba,0) ";
                $query .= " GROUP BY SUBSTR(obi_prdcd,1,6) || '0' ";
                $query .= " ORDER BY 1 ";
                $dt = DB::select($query);

                if(count($dt) > 0){
                    //* KALAU ADA YANG SELISIH HITUNG ULANG
                    // Split the string based on the "/" delimiter
                    $splitTrans = explode("/", $nopb);

                    // Access the first element of the resulting array
                    $notrx = $splitTrans[0];

                    $query = '';
                    $query .= "SELECT SUBSTR(obi_prdcd,1,6) || '0' PLU, ";
                    $query .= "       SUM(coalesce(obi_qtyorder,0)) orderr, ";
                    $query .= "       SUM(coalesce(obi_qtyrealisasi,0) - coalesce(d.obi_qtyba,0)) realisasi, ";
                    $query .= "       SUM( ";
                    $query .= "         ROUND(d.obi_hargaweb * COALESCE(obi_qtyorder,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ";
                    $query .= "         - ROUND(d.obi_diskon * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * COALESCE(obi_qtyorder,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END),-1) ";
                    $query .= "       ) orderinrp, ";
                    $query .= "       SUM( ";
                    $query .= "         ROUND(d.obi_hargaweb * (coalesce(obi_qtyrealisasi,0) - coalesce(d.obi_qtyba,0)) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ";
                    $query .= "         - ROUND(d.obi_diskon * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * (coalesce(obi_qtyrealisasi,0) - coalesce(d.obi_qtyba,0)) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END),-1) ";
                    $query .= "       ) realisasiiinrp ";
                    $query .= "  FROM tbtr_obi_h h ";
                    $query .= "  JOIN tbtr_obi_d d ";
                    $query .= "    ON h.obi_notrans = d.obi_notrans ";
                    $query .= "   AND h.obi_tgltrans = d.obi_tgltrans ";
                    $query .= "  JOIN tbmaster_prodmast p ON p.prd_prdcd = d.obi_prdcd ";
                    $query .= " WHERE h.obi_kdmember = '" . $kdmember . "' ";
                    $query .= "   AND h.obi_notrans = '" . $notrans . "' ";
                    $query .= "   AND h.obi_nopb = '" . $nopb . "' ";
                    $query .= "   AND d.obi_recid IS NULL";
                    $query .= " GROUP BY SUBSTR(obi_prdcd,1,6) || '0' ";
                    $query .= " ORDER BY 1 ";
                    $dt = DB::select($query);

                    $transKlik = [];
                    foreach($dt as $item){
                        $transKlik[] = [
                            'PLU' => $item->PLU,
                            'order' => $item->orderr,
                            'realisasi' => $item->realisasi,
                            'orderinrp' => $item->orderinrp,
                            'realisasiiinrp' => $item->realisasiiinrp,
                        ];
                    }

                    $api = $this->requestPromoBARK($transKlik, $kdmember, $notrans, $nopb);
                    if($api != true){
                        $message = 'Gagal Hitung Ulang Promosi';
                        throw new HttpResponseException(ApiFormatter::error(400, $message));
                    }

                }else{
                    //* KALAU TIDAK ADA YANG SELISIH FULL SEMUA
                    $query = '';
                    $query .= "UPDATE promo_klikigr ";
                    $query .= "   SET cashback_real = cashback_order ";
                    $query .= "WHERE kode_member = '" . $kdmember . "' ";
                    $query .= "  AND no_trans = '" . $notrans . "' ";
                    $query .= "  AND no_pb = '" . $nopb . "' ";
                    $query .= "  AND tipe_promo = 'CASHBACK' ";
                    DB::update($query);
                }
            }
        }

        //* Ulang DSPB
        $query = '';
        $query .= "UPDATE tbtr_obi_h  ";
        $query .= "SET obi_realorder = 0, ";
        $query .= "    obi_realppn = 0, ";
        $query .= "    obi_realdiskon = 0, ";
        $query .= "	   obi_realitem = 0, ";
        $query .= "	   obi_recid = CASE WHEN obi_flagbayar = 'Y' THEN '3' ELSE CASE WHEN obi_freeongkir = 'T' THEN '3' ELSE '7' END END, ";
        $query .= "	   obi_ekspedisi = CASE WHEN obi_flagbayar = 'Y' THEN obi_ekspedisi ELSE 0 END, ";
        $query .= "	   obi_zona = '1', ";
        $query .= "	   obi_kdekspedisi = CASE WHEN obi_flagbayar = 'Y' THEN obi_kdekspedisi ELSE null END, ";
        $query .= "	   obi_realcashback = 0 ";
        $query .= "WHERE obi_notrans = '" . $notrans . "' ";
        $query .= "AND obi_nopb =  '" . $nopb . "' ";

        //* BATALIN DSP SPI
        $query = '';
        $query .= "DELETE FROM tbtr_dsp_spi ";
        $query .= "WHERE dsp_kodemember = '" . $kdmember . "' ";
        $query .= "  AND dsp_notrans = '" . $notrans . "' ";
        $query .= "  AND dsp_nopb = '" . $nopb . "' ";

        //* UPDATE ULANG INTRANSIT
        $this->updateIntransit(False, $notrans, $tgltrans);

        $userMODUL = session('userid');
        $KodeIGR = session('KODECABANG');
        $procedure = DB::select("call sp_create_draftstrukobi ('$nopb','$tgltrans','$notrans', '$userMODUL', '$KodeIGR', '')");
        $procedure = $procedure[0]->p_status;

        if (str_contains($procedure, 'Sukses!')) {
            $query = '';
            $query .= " UPDATE TBTR_OBI_H SET OBI_RECID = '5'  ";
            $query .= " Where OBI_NOPB = '" . $nopb . "'  ";
            $query .= " AND OBI_KDMEMBER = '" . $kdmember . "'  ";
            $query .= " AND OBI_NOTRANS = '" . $notrans . "'  ";
            $query .= " AND OBI_RECID = '4'  ";
            DB::update($query);
        }
    }

    //! ADA NOTE KEVIN
    private function requestPromoBARK($transKlik, $kdMember, $noTrans, $noPB, $flagHitungUlang = false){

        $query = '';
        $query .= " SELECT ws_url, cre_name, cre_key FROM tbmaster_webservice  ";
        $query .= " LEFT JOIN tbmaster_credential ON ws_nama = cre_type ";
        if(session('flagSPI')){
            $query .= " WHERE ws_nama = 'HIT_PROMO_SPI' ";
        }else{
            $query .= " WHERE ws_nama = 'HIT_PROMO_KLIK' ";
        }
        $dt = DB::select($query);

        $splitTrans = explode("/", $noPB);
        $trxid = $splitTrans[0];

        $urlPromo = "https://api.mitraindogrosir.co.id/dataportal/klik-igr/flex-promo/recalculate-new";
        $apiName = "x-api-key";
        $apiKey = "zEebxEx3Y44V8UdNJdkOE79HdYEMKDER1eEvYg2T";
        if(count($dt) > 0){
            $urlPromo = $dt[0]->ws_url;
            $apiName = $dt[0]->cre_name;
            $apiKey = $dt[0]->cre_key;
        }

        $postData = [
            'transaction_id' => $trxid,
            'data' => $transKlik,
        ];

        $strResponse = $this->ConToWebServiceNew($urlPromo, $apiName, $apiKey, $postData);

        //* GET RESPONSE DARI ConToWebServiceNew
        $statusMessage = 'OK';
        $data = [];
        $type = 0;
        $promo_type = 'CASHBACK';
        $strResponse = '';
        $transaction_id = $trxid; //transKlik.transaction_id
        $affected_plu = '';

        $query = '';
        $query .= "INSERT INTO log_hitung_promoklik ( ";
        $query .= " nopb, ";
        $query .= " notrans, ";
        $query .= " url, ";
        $query .= " post_data, ";
        $query .= " response, ";
        $query .= " create_dt ";
        $query .= ") ";
        $query .= "VALUES ( ";
        $query .= " '" . $noPB . "-BARK', ";
        $query .= " '" . $transaction_id . "', ";
        $query .= " '" . $urlPromo . "', ";
        $query .= " '" . $postData . "', ";
        $query .= " '" . $strResponse . " ', ";
        $query .= " NOW() ";
        $query .= ") ";

        if($statusMessage != 'OK'){
            $message = $strResponse;
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        foreach($data as $iPromo){
            $query = '';
            $query .= "UPDATE promo_klikigr SET ";
            if ($flagHitungUlang) {
                if ($iPromo->promo_type === "CASHBACK") {
                    $query .= "cashback_hitungulang = CASE WHEN " . str_replace(",", ".", $iPromo->promo_total) . " > cashback_order THEN cashback_order ELSE " . str_replace(",", ".", $iPromo->promo_total) . " END, ";
                } else {
                    continue;
                }
                $query .= "kelipatan_hitungulang = CASE WHEN " . str_replace(",", ".", $iPromo->promo_qty) . " > kelipatan THEN kelipatan ELSE " . str_replace(",", ".", $iPromo->promo_qty) . " END, ";
                $query .= "reward_per_promo_hitungulang = " . str_replace(",", ".", $iPromo->promo_reward) . ", ";
                $query .= "reward_nominal_hitungulang = CASE WHEN " . str_replace(",", ".", $iPromo->promo_total) . " > reward_nominal THEN reward_nominal ELSE " . str_replace(",", ".", $iPromo->promo_total) . " END ";
            } else {
                if ($iPromo->promo_type === "CASHBACK") {
                    $query .= "cashback_real = CASE WHEN " . str_replace(",", ".", $iPromo->promo_total) . " > cashback_order THEN cashback_order ELSE " . str_replace(",", ".", $iPromo->promo_total) . " END, ";
                } else {
                    $query .= "gift_real = '" . $iPromo->desc . "', ";
                }
                $query .= "kelipatan = CASE WHEN " . str_replace(",", ".", $iPromo->promo_qty) . " > kelipatan THEN kelipatan ELSE " . str_replace(",", ".", $iPromo->promo_qty) . " END, ";
                $query .= "reward_per_promo = " . str_replace(",", ".", $iPromo->promo_reward) . ", ";
                $query .= "reward_nominal = CASE WHEN " . str_replace(",", ".", $iPromo->promo_total) . " > reward_nominal THEN reward_nominal ELSE " . str_replace(",", ".", $iPromo->promo_total) . " END ";
            }

            $query .= "WHERE kode_member = '" . $kdMember . "' ";
            $query .= "AND no_trans = '" . $noTrans . "' ";
            $query .= "AND no_pb = '" . $noPB . "' ";
            $query .= "AND kode_promo = '" . $iPromo->promo_code . "' ";

            if ($iPromo->promo_type === "CASHBACK") {
                $query .= "AND prdcd = '" . $iPromo->affected_plu . "' ";
            }

            DB::update($query);
        }

        $query = '';
        $query .= "UPDATE promo_klikigr ";
        if($flagHitungUlang){
            $query .= "SET cashback_hitungulang = 0, reward_nominal = 0 ";
        }else{
            $query .= "SET cashback_real = 0, reward_nominal = 0 ";
        }
        $query .= "WHERE kode_member = '" . $kdMember . "' ";
        $query .= "  AND no_trans = '" . $noTrans . "' ";
        $query .= "  AND no_pb = '" . $noPB . "' ";
        if($flagHitungUlang){
            $query .= "  AND cashback_hitungulang IS NULL ";
        }else{
            $query .= "  AND cashback_real IS NULL ";
        }
        $query .= "  AND tipe_promo = 'CASHBACK' ";
        $query .= "  AND id_tipe = '0' ";
        $query .= "  AND EXISTS ( ";
        $query .= "      SELECT obi_prdcd ";
        $query .= "      FROM ( ";
        $query .= "        SELECT SUBSTR(obi_prdcd,1,6) || '0' obi_prdcd, ";
        if($flagHitungUlang){
            $query .= "               sum(obi_qty_hitungulang) obi_qtyrealisasi ";
        }else{
            $query .= "               sum(obi_qtyrealisasi - COALESCE(obi_qtyba,0)) obi_qtyrealisasi ";
        }
        $query .= "        FROM tbtr_obi_h h ";
        $query .= "        JOIN tbtr_obi_d d ";
        $query .= "        ON h.obi_notrans = d.obi_notrans ";
        $query .= "        AND h.obi_tgltrans = d.obi_tgltrans ";
        $query .= "        WHERE h.obi_kdmember = '" . $kdMember . "' ";
        $query .= "        AND h.obi_notrans = '" . $noTrans . "' ";
        $query .= "        AND h.obi_nopb = '" . $noPB . "' ";
        $query .= "        GROUP BY SUBSTR(obi_prdcd,1,6) || '0' ";
        if($flagHitungUlang){
            $query .= "        HAVING sum(obi_qty_hitungulang) = 0 ";
        }else{
            $query .= "        HAVING sum(obi_qtyrealisasi - COALESCE(obi_qtyba,0)) = 0 ";
        }
        $query .= "      ) tbtr_obi ";
        $query .= "      WHERE obi_prdcd = prdcd ";
        $query .= "  ) ";
        DB::update($query);

        $query = '';
        $query .= "UPDATE promo_klikigr ";
        if($flagHitungUlang){
            $query .= "   SET cashback_hitungulang = cashback_order, ";
            $query .= "       kelipatan_hitungulang = kelipatan, ";
            $query .= "       reward_per_promo_hitungulang = reward_per_promo, ";
            $query .= "       reward_nominal_hitungulang = reward_nominal ";
        }else{
            $query .= "   SET cashback_real = cashback_order ";
        }
        $query .= "WHERE kode_member = '" . $kdMember . "' ";
        $query .= "  AND no_trans = '" . $noTrans . "' ";
        $query .= "  AND no_pb = '" . $noPB . "' ";
        if($flagHitungUlang){
            $query .= "  AND cashback_hitungulang IS NULL ";
        }else{
            $query .= "  AND cashback_real IS NULL ";
        }
        $query .= "  AND tipe_promo = 'CASHBACK' ";
        $query .= "  AND id_tipe = '1' ";
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

        $query = '';
        $query .= "UPDATE promo_klikigr ";
        if($flagHitungUlang){
            $query .= "   SET kelipatan_hitungulang = ROUND(cashback_hitungulang / reward_per_promo_hitungulang,2) ";
        }else{
            $query .= "   SET kelipatan = ROUND(cashback_real / reward_per_promo,2) ";
        }

        $query .= "WHERE kode_member = '"  . $kdMember . "' ";
        $query .= "  AND no_trans = '"  . $noTrans . "' ";
        $query .= "  AND no_pb = '"  . $noPB . "' ";
        if($flagHitungUlang){
            $query .= "  AND cashback_hitungulang IS NOT NULL ";
        }else{
            $query .= "  AND cashback_real IS NOT NULL ";
        }
        $query .= "  AND tipe_promo = 'CASHBACK' ";
        DB::update($query);
    }

    private function insertMstranH($nodoc, $noret){

        $KodeIGR = session('KODECABANG');
        $UserMODUL = session('userid');

        $tglStruk = $this->tglStruk;
        $cashierID = $this->cashierID;
        $cashierStation = $this->cashierStation;
        $noStruk = $this->noStruk;

        $query = '';
        $query .= "INSERT INTO tbtr_mstran_h ( ";
        $query .= "  msth_kodeigr, ";
        $query .= "  msth_typetrn, ";
        $query .= "  msth_nodoc, ";
        $query .= "  msth_tgldoc, ";
        $query .= "  msth_nopo, ";
        $query .= "  msth_tglpo, ";
        $query .= "  msth_nofaktur, ";
        $query .= "  msth_tglfaktur, ";
        $query .= "  msth_kodesupplier, ";
        $query .= "  msth_pkp, ";
        $query .= "  msth_keterangan_header, ";
        $query .= "  msth_flagdoc, ";
        $query .= "  msth_create_by, ";
        $query .= "  msth_create_dt, ";
        $query .= "  msth_cterm ";
        $query .= ") ";
        $query .= "VALUES (  ";
        $query .= "  '" . $KodeIGR . "' , ";
        $query .= "  'Z', ";
        $query .= "  '" . $nodoc . "', ";
        $query .= "  CURRENT_DATE, ";
        $query .= "  '" . $noret . "',  ";
        $query .= "  CURRENT_DATE,  ";
        $query .= "  '" . $tglStruk . $cashierID . $cashierStation . $noStruk . "',  ";
        $query .= "  TO_DATE('" . $tglStruk . "', 'YYYYMMDD'),  ";
        $query .= "  '', ";
        $query .= "  '', ";
        $query .= "  'RETUR BARANG RUSAK SPI', ";
        $query .= "  '1', ";
        $query .= "  '" . $UserMODUL . "', ";
        $query .= "  NOW(), ";
        $query .= "  0 ";
        $query .= ") ";
        DB::insert($query);

        return true;
    }

    //! ADA NOTE KEVIN
    private function insertVCHRetur($nodoc, $noret, $total){

        //! NOTE KEVIN
        //? cari variable ini darimana
        //* txtAlasan2 kalo dari vb dari txtAlasan2.text
        $txtAlasan2 = '';
        $kdmember = '';

        $UserMODUL = session('userid');
        $KodeIGR = session('KODECABANG');
        $StationMODUL = session("SPI_STATION");


        $total = round($total / 10) * 10;

        $query = '';
        $query .= " INSERT INTO TBTR_VCH_RETUR (  ";
        $query .= "   VCRT_KODEIGR,  ";
        $query .= "   VCRT_TRANSACTIONNO,  ";
        $query .= "   VCRT_TRANSACTIONDATE,  ";
        $query .= "   VCRT_CASHIERID,  ";
        $query .= "   VCRT_STATION,  ";
        $query .= "   VCRT_KODEMEMBER,  ";
        $query .= "   VCRT_TGLAWAL,  ";
        $query .= "   VCRT_TGLAKHIR,  ";
        $query .= "   VCRT_NOMINAL,  ";
        $query .= "   VCRT_FLAGUSED,  ";
        $query .= "   VCRT_KETERANGAN,  ";
        $query .= "   VCRT_CREATE_BY,  ";
        $query .= "   VCRT_CREATE_DT  ";
        $query .= " )  ";
        $query .= " VALUES (  ";
        $query .= "   '" . $KodeIGR . "',  ";
        $query .= "   '" . $noret . "',  ";
        $query .= "   CURRENT_DATE,  ";
        $query .= "   '" . $UserMODUL . "',  ";
        $query .= "   '" . $StationMODUL . "',  ";
        $query .= "   '" . $kdmember . "',  ";
        $query .= "   CURRENT_DATE,  ";
        $query .= "   CURRENT_DATE,  ";
        $query .= "   '" . str_replace(",", ".", $total) . "',  ";
        $query .= "   'N',  ";
        $query .= "   '" . substr(str_replace("'", "", $txtAlasan2), 0, 100) . "',  ";
        $query .= "   '" . $UserMODUL . "',  ";
        $query .= "   NOW() ";
        $query .= " )  ";
        DB::insert($query);

        return true;
    }

    private function sendNotif_SPIKLIK($type, $nopb, $notif){

        $ws_nama = $type == 'SPI' ? 'IPP_SPI' : 'IPP_KLIK';

        //* GET API IPP x SPI
        $dt = DB::select("SELECT ws_url FROM tbmaster_webservice WHERE ws_nama = $ws_nama");
        if(count($dt) == 0 || $dt[0]->ws_url == ''){
            $message = 'API IPP SPI tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        //* GET CREDENTIAL API IPP x SPI
        $dt = DB::select("SELECT cre_name, cre_key FROM tbmaster_credential WHERE cre_type = $ws_nama");
        if(count($dt) == 0){
            $message = 'CREDENTIAL API IPP x SPI tidak ditemukan';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $apiName = $dt[0]->cre_name;
        $apiKey = $dt[0]->cre_key;

        $splitTrans = explode("/", $nopb);
        $trxid = $splitTrans[0];
        $newTrxid = "A" . $trxid;

        //* HIT API RE-CREATE AWB
        $urlSPI = '/recreateawb';

        $postData = [
            'trxid' => $trxid,
            'message' => $notif,
        ];

        $strResponse = $this->ConToWebServiceNew($urlSPI, $apiName, $apiKey, $postData);

        //! GET RESPONSE DARI ConToWebServiceNew
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
        $query .= " '" . $nopb . "', ";
        $query .= " '" . $newTrxid . "', ";
        $query .= " '" . $urlSPI . "', ";
        $query .= " '" . $strPostData . "', ";
        $query .= " '" . $strResponse . "', ";
        $query .= " NOW() ";
        $query .= ") ";
        DB::insert($query);

        return true;
    }

    //! NOTE KEVIN
    //? memang di vb function kosong
    private function CetakBARK(){

    }

    //! NOTE KEVIN
    //? memang di vb function kosong
    private function CetakBPBR(){

    }


    private function getDataStruk($nopb,$notrans,$kdmember){
        $query = '';
        $query .= "SELECT  ";
        $query .= "  TO_CHAR(h.obi_tglstruk,'YYYYMMDD') tglstruk, ";
        $query .= "  UPPER(h.obi_cashierid) cashierid, ";
        $query .= "  UPPER(h.obi_kdstation) station, ";
        $query .= "  h.obi_nostruk nostruk ";
        $query .= "FROM tbtr_obi_h h ";
        $query .= "WHERE h.obi_nopb = '" . $nopb . "' ";
        $query .= "AND h.obi_notrans = '" . $notrans . "' ";
        $query .= "AND h.obi_kdmember = '" . $kdmember . "' ";
        $query .= "AND h.obi_tglstruk IS NOT NULL ";
        $query .= "AND h.obi_cashierid IS NOT NULL ";
        $query .= "AND h.obi_kdstation IS NOT NULL ";
        $query .= "AND h.obi_nostruk IS NOT NULL ";
        $dtStruk = DB::select($query);


        if(count($dtStruk) == 0){
            $message = 'Data Struk Tidak Ditemukan!';
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        $this->tglStruk = $dtStruk[0]->tglstruk;
        $this->cashierID = $dtStruk[0]->cashierid;
        $this->cashierStation = $dtStruk[0]->station;
        $this->noStruk = $dtStruk[0]->nostruk;

        return true;
    }

    private function GetSeqNodoc(){
        $data = DB::select("SELECT NEXTVAL('IGR_RET_DISTRIBUSI')");
        return $data[0]->nextval;
    }
}
