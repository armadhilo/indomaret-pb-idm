<?php

namespace App\Http\Controllers;

use App\Traits\LibraryPDF;
use App\Traits\mdPublic;
use Illuminate\Http\Request;
use DB;
use PDF;

class RPTController extends Controller
{
    use LibraryPDF;
    use mdPublic;
    public $DB_PGSQL;
    public function __construct()
    { 
        $this->DB_PGSQL = DB::connection('pgsql');

        // try {
        //     $this->DB_PGSQL->beginTransaction();

            
        //     $this->DB_PGSQL->commit();
        // } catch (\Throwable $th) {
            
        //     $this->DB_PGSQL->rollBack();
        //     dd($th);
        //     return response()->json(['errors'=>true,'messages'=>$th->getMessage()],500);
        // }
    }

    public function index(){
        return view("menu.rpt.index");
    }
        
    public function print_report($data){
        $data = json_decode(base64_decode($data));
        $jenis_page = null;
        $folder_page = null;
        $title_report = 'default-'.date('Y-m-d');
        
        switch ($data->filename) {
            case 'cetak-ulang-dsp':
                $jenis_page = $data->jenis_page;
                $folder_page = $data->folder_page;
                $title_report = $data->title_report;
                $data->data =$this->data_cetak_ulang_dsp($data->kodetoko,$data->nopb,$data->tglpb);
                break;
            case 'cetak-ulang-sj':
                $jenis_page = $data->jenis_page;
                $folder_page = $data->folder_page;
                $title_report = $data->title_report;
                $data->data = (object)$this->data_cetak_ulang_sj($data->kodetoko,$data->nopb,$data->tglpb);
                break;
            case 'struk-hadiah':
                $jenis_page = $data->jenis_page;
                $folder_page = $data->folder_page;
                $title_report = $data->title_report;
                $data->data = (object)$this->data_struk_hadiah($data->kodetoko,$data->nopb,$data->tglpb);
                break;
            
            default:
                break;
        }

        $tanggal = date('Y-m-d');
        
        $perusahaan = $this->DB_PGSQL
                           ->table("tbmaster_perusahaan")
                           ->whereRaw("prs_kodeigr = '".session('KODECABANG')."'")
                           ->get();
        $perusahaan = $perusahaan[0];
        if ($jenis_page == 'default-page') {
            
            $header_cetak_custom = false;    
            $pdf = PDF::loadview('menu.rpt.'.$folder_page.'.'.$data->filename, compact('data','tanggal','perusahaan','header_cetak_custom'));
            $pdf->output();
            $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
            $canvas = $dompdf->get_canvas();
    
            // //make page text in header and right side
    
            $canvas->page_text(615, 63, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 7, array(0, 0, 0));

            return $pdf->stream($title_report );
            
        } elseif ($jenis_page == 'struk-page') {
           
            $dompdf = new PDF();
            $pdf = PDF::loadview('menu.rpt.'.$folder_page.'.'.$data->filename,compact(['perusahaan','data']));
            error_reporting(E_ALL ^ E_DEPRECATED);
            $pdf->output();
            $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
            $canvas = $dompdf ->get_canvas();
            $dompdf = $pdf;
            return $pdf->stream($title_report );
        } 
        // else{
            
        // }
        // dd('out');
    }

    public function print_cetak_ulang_dsp(Request $request){
        $kodetoko = $request->toko;
        $nopb = explode(" / ",$request->nopb)[0];
        $tglpb = explode(" / ",$request->nopb)[1];
        
        $data_report =[];
        // $data_report['data'] = $this->data_cetak_ulang_dsp($kodetoko,$nopb,$tglpb);
        // dd($data_report);
        $data_report['kodetoko'] = $kodetoko;
        $data_report['nopb'] = $nopb;
        $data_report['tglpb'] = $tglpb;
        $data_report['filename'] = 'cetak-ulang-dsp';
        $data_report['jenis_page'] = 'struk-page';
        $data_report['folder_page'] = 'omi';
        $data_report['title_report'] = 'CETAK-ULANG-DSP';
        $encrypt_data = base64_encode(json_encode($data_report));
         //    $decrypt_data = json_decode(base64_decode($encrypt_data));
         $link = url('/api/print/report/'.$encrypt_data);
         
         return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
    }

    public function print_cetak_ulang_sj(Request $request){
        $kodetoko = $request->toko;
        $nopb = explode(" / ",$request->nopb)[0];
        $tglpb = explode(" / ",$request->nopb)[1];
        
        $data_report =[];
        // $data_report['data'] = $this->data_cetak_ulang_dsp($kodetoko,$nopb,$tglpb);
        // dd($data_report);
        $data_report['kodetoko'] = $kodetoko;
        $data_report['nopb'] = $nopb;
        $data_report['tglpb'] = $tglpb;
        $data_report['filename'] = 'cetak-ulang-sj';
        $data_report['jenis_page'] = 'default-page';
        $data_report['folder_page'] = 'omi';
        $data_report['title_report'] = 'CETAK-ULANG-SJ';
        $encrypt_data = base64_encode(json_encode($data_report));
         //    $decrypt_data = json_decode(base64_decode($encrypt_data));
         $link = url('/api/print/report/'.$encrypt_data);

         return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
    }

