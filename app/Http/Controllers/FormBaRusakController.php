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

class FormBaRusakController extends KlikIgrController
{
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
                $this->prosesDSPUlang_COD();
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

    //! NOTE KEVIN
    //? tinggal di benerin query dan paramnya
    private function prosesBA_COD(){
        //* UPDATE TBTR_OBI_D - OBI_QTYREALISASI
        sb = New StringBuilder
        sb.AppendLine(" UPDATE tbtr_obi_d ")
        sb.AppendLine(" SET obi_qtyrealisasi = obi_qtyrealisasi - " & qty & " ")
        sb.AppendLine(" WHERE obi_tgltrans = TO_DATE('" & tgltrans & "','DD-MM-YYYY') ")
        sb.AppendLine(" AND obi_notrans = '" & notrans & "' ")
        sb.AppendLine(" AND obi_prdcd = '" & plu & "' ")

        //* UPDATE TBTR_PACKING_OBI - POBI_QTY
        sb = New StringBuilder
        sb.AppendLine(" UPDATE tbtr_packing_obi ")
        sb.AppendLine(" SET pobi_qty = pobi_qty - " & qty & " ")
        sb.AppendLine(" WHERE DATE_TRUNC('DAY',pobi_tgltransaksi) = TO_DATE('" & tgltrans & "','DD-MM-YYYY') ")
        sb.AppendLine(" AND pobi_notransaksi = '" & notrans & "' ")
        sb.AppendLine(" AND pobi_prdcd = '" & plu & "' ")

        return true;
    }

