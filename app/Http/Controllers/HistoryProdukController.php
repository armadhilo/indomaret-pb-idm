<?php

namespace App\Http\Controllers;
ini_set('max_execution_time', '0');
ini_set('memory_limit', '-1');

use App\Helper\ApiFormatter;
use App\Helper\DatabaseConnection;
use App\Http\Requests\HistoryProdukRequest;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GeneralExcelImport;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Barryvdh\DomPDF\Facade\Pdf;


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
        $query .= "SELECT DISTINCT ";
        $query .= "TKO_KODEOMI AS KODETK, ";
        $query .= "TKO_NAMAOMI AS NAMATK, ";
        $query .= "NULL AS stat, ";   // Add stat column with NULL value
        $query .= "NULL AS periode "; // Add periode column with NULL value
        $query .= "FROM tbmaster_tokoigr ";
        $query .= "WHERE tko_kodesbu = 'I' ";
        $query .= "AND TKO_FLAGKPH = 'Y' ";
        // $query .= "AND tko_kodeigr = '" . session('KODECABANG') . "' ";
        $query .= "ORDER BY 1";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);

        // dgvHP.Rows.Add(data.Rows(i).Item("KODETK").ToString, _
        //                       data.Rows(i).Item("NAMATK").ToString)
    }

    public function isiDataDatatables($periode){
        $countTempHP1 = DB::select("SELECT COUNT(*) FROM tbtemp_hp1")[0]->count;

        if($countTempHP1 > 0){
            $query = "";
            $query .= "SELECT DISTINCT TKO_KODEOMI AS KODETK, TKO_NAMAOMI AS NAMATK, ";
            $query .= "CASE ";
            $query .= "WHEN KODETOKO IS NOT NULL THEN 'OK' ";
            $query .= "WHEN KODETOKO IS NULL THEN 'BELUM TRANSFER' ";
            $query .= "END AS STAT, TGL AS PERIODE ";
            $query .= "FROM tbmaster_tokoigr ";
            $query .= "LEFT JOIN tbtemp_hp1 ON tko_kodeomi = kodetoko ";
            $query .= "WHERE tko_kodesbu = 'I' ";
            $query .= "AND TKO_FLAGKPH = 'Y' ";
            $query .= "AND recid IS NULL ";
            $query .= "AND tko_kodeigr = '" . session("KODECABANG") . "' ";
            if($periode !== "0"){
                $query .= "AND tgl = '" . $periode . "' ";
            }
            $query .= "ORDER BY 1";
        } else {
            $query = "";
            $query .= "SELECT DISTINCT TKO_KODEOMI AS KODETK, TKO_NAMAOMI AS NAMATK, ";
            $query .= "CASE ";
            $query .= "WHEN KODETOKO IS NOT NULL THEN 'OK' ";
            $query .= "WHEN KODETOKO IS NULL THEN 'BELUM TRANSFER' ";
            $query .= "END AS STAT, TGL AS PERIODE ";
            $query .= "FROM tbmaster_tokoigr ";
            $query .= "LEFT JOIN tbtemp_hp ON tko_kodeomi = kodetoko ";
            $query .= "WHERE tko_kodesbu = 'I' ";
            $query .= "AND TKO_FLAGKPH = 'Y' ";
            $query .= "AND recid IS NULL ";
            $query .= "AND tko_kodeigr = '" . session("KODECABANG") . "' ";
            if($periode !== "0"){
                $query .= "AND tgl = '" . $periode . "' ";
            }
            $query .= "ORDER BY 1";
        }
        $data = DB::select($query);

        return ApiFormatter::success(200, "success isi data", $data);
    }

    public function actionGlobalDownloadPdf(Request $request){
        $fileName = $request->fileName;
        $userDirectory = storage_path('temp_pdf') . "/" . session("userid");
        $filePath = $userDirectory . "/" . $fileName;

        if (!File::exists($filePath)) {
            return ApiFormatter::error(400, "File Not found.");
        }

        $fileContent = file_get_contents($filePath);

        File::deleteDirectory($userDirectory, false);

        return response($fileContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');

    }


    public function actionCheckPath(Request $request){
        $Periode = $request->blnPeriode . $request->thnPeriode;
        $numProses = $this->getCountLOG($Periode);

        if($numProses == 3){
            return ApiFormatter::error(400, "Periode " . $request->periode . " sudah pernah di proses..");
        }

        if($this->ORADataFound('tbmaster_kph', "pid = '$Periode' AND t_spd > '0' ")){
            return ApiFormatter::error(400, "Bersihkan data terlebih dahulu di TBMASTER_KPH");
        }

        $combined = $request->blnPeriode . $request->thnPeriode;
        if((int)$request->blnPeriode < 10 ){
            $blnPeriode = substr($combined, 0, 1);
        } else {
            $blnPeriode = substr($combined, 0, 2);
            if ($blnPeriode == "10") {
                $blnPeriode = "A";
            } elseif ($blnPeriode == "11") {
                $blnPeriode = "B";
            } else {
                $blnPeriode = "C";
            }
        }

        switch ($request->thnPeriode) {
            case '2015':
                $thnPeriode = 'F';
                break;
            case '2016':
                $thnPeriode = 'G';
                break;
            case '2017':
                $thnPeriode = 'H';
                break;
            case '2018':
                $thnPeriode = 'I';
                break;
            case '2019':
                $thnPeriode = 'J';
                break;
            case '2020':
                $thnPeriode = 'K';
                break;
            case '2021':
                $thnPeriode = 'L';
                break;
            case '2022':
                $thnPeriode = 'M';
                break;
            case '2023':
                $thnPeriode = 'N';
                break;
            case '2024':
                $thnPeriode = 'O';
                break;
            case '2025':
                $thnPeriode = 'P';
                break;
            default:
                $thnPeriode = '-';
        }

        $kodeCabang = session('KODECABANG');

        $query = "";
        $query .= "SELECT DISTINCT TKO_KODEOMI KODETK, TKO_NAMAOMI NAMATK ";
        $query .= "FROM tbmaster_tokoigr ";
        $query .= "WHERE tko_kodesbu = 'I' ";
        $query .= "AND tko_kodeigr = '" . $kodeCabang . "' ";
        $query .= "AND tko_flagkph = 'Y' ";
        $query .= "ORDER BY 1";

        // Execute the query and get the results
        $dt = DB::select($query);

        if(count($dt) < 1){
            return ApiFormatter::error(400, "Master Toko IGR Kosong!");
        }

        $matchingFiles = [];
        foreach ($request->file('files') as $file) {
            $filename = $file->getClientOriginalName();
            $pattern = "/^HP....{$blnPeriode}{$thnPeriode}/";

            if (preg_match($pattern, $filename)) {
                $matchingFiles[] = [
                    'filename' => $filename,
                ];
            }
        }

        if(count($matchingFiles) < 1){
            return ApiFormatter::error(400, "File dengan Periode Tidak Valid");
        }

        $data['checkAbsensi'] = 1;
        $matchingDt = [];
        foreach ($dt as $keyDt => $item){
            $matchingDt[] = [
                'kodeToko' => $item->kodetk,
                'namaToko' => $item->namatk,
                'filename' => '-',
                'isFound' => 0
            ];
            foreach($matchingFiles as $key => $file){
                $fName = "";
                $fName = explode('.', $file['filename'], 2)[0];
                if($item->kodetk == substr($fName, 2, 4)){
                    $matchingDt[$keyDt]['filename'] = $file['filename'];
                    $matchingDt[$keyDt]['isFound'] = 1;
                } else {
                    $data['checkAbsensi'] = 0;
                }
            }
        }

        $data['matchingDt'] = $matchingDt;

        return ApiFormatter::success(200, 'Check Path berhasil dilakukan', $data);
    }

    public function actionProses(HistoryProdukRequest $request){
        DB::beginTransaction();
        try{        
            $formattedDate = $request->periode . '-01';
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
                    break;
                case '2016':
                    $thnPeriode = 'G';
                    break;
                case '2017':
                    $thnPeriode = 'H';
                    break;
                case '2018':
                    $thnPeriode = 'I';
                    break;
                case '2019':
                    $thnPeriode = 'J';
                    break;
                case '2020':
                    $thnPeriode = 'K';
                    break;
                case '2021':
                    $thnPeriode = 'L';
                    break;
                case '2022':
                    $thnPeriode = 'M';
                    break;
                case '2023':
                    $thnPeriode = 'N';
                    break;
                case '2024':
                    $thnPeriode = 'O';
                    break;
                case '2025':
                    $thnPeriode = 'P';
                    break;
                default:
                    $thnPeriode = '-';
            }

            $this->initializeFile($blnPeriode, $thnPeriode, $request->file('files'));
            DB::commit();
            return ApiFormatter::success(200, 'Proses berhasil dilakukan');
        } catch (HttpResponseException $e) {
            throw new HttpResponseException($e->getResponse());
        }catch(\Exception $e){

            DB::rollBack();

            $message = "Oops! Something wrong ( $e )";
            throw new HttpResponseException(ApiFormatter::error(400, $message));
            return ApiFormatter::error(400, $message);
        }
    }

    private function initializeFile($blnPeriode, $thnPeriode, $files){
        $filePath = storage_path('temp_history_produk');
        if (!file_exists($filePath)) {
            File::makeDirectory($filePath);
        }
        $filePath = storage_path('temp_history_produk') . "/" . session("userid");
        if (!file_exists($filePath)) {
            File::makeDirectory($filePath);
        } else {
            File::deleteDirectory($filePath, false);
            File::makeDirectory($filePath, 0755, true);
        }
        foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            //! NOTE IRVAN | blnPriode Dummy {5}
            $pattern = "/^HP....{$blnPeriode}{$thnPeriode}/";

            if (preg_match($pattern, $filename)) {
                $newName = pathinfo($filename, PATHINFO_FILENAME) . '.csv';
                $file->move($filePath, $newName);
            }
        }

        $filePaths = File::files($filePath);

        foreach($filePaths as $matchingFile){
            $filename = pathinfo($matchingFile, PATHINFO_BASENAME);
            $fName = "";
            $fName = explode('.', $filename, 2)[0];

            $charAtPos7 = substr($filename, 6, 1);
            if ($charAtPos7 === "A") {
                $tglFile = "10" . $thnPeriode;
            } elseif ($charAtPos7 === "B") {
                $tglFile = "11" . $thnPeriode;
            } elseif ($charAtPos7 === "C") {
                $tglFile = "12" . $thnPeriode;
            } else {
                $tglFile = $charAtPos7 . $thnPeriode;
            }

            $substring = substr($fName, 2, 4);
            $isDataExists = $this->ORADataFound('tbtemp_hp1', "tgl = '$tglFile' and kodetoko = '$substring' ");

            if(!$isDataExists){
                foreach ($filePaths as $file) {
                    $filePath = $file->getPathname();
                    $data = Excel::toArray([], $filePath);
    
                    // Process your data as needed
                    $headerRow = $data[0][0];
                    $dataRows = array_slice($data[0], 1);
                    $dataCSV = [];
    
                    foreach ($dataRows as $rowData) {
                        $rowDataAssoc = [];
                        foreach ($headerRow as $index => $header) {
                            $rowDataAssoc[$header] = $rowData[$index] ?? null;
                        }
    
                        $dataCSV[] = $rowDataAssoc;
                    }
                }
                
                if($this->ORADataFound('tbmaster_tokoigr', "tko_kodeomi = '$substring' AND tko_flagkph = 'Y'")){
                    if($this->ORADataFound('tbtemp_hp1', "kodetoko = '$substring'") == false){
                        if(count($dataCSV) > 0){
                            foreach ($dataCSV as $row){
                                DB::table('tbtemp_hp1')->insert([
                                    'recid' => $row['RECID'],
                                    'prdcd' => $row['PRDCD'],
                                    'ptag' => $row['PTAG'],
                                    'kons' => $row['KONS'],
                                    'hpp' => $row['HPP'],
                                    'min_disp' => $row['MIN_DISP'],
                                    'kap_disp' => $row['KAP_DISP'],
                                    'minor' => $row['MINOR'],
                                    'spd' => $row['SPD'],
                                    'hp' => $row['HP'],
                                    'aqty' => $row['AQTY'],
                                    'bqty' => $row['BQTY'],
                                    'cqty' => $row['CQTY'],
                                    'arp' => $row['ARP'],
                                    'brp' => $row['BRP'],
                                    'crp' => $row['CRP'],
                                    'st01' => $row['ST01'],
                                    'st02' => $row['ST02'],
                                    'st03' => $row['ST03'],
                                    'saldo' => $row['SALDO'],
                                    'kodetoko' => $substring, 
                                    'tgl' => $tglFile, 
                                ]);
                            }

                        }
                    }   
                }
            }
        }

        $query = "";
        $query .= "SELECT COUNT(DISTINCT TKO_KODEOMI) ";
        $query .= "FROM tbmaster_tokoigr ";
        $query .= "WHERE tko_kodesbu = 'I' ";
        $query .= "AND tko_kodeigr = '" . session("KODECABANG") . "' ";
        $query .= "AND TKO_FLAGKPH = 'Y' ";
        $jmlToko = DB::select($query);

        $countTempHp1 = DB::select("select COUNT(DISTINCT KODETOKO) from tbtemp_hp1");

        if($countTempHp1[0]->count !== $jmlToko[0]->count){
            throw new HttpResponseException(ApiFormatter::error(400, "Data Toko IDM Tidak sesuai dengan master toko IGR!"));
        }

        $data = DB::select("
            SELECT 
                RECID,
                PRDCD,
                PTAG,
                KONS,
                TO_NUMBER(HPP,'99999999.9999') AS HPP1,
                MIN_DISP::numeric AS MIN_DISP,
                KAP_DISP::numeric AS KAP_DISP,
                MINOR,
                TO_NUMBER(SPD,'9999999.999999999') AS SPD1,
                HP,
                AQTY,
                BQTY,
                CQTY,
                ARP,
                BRP,
                CRP,
                ST01,
                ST02,
                ST03,
                SALDO,
                KODETOKO,
                TGL
            FROM TBTEMP_HP1
        ");

        foreach ($data as $row) {
            DB::table('tbtemp_hp')->insert([
                'recid' => $row->recid,
                'prdcd' => $row->prdcd,
                'ptag' => $row->ptag,
                'kons' => $row->kons,
                'hpp' => $row->hpp1,
                'min_disp' => $row->min_disp,
                'kap_disp' => $row->kap_disp,
                'minor' => $row->minor,
                'spd' => $row->spd1,
                'hp' => $row->hp,
                'aqty' => $row->aqty,
                'bqty' => $row->bqty,
                'cqty' => $row->cqty,
                'arp' => $row->arp,
                'brp' => $row->brp,
                'crp' => $row->crp,
                'st01' => $row->st01,
                'st02' => $row->st02,
                'st03' => $row->st03,
                'saldo' => $row->saldo,
                'kodetoko' => $row->kodetoko,
                'tgl' => $row->tgl,
            ]);
        }

        DB::statement($query);

        DB::table('tbtemp_hp1')->truncate();
        return true;
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
        $request->validate([
            'excel_file' => 'required|file',
        ]);

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

        $file = $request->file('excel_file');

        $originalName = $file->getClientOriginalName();
        $newName = pathinfo($originalName, PATHINFO_FILENAME) . '.zip';

        $filePath = storage_path('temp_extract_zip');
        if (!file_exists($filePath)) {
            File::makeDirectory($filePath);
        }
        $filePath = storage_path('temp_extract_zip') . "/" . session("userid");
        if (!file_exists($filePath)) {
            File::makeDirectory($filePath);
        } else {
            File::deleteDirectory($filePath, false);
            File::makeDirectory($filePath, 0755, true);
        }

        if($request->pilUpload !== "PINDAH SUPPLY"){
            $rarPath = $filePath . "/" . $newName;
            $file->move($filePath, $newName);
    
            $zip = new ZipArchive;
            if ($zip->open($rarPath) === TRUE) {
                $zip->extractTo($filePath);
                $zip->close();
            } else {
                return response()->json(['error' => 'Failed to open ZIP file'], 400);
            }
    
            File::delete($rarPath);

        } else {
            $file->move($filePath, $file->getClientOriginalName());
        }
        
        $filePaths = File::files($filePath);

        foreach ($filePaths as $file) {
            $filePath = $file->getPathname();
            
            $data = Excel::toArray([], $filePath);

            // Process your data as needed
            $headerRow = $data[0][0];
            $dataRows = array_slice($data[0], 1);
            $dataCSV = [];

            foreach ($dataRows as $rowData) {
                $rowDataAssoc = [];
                foreach ($headerRow as $index => $header) {
                    $rowDataAssoc[$header] = $rowData[$index] ?? null;
                }

                $dataCSV[] = $rowDataAssoc;
            }
        }
        
        //! START FUNCTION prosesData

        if($request->pilUpload == 'MINOR'){
            $columns = ['PRDCD', 'DESC', 'FRAC', 'MINOR'];

            $processedData = [];

            foreach ($dataCSV as $row) {
                $processedRow = [];
                foreach ($columns as $index => $column) {
                    switch ($column) {
                        case 'PRDCD':
                            $processedRow[$column] = str_replace([",", "'"], [".", ""], $row['PLUIDM']);
                            break;
                        case 'DESC':
                            $processedRow[$column] = str_replace([",", "'"], [".", ""], $row['PLUIGR']);
                            break;
                        case 'FRAC':
                            $value = str_replace([",", "'"], [".", ""], $row['TAG']);
                            $processedRow[$column] = $value === "" ? 0 : (double)$value;
                            break;
                        case 'MINOR':
                            $value = str_replace([",", "'"], [".", ""], $row['MINOR']);
                            $processedRow[$column] = $value === "" ? 0 : (double)$value;
                            break;
                        default:
                            break;
                    }
                }
                $processedData[] = $processedRow;
            }

            DB::delete("DELETE FROM TBTEMP_MINORTK");
            foreach($processedData as $item){   
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


            $ip = $this->getIP();
            foreach($dataCSV as $item){   
                //! INSERT TBTEMP_PLUIDM2 - PENGGANTI BULKCOPY
                DB::table('TBTEMP_PLUIDM2')
                    ->insert([
                        'idm_pluidm' => $item['PLUIDM'],
                        'idm_pluigr' => $item['PLUIGR'],
                        'idm_tag' => $item['TAG'],
                        'idm_renceng' => $item['RENCENG'],
                        'idm_minor' => $item['MINOR'],
                        'idm_kdidm' => $item['KDIDM'],
                        'ip' => $ip,
                    ]);
            }

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
        } else {
            //? PINDAH SUPPLY
            $today = Carbon::today();

            if (count($dataCSV) > 0) {
                foreach ($dataCSV as $row) {
                    $cekTgl = $row['TGLPINDAH'];
                    $newTgl = null;

                    try {
                        $newTgl = Carbon::createFromFormat('d-m-Y', $cekTgl);
                    } catch (\Exception $e) {
                        return ApiFormatter::error(400, 'Format Tanggal Pindah Supply DD-MM-YYYY');
                    }

                    // Check date condition
                    if ($newTgl->copy()->subDays(2)->isAfter($today)) {
                        return ApiFormatter::error(400, 'Tanggal Pindah Supply tidak boleh lebih dari 2 hari dari Tanggal Upload');
                    }
                }

                // Truncate the table
                // DB::table('temp_pindahsupply')->truncate();

                // Insert dataCSV
                DB::table('temp_pindahsupply')->insert([
                    "PRDCD" => "0215300",
                    "TGLPINDAH" => "20-02-2024"
                ]);
            } else {
                return ApiFormatter::error(400, 'Tidak ada Data!');
            }
        
        }

        return ApiFormatter::success(200, "UPLOAD CSV BERHASIL!");

        //! END FUNCTION prosesData
    }


    private function insertData($table, $data, $periodeProses, $periodeFile){
        $periode = $this->getLastPeriod();

        DB::select("DELETE FROM $table");

        foreach($data as $item){
            DB::table($table)
                ->insert([
                    'KODEIGR' => session("KODECABANG"),
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

        $dtCek = DB::table('tbmaster_kph')
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
            $query .= "H.KSL_MEAN * 4 KPH_4, ";
            $query .= "H.KSL_MEAN * PRS_KPHCONST KPH_CONST,  ";
            $query .= "CASE WHEN T_SPD = 0 THEN '*' ELSE '' END PRD ";
            $query .= "FROM tbmaster_kph H ";
            $query .= "LEFT JOIN TBMASTER_PRODCRM R ";
            $query .= "  ON H.PRDCD = R.PRC_PLUIDM ";
            $query .= "LEFT JOIN TBMASTER_PRODMAST P ";
            $query .= "  ON R.PRC_PLUIGR = P.PRD_PRDCD  ";
            $query .= "LEFT JOIN TBMASTER_PERUSAHAAN ";
            $query .= "  ON PRS_KODEIGR = H.KODEIGR";
            $query .= " WHERE H.PID = '" . $periode . "' ORDER BY 13,2 ";
        }else{
            $query = '';
            $query .= "SELECT H.PRDCD PLUIDM, R.PRC_PLUIGR PLUIGR,   ";
            $query .= "coalesce(P.PRD_DESKRIPSIPENDEK,'-') DESKRIPSI,   ";
            $query .= "H.KSL_MEAN KPH, H.MINOR, R.PRC_KODETAG TAG_CRM,  ";
            $query .= "P.PRD_KODETAG TAG_PRD, coalesce(P.PRD_MINORDER,0) MINOR_IGR,  ";
            $query .= "R.PRC_MINORDER MINOR_CRM, coalesce(P.PRD_FRAC,0) FRAC,  ";
            $query .= "H.KSL_MEAN * 3 KPH_3, ";
            $query .= "H.KSL_MEAN * 4 KPH_4, ";
            $query .= "H.KSL_MEAN * PRS_KPHCONST KPH_CONST,  ";
            $query .= "CASE WHEN T_SPD = 0 THEN '*' ELSE '' END PRD ";
            $query .= "FROM tbmaster_kph H ";
            $query .= "LEFT JOIN TBMASTER_PRODCRM R ";
            $query .= "  ON H.PRDCD = R.PRC_PLUIDM ";
            $query .= "LEFT JOIN TBMASTER_PRODMAST P ";
            $query .= "  ON R.PRC_PLUIGR = P.PRD_PRDCD  ";
            $query .= "LEFT JOIN TBMASTER_PERUSAHAAN ";
            $query .= "  ON PRS_KODEIGR = H.KODEIGR";
            $query .= "WHERE H.PID = '" . $periode . "' ORDER BY 13,2 ";
        }

        $data['data'] = DB::select($query);
        $data['perusahaan'] = DB::select("select prs_kodeigr kode_igr, PRS_NAMACABANG, coalesce(PRS_KPHCONST,1) V_KPH from tbmaster_perusahaan");
        $data['jmltokoProses'] = DB::select("select count(distinct kodetoko) JML_TOKO_PROSES from tbtemp_hp where tgl = '" . $periode . "'");
        $dt = DB::select("SELECT coalesce(JML_TOKO,0) JMLTOKO, PERIODE FROM TEMP_LOG_HP WHERE PERIODE = '" . $periode . "' AND PROSES = 'P2'");
        $periode = $dt[0]->periode;
        $length = strlen($periode);
        $lastFourDigits = substr($periode, -4);
        $remainingPart = substr($periode, 0, $length - 4);
        $data['periode'] = $remainingPart . '-' . $lastFourDigits;
        $data['jmlToko'] = $dt[0]->jmltoko;

        $nama_file = "REPORT_KPH_" . Carbon::now()->format('Ymd_His') . ".pdf";
        $pdf = PDF::loadView('pdf.history-produk-report-kph', $data);
        $pdf->setPaper('letter', 'landscape');

        $filePath = storage_path('temp_pdf');
        if (!file_exists($filePath)) {
            File::makeDirectory($filePath);
        }
        $filePath = storage_path('temp_pdf') . "/" . session("userid");
        if (!file_exists($filePath)) {
            File::makeDirectory($filePath);
        } else {
            File::deleteDirectory($filePath, false);
            File::makeDirectory($filePath, 0755, true);
        }

        $filePath = $filePath . "/" . $nama_file;

        file_put_contents($filePath, $pdf->output());

        return ApiFormatter::success(200, "Report KPH Berhasil Didownload", $nama_file);
    }

    public function datatablesReportKPH(){
        if($this->setKode == 'KPH'){
            $query = "Select distinct coalesce(TGL,'X') PERIODE FROM TBTEMP_HP";
        }else{
            $query = "select distinct CAST(coalesce(PID,'X')as INT) PERIODE FROM tbmaster_kph ORDER BY 1";
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