    public function print_struk_hadiah(Request $request){
        $kodetoko = $request->toko;
        $nopb = explode(" / ",$request->nopb)[0];
        $tglpb = explode(" / ",$request->nopb)[1];
        
        $data_report =[];
        // $data_report['data'] = $this->data_cetak_ulang_dsp($kodetoko,$nopb,$tglpb);
        // dd($data_report);
        $data_report['kodetoko'] = $kodetoko;
        $data_report['nopb'] = $nopb;
        $data_report['tglpb'] = $tglpb;
        $data_report['filename'] = 'struk-hadiah';
        $data_report['jenis_page'] = 'struk-page';
        $data_report['folder_page'] = 'omi';
        $data_report['title_report'] = 'STRUK-HADIAH';
        $encrypt_data = base64_encode(json_encode($data_report));
         //    $decrypt_data = json_decode(base64_decode($encrypt_data));
         $link = url('/api/print/report/'.$encrypt_data);

         return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
    }

    public function get_toko_omi(Request $request){
        /**
         * Perlu di cek kembali
         * file Lov_OMI 
         * tidak ditemukan query select toko
         */
        $data = $this->DB_PGSQL
                    ->table("tbmaster_tokoigr")
                    ->selectRaw("tko_kodeomi,tko_kodecustomer")
                    ->whereRaw("tko_kodeigr = '".session('KODECABANG')."'")
                    ->whereRaw("tko_namasbu = 'INDOMARET'")
                    ->get();
        return $data;
    }

    public function get_no_pb(Request $request){
        $toko = $request->toko;
        $sql = "SELECT ROW_NUMBER() OVER() as NOUR, TGDSP::date, NOPB, ISIKOLI, STATUS 
                FROM 
                (   
                    SELECT DISTINCT  
                    TGDSP, NOPB, COUNT(1)||' Koli.' as ISIKOLI, STATUS   
                    FROM  
                    (  
                    SELECT IKL_TGLBPD AS TGDSP,  
                        TRIM(IKL_NOPB) AS NoPB,         
                        CASE  
                            WHEN IKL_IDSTRUK IS NOT NULL  
                                THEN 'OK'  
                        ELSE   
                            CASE  
                            WHEN IKL_NOSPH IS NULL  
                                THEN 'Intransit'  
                            ELSE 'Verifikasi'  
                            END  
                        END as STATUS  
                    FROM TBTR_IDMKOLI  
                    WHERE IKL_KODEIGR = '22'   
                    AND IKL_KODEIDM = '$toko'   
                    ) A        
                    Group BY TGDSP,NoPB,STATUS 
                    Order By TGDSP DESC      
                ) B LIMIT 50";
        $data = $this->DB_PGSQL
                    ->select($sql);
        return $data;
    }

