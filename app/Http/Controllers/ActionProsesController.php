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

class ActionProsesController extends KlikIgrController
{
    public function listPB(){

        $_minJual = $ttlitem = $n = $frac = $ctr = $PLUtolak = $qty = $price = $dpp = $diskon = $ppn = $ekspedisi = $ttlorder = $ttlppn = $ttldiskon = $jarak = $getNilaiPPN = $qtyNoFrac = $priceNoFrac = 0;
        $hargajual = $potMD = $potMKT = $cshbck = $ttlcshbck = $ttlHargaTnpPajak = $ttlHargaKnaPajak = $dppHargaKnaPajak = $ppnHargaKnaPajak = $pluSelisih = 0;
        $trnId = $zona = $kdmember = $nopb = $NamaFile = $FullPath = $kdAlamat = $kdWeb = $namaPenerima = $namaWP = $npwp = $alamatWP = $email = $noHp = "";
        $expiredDate = $OraTrans = $nopo = $kodePromosi = $strQuery = $satuanJual = $strSelisih = $str = "";
        $flagOk = false;
        $flagHeader = false;
        $flagCor = false;
        $flagMember = true;
        $alreadyShow = false;
        $strArr = array();
        $count = 0;

        $zipFile = [];
        $KodeIGR = session('KODECABANG');
        $UserMODUL = session('userid');

        $ctr = 0;
        $tglnow = Carbon::parse(DB::select("select DATE_TRUNC('DAY',CURRENT_DATE)")[0]->date_trunc)->format('Y-m-d H:i:s');
        $tglNowDateTime = $tglnow;

        foreach($zipFile as $item){

            $PLUtolak = 0;
            $NamaFile = ''; //! dapet dari nama filenya

            if(strlen($NamaFile) == 28){
                if (substr($NamaFile, 0, 6) == "PB_" . $KodeIGR . "_" && (strtoupper(substr($NamaFile, 13, 3)) == "ODR" || strtoupper(substr($NamaFile, 13, 3)) == "COR" || strtoupper(substr($NamaFile, 13, 3)) == "OMM")){

                    $NamaFile = '';

                    $ctr++;

                    $datas = []; //! data didalam filenya

                    if(count($datas) == 11 OR count($datas) == 15 OR count($datas) == 17 OR count($datas) == 18){
                        $kdmember = "";
                        $nopb = "";
                        $kdAlamat = "";
                        $tglpb = null;
                        $flagOk = False;
                        $n = 0;
                        $zona = "1";
                        $trnId = "00000";
                        $_minJual = 1;
                        $nopo = "";
                        $kdWeb = "";
                        $strQuery = "";
                        $ttlitem = 0;
                        $flagMember = True;
                        $namaPenerima = "";
                        $namaWP = "";
                        $npwp = "";
                        $alamatWP = "";
                        $email = "";
                        $noHp = "";

                        foreach($datas as $key => $data){
                            $hargajual = 0;
                            $potMD = 0;
                            $potMKT = 0;
                            $kodePromosi = "";

                            if (str_contains($data['No Pemesanan'], 'omm')) {
                                $kdWeb = 'WebMM';
                            }else{
                                $kdWeb = 'KlikIGR';
                                $flagCor = false;

                                $cekCor = explode("/", $data["No Pemesanan"]);
                                if (strtoupper($cekCor[1]) == "COR") {
                                    $flagCor = true;
                                    $kdWeb = "Corp";
                                }
                            }

                            $str = $data["No Anggota"]->ToString();
                            $strArr = explode("/", $str);

                            if($kdWeb != 'WebMM'){
                                if($kdmember == $strArr[0] AND $tglpb == $data['Tgl Pemesanan'] AND $nopb == $data['No Pemesanan'] AND $kdAlamat == $data['Kode Alamat']){
                                    $n += 1;

                                    goto SelainWebMM1;
                                }
                            }

                            $kdmember = $strArr[0];
                            $tglpb = date_create($data["Tgl Pemesanan"]);
                            $nopb = $data["No Pemesanan"]->ToString();
                            $kdAlamat = $data["Kode Alamat"]->ToString();
                            $nopo = $data["No PO"]->ToString();
                            $zona = "1";

                            if (count($datas) == 15) {
                                $namaPenerima = $data["Nama Penerima"]->ToString();
                                $namaWP = $data["Nama WP"]->ToString();
                                $npwp = $data["NPWP"]->ToString();
                                $alamatWP = $data["Alamat WP"]->ToString();
                            }

                            if (count($datas) == 17) {
                                $namaPenerima = $data["Nama Penerima"]->ToString();
                                $namaWP = $data["Nama WP"]->ToString();
                                $npwp = $data["NPWP"]->ToString();
                                $alamatWP = $data["Alamat WP"]->ToString();
                                $email = $data["Email"]->ToString();
                                $noHp = $data["No Hp"]->ToString();
                            }

                            if (count($datas) == 18) {
                                $namaPenerima = $data["Nama Penerima"]->ToString();
                                $namaWP = $data["Nama WP"]->ToString();
                                $npwp = $data["NPWP"]->ToString();
                                $alamatWP = $data["Alamat WP"]->ToString();
                                $email = $data["Email"]->ToString();
                                $arrJarak = explode(" ", $data["Jarak"]->ToString());
                                $jarak = $arrJarak[0] == "" ? 0 : $arrJarak[0];
                            }

                            //* CHECK CUSTOMER
                            $cek = DB::table('tbmaster_customer')
                                ->where('cus_kodemember', $kdmember)
                                ->count();

                            if($cek == 0){
                                $message = "Member : $kdmember Tidak Terdaftar di Database!";
                                throw new HttpResponseException(ApiFormatter::error(400, $message));
                            }

                            $query = '';
                            $query .= "SELECT COUNT(1) ";
                            $query .= "FROM TBTR_OBI_H H,TBTR_OBI_D D ";
                            $query .= "WHERE H.OBI_TGLTRANS = D.OBI_TGLTRANS ";
                            $query .= "AND H.OBI_NOTRANS = D.OBI_NOTRANS  ";
                            $query .= "AND OBI_NOPB = '" . $nopb . "' ";
                            $query .= "AND DATE_TRUNC('DAY',OBI_TGLPB) = '" . $tglpb . "' ";
                            $query .= "AND OBI_KDMEMBER = '" . $kdmember . "' ";
                            $cek = DB::select($query)[0]->count;

                            if($cek > 0){
                                $message = "Member : $kdmember, NoPB : $nopb Sudah Ada Di Database!";
                                throw new HttpResponseException(ApiFormatter::error(400, $message));
                            }else{
                                if($trnId == '00000'){
                                    $query = DB::select("SELECT NEXTVAL('SEQ_KLIKIGR')")[0]->nextval;
                                    $trnId = str_pad($query, 5, "0", STR_PAD_LEFT);
                                    $n = 1;
                                }else{
                                    $n++;
                                }
                            }

                            SelainWebMM1:

                            $query = '';
                            $query .= "SELECT COUNT(1)  ";
                            $query .= "FROM TBMASTER_PRODMAST ";
                            $query .= "WHERE PRD_KODEIGR = '" . $KodeIGR . "' ";
                            $query .= "AND PRD_PRDCD = '" . $data['Kode PLU'] . "' ";
                            $frac = DB::select($query)[0]->count;

                            if($frac == 0){
                                $message = "Kode PLU " . $data['Kode PLU'] . " Tidak ditemukan!";
                                throw new HttpResponseException(ApiFormatter::error(400, $message));

                                //! ADA FUNCTION LogPLUTolakan TAPI HARUSNYA FUNCTION SUDAH RETURN ERROR JADI PASTI TIDAK KESINI ATAU FLOWNYA GIMANA YA
                            }

                            $query = '';
                            $query .= "SELECT COALESCE((CASE WHEN prd_unit = 'KG' THEN 1 ELSE prd_frac END),1)  ";
                            $query .= "FROM TBMASTER_PRODMAST ";
                            $query .= "WHERE PRD_KODEIGR = '" . $KodeIGR . "'";
                            $query .= "AND PRD_PRDCD = '" . $data['Kode PLU'] . "' ";
                            $frac = DB::select($query)[0]->coalesce;

                            //* ALDO 10 Oktober 2017
                            $satuanJual = substr($data['Kode PLU'], -1);

                            $query = '';
                            $query .= "SELECT COALESCE(PRD_MINJUAL,1)  ";
                            $query .= "FROM TBMASTER_PRODMAST ";
                            $query .= "WHERE PRD_KODEIGR = '" & $KodeIGR & "'";
                            $query .= "AND PRD_PRDCD = '" . $data['Kode PLU'] . "' ";
                            $_minJual = DB::select($query)[0]->coalesce;

                            if ($data["Jml Pesanan"]->ToString() != "") {
                                //* ALDO 10 Oktober 2017
                                $qty = (doubleval(str_replace(".", ",", $data["Jml Pesanan"]->ToString())) * $frac);
                                $qtyNoFrac = doubleval(str_replace(".", ",", $data["Jml Pesanan"]->ToString()));

                                if ($satuanJual == "2" && $qty < $_minJual) {

                                    $message = "PLU SATUAN RENCENG KURANG DARI MINIMUM JUALNYA (Qty: " . $qty . " / MinJual " . $_minJual . "), NoPb = $nopb";
                                    throw new HttpResponseException(ApiFormatter::error(400, $message));

                                    // LogPLUTolakan(2, $data["Kode PLU"]->ToString() . " PLU SATUAN RENCENG KURANG DARI MINIMUM JUALNYA (Qty: " . $qty . " / MinJual " . $_minJual . ")", str_replace("/", "-", $nopb), $tglpb);
                                }
                            } else {
                                $qty = 0;
                            }

                            if ($data["Harga (Rp)"]->ToString() != "") {
                                //* ALDO 11 Oktober 2017
                                $price = round(doubleval(str_replace(".", ",", $data["Harga (Rp)"]->ToString())) / $frac, 2);
                                $priceNoFrac = doubleval(str_replace(".", ",", $data["Harga (Rp)"]->ToString()));
                            } else {
                                $price = 0;
                            }

                            $diskon = 0;
                            $ppn = 0;
                            $dpp = 0;
                            $ekspedisi = 0;
                            $cshbck = 0;

                            if($kdWeb != 'WebMM'){
                                //* ALDO 2 November 2017
                                $hargajual = $price;
                                $potMD = 0;
                                $potMKT = 0;

                                goto SelainWebMM2;
                            }

                            $query = '';
                            $query .= "SELECT COUNT(1) FROM TBTR_OBI_D ";
                            $query .= "WHERE OBI_TGLTRANS = '" . $tglnow . "'  ";
                            $query .= "AND   OBI_NOTRANS  = '" . $trnId . "' ";
                            $query .= "AND   OBI_PRDCD    = '" . $data['Kode PLU'] . "' ";
                            $cek = DB::select($query)[0]->count;

                            if($cek > 0){
                                if($cek <> $dpp){
                                    $message = "Harga PLU " . $data['Kode PLU'] . " tidak sama, proses tidak dapat dilanjutkan!";
                                    throw new HttpResponseException(ApiFormatter::error(400, $message));
                                }else{
                                    $query = '';
                                    $query .= "UPDATE TBTR_OBI_D ";
                                    $query .= "SET OBI_QTYORDER = (OBI_QTYORDER + '" . $qty . "')  ";
                                    $query .= "WHERE OBI_TGLTRANS = '" . $tglnow . "' ";
                                    $query .= "AND OBI_NOTRANS = '" . $trnId . "' ";
                                    $query .= "AND OBI_PRDCD = '" . $data['Kode PLU'] . "' ";
                                    DB::update($query);

                                    goto SkipInsert;
                                }
                            }

                            SelainWebMM2:

                            $query = '';
                            $query .= "SELECT COUNT(1)  ";
                            $query .= "FROM TBMASTER_PRODMAST ";
                            $query .= "WHERE PRD_KODEIGR = '" . $KodeIGR . "' ";
                            $query .= "AND PRD_PRDCD =  '" . $data['Kode PLU'] . "' ";
                            $query .= "AND PRD_FLAGBKP1 = 'Y' ";
                            $cek = DB::select($query)[0]->count;

                            if ($cek > 0) {
                                if ($price > 0) {
                                    // ALDO 11 Oktober 2017
                                    $dpp = round(($price / (1 + $getNilaiPPN)), 2);
                                    $ppn = round($price - $dpp, 2);
                                    $diskon = $potMD;
                                    $cshbck = $potMKT;
                                    // ALDO 13 Okt 2017
                                    $ttlHargaKnaPajak += ($priceNoFrac * $qtyNoFrac) - ($diskon * $qty);
                                }
                            } else {
                                if ($price > 0) {
                                    $dpp = $price;
                                    $ppn = 0;
                                    $diskon = $potMD;
                                    $cshbck = $potMKT;
                                    // ALDO 13 Okt 2017
                                    $ttlHargaKnaPajak += ($priceNoFrac * $qtyNoFrac) - ($diskon * $qty);
                                }
                            }

                            if($kdWeb != 'WebMM'){
                                $query = '';
                                $query .= "SELECT COUNT(1) FROM TBTR_OBI_D ";
                                $query .= "WHERE OBI_TGLTRANS = '" . $tglnow . "'  ";
                                $query .= "AND   OBI_NOTRANS  = '" . $trnId . "' ";
                                $query .= "AND   OBI_PRDCD    = '" . $data['Kode PLU'] . "' ";
                                $cek = DB::select($query)[0]->count;

                                if($cek > 0){
                                    $query = '';
                                    $query .= "UPDATE TBTR_OBI_D ";
                                    $query .= "SET OBI_QTYORDER = (OBI_QTYORDER + '" . $qty . "')  ";
                                    $query .= "WHERE OBI_TGLTRANS = '" . $tglnow . "' ";
                                    $query .= "AND OBI_NOTRANS = '" . $trnId . "' ";
                                    $query .= "AND OBI_PRDCD = '" . $data['Kode PLU'] . "' ";
                                    DB::update($query);

                                    goto SkipInsert;
                                }
                            }

                            DB::table('tbtr_obi_d')
                                ->insert([
                                    'obi_tgltrans' => $tglnow,
                                    'obi_notrans' => $trnId,
                                    'obi_prdcd' => $data['Kode PLU'],
                                    'obi_hargasatuan' => $dpp,
                                    'obi_qtyorder' => $qty,
                                    'obi_qtyrealisasi' => '0',
                                    'obi_ppn' => $ppn,
                                    'obi_diskon' => $diskon,
                                    'obi_hpp' => 0,
                                    'obi_kodealamat' => 000,
                                    'obi_cashback' => $cshbck,
                                    'obi_kd_promosi' => $kdWeb == 'WebMM' ? null : $kodePromosi,
                                ]);

                            $ttlitem++;

                            $query = '';
                            $query .= "SELECT COUNT(1)  ";
                            $query .= "FROM TBTR_ALAMAT_MM ";
                            $query .= "WHERE AMM_KODEMEMBER = '" . $kdmember . "'";
                            $query .= "AND AMM_NOPB = '" . $nopb . "' ";
                            $query .= "AND DATE_TRUNC('DAY',AMM_TGLPB) = '" . $tglpb . "' ";
                            $cek = DB::select($query)[0]->count;

                            if($cek == 0){
                                DB::table('tbtr_alamat_mm')
                                    ->insert([
                                        'amm_kodeigr' => $KodeIGR,
                                        'amm_kodemember' => $kdmember,
                                        'amm_nopb' => $nopb,
                                        'amm_tglpb' => $tglpb,
                                        'amm_notrans' => $trnId,
                                        'amm_namapenerima' => $namaPenerima,
                                        'amm_namaalamat' => $data['Kode Alamat'],
                                        'amm_email' => $email,
                                        'amm_hp' => $noHp,
                                        'amm_create_by' => $UserMODUL,
                                        'amm_create_dt' => $tglnow,
                                    ]);
                            }

                            if($kdWeb != 'WebMM'){
                                if($namaWP <> "" AND $alamatWP <> ""){
                                    $query = '';
                                    $query .= "SELECT COUNT(1)  ";
                                    $query .= "FROM PAJAK_KIGR ";
                                    $query .= "WHERE PKI_KODEMEMBER = '" . $kdmember . "' ";
                                    $query .= "AND PKI_NOPB = '" . $nopb . "' ";
                                    $query .= "AND DATE_TRUNC('DAY',PKI_TGLPB) = '" . $tglpb . "' ";
                                    $cek = DB::select($query)[0]->count;

                                    if($cek == 0){
                                        $query = '';
                                        $query .= "INSERT INTO PAJAK_KIGR(PKI_KODEIGR, PKI_KODEMEMBER, PKI_NOPB, ";
                                        $query .= "PKI_TGLPB, PKI_TGLTRANS, PKI_NOTRANS, PKI_NAMA, PKI_NPWP, PKI_ALAMAT, PKI_CREATE_BY, PKI_CREATE_DT) ";
                                        $query .= "VALUES('" . $KodeIGR . "', '" . $kdmember . "', '" . $nopb . "', ";
                                        $query .= "'" . $tglpb . "', '" . $tglnow . "', '" . $trnId . "', '" . $namaWP . "', '" . $npwp . "', '" . $alamatWP . "', '" . $UserMODUL . "', '" . $tglnow . "') ";
                                    }
                                }
                            }

                            SkipInsert:

                            if($kdWeb != 'WebMM'){
                                $ttlorder += round(($qty * $dpp), 2);
                                $ttlppn += round(($qty * $ppn), 2);
                            }else{
                                $ttlorder += round(($qty * $price));
                                $ttlppn = ($ttlorder * $getNilaiPPN);
                            }

                            $ttldiskon += ($diskon * $qty);
                            $ttlcshbck += ($cshbck * $qty);

                            if ($key < count($datas) - 1) {
                                $_kdmember = "";
                                $str = $datas[$key + 1]["No Anggota"]->ToString();
                                $strArr = explode("/", $str);
                                $_kdmember = $strArr[0];
                                if ($kdmember == $_kdmember &&
                                    $tglpb == date_create($datas[$key + 1]["Tgl Pemesanan"]) &&
                                    $nopb == $datas[$key + 1]["No Pemesanan"]->ToString() &&
                                    $kdAlamat == $datas[$key + 1]["Kode Alamat"]->ToString()) {
                                    $flagHeader = false;
                                } else {
                                    $flagHeader = true;
                                }
                            } else {
                                $flagHeader = true;
                            }

                            if($qty < 0 OR $price < 0){
                                $message = " qty/harga < 0, proses gagal!";
                                throw new HttpResponseException(ApiFormatter::error(400, $message));
                            }

                            $query = '';
                            $query .= "INSERT INTO TBHISTORY_OBI_D (OBI_NOPB,OBI_TGLPB,OBI_TGLTRANS,OBI_NOTRANS,OBI_SEQ,OBI_PRDCD,OBI_FRAC, ";
                            $query .= "OBI_MINJUAL,OBI_HARGASATUAN,OBI_QTYORDER,OBI_QTYREAL,OBI_KODEALAMAT) ";
                            $query .= "VALUES('" . $nopb . "','" . $tglpb . "','" . $tglnow . "' ,'" . $trnId . "','" . $n . "','" . $data['Kode PLU'] . "','" . $frac . ", ";
                            $query .= "'" . $_minJual . "','" . $hargajual . "', '" . $qty . "',0,'000') ";
                            DB::insert($query);

                            if($flagHeader == true){
                                $dppHargaKnaPajak = round($ttlHargaKnaPajak / (1 + $getNilaiPPN), 2);
                                $ppnHargaKnaPajak = round($dppHargaKnaPajak * $getNilaiPPN, 2);

                                $dppHargaKnaPajak = round($dppHargaKnaPajak);
                                $ppnHargaKnaPajak = round($ppnHargaKnaPajak);

                                DB::table('tbtr_obi_h')
                                    ->insert([
                                        'obi_kodeigr' => $KodeIGR,
                                        'obi_nopb' => $nopb,
                                        'obi_tglpb' => $tglpb,
                                        'obi_tgltrans' => $tglnow,
                                        'obi_notrans' => $trnId,
                                        'obi_kdmember' => $kdmember,
                                        'obi_zona' => $zona,
                                        'obi_ttlorder' => $dppHargaKnaPajak + $ttlHargaTnpPajak,
                                        'obi_ttlppn' => $ppnHargaKnaPajak,
                                        'obi_ttldiskon' => $ttldiskon,
                                        'obi_itemorder' => $ttlitem,
                                        'obi_realorder' => 0,
                                        'obi_realppn' => 0,
                                        'obi_realdiskon' => 0,
                                        'obi_realitem' => 0,
                                        'obi_distributionfee' => 0,
                                        'obi_ekspedisi' => $ekspedisi,
                                        'obi_jrkekspedisi' => $jarak,
                                        'obi_freeongkir' => $data['Bebas Biaya Pengiriman'],
                                        'obi_createby' => $UserMODUL,
                                        'obi_createdt' => $tglNowDateTime,
                                        'obi_nopo' => $nopo,
                                        'obi_attribute2' => $kdWeb,
                                        'obi_expireddt' => $expiredDate,
                                    ]);

                                $ttlHargaKnaPajak = 0;
                                $ttlHargaTnpPajak = 0;
                                $dppHargaKnaPajak = 0;
                                $ppnHargaKnaPajak = 0;
                            }
                        }

                        if($pluSelisih > 0){
                            $message = "Ditemukan Selisih Harga Pada PLU:" . substr($strSelisih, 0, strlen($strSelisih) - 2);
                            throw new HttpResponseException(ApiFormatter::error(400, $message));
                        }
                    }else{
                        $message = "Jumlah Kolom tidak sesuai pada data $item";
                        throw new HttpResponseException(ApiFormatter::error(400, $message));
                    }
                }
            }elseif(strlen($NamaFile) == 27){
                $NamaFile = '';

                $ctr = $ctr + 1;

                $datas = []; //! data didalam filenya

                if(count($datas) != 7){
                    $message = "Kolom $item Tidak Sesuai!";
                    throw new HttpResponseException(ApiFormatter::error(400, $message));
                }

                foreach($datas as $key => $data){
                    $kdmemberV = substr($data[1], 0, 6);
                    $nopbV = $data[2];

                    $dtTrans = DB::table('tbtr_obi_h')
                        ->select('obi_notrans','obi_tgltrans')
                        ->where([
                            'obi_kdmember' => $kdmemberV,
                            'obi_nopb' => $nopbV,
                        ])
                        ->get();

                    $voucherDataFound = DB::table('tbtr_obi_voucher')
                        ->where([
                            'vki_kdmember' => substr($data[1], 0, 6),
                            'vki_nopb' => $data[2],
                        ])
                        ->count();

                    $tglPBvouc = date_create($data[3]);
                    $nominalVouc = str_replace(".00", "", $data["Nominal"]);
                    $nominalVouc = doubleval($nominalVouc);
                    $tgltrans = date_create($dtTrans[0]->obi_tgltrans);

                    if($voucherDataFound > 0){
                        $message = "Member : " . $kdmemberV . ", NoPB = " . $nopbV . " Sudah Ada Di Database!";
                        throw new HttpResponseException(ApiFormatter::error(400, $message));
                    }else{
                        DB::table('tbtr_obi_voucher')
                            ->insert([
                                'vki_kodeigr' => $data[0],
                                'vki_kdmember' => $kdmemberV,
                                'vki_nopb' => $nopbV,
                                'vki_tglpb' => $tglPBvouc,
                                'vki_kodealamat' => $data[4],
                                'vki_kodevoucher' => $data[5],
                                'vki_nominal' => $nominalVouc,
                                'vki_notrans' => $dtTrans[0]->obi_tgltrans,
                                'vki_tgltrans' => $tgltrans,
                                'vki_create_by' => $UserMODUL,
                                'vki_create_dt' => $tglPBvouc,
                            ]);
                    }
                }
            }
        }
    }
}
