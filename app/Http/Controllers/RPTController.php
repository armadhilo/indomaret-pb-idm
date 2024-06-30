<?php

namespace App\Http\Controllers;

use App\Traits\LibraryPDF;
use App\Traits\mdPublic;
use Illuminate\Http\Request;
use DB;
use PDF;
use DNS1D;
use DNS2D;

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
        $flag = [
            "flagFTZ" => session()->get('flagFTZ'),
            "flagIGR" => session()->get('flagIGR'),
            "flagSPI" => session()->get('flagSPI'),
            "flagHHSPI" => session()->get('flagHHSPI')
        ];
        return view("menu.rpt.index",compact('flag'));
    }
        
    /**
     * IDM1
     */
        public function print_outstanding_dspb(Request $request){
            $kodetoko1 = $request->toko;
            $kodetoko2 = $request->toko2;
            
            $data_report =[];
            $data_report['kodetoko1'] = $kodetoko1;
            $data_report['kodetoko2'] = $kodetoko2;
            $data_report['filename'] = 'outstanding_dspb';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr'; // for testing
            // $data_report['folder_page'] = 'idm2'; // for testing
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_OUTSTANDING_DSPB';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_cetak_hitory_dspb(Request $request){
            $kodetoko = $request->toko;
            $nodspb = $request->dspb;
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nodspb'] = $nodspb;
            $data_report['filename'] = 'cetak_hitory_dspb';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_CETAK_HITORY_DSPB';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_struk_hadiah(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            $data_report['filename'] = 'struk_hadiah';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_STRUK_HADIAH';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_pemutihan_batch(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            // $data_report =[];
            // $data_report['kodetoko'] = $kodetoko;
            // $data_report['nopb'] = $nopb;
            // $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'pemutihan_batch';
            // $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            // $data_report['jenis_page'] = 'struk-page';
            // $data_report['title_report'] = 'PRINT_PEMUTIHAN_BATCH';
            // $encrypt_data = base64_encode(json_encode($data_report));   
            // $link = url('/api/print/report/'.$encrypt_data);

            $link = false;
            $code = 200;
            $response = $this->data_pemutihan_batch($kodetoko,$nopb,$tglpb);
            $response['url'] = $link;
            if (isset($response['errors'])) {
                $code = 500;
            }
            return response()->json($response,$code);
        }
        public function print_cetak_ba_ulang(Request $request){
            $no_bpbr = $request->no_bpbr;
            $tgl_ret = $request->tgl_ret;
            
            $data_report =[];
            $data_report['no_bpbr'] = $no_bpbr;
            $data_report['tgl_ret'] = $tgl_ret;
            $data_report['filename'] = 'cetak_ba_ulang';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_CETAK_BA_ULANG';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_cetak_bpbr_ulang(Request $request){
            $no_bpbr = $request->no_bpbr;
            $tgl_ret = $request->tgl_ret;
            
            $data_report =[];
            $data_report['no_bpbr'] = $no_bpbr;
            $data_report['tgl_ret'] = $tgl_ret;
            $data_report['filename'] = 'cetak_bpbr_ulang';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_CETAK_BPBR_ULANG';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_beban_retur_igr(Request $request){
            $no_bpbr = $request->no_bpbr;
            $tgl_ret = $request->tgl_ret;
            
            $data_report =[];
            $data_report['no_bpbr'] = $no_bpbr;
            $data_report['tgl_ret'] = $tgl_ret;
            $data_report['filename'] = 'beban_retur_igr';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_BEBAN_RETUR_IGR';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_analisa_crm(Request $request){
            $tgl1 = $request->tgl1;
            $tgl2 = $request->tgl2;
            
            $data_report =[];
            $data_report['tgl1'] = $tgl1;
            $data_report['tgl2'] = $tgl2;
            $data_report['filename'] = 'analisa_crm';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_ANALISA_CRM';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_absensi_wt(Request $request){
            $tgl1 = $request->tgl1;
            $tgl2 = $request->tgl2;
            
            $data_report =[];
            $data_report['tgl1'] = $tgl1;
            $data_report['tgl2'] = $tgl2;
            $data_report['filename'] = 'absensi_wt';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_ABSENSI_WT';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_listing_ba(Request $request){
            $tgl1 = $request->tgl1;
            $tgl2 = $request->tgl2;
            
            $data_report =[];
            $data_report['tgl1'] = $tgl1;
            $data_report['tgl2'] = $tgl2;
            $data_report['filename'] = 'listing_ba';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_LISTING_BA';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_retur_idm(Request $request){
            $tgl1 = $request->tgl1;
            $tgl2 = $request->tgl2;
            
            $data_report =[];
            $data_report['tgl1'] = $tgl1;
            $data_report['tgl2'] = $tgl2;
            $data_report['filename'] = 'retur_idm';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_RETUR_IDM';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_outstanding_retur(Request $request){
            $data_report['filename'] = 'outstanding_retur';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_OUTSTANDING_RETUR';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_cetak_ba_bronjong(Request $request){
            $noba = $request->noba;
            $tglba = $request->tglba;
            
            $data_report =[];
            $data_report['noba'] = $noba;
            $data_report['tglba'] = $tglba;
            $data_report['filename'] = 'cetak_ba_bronjong';
            $data_report['folder_page'] = 'idm1';
            // $data_report['filename'] ='rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'default-page';
            $data_report['title_report'] = 'PRINT_CETAK_BA_BRONJONG';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }

        public function data_outstanding_dspb($kodetoko1,$kodetoko2){
            $toko1 = 'AAAA';
            $toko2 = 'ZZZZ';

            if (!empty($kodetoko1)) {
                $toko1 = $kodetoko1;
            }
            if (!empty($kodetoko2)) {
                $toko2 = $kodetoko2;
            }

            $query = "
                SELECT HDR.*, TKO_NAMAOMI
                FROM (
                    SELECT ikl_kodeidm, ikl_nopb, ikl_tglbpd::date AS ikl_tglbpd,
                        ikl_registerrealisasi, SUM(ikl_rphdpp) AS dpp, SUM(ikl_rphppn) AS ppn, npb_file AS filenpb
                    FROM tbtr_idmkoli
                    JOIN log_npb ON npb_kodetoko = ikl_kodeidm
                                AND npb_nopb = ikl_nopb
                                AND npb_nodspb = ikl_registerrealisasi
                    WHERE ikl_registerrealisasi IS NOT NULL
                    AND ikl_recordid = '1'
                     AND ikl_kodeidm BETWEEN '$toko1' AND '$toko2' -- command for debug
                    GROUP BY ikl_kodeidm, ikl_nopb, ikl_tglbpd::date, ikl_registerrealisasi, npb_file
                    -- limit 10 -- debug
                ) HDR
                JOIN TBMASTER_TOKOIGR ON ikl_kodeidm = tko_kodeomi
            ";

            $results = $this->DB_PGSQL->select($query);

            if($results){

                return $results;
            }else {
                
                return (object)['errors'=>true];
            }

        }
        public function data_cetak_hitory_dspb($kodetoko,$nodspb){
            $data = [];
            $encryptedValue = $this->caesarEncrypt("P" . $nodspb . "9999", date('Y-m-d'));
            $data['list_data'][] = (object)[
                "kode_igr" => "Testing kode_igr",
                "koli" => "Testing koli",
                "container" => "Testing container",
                "item" => "Testing item",
                "bkp" => "Testing bkp",
                "btkp" => "Testing btkp",
            ];
            $data['header'] [] = (object)[
                "encrypt"=>  $encryptedValue,
                "kode_igr"=> " kode_igr test",
                "ikl_nopb"=> " ikl_nopb test",
                "ikl_nobpd"=> " ikl_nobpd test",
                "reprint"=> " reprint test",
                "dspb"=> " dspb test",
                "tgl_dspb"=> " tgl_dspb test"
            ];
            $data['perusahaan'] [] = (object)[
                "kode_igr" =>"22",
                "prs_namacabang" =>"IGR SEMARANG TEST"
            ];
            $data['toko'] [] = (object)[
                "kode_igr"=> $kodetoko,
                "tko_namaomi"=> "TOKOTEST",
                "tko_kodeomi"=> $kodetoko
            ];
            $data['tgl'] = date('Y-m-d');
            $data['tglPB'] = date('Y-m-d');
            $data['kodetoko'] = $kodetoko;
            $data['nodspb'] = $nodspb;
            $data['cluster'][] = "cluster Test";
            $data['group'][] = "group Test";
            $data['nopaket'][] = "nopaket Test";
            $data['koli'][] = "koli Test";
            $data['kardus'][] = "kardus Test";
            $data['bronjong'][] = "bronjong Test";
            $data['dolly'][] = "dolly Test";
            $data['koliRetur'][] = "koliRetur Test";
            $data['dspbKoliRetur'][] = "dspbKoliRetur Test";
            $data['pbKoliRetur'][] = "pbKoliRetur Test";
            $data['msg'][] = "msg Test";
            $data['list'] = [
                'cluster' => "Cluster : " . $data['cluster'][0] . "  Group : " . $data['group'][0] . (($data['nopaket'][0] != "" && $data['nopaket'][0] != "0") ? "  No.Pengiriman : " . $data['nopaket'][0] : ""),
                'koli' => $data['koli'][0] . " pcs. (" . $data['kardus'][0] . " pcs.)",
                'bronjong' => $data['bronjong'][0] . " pcs.",
                'dolly' => $data['dolly'][0] . " pcs.",
                'koli_retur' => ($data['koliRetur'][0] == 0) ? "-" : $data['koliRetur'][0] . " pcs. (atas No.DSPB: " . $data['dspbKoliRetur'][0] . " No.PB: " . $data['pbKoliRetur'][0] . ")",
                'paket' => $data['msg'][0],
            ];
            return (object)$data;
            // debug testing
            
            $flagFTZ = session()->get("flagFTZ")?true:false;
            $flagIGR = session()->get("flagIGR")?true:false;
            $flagPunyaICM = false;
            $flagByPassICM = false;
            $skemaICM = '';
            $data = [];
            $data['kodetoko'] = $kodetoko;
            $data['nodspb'] = $nodspb;
            try {
                $this->DB_PGSQL->beginTransaction();
                if ($flagFTZ) {
                    if ($flagIGR) {
                        $data['jenis_rpt'] = "rptSuratJalan_FTZ";
                    } else {
                        $data['jenis_rpt'] = "rptSuratJalanICM_FTZ";
                    }
                } else {
                    if ($flagIGR) {
                        $data['jenis_rpt'] = "rptSuratJalan";
                    } else {
                        $data['jenis_rpt'] = "rptSuratJalanICM";
                    }
                }
                $sql = "SELECT ikl_kodeigr AS kode_igr, ikl_nokoli AS koli, ikl_nocontainer AS CONTAINER, COUNT(pbo_qtyrealisasi) AS item, ";

                // Add conditional parts based on flags
                if ($flagFTZ) {
                    $sql .= "SUM(COALESCE(pbo_ttlnilai, 0) + COALESCE(pbo_ttlppn, 0)) AS bkp, 0 AS btkp, 0 AS ppn ";
                } else {
                    $sql .= "SUM(CASE WHEN COALESCE(pbo_ttlppn, 0) > 0 THEN COALESCE(pbo_ttlnilai, 0) END) AS bkp, ";
                    $sql .= "SUM(CASE WHEN COALESCE(pbo_ttlppn, 0) = 0 THEN COALESCE(pbo_ttlnilai, 0) END) AS btkp, ";
                    $sql .= "SUM(pbo_ttlppn) AS ppn ";
                }
            
                // Continue building the SQL query
                $sql .= "FROM tbmaster_pbomi 
                         JOIN tbtr_idmkoli 
                         ON pbo_tglpb = TO_DATE(ikl_tglpb, 'YYYYMMDD') 
                         AND pbo_nopb = ikl_nopb 
                         AND pbo_kodeomi = ikl_kodeidm 
                         AND pbo_nokoli = ikl_nokoli 
                          AND IKL_REGISTERREALISASI = '$nodspb' -- command for debug
                         JOIN tbmaster_prodmast 
                         ON pbo_pluigr = prd_prdcd 
                         LEFT JOIN tbmaster_kodefp 
                         ON COALESCE(prd_flagbkp1, 'N') = kfp_flagbkp1 
                         AND COALESCE(prd_flagbkp2, 'N') = kfp_flagbkp2 
                         WHERE pbo_qtyrealisasi > 0 
                          AND PBO_KODEOMI = '$kodetoko' -- command for debug
                         AND PBO_NOKOLI NOT LIKE '04%' 
                         GROUP BY ikl_kodeigr, IKL_NOKOLI, ikl_nocontainer 
                         ORDER BY IKL_NOKOLI
                         -- limit 10 -- debug";
            
                // Execute the query
                $data['list_data'] = $this->DB_PGSQL->select($sql);
                // Additional logic based on the results
                if (count($data['list_data'])) {
                    
                    // Query to get the creation date
                    $sql = "SELECT DISTINCT RPB_CREATE_DT 
                            FROM tbtr_realpb 
                             WHERE rpb_idsuratjalan = '$nodspb'-- command for debug
                             AND RPB_KODEOMI = '$kodetoko'-- command for debug
                            LIMIT 1";
                    $data['tgl'] =  $this->DB_PGSQL->selectOne($sql)[0]->rpb_create_dt;
            
                    // Query to get the formatted date
                    $sql = "SELECT TO_CHAR(pbo_create_dt, 'DD-MM-YYYY') AS create_dt 
                            FROM tbmaster_pbomi 
                            JOIN tbtr_idmkoli 
                            ON pbo_tglpb = TO_DATE(ikl_tglpb, 'YYYYMMdd') 
                            AND pbo_nopb = ikl_nopb 
                            AND pbo_kodeomi = ikl_kodeidm 
                            AND pbo_nokoli = ikl_nokoli 
                            WHERE pbo_qtyrealisasi > 0 
                             AND IKL_REGISTERREALISASI = '$nodspb' -- command for debug
                             AND PBO_KODEOMI = '$kodetoko' -- command for debug
                            AND PBO_NOKOLI NOT LIKE '04%' 
                            LIMIT 1";
                    $data['tglPB'] =  $this->DB_PGSQL->selectOne($sql)[0]->create_dt;


                    $encryptedValue = $this->caesarEncrypt("P" . $nodspb . "9999", $Tgl);

                    // Fetch HEADER data
                    $data['header'] = $this->DB_PGSQL->select("
                        SELECT '$encryptedValue' AS encrypt,
                               IKL_KODEIGR AS KODE_IGR,
                               ikl_nopb,
                               ikl_nobpd,
                               'REPRINT' AS reprint,
                               ikl_registerrealisasi AS dspb,
                               ikl_tglbpd AS tgl_dspb
                        FROM tbtr_idmkoli
                        WHERE IKL_REGISTERREALISASI = '$nodspb'
                        LIMIT 1
                    ");
                
                    // Fetch PERUSAHAAN data
                    $data['perusahaan'] = $this->DB_PGSQL->select("
                        SELECT prs_kodeigr AS kode_igr,
                               PRS_NAMACABANG
                        FROM tbmaster_perusahaan
                    ");
                
                    // Fetch TOKO data
                    $data['toko'] = $this->DB_PGSQL->select("
                        SELECT tko_kodeigr AS kode_igr,
                               TKO_NAMAOMI,
                               TKO_KODEOMI
                        FROM tbmaster_tokoigr
                        WHERE TKO_KODEOMI = '$kodetoko' AND TKO_NAMASBU = 'INDOMARET'
                    ");

                    $msg = "";
                    $dtIPP = $this->DB_PGSQL
                            ->select("
                                SELECT COUNT(1) AS count
                                FROM PAKET_IPP
                                 WHERE PIP_KODETOKO = '$kodetoko' AND PIP_NODSPB = '$nodspb'  -- command for debug
                                -- limit 1 -- debug
                            ");
                
                    if (!empty($dtIPP) && $dtIPP[0]->count > 0) {
                        $data['msg'] = "*Ambil paket di HUB*";
                    } else {
                        $data['msg'] = "-";
                    }
                
                    // Initialize report data variables
                    $data['cluster'] = "";
                    $data['group'] = "";
                    $data['nopaket'] = "";
                    $data['nopb'] = "";
                    $data['koli'] = 0;
                    $data['kardus'] = 0;
                    $data['bronjong'] = 0;
                    $data['dolly'] = 0;
                    $data['koliRetur'] = 0;
                    $data['dspbKoliRetur'] = "";
                    $data['pbKoliRetur'] = "";
                    $data['dtPBICM'] = [];
                
                    // GET NOPAKET aka NOSJ
                    $data['nopaket'] = $this->DB_PGSQL
                            ->select("
                                select MAX(ikl_nosj) from tbtr_idmkoli 
                                 Where ikl_kodeidm = '$kodetoko'  -- command for debug
                                 and ikl_idtransaksi = '$nodspb'  -- command for debug
                                -- limit 1 -- debug
                            ");
                    // GET NOPB
                    $data['nopb'] = $this->DB_PGSQL
                            ->select("
                                select MAX(ikl_nopb) from tbtr_idmkoli 
                                 Where ikl_kodeidm = '$kodetoko'  -- command for debug
                                 and ikl_idtransaksi = '$nodspb '  -- command for debug
                                -- limit 1 -- debug
                            ");
                
                    // GET CLUSTER TOKO IDM
                    $data['cluster'] = $this->DB_PGSQL
                            ->select("
                                select cls_kode from cluster_idm  
                                 Where cls_toko = '$kodetoko ' -- command for debug
                                -- limit 1 -- debug
                            ");
                
                    // GET GROUP TOKO IDM
                    $data['group'] = $this->DB_PGSQL
                            ->select("
                                select cls_group from cluster_idm  
                                 Where cls_toko = '$kodetoko '  -- command for debug
                                -- limit 1 -- debug
                            ");
                
                    // GET JML KOLI
                    $data['koli'] = $this->DB_PGSQL
                            ->select("
                                select count(1) from tbtr_idmkoli  
                                 Where ikl_kodeidm = '$kodetoko'    -- command for debug 
                                 and ikl_idtransaksi = '$kode'  -- command for debug
                                 and coalesce(ikl_kardus,'N') = 'N' -- command for debug  
                                -- where coalesce(ikl_kardus,'N') = 'N' -- debug  
                                and substr(ikl_nokoli,1,2) IN ('01','09')
                                -- limit 1 -- debug  
                            ");
                
                    // GET JML BRONJONG
                    $data['bronjong'] = $this->DB_PGSQL
                            ->select("
                                select count(1) from tbtr_idmkoli  
                                 Where ikl_kodeidm = '$kodetoko'  -- command for debug
                                 and ikl_idtransaksi = '$nodspb'  -- command for debug
                                 and coalesce(ikl_kardus,'N') = 'N'  -- command for debug
                                -- where coalesce(ikl_kardus,'N') = 'N' -- debug
                                and substr(ikl_nokoli,1,2) IN ('02','08') 
                                -- limit 1 -- debug
                            ");
                
                    // GET JUMLAH KARDUS
                    $data['kardus'] = $this->DB_PGSQL
                            ->select("
                            
                                select count(1) from tbtr_idmkoli  
                                 Where ikl_kodeidm = '$kodetoko' -- command for debug
                                 and ikl_idtransaksi = '$nodspb' -- command for debug
                                 and coalesce(ikl_kardus,'N') = 'Y'  -- command for debug
                                -- where coalesce(ikl_kardus,'N') = 'Y' -- debug
                                -- limit 1 -- debug 
                            ");
                
                    // GET JUMLAH DOLLY
                    $data['dollyData'] = $this->DB_PGSQL
                            ->select("
                                SELECT MAX(COALESCE(lki_dolly, 0)) AS jml_dolly
                                FROM LOADING_KOLI_IDM
                                WHERE LKI_KODETOKO = '$kodetoko'AND LKI_NOPB = '".$data['nopb'][0]."' -- command for debug
                                -- limit 2 -- debug
                            ");
                    $data['dolly'] = !empty($dollyData) ? $dollyData[0]->jml_dolly : 0;
                
                    // GET KOLI YANG BELUM DIRETUR
                    $dt = $this->DB_PGSQL
                            ->select("
                                    SELECT COALESCE(qtydspb, 0) AS qty, nodspb, nopb
                                    FROM tbhistory_container_idm
                                    WHERE qtyretur IS NULL
                                    AND kodetoko = '$kodetoko' AND nodspb <> '$nodspb'
                                    AND tgldspb::date <  '".date('Y-m-d', strtotime($data['tglPB']))."'::date
                                    ORDER BY create_dt ASC
                                ");
                    $data['koliRetur'] = !empty($dt) ? $dt[0]->qty : 0;
                    $data['dspbKoliRetur'] = !empty($dt) ? $dt[0]->nodspb : "";
                    $data['pbKoliRetur'] = !empty($dt) ? $dt[0]->nopb : "";
                
                    // Get No Ref DSPB ICM 
                    if ($flagIGR && $flagPunyaICM) {
                        $skemaICM = $skemaICM??'@'.$skemaICM;
                        $sql = "
                            SELECT DISTINCT COALESCE(ikl_registerrealisasi,'-') AS NODSPB, pbo_nopb AS NOPB
                            FROM tbmaster_pbomi" . $skemaICM . "
                            LEFT JOIN tbtr_idmkoli" . $skemaICM . " ON ikl_kodeidm = pbo_kodeomi
                            AND ikl_tglpb = TO_CHAR(pbo_tglpb, 'YYYYMMDD')
                            AND ikl_nokoli = pbo_nokoli
                            WHERE pbo_create_dt::date = '".$data['tglPB']."'::date
                            AND pbo_kodeomi = '$kodetoko'
                            AND pbo_qtyrealisasi > 0
                        ";
                        $data['dtPBICM'] = $this->DB_PGSQL
                                ->select($sql);
                    }
                
                    // Generating the report
                    $data['list'] = [
                        'cluster' => "Cluster : " . $data['cluster'][0] . "  Group : " . $data['group'][0] . (($data['nopaket'][0] != "" && $data['nopaket'][0] != "0") ? "  No.Pengiriman : " . $data['nopaket'][0] : ""),
                        'koli' => $data['koli'][0] . " pcs. (" . $data['kardus'][0] . " pcs.)",
                        'bronjong' => $data['bronjong'][0] . " pcs.",
                        'dolly' => $data['dolly'][0] . " pcs.",
                        'koli_retur' => ($data['koliRetur'][0] == 0) ? "-" : $data['koliRetur'][0] . " pcs. (atas No.DSPB: " . $data['dspbKoliRetur'][0] . " No.PB: " . $data['pbKoliRetur'][0] . ")",
                        'paket' => $data['msg'][0],
                    ];
                
                    if ($flagIGR && $flagPunyaICM) {
                        if (!$flagByPassICM) {
                            if (!empty($dtPBICM)) {
                                $data['list']['NoDSPBICM'] = !empty($data['dtPBICM'][0]->nodspb) ? $data['dtPBICM'][0]->nodspb : "-";
                                $data['list']['NoPBICM'] = !empty($data['dtPBICM'][0]->nopb) ? $data['dtPBICM'][0]->nopb : "-";
                            } else {
                                $data['list']['NoDSPBICM'] = "-";
                                $data['list']['NoPBICM'] = "-";
                            }
                        } else {
                            $data['list']['NoDSPBICM'] = "-";
                            $data['list']['NoPBICM'] = "-";
                        }
                    }
                
                    
                    
                    $this->DB_PGSQL->commit();
                    return $data;
                } else {
                    return (object)['errors'=>true];
                }
                
            } catch (\Throwable $th) {
                
                $this->DB_PGSQL->rollBack();
                // dd($th);
                return (object)['errors'=>true,'messages'=>$th->getMessage()];
            }

        }
        public function data_struk_hadiah($kodetoko,$nopb,$tglpb,$request){
            $sql = "select distinct count (1) from tbmaster_hadiahdcp where hdc_kodetoko = '$kodetoko' and hdc_nodokumen = '$nopb'";
            $jmlh_hadiah = $this->DB_PGSQL->select($sql);
            $IPMODUL = $request->getClientIp();
            $KodeIGR = session()->get('KODECABANG');
            $TokoOmi = $kodetoko;
            $NoOrder = $nopb;
            $NamaCab = "";
            $AlamatCab1 = "";
            $AlamatCab2 = "";
            $NamaPersh = "";
            $AlamatPersh1 = "";
            $AlamatPersh2 = "";
            $AlamatPersh3 = "";
            $NPWP = "";
            $NamaOMI = "";
            $dtHDH = [];  
            $msg = "";
            $tglTran = null;
            $STT = "";
            $StationMODUL = session()->get('KODECABANG');

            $toko = $kodetoko;
            $noPb = $nopb;    
            if (empty($toko) || empty($noPb)) {
                return (object)['errors'=>true];
            }


                $headerData = $this->DB_PGSQL->select("
                    SELECT PRS_NamaCabang, PRS_Alamat1, CONCAT(PRS_NamaWilayah, ' - TELP ', PRS_Telepon) AS AlamatCab2,
                            PRS_Namaperusahaan, PRS_AlamatFakturPajak1, PRS_AlamatFakturPajak2, PRS_AlamatFakturPajak3, 
                            CONCAT('NPWP: ', PRS_NPWP) AS NPWP
                    FROM tbMaster_perusahaan
                ");

                if (!empty($headerData)) {
                    $header = $headerData[0];
                    $NamaCab = $header->prs_namacabang;
                    $AlamatCab1 = $header->prs_alamat1;
                    $AlamatCab2 = $header->alamatcab2;
                    $NamaPersh = $header->prs_namaperusahaan;
                    $AlamatPersh1 = $header->prs_alamatfakturpajak1;
                    $AlamatPersh2 = $header->prs_alamatfakturpajak2;
                    $AlamatPersh3 = $header->prs_alamatfakturpajak3;
                    $NPWP = $header->npwp;
                }
             // Get OMI name
                $omiData = $this->DB_PGSQL->select("
                    SELECT TKO_NamaOMI
                    FROM tbMaster_TokoIGR
                    WHERE TKO_KodeIGR = '$KodeIGR'
                    AND TKO_KodeOMI = '$TokoOmi'
                ");

                if (!empty($omiData)) {
                    $NamaOMI = $omiData[0]->tko_namaomi;
                }
             // Get hadiah details
                // $hadiahDetails =  $this->DB_PGSQL->select("
                //     SELECT CONCAT(PRD_DeskripsiPendek, ' ', PRD_Unit, '/', PRD_Frac) AS PLU,
                //         fdkplu as prdcd,
                //         FLOOR(FDJQTY / PRD_FRAC) AS QTY,
                //         MOD(FDJQTY, PRD_Frac) AS FRAC
                //     FROM tbMaster_HadiahDCP, TEMP_CFL, TbMaster_Prodmast
                //     -- WHERE HDC_KodeIGR = '$KodeIGR'
                //     -- AND HDC_KodeToko = '$TokoOmi'
                //     -- AND HDC_NoDokumen = '$NoOrder'
                //     WHERE COALESCE(HDC_RecordID, 'X') = '2'
                //     AND PRD_KodeIGR = HDC_KodeIGR
                //     AND PRD_PRDCD = CONCAT(SUBSTR(HDC_PRDCD, 1, 6), '0')
                //     AND SUBSTR(FDKPLU, 1, 6) = SUBSTR(HDC_PRDCD, 1, 6)
                //     ORDER BY PRD_PRDCD
                // ");
                $hadiahDetails =  $this->DB_PGSQL->select("
                    SELECT CONCAT(HDC_KODETOKO, '  ', TKO_NAMAOMI) AS TOKOIDM, 
                            HDC_NODOKUMEN, 
                            PRD_PRDCD AS PRDCD,
                            CONCAT(PRD_DeskripsiPendek, ' ', PRD_Unit, '/', PRD_Frac) AS PLU, 
                            -- CAST(HDC_QTYPRDCD/PRD_FRAC AS SIGNED) AS QTY, --last error query
                            HDC_QTYPRDCD/PRD_FRAC AS QTY, 
                            MOD(HDC_QTYPRDCD, PRD_Frac) AS FRAC 
                    FROM tbMaster_HadiahDCP 
                    JOIN TbMaster_Prodmast ON HDC_KODEIGR = PRD_KODEIGR 
                    JOIN TBMASTER_TOKOIGR ON HDC_KODETOKO = TKO_KODEOMI 
                    WHERE SUBSTRING(HDC_PRDCD, 1, 6) || '0' = PRD_PRDCD 
                     AND HDC_KodeIGR = '$KodeIGR' -- command for debug
                     AND HDC_KodeToko = '$toko' -- command for debug
                     AND HDC_NoDokumen = '$noPb' -- command for debug
                    AND HDC_RecordID IS NULL
                    -- limit 10 -- debug
                ");

                if (!empty($hadiahDetails)) {
                    foreach ($hadiahDetails as $key => $value) {
                        $dtHDH[] = $value;
                    }
                }else{
                    return (object)['errors'=>true];
                }

                $this->DB_PGSQL->table('tbmaster_hadiahdcp')
                    ->whereRaw("hdc_kodeigr ='$KodeIGR'")
                    ->whereRaw("hdc_kodetoko ='$toko'")
                    ->whereRaw("hdc_nodokumen ='$noPb'")
                    ->update(['hdc_recordid' => '2']);
                
                return (object)[
                    'list_struk'=>$dtHDH,
                    'tglTran'=>date('Y-m-d'),
                    'NamaOMI'=>$NamaOMI,
                    'NamaCab'=>$NamaCab,
                    'AlamatCab1'=>$AlamatCab1,
                    'AlamatCab2'=>$AlamatCab2,
                    'NamaPersh'=>$NamaPersh,
                    'AlamatPersh1'=>$AlamatPersh1,
                    'AlamatPersh2'=>$AlamatPersh2,
                    'AlamatPersh3'=>$AlamatPersh3,
                    'NPWP'=>$NPWP 
                ];
        }
        public function data_pemutihan_batch($kodetoko,$nopb,$tglpb){
            $toko = $kodetoko;
            $noPb = $nopb;
            $tglpb = $tglpb;
            $batch = '';
            $batchOld = '';
            $Flg = '';
            $lengthBatch = 0;
            $UserMODUL =session()->get('userid');

            $this->DB_PGSQL->beginTransaction();

            try {
                // First query to fetch PBO_BATCH
                $query1 = "
                    SELECT DISTINCT PBO_BATCH 
                    FROM TBMASTER_PBOMI 
                     WHERE PBO_NOPB = '$noPb' -- command for debug
                     AND PBO_TGLPB::date = '$tglpb'::date -- command for debug
                     AND PBO_KODEOMI = '$toko' -- command for debug
                     AND PBO_BATCH IS NOT NULL -- command for debug
                    -- where PBO_BATCH IS NOT NULL --  debug
                    -- limit 10 --  debug
                ";
                $results1 =  $this->DB_PGSQL->select($query1);

                if (count($results1) > 0) {
                    // Second query to check if record exists
                    $query2 = "SELECT COUNT(1) AS count FROM TBTR_COUNTERPBOMI WHERE COU_KODEOMI = '$toko'";
                    $count =  $this->DB_PGSQL->selectOne($query2)->count;

                    if ($count > 0) {
                        // Third query to get length of COU_NODOKUMEN
                        $query3 = "SELECT LENGTH(COU_NODOKUMEN) AS length FROM TBTR_COUNTERPBOMI WHERE COU_KODEOMI = '$toko'";
                        $lengthBatch =  $this->DB_PGSQL->select($query3)[0]->length ?? 0;

                        if ($lengthBatch > 0) {
                            // Fourth query to get COU_NODOKUMEN
                            $query4 = "SELECT COU_NODOKUMEN FROM TBTR_COUNTERPBOMI WHERE COU_KODEOMI = '$toko'";
                            $batchOld =  $this->DB_PGSQL->select($query4)[0]->cou_nodokumen;

                            for ($i = 0; $i < $lengthBatch; $i++) {
                                $Flg = 'N';

                                foreach ($results1 as $row) {
                                    if (($i + 1) == $row->pbo_batch) {
                                        $batch .= ' ';
                                        $Flg = 'Y';
                                        // break;
                                    }
                                }

                                if ($Flg !== 'Y') {
                                    $batch .= substr($batchOld, $i, 1);
                                }
                            }

                            // Fifth query to update TBTR_COUNTERPBOMI
                            $query5 = "
                                UPDATE TBTR_COUNTERPBOMI 
                                SET COU_NODOKUMEN = '$batch', COU_MODIFY_DT = current_date, COU_MODIFY_BY = '$UserMODUL' 
                                WHERE COU_KODEOMI = '$toko'
                            ";
                             $this->DB_PGSQL->update($query5);

                            // Sixth query to update TBMASTER_PBOMI
                            $query6 = "
                                UPDATE TBMASTER_PBOMI 
                                SET PBO_BATCH = NULL, PBO_MODIFY_DT = current_date, PBO_MODIFY_BY = '$UserMODUL'
                                WHERE PBO_NOPB = '$noPb'
                                AND PBO_TGLPB::date = '$tglpb'::date 
                                AND PBO_KODEOMI = '$toko'
                            ";
                             $this->DB_PGSQL->update($query6);

                             $this->DB_PGSQL->commit();
                            return ['messages' => 'Data Terupdate!', 'status' => 'success'];
                        } else {
                            return ['errors'=>true,'messages' => 'Batch Toko ' . $toko . ' Belum Terisi!', 'status' => 'error'];
                        }
                    } else {
                        return ['errors'=>true,'messages' => 'Batch Tidak Diketemukan Di Counter Pb Omi!', 'status' => 'error'];
                    }
                } else {
                    return ['errors'=>true,'messages' => 'Tidak ada Data!', 'status' => 'error'];
                }
            } catch (Exception $ex) {
                 $this->DB_PGSQL->rollBack();
                return ['errors'=>true,'message' => 'Gagal Update! ' . substr($ex->getMessage(), 0, 100), 'status' => 'error'];
            }
        }
        public function data_cetak_ba_ulang($no_bpbr,$tgl_ret){
            $noRet = $no_bpbr;
            $tglRet = $tgl_ret;
            $KodeIGR = session()->get('KODECABANG');
    
            try {
                $sql = "SELECT BTH_TYPE, BTH_KODEMEMBER, bth_tgldoc, bth_nonrb
                        FROM TBTR_BATOKO_H 
                        WHERE BTH_PBR = '$noRet'
                        AND BTH_tgpBR = '$tglRet'
                        limit 10
                        ";
                
                $dtBa = $this->DB_PGSQL->select($sql);
   
                if (count($dtBa) > 0) {
                    $dataset = [];
                    $type = $dtBa[0]->bth_type;
                    $kodeMember = $dtBa[0]->bth_kodemember;
                    $sql = "SELECT tko_kodeigr AS kode_igr, TKO_NAMAOMI, TKO_KODEOMI
                            FROM tbmaster_tokoigr
                            WHERE TKO_KODECUSTOMER = '$kodeMember '
                            AND TKO_NAMASBU = 'INDOMARET'
                            limit 1 -- debug";
            
                    $tokoData = $this->DB_PGSQL->select($sql);


                    //debug data
                    // $json_string = '[{"kode_igr":"22","btd_prdcd":"0033580","prd_deskripsipendek":"MARJAN MELON 460ML","btd_dspb":"0","btd_cctv":"0","btd_qtynrb":"0","qtybigr":"-1","qtybidm":"1","btd_price":"16215","bigr":"-17836.5","bidm":"17836.5","bth_nonrb":"1593","bth_tglnrb":"2015-11-14 00:00:00","bth_nodoc":"22\\\\0152\\\\11\\\\15","bth_tgldoc":"2015-11-17 00:00:00","bth_pbr":"1500885"},{"kode_igr":"22","btd_prdcd":"1268980","prd_deskripsipendek":"FF KENTAL MANIS 6X38","btd_dspb":"0","btd_cctv":"0","btd_qtynrb":"0","qtybigr":"-1","qtybidm":"1","btd_price":"6131","bigr":"-6744.1","bidm":"6744.1","bth_nonrb":"1552","bth_tglnrb":"2015-11-17 00:00:00","bth_nodoc":"22\\\\0213\\\\11\\\\15","bth_tgldoc":"2015-11-19 00:00:00","bth_pbr":"1501109"},{"kode_igr":"22","btd_prdcd":"1268980","prd_deskripsipendek":"FF KENTAL MANIS 6X38","btd_dspb":"0","btd_cctv":"0","btd_qtynrb":"0","qtybigr":"-1","qtybidm":"1","btd_price":"6131","bigr":"-6744.1","bidm":"6744.1","bth_nonrb":"1769","bth_tglnrb":"2015-11-16 00:00:00","bth_nodoc":"22\\\\0215\\\\11\\\\15","bth_tgldoc":"2015-11-19 00:00:00","bth_pbr":"1501052"},{"kode_igr":"22","btd_prdcd":"0030180","prd_deskripsipendek":"SPRITE 1500 ML","btd_dspb":"0","btd_cctv":"0","btd_qtynrb":"0","qtybigr":"-1","qtybidm":"1","btd_price":"11645","bigr":"-12809.5","bidm":"12809.5","bth_nonrb":"1675","bth_tglnrb":"2015-11-20 00:00:00","bth_nodoc":"22\\\\0270\\\\11\\\\15","bth_tgldoc":"2015-11-23 00:00:00","bth_pbr":"1501307"},{"kode_igr":"22","btd_prdcd":"1268970","prd_deskripsipendek":"FF KENTAL MANIS 370G","btd_dspb":"0","btd_cctv":"0","btd_qtynrb":"0","qtybigr":"-1","qtybidm":"1","btd_price":"8032","bigr":"-8835.2","bidm":"8835.2","bth_nonrb":"1675","bth_tglnrb":"2015-11-20 00:00:00","bth_nodoc":"22\\\\0270\\\\11\\\\15","bth_tgldoc":"2015-11-23 00:00:00","bth_pbr":"1501307"},{"kode_igr":"22","btd_prdcd":"1268980","prd_deskripsipendek":"FF KENTAL MANIS 6X38","btd_dspb":"0","btd_cctv":"0","btd_qtynrb":"0","qtybigr":"-1","qtybidm":"1","btd_price":"6133","bigr":"-6746.3","bidm":"6746.3","bth_nonrb":"1675","bth_tglnrb":"2015-11-20 00:00:00","bth_nodoc":"22\\\\0270\\\\11\\\\15","bth_tgldoc":"2015-11-23 00:00:00","bth_pbr":"1501307"},{"kode_igr":"22","btd_prdcd":"1268980","prd_deskripsipendek":"FF KENTAL MANIS 6X38","btd_dspb":"0","btd_cctv":"0","btd_qtynrb":"0","qtybigr":"-1","qtybidm":"1","btd_price":"6133","bigr":"-6746.3","bidm":"6746.3","bth_nonrb":"1677","bth_tglnrb":"2015-11-20 00:00:00","bth_nodoc":"22\\\\0272\\\\11\\\\15","bth_tgldoc":"2015-11-23 00:00:00","bth_pbr":"1501318"},{"kode_igr":"22","btd_prdcd":"0030180","prd_deskripsipendek":"SPRITE 1500 ML","btd_dspb":"0","btd_cctv":"0","btd_qtynrb":"0","qtybigr":"-1","qtybidm":"1","btd_price":"11640","bigr":"-12804","bidm":"12804","bth_nonrb":"1783","bth_tglnrb":"2015-11-19 00:00:00","bth_nodoc":"22\\\\0275\\\\11\\\\15","bth_tgldoc":"2015-11-23 00:00:00","bth_pbr":"1501337"},{"kode_igr":"22","btd_prdcd":"0030200","prd_deskripsipendek":"FANTA STRAWBERY 1500","btd_dspb":"0","btd_cctv":"0","btd_qtynrb":"0","qtybigr":"-12","qtybidm":"12","btd_price":"11640","bigr":"-153648","bidm":"153648","bth_nonrb":"1783","bth_tglnrb":"2015-11-19 00:00:00","bth_nodoc":"22\\\\0275\\\\11\\\\15","bth_tgldoc":"2015-11-23 00:00:00","bth_pbr":"1501337"},{"kode_igr":"22","btd_prdcd":"0030170","prd_deskripsipendek":"COCA COLA 1500 ML","btd_dspb":"0","btd_cctv":"0","btd_qtynrb":"0","qtybigr":"-12","qtybidm":"12","btd_price":"11640","bigr":"-153648","bidm":"153648","bth_nonrb":"1783","bth_tglnrb":"2015-11-19 00:00:00","bth_nodoc":"22\\\\0275\\\\11\\\\15","bth_tgldoc":"2015-11-23 00:00:00","bth_pbr":"1501337"}]';
                    // $debug_data = json_decode($json_string);
                    // $dataset = $debug_data;
                    //debug data end
    
                    if ($type == 'F') {
                        $sql = "SELECT prd_kodeigr AS kode_igr, 
                                (SELECT BTH_NODOC FROM tbtr_batoko_h WHERE BTH_PBR = rom_nodokumen 
                                AND BTH_tgpBR = rom_tgldokumen LIMIT 1) AS noba, 
                                rom_nodokumen, rom_tgldokumen, rom_prdcd, prd_deskripsipendek, 
                                (rom_qty + rom_qtytlr) AS qtynrb, 'Kekurangan penerimaan retur' AS tipe, 
                                prd_kodetag AS tag, rom_qtyrealisasi AS fisik, 
                                ((rom_qty + rom_qtytlr) - rom_qtyrealisasi) AS fisikkrg, 0 AS fisiktolak, 
                                rom_qtytlr AS ba, rom_hrgsatuan, 
                                CASE WHEN PRD_FLAGBKP1 = 'Y' THEN 
                                (((rom_qty + rom_qtytlr) - rom_qtyrealisasi) * rom_hrgsatuan) * (1+(COALESCE(PRD_PPN,0)/100)) 
                                ELSE (((rom_qty + rom_qtytlr) - rom_qtyrealisasi) * rom_hrgsatuan) END AS ttl, 
                                rom_noreferensi, rom_tglreferensi 
                                FROM tbtr_returomi, tbmaster_prodmast 
                                WHERE rom_prdcd = prd_prdcd AND rom_kodeigr = prd_kodeigr 
                                AND rom_nodokumen = '$noRet' 
                                AND rom_tgldokumen::date ='$tglRet'::date
                                AND ((rom_qty + rom_qtytlr) - rom_qtyrealisasi) > 0 
                                UNION ALL 
                                SELECT prd_kodeigr AS kode_igr, 
                                (SELECT BTH_NODOC FROM tbtr_batoko_h WHERE BTH_PBR = rom_nodokumen 
                                AND BTH_tgpBR = rom_tgldokumen LIMIT 1) AS noba, 
                                rom_nodokumen, rom_tgldokumen, rom_prdcd, prd_deskripsipendek, 
                                (rom_qty + rom_qtytlr) AS qtynrb, 'Tolakan barang retur' AS tipe, 
                                (SELECT hgb_statusbarang FROM tbmaster_hargabeli WHERE hgb_prdcd = rom_prdcd 
                                AND hgb_tipe = '2' LIMIT 1) AS tag, rom_qtyrealisasi AS fisik, 0 AS fisikkrg, 
                                (rom_qtyrealisasi - (rom_qtymlj + rom_qtytlj)) AS fisiktolak, 
                                rom_qtytlr AS ba, rom_hrgsatuan, 
                                CASE WHEN PRD_FLAGBKP1 = 'Y' THEN 
                                ((rom_qtyrealisasi - (rom_qtymlj + rom_qtytlj)) * rom_hrgsatuan) * (1+(COALESCE(PRD_PPN,0)/100)) 
                                ELSE ((rom_qtyrealisasi - (rom_qtymlj + rom_qtytlj)) * rom_hrgsatuan) END AS ttl, 
                                rom_noreferensi, rom_tglreferensi 
                                FROM tbtr_returomi, tbmaster_prodmast 
                                WHERE rom_prdcd = prd_prdcd AND rom_kodeigr = prd_kodeigr 
                                AND rom_nodokumen = '$noRet' 
                                AND rom_tgldokumen::date ='$tglRet'::date
                                AND (rom_qtyrealisasi - (rom_qtymlj + rom_qtytlj)) > 0";
    
                        $dataset = $this->DB_PGSQL->select($sql);
    
                    } else {
                        $sql = "SELECT prd_kodeigr AS kode_igr, btd_prdcd, prd_deskripsipendek, btd_dspb, btd_cctv, 
                                BTD_QTYNRB, (BTD_QTYNRB - BTD_QTY) AS qtybigr, BTD_QTY AS qtybidm, BTD_PRICE, 
                                ((BTD_QTYNRB - BTD_QTY) * BTD_PRICE) + ((BTD_QTYNRB - BTD_QTY) * 
                                CASE WHEN btd_ppn > 0 THEN (btd_ppn/btd_qty) ELSE 0 END) AS bigr, 
                                (BTD_QTY * BTD_PRICE) + (BTD_QTY * CASE WHEN btd_ppn > 0 THEN (btd_ppn/btd_qty) ELSE 0 END) AS bidm, 
                                LPAD(CAST(BTH_NONRB AS TEXT), 4, '0') AS BTH_NONRB, BTH_TGLNRB, BTH_NODOC, BTH_TGLDOC, BTH_PBR 
                                FROM tbtr_batoko_d, tbmaster_prodmast, TBTR_BATOKO_H 
                                WHERE btd_prdcd = prd_prdcd AND PRD_KODEIGR = '$KodeIGR' AND BTH_ID = BTD_ID 
                                 AND BTH_TYPE = 'P' AND BTH_PBR = '$noRet'   -- command for debug
                                 AND BTH_tgpBR::date ='$tglRet'::date  -- command for debug
                                -- LIMIT 10 -- debug
                                ";
    
                        $dataset = $this->DB_PGSQL->select($sql);
                    }
    
                    if (count($dataset) > 0) {
                        return (object)['data'=>$dataset,'messages' => 'success' ,'type'=>$type,'data_header'=>$dtBa[0],'toko'=>$tokoData];
                    } else {
                        return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
                    }
                } else {

                    return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
                }
            } catch (Exception $ex) {
                
                return (object)['errors'=>true ,'messages' => 'Error: ' . $ex->getMessage()];
            }
        }
        public function data_cetak_bpbr_ulang($noRet, $tglRet){

            $tglRetFormatted = date('d/m/Y', strtotime($tglRet));

            // First Query
            $dtBa =  $this->DB_PGSQL->select("
                SELECT TRPT_CUS_KODEMEMBER
                FROM TBTR_piutang
                 WHERE TRPT_SALESINVOICEDATE::date <= '$tglRet'::date -- command for debug
                 AND TRPT_SALESINVOICENO = '$noRet' -- command for debug
                -- limit 1 -- debug
            ");

            if (!empty($dtBa)) {
                // Second Query
                $data =  $this->DB_PGSQL->select("
                    SELECT prd_kodeigr AS kode_igr, ROM_NODOKUMEN, ROM_TGLDOKUMEN, ROM_PRDCD, PRD_UNIT, ROM_NOREFERENSI, 
                    prd_frac, prd_deskripsipendek, (ROM_QTY + ROM_QTYTLR) AS qty, ROM_QTY AS qtyf, 
                    ((ROM_QTY + ROM_QTYTLR) - ROM_QTYREALISASI) AS fisikkrg, 
                    (ROM_QTYREALISASI - (ROM_QTYMLJ + ROM_QTYTLJ)) AS fisiktolak, ROM_QTYTLR AS ba, 
                    (ROM_QTY * ROM_AVGCOST) AS ttl_Avg, (ROM_QTYTLR * ROM_HRGSATUAN) AS ttl, ROM_HRGSATUAN, ROM_AVGCOST, 
                    rom_tglreferensi, ROM_TGLREFERENSI, 
                    (SELECT BTH_NODOC FROM TBTR_BATOKO_H WHERE BTH_PBR = ROM_NODOKUMEN AND BTH_TGPBR = rom_tgldokumen LIMIT 1) AS noba
                    FROM tbtr_returomi, tbmaster_prodmast
                    WHERE ROM_PRDCD = prd_prdcd AND ROM_KODEIGR = prd_kodeigr 
                     AND ROM_NODOKUMEN = '$noRet'-- command for debug
                     AND rom_tgldokumen::date = '$tglRet'::date-- command for debug
                    -- limit 10 -- debug
                ");

                if (!empty($data)) {
                    // Third Query
                    $perusahaan =  $this->DB_PGSQL->select("
                        SELECT prs_kodeigr AS kode_igr, PRS_NAMACABANG 
                        FROM tbmaster_perusahaan
                    ");

                    // Fourth Query
                    $kodeCustomer = $dtBa[0]->trpt_cus_kodemember;
                    $toko =  $this->DB_PGSQL->select("
                        SELECT tko_kodeigr AS kode_igr, TKO_NAMAOMI, TKO_KODEOMI
                        FROM tbmaster_tokoigr
                         WHERE TKO_KODECUSTOMER = '$kodeCustomer' -- command for debug
                         AND TKO_NAMASBU = 'INDOMARET' -- command for debug
                        -- where TKO_NAMASBU = 'INDOMARET' -- debug
                        -- limit 1 -- debug
                    ");

                    // Simulate report generation process
                    $reportData = [
                        'DATA' => $data,
                        'PERUSAHAAN' => $perusahaan,
                        'TOKO' => $toko,
                    ];

                    return (object)['data'=> $reportData,'messages' => 'Berhasil'];
                } else {
                    return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
                }
            } else {
                return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
            }
        }

        public function data_beban_retur_igr($noRet, $tglRet){
            $tglRetFormatted = date('d/m/Y', strtotime($tglRet));
            $KodeIGR = session()->get('KODECABANG');

            // First Query
            $data = $this->DB_PGSQL->select("
                SELECT a.*, prd_deskripsipendek
                FROM TBTR_BEBANRETURIGR a, tbmaster_prodmast
                WHERE prd_kodeigr = '$KodeIGR'
                AND PRD_PRDCD = BRI_PRDCD
                 AND BRI_ID = '$noRet' -- command for debug
                 AND BRI_TGL = $tglRet'date -- command for debug
                -- limit 10 -- debug
            ");

            if (!empty($data)) {
                // Second Query
                $perusahaan = $this->DB_PGSQL->select("
                    SELECT prs_kodeigr AS kode_igr, PRS_NAMACABANG
                    FROM tbmaster_perusahaan
                ");

                // Third Query
                $BRI_MEMBER = $data[0]->bri_member;
                $toko = $this->DB_PGSQL->select("
                    SELECT tko_kodeigr AS kode_igr, TKO_NAMAOMI, TKO_KODEOMI
                    FROM tbmaster_tokoigr
                    WHERE TKO_KODECUSTOMER = '$BRI_MEMBER'
                    AND TKO_NAMASBU = 'INDOMARET'
                ");

                // Simulate report generation process
                $reportData = [
                    'DATA' => $data,
                    'PERUSAHAAN' => $perusahaan,
                    'TOKO' => $toko,
                ];

                return (object)['data'=> $reportData,'messages' => 'Berhasil'];
            } else {
                return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
            }
            //  return (object)['errors'=>true];
        }
        public function data_analisa_crm($tgl1, $tgl2){
            $tgl1Formatted = date('Y-m-d',strtotime($tgl1));
            $tgl2Formatted = date('Y-m-d',strtotime($tgl2));
            $KodeIGR = session()->get('KODECABANG');

            // Build the SQL query
            $sql = "
                SELECT * FROM (
                    SELECT 
                        '$tgl1Formatted'::date TG1, 
                        '$tgl2Formatted'::date TG2, 
                        '$KodeIGR' kd_igr, 
                        jh_cus_kodemember kode_member, 
                        SUBSTR(cus_namamember, 1, 15) nama_member, 
                        CASE WHEN desa IS NULL THEN 'UNKNOWN' ELSE desa END kelurahan, 
                        CASE WHEN KECAMATAN IS NULL THEN 'UNKNOWN' ELSE KECAMATAN END kecamatan, 
                        CASE WHEN KABKOT IS NULL THEN 'UNKNOWN' ELSE KABKOT END kabupaten, 
                        CASE WHEN PROVINSI IS NULL THEN 'UNKNOWN' ELSE PROVINSI END provinsi, 
                        kode2010, tps, dpt, avg_dptkec, avg_dptkel, CRM_KOORDINAT, 
                        CASE WHEN grp_kategori IS NULL THEN 'RETAILER' ELSE grp_kategori END kategori, 
                        luas, cus_alamatmember1, seg_nama segmentasi_crm, sales 
                    FROM (
                        SELECT 
                            jh_kodeigr, 
                            jh_cus_kodemember, 
                            SUM(CASE 
                                WHEN jh_transactiontype = 'S' THEN jh_transactionamt 
                                WHEN jh_transactiontype = 'R' THEN jh_transactionamt * -1 
                            END) sales 
                        FROM tbtr_jualheader 
                        WHERE 
                             jh_transactiondate::date >= '$tgl1Formatted'::date -- command for debug
                             AND jh_transactiondate::date <= '$tgl2Formatted'::date -- command for debug
                             AND (jh_transactiontype = 'R' OR jh_transactiontype = 'S') -- command for debug
                             -- jh_transactiontype = 'R' OR jh_transactiontype = 'S' -- debug
                        GROUP BY jh_cus_kodemember, jh_kodeigr
                        -- limit 20 -- debug
                    ) sls 
                    LEFT JOIN tbmaster_customer ON jh_cus_kodemember = cus_kodemember 
                        AND jh_kodeigr = cus_kodeigr  
                        AND COALESCE(CUS_FLAGMEMBERKHUSUS, 'N') = 'Y' 
                    LEFT JOIN tbmaster_customercrm ON cus_kodemember = crm_kodemember 
                    LEFT JOIN tbmaster_segmentasi ON crm_idsegment = seg_id 
                    LEFT JOIN tbmaster_perusahaan ON jh_kodeigr = prs_kodeigr 
                    LEFT JOIN tbmaster_customerpoly ON crm_kodemember = poly_kodemember 
                    LEFT JOIN tbmaster_polygonid ON poly_polygonid = id_auto 
                    LEFT JOIN tbtabel_groupkategori ON crm_idgroupkat = grp_idgroupkat
                ) q 
                ORDER BY provinsi, kabupaten, kecamatan, kelurahan, segmentasi_crm, sales
            ";

            // Execute the query
            $data = $this->DB_PGSQL->select($sql);

            if (!empty($data)) {
                // Fetch company information
                $perusahaan = $this->DB_PGSQL->select("SELECT prs_kodeigr AS kode_igr, PRS_NAMACABANG FROM tbmaster_perusahaan");

                // Simulate report generation process
                $reportData = [
                    'DATA' => $data,
                    'PERUSAHAAN' => $perusahaan,
                ];

                return (object)['data'=> $reportData,'messages' => 'Berhasil'];
            } else {
                return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
            }
    
            //  return (object)['errors'=>true];
        }
        public function data_absensi_wt($tgl1, $tgl2){
            $tgl1Formatted = date('Y-m-d',strtotime($tgl1));
            $tgl2Formatted = date('Y-m-d',strtotime($tgl2));
            $KodeIGR = session()->get('KODECABANG');
            $data_group = [];
            // Build the SQL query
            $sql = "
                SELECT DISTINCT '$tgl1Formatted'::date TG1, 
                                '$tgl2Formatted'::date TG2, 
                                '$KodeIGR' KD_IGR, 
                                shop || ' - ' || tko_namaomi shop, 
                                nm_wt, 
                                tgl1, 
                                'Sukses Diproses' KETERANGAN, 
                                'SUKSES' flag 
                FROM tbtr_wt_interface, tbmaster_tokoigr 
                 WHERE wt_create_dt::date >= '$tgl1Formatted'::date  -- command for debug
                 AND wt_create_dt::date <= '$tgl2Formatted'::date  -- command for debug
                 AND tko_kodeomi = shop  -- command for debug
                -- WHERE tko_kodeomi = shop  -- debug
                
                UNION ALL 
                
                SELECT DISTINCT '$tgl1Formatted'::date TG1, 
                                '$tgl2Formatted'::date TG2, 
                                '$KodeIGR' KD_IGR, 
                                tlk_toko || ' - ' || tko_namaomi shop, 
                                TLK_NAMAWT, 
                                TLK_CREATE_DT, 
                                TLK_MESSAGE, 
                                'TOLAKAN' flag 
                FROM tbtr_tolakanwt, tbmaster_tokoigr 
                 WHERE TLK_CREATE_DT::date >= '$tgl1Formatted'::date  -- command for debug
                 AND TLK_CREATE_DT::date <= '$tgl2Formatted'::date  -- command for debug
                 AND tko_kodeomi = tlk_toko  -- command for debug
                -- WHERE tko_kodeomi = tlk_toko  -- debug
                
                ORDER BY shop 
                -- limit 20  -- debug
            ";

            // Execute the query
            $data = $this->DB_PGSQL->select($sql);

            foreach ($data as $key => $value) {
                $data_group[$value->flag][] = $value;
            }

            if (!empty($data)) {
                // Fetch company information
                $perusahaan = $this->DB_PGSQL->select("SELECT prs_kodeigr AS kode_igr, PRS_NAMACABANG FROM tbmaster_perusahaan");

                // Simulate report generation process
                $reportData = [
                    'DATA' => $data_group,
                    'PERUSAHAAN' => $perusahaan,
                ];

                return (object)['data'=> $reportData,'messages' => 'Berhasil'];
            } else {
                return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
            }
            //  return (object)['errors'=>true];
        }
        public function data_listing_ba($tgl1, $tgl2){ 
            $tgl1Formatted = date('Y-m-d',strtotime($tgl1));
            $tgl2Formatted = date('Y-m-d',strtotime($tgl2));
            $KodeIGR = session()->get('KODECABANG'); 
            $sql = "
                select '$tgl1Formatted'::date TG1, 
                        '$tgl2Formatted'::date TG2, 
                    TRPT_KODEIGR kd_igr, 
                    TKO_KODEOMI, 
                    trpt_salesinvoiceno, 
                    trpt_salesinvoicedate, 
                    trpt_invoicetaxno, 
                    trpt_invoicetaxdate, 
                    bth_nodoc, 
                    bth_dpp idmdpp, 
                    bth_ppn idmppn, 
                    igrdpp, 
                    igrppn 
                from tbmaster_tokoigr 
                join tbtr_batoko_h on tko_kodecustomer = bth_kodemember 
                join tbtr_piutang on bth_kodemember = trpt_cus_kodemember 
                and bth_nonrb = cast(trpt_invoicetaxno as int) 
                and bth_tglnrb::date = trpt_invoicetaxdate::date 
                left join (SELECT bri_id, 
                                bri_nrb, 
                                bri_member, 
                                bri_tgl, 
                                bri_tnrb, 
                                SUM(bri_price * bri_qty) igrdpp, 
                                SUM(bri_ppn) igrppn 
                        FROM tbtr_bebanreturigr 
                         WHERE bri_tgl >= '$tgl1Formatted'::date -- command for debug
                         AND bri_tgl <= '$tgl2Formatted'::date -- command for debug
                        GROUP BY bri_id, bri_nrb, bri_member, bri_tgl, bri_tnrb) c 
                ON trpt_cus_kodemember = c.bri_member 
                AND cast(trpt_invoicetaxno as int) = cast(c.bri_nrb as int) 
                 where bth_tgpbr::date >= '$tgl1Formatted'::date -- command for debug
                 and bth_tgpbr::date <= '$tgl2Formatted'::date -- command for debug
                 and trpt_recordid is null -- command for debug
                order by TKO_KODEOMI
                -- limit 10 -- debug
            ";

            // Execute the query
            $data = $this->DB_PGSQL->select($sql);

            if (!empty($data)) {
                // Fetch company information
                $perusahaan = DB::select("SELECT prs_kodeigr AS kode_igr, PRS_NAMACABANG FROM tbmaster_perusahaan");

                // Simulate report generation process
                $reportData = [
                    'DATA' => $data,
                    'PERUSAHAAN' => $perusahaan,
                ];

                return (object)['data'=> $reportData,'messages' => 'Berhasil'];
            } else {
                return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
            }
        }
        public function data_retur_idm($tgl1, $tgl2){
            $tgl1Formatted = date('Y-m-d',strtotime($tgl1));
            $tgl2Formatted = date('Y-m-d',strtotime($tgl2));
            $KodeIGR = session()->get('KODECABANG');
            
            $sql = " 
            SELECT '$tgl1Formatted'::date TG1, 
                   '$tgl2Formatted'::date TG2, 
                    trpt_kodeigr  kode_igr, tko_kodeomi  
                || ' ' || tko_namaomi tko_namaomi, 
                trpt_salesinvoicedate, 
                trpt_salesinvoiceno, 
                no_nrb, 
                tgl_nrb, 
                tot_di_piutang, 
                jns_retur, 
                plu, 
                coalesce(qty,0) qty_rtr_idm, 
                coalesce(rom_qty,0) qty_rtr_fsk, 
                coalesce(rom_ttl,0) rph_rtr_fsk, 
                coalesce(btd_qty,0) qty_ba_idm, 
                coalesce(ba_idm, 0) ba_idm, 
                coalesce(bri_qty, 0) qty_ba_igr, 
                coalesce(ba_igr, 0) ba_igr 
                FROM (SELECT tko_kodeomi, TRPT_KODEIGR,
                qty, 
                tko_namaomi, 
                trpt_salesinvoicedate, 
                trpt_salesinvoiceno, 
                trpt_invoicetaxno no_nrb, 
                trpt_invoicetaxdate tgl_nrb, 
                trpt_salesvalue tot_di_piutang, 
                CASE 
                WHEN istype = '01' 
                THEN 'P' 
                ELSE 'F' 
                END jns_retur, 
                prc_pluigr plu 
                FROM tbtr_piutang 
                JOIN tbmaster_tokoigr 
                ON trpt_kodeigr = tko_kodeigr 
                AND trpt_cus_kodemember = tko_kodecustomer 
                JOIN tbtr_wt_interface 
                ON trpt_invoicetaxno = docno 
                AND trpt_invoicetaxdate = tgl1 
                AND tko_kodeomi = shop 
                LEFT JOIN tbmaster_prodcrm 
                ON prdcd = prc_pluidm 
                WHERE trpt_type = 'D' 
                 AND trpt_salesinvoicedate::date >= '$tgl1Formatted'::date -- command for debug
                 AND trpt_salesinvoicedate::date <=  '$tgl2Formatted'::date -- command for debug
                ) h 
                LEFT JOIN 
                (SELECT bth_pbr, 
                        bth_tgpbr, 
                        btd_prdcd, 
                        btd_qty, 
                        (btd_qty * btd_price) + btd_ppn ba_idm, 
                        bth_nonrb, 
                        bth_tglnrb, 
                        bth_kodemember kdmember 
                FROM tbtr_batoko_h, tbtr_batoko_d 
                WHERE bth_id = btd_id 
                ) baidm 
                ON trpt_salesinvoiceno = bth_pbr 
                AND trpt_salesinvoicedate = bth_tgpbr 
                AND plu = btd_prdcd 
                LEFT JOIN 
                (SELECT bri_id, 
                    bri_tgl, 
                    bri_prdcd, 
                    bri_qty, 
                    (bri_price * bri_qty) + bri_ppn ba_igr, 
                    bri_nrb, 
                    bri_tnrb, 
                    bri_member 
                FROM tbtr_bebanreturigr 
                ) baigr 
                ON trpt_salesinvoiceno = bri_id 
                AND trpt_salesinvoicedate = bri_tgl 
                AND plu = bri_prdcd 
                LEFT JOIN 
                tbtr_returomi 
                ON trpt_salesinvoiceno = rom_nodokumen 
                AND trpt_salesinvoicedate = rom_tgldokumen 
                AND plu = rom_prdcd 
                -- limit 10 -- debug
            ";

            $data = $this->DB_PGSQL->select($sql);

            if (count($data) > 0) {
                return (object)['data'=>$data,'messages' => 'success' ];
            } else {
                return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
            }

        }
        public function data_outstanding_retur(){
            $data_group = [];
            $sql = "
                SELECT w.shop || ' (' || t.tko_namaomi || ' - ' || t.tko_kodecustomer || ')' AS KDTOKO,
                    w.DOCNO AS NODOC,
                    CASE
                        WHEN SUBSTR(w.keterangan, 0, 3) = '011' THEN 'P'
                        ELSE 'F'
                    END AS tipe,
                    w.PRDCD AS PLUIDM,
                    m.prc_pluigr AS PLUIGR,
                    COALESCE(p.prd_deskripsipanjang, '') AS NMBRG,
                    w.qty AS QTY,
                    w.price_idm AS HARGA,
                    w.ppnrp_idm AS PPN,
                    (w.qty * w.price_idm) + w.ppnrp_idm AS TOTAL
                FROM tbtr_wt_interface w
                JOIN tbmaster_prodcrm m ON m.prc_pluidm = w.prdcd
                JOIN tbmaster_prodmast p ON m.prc_pluigr = p.prd_prdcd
                JOIN tbmaster_tokoigr t ON w.shop = t.tko_kodeomi
                WHERE w.recid <> 'P'
                -- limit 20 -- debug
            ";

            $data = $this->DB_PGSQL->select($sql);

            foreach ($data as $key => $data_list) {
               $data_group[$data_list->kdtoko][$data_list->nodoc][] = $data_list;
            }
            if (count($data) > 0) {
                return (object)['data'=>$data_group,'messages' => 'success' ];
            } else {
                return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
            }
        }
        public function data_cetak_ba_bronjong($noba,$tglba){

            // Build the SQL query
            $sql = "
                SELECT 
                    bab_nodoc AS NOBA, 
                    TRIM(TO_CHAR(bab_tgldoc, 'Day')) || ', ' || TO_CHAR(bab_tgldoc, 'DD-MM-YYYY') AS TGLBA, 
                    bab_kodetoko || ' - ' || tko_namaomi AS TOKO, 
                    bab_nodspb || ' / ' || TO_CHAR(bab_tgldspb, 'DD-MM-YYYY') AS DSPB, 
                    COALESCE(bab_qtybronjong, 0) AS BA_BRONJONG, 
                    COALESCE(bab_qtydolly, 0) AS BA_DOLLY 
                FROM tbtr_ba_bronjong 
                JOIN tbmaster_tokoigr ON bab_kodetoko = tko_kodeomi 
                -- WHERE bab_id = '$noba' -- command for debug
                -- AND bab_tgldoc::date = '$tglba'::date -- command for debug
                limit 1 -- debug
            ";
    
            $dtBa = $this->DB_PGSQL->select($sql);
            if (count($dtBa) > 0) {
                return (object)['data'=>$dtBa,'messages' => 'success' ];
            } else {
                return (object)['errors'=>true,'messages' => 'Tidak ada data!'];
            }
        }

    /**
     * END IDM1
     */

    /**
     * IDM2
     */
    
        public function print_rtbr(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'rtbr';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'RTBR';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_tolakan_retur(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_tolakan_retur($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'tolakan_retur';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'TOLAKAN_RETUR';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_cetak_ba_acost(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_cetak_ba_acost($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'cetak_ba_acost';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'CETAK_BA_ACOST';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_history_dspb_roti(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_history_dspb_roti($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'history_dspb_roti';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'HISTORY_DSPB_ROTI';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_rekap_dspb_roti(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_rekap_dspb_roti($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'rekap_dspb_roti';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'REKAP_DSPB_ROTI';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_history_dspb_voucher(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_history_dspb_voucher($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'history_dspb_voucher';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'HISTORY_DSPB_VOUCHER';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_history_rubah_status(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_history_rubah_status($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'history_rubah_status';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'HISTORY_RUBAH_STATUS';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_history_paket_ipp(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_history_paket_ipp($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'history_paket_ipp';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'HISTORY_PAKET_IPP';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_rekap_pindah_lokasi(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_rekap_pindah_lokasi($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'rekap_pindah_lokasi';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'REKAP_PINDAH_LOKASI';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_npb_web_service(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_npb_web_service($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'npb_web_service';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'NPB_WEB_SERVICE';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_perubahan_status_retur(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_perubahan_status_retur($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'perubahan_status_retur';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PERUBAHAN_STATUS_RETUR';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_retur_supplier(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_retur_supplier($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'retur_supplier';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'RETUR_SUPPLIER';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_serah_terima_retur(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_serah_terima_retur($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'serah_terima_retur';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'SERAH_TERIMA_RETUR';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_cetak_history_qrcode(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            // $data_report['data'] = $this->data_cetak_history_qrcode($kodetoko,$nopb,$tglpb);
            // dd($data_report);
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'cetak_history_qrcode';
            // $data_report['folder_page'] = 'idm2';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'CETAK_HISTORY_QRCODE';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }


        public function data_rtbr($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_tolakan_retur($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_cetak_ba_acost($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_history_dspb_roti($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_rekap_dspb_roti($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_history_dspb_voucher($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_history_rubah_status($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_history_paket_ipp($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_rekap_pindah_lokasi($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_npb_web_service($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_perubahan_status_retur($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_retur_supplier($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_serah_terima_retur($kodetoko,$nopb,$tglpb){
                    return (object)['errors'=>true];
        }
        public function data_cetak_history_qrcode($kodetoko,$tglpb){
                    return (object)['errors'=>true];
        }

    /**
     * END IDM2
     */
    
    /**
     * OMI
     */
    
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

        public function print_struk_hadiah_omi(Request $request){

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

        public function print_outstanding_dsp(Request $request){

            $kodetoko = $request->toko;
            $kodetoko2 = $request->toko2;
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['kodetoko2'] = $kodetoko2;
            // $data_report['nopb'] = $nopb;
            // $data_report['tglpb'] = $tglpb;
            $data_report['filename'] = 'outstanding-dsp';
            $data_report['jenis_page'] = 'default-page';
            $data_report['folder_page'] = 'omi';
            $data_report['title_report'] = 'OUTSTANDING-DSP';
            $encrypt_data = base64_encode(json_encode($data_report));
            //    $decrypt_data = json_decode(base64_decode($encrypt_data));
            $link = url('/api/print/report/'.$encrypt_data);

            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
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

        public function data_outstanding_dsp($kodetoko = null, $kodetoko2 = null){
                $sql = "SELECT HDR.*, TKO_NAMAOMI FROM (
                            SELECT ikl_kodeidm, ikl_nopb, 
                                to_char(ikl_tglbpd, 'dd-MM-YYYY') AS ikl_tglbpd, 
                                ikl_registerrealisasi, 
                                SUM(ikl_rphdpp) AS dpp, 
                                SUM(ikl_rphppn) AS ppn 
                            FROM tbtr_idmkoli 
                            WHERE ikl_registerrealisasi IS NOT NULL 
                            AND coalesce(ikl_flag, 'X') <> '5' ";
        
                if (!empty($kodetoko) && !empty($kodetoko2)) {
                    $sql .= "AND ikl_kodeidm BETWEEN '$kodetoko' AND '$kodetoko2' ";
                }
        
                $sql .= "GROUP BY ikl_kodeidm, ikl_tglbpd, ikl_nopb, ikl_registerrealisasi
                        ) HDR, TBMASTER_TOKOIGR 
                        WHERE ikl_kodeidm = tko_kodeomi 
                        AND coalesce(TKO_KODESBU, 'X') = 'O'";
        
                $results = $this->DB_PGSQL->select($sql);

                $perusahaanQuery = "
                        SELECT prs_kodeigr AS kode_igr, PRS_NAMACABANG
                        FROM tbmaster_perusahaan
                    ";
                $perusahaanResults = $this->DB_PGSQL->select($perusahaanQuery);
                // Create a dataset with both result sets
                $dataSet =(object)[
                    'DATA' => $results,
                    'PERUSAHAAN' => $perusahaanResults[0]
                ];

                if (count($results) > 0) {
                    return  $dataSet;
                }else {
                    return (object)['errors'=>true];
                }

        }
        // public function data_outstanding_retur($kodetoko = null, $kodetoko2 = null){
        //     $list_data = [];
        //     $query = "
        //             SELECT w.shop,w.shop || ' (' || t.tko_namaomi || ' - ' || t.tko_kodecustomer || ')' AS KDTOKO,
        //                 w.DOCNO AS NODOC,
        //                 CASE 
        //                     WHEN SUBSTR(w.keterangan, 1, 3) = '011' THEN 'P'
        //                     WHEN SUBSTR(w.keterangan, 1, 3) <> '011' THEN 'F'
        //                 END AS tipe,
        //                 w.PRDCD AS PLUIDM,
        //                 m.prc_pluigr AS PLUIGR,
        //                 coalesce(p.prd_deskripsipanjang, '') AS NMBRG,
        //                 w.qty AS QTY,
        //                 w.price_idm AS HARGA,
        //                 w.ppnrp_idm AS PPN,
        //                 (w.qty * w.price_idm) + w.ppnrp_idm AS TOTAL
        //             FROM tbtr_wt_interface w
        //                 JOIN tbmaster_prodcrm m ON m.prc_pluidm = w.prdcd
        //                 JOIN tbmaster_prodmast p ON m.prc_pluigr = p.prd_prdcd
        //                 JOIN tbmaster_tokoigr t ON w.shop = t.tko_kodeomi
        //             WHERE w.recid <> 'P'
        //         ";

        //         $results = $this->DB_PGSQL->select($query);

        //         // foreach ($results as $key => $value) {
        //         //     $list_data[$value->kdtoko][] = $value;
        //         // }

        //         if (count($results) > 0) {
        //             $perusahaanQuery = "
        //                 SELECT prs_kodeigr AS kode_igr, PRS_NAMACABANG
        //                 FROM tbmaster_perusahaan
        //             ";

        //             $perusahaanResults = $this->DB_PGSQL->select($perusahaanQuery);

        //             // Create a dataset with both result sets
        //             $dataSet =(object)[
        //                 'DATA' => $results,
        //                 'PERUSAHAAN' => $perusahaanResults[0]
        //             ];
        //         }else{

        //             return (object)['errors'=>true];
        //         }
                
        //         return $dataSet;

        // }

        public function data_struk_hadiah_omi($kodetoko = null, $nopb = null, $tglpb = null,$request = null){
            
            
            $sql = "select distinct count (1) from tbmaster_hadiahdcp where hdc_kodetoko = '$kodetoko' and hdc_nodokumen = '$nopb'";
            $jmlh_hadiah = $this->DB_PGSQL->select($sql);
            $IPMODUL = $request->getClientIp();
            $KodeIGR = session()->get('KODECABANG');
            $TokoOmi = $kodetoko;
            $NoOrder = $nopb;
            $NamaCab = "";
            $AlamatCab1 = "";
            $AlamatCab2 = "";
            $NamaPersh = "";
            $AlamatPersh1 = "";
            $AlamatPersh2 = "";
            $AlamatPersh3 = "";
            $NPWP = "";
            $NamaOMI = "";
            $dtHDH = [];  
            $msg = "";
            $tglTran = null;
            $STT = "";
            $StationMODUL = session()->get('KODECABANG');
        

            //     $headerData = $this->DB_PGSQL->select("
            //         SELECT PRS_NamaCabang, PRS_Alamat1, CONCAT(PRS_NamaWilayah, ' - TELP ', PRS_Telepon) AS AlamatCab2,
            //                 PRS_Namaperusahaan, PRS_AlamatFakturPajak1, PRS_AlamatFakturPajak2, PRS_AlamatFakturPajak3, 
            //                 CONCAT('NPWP: ', PRS_NPWP) AS NPWP
            //         FROM tbMaster_perusahaan
            //     ");

            //     if (!empty($headerData)) {
            //         $header = $headerData[0];
            //         $NamaCab = $header->prs_namacabang;
            //         $AlamatCab1 = $header->prs_alamat1;
            //         $AlamatCab2 = $header->alamatcab2;
            //         $NamaPersh = $header->prs_namaperusahaan;
            //         $AlamatPersh1 = $header->prs_alamatfakturpajak1;
            //         $AlamatPersh2 = $header->prs_alamatfakturpajak2;
            //         $AlamatPersh3 = $header->prs_alamatfakturpajak3;
            //         $NPWP = $header->npwp;
            //     }
            // // Get OMI name
            //     $omiData = $this->DB_PGSQL->select("
            //         SELECT TKO_NamaOMI
            //         FROM tbMaster_TokoIGR
            //         WHERE TKO_KodeIGR = '$KodeIGR'
            //         AND TKO_KodeOMI = '$TokoOmi'
            //     ");

            //     if (!empty($omiData)) {
            //         $NamaOMI = $omiData[0]->tko_namaomi;
            //     }
            //     // Get hadiah details
            //     $hadiahDetails =  $this->DB_PGSQL->select("
            //         SELECT CONCAT(PRD_DeskripsiPendek, ' ', PRD_Unit, '/', PRD_Frac) AS PLU,fdkplu as prdcd,
            //             FLOOR(FDJQTY / PRD_FRAC) AS QTY,
            //             MOD(FDJQTY, PRD_Frac) AS FRAC
            //         FROM tbMaster_HadiahDCP, TEMP_CFL, TbMaster_Prodmast
            //         -- WHERE HDC_KodeIGR = '$KodeIGR'
            //         -- AND HDC_KodeToko = '$TokoOmi'
            //         -- AND HDC_NoDokumen = '$NoOrder'
            //         WHERE COALESCE(HDC_RecordID, 'X') = '2'
            //         AND PRD_KodeIGR = HDC_KodeIGR
            //         AND PRD_PRDCD = CONCAT(SUBSTR(HDC_PRDCD, 1, 6), '0')
            //         AND SUBSTR(FDKPLU, 1, 6) = SUBSTR(HDC_PRDCD, 1, 6)
            //         ORDER BY PRD_PRDCD
            //     ");

            //     if (!empty($hadiahDetails)) {
            //         foreach ($hadiahDetails as $key => $value) {
            //             $dtHDH[] = $value;
            //         }
            //     }
                
            //     return (object)[
            //         'list_struk'=>$dtHDH,
            //         'tglTran'=>date('Y-m-d'),
            //         'NamaOMI'=>$NamaOMI,
            //         'NamaCab'=>$NamaCab,
            //         'AlamatCab1'=>$AlamatCab1,
            //         'AlamatCab2'=>$AlamatCab2,
            //         'NamaPersh'=>$NamaPersh,
            //         'AlamatPersh1'=>$AlamatPersh1,
            //         'AlamatPersh2'=>$AlamatPersh2,
            //         'AlamatPersh3'=>$AlamatPersh3,
            //         'NPWP'=>$NPWP 
            //     ];

            if ($jmlh_hadiah[0]->count) {

                try {
                    $this->DB_PGSQL->beginTransaction();
        
                    
                
            
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
                                '$IPMODUL' as IPMODUL
                        From TbTr_RealPB, tbMaster_Prodmast
                        Where RPB_KodeIGR = '$KodeIGR'
                        And TRIM(RPB_NoDokumen) = TRIM('$NoOrder')
                        And RPB_KodeOMI = '$TokoOmi'
                        And RPB_Plu2 = PRD_PRDCD
                        Group By SUBSTR(RPB_PLU2, 1, 6) || '0')
                    ";
                    $this->DB_PGSQL->statement($sql);
            
                    // Select distinct TO_DATE(rpb_create_dt)
                    $sql = "
                        SELECT rpb_create_dt::date
                        FROM tbtr_realpb
                        WHERE RPB_KodeIGR = '$KodeIGR'
                        AND TRIM(RPB_NoDokumen) = TRIM('$NoOrder')
                        AND RPB_KodeOMI = '$TokoOmi'
                    ";
                    $results = $this->DB_PGSQL->select($sql);
            
                    foreach ($results as $row) {
                        $tglTran = $row->rpb_create_dt;
                    }
            
                    // Select distinct SUBSTR(RPB_IDSuratJalan, 14, 2)
                    $sql = "
                        SELECT DISTINCT SUBSTR(RPB_IDSuratJalan, 14, 2)
                        FROM tbtr_realpb
                        WHERE RPB_KodeIGR = '$KodeIGR'
                        AND TRIM(RPB_NoDokumen) = TRIM('$NoOrder')
                        AND RPB_KodeOMI = '$TokoOmi'
                    ";
                    $results = $this->DB_PGSQL->select($sql);
            
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
                                ('2', '".$row->ftksup."',  '".$row->ftkplu."',  '".$row->ftketr."',  ".$q * $row->ftjvch.")
                            ";
                            $this->DB_PGSQL->statement($sql);
                        }
                    }

                    // Process GIFT_DSC
                    $results = $this->DB_PGSQL->select("SELECT * FROM TEMP_FGF");

                    foreach ($results as $row) {
                        if (!is_null($row->PLUGF)) {
                            $sql = "
                                SELECT COALESCE(COUNT(1), 0) 
                                FROM tbMaster_BrgPromosi 
                                WHERE BPRP_KodeIGR = '$KodeIGR'
                                AND BPRP_PRDCD = '".$row->PLUGF."'
                            ";
                            $check = $this->DB_PGSQL->select($sql);
                            $jum = $check[0]->coalesce;

                            if ($jum > 0) {
                                $sql = "
                                    SELECT BPRP_KetPanjang 
                                    FROM tbMaster_BrgPromosi 
                                    WHERE BPRP_KodeIGR = '$KodeIGR'
                                    AND BPRP_PRDCD = '".$row->PLUGF."'
                                ";
                                $descResult = $this->DB_PGSQL->select($sql);
                                $ftdesc = $descResult[0]->BPRP_KetPanjang;

                                $sql = "
                                    UPDATE TEMP_FGF 
                                    SET nmagf = '$ftdesc'
                                    WHERE plugf = '".$row->PLUGF."'
                                ";
                                $this->DB_PGSQL->statement($sql);
                            }
                        }
                    }

                    // Update tbMaster_HadiahDCP
                    $sql = "
                        UPDATE tbMaster_HadiahDCP 
                        SET HDC_RecordID = '2' 
                        WHERE HDC_KodeIGR = '$KodeIGR'
                        AND HDC_KodeToko = '$TokoOmi'
                        AND HDC_NoDokumen = '$NoOrder'
                        AND HDC_RecordID IS NULL
                    ";
                    $this->DB_PGSQL->statement($sql);

                    /**
                     * Cetak Hdiah
                     */

                    $headerData = $this->DB_PGSQL->select("
                        SELECT PRS_NamaCabang, PRS_Alamat1, CONCAT(PRS_NamaWilayah, ' - TELP ', PRS_Telepon) AS AlamatCab2,
                                PRS_Namaperusahaan, PRS_AlamatFakturPajak1, PRS_AlamatFakturPajak2, PRS_AlamatFakturPajak3, 
                                CONCAT('NPWP: ', PRS_NPWP) AS NPWP
                        FROM tbMaster_perusahaan
                    ");
                
                    if (!empty($headerData)) {
                        $header = $headerData[0];
                        $NamaCab = $header->prs_namacabang;
                        $AlamatCab1 = $header->prs_alamat1;
                        $AlamatCab2 = $header->alamatcab2;
                        $NamaPersh = $header->prs_namaperusahaan;
                        $AlamatPersh1 = $header->prs_alamatfakturpajak1;
                        $AlamatPersh2 = $header->prs_alamatfakturpajak2;
                        $AlamatPersh3 = $header->prs_alamatfakturpajak3;
                        $NPWP = $header->npwp;
                    }
                    // Get OMI name
                        $omiData = $this->DB_PGSQL->select("
                            SELECT TKO_NamaOMI
                            FROM tbMaster_TokoIGR
                            WHERE TKO_KodeIGR = '$KodeIGR'
                            AND TKO_KodeOMI = '$TokoOmi'
                        ");

                        if (!empty($omiData)) {
                            $NamaOMI = $omiData[0]->tko_namaomi;
                        }
                        // Get hadiah details
                        $hadiahDetails =  $this->DB_PGSQL->select("
                            SELECT CONCAT(PRD_DeskripsiPendek, ' ', PRD_Unit, '/', PRD_Frac) AS PLU,
                                FLOOR(FDJQTY / PRD_FRAC) AS QTY,
                                MOD(FDJQTY, PRD_Frac) AS FRAC
                            FROM tbMaster_HadiahDCP, TEMP_CFL, TbMaster_Prodmast
                            WHERE HDC_KodeIGR = '$KodeIGR'
                            AND HDC_KodeToko = '$TokoOmi'
                            AND HDC_NoDokumen = '$NoOrder'
                            AND COALESCE(HDC_RecordID, 'X') = '2'
                            AND PRD_KodeIGR = HDC_KodeIGR
                            AND PRD_PRDCD = CONCAT(SUBSTR(HDC_PRDCD, 1, 6), '0')
                            AND SUBSTR(FDKPLU, 1, 6) = SUBSTR(HDC_PRDCD, 1, 6)
                            ORDER BY PRD_PRDCD
                        ");

                        if (!empty($hadiahDetails)) {
                            foreach ($hadiahDetails as $key => $value) {
                                $dtHDH[] = $value;
                            }
                        }



                    /**
                     * End Cetak Hdiah
                     */


                    // Check and delete old records
                    $sql = "
                        SELECT COALESCE(COUNT(1), 0) 
                        FROM tbMaster_HadiahDCP 
                        WHERE HDC_KodeIGR = '$KodeIGR'
                        AND HDC_KodeToko = '$TokoOmi'
                        AND HDC_RecordID = '2' 
                        AND DATE_TRUNC('DAY', CURRENT_DATE) - DATE_TRUNC('DAY', HDC_TglDokumen) > INTERVAL '45 DAYS'
                    ";
                    $results = $this->DB_PGSQL->select($sql);
                    $jum = $results[0]->coalesce;

                    if ($jum > 0) {
                        $sql = "
                            DELETE FROM tbMaster_HadiahDCP 
                            WHERE HDC_KodeIGR = '$KodeIGR' 
                            AND HDC_KodeToko = '$TokoOmi'
                            AND HDC_RecordID = '2' 
                            AND DATE_TRUNC('DAY', CURRENT_DATE) - DATE_TRUNC('DAY', HDC_TglDokumen) > INTERVAL '45 DAYS'
                        ";
                        $this->DB_PGSQL->statement($sql);
                    }
                    
                    $this->DB_PGSQL->commit();
                    $headerData = $this->DB_PGSQL->select("
                        SELECT PRS_NamaCabang, PRS_Alamat1, CONCAT(PRS_NamaWilayah, ' - TELP ', PRS_Telepon) AS AlamatCab2,
                                PRS_Namaperusahaan, PRS_AlamatFakturPajak1, PRS_AlamatFakturPajak2, PRS_AlamatFakturPajak3, 
                                CONCAT('NPWP: ', PRS_NPWP) AS NPWP
                        FROM tbMaster_perusahaan
                    ");
                
                    if (!empty($headerData)) {
                        $header = $headerData[0];
                        $NamaCab = $header->prs_namacabang;
                        $AlamatCab1 = $header->prs_alamat1;
                        $AlamatCab2 = $header->alamatcab2;
                        $NamaPersh = $header->prs_namaperusahaan;
                        $AlamatPersh1 = $header->prs_alamatfakturpajak1;
                        $AlamatPersh2 = $header->prs_alamatfakturpajak2;
                        $AlamatPersh3 = $header->prs_alamatfakturpajak3;
                        $NPWP = $header->npwp;
                    }
                    // Get OMI name
                        $omiData = $this->DB_PGSQL->select("
                            SELECT TKO_NamaOMI
                            FROM tbMaster_TokoIGR
                            WHERE TKO_KodeIGR = '$KodeIGR'
                            AND TKO_KodeOMI = '$TokoOmi'
                        ");

                        if (!empty($omiData)) {
                            $NamaOMI = $omiData[0]->tko_namaomi;
                        }
                        // Get hadiah details
                        $hadiahDetails =  $this->DB_PGSQL->select("
                            SELECT CONCAT(PRD_DeskripsiPendek, ' ', PRD_Unit, '/', PRD_Frac) AS PLU,fdkplu as prdcd,
                                FLOOR(FDJQTY / PRD_FRAC) AS QTY,
                                MOD(FDJQTY, PRD_Frac) AS FRAC
                            FROM tbMaster_HadiahDCP, TEMP_CFL, TbMaster_Prodmast
                            WHERE HDC_KodeIGR = '$KodeIGR'
                            AND HDC_KodeToko = '$TokoOmi'
                            AND HDC_NoDokumen = '$NoOrder'
                            AND COALESCE(HDC_RecordID, 'X') = '2'
                            AND PRD_KodeIGR = HDC_KodeIGR
                            AND PRD_PRDCD = CONCAT(SUBSTR(HDC_PRDCD, 1, 6), '0')
                            AND SUBSTR(FDKPLU, 1, 6) = SUBSTR(HDC_PRDCD, 1, 6)
                            ORDER BY PRD_PRDCD
                        ");

                        if (!empty($hadiahDetails)) {
                            foreach ($hadiahDetails as $key => $value) {
                                $dtHDH[] = $value;
                            }
                        }
                    return [
                        'list_struk'=>$dtHDH,
                        'tglTran'=>$tglTran,
                        'NamaOMI'=>$NamaOMI,
                        'NamaCab'=>$NamaCab,
                        'AlamatCab1'=>$AlamatCab1,
                        'AlamatCab2'=>$AlamatCab2,
                        'NamaPersh'=>$NamaPersh,
                        'AlamatPersh1'=>$AlamatPersh1,
                        'AlamatPersh2'=>$AlamatPersh2,
                        'AlamatPersh3'=>$AlamatPersh3,
                        'NPWP'=>$NPWP 
                    ];
                } catch (\Throwable $th) {
                    
                    $this->DB_PGSQL->rollBack();
                    // dd($th);
                    return (object)['errors'=>true,'messages'=>$th->getMessage()];
                }
        
            } else {
                
                return (object)['errors'=>true,'messages'=>'Data Tidak Tersedia'];
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
                    return (object)['errors'=>true,'message' => 'Data (ikl_registerrealisasi) Tidak ada data!'];
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
                    return (object)['errors'=>true,'message' => 'Data (pbo_create_dt) Tidak ada data!'];
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
                    return (object)['errors'=>true,'message' => 'Data (koli) Tidak ada data!'];
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


    /**
     * END OMI
     */

    public function print_report(Request $request, $data){
        $data = json_decode(base64_decode($data));
        $jenis_page = null;
        $folder_page = null;
        $filename = $data->filename;
        $title_report = 'default-'.date('Y-m-d');
        $header_cetak_custom = false;
        $postiion_page_number_x = 63;
        $postiion_page_number_y = 615;
        
        switch ($data->filename) {

            /**
             * IDM 1
             */
                case 'outstanding_dspb':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = $this->data_outstanding_dspb($data->kodetoko1,$data->kodetoko2);
                    $header_cetak_custom = 'upper';
                    // dd($data);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'cetak_hitory_dspb':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_hitory_dspb($data->kodetoko,$data->nodspb);
                    
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'struk_hadiah':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_struk_hadiah($data->kodetoko,$data->nopb,$data->tglpb,$request);
                    
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'pemutihan_batch':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    // $data->data = ($this->data_pemutihan_batch($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'cetak_ba_ulang':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_ba_ulang($data->no_bpbr,$data->tgl_ret);
                    
                    $header_cetak_custom = 'upper';
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'cetak_bpbr_ulang':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_bpbr_ulang($data->no_bpbr,$data->tgl_ret);
                    $header_cetak_custom = 'upper';
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'beban_retur_igr':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_beban_retur_igr($data->no_bpbr,$data->tgl_ret);
                    $header_cetak_custom = 'upper';
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'analisa_crm':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_analisa_crm($data->tgl1,$data->tgl2);
                    $header_cetak_custom = 'upper';
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'absensi_wt':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_absensi_wt($data->tgl1,$data->tgl2);
                    $header_cetak_custom = 'upper';
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'listing_ba':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_listing_ba($data->tgl1,$data->tgl2);
                    $header_cetak_custom = 'upper';
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'retur_idm':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_retur_idm($data->tgl1,$data->tgl2);
                    $header_cetak_custom = 'upper';
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'outstanding_retur':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_outstanding_retur();
                    $header_cetak_custom = 'upper';
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'cetak_ba_bronjong':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    // $header_cetak_custom = 'upper';
                    $data->data = (object)$this->data_cetak_ba_bronjong($data->noba,$data->tglba);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
            /**
             * END IDM 1
             */

            /**
             * IDM 2
             */

                case 'rtbr':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_rtbr($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'tolakan_retur':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_tolakan_retur($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'cetak_ba_acost':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_ba_acost($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'history_dspb_roti':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_history_dspb_roti($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'rekap_dspb_roti':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_rekap_dspb_roti($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'history_dspb_voucher':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_history_dspb_voucher($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'history_rubah_status':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_history_rubah_status($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'history_paket_ipp':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_history_paket_ipp($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'rekap_pindah_lokasi':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_rekap_pindah_lokasi($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'npb_web_service':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_npb_web_service($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'perubahan_status_retur':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_perubahan_status_retur($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'retur_supplier':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_retur_supplier($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'serah_terima_retur':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_serah_terima_retur($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'cetak_history_qrcode':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_history_qrcode($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;

            /**
             * END IDM 2
             */

            /**
             * OMI
             */

                case 'cetak-ulang-dsp':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data =$this->data_cetak_ulang_dsp($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'cetak-ulang-sj':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_ulang_sj($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    break;
                case 'struk-hadiah':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_struk_hadiah_omi($data->kodetoko,$data->nopb,$data->tglpb,$request);
                    
                    if (isset($data->data->errors)) {
                        $data = null;
                    }

                    break;
                case 'outstanding-dsp':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_outstanding_dsp($data->kodetoko,$data->kodetoko2);
                    $header_cetak_custom = 'upper';  
                    
                    if (isset($data->data->errors)) {
                        $data = null;
                    }

                    break;
            /**
             * END OMI
             */

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
              
            $pdf = PDF::loadview('menu.rpt.'.$folder_page.'.'.$filename, compact('data','tanggal','perusahaan','header_cetak_custom'));
            $pdf->output();
            $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
            $canvas = $dompdf->get_canvas();
    
            // //make page text in header and right side
    
            $canvas->page_text($postiion_page_number_y,$postiion_page_number_x , "Page {PAGE_NUM} of {PAGE_COUNT}", null, 7, array(0, 0, 0));

            return $pdf->stream($title_report );
            
        } elseif ($jenis_page == 'struk-page') {
           
            $dompdf = new PDF();
            $pdf = PDF::loadview('menu.rpt.'.$folder_page.'.'.$filename,compact(['perusahaan','data']));
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

    public function get_toko_omi(Request $request,){
        /**
         * Perlu di cek kembali
         * file Lov_OMI 
         * tidak ditemukan query select toko
         */
        if ($request->type == 'idm') {
           return $this->HelpTokoIDM();
        } else {
            return $this->HelpTokoOMI();
        }
        
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
}