    //! NOTE KEVIN
    //? tinggal di benerin query dan paramnya
    private function prosesBPBR(){
        sb.AppendLine("SELECT DISTINCT ")
        sb.AppendLine("  trjd_flagtax1 tax1, ")
        sb.AppendLine("  trjd_flagtax2 tax2, ")
        sb.AppendLine("  " & qty & " qty, ")
        sb.AppendLine("  Coalesce(ROUND(st_avgcost::INT,2),0) avgCost, ")
        sb.AppendLine("  Coalesce(ROUND(st_avgcost::INT,2),0) * " & qty & " ttlCost, ")
        sb.AppendLine("  Coalesce(ROUND(trjd_unitprice,2),0) price, ")
        sb.AppendLine("  Coalesce(ROUND(trjd_unitprice,2),0) * " & qty & " ttlPrice, ")
        sb.AppendLine("  prd_ppn persenPpn ")
        sb.AppendLine("FROM tbtr_obi_h h ")
        sb.AppendLine("JOIN tbtr_obi_d d ")
        sb.AppendLine("ON d.obi_notrans = h.obi_notrans ")
        sb.AppendLine("AND d.obi_tgltrans = h.obi_tgltrans ")
        sb.AppendLine("JOIN tbmaster_prodmast ")
        sb.AppendLine("ON prd_prdcd = d.obi_prdcd ")
        sb.AppendLine("JOIN tbtr_jualdetail ")
        sb.AppendLine("ON trjd_transactionno = h.obi_nostruk ")
        sb.AppendLine("AND DATE_TRUNC('DAY',trjd_transactiondate) = DATE_TRUNC('DAY',h.obi_tglstruk) ")
        sb.AppendLine("AND trjd_cus_kodemember = h.obi_kdmember ")
        sb.AppendLine("AND trjd_cashierstation = h.obi_kdstation ")
        sb.AppendLine("AND trjd_prdcd = d.obi_prdcd ")
        sb.AppendLine("AND trjd_transactiontype = 'S' ")
        sb.AppendLine("JOIN tbmaster_stock ")
        sb.AppendLine("ON st_lokasi = '01' ")
        sb.AppendLine("AND st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("WHERE h.obi_nopb = '" & nopb & "' ")
        sb.AppendLine("AND h.obi_notrans = '" & notrans & "' ")
        sb.AppendLine("AND h.obi_kdmember = '" & kdmember & "' ")
        sb.AppendLine("AND d.obi_prdcd = '" & plu & "' ")
        sb.AppendLine("AND d.obi_recid IS NULL ")
        $dtStruk = DB::select($query);

        if(count($query) == 0){
            $message = "Data Struk Item " . $plu . " tidak ditemukan.";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
        }

        sb.AppendLine("INSERT INTO TBTR_RETUROMI ( ")
        sb.AppendLine("  ROM_KODEIGR,  ")
        sb.AppendLine("  ROM_RECORDID,  ")
        sb.AppendLine("  ROM_NODOKUMEN,  ")
        sb.AppendLine("  ROM_TGLDOKUMEN,  ")
        sb.AppendLine("  ROM_REFERENSISTRUK,  ")
        sb.AppendLine("  ROM_MEMBER,  ")
        sb.AppendLine("  ROM_KODEKASIR,  ")
        sb.AppendLine("  ROM_STATION,  ")
        sb.AppendLine("  ROM_PRDCD,  ")
        sb.AppendLine("  ROM_FLAGBKP,  ")
        sb.AppendLine("  ROM_FLAGBKP2,  ")
        sb.AppendLine("  ROM_QTY,  ")
        sb.AppendLine("  ROM_QTYREALISASI,  ")
        sb.AppendLine("  ROM_QTYSELISIH,  ")
        sb.AppendLine("  ROM_HRGSATUAN,  ")
        sb.AppendLine("  ROM_AVGCOST,  ")
        sb.AppendLine("  ROM_TTLNILAI,  ")
        sb.AppendLine("  ROM_TTLCOST,  ")
        sb.AppendLine("  ROM_HRG,  ")
        sb.AppendLine("  ROM_TTL,  ")
        sb.AppendLine("  ROM_PERSENPPN,  ")
        sb.AppendLine("  ROM_CREATE_BY,  ")
        sb.AppendLine("  ROM_CREATE_DT  ")
        sb.AppendLine(")  ")
        sb.AppendLine("VALUES (  ")
        sb.AppendLine("  '" & KodeIGR & "',  ")
        sb.AppendLine("  '2',  ")
        sb.AppendLine("  '" & noret & "',  ")
        sb.AppendLine("  CURRENT_DATE,  ")
        sb.AppendLine("  '" & tglStruk _
                            & cashierID _
                            & cashierStation _
                            & noStruk & "',  ")
        sb.AppendLine("  '" & kdmember & "',  ")
        sb.AppendLine("  '" & UserMODUL & "',  ")
        sb.AppendLine("  '" & StationMODUL & "',  ")
        sb.AppendLine("  '" & plu & "',  ")
        sb.AppendLine("  '" & dtStruk.Rows(0).Item("tax1").ToString & "',  ")
        sb.AppendLine("  '" & dtStruk.Rows(0).Item("tax2").ToString & "',  ")
        sb.AppendLine("  '" & qty.ToString.Replace(",", ".") & "',  ")
        sb.AppendLine("  '" & qty.ToString.Replace(",", ".") & "',  ")
        sb.AppendLine("  '0',  ")
        sb.AppendLine("  '0',  ")
        sb.AppendLine("  '" & dtStruk.Rows(0).Item("avgCost").ToString.Replace(",", ".") & "',  ")
        sb.AppendLine("  '0',  ")
        sb.AppendLine("  '" & dtStruk.Rows(0).Item("ttlCost").ToString.Replace(",", ".") & "',  ")
        sb.AppendLine("  '" & dtStruk.Rows(0).Item("price").ToString.Replace(",", ".") & "',  ")
        sb.AppendLine("  '" & dtStruk.Rows(0).Item("ttlPrice").ToString.Replace(",", ".") & "',  ")
        sb.AppendLine("  '" & dtStruk.Rows(0).Item("persenPpn").ToString.Replace(",", ".") & "',  ")
        sb.AppendLine("  '" & UserMODUL & "',  ")
        sb.AppendLine("  NOW() ")
        sb.AppendLine(")  ")

        return $true;
    }

    //! NOTE KEVIN
    //? PROSES BANYAK DILANJUT NANTI
    private function insertMstranD(){
        sb.AppendLine("SELECT DISTINCT ")
        sb.AppendLine("  d.obi_prdcd plu, ")
        sb.AppendLine("  hgb_statusbarang statusbarang, ")
        sb.AppendLine("  hgb_kodesupplier kodeSup, ")
        sb.AppendLine("  sup_pkp pkpSup, ")
        sb.AppendLine("  prd_kodedivisi kodedivisi, ")
        sb.AppendLine("  prd_kodedepartement kodedepartement, ")
        sb.AppendLine("  prd_kodekategoribarang kodekategoribarang, ")
        sb.AppendLine("  prd_flagbkp1 flagbkp1, ")
        sb.AppendLine("  prd_flagbkp2 flagbkp2, ")
        sb.AppendLine("  prd_unit unit, ")
        sb.AppendLine("  prd_frac frac, ")
        sb.AppendLine("  " & qty & " qty, ")
        sb.AppendLine("  COALESCE(ROUND(st_avgcost::int,2),0) avgCost, ")
        sb.AppendLine("  COALESCE(ROUND(st_lastcost::int,2),0) lastCost, ")
        sb.AppendLine("  COALESCE(ROUND(st_avgcost::int,2),0) * " & qty & " ttlCost, ")
        sb.AppendLine("  COALESCE(trjd_unitprice,0) hrgPrice, ")
        sb.AppendLine("  COALESCE(trjd_unitprice,0) * " & qty & " ttlPrice, ")
        sb.AppendLine("  COALESCE(st_saldoakhir,0) saldoakhir, ")
        sb.AppendLine("  prd_ppn persenPpn ")
        sb.AppendLine("FROM tbtr_obi_h h ")
        sb.AppendLine("JOIN tbtr_obi_d d ")
        sb.AppendLine(" ON  d.obi_notrans = h.obi_notrans ")
        sb.AppendLine(" AND d.obi_tgltrans = h.obi_tgltrans ")
        sb.AppendLine("JOIN tbmaster_prodmast ")
        sb.AppendLine(" ON  prd_prdcd = d.obi_prdcd ")
        sb.AppendLine("JOIN tbtr_jualdetail ")
        sb.AppendLine(" ON  trjd_transactionno = h.obi_nostruk ")
        sb.AppendLine(" AND DATE_TRUNC('DAY',trjd_transactiondate) = DATE_TRUNC('DAY',h.obi_tglstruk) ")
        sb.AppendLine(" AND trjd_cus_kodemember = h.obi_kdmember ")
        sb.AppendLine(" AND trjd_cashierstation = h.obi_kdstation ")
        sb.AppendLine(" AND trjd_prdcd = d.obi_prdcd ")
        sb.AppendLine(" AND trjd_transactiontype = 'S' ")
        sb.AppendLine("LEFT JOIN tbmaster_stock ")
        sb.AppendLine(" ON  st_lokasi = '01' ")
        sb.AppendLine(" AND st_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("JOIN tbmaster_hargabeli ")
        sb.AppendLine(" ON  hgb_tipe = '2' ")
        sb.AppendLine(" AND hgb_prdcd = SUBSTR(d.obi_prdcd,1,6) || '0' ")
        sb.AppendLine("JOIN tbmaster_supplier ")
        sb.AppendLine(" ON  sup_kodesupplier = hgb_kodesupplier ")
        sb.AppendLine("WHERE h.obi_nopb = '" & nopb & "' ")
        sb.AppendLine(" AND h.obi_notrans = '" & notrans & "' ")
        sb.AppendLine(" AND h.obi_kdmember = '" & kdmember & "' ")
        sb.AppendLine(" AND d.obi_prdcd = '" & plu & "' ")
        sb.AppendLine(" AND d.obi_recid IS NULL ")
    }

    //! NOTE KEVIN
    //? tinggal di benerin query dan paramnya
    private function prosesDSPUlang_COD(){
        if($tipeBayar == 'COD'){
            sb.AppendLine("UPDATE promo_klikigr ")
            sb.AppendLine("   SET cashback_real = NULL ")
            sb.AppendLine("WHERE kode_member = '" & kdmember & "' ")
            sb.AppendLine("  AND no_trans = '" & notrans & "' ")
            sb.AppendLine("  AND no_pb = '" & nopb & "' ")

            sb.AppendLine("SELECT kode_promo ")
            sb.AppendLine("FROM promo_klikigr ")
            sb.AppendLine("WHERE kode_member = '" & kdmember & "' ")
            sb.AppendLine("  AND no_trans = '" & notrans & "' ")
            sb.AppendLine("  AND no_pb = '" & nopb & "' ")
            $dt = DB::select($query);

            if(count($dt) > 0){
                sb.AppendLine("SELECT SUBSTR(obi_prdcd,1,6) || '0' PLU, ")
                sb.AppendLine("       SUM(coalesce(obi_qtyorder,0)) orderr, ")
                sb.AppendLine("       SUM(coalesce(obi_qtyrealisasi,0) - coalesce(obi_qtyba,0)) realisasi ")
                sb.AppendLine("  FROM tbtr_obi_h h ")
                sb.AppendLine("  JOIN tbtr_obi_d d ")
                sb.AppendLine("    ON h.obi_notrans = d.obi_notrans ")
                sb.AppendLine("   AND h.obi_tgltrans = d.obi_tgltrans ")
                sb.AppendLine(" WHERE h.obi_kdmember = '" & kdmember & "' ")
                sb.AppendLine("   AND h.obi_notrans = '" & notrans & "' ")
                sb.AppendLine("   AND h.obi_nopb = '" & nopb & "' ")
                sb.AppendLine("   AND d.obi_recid IS NULL")
                sb.AppendLine("   AND d.obi_qtyorder <> d.obi_qtyrealisasi - coalesce(d.obi_qtyba,0) ")
                sb.AppendLine(" GROUP BY SUBSTR(obi_prdcd,1,6) || '0' ")
                sb.AppendLine(" ORDER BY 1 ")
                $dt = DB::select($query);

                if(count($dt) > 0){
                    //* KALAU ADA YANG SELISIH HITUNG ULANG
                    // Split the string based on the "/" delimiter
                    $splitTrans = explode("/", $nopb);

                    // Access the first element of the resulting array
                    $notrx = $splitTrans[0];

                    sb.AppendLine("SELECT SUBSTR(obi_prdcd,1,6) || '0' PLU, ")
                    sb.AppendLine("       SUM(coalesce(obi_qtyorder,0)) orderr, ")
                    sb.AppendLine("       SUM(coalesce(obi_qtyrealisasi,0) - coalesce(d.obi_qtyba,0)) realisasi, ")
                    sb.AppendLine("       SUM( ")
                    sb.AppendLine("         ROUND(d.obi_hargaweb * COALESCE(obi_qtyorder,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ")
                    sb.AppendLine("         - ROUND(d.obi_diskon * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * COALESCE(obi_qtyorder,0) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END),-1) ")
                    sb.AppendLine("       ) orderinrp, ")
                    sb.AppendLine("       SUM( ")
                    sb.AppendLine("         ROUND(d.obi_hargaweb * (coalesce(obi_qtyrealisasi,0) - coalesce(d.obi_qtyba,0)) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END), -1) ")
                    sb.AppendLine("         - ROUND(d.obi_diskon * (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END) * (coalesce(obi_qtyrealisasi,0) - coalesce(d.obi_qtyba,0)) / (CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END),-1) ")
                    sb.AppendLine("       ) realisasiiinrp ")
                    sb.AppendLine("  FROM tbtr_obi_h h ")
                    sb.AppendLine("  JOIN tbtr_obi_d d ")
                    sb.AppendLine("    ON h.obi_notrans = d.obi_notrans ")
                    sb.AppendLine("   AND h.obi_tgltrans = d.obi_tgltrans ")
                    sb.AppendLine("  JOIN tbmaster_prodmast p ON p.prd_prdcd = d.obi_prdcd ")
                    sb.AppendLine(" WHERE h.obi_kdmember = '" & kdmember & "' ")
                    sb.AppendLine("   AND h.obi_notrans = '" & notrans & "' ")
                    sb.AppendLine("   AND h.obi_nopb = '" & nopb & "' ")
                    sb.AppendLine("   AND d.obi_recid IS NULL")
                    sb.AppendLine(" GROUP BY SUBSTR(obi_prdcd,1,6) || '0' ")
                    sb.AppendLine(" ORDER BY 1 ")
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

                    $api = $this->requestPromoBARK($transKlik, $kdMember, $noTrans, $noPB);
                    if($api != true){
                        $message = 'Gagal Hitung Ulang Promosi';
                        throw new HttpResponseException(ApiFormatter::error(400, $message));
                    }

                }else{
                    //* KALAU TIDAK ADA YANG SELISIH FULL SEMUA
                    sb.AppendLine("UPDATE promo_klikigr ")
                    sb.AppendLine("   SET cashback_real = cashback_order ")
                    sb.AppendLine("WHERE kode_member = '" & kdmember & "' ")
                    sb.AppendLine("  AND no_trans = '" & notrans & "' ")
                    sb.AppendLine("  AND no_pb = '" & nopb & "' ")
                    sb.AppendLine("  AND tipe_promo = 'CASHBACK' ")
                }
            }
        }

        //* Ulang DSPB
        sb.AppendLine("UPDATE tbtr_obi_h  ")
        sb.AppendLine("SET obi_realorder = 0, ")
        sb.AppendLine("    obi_realppn = 0, ")
        sb.AppendLine("    obi_realdiskon = 0, ")
        sb.AppendLine("	   obi_realitem = 0, ")
        sb.AppendLine("	   obi_recid = CASE WHEN obi_flagbayar = 'Y' THEN '3' ELSE CASE WHEN obi_freeongkir = 'T' THEN '3' ELSE '7' END END, ")
        sb.AppendLine("	   obi_ekspedisi = CASE WHEN obi_flagbayar = 'Y' THEN obi_ekspedisi ELSE 0 END, ")
        sb.AppendLine("	   obi_zona = '1', ")
        sb.AppendLine("	   obi_kdekspedisi = CASE WHEN obi_flagbayar = 'Y' THEN obi_kdekspedisi ELSE null END, ")
        sb.AppendLine("	   obi_realcashback = 0 ")
        sb.AppendLine("WHERE obi_notrans = '" & notrans & "' ")
        sb.AppendLine("AND obi_nopb =  '" & nopb & "' ")

        //* BATALIN DSP SPI
        sb.AppendLine("DELETE FROM tbtr_dsp_spi ")
        sb.AppendLine("WHERE dsp_kodemember = '" & kdmember & "' ")
        sb.AppendLine("  AND dsp_notrans = '" & notrans & "' ")
        sb.AppendLine("  AND dsp_nopb = '" & nopb & "' ")

        //* UPDATE ULANG INTRANSIT
        $this->updateIntransit(False, $notrans, $tgltrans);

        $userMODUL = session('userid');
        $KodeIGR = session('KODECABANG');
        $procedure = DB::select("call sp_create_draftstrukobi ('$nopb','$tgltrans','$notrans', '$userMODUL', '$KodeIGR', '')");
        $procedure = $procedure[0]->p_status;

        if (str_contains($procedure, 'Sukses!')) {
            sb.AppendLine(" UPDATE TBTR_OBI_H SET OBI_RECID = '5'  ")
            sb.AppendLine(" Where OBI_NOPB = '" & nopb & "'  ")
            sb.AppendLine(" AND OBI_KDMEMBER = '" & kdmember & "'  ")
            sb.AppendLine(" AND OBI_NOTRANS = '" & notrans & "'  ")
            sb.AppendLine(" AND OBI_RECID = '4'  ")
        }
    }

    //! NOTE KEVIN
    //? ada proses hit API
    //? ini masih dimintakan json yang dikirim ke API
    private function requestPromoBARK(){
        $flagProrate = true;

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

        //! GET RESPONSE DARI ConToWebServiceNew
        $statusMessage = 'OK';
        $data = [];
        $type = 0;
        $promo_type = 'CASHBACK';
        $strResponse = '';
        $transaction_id = $trxid; //transKlik.transaction_id
        $affected_plu = '';
    }

    //! NOTE KEVIN
    //? tinggal di benerin query dan paramnya
    private function insertMstranH(){
        sb.AppendLine("INSERT INTO tbtr_mstran_h ( ")
        sb.AppendLine("  msth_kodeigr, ")
        sb.AppendLine("  msth_typetrn, ")
        sb.AppendLine("  msth_nodoc, ")
        sb.AppendLine("  msth_tgldoc, ")
        sb.AppendLine("  msth_nopo, ")
        sb.AppendLine("  msth_tglpo, ")
        sb.AppendLine("  msth_nofaktur, ")
        sb.AppendLine("  msth_tglfaktur, ")
        sb.AppendLine("  msth_kodesupplier, ")
        sb.AppendLine("  msth_pkp, ")
        sb.AppendLine("  msth_keterangan_header, ")
        sb.AppendLine("  msth_flagdoc, ")
        sb.AppendLine("  msth_create_by, ")
        sb.AppendLine("  msth_create_dt, ")
        sb.AppendLine("  msth_cterm ")
        sb.AppendLine(") ")
        sb.AppendLine("VALUES (  ")
        sb.AppendLine("  '" & KodeIGR & "' , ")
        sb.AppendLine("  'Z', ")
        sb.AppendLine("  '" & nodoc & "', ")
        sb.AppendLine("  CURRENT_DATE, ")
        sb.AppendLine("  '" & noret & "',  ")
        sb.AppendLine("  CURRENT_DATE,  ")
        sb.AppendLine("  '" & tglStruk & cashierID & cashierStation & noStruk & "',  ")
        sb.AppendLine("  TO_DATE('" & tglStruk & "', 'YYYYMMDD'),  ")
        sb.AppendLine("  '', ")
        sb.AppendLine("  '', ")
        sb.AppendLine("  'RETUR BARANG RUSAK SPI', ")
        sb.AppendLine("  '1', ")
        sb.AppendLine("  '" & UserMODUL & "', ")
        sb.AppendLine("  NOW(), ")
        sb.AppendLine("  0 ")
        sb.AppendLine(") ")

        return true;
    }

    //! NOTE KEVIN
    //? tinggal di benerin query dan paramnya
    private function insertVCHRetur(){
        $total = round($total / 10) * 10;

        sb.AppendLine(" INSERT INTO TBTR_VCH_RETUR (  ")
        sb.AppendLine("   VCRT_KODEIGR,  ")
        sb.AppendLine("   VCRT_TRANSACTIONNO,  ")
        sb.AppendLine("   VCRT_TRANSACTIONDATE,  ")
        sb.AppendLine("   VCRT_CASHIERID,  ")
        sb.AppendLine("   VCRT_STATION,  ")
        sb.AppendLine("   VCRT_KODEMEMBER,  ")
        sb.AppendLine("   VCRT_TGLAWAL,  ")
        sb.AppendLine("   VCRT_TGLAKHIR,  ")
        sb.AppendLine("   VCRT_NOMINAL,  ")
        sb.AppendLine("   VCRT_FLAGUSED,  ")
        sb.AppendLine("   VCRT_KETERANGAN,  ")
        sb.AppendLine("   VCRT_CREATE_BY,  ")
        sb.AppendLine("   VCRT_CREATE_DT  ")
        sb.AppendLine(" )  ")
        sb.AppendLine(" VALUES (  ")
        sb.AppendLine("   '" & KodeIGR & "',  ")
        sb.AppendLine("   '" & noret & "',  ")
        sb.AppendLine("   CURRENT_DATE,  ")
        sb.AppendLine("   '" & UserMODUL & "',  ")
        sb.AppendLine("   '" & StationMODUL & "',  ")
        sb.AppendLine("   '" & kdmember & "',  ")
        sb.AppendLine("   CURRENT_DATE,  ")
        sb.AppendLine("   CURRENT_DATE,  ")
        sb.AppendLine("   '" & total.ToString.Replace(",", ".") & "',  ")
        sb.AppendLine("   'N',  ")
        sb.AppendLine("   '" & Strings.Left(txtAlasan2.Text.Replace("'", ""), 100) & "',  ")
        sb.AppendLine("   '" & UserMODUL & "',  ")
        sb.AppendLine("   NOW() ")
        sb.AppendLine(" )  ")

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
        $query .= " '" . $noPB . "', ";
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

        $tglStruk = $dtStruk[0]->tglstruk;
        $cashierID = $dtStruk[0]->cashierid;
        $cashierStation = $dtStruk[0]->station;
        $noStruk = $dtStruk[0]->nostruk;

        return true;
    }

    private function GetSeqNodoc(){
        $data = DB::select("SELECT NEXTVAL('IGR_RET_DISTRIBUSI')");
        return $data[0]->nextval;
    }
}