    public function data_struk_hadiah($kodetoko = null, $nopb = null, $tglpb = null){
        $sql = "select distinct count (1) from tbmaster_hadiahdcp where hdc_kodetoko = '$kodetoko' and hdc_nodokumen = '$nopb'";
        $jmlh_hadiah = $this->DB_PGSQL->select($sql);

        if (count($jmlh_hadiah)) {

            try {
                $this->DB_PGSQL->beginTransaction();
    
                
                $msg = "";
                $tglTran = null;
                $STT = "";
                $StationMODUL = session()->get('KODECABANG');
        
                // Truncate tables
                $this->DB_PGSQL->statement("TRUNCATE TABLE TEMP_CFL");
                $this->DB_PGSQL->statement("TRUNCATE TABLE TEMP_FGF");
                $this->DB_PGSQL->statement("TRUNCATE TABLE TEMP_KGAB");
        
                // Insert into TEMP_CFL
                $sql = "
                    INSERT INTO TEMP_CFL
                    (Select NULL as FDMBER,
                            SUBSTR(RPB_PLU2, 1, 6) || '0' as FDKPLU,
                            SUM(coalesce(RPB_QtyRealisasi, 0) * CASE WHEN PRD_Unit = 'KG' THEN 1 ELSE PRD_FRAC END) as FDJQTY,
                            SUM(coalesce(RPB_QtyRealisasi, 0) * PRD_HrgJual) as FDNAMT,
                            ? as IPMODUL
                     From TbTr_RealPB, tbMaster_Prodmast
                     Where RPB_KodeIGR = ?
                       And TRIM(RPB_NoDokumen) = TRIM(?)
                       And RPB_KodeOMI = ?
                       And RPB_Plu2 = PRD_PRDCD
                     Group By SUBSTR(RPB_PLU2, 1, 6) || '0')
                ";
                $this->DB_PGSQL->statement($sql, [$IPMODUL, $KodeIGR, $NoOrder, $TokoOmi]);
        
                // Select distinct TO_DATE(rpb_create_dt)
                $sql = "
                    SELECT DISTINCT TO_DATE(rpb_create_dt)
                    FROM tbtr_realpb
                    WHERE RPB_KodeIGR = ?
                      AND TRIM(RPB_NoDokumen) = TRIM(?)
                      AND RPB_KodeOMI = ?
                ";
                $results = $this->DB_PGSQL->select($sql, [$KodeIGR, $NoOrder, $TokoOmi]);
        
                foreach ($results as $row) {
                    $tglTran = $row->to_date;
                }
        
                // Select distinct SUBSTR(RPB_IDSuratJalan, 14, 2)
                $sql = "
                    SELECT DISTINCT SUBSTR(RPB_IDSuratJalan, 14, 2)
                    FROM tbtr_realpb
                    WHERE RPB_KodeIGR = ?
                      AND TRIM(RPB_NoDokumen) = TRIM(?)
                      AND RPB_KodeOMI = ?
                ";
                $results = $this->DB_PGSQL->select($sql, [$KodeIGR, $NoOrder, $TokoOmi]);
        
                // STT
                $STT = $StationMODUL;

                // Process TEMP_KGAB
                $results = $this->DB_PGSQL->select("SELECT * FROM TEMP_KGAB");

                foreach ($results as $row) {
                    $q = 0;

                    if ($row->ftminr > 0) { // minim rp
                        if ($row->fdnamt >= $row->ftminr) {
                            $q = 1; // default dpt 1 hdh
                            if ($row->fdnamt > $row->ftminr && $row->ftklpt == "Y") {
                                $q = floor($row->fdnamt / $row->ftminr);
                            }
                        }
                    } else { // minim qty
                        if ($row->fdjqty >= $row->ftminq) {
                            $q = 1; // default dpt 1 hdh
                            if ($row->fdjqty > $row->ftminq && $row->ftklpt == "Y") {
                                $q = floor($row->fdjqty / $row->ftminq);
                            }
                        }
                    }

                    if ($q > 0) {
                        $sql = "
                            INSERT INTO TEMP_FGF 
                            (JNSGF, PLUTR, PLUGF, NMAGF, QDPAT)
                            VALUES 
                            ('2', ?, ?, ?, ?)
                        ";
                        $this->DB_PGSQL->statement($sql, [$row->ftksup, $row->ftkplu, $row->ftketr, $q * $row->ftjvch]);
                    }
                }

                // Process GIFT_DSC
                $results = $this->DB_PGSQL->select("SELECT * FROM TEMP_FGF");

                foreach ($results as $row) {
                    if (!is_null($row->PLUGF)) {
                        $sql = "
                            SELECT COALESCE(COUNT(1), 0) 
                            FROM tbMaster_BrgPromosi 
                            WHERE BPRP_KodeIGR = ? 
                            AND BPRP_PRDCD = ?
                        ";
                        $check = $this->DB_PGSQL->select($sql, [$KodeIGR, $row->PLUGF]);
                        $jum = $check[0]->coalesce;

                        if ($jum > 0) {
                            $sql = "
                                SELECT BPRP_KetPanjang 
                                FROM tbMaster_BrgPromosi 
                                WHERE BPRP_KodeIGR = ? 
                                AND BPRP_PRDCD = ?
                            ";
                            $descResult = $this->DB_PGSQL->select($sql, [$KodeIGR, $row->PLUGF]);
                            $ftdesc = $descResult[0]->BPRP_KetPanjang;

                            $sql = "
                                UPDATE TEMP_FGF 
                                SET nmagf = ? 
                                WHERE plugf = ?
                            ";
                            $this->DB_PGSQL->statement($sql, [$ftdesc, $row->PLUGF]);
                        }
                    }
                }

                // Update tbMaster_HadiahDCP
                $sql = "
                    UPDATE tbMaster_HadiahDCP 
                    SET HDC_RecordID = '2' 
                    WHERE HDC_KodeIGR = ? 
                    AND HDC_KodeToko = ? 
                    AND HDC_NoDokumen = ? 
                    AND HDC_RecordID IS NULL
                ";
                $this->DB_PGSQL->statement($sql, [$KodeIGR, $TokoOmi, $NoOrder]);

                $this->cetakHadiah($TokoOmi, $NoOrder);

                // Check and delete old records
                $sql = "
                    SELECT COALESCE(COUNT(1), 0) 
                    FROM tbMaster_HadiahDCP 
                    WHERE HDC_KodeIGR = ? 
                    AND HDC_KodeToko = ? 
                    AND HDC_RecordID = '2' 
                    AND DATE_TRUNC('DAY', CURRENT_DATE) - DATE_TRUNC('DAY', HDC_TglDokumen) > INTERVAL '45 DAYS'
                ";
                $results = $this->DB_PGSQL->select($sql, [$KodeIGR, $TokoOmi]);
                $jum = $results[0]->coalesce;

                if ($jum > 0) {
                    $sql = "
                        DELETE FROM tbMaster_HadiahDCP 
                        WHERE HDC_KodeIGR = ? 
                        AND HDC_KodeToko = ? 
                        AND HDC_RecordID = '2' 
                        AND DATE_TRUNC('DAY', CURRENT_DATE) - DATE_TRUNC('DAY', HDC_TglDokumen) > INTERVAL '45 DAYS'
                    ";
                    $this->DB_PGSQL->statement($sql, [$KodeIGR, $TokoOmi]);
                }
                
                $this->DB_PGSQL->commit();
            } catch (\Throwable $th) {
                
                $this->DB_PGSQL->rollBack();
                dd($th);
                return response()->json(['errors'=>true,'messages'=>$th->getMessage()],500);
            }
    
        } else {
            
            return response()->json(['errors'=>true,'messages'=>'Data Tidak Tersedia'],404);
        }
        


    }
    
