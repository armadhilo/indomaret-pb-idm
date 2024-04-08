<?php

namespace App\Http\Controllers;

use App\Helper\ApiFormatter;
use App\Helper\DatabaseConnection;
use App\Http\Requests\DetailKasirRequest;
use App\Http\Requests\TableRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DspbRotiController extends Controller
{

    public function __construct(Request $request){
        DatabaseConnection::setConnection(session('KODECABANG'), "PRODUCTION");
    }

    public function index(){

        $this->createTableLogNPB();

        return view('home');
    }

    public function getClusterMobil(){
        $data = DB::select("SELECT distinct cri_kodecluster FROM CLUSTER_ROTI_IDM order by 1");

        return ApiFormatter::success(200, 'Berhasil menampilkan cluster mobil', $data);
    }

    public function datatables($cluster, $date){
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
        $query .= "  AND DATE_TRUNC('DAY',HDR_TGLTRANSAKSI) = TO_DATE('" . $date . "','YYYY-MM-DD')  ";
        $query .= "  GROUP BY HDR_KODETOKO, HDR_TGLTRANSAKSI, HDR_TGLPB  ";
        $query .= "  ORDER BY HDR_KODETOKO  ";
        $query .= "  ) A  ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function actionCetakDspb(){
        //* cek STATUS pada datatables jika tidak ada yang "SIAP DSPB" muncul error -> Tidak ada data yang siap DSPB

        //* jika ada maka muncul popup -> DSPB Cluster '" & cbCluster.Text & "' tanggal " & datePick.Value.ToString("dd-MM-yyyy") & " ini?

        //! LOOPING DATATABLES yang STATUS "SIAP DSPB"
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

    private function DspbRoti($kodetoko, $tgltrans, $noRekap){

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

        // Concatenate the values
        $noDspb = $thnPb . str_pad($noDspb, 5, "0", STR_PAD_LEFT);

        //! CHECK DATA
        $dt = DB::table('tbtr_header_roti')
            ->selectRaw("distinct hdr_nopb, hdr_tglpb")
            ->where('hdr_kodetoko', $kodetoko)
            ->whereDate('hdr_tgltransaksi', $tgltrans)
            ->get();

        if(Count($dt)){
            // $nmPb = "NPR" & _kodegudang & _toko & Format(_tglServer, "yyyyMMddHHmm")
            // $nmRpb = "XPR" & _kodegudang & _toko & Format(_tglServer, "yyyyMMddHHmm")

            $dtCek = DB::table('master_supply_idm')
                ->select('msi_kodedc')
                ->where('msi_kodetoko', $kodetoko)
                ->first();

            $kodeDCIDM = empty($dtCek) ? '' : $dtCek->msi_kodedc;

            foreach($dt as $item){
                $noPb = $item->HDR_NOPB;
                $tglPb = $item->HDR_TGLPB;

                //! CHECK
                $query = '';
                $query .= "SELECT '*' recid, ";
                $query .= "  NULL rtype, ";
                $query .= "  " & $noDspb & "  docno, ";
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
                $query .= "WHERE PBO_TGLPB = TO_DATE('" & $tglPb & "','YYYY-MM-DD')  ";
                $query .= "AND pbo_nopb = '" & $noPb & "' ";
                $query .= "AND pbo_kodeomi = '" & $kodetoko & "' ";
                $query .= "AND pbo_qtyrealisasi > 0 ";
                $query .= "AND pbo_recordid = '4'";
                $query .= "AND pbo_nokoli like '06%'";
                $check = DB::select($query);

                if(count($check)){
                    $this->updateDb($noDspb,$tglPb,$kodetoko,$noPb);

                    $this->WriteCSV($nmNpb, $check);

                    $this->cetakSJP($kodetoko, $noDspb, $noPb, $tglPb);
                }
            }

            sb = New StringBuilder
            sb.AppendLine("INSERT INTO TBTR_REKAP_DSPB_ROTI")
            sb.AppendLine("(SP_KODEIGR, DSP_NO_REKAP, DSP_NODSPB, DSP_CLUSTER, DSP_CREATE_BY, DSP_CREATE_DT)")
            sb.AppendLine("VALUES('" & KodeIGR & "', '" & noRekap & "', '" & noDspb & "', '" & cbCluster.Text & "', '" & UserMODUL & "', NOW() )")

            sb = New StringBuilder
            sb.AppendLine("SELECT " & noDspb & " docno,")
            sb.AppendLine("  TO_CHAR(CURRENT_DATE, 'dd-MM-YYYY') doc_date,")
            sb.AppendLine("  pbo_kodeomi toko,")
            sb.AppendLine("  (SELECT 'GI' || prs_kodeigr FROM tbmaster_perusahaan LIMIT 1) gudang, ")
            sb.AppendLine("  COUNT(DISTINCT pbo_pluomi) item, SUM(pbo_qtyrealisasi) qty, ")
            sb.AppendLine("  SUM(pbo_ttlnilai) gross, NULL koli, NULL kubikasi ")
            sb.AppendLine("FROM tbmaster_pbomi a  ")
            sb.AppendLine("WHERE  A.PBO_TGLPB = TO_DATE('" & _tglpb & "','dd/MM/YYYY')  ")
            sb.AppendLine("AND pbo_kodeomi = '" & _toko & "' ")
            sb.AppendLine("AND pbo_nokoli like '06%' ")
            sb.AppendLine("AND PBO_QTYREALISASI > 0 ")
            sb.AppendLine("GROUP BY pbo_tglpb, pbo_kodeomi ")

            //! SIMPAN NAMA NPB
            simpanDSPB(nmNpb & ".ZIP", _toko, _nopb, 0, 0, noDspb, "R- PBROTI")

            // cmd.CommandText = "SELECT ws_url FROM tbmaster_webservice WHERE ws_nama = 'NPB' AND COALESCE(ws_aktif, 0) = 1 " & _
            //                   IIf(kodeDCIDM <> "", "AND ws_dc = '" & kodeDCIDM & "' ", "")
            // npbIP = cmd.ExecuteScalar

            // If kodeDCIDM <> "" Then
            //     npbGudang = kodeDCIDM
            // Else
            //     cmd.CommandText = "SELECT ws_DC FROM tbmaster_webservice WHERE ws_nama = 'NPB' " & _
            //         IIf(kodeDCIDM <> "", "AND ws_dc = '" & kodeDCIDM & "' ", "")
            //     npbGudang = cmd.ExecuteScalar
            // End If

            // If Not (npbIP Is Nothing) Then
            //     Dim okNPB As Boolean = True

            //     okNPB = insertToNPB(Format(_tglServer, "yyyyMMdd") & npbGudang.ToString, _
            //                         nmNpb, _
            //                         dtH, _
            //                         dtD)

            //     sb = New StringBuilder
            //     sb.AppendLine("INSERT INTO log_npb ( ")
            //     sb.AppendLine("   npb_tgl_proses, ")
            //     sb.AppendLine("   npb_kodetoko, ")
            //     sb.AppendLine("   npb_nopb, ")
            //     sb.AppendLine("   npb_tglpb, ")
            //     sb.AppendLine("   npb_nodspb, ")
            //     sb.AppendLine("   npb_file, ")
            //     sb.AppendLine("   npb_jml_item, ")
            //     sb.AppendLine("   npb_jenis, ")
            //     sb.AppendLine("   npb_url, ")
            //     sb.AppendLine("   npb_response, ")
            //     sb.AppendLine("   npb_create_web, ")
            //     sb.AppendLine("   npb_create_csv, ")
            //     sb.AppendLine("   npb_kirim, ")
            //     sb.AppendLine("   npb_confirm, ")
            //     sb.AppendLine("   npb_jml_retry, ")
            //     sb.AppendLine("   npb_create_by,  ")
            //     sb.AppendLine("   npb_create_dt ")
            //     sb.AppendLine(" ) VALUES ( ")
            //     sb.AppendLine("   DATE_TRUNC('DAY',CURRENT_DATE ), ")
            //     sb.AppendLine("   '" & _toko & "', ")
            //     sb.AppendLine("   '" & _nopb & "', ")
            //     sb.AppendLine("   to_date('" & Format(_tglpb, "yyyyMMdd") & "','YYYYMMDD'), ")
            //     sb.AppendLine("   '" & noDspb & "', ")
            //     sb.AppendLine("   '" & nmNpb & "', ")
            //     sb.AppendLine("   '" & jmlItem & "', ")
            //     sb.AppendLine("   'ROTI', ")
            //     sb.AppendLine("   '" & npbIP & "', ")
            //     sb.AppendLine("   '" & IIf(okNPB, "SUKSES! ", "GAGAL! ") & npbRes & "', ")
            //     sb.AppendLine("   '" & jamCreateWeb & "', ")
            //     sb.AppendLine("   '" & jamCreateCSV & "', ")
            //     sb.AppendLine("   '" & jamKirim & "', ")
            //     sb.AppendLine("   '" & tglConfirm & "', ")
            //     sb.AppendLine("   0, ")
            //     sb.AppendLine("   '" & UserMODUL & "', ")
            //     sb.AppendLine("   NOW() ")
            //     sb.AppendLine(" ) ")

            //     cmd.CommandText = sb.ToString
            //     cmd.ExecuteNonQuery()
            // End If

            //! REPORT QR CODE
            // If checkQR.Checked Then
            //     Create_QRCode(nmNpb, _
            //                   _toko, _
            //                   noDspb, _
            //                   dtD.Rows(0).Item("TANGGAL1").ToString.Replace("-", "/"), _
            //                   dtD.Rows.Count, _
            //                   dtD)
            // End If
        }
    }

    private function Create_QRCode($filename,$toko,$nodspb,$tgldspb,$jmlrecord,$sourceDetail, $reprint = false, $rpt = null){
        //! BINGUNG FUNCTION GIMANA
    }

    private function simpanDSPB($namafile,$kodetoko,$nopb,$nopick,$nosj,$nodspb,$jenispb){
        jenispb = jenispb.ToUpper

        sql = "KODEIGR = '" & KodeIGR & " ' " &
        "AND NAMAFILE = '" & namafile & "' " &
        "AND KODETOKO = '" & kodetoko & "' " &
        "AND NOPB = '" & nopb & "' " &
        "AND NOPICK = '" & nopick & "' " &
        "AND NOSJ = '" & nosj & "' " &
        "AND NODSPB = '" & nodspb & "'" &
        "AND JENISPB = '" & jenispb & "'"

        If ORADataFound(_cn, "TBHISTORY_DSPB", sql) = False Then
                query = "INSERT INTO TBHISTORY_DSPB   "
                query += "(KODEIGR,   "
                query += "NAMAFILE,   "
                query += "KODETOKO,   "
                query += "NOPB,   "
                query += "NOPICK,   "
                query += "NOSJ,   "
                query += "NODSPB,   "
                query += "JENISPB,   "
                query += "CREATEBY,   "
                query += "CREATEDT)   "
                query += "VALUES   "
                query += "('" & KodeIGR & "',   "
                query += "'" & namafile & "',   "
                query += "'" & kodetoko & "',   "
                query += "'" & nopb & "',   "
                query += "'" & nopick & "',   "
                query += "'" & nosj & "',   "
                query += "'" & nodspb & "',   "
                query += "'" & jenispb & "',   "
                query += "'" & UserMODUL & "',   "
                query += "NOW() )  "
                NonQueryOra1(query)
            End If
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
                $query .= "UPDATE PAKET_IPP SET PIP_NODSPB = '" & $noDspb & "', ";
                $query .= "PIP_RECORDID = '3', ";
                $query .= "PIP_TGLDSPB = CURRENT_DATE  ";
                $query .= "WHERE PIP_KODETOKO = '" & $kodetoko & "' ";
                $query .= "AND PIP_RECORDID = '1' ";
                $query .= "AND PIP_NODSPB IS NULL ";
            }

            $this->DCPickingFinal($noDspb, $kodetoko, $noPb, $tglPb);
        }


    }

    private function writeCSV($nmNpb, $check){

    }

    private function updateDb($noDspb,$tglPb,$kodetoko,$noPb){
        //! UPDATE IDM KOLI
        $query = '';
        $query .= "UPDATE TBTR_IDMKOLI  ";
        $query .= "SET IKL_REGISTERREALISASI = '" & $noDspb & "',  ";
        $query .= "IKL_NOBPD = '1' , ";
        $query .= "IKL_IDTRANSAKSI = '" & $noDspb & "',  ";
        $query .= "IKL_TGLBPD = date_trunc('day',current_date), ";
        $query .= "IKL_RECORDID = '1' ";
        $query .= "WHERE ikl_tglpb = TO_DATE('" & $tglPb & "','YYYY-MM-DD') ";
        $query .= "AND ikl_kodeidm = '" & $kodetoko & "' ";
        $query .= "AND ikl_nopb = '" & $noPb & "'   ";
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
        $query .= "AND date_trunc('day',PBO_TGLPB) = TO_DATE('" & $tglPb & "','YYYY-MM-DD')  ";
        $query .= "AND pbo_nopb = '" & $noPb & "' ";
        $query .= "AND pbo_kodeomi = '" & $kodetoko & "' ";
        $query .= "AND pbo_recordid = '4' ";
        $query .= "AND SUBSTR(PRD_PRDCD,1,6) || '0' = ST_PRDCD ) ";
        $query .= "WHERE st_kodeigr = '" & session('KODECABANG') & "' ";
        $query .= "AND st_lokasi = '01' ";
        $query .= "AND EXISTS( ";
        $query .= "SELECT 1 ";
        $query .= "FROM tbmaster_pbomi ";
        $query .= "WHERE pbo_kodeigr = st_kodeigr ";
        $query .= "AND SUBSTR(pbo_pluigr, 1, 6) || '0' = st_prdcd ";
        $query .= "AND date_trunc('day',PBO_TGLPB) = TO_DATE('" & $tglPb & "','YYYY-MM-DD')  ";
        $query .= "AND pbo_recordid = '4' ";
        $query .= "AND pbo_nopb = '" & $noPb & "' ";
        $query .= "AND pbo_kodeomi = '" & $kodetoko & "' ";
        $query .= "AND pbo_nokoli like '06%' ";
        $query .= ")    ";

        //! INSERT REALPB
        $query = '';
        $query .= "INSERT into tbtr_realpb (RPB_KODEIGR,RPB_NOKOLI,RPB_NOURUT,RPB_TGLDOKUMEN,RPB_NODOKUMEN,RPB_IDSURATJALAN,RPB_KODECUSTOMER,RPB_KODEOMI";
        $query .= ",RPB_PLU1,RPB_PLU2,RPB_HRGSATUAN,RPB_QTYORDER,RPB_QTYREALISASI,RPB_NILAIORDER,RPB_PPNORDER,RPB_TTLNILAI,RPB_TTLPPN,RPB_DISTRIBUTIONFEE,RPB_COST";
        $query .= ",RPB_QTYV,RPB_QTYBDR,RPB_HBDR,RPB_FLAG,RPB_CREATE_BY,RPB_CREATE_DT) ";
        $query .= "SELECT PBO_KODEIGR,PBO_NOKOLI, row_number() over(),PBO_TGLPB, PBO_NOPB, '" & $noDspb & "',PBO_KODEMEMBER,PBO_KODEOMI,PBO_PLUOMI,PBO_PLUIGR,PBO_HRGSATUAN,";
        $query .= "PBO_QTYORDER, PBO_QTYREALISASI,PBO_NILAIORDER,PBO_PPNORDER,PBO_TTLNILAI,PBO_TTLPPN,PBO_DISTRIBUTIONFEE,ST_AVGCOST,0,0,0,'4','" & session('userid') & "',now() ";
        $query .= "FROM tbmaster_pbomi  , tbmaster_STOCK   ";
        $query .= "WHERE PBO_KODEIGR = ST_KODEIGR ";
        $query .= "AND SUBSTR(PBO_PLUIGR,1,6) || '0' = ST_PRDCD ";
        $query .= "AND ST_LOKASI = '01' ";
        $query .= "AND pbo_tglpb = TO_DATE('" & $tglPb & "','YYYY-MM-DD')  ";
        $query .= "AND pbo_nopb = '" & $noPb & "' ";
        $query .= "AND pbo_kodeomi = '" & $kodetoko & "' ";
        $query .= "AND pbo_recordid = '4' ";
        $query .= "AND pbo_nokoli like '06%' ";

        //! UPDATE HEADER ROTI
        $query = '';
        $query .= "UPDATE TBTR_HEADER_ROTI  ";
        $query .= "SET HDR_FLAG = '5' ";
        $query .= "WHERE date_TRUNC('day',HDR_TGLPB) =  TO_DATE('" & $tglPb & "','YYYY-MM-DD')   ";
        $query .= "AND HDR_NOPB= '" & $noPb & "' ";
        $query .= "AND HDR_KODETOKO = '" & $noPb & "' ";
    }
}
