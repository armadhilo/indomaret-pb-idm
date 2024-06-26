<?php

namespace App\Http\Controllers;

use App\Helper\ApiFormatter;
use App\Helper\DatabaseConnection;
use App\Http\Requests\HistoryProdukRequest;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class HistoryProdukController extends Controller
{

    private $jmlToko;
    private $setKode;

    public function __construct(Request $request){
        DatabaseConnection::setConnection(session('KODECABANG'), "PRODUCTION");

        $this->setKode = 'REPORT';
    }

    public function index(){

        $this->jmlToko = DB::select("select COUNT(DISTINCT TKO_KODEOMI) from tbmaster_tokoigr where tko_kodesbu = 'I' and tko_kodeigr = '" . session('KODECABANG') . "' and TKO_FLAGKPH = 'Y' ")[0]->count;

        //* untuk mode ada 2 yaitu KPH MEAN dan PRODUK BARU
        //* untuk KPH MEAN button upload CSV name jadi 'Upload CSV' (pilihan default)
        //* untuk KPH MEAN button upload CSV name jadi 'Minor'

        //* diawal akan panggil function datatables

        return view('menu.history-produk');
    }

    public function datatables(){

        $query = '';
        $query .= "select DISTINCT TKO_KODEOMI KODETK, TKO_NAMAOMI NAMATK ";
        $query .= "from tbmaster_tokoigr ";
        $query .= "where tko_kodesbu = 'I'  ";
        $query .= "and TKO_FLAGKPH = 'Y' ";
        // $query .= "and tko_kodeigr = '" . session('KODECABANG') . "'   ";
        $query .= "order by 1  ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);

        // dgvHP.Rows.Add(data.Rows(i).Item("KODETK").ToString, _
        //                       data.Rows(i).Item("NAMATK").ToString)
    }

    public function actionProses(HistoryProdukRequest $request){
        $formattedDate = $request->date . '-01';
        $dtCek = DB::table('tbtemp_hp')
            ->whereDate('tgl', '>=', $formattedDate)
            ->whereDate('tgl', '<', date('Y-m-d', strtotime($formattedDate . ' +1 month')))
            ->count();

        if($dtCek){
            return ApiFormatter::error(400, "File history periode " . $request->pilBulan . $request->pilTahun . " sudah pernah diupload!");
        }

        if((int)$request->pilBulan < 10 ){
            $blnPeriode = (int)$request->pilBulan;
        }else{
            if($request->pilBulan == 10){
                $blnPeriode = 'A';
            }elseif($request->pilBulan == 11){
                $blnPeriode = 'B';
            }else{
                $blnPeriode = 'C';
            }
        }

        switch ($request->pilTahun) {
            case '2015':
                $thnPeriode = 'F';
            case '2016':
                $thnPeriode = 'G';
            case '2017':
                $thnPeriode = 'H';
            case '2018':
                $thnPeriode = 'I';
            case '2019':
                $thnPeriode = 'J';
            case '2020':
                $thnPeriode = 'K';
            case '2021':
                $thnPeriode = 'L';
            case '2022':
                $thnPeriode = 'M';
            case '2023':
                $thnPeriode = 'N';
            case '2024':
                $thnPeriode = 'O';
            case '2025':
                $thnPeriode = 'P';
              break;
            default:
              $thnPeriode = '-';
          }

        $this->initializeFile($blnPeriode, $thnPeriode, $request->pilTahun);

        return ApiFormatter::success(200, 'Proses berhasil dilakukan');
    }

    private function initializeFile($blnPeriode, $thnPeriode, $pilTahun){

    }

    //! NOTE KEVIN | AMBIL FILE DARI FTP (TIDAK DICOBA) 
    // public function actionUploadCsvFtp(){
    //     //* Update File PLU IDM via FTP?

    //     try{
    //         $kodeigr = session('KODECABANG');
    //         $UserMODUL = session('userid');
    //         $procedure = DB::select("call sp_downloadidm('$kodeigr', NULL)");
    //         $procedure = $procedure[0]->v_msg;

    //         if (str_contains($procedure, 'selesai')) {
    //             //! NOTE -> DBNull.Value bingung darimana VB sekarang di default NULL
    //             $ipModul = str_replace(".", "", $this->getIP());
    //             $procedure = DB::select("call sp_trf_plu_idm(NULL, '$ipModul', '$kodeigr', '$UserMODUL', NULL)");
    //             $procedure = $procedure[0]->v_result;

    //             if (str_contains($procedure, 'BERHASIL')) {
    //                 $procedure = DB::select("call sp_update_prodcrm('$kodeigr', '$UserMODUL', NULL)");
    //                 $procedure = $procedure[0]->p_stat;

    //                 if (str_contains($procedure, 'SUKSES')) {
    //                     $this->cetakLaporan('', 1);
    //                 }
    //             }
    //         }
    //     }

    //     catch(\Exception $e){

    //         DB::rollBack();

    //         $message = "Oops! Something wrong ( $e )";
    //         return ApiFormatter::error(400, $message);
    //     }
    // }

    private function cetakLaporan($pathfile, $code = 0){

        $dt = DB::select("SELECT TRF_NAMADBF from tbtr_transferfile where trf_namaprog = 'IGR_BO_TRF_PLU_IDM' order by trf_create_dt desc");
        if(count($dt) == 0){
            throw new HttpResponseException(ApiFormatter::error(400, 'Cetak Laporan Gagal, TRF_NAMADBF pada table tbtr_transferfile tidak ditemukan'));
        }

        $fileIdm = $pathfile;
        if($code != 0){
            $fileIdm = $dt[0]->trf_namadbf;
        }

        $query = '';
        $query .= "select row_number() over() nomor, b.* from   ";
        $query .= "(select distinct   ";
        $query .= "idm_pluidm pluidm, idm_tag tag_idm, prd_deskripsipanjang deskripsi  ";
        $query .= "from tbtemp_pluidm   ";
        $query .= "left join tbmaster_prodmast   ";
        $query .= "on idm_pluidm = prd_plumcg   ";
        $query .= "where idm_pluigr is null and coalesce(idm_tag,'A') not in ('F','N','R')  ";
        $query .= "order by 1) b  ";
        $data = DB::select($query);

        //! TUNGGU BENTUK CSV NYA
        //! NANTI DIKIRIM EMAIL
    }

    public function actionUploadCsvBrowse(Request $request){

        //? function ini hanya untuk checking

        if($request->pilUpload == 'PLUIDM'){
            if(substr($request->filename, 0, 3) != 'IDM'){
                return ApiFormatter::error(400, 'File tidak sesuai dengan kode cabang');
            }
        }elseif($request->pilUpload == 'PRODUK BARU'){
            if($this->cekNamaFile($request->filename) == false){
                return ApiFormatter::error(400, 'Nama File Tidak Sesuai. Tidak bisa ambil periode!');
            }

            $dtCek1 = DB::table('TBHISTORY_PRODUK_BARU')
                ->where('HPB_PERIODE_FILE', $this->getPeriodNewProduct($request->filename, 2))
                ->count();

            $dtCek2 = $this->getFileRevision($request->filename);

            if($dtCek1 > 0 AND $dtCek2 == false){
                return ApiFormatter::error(400, 'File sudah pernah di proses!');
            }
        }

        //! START FUNCTION prosesData

        if($request->pilUpload == 'MINOR'){

            DB::select("DELETE FROM TBTEMP_MINORTK");

            foreach($request->filename as $item){
                //! INSERT TBTEMP_MINORTK - PENGGANTI BULKCOPY
                DB::table('tbtemp_minortk') 
                    ->insert([
                        'prdcd' => $item['PRDCD'],
                        'desc2' => $item['DESC'],
                        'frac' => $item['FRAC'],
                        'minor' => $item['MINOR'],
                    ]);
            }
        }elseif($request->pilUpload == 'PLUIDM'){
            //! FILE DALAM BENTUK ZIP MULTIPLE CSV

            DB::select("DELETE FROM TBTEMP_PLUIDM");
            DB::select("DELETE FROM TBTEMP_PLUIDM2 WHERE ip = '" . $this->getIP() . "' ");


            //! INSERT TBTEMP_PLUIDM2 - PENGGANTI BULKCOPY
            DB::table('TBTEMP_PLUIDM2')
                ->insert([
                    'idm_pluidm' => $item['IDM_PLUIDM'],
                    'idm_pluigr' => $item['IDM_PLUIGR'],
                    'idm_tag' => $item['IDM_TAG'],
                    'idm_renceng' => $item['IDM_RENCENG'],
                    'idm_minor' => $item['IDM_MINOR'],
                    'idm_kdidm' => $item['IDM_KDIDM'],
                    'ip' => $item['IP'],
                ]);

            $query = '';
            $query .= " INSERT INTO tbtemp_pluidm ( ";
            $query .= "   idm_pluidm, ";
            $query .= "   idm_pluigr, ";
            $query .= "   idm_tag, ";
            $query .= "   idm_renceng, ";
            $query .= "   idm_minor, ";
            $query .= "   idm_kdidm, ";
            $query .= "   idm_create_dt, ";
            $query .= "   idm_create_by ";
            $query .= " ) ";
            $query .= " SELECT  ";
            $query .= "   idm_pluidm, ";
            $query .= "   idm_pluigr, ";
            $query .= "   idm_tag, ";
            $query .= "   idm_renceng, ";
            $query .= "   idm_minor, ";
            $query .= "   idm_kdidm, ";
            $query .= "   NOW() idm_create_dt, ";
            $query .= "   '" . session('userid') . "' idm_create_by ";
            $query .= " FROM tbtemp_pluidm2  ";
            $query .= " WHERE ip = '" . $this->getIP() . "' ";
            DB::insert($query);

            $kodeigr = session('KODECABANG');
            $UserMODUL = session('userid');
            $procedure = DB::select("call sp_update_prodcrm('$kodeigr', '$UserMODUL', '')");
            $procedure = $procedure[0]->p_stat;

            if (str_contains($procedure, 'SUKSES')) {
                $this->cetakLaporan($request->filename);
            }

        }elseif($request->pilUpload == 'PRODUK BARU'){
            if($dataCSV[0]['kodeigr'] <> session('KODECABANG')){
                return ApiFormatter::error(400, 'File tidak sesuai dengan cabang ' . session('NAMACABANG'));
            }

            $this->insertData('TBTEMP_PRODUK_BARU1', $dataCSV, $this->getPeriodNewProduct($request->filename, 1), $this->getPeriodNewProduct($request->filename, 2));
        }

        //! END FUNCTION prosesData
    }


    private function insertData($table, $data, $periodeProses, $periodeFile){
        $periode = $this->getLastPeriod();

        DB::select("DELETE FROM $table");

        foreach($data as $item){
            DB::table($table)
                ->insert([
                    'KODEIGR' => $item['KODEIGR'],
                    'TGL_SHELF' => date('Y-m-d', strtotime($item['tglFS'])),
                    'PLUIDM' => $item['PLUIDM'],
                    'PLUIGR' => $item['PLUIGR'],
                    'DESKRIPSI' => $item['DESKRIPSI'],
                    'JMLTOKO' => $item['JTD'],
                    'SPD' => str_replace('.', ',', $item['SPD']),
                    'MINOR' => $item['MINOR'],
                    'HPP' => $item['HPP'],
                    'TGL_MEETING' => date('Y-m-d', strtotime($item['tglFM'])),
                    'PERIODE' => $periodeProses,
                    'CREATE_DT' => now(), // Assuming 'NOW()' refers to current timestamp
                    'CREATE_BY' => session('userid'),
                    'PERIODE_FILE' => $periodeFile
                ]);
        }

        $query = '';
        $query .= "MERGE INTO TBTEMP_PRODUK_BARU T ";
        $query .= "     USING (SELECT *  ";
        $query .= "              FROM TBTEMP_PRODUK_BARU1)  ";
        $query .= "        ON (PBR_PLUIDM = PLUIDM)  ";
        $query .= "WHEN MATCHED  ";
        $query .= "THEN  ";
        $query .= "   UPDATE SET PBR_TGL_SHELF = TGL_SHELF,  ";
        $query .= "              PBR_SPD = SPD,  ";
        $query .= "              PBR_JMLTOKO = JMLTOKO::INT,  ";
        $query .= "              PBR_MINOR = MINOR,  ";
        $query .= "              PBR_HPP = HPP,  ";
        $query .= "              PBR_TGL_MEETING = TGL_MEETING, ";
        $query .= "              PBR_PERIODE = PERIODE, ";
        $query .= "              PBR_PERIODE_FILE = PERIODE_FILE, ";
        $query .= "              PBR_MODIFY_DT = NOW(),  ";
        $query .= "              PBR_MODIFY_BY = '" . session('userid') . "'  ";
        $query .= "WHEN NOT MATCHED  ";
        $query .= "THEN  ";
        $query .= "   INSERT     VALUES (NULL,  ";
        $query .= "                      KODEIGR,  ";
        $query .= "                      TGL_SHELF,  ";
        $query .= "                      TGL_MEETING, ";
        $query .= "                      PLUIDM,  ";
        $query .= "                      PLUIGR,  ";
        $query .= "                      DESKRIPSI,  ";
        $query .= "                      SPD,  ";
        $query .= "                      JMLTOKO::INT,  ";
        $query .= "                      MINOR,  ";
        $query .= "                      HPP,  ";
        $query .= "                      PERIODE, ";
        $query .= "                      PERIODE_FILE, ";
        $query .= "                      NOW(),  ";
        $query .= "                      '" . session('userid') . "',  ";
        $query .= "                      NULL,  ";
        $query .= "                      NULL)  ";
        DB::insert($query);

        //! INSERT KE HISTORY
        DB::insert("INSERT INTO TBHISTORY_PRODUK_BARU SELECT * FROM TBTEMP_PRODUK_BARU1");

        $kodeigr = session('KODECABANG');
        $UserMODUL = session('userid');
        $periodeProc = $periode != $periodeProses ? $periodeProses : $periode;
        $procedure = DB::select("call hp_proses4('$periodeProc', '$kodeigr', '$UserMODUL', NULL)");
        $procedure = $procedure[0]->p_status;

        return true;
    }

    private function getFileRevision($filename){
        // Replace "REPORT PRODUK BARU IDM " with an empty string
        $filename = str_replace("REPORT PRODUK BARU IDM ", "", $filename);

        // Check if the character at position 8 is "R"
        if (substr($filename, 7, 1) === "R") {
            // Return true if the condition is met
            return true;
        } else {
            // Return false if the condition is not met
            return false;
        }
    }

    private function getPeriodNewProduct($filename, $kode){
        // Replace "REPORT PRODUK BARU IDM " with an empty string
        $filename = str_replace("REPORT PRODUK BARU IDM ", "", $filename);

        // Extract the first 6 characters of the modified string as a date string
        $dateString = substr($filename, 0, 6);

        // Parse the date string
        $dateConvert = DateTime::createFromFormat("dmy", $dateString);

        if ($kode == 1) {
            // For "PID"
            $strPeriod = $dateConvert->format("mY");

            // Check if the first character of $strPeriod is '0'
            if (substr($strPeriod, 0, 1) == '0') {
                // If so, remove the leading '0'
                $strPeriod = substr($strPeriod, 1);
            }
        } else {
            // For "FILE PERIOD"
            $strPeriod = $dateConvert->format("dmY");
        }

        return $strPeriod;
    }

    private function cekNamaFile($filename){
        // Replace "REPORT PRODUK BARU IDM " with an empty string
        $filename = str_replace("REPORT PRODUK BARU IDM ", "", $filename);

        // Extract the first 6 characters of the modified string
        $dateString = substr($filename, 0, 6);

        // Parse the date string using DateTime::createFromFormat()
        $dateConvert = DateTime::createFromFormat("dmy", $dateString);

        // Check if parsing was successful
        if ($dateConvert !== false) {
            // Date parsing successful, return true
            return true;
        } else {
            // Date parsing failed
            return false;
        }
    }

    public function actionHitKPH(){
        //! btnHitungKPH_Click
        //! initKPH

        $dtCek = DB::select("SELECT COUNT(DISTINCT PRDCD) from tbtemp_minortk")[0]->count;

        if($dtCek < 50){
            return ApiFormatter::error(400, 'Belum upload file minor (data < 50)!');
        }

        $dtCek = DB::select("SELECT COUNT(DISTINCT IDM_PLUIDM) FROM TBTEMP_PLUIDM WHERE DATE_TRUNC('DAY',IDM_CREATE_DT) = DATE_TRUNC('DAY',CURRENT_DATE)")[0]->count;
        if($dtCek == 0){
            return ApiFormatter::error(400, 'Data PLU IDM tidak sama dengan tanggal hari ini! Silahkan upload ulang ...');
        }

        $periode = $this->getLastPeriod();

        if($periode == false){
            return ApiFormatter::error(400, 'Belum pernah upload data History Produk!');
        }

        //! START function -> initKPH($periode)

        $dtCek = DB::select("select COUNT(DISTINCT KODETOKO) from tbtemp_hp where TGL = '" . $periode . "'");
        if($dtCek <> $this->jmlToko){
            return ApiFormatter::error(400, 'Data Toko IDM Tidak sesuai dengan master toko IGR!');
        }

        $dtCek = DB::table('temp_log_hp')
            ->where('periode', $periode)
            ->hereNull('time_end')
            ->whereNotIn('proses', ['P4'])
            ->count();

        if($dtCek > 0){
            return ApiFormatter::error(400, "Hitung KPH mean periode " . $periode . " tidak sempurna! bersihkan data terlebih dahulu kemudian ulangi prosesnya");
        }

        $numProses = $this->getCountLOG($periode);

        if($numProses >= 3){
            return ApiFormatter::error(400, "Hitung KPH mean periode " . $periode . " sudah pernah di proses..");
        }elseif($numProses > 0 AND $numProses < 3){
            return ApiFormatter::error(400, "Hitung KPH mean periode " . $periode . " tidak sempurna! bersihkan data terlebih dahulu kemudian ulangi prosesnya");
        }

        $dtCek = DB::table('TBMASTER_KPH')
            ->where('pid', $periode)
            ->where('t_spd', '>', '0')
            ->count();

        if($dtCek == 0){
            $cek1 = DB::select("SELECT COUNT(DISTINCT msi_kodedc) FROM master_supply_idm")[0]->count;
            $cek2 = DB::select("SELECT COUNT(DISTINCT idm_pluidm) FROM tbmaster_pluidm")[0]->count;

            $flagPLUIDM = false;
            if($cek1 > 0 AND $cek2 > 0){
                $flagPLUIDM = true;
            }

            if($flagPLUIDM){
                $query = '';
                $query .= "insert into tbmaster_kph(KODEIGR, PID, PRDCD)  ";
                $query .= "Select distinct '" . session('KODECABANG') . "' KDIGR, '" . $periode . "' as PRID, H.PRDCD ";
                $query .= "FROM TBTEMP_HP H";
                $query .= "Join master_supply_idm";
                $query .= "   on H.KODETOKO = MSI_KODETOKO";
                $query .= "join TBTEMP_PLUIDM P";
                $query .= "   ON H.PRDCD = P.IDM_PLUIDM ";
                $query .= "   and MSI_KODEDC = P.IDM_KDIDM ";
                $query .= "    AND coalesce(P.IDM_TAG,'1') NOT IN ('H','A','N','O','X') ";
                $query .= "left join TBTEMP_MINORTK M ";
                $query .= "   on H.PRDCD = M.PRDCD";
                $query .= "WHERE H.SPD > 0 ";
                $query .= "  AND H.PRDCD NOT IN ";
                $query .= "  (SELECT PBR_PLUIDM FROM TBTEMP_PRODUK_BARU WHERE DATE_TRUNC('DAY',PBR_TGL_SHELF) > CURRENT_DATE - 90 AND PBR_PERIODE = H.TGL) ";
                $query .= "  AND H.TGL = '" . $periode . "' ";
            }else{
                $query = '';
                $query .= "insert into tbmaster_kph(KODEIGR, PID, PRDCD)  ";
                $query .= "Select distinct '" . session('KODECABANG') . "' KDIGR, '" . $periode . "' as PRID, H.PRDCD ";
                $query .= "FROM TBTEMP_HP H";
                $query .= "join TBTEMP_PLUIDM P";
                $query .= "   ON H.PRDCD = P.IDM_PLUIDM";
                $query .= "   AND coalesce(P.IDM_TAG,'1') NOT IN ('H','A','N','O','X')";
                $query .= "left join TBTEMP_MINORTK M";
                $query .= "   on H.PRDCD = M.PRDCD";
                $query .= "WHERE H.SPD > 0 ";
                $query .= "AND H.PRDCD NOT IN ";
                $query .= "(SELECT PBR_PLUIDM FROM TBTEMP_PRODUK_BARU WHERE DATE_TRUNC('DAY',PBR_TGL_SHELF) > CURRENT_DATE - INTERVAL 'DAY 90' AND PBR_PERIODE = H.TGL) ";
                $query .= "AND H.TGL = '" . $periode . "' ";
            }

        }

        //! END function -> initKPH($periode)

        return ApiFormatter::success(200, 'Proses HIT KPH berhasil dilakukan');

    }

    private function getCountLOG($periode){
        return DB::select("SELECT count(distinct proses) from temp_log_hp where periode = '" . $periode . "' and time_end is not null and proses not in ('P4')")[0]->count;
    }

    private function getLastPeriod(){
        $query = '';
        $query .= "SELECT DISTINCT  ";
        $query .= "MAX(  ";
        $query .= "  TO_DATE(  ";
        $query .= "    CASE WHEN LENGTH(TGL) = 5   ";
        $query .= "      THEN '0'||coalesce(TGL,'0')   ";
        $query .= "    ELSE coalesce(TGL,'0')   ";
        $query .= "    END,  ";
        $query .= "    'MMYYYY')  ";
        $query .= ") TGL   ";
        $query .= "FROM TBTEMP_HP  ";
        $data = DB::select($query);

        if(count($data)){
            if($data[0]->tgl != null){
                return (int) Carbon::parse($data[0]->tgl)->format('mY');
            }
        }

        return false;
    }

    public function actionReportKPH(Request $request){
        //! btnReport_Click

        //! form -> frmPilPeriode (digunakan hanya untuk memilih periode)
        $periode = $request->periode;

        $pengaliKPH = DB::select("select coalesce(PRS_KPHCONST,1) V_KPH from tbmaster_perusahaan")[0]->v_kph ?? 0;

        //! CHECK MASTER_SUPPLY_IDM
        $cek1 = DB::select("SELECT COUNT(DISTINCT msi_kodedc) FROM master_supply_idm")[0]->count;
        $cek2 = DB::select("SELECT COUNT(idm_pluidm) FROM tbmaster_pluidm")[0]->count;

        $flagPLUIDM = False;
        if($cek1 > 0 AND $cek2 > 0){
            $flagPLUIDM = True;
        }

        if($flagPLUIDM){
            $query = '';
            $query .= "SELECT H.PRDCD PLUIDM, R.PRC_PLUIGR PLUIGR,   ";
            $query .= "coalesce(P.PRD_DESKRIPSIPENDEK,'-') DESKRIPSI,   ";
            $query .= "H.KSL_MEAN KPH, H.MINOR, R.PRC_KODETAG TAG_CRM,  ";
            $query .= "P.PRD_KODETAG TAG_PRD, coalesce(P.PRD_MINORDER,0) MINOR_IGR,  ";
            $query .= "R.PRC_MINORDER MINOR_CRM, coalesce(P.PRD_FRAC,0) FRAC,  ";
            $query .= "H.KSL_MEAN * 3 KPH_3, ";
            $query .= "H.KSL_MEAN * PRS_KPHCONST KPH_CONST,  ";
            $query .= "CASE WHEN T_SPD = 0 THEN '*' ELSE '' END PRD ";
            $query .= "FROM TBMASTER_KPH H ";
            $query .= "LEFT JOIN TBMASTER_PRODCRM R ";
            $query .= "  ON H.PRDCD = R.PRC_PLUIDM ";
            $query .= "LEFT JOIN TBMASTER_PRODMAST P ";
            $query .= "  ON R.PRC_PLUIGR = P.PRD_PRDCD  ";
            $query .= "LEFT JOIN TBMASTER_PERUSAHAAN ";
            $query .= "  ON PRS_KODEIGR = H.KODEIGR";
            $query .= " WHERE H.PID = '" . $periode . "' ORDER BY 13,2  ";
        }else{
            $query = '';
            $query .= "SELECT H.PRDCD PLUIDM, R.PRC_PLUIGR PLUIGR,   ";
            $query .= "coalesce(P.PRD_DESKRIPSIPENDEK,'-') DESKRIPSI,   ";
            $query .= "H.KSL_MEAN KPH, H.MINOR, R.PRC_KODETAG TAG_CRM,  ";
            $query .= "P.PRD_KODETAG TAG_PRD, coalesce(P.PRD_MINORDER,0) MINOR_IGR,  ";
            $query .= "R.PRC_MINORDER MINOR_CRM, coalesce(P.PRD_FRAC,0) FRAC,  ";
            $query .= "H.KSL_MEAN * 3 KPH_3, ";
            $query .= "H.KSL_MEAN * PRS_KPHCONST KPH_CONST,  ";
            $query .= "CASE WHEN T_SPD = 0 THEN '*' ELSE '' END PRD ";
            $query .= "FROM TBMASTER_KPH H ";
            $query .= "LEFT JOIN TBMASTER_PRODCRM R ";
            $query .= "  ON H.PRDCD = R.PRC_PLUIDM ";
            $query .= "LEFT JOIN TBMASTER_PRODMAST P ";
            $query .= "  ON R.PRC_PLUIGR = P.PRD_PRDCD  ";
            $query .= "LEFT JOIN TBMASTER_PERUSAHAAN ";
            $query .= "  ON PRS_KODEIGR = H.KODEIGR";
            $query .= "WHERE H.PID = '" . $periode . "' ORDER BY 13,2  ";
        }

        $data['data'] = DB::select($query);
        $data['perusahaan'] = DB::select("select prs_kodeigr kode_igr, PRS_NAMACABANG, coalesce(PRS_KPHCONST,1) V_KPH from tbmaster_perusahaan");
        $data['jmltoko'] = DB::select("select count(distinct kodetoko) JML_TOKO_PROSES from tbtemp_hp where tgl = '" . $periode . "'");
        $data['periode'] = DB::select("SELECT coalesce(JML_TOKO,0) JMLTOKO, PERIODE FROM TEMP_LOG_HP WHERE PERIODE = '" . $periode . "' AND PROSES = 'P2'");

        // return view('report', $data);
        return $data;
    }

    public function datatablesReportKPH(){
        if($this->setKode == 'KPH'){
            $query = "Select distinct coalesce(TGL,'X') PERIODE FROM TBTEMP_HP";
        }else{
            $query = "select distinct CAST(coalesce(PID,'X')as INT) PERIODE FROM TBMASTER_KPH ORDER BY 1";
        }

        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function actionOkReportKPH(){
        //? hanya bisa pilih 1 data pada datatables report KPH
        //? nanti periode yang dipilih akan digunakan actionReportKPH
    }
}