    public function data_cetak_ulang_sj($kodetoko = null, $nopb = null, $tglpb = null){
            // Initialize variables
            $dt = [];
            $dtTemp = [];
            $NamaCabang = "";
            $OMI = "";
            $FMNDOC = "";
            $JumFMNDOC = 0;
            $ds = [];
            $dtKoli = [];
            $dtPBICM  = [];
            $oPs = null;
            $KodeIGR = session()->get('KODECABANG');
            $FMNDOC = null;
            $noSJ = null;
            $tglSJ = null;
            $JumFMNDOC = null;
            $StationMODUL = session()->get('KODECABANG');
            $UserMODUL = session()->get('userid');
            $flagIGR = true;
            $flagPunyaICM = false;
            $kodeomi = $kodetoko;
            $nopb = $nopb;
            $TglPB = $tglpb;
            $nodspb = "";
            $TglCreateDt = "";
            $noSJ = "";
            $tglSJ = "";
            
            $koli = 0;
            $kardus = 0;
            $bronjong = 0;
            $dolly = 0;
            $koliRetur = 0;
            $dspbKoliRetur = "";
            $pbKoliRetur = "";

            $sql = "SELECT DISTINCT ikl_registerrealisasi ,ikl_tglbpd,ikl_nopb,ikl_kodeidm
                    FROM tbtr_idmkoli 
                    WHERE ikl_kodeidm = '$kodeomi' 
                    AND ikl_nopb = '$nopb' 
                    AND ikl_tglbpd::date = '$TglPB'::date
                    LIMIT 1";

            $nodspb = $this->DB_PGSQL->select($sql);
            $nodspb = $nodspb[0]->ikl_registerrealisasi;

            if (!$nodspb) {
                return ['errors'=>true,'message' => 'Data (ikl_registerrealisasi) Tidak ada data!'];
            }

            $sql2 = "SELECT TO_CHAR(pbo_create_dt, 'DD-MM-YYYY') AS pbo_create_dt ,ikl_registerrealisasi,pbo_kodeomi
                    FROM tbmaster_pbomi, tbtr_idmkoli 
                    WHERE pbo_tglpb::date = ikl_tglpb::date 
                    AND pbo_nopb = ikl_nopb 
                    AND pbo_kodeomi = ikl_kodeidm 
                    AND pbo_nokoli = ikl_nokoli 
                    AND pbo_qtyrealisasi > 0 
                    AND ikl_registerrealisasi = '$nodspb' 
                    AND pbo_kodeomi = '$kodeomi' 
                    LIMIT 1";
    
            $TglCreateDtResult = $this->DB_PGSQL->select($sql2);
            $TglCreateDt = $TglCreateDtResult ? $TglCreateDtResult[0]->pbo_create_dt : null;
            
            if (!$TglCreateDt) {
                return ['errors'=>true,'message' => 'Data (pbo_create_dt) Tidak ada data!'];
            }
    
            // Additional logic based on retrieved data...
            // Execute additional queries
            $sql3 = "SELECT PRS_NamaCabang, 
                            CONCAT(TKO_KODEOMI, ' - ', TKO_NamaOMI) AS OMI 
                        FROM tbMaster_Perusahaan, tbMaster_TokoIGR 
                        WHERE PRS_KodeIGR = '$KodeIGR' 
                        AND TKO_KodeIGR = PRS_KodeIGR 
                        AND TKO_KodeOMI = '$kodeomi'";
    
            $dtTemp = $this->DB_PGSQL->select($sql3);
            if (count($dtTemp) > 0) {
                $NamaCabang = $dtTemp[0]->prs_namacabang;
                $OMI = $dtTemp[0]->omi;
            }
    
            $sql4 = "SELECT rpb_nodokumen AS nopb,  
                            TO_CHAR(rpb_tgldokumen, 'DD-MM-YYYY') AS tglpb,  
                            TO_CHAR(rpb_create_dt, 'DD-MM-YYYY') AS tglsj, 
                            COUNT(DISTINCT rpb_nodokumen) AS JumFMNDOC
                        FROM TBTR_REALPB
                        WHERE rpb_kodeomi = '$kodeomi'
                        AND rpb_idsuratjalan = '$nodspb'
                        GROUP BY rpb_nodokumen, rpb_tgldokumen, rpb_create_dt";
    
            $dtTemp = $this->DB_PGSQL->select($sql4);
            if (count($dtTemp) > 0) {
                $FMNDOC = $dtTemp[0]->nopb;
                $nopb = $dtTemp[0]->nopb;
                $TglPB = $dtTemp[0]->tglpb;
                $noSJ = $nodspb;
                $tglSJ = $dtTemp[0]->tglsj;
                $JumFMNDOC = $dtTemp[0]->jumfmndoc;
            }

            $sql5 = "SELECT 
                        rpb_nokoli AS KOLI,  
                        CONCAT(rpb_idsuratjalan, '.', COALESCE(rpb_dsp_kdstation, '$StationMODUL'), '.', COALESCE(rpb_dsp_cashierid, '$UserMODUL')) AS DraftStruk, 
                        COUNT(DISTINCT rpb_plu1) AS ISIKOLI,  
                        SUM(rpb_ttlnilai) AS NILAI,  
                        SUM(
                            CASE 
                                WHEN CONCAT(COALESCE(PRD_FlagBKP1, 'N'), COALESCE(PRD_FlagBKP2, 'N')) = 'YY' 
                                THEN COALESCE(RPB_TtlPPN, 0) 
                                ELSE 0 
                            END
                        ) AS PPN,  
                        SUM(rpb_distributionfee::float) + ROUND(SUM(rpb_distributionfee::numeric), 2) * (SELECT MAX(COALESCE(prs_nilaippn, 0) / 100) FROM tbmaster_perusahaan) AS DF,
                        SUM(RPB_TTLNILAI) + 
                        SUM(
                            CASE 
                                WHEN CONCAT(COALESCE(PRD_FlagBKP1, 'N'), COALESCE(PRD_FlagBKP2, 'N')) = 'YY' 
                                THEN COALESCE(RPB_TtlPPN, 0) 
                                ELSE 0 
                            END
                        ) + SUM(RPB_DISTRIBUTIONFEE) + 
                        ROUND(SUM(rpb_distributionfee::int), 2) * (SELECT MAX(COALESCE(prs_nilaippn, 0) / 100) FROM tbmaster_perusahaan) AS TotalNilai,
                        SUM(
                            CASE 
                                WHEN CONCAT(COALESCE(PRD_FlagBKP1, 'N'), COALESCE(PRD_FlagBKP2, 'N')) = 'YY' 
                                THEN COALESCE(rpb_ttlnilai, 0) 
                                ELSE 0 
                            END
                        ) AS NilaiBKP,  
                        SUM(
                            CASE 
                                WHEN CONCAT(COALESCE(PRD_FlagBKP1, 'N'), COALESCE(PRD_FlagBKP2, 'N')) <> 'YY' 
                                THEN COALESCE(rpb_ttlnilai, 0) 
                                ELSE 0 
                            END
                        ) AS NilaiBTKP,  
                        SUM(
                            CASE 
                                WHEN CONCAT(COALESCE(PRD_FlagBKP1, 'N'), COALESCE(PRD_FlagBKP2, 'N')) = 'YY' 
                                THEN rpb_distributionfee + ROUND(rpb_distributionfee::numeric) * (SELECT MAX(COALESCE(prs_nilaippn, 0) / 100) FROM tbmaster_perusahaan) 
                                ELSE 0 
                            END
                        ) AS DFBKP,
                        SUM(
                            CASE 
                                WHEN CONCAT(COALESCE(PRD_FlagBKP1, 'N'), COALESCE(PRD_FlagBKP2, 'N')) <> 'YY' 
                                THEN rpb_distributionfee + ROUND(rpb_distributionfee::numeric) * (SELECT MAX(COALESCE(prs_nilaippn, 0) / 100) FROM tbmaster_perusahaan) 
                                ELSE 0 
                            END
                        ) AS DFBTKP
                    FROM TBTR_REALPB 
                    JOIN tbmaster_prodmast ON rpb_plu2 = prd_prdcd 
                    WHERE RPB_IDSURATJALAN = '$nodspb'  
                    AND RPB_KODEOMI = '$kodeomi'  
                    AND RPB_KODEIGR = '$KodeIGR'  
                    AND RPB_QTYREALISASI > 0 
                    AND SUBSTR(RPB_NOKOLI, 1, 2) <> '0C'
                    GROUP BY rpb_create_dt, rpb_nodokumen, rpb_tgldokumen, rpb_nokoli, rpb_idsuratjalan, rpb_dsp_kdstation, rpb_dsp_cashierid  
                    ORDER BY rpb_nokoli";

            $dtKoli = $this->DB_PGSQL->select($sql5);

            if (count($dtKoli) == 0) {
                return ['errors'=>true,'message' => 'Data (koli) Tidak ada data!'];
            }


            // GET JML KOLI
            $sql = "SELECT COUNT(1) FROM tbtr_idmkoli
                    WHERE ikl_kodeidm = '$kodeomi' AND ikl_nopb = '$nopb'
                    AND COALESCE(ikl_kardus, 'N') = 'N'
                    AND SUBSTR(ikl_nokoli, 1, 2) = '01'
                    AND TO_DATE(ikl_tglpb, 'YYYYMMDD') = TO_DATE('$TglPB', 'DD-MM-YYYY')";
            $koli = $this->DB_PGSQL->select($sql);
            $koli = count($koli);

            // GET JML BRONJONG
            $sql = "SELECT COUNT(1) FROM tbtr_idmkoli
                    WHERE ikl_kodeidm = '$kodeomi' AND ikl_nopb = '$nopb'
                    AND COALESCE(ikl_kardus, 'N') = 'N'
                    AND SUBSTR(ikl_nokoli, 1, 2) = '02'
                    AND TO_DATE(ikl_tglpb, 'YYYYMMDD') = TO_DATE('$TglPB', 'DD-MM-YYYY')";
            $bronjong = $this->DB_PGSQL->select($sql);
            $bronjong = count($bronjong);

            // GET JUMLAH KARDUS
            $sql = "SELECT COUNT(1) FROM tbtr_idmkoli
                    WHERE ikl_kodeidm = '$kodeomi' AND ikl_nopb = '$nopb'
                    AND COALESCE(ikl_kardus, 'N') = 'Y'
                    AND TO_DATE(ikl_tglpb, 'YYYYMMDD') = TO_DATE('$TglPB', 'DD-MM-YYYY')";
            $kardus = $this->DB_PGSQL->select($sql);
            $kardus = count($kardus);

            // GET JUMLAH DOLLY
            $sql = "SELECT 
                    -- MAX(COALESCE(lki_dolly, 0)) AS jml_dolly -- query perlu di tanyakan column lki_dolly tidak ada
                    MAX(COALESCE(lki_nosj, 0)) AS jml_dolly
                    FROM LOADING_KOLI_IDM
                    WHERE LKI_KODETOKO = '$kodeomi' AND LKI_NOPB = '$nopb'";
            $dolly = $this->DB_PGSQL->select($sql);
            $dolly = $dolly[0]->jml_dolly;

            // GET KOLI YANG BELUM DIRETUR
            $sql = "SELECT COALESCE(qtydspb, 0) AS qty, nodspb, nopb
                    FROM tbhistory_container_idm
                    WHERE qtyretur IS NULL AND kodetoko = '$kodeomi'
                    AND nodspb <> '$nodspb' AND DATE_TRUNC('DAY', tgldspb) < DATE_TRUNC('DAY', NOW())
                    ORDER BY create_dt ASC";
            $result = $this->DB_PGSQL->select($sql);

            if (!empty($result)) {
                $koliRetur = $result[0]->qty;
                $dspbKoliRetur = $result[0]->nodspb;
                $pbKoliRetur = $result[0]->nopb;
            } else {
                $koliRetur = 0;
            }

            // Get No Ref DSPB ICM 
            $dtPBICM = null;
            if (!$flagPunyaICM && $flagIGR && $flagPunyaICM) {
                $sql = "SELECT DISTINCT COALESCE(ikl_registerrealisasi, '-') AS NODSPB
                        FROM {$skemaICM}.tbmaster_pbomi
                        LEFT JOIN {$skemaICM}.tbtr_idmkoli
                        ON ikl_kodeidm = pbo_kodeomi
                        AND ikl_tglpb = TO_CHAR(pbo_tglpb, 'YYYYMMDD')
                        AND ikl_nokoli = pbo_nokoli
                        WHERE pbo_create_dt = TO_DATE(?, 'DD-MM-YYYY')
                        AND pbo_kodeomi = ?
                        AND pbo_qtyrealisasi > 0";
                $dtPBICM = $this->DB_PGSQL->connection($skemaICM)->select($sql, [$TglCreateDt, $kodeomi]);
            }

            $data_report = [
                    'NamaCabang' => $NamaCabang,
                    'OMI' => $OMI,
                    'FMNDOC' => $FMNDOC,
                    'NoPB' => $nopb,
                    'TglPB' => $TglPB,
                    'NoSJ' => $noSJ,
                    'TglSJ' => $tglSJ,
                    'jumFMNDOC' => $JumFMNDOC,
                    'UserID' => $UserMODUL,
                    'koli' => "$koli pcs. ($kardus pcs.)",
                    'bronjong' => "$bronjong pcs.",
                    'dolly' => "$dolly pcs.",
                    'koli_retur' => $koliRetur == 0 ? "-" : "$koliRetur pcs. (atas No.DSPB: $dspbKoliRetur No.PB: $pbKoliRetur)",
                    'filename'=>'SJ-'.$kodeomi.'-'.$nodspb.'.PDF',
                    'list_koli'=>$dtKoli
                ];
            return $data_report;


    }

