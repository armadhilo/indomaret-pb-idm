<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', '0');

use App\Exports\DspbRotiExport;
use App\Helper\ApiFormatter;
use App\Helper\DatabaseConnection;
use App\Http\Requests\CetakDspbRequest;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class DspbRotiController extends Controller
{

    public function __construct(Request $request){
        DatabaseConnection::setConnection(session('KODECABANG'), "PRODUCTION");
    }

    public function index(){
        $this->createTableLogNPB();
        return view('menu.dspb-roti');
    }

    public function getClusterMobil(){
        $data = DB::select("SELECT distinct cri_kodecluster FROM CLUSTER_ROTI_IDM order by 1");

        return ApiFormatter::success(200, 'Berhasil menampilkan cluster mobil', $data);
    }

    public function datatables($date, $cluster){
        $query = '';
        $query .= "SELECT ROW_NUMBER() OVER() NOURUT,  ";
        $query .= "  A.*  ";
        $query .= "FROM  ";
        $query .= "  (SELECT ";
        $query .= "    CASE WHEN MOD(SUM(HDR_FLAG::INT),5) = 0 THEN 'SELESAI DSPB' ELSE 'SIAP DSPB' END STATUS,  ";
        $query .= "    HDR_KODETOKO KODETOKO,  ";
        $query .= "    HDR_TGLTRANSAKSI TGLTRANS,  ";
        $query .= "    COUNT(HDR_NOPB) NOPB,  ";
        $query .= "    HDR_TGLPB TGLPB,  ";
        $query .= "    SUM(HDR_ITEMVALID) ITEMVALID,  ";
        $query .= "    SUM(HDR_RPHVALID) RPHVALID  ";
        $query .= "  FROM TBTR_HEADER_ROTI  ";
        $query .= "  JOIN CLUSTER_ROTI_IDM  ";
        $query .= "  ON CRI_KODETOKO     = HDR_KODETOKO  ";
        $query .= "  AND CRI_KODECLUSTER = '" . $cluster . "'  ";
        // $query .= "  AND DATE_TRUNC('DAY',HDR_TGLTRANSAKSI) = TO_DATE('" . $date . "','YYYY-MM-DD')  ";
        $query .= "  GROUP BY HDR_KODETOKO, HDR_TGLTRANSAKSI, HDR_TGLPB  ";
        $query .= "  ORDER BY HDR_KODETOKO  ";
        $query .= "  ) A  ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function actionCetakDspb(CetakDspbRequest $request){
        //! LOOPING DATATABLES
        $thnNow = Carbon::now()->format('y');
        $noRekap = DB::select("SELECT NEXTVAL('SEQ_ROTI')")[0]->nextval;


        $noRekap = $thnNow . str_pad($noRekap, 5, "0", STR_PAD_LEFT);
        if(!File::exists(storage_path('temp_dspb'))){
            File::makeDirectory(storage_path('temp_dspb'));
        }

        $tempDir = storage_path('temp_dspb/' . Carbon::now()->format('Ymd_His'));
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir);
        } else {
            return ApiFormatter::error(400, "Harap Tunggu 30 detik, lalu ulang Proses Cetak DSPB");
        }

        //! CHECK HANYA YANG -> SIAP DSPB
        foreach($request->datatables as $item){
            if($item['status'] == 'SIAP DSPB'){
                $check = $this->DSPB_ROTI($item['kodetoko'], $item['tgltrans'], $noRekap, $request->cluster, $request->qr_code, $tempDir);
                if($check !== true){
                    File::deleteDirectory($tempDir);
                    return ApiFormatter::error(400, json_decode($check->getContent(), true)["message"]);
                }
            }
        }
        //! DELETE TEMP FOLDER
        
        $this->cetakReportRekap($noRekap, $request->cluster, $tempDir);

        $zip = new ZipArchive();
        $zipDirectory = $tempDir . '/DSPB ROTI.zip';
        if ($zip->open($zipDirectory, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            
            $zip->close();
            $data['temp'] = basename($tempDir);
            return ApiFormatter::success(200, "Berhasil DSPB ROTI", $data);
        } else {
            File::deleteDirectory($tempDir);
            return ApiFormatter::error(400, "Gagal Membuat Zip");
        }
    
    }

    public function getZipFile($temp){
        $zipDirectory = storage_path('temp_dspb/' . $temp . '/DSPB ROTI.zip');
        $zipContent = file_get_contents($zipDirectory);
        File::deleteDirectory(storage_path('temp_dspb/' . $temp));
        $headers = [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="DSPB ROTI.zip"',
        ];
        return response($zipContent, 200, $headers);
    }

    private function cetakReportRekap($noRekap, $cluster, $tempDir){
        $query = '';
        $query .= "select distinct TKO_KODEOMI KODETOKO, TKO_NAMAOMI NAMATOKO, IKL_IDTRANSAKSI NODSPB, DSP_NO_REKAP NOREKAP, ";
        $query .= "CRI_KODECLUSTER KDCLUSTER, null ENCRYPT, ikl_tglbpd tgl_dspb ";
        $query .= "from TBTR_HEADER_ROTI join CLUSTER_ROTI_IDM on HDR_KODETOKO = CRI_KODETOKO ";
        $query .= "JOIN TBTR_IDMKOLI on HDR_NOPB = IKL_NOPB and HDR_KODETOKO = IKL_KODEIDM ";
        $query .= "join TBMASTER_TOKOIGR on HDR_KODETOKO = TKO_KODEOMI and TKO_KODESBU = 'I' ";
        $query .= "join TBTR_REKAP_DSPB_ROTI on DSP_NODSPB = IKL_IDTRANSAKSI and CRI_KODECLUSTER = DSP_CLUSTER ";
        $query .= "where CRI_KODECLUSTER = '" . $cluster . "' and HDR_FLAG = '5' and DSP_NO_REKAP = '" . $noRekap . "' ";
        $data['data'] = DB::select($query); 

        if(!count($data['data'])){
            File::deleteDirectory($tempDir);
            throw new HttpResponseException(ApiFormatter::error(400, 'Tidak ada data report rekap'));
        }

        //! PERUSAHAAN
        $data['perusahaan'] = DB::table('tbmaster_perusahaan')
        ->select('prs_kodeigr','kode_igr','PRS_NAMACABANG')
        ->first();

        $pdf = PDF::loadView('pdf.dspb-surat-jalan', $data);
        $pdfContent = $pdf->output();
        file_put_contents($tempDir . "/DSPB REKAP SURAT JALAN.pdf", $pdfContent);
        return true;

        //! ENCRPYT YANG GATAU CARA ENCRYPT NYA

        //! report view -> rptRekapDSPBRoti
    }

    private function createTableLogNPB(){
        //! CEK TABLE LOG_NPB
        $count = DB::table('information_schema.columns')
            ->whereRaw("upper(table_name) = 'LOG_NPB'")
            ->count();

        if($count == 0){
            $query = '';
            $query .= "CREATE TABLE log_npb ( ";
            $query .= "  npb_tgl_proses  DATE, ";
            $query .= "  npb_kodetoko    VARCHAR(5), ";
            $query .= "  npb_nopb        VARCHAR(20), ";
            $query .= "  npb_tglpb       DATE, ";
            $query .= "  npb_nodspb      VARCHAR(20), ";
            $query .= "  npb_file        VARCHAR(100), ";
            $query .= "  npb_jenis       VARCHAR(10), ";
            $query .= "  npb_url         VARCHAR(100), ";
            $query .= "  npb_response    CLOB, ";
            $query .= "  npb_jml_item    NUMERIC, ";
            $query .= "  npb_create_web  VARCHAR(12), ";
            $query .= "  npb_create_csv  VARCHAR(12), ";
            $query .= "  npb_kirim       VARCHAR(12), ";
            $query .= "  npb_confirm     VARCHAR(30), ";
            $query .= "  npb_tgl_retry   DATE, ";
            $query .= "  npb_jml_retry   NUMERIC, ";
            $query .= "  npb_create_by   VARCHAR(20),  ";
            $query .= "  npb_create_dt   DATE, ";
            $query .= "  npb_modify_by   VARCHAR(20), ";
            $query .= "  npb_modify_dt   DATE ";
            $query .= ") ";
            DB::insert($query);
        }

        return true;
    }

    private function DSPB_ROTI($kodetoko, $tgltrans, $noRekap, $cbCluster, $qrCode = 0, $tempDir){
        File::makeDirectory($tempDir . '/zip');
        DB::beginTransaction();
	    try{

            //! DEFAULT
            $noKoli = "";
            $taxNum = "";
            $nmRpb = "";
            $nmNpb = "";
            $flagCrtPjk = "N";
            $_koli = "";

            // Execute the first query to get the current year
            $thnPb = DB::select("SELECT TO_CHAR(CURRENT_DATE, 'YY')")[0]->to_char;

            // Execute the second query to get the next value from the sequence
            $noDspb = DB::select("SELECT NEXTVAL('SEQ_NPB')")[0]->nextval;

            $kodeGudang = DB::table('tbmaster_perusahaan')
                ->select(DB::raw("'GI' || prs_kodeigr as concatenated_value"))
                ->first();

            $tglServer = DB::select("SELECT NOW() as now")[0]->now;
            // Concatenate the values
            $noDspb = $thnPb . str_pad($noDspb, 5, "0", STR_PAD_LEFT);

            //! CHECK DATA
            $dt = DB::table('tbtr_header_roti')
                ->selectRaw("distinct hdr_nopb, hdr_tglpb")
                ->where('hdr_kodetoko', $kodetoko)
                ->whereDate('hdr_tgltransaksi', $tgltrans)
                ->get();

            if(Count($dt)){
                $nmPb = "NPR" . $kodeGudang->concatenated_value . $kodetoko . Carbon::parse($tglServer)->format('YmdHi');
                $nmRpb = "XPR" . $kodeGudang->concatenated_value . $kodetoko . Carbon::parse($tglServer)->format('YmdHi');

                $dtCek = DB::table('master_supply_idm')
                    ->select('msi_kodedc')
                    ->where('msi_kodetoko', $kodetoko)
                    ->first();

                $kodeDCIDM = empty($dtCek) ? '' : $dtCek->msi_kodedc;

                foreach($dt as $item){
                    $noPb = $item->hdr_nopb;
                    $tglPb = Carbon::createFromFormat('Y-m-d H:i:s', $item->hdr_tglpb)->toDateString();

                    //! CHECK
                    $query = '';
                    $query .= "SELECT '*' recid, ";
                    $query .= "  NULL rtype, ";
                    $query .= "  " . $noDspb . "  docno, ";
                    $query .= "  ROW_NUMBER() OVER() seqno, ";
                    $query .= "  pbo_nopb picno, ";
                    $query .= "  NULL picnot, ";
                    $query .= "  TO_CHAR(pbo_tglpb, 'YYYY-MM-DD') pictgl, ";
                    $query .= "  pbo_pluomi prdcd, ";
                    $query .= "  (select prd_deskripsipendek from tbmaster_prodmast ";
                    $query .= "  where prd_prdcd = pbo_pluigr LIMIT 1) nama, ";
                    $query .= "  pbo_kodedivisi div, ";
                    $query .= "  pbo_qtyorder qty, ";
                    $query .= "  pbo_qtyrealisasi sj_qty, ";
                    $query .= "  pbo_hrgsatuan price, ";
                    $query .= "  pbo_ttlnilai gross, ";
                    $query .= "  pbo_ttlppn ppnrp, ";
                    $query .= "  pbo_hrgsatuan hpp, ";
                    $query .= "  pbo_kodeomi toko, ";
                    $query .= "  'R-' keter, ";
                    $query .= "  TO_CHAR(CURRENT_DATE, 'YYYY-MM-DD') tanggal1, ";
                    $query .= "  TO_CHAR(pbo_tglpb, 'YYYY-MM-DD') tanggal2, ";
                    $query .= "  pbo_nopb docno2, ";
                    $query .= "  NULL lt, ";
                    $query .= "  NULL rak, ";
                    $query .= "  NULL bar, ";
                    $query .= "  (SELECT 'GI' || prs_kodeigr ";
                    $query .= "  FROM tbmaster_perusahaan ";
                    $query .= "  LIMIT 1) kirim, ";
                    $query .= "  lpad(pbo_NOKOLI,12,'0') dus_no, ";
                    $query .= "  NULL TGLEXP, ";
                    $query .= "  COALESCE(prd_ppn, 0) ppn_rate, ";
                    $query .= "  COALESCE(prd_flagbkp1, 'N') BKP, ";
                    $query .= "  COALESCE(prd_flagbkp2,'N') SUB_BKP ";
                    $query .= "FROM tbmaster_pbomi ";
                    $query .= "JOIN TBMASTER_PRODMAST ";
                    $query .= "ON pbo_pluigr = prd_prdcd ";
                    $query .= "WHERE PBO_TGLPB = TO_DATE('" . $tglPb . "','YYYY-MM-DD')  ";
                    $query .= "AND pbo_nopb = '" . $noPb . "' ";
                    $query .= "AND pbo_kodeomi = '" . $kodetoko . "' ";
                    $query .= "AND pbo_qtyrealisasi > 0 ";
                    $query .= "AND pbo_recordid = '4'";
                    $query .= "AND pbo_nokoli like '06%'";
                    $check = DB::select($query);

                    if(count($check)){
                        $this->updateDb($noDspb,$tglPb,$kodetoko,$noPb);
                        $this->cetakSJP($kodetoko, $noDspb, $noPb, $tglPb);
                        $this->WriteCSV($tempDir, $nmPb, $check);
                    }
                }

                DB::table('tbtr_rekap_dspb_roti')
                    ->insert([
                        'sp_kodeigr' => session('KODECABANG'),
                        'dsp_no_rekap' => $noRekap,
                        'dsp_nodspb' => $noDspb,
                        'dsp_cluster' => $cbCluster,
                        'dsp_create_by' => session('userid'),
                        'dsp_create_dt' => Carbon::now(),
                    ]);

                //! SIMPAN NAMA NPB
                $this->simpanDSPB($nmNpb . ".ZIP", $kodetoko, $noPb, 0, 0, $noDspb, "R- PBROTI");

                if($kodeDCIDM <> ''){
                    $npbGudang = $kodeDCIDM;
                }else{
                    $query = "SELECT ws_DC FROM tbmaster_webservice WHERE ws_nama = 'NPB' ";
                    if($kodeDCIDM <> ''){
                        $query .= "AND ws_dc = '$kodeDCIDM'";
                    }

                    $npbGudang = DB::select($query)[0]['ws_url'];
                }

                //! dtHeader
                // dd(DB::select("SELECT * FROM tbmaster_pbomi LIMIT 10"));
                $query = '';
                $query .= "SELECT " . $noDspb . " docno,";
                $query .= "  TO_CHAR(CURRENT_DATE, 'dd-MM-YYYY') doc_date,";
                $query .= "  pbo_kodeomi toko,";
                $query .= "  (SELECT 'GI' || prs_kodeigr FROM tbmaster_perusahaan LIMIT 1) gudang, ";
                $query .= "  COUNT(DISTINCT pbo_pluomi) item, SUM(pbo_qtyrealisasi) qty, ";
                $query .= "  SUM(pbo_ttlnilai) gross, NULL koli, NULL kubikasi ";
                $query .= "FROM tbmaster_pbomi a  ";
                $query .= "WHERE  A.PBO_TGLPB = '" . $tglPb . "'  ";
                $query .= "AND pbo_kodeomi = '" . $kodetoko . "' ";
                $query .= "AND pbo_nokoli like '06%' ";
                $query .= "AND PBO_QTYREALISASI > 0 ";
                $query .= "GROUP BY pbo_tglpb, pbo_kodeomi ";
                $dtH = DB::select($query);

                $this->WriteCSV($tempDir, $nmRpb, $dtH);
                $zip = new ZipArchive();
                $zipDirectory = $tempDir . '/' . basename($tempDir) . '.zip';
                if ($zip->open($zipDirectory, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $files = glob($tempDir . '/zip/*.csv');
                    foreach ($files as $file) {
                        $zip->addFile($file, basename($file));
                    }
                    
                    $zip->close();
                    File::deleteDirectory($tempDir . '/zip/');
                } else {
                    File::deleteDirectory($tempDir);
                    throw new HttpResponseException(ApiFormatter::error(400, 'Gagal Membuat Zip'));
                }

                //! dtDetail
                $query = '';
                $query .= "SELECT '*' recid, ";
                $query .= "  NULL rtype, ";
                $query .= "  " . $noDspb . "  docno, ";
                $query .= "  ROW_NUMBER() OVER() seqno, ";
                $query .= "  pbo_nopb picno, ";
                $query .= "  NULL picnot, ";
                $query .= "  TO_CHAR(pbo_tglpb, 'dd-MM-YYYY') pictgl, ";
                $query .= "  pbo_pluomi prdcd, ";
                $query .= "  (SELECT prd_deskripsipendek FROM tbmaster_prodmast WHERE prd_prdcd = pbo_pluigr) nama, ";
                $query .= "  pbo_kodedivisi div, ";
                $query .= "  pbo_qtyorder qty, ";
                $query .= "  pbo_qtyrealisasi sj_qty, ";
                $query .= "  pbo_hrgsatuan price, ";
                $query .= "  pbo_ttlnilai gross, ";
                $query .= "  pbo_ttlppn ppnrp, ";
                $query .= "  pbo_hrgsatuan hpp, ";
                $query .= "  pbo_kodeomi toko, ";
                $query .= "  'R-' keter, ";
                $query .= "  TO_CHAR(IKL_TGLBPD, 'dd-MM-YYYY') tanggal1, ";
                $query .= "  TO_CHAR(pbo_tglpb, 'dd-MM-YYYY') tanggal2, ";
                $query .= "  pbo_nopb docno2, ";
                $query .= "  NULL lt, ";
                $query .= "  NULL rak, ";
                $query .= "  NULL bar, ";
                $query .= "  (SELECT 'GI' || prs_kodeigr ";
                $query .= "  FROM tbmaster_perusahaan ";
                $query .= "  LIMIT 1) kirim, ";
                $query .= "  lpad(pbo_NOKOLI,12,'0') dus_no, ";
                $query .= "  NULL TGLEXP, ";
                $query .= "  COALESCE(prd_ppn, 0) ppn_rate, ";
                $query .= "  COALESCE(prd_flagbkp1, 'N') BKP, ";
                $query .= "  COALESCE(prd_flagbkp2,'N') SUB_BKP ";
                $query .= "FROM tbmaster_pbomi ,TBTR_IDMKOLI, tbmaster_prodmast ";
                $query .= "WHERE PBO_QTYREALISASI > 0 ";
                $query .= "AND pbo_tglpb = TO_DATE(ikl_tglpb, 'YYYYMMdd')  ";
                $query .= "AND pbo_nopb = ikl_nopb ";
                $query .= "AND pbo_kodeomi = ikl_kodeidm ";
                $query .= "AND pbo_nokoli = ikl_nokoli ";
                $query .= "AND pbo_pluigr = prd_prdcd ";
                $query .= "AND ikl_registerrealisasi = '" . $noDspb . "'";
                $query .= "AND pbo_nokoli like '06%'";
                $dtD = DB::select($query);

                if(count($dtD) <= 0){
                    return ApiFormatter::error(400, 'Tidak ada data');
                }

                $query = "SELECT ws_url FROM tbmaster_webservice WHERE ws_nama = 'NPB' AND COALESCE(ws_aktif, 0) = 1 ";
                if($kodeDCIDM <> ''){
                    $query .= "AND ws_dc = '$kodeDCIDM'";
                }

                $checkNpbIP = DB::select($query);

                if(isset($npbIP)){

                    $npbIP = $checkNpbIP[0]['ws_url'];

                    $tglConfirm = null;
                    $npbRes = null;
                    $jmlItem = 0;

                    $okNPB = $this->insertToNPB(carbon::now(), $npbGudang, $nmNpb, $dtH, $dtD);
                    if($okNPB){
                        $tglConfirm = $okNPB['tglConfirm'];
                        $npbRes = $okNPB['npbRes'];
                        $jmlItem = $okNPB['jmlItem'];
                    }

                    $query = '';
                    $query .= "INSERT INTO log_npb ( ";
                    $query .= "   npb_tgl_proses, ";
                    $query .= "   npb_kodetoko, ";
                    $query .= "   npb_nopb, ";
                    $query .= "   npb_tglpb, ";
                    $query .= "   npb_nodspb, ";
                    $query .= "   npb_file, ";
                    $query .= "   npb_jml_item, ";
                    $query .= "   npb_jenis, ";
                    $query .= "   npb_url, ";
                    $query .= "   npb_response, ";
                    $query .= "   npb_create_web, ";
                    $query .= "   npb_create_csv, ";
                    $query .= "   npb_kirim, ";
                    $query .= "   npb_confirm, ";
                    $query .= "   npb_jml_retry, ";
                    $query .= "   npb_create_by,  ";
                    $query .= "   npb_create_dt ";
                    $query .= " ) VALUES ( ";
                    $query .= "   DATE_TRUNC('DAY',CURRENT_DATE ), ";
                    $query .= "   '" . $kodetoko . "', ";
                    $query .= "   '" . $noPb . "', ";
                    $query .= "   TO_DATE('" . $tglPb . "','YYYY-MM-DD'), ";
                    $query .= "   '" . $noDspb . "', ";
                    $query .= "   '" . $nmNpb . "', ";
                    $query .= "   '" . $jmlItem . "', ";
                    $query .= "   'ROTI', ";
                    $query .= "   '" . $npbIP . "', ";
                    $query .= "   '" . $npbRes . "', ";
                    $query .= "   '" . Carbon::now() . "', ";
                    $query .= "   '" . Carbon::now() . "', ";
                    $query .= "   '" . Carbon::now() . "', ";
                    $query .= "   '" . $tglConfirm . "', ";
                    $query .= "   0, ";
                    $query .= "   '" . session('userid') . "', ";
                    $query .= "   NOW() ";
                    $query .= " ) ";
                }

                //! REPORT PDF
                $check = $this->cetakReportToko($noDspb, $kodetoko, $tempDir);
                if($check !== true){
                    return ApiFormatter::error(400, json_decode($check->getContent(), true)["message"]);
                }

                return true;

                //! REPORT QR CODE
                //! INI DI SKIP INFO PAK EVAN 17/04/2024
                // If checkQR.Checked Then
                //     Create_QRCode(nmNpb, _
                //                   _toko, _
                //                   noDspb, _
                //                   dtD.Rows(0).Item("TANGGAL1").ToString.Replace("-", "/"), _
                //                   dtD.Rows.Count, _
                //                   dtD)
                // End If
            }
        } catch(\Exception $e){
            
            DB::rollBack();
            
            $message = "Oops! Something wrong ( $e )";
            return ApiFormatter::error(400, $message);
        }
    }

    private function cetakReportToko($noDspb, $kodetoko, $tempDir){
        $query = '';
        $query .= "SELECT DISTINCT HDR_KODETOKO TOKO, HDR_NOPB NOPB, ";
        $query .= "COUNT(DISTINCT PBO_PLUIGR) JMLITEM, ";
        $query .= "CASE UPPER(SUBSTR(REVERSE(SUBSTR(REVERSE(hdr_filepb), 1, POSITION('\' IN REVERSE(hdr_filepb))-1)),1,2)) ";
        $query .= "WHEN 'CM' THEN 'CAKE' WHEN 'MB' THEN 'MR. BREAD' WHEN 'GM' THEN 'PRIME BREAD' ELSE 'SARI ROTI' END TIPE, ";
        $query .= "count(case when prd_kodedivisi = '5' and prd_kodedepartement = '40' and prd_kodekategoribarang = '04' then null else pbo_pluigr end) as ITEM, ";
        $query .= "count(case when prd_kodedivisi||prd_kodedepartement||prd_kodekategoribarang = '54004' then pbo_pluigr else null end) as KRAT ";
        $query .= "FROM TBMASTER_PBOMI, TBTR_HEADER_ROTI, TBMASTER_PRODMAST ";
        $query .= "WHERE PBO_NOKOLI IN (SELECT DISTINCT IKL_NOKOLI FROM TBTR_IDMKOLI ";
        $query .= "WHERE IKL_REGISTERREALISASI = '" . $noDspb . "' AND IKL_KODEIDM = '" . $kodetoko . "' ) ";
        $query .= "AND pbo_pluigr = prd_prdcd ";
        $query .= "AND pbo_nokoli IS NOT NULL ";
        $query .= "AND PBO_NOPB = HDR_NOPB ";
        $query .= "AND PBO_KODEOMI = HDR_KODETOKO ";
        $query .= "AND DATE_TRUNC('DAY',PBO_TGLPB) = DATE_TRUNC('DAY',HDR_TGLPB) ";
        $query .= "AND HDR_KODETOKO = '" . $kodetoko . "' ";
        $query .= "GROUP BY HDR_KODETOKO, HDR_NOPB, HDR_FILEPB";

        $data['data'] = DB::select($query);

        if(!count($data['data'])){
            File::deleteDirectory($tempDir);
            return ApiFormatter::error(400, 'Tidak ada data report toko ' . $kodetoko);
        }

        //! HEADER
        //! ADA HEADER ENCRPYT YANG GATAU CARA ENCRYPT NYA
        $query = '';
        $query .= "SELECT null encrypt,IKL_KODEIGR KODE_IGR,ikl_nopb, ikl_nobpd,  ";
        $query .= "NULL reprint, ";
        $query .= "ikl_registerrealisasi dspb, ikl_tglbpd tgl_dspb ";
        $query .= "FROM tbtr_idmkoli  ";
        $query .= "WHERE ikl_registerrealisasi = '" . $noDspb . "' ";
        $query .= "AND ikl_kodeidm = '" . $kodetoko . "' ";
        $query .= "AND IKL_NOKOLI is not null  ";
        $query .= "AND IKL_NOKOLI like '06%'  ";
        $query .= "LIMIT 1 ";
        $data['header'] = DB::select($query);

        //! NAMA CABANG
        $data['namaCabang'] = DB::table('tbmaster_perusahaan')
            ->select('prs_namacabang')
            ->first()->prs_namacabang;

        //! TOKO
        $data['toko'] = DB::table('tbmaster_tokoigr')
            ->selectRaw("tko_kodeigr kode_igr ,tko_namaomi , tko_kodeomi")
            ->where([
                'tko_kodeomi' => $kodetoko,
                'tko_namasbu' => 'INDOMARET'
            ])->first();

        //! CLUSTER
        $data['cluster'] = DB::table('cluster_idm')
            ->selectRaw("cls_kode")
            ->where([
                'cls_toko' => $kodetoko,
            ])->first();

        //! GROUP
        $data['group'] = DB::table('cluster_idm')
            ->selectRaw("cls_group")
            ->where([
                'cls_toko' => $kodetoko,
            ])->first();

        $pdf = PDF::loadView('pdf.dspb-daftar-struk', $data);
        $pdfContent = $pdf->output();
        file_put_contents($tempDir . '/' . basename($tempDir) . ".pdf", $pdfContent);
        return true;

        //! report view -> rptDSPBRoti
    }

    private function Create_QRCode($filename,$toko,$nodspb,$tgldspb,$jmlrecord,$sourceDetail, $reprint = false, $rpt = null){
        //! BINGUNG FUNCTION GIMANA
    }

    private function simpanDSPB($namafile,$kodetoko,$nopb,$nopick,$nosj,$nodspb,$jenispb){
        $jenispb = strtoupper($jenispb);

        $dtCek = DB::table('tbhistory_dspb')
            ->where([
                'kodeigr' => session('KODECABANG'),
                'namafile' => $namafile,
                'kodetoko' => $kodetoko,
                'nopb' => $nopb,
                'nopick' => $nopick,
                'nosj' => $nosj,
                'nodspb' => $nodspb,
                'jenispb' => $jenispb,
            ])->count();

        if($dtCek == 0){

            $dtCek = DB::table('tbhistory_dspb')
            ->insert([
                'kodeigr' => session('KODECABANG'),
                'namafile' => $namafile,
                'kodetoko' => $kodetoko,
                'nopb' => $nopb,
                'nopick' => $nopick,
                'nosj' => $nosj,
                'nodspb' => $nodspb,
                'jenispb' => $jenispb,
                'createby' => session('userid'),
                'createdt' => Carbon::now(),
            ]);
        }
    }

    private function cetakSJP($kodetoko, $noDspb, $noPb, $tglPb){

        $paket = $this->getPaketIPP($kodetoko);

        if($paket != null){

            $check = DB::table('paket_ipp')
                ->where([
                    'pip_kodetoko' => $kodetoko,
                    'pip_recordid' => 1,
                ])
                ->whereNull('pip_nodspb')
                ->count();

            if($check > 0){
                $query = '';
                $query .= "UPDATE PAKET_IPP SET PIP_NODSPB = '" . $noDspb . "', ";
                $query .= "PIP_RECORDID = '3', ";
                $query .= "PIP_TGLDSPB = CURRENT_DATE  ";
                $query .= "WHERE PIP_KODETOKO = '" . $kodetoko . "' ";
                $query .= "AND PIP_RECORDID = '1' ";
                $query .= "AND PIP_NODSPB IS NULL ";
            }


            $this->DCPickingFinal($noDspb, $kodetoko, $noPb, $tglPb);
        }


    }

    private function writeCSV($tempDir, $nameFile, $check){
        $fileContent = Excel::raw(new DspbRotiExport($check), \Maatwebsite\Excel\Excel::CSV);
        file_put_contents($tempDir . '/zip/' . $nameFile . ".csv", $fileContent);
    }

    private function updateDb($noDspb,$tglPb,$kodetoko,$noPb){
        //! UPDATE IDM KOLI
        $query = '';
        $query .= "UPDATE TBTR_IDMKOLI  ";
        $query .= "SET IKL_REGISTERREALISASI = '" . $noDspb . "',  ";
        $query .= "IKL_NOBPD = '1' , ";
        $query .= "IKL_IDTRANSAKSI = '" . $noDspb . "',  ";
        $query .= "IKL_TGLBPD = date_trunc('day',current_date), ";
        $query .= "IKL_RECORDID = '1' ";
        $query .= "WHERE ikl_tglpb = TO_DATE('" . $tglPb . "','YYYY-MM-DD') ";
        $query .= "AND ikl_kodeidm = '" . $kodetoko . "' ";
        $query .= "AND ikl_nopb = '" . $noPb . "'   ";
        $query .= "AND ikl_nokoli like '06%' ";

        //! UPDATE TBMASTER_STOCK
        $query = '';
        $query .= "UPDATE tbmaster_stock ";
        $query .= "SET (st_intransit, st_saldoakhir) = ";
        $query .= "(SELECT (st_intransit - SUM(pbo_qtyrealisasi * case when prd_unit = 'KG' then 1 else prd_frac end)), ";
        $query .= "(st_saldoakhir - SUM(pbo_qtyrealisasi * case when prd_unit = 'KG' then 1 else prd_frac end)) ";
        $query .= "FROM tbmaster_pbomi, tbmaster_prodmast ";
        $query .= "WHERE pbo_kodeigr = prd_kodeigr ";
        $query .= "AND pbo_pluigr = prd_prdcd ";
        $query .= "AND pbo_kodeigr = st_kodeigr ";
        $query .= "AND SUBSTR(pbo_pluigr, 1, 6) || '0' = st_prdcd ";
        $query .= "AND date_trunc('day',PBO_TGLPB) = TO_DATE('" . $tglPb . "','YYYY-MM-DD')  ";
        $query .= "AND pbo_nopb = '" . $noPb . "' ";
        $query .= "AND pbo_kodeomi = '" . $kodetoko . "' ";
        $query .= "AND pbo_recordid = '4' ";
        $query .= "AND SUBSTR(PRD_PRDCD,1,6) || '0' = ST_PRDCD ) ";
        $query .= "WHERE st_kodeigr = '" . session('KODECABANG') . "' ";
        $query .= "AND st_lokasi = '01' ";
        $query .= "AND EXISTS( ";
        $query .= "SELECT 1 ";
        $query .= "FROM tbmaster_pbomi ";
        $query .= "WHERE pbo_kodeigr = st_kodeigr ";
        $query .= "AND SUBSTR(pbo_pluigr, 1, 6) || '0' = st_prdcd ";
        $query .= "AND date_trunc('day',PBO_TGLPB) = TO_DATE('" . $tglPb . "','YYYY-MM-DD')  ";
        $query .= "AND pbo_recordid = '4' ";
        $query .= "AND pbo_nopb = '" . $noPb . "' ";
        $query .= "AND pbo_kodeomi = '" . $kodetoko . "' ";
        $query .= "AND pbo_nokoli like '06%' ";
        $query .= ")    ";

        //! INSERT REALPB
        $query = '';
        $query .= "INSERT into tbtr_realpb (RPB_KODEIGR,RPB_NOKOLI,RPB_NOURUT,RPB_TGLDOKUMEN,RPB_NODOKUMEN,RPB_IDSURATJALAN,RPB_KODECUSTOMER,RPB_KODEOMI";
        $query .= ",RPB_PLU1,RPB_PLU2,RPB_HRGSATUAN,RPB_QTYORDER,RPB_QTYREALISASI,RPB_NILAIORDER,RPB_PPNORDER,RPB_TTLNILAI,RPB_TTLPPN,RPB_DISTRIBUTIONFEE,RPB_COST";
        $query .= ",RPB_QTYV,RPB_QTYBDR,RPB_HBDR,RPB_FLAG,RPB_CREATE_BY,RPB_CREATE_DT) ";
        $query .= "SELECT PBO_KODEIGR,PBO_NOKOLI, row_number() over(),PBO_TGLPB, PBO_NOPB, '" . $noDspb . "',PBO_KODEMEMBER,PBO_KODEOMI,PBO_PLUOMI,PBO_PLUIGR,PBO_HRGSATUAN,";
        $query .= "PBO_QTYORDER, PBO_QTYREALISASI,PBO_NILAIORDER,PBO_PPNORDER,PBO_TTLNILAI,PBO_TTLPPN,PBO_DISTRIBUTIONFEE,ST_AVGCOST,0,0,0,'4','" . session('userid') . "',now() ";
        $query .= "FROM tbmaster_pbomi  , tbmaster_STOCK   ";
        $query .= "WHERE PBO_KODEIGR = ST_KODEIGR ";
        $query .= "AND SUBSTR(PBO_PLUIGR,1,6) || '0' = ST_PRDCD ";
        $query .= "AND ST_LOKASI = '01' ";
        $query .= "AND pbo_tglpb = TO_DATE('" . $tglPb . "','YYYY-MM-DD')  ";
        $query .= "AND pbo_nopb = '" . $noPb . "' ";
        $query .= "AND pbo_kodeomi = '" . $kodetoko . "' ";
        $query .= "AND pbo_recordid = '4' ";
        $query .= "AND pbo_nokoli like '06%' ";

        //! UPDATE HEADER ROTI
        $query = '';
        $query .= "UPDATE TBTR_HEADER_ROTI  ";
        $query .= "SET HDR_FLAG = '5' ";
        $query .= "WHERE date_TRUNC('day',HDR_TGLPB) =  TO_DATE('" . $tglPb . "','YYYY-MM-DD')   ";
        $query .= "AND HDR_NOPB= '" . $noPb . "' ";
        $query .= "AND HDR_KODETOKO = '" . $noPb . "' ";
    }
}