    public function data_cetak_ulang_dsp($kodetoko = null, $nopb = null, $tglpb = null){
        
            $data_report = [];
            $KodeOMI = $kodetoko;
            $noPB =  $nopb;
            $tglPB = $tglpb;
            // $pathStrukOMI = "C:\\IGR\\PBIDM\\PBOMI";
            $nodspb = "";
            $NamaCab = "";
            $AlamatCab1 = "";
            $AlamatCab2 = "";
            $NamaPersh = "";
            $AlamatPersh1 = "";
            $AlamatPersh2 = "";
            $AlamatPersh3 = "";
            $NPWP = "";
            $NamaOMI = "";
            $NoOrder = "";
            $NamaCheker = "";
            $KodeCustomer = "";
            $pdFee = 0;
            $NamaCab = "";
            $AlamatCab1 = "";
            $AlamatCab2 = "";
            $NamaPersh = "";
            $AlamatPersh1 = "";
            $AlamatPersh2 = "";
            $AlamatPersh3 = "";
            $NPWP = "";
            $ppnRatePersh = 0;
            $dtlistkoli = [];
            $ppnRatePrd = 0;
            $DPPRPH = $DPPBBS = $DPPDTP = $DPPCUK = $DPPTKP = 0;
            $cntrBKP = $cntrBBS = $cntrDTP = $cntrCUK = $cntrTKP = 0;
        

        // Execute the raw query with bindings
        $NoKoli = $this->DB_PGSQL
                        ->table("tbtr_realpb")
                        ->selectRaw("
                            rpb_nokoli as nokoli
                        ")
                        ->distinct()
                        ->whereRaw("rpb_kodeomi = '$KodeOMI'")
                        ->whereRaw("rpb_nodokumen = '$noPB'")
                        ->whereRaw("rpb_create_dt::date = '$tglPB'::date")
                        ->orderBy($this->DB_PGSQL->raw("1"))
                        ->get();
                        
        $data_perusahaan =  $this->DB_PGSQL
                                ->table("tbmaster_perusahaan")
                                ->selectRaw("
                                    prs_namacabang,
                                    prs_alamat1,
                                    prs_namawilayah || ' - TELP ' || PRS_Telepon as alamat_telp,
                                    prs_namaperusahaan,
                                    prs_alamatfakturpajak1,
                                    prs_alamatfakturpajak2,
                                    prs_alamatfakturpajak3,
                                    'NPWP: ' || PRS_NPWP as npwp,
                                    (COALESCE(PRS_NilaiPPN, 0) / 100) AS PRS_NilaiPPN
                                ")
                                ->first();
        if ($data_perusahaan) {
            $NamaCab = $data_perusahaan->prs_namacabang;
            $AlamatCab1 = $data_perusahaan->prs_alamat1;
            $AlamatCab2 = $data_perusahaan->alamat_telp;
            $NamaPersh = $data_perusahaan->prs_namaperusahaan;
            $AlamatPersh1 = $data_perusahaan->prs_alamatfakturpajak1;
            $AlamatPersh2 = $data_perusahaan->prs_alamatfakturpajak2;
            $AlamatPersh3 = $data_perusahaan->prs_alamatfakturpajak3;
            $NPWP = $data_perusahaan->npwp;
            $ppnRatePersh = $data_perusahaan->prs_nilaippn;
        }
        $data_report['list_data']=[];
        $data_report['data_perusahaan'] =(object)[
            'NamaCab2' => $NamaCab,
            'NamaCab' => "= " .$NamaCab. " =",
            'AlamatCab1' => $AlamatCab1,
            'AlamatCab2' => $AlamatCab2,
            'NamaPersh' => $NamaPersh,
            'AlamatPersh1' => $AlamatPersh1,
            'AlamatPersh2' => $AlamatPersh2,
            'AlamatPersh3' => $AlamatPersh3,
            'NPWP' => $NPWP,
        ];
        if (count($NoKoli)) {
            foreach ($NoKoli as $key => $row_koli) {


                $dtlistkoli =  $this->DB_PGSQL
                                    ->table("tbtr_realpb")
                                    ->join("tbmaster_prodmast",function($join){
                                        $join->on("rpb_plu2","=","prd_prdcd");
                                    })
                                    ->selectRaw("
                                        RPB_TTLNILAI / RPB_QTYREALISASI AS HG, 
                                        CASE 
                                            WHEN COALESCE(RPB_KETERANGANV, 'XX') <> '10'  
                                            THEN COALESCE(RPB_QTYREALISASI, 0) - COALESCE(RPB_QTYV, 0)
                                            ELSE COALESCE(RPB_QTYREALISASI, 0) 
                                        END AS QT, 
                                        '(' || RPB_PLU2 || ')' AS PLU, 
                                        PRD_DeskripsiPendek as DESK, 
                                        COALESCE(PRD_FlagBKP1, 'N') as PKP, 
                                        COALESCE(PRD_FlagBKP2, 'N') as PKP2, 
                                        (COALESCE(PRD_PPN, 0) / 100) as PPNRATEPRD
                                    ")
                                    ->whereRaw("RPB_KODEIGR = '".session('KODECABANG')."' ")
                                    ->whereRaw("RPB_KODEOMI = '$KodeOMI'")
                                    ->whereRaw("RPB_NODOKUMEN = '$nopb'")
                                    ->whereRaw("RPB_NOKOLI = '".$row_koli->nokoli."'")
                                    ->whereRaw("RPB_QTYREALISASI > 0 ")
                                    ->get();              
                foreach ($dtlistkoli as $i => $item) {
                    if ((int)$item->ppnrateprd <= 0) {
                        $ppnRatePrd = (int)$item->ppnrateprd;
                    }

                    $cekPPn = $this->checkPPN($item->pkp . $item->pkp2);
                    $dtPPn = $cekPPn->data;

                    $status = '';
                    if(isset($cekPPn->data[0]->status)){
                        $status = $cekPPn->data[0]->status;
                    }

                    if (count($dtPPn)) {
                        switch ($status) {
                            case "KENA PPN":
                                $DPPRPH += ((int)$item->hg * (int)$item->qt);
                                $item->plu .= "    ";
                                $cntrBKP++;
                                break;
                            case "BEBAS PPN":
                                $DPPBBS += ((int)$item->hg * (int)$item->qt);
                                $item->plu .= "****";
                                $cntrBBS++;
                                break;
                            case "PPN DTP":
                                $DPPDTP += ((int)$item->hg * (int)$item->qt);
                                $item->plu .= "*** ";
                                $cntrDTP++;
                                break;
                            case "CUKAI":
                                $DPPCUK += ((int)$item->hg * (int)$item->qt);
                                $item->plu .= "**  ";
                                $cntrCUK++;
                                break;
                            default:
                                $DPPTKP += ((int)$item->hg * (int)$item->qt);
                                $item->plu .= "*   ";
                                $cntrTKP++;
                                break;
                        }
                    } else {
                        $DPPTKP += ((int)$item->hg * (int)$item->qt);
                        $item->plu .= "    ";
                        $cntrTKP += 1;
                    }
                    

                }

                $dtOMI = $this->DB_PGSQL
                            ->table("tbmaster_tokoigr")
                            ->selectRaw("
                                TKO_KodeOMI AS Kode, 
                                TKO_NamaOMI AS Nama, 
                                '{$nopb}' AS NoOrder, 
                                TKO_KodeCustomer, 
                                TKO_PERSENDISTRIBUTIONFEE::INT / 100 AS DSTFEE 
                            ")
                            ->whereRaw("tko_kodeomi = '$KodeOMI'")
                            ->get();

                if (count($dtOMI) > 0) {
                    $dtOMI = $dtOMI[0];
                    $KodeOMI = $dtOMI->kode;
                    $NamaOMI = $dtOMI->nama;
                    $NoOrder = $dtOMI->noorder;
                    $KodeCustomer = $dtOMI->tko_kodecustomer;
                    $pdFee = (float) str_replace(",", ".", $dtOMI->dstfee);
                }

                $Checker = $this->DB_PGSQL
                                ->table("tbmaster_pbomi")
                                ->selectRaw("
                                    COALESCE(PBO_USERUPDATECHECKER, 'XXX') AS checker
                                ")
                                ->distinct()
                                ->whereRaw(" PBO_KODEOMI = '$KodeOMI'")
                                ->whereRaw(" PBO_NOPB = '$nopb'")
                                ->whereRaw(" PBO_NOKOLI = '$row_koli->nokoli'")
                                ->limit(1)
                                ->get();

                
                $NamaCheker = '-';
                if (count($Checker) > 0) {
                    $NamaCheker = $Checker[0]->checker;
                }
                
                $NoDSP = $this->DB_PGSQL
                               ->table("tbtr_idmkoli")
                               ->selectRaw("
                                    ikl_registerrealisasi
                               ")
                               ->distinct()
                               ->whereRaw("ikl_kodeidm = '$KodeOMI'")
                               ->whereRaw("ikl_nopb = '$nopb'")
                               ->whereRaw("TO_CHAR(ikl_tglbpd, 'YYYYMMDD') = '".date("Ymd",strtotime($tglpb))."'")
                               ->get();

                $data_report['list_data'][] = (object)[
                                'NamaCab' => "= " .$NamaCab. " =",
                                'AlamatCab1' => $AlamatCab1,
                                'AlamatCab2' => $AlamatCab2,
                                'NamaPersh' => $NamaPersh,
                                'AlamatPersh1' => $AlamatPersh1,
                                'AlamatPersh2' => $AlamatPersh2,
                                'AlamatPersh3' => $AlamatPersh3,
                                'NPWP' => $NPWP,
                                'NoDSP' => $NoDSP[0]->ikl_registerrealisasi,
                                'NoKoli' => $row_koli->nokoli,
                                'dataKoli' =>$item,
                                'plu' => $item->plu,
                                'TglDSP' => date('d-m-Y'),
                                'JamDSP' => date('H:i:s'),
                                'KsrStt' => session()->get('userid').".".session()->get('NAMACABANG'),
                                'pdFee' => $pdFee,
                                'KodeOMI' => $KodeOMI,
                                'NamaOMI' => $NamaOMI,
                                'NoOrder' => $NoOrder,
                                'NamaChecker' => $NamaCheker,
                                'ppnRatePersh' => $ppnRatePersh,
                                'ppnRatePrd' => $ppnRatePrd,
                                'DPPRPH' => $DPPRPH,
                                'DPPBBS' => $DPPBBS,
                                'DPPDTP' => $DPPDTP,
                                'DPPTKP' => $DPPTKP,
                                'DPPCUK' => $DPPCUK,
                                'cntrBKP' => $cntrBKP,
                                'cntrTKP' => $cntrTKP,
                                'cntrBBS' => $cntrBBS,
                                'cntrDTP' => $cntrDTP,
                                'cntrCUK' => str_pad(" Re-PRINT " .date('Y-m-d H:i:s'), 30, ' ', STR_PAD_BOTH),
                                'Koreksi' => "",
                                'KodeMember' => $KodeCustomer,
                            ];
               
              
                
            }
            
        }
        return (object)$data_report;
    }
}