<?php

namespace App\Http\Controllers;

use App\Traits\LibraryPDF;
use Illuminate\Http\Request;
use DB;
use PDF;

class RPTController extends Controller
{
    use LibraryPDF;
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

    public function print_cetak_ulang_dsp_test(Request $request){
        $perusahaan ='[{"prs_namaperusahaan":"PT.INTI CAKRAWALA CITRA","prs_namacabang":"INDOGROSIR SEMARANG POST","customer":"105005 - PUJI RAHAYU","nomor_faktur":"010.007-23.30540947","tgl_faktur":"2023-05-31 00:00:00","dpp":"6432836.0000","ppn":"707624.0000","ppn_bkp":"707624.0000","ppn_bebas":"0","ppn_dtp":"0","prs_alamat1":"Jln. Testing","prs_alamat2":"Jln. Testing","prs_alamat3":"Jln. Testing","prs_npwp":"NPWP. 13123123123","prs_telepon":"Telp. 13123123123","prs_kodemto":"13123123123"}]';
        $data = '[{"cp_ppn" : 0,"cp_plu" : "","cp_hsat" : 0,"cp_total" : 0,"rom_nodokumen" : "O230674","tgldokumen" : "15\/09\/2023","rom_noreferensi" : 3230001,"rom_tglreferensi" : "08\/02\/2023","rom_prdcd" : "0035870","prd_deskripsipanjang" : "POP MIE MI INSTAN BASO CUP 75g","prd_deskripsipendek" : "POP MIE BASO 75G","kemasan" : "CTN\/24","cus_kodemember" : null,"cus_namamember" : null,"rom_namadrive" : "Putra","rom_kodekasir" : "SOS","rom_station" : "99","rom_jenistransaksi" : "0006","rom_qty" : 2,"rom_qtyselisih" : 2,"rom_hrg" : 4108.0,"rom_ttl" : 8216.0,"trjd_discount" : 0.0000,"prd_prdcd" : "0035870","rom_flagbkp" : "Y","rom_flagbkp2" : "Y","kfp_statuspajak" : "KENA PPN","rom_persenppn" : 11,"total" : 7401.801801801801,"ppn" : 814.198198198199},{"cp_ppn" : 0,"cp_total" : 0,"cp_plu" : "","cp_hsat" : 0,"rom_nodokumen" : "O230675","tgldokumen" : "21\/09\/2023","rom_noreferensi" : 3230008,"rom_tglreferensi" : "13\/02\/2023","rom_prdcd" : "0030180","prd_deskripsipanjang" : null,"prd_deskripsipendek" : "SPRITE 1500 ML","kemasan" : "CTN\/12","cus_kodemember" : null,"cus_namamember" : null,"rom_namadrive" : "Helvin","rom_kodekasir" : "SOS","rom_station" : "99","rom_jenistransaksi" : "0007","rom_qty" : 4,"rom_qtyselisih" : 1,"rom_hrg" : 14234.0,"rom_ttl" : 56934.0,"trjd_discount" : 0.0000,"prd_prdcd" : "0030180","rom_flagbkp" : "Y","rom_flagbkp2" : "Y","kfp_statuspajak" : "KENA PPN","rom_persenppn" : 11,"total" : 51291.891891891886,"ppn" : 5642.108108108114}]';
        $perusahaan = (json_decode($perusahaan))[0];
        $data = json_decode($data);
        $nodoc = "O230675";
        $tgldoc = "21/09/2023";
        $dompdf = new PDF();
        $pdf = PDF::loadview('menu.rpt.struk-hadiah',compact(['perusahaan','data','nodoc','tgldoc']));
 
        error_reporting(E_ALL ^ E_DEPRECATED);

        $pdf->output();
        $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
        $canvas = $dompdf ->get_canvas();
        $dompdf = $pdf;

        return $dompdf->stream('DSP-TOKO-KOLI');
    }
    public function print_cetak_ulang_dsp_test2(Request $request){
        $perusahaan ='[{"prs_namaperusahaan":"PT.INTI CAKRAWALA CITRA","prs_namacabang":"INDOGROSIR SEMARANG POST","customer":"105005 - PUJI RAHAYU","nomor_faktur":"010.007-23.30540947","tgl_faktur":"2023-05-31 00:00:00","dpp":"6432836.0000","ppn":"707624.0000","ppn_bkp":"707624.0000","ppn_bebas":"0","ppn_dtp":"0","prs_alamat1":"Jln. Testing","prs_alamat2":"Jln. Testing","prs_alamat3":"Jln. Testing","prs_npwp":"NPWP. 13123123123","prs_telepon":"Telp. 13123123123","prs_kodemto":"13123123123"}]';
        $data = '[{"cp_ppn" : 0,"cp_plu" : "","cp_hsat" : 0,"cp_total" : 0,"rom_nodokumen" : "O230674","tgldokumen" : "15\/09\/2023","rom_noreferensi" : 3230001,"rom_tglreferensi" : "08\/02\/2023","rom_prdcd" : "0035870","prd_deskripsipanjang" : "POP MIE MI INSTAN BASO CUP 75g","prd_deskripsipendek" : "POP MIE BASO 75G","kemasan" : "CTN\/24","cus_kodemember" : null,"cus_namamember" : null,"rom_namadrive" : "Putra","rom_kodekasir" : "SOS","rom_station" : "99","rom_jenistransaksi" : "0006","rom_qty" : 2,"rom_qtyselisih" : 2,"rom_hrg" : 4108.0,"rom_ttl" : 8216.0,"trjd_discount" : 0.0000,"prd_prdcd" : "0035870","rom_flagbkp" : "Y","rom_flagbkp2" : "Y","kfp_statuspajak" : "KENA PPN","rom_persenppn" : 11,"total" : 7401.801801801801,"ppn" : 814.198198198199},{"cp_ppn" : 0,"cp_total" : 0,"cp_plu" : "","cp_hsat" : 0,"rom_nodokumen" : "O230675","tgldokumen" : "21\/09\/2023","rom_noreferensi" : 3230008,"rom_tglreferensi" : "13\/02\/2023","rom_prdcd" : "0030180","prd_deskripsipanjang" : null,"prd_deskripsipendek" : "SPRITE 1500 ML","kemasan" : "CTN\/12","cus_kodemember" : null,"cus_namamember" : null,"rom_namadrive" : "Helvin","rom_kodekasir" : "SOS","rom_station" : "99","rom_jenistransaksi" : "0007","rom_qty" : 4,"rom_qtyselisih" : 1,"rom_hrg" : 14234.0,"rom_ttl" : 56934.0,"trjd_discount" : 0.0000,"prd_prdcd" : "0030180","rom_flagbkp" : "Y","rom_flagbkp2" : "Y","kfp_statuspajak" : "KENA PPN","rom_persenppn" : 11,"total" : 51291.891891891886,"ppn" : 5642.108108108114}]';
        $perusahaan = (json_decode($perusahaan))[0];
        $data = json_decode($data);
        $nodoc = "O230675";
        $tgldoc = "21/09/2023";
        $dompdf = new PDF();
        $pdf = PDF::loadview('menu.rpt.cetak-ulang-dsp',compact(['perusahaan','data','nodoc','tgldoc']));
 
        error_reporting(E_ALL ^ E_DEPRECATED);

        $pdf->output();
        $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
        $canvas = $dompdf ->get_canvas();
        $dompdf = $pdf;

        return $dompdf->stream('DSP-TOKO-KOLI');
    }


    public function print_cetak_ulang_dsp(Request $request){
        
            $nopb_tgldsp = explode(' / ',$request->nopb);
            $KodeOMI = $request->toko; // Assuming these are passed in the request
            $nopb =  $nopb_tgldsp[0];
            $tglPb =  $nopb_tgldsp[1];
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
            $pdfee = 0;
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
        $nokoli = $this->DB_PGSQL
                        ->table("tbtr_realpb")
                        ->selectRaw("
                            rpb_nokoli as nokoli
                        ")
                        ->distinct()
                        ->whereRaw("rpb_kodeomi = '$KodeOMI'")
                        ->whereRaw("rpb_nodokumen = '$nopb'")
                        ->whereRaw("rpb_create_dt::date = '$tglPb'::date")
                        ->orderBy($this->DB_PGSQL->raw("1"))
                        ->get();
                        
        if (count($nokoli)) {
            foreach ($nokoli as $key => $row_koli) {

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
                    if ($item->ppnrateprd <= 0) {
                        $ppnRatePrd = $item->ppnrateprd;
                    }
                    /**
                     * Cek di virtual box metohod 
                     * checkPPN()
                     */

                    $cekPPn = $this->checkPPN($item->pkp . $item->pkp2);
                    $dtPPn = $cekPPn;
                    $status = $cekPPn[0]->STATUS ?? '';

                    if (count($dtPPn)) {
                        switch ($status) {
                            case "KENA PPN":
                                $DPPRPH += ($item->hg * $item->qt);
                                $item->PLU .= "    ";
                                $cntrBKP++;
                                break;
                            case "BEBAS PPN":
                                $DPPBBS += ($item->hg * $item->qt);
                                $item->PLU .= "****";
                                $cntrBBS++;
                                break;
                            case "PPN DTP":
                                $DPPDTP += ($item->hg * $item->qt);
                                $item->PLU .= "*** ";
                                $cntrDTP++;
                                break;
                            case "CUKAI":
                                $DPPCUK += ($item->hg * $item->qt);
                                $item->PLU .= "**  ";
                                $cntrCUK++;
                                break;
                            default:
                                $DPPTKP += ($item->hg * $item->qt);
                                $item->PLU .= "*   ";
                                $cntrTKP++;
                                break;
                        }
                    } else {
                        $DPPTKP += ($item->hg * $item->qt);
                        $item->PLU .= "    ";
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
                    $KodeOMI = $dtOMI->Kode;
                    $NamaOMI = $dtOMI->Nama;
                    $NoOrder = $dtOMI->NoOrder;
                    $KodeCustomer = $dtOMI->TKO_KodeCustomer;
                    $pdfee = (float) str_replace(",", ".", $dtOMI->DSTFEE);
                }

                $Checker = $this->DB_PGSQL
                                ->table("tbmaster_pbomi")
                                ->selectRaw("
                                    COALESCE(PBO_USERUPDATECHECKER, 'XXX') AS checker
                                ")
                                ->distinct()
                                ->whereRaw(" PBO_KODEOMI = '$KodeOMI'")
                                ->whereRaw(" PBO_NOPB = '$nopb'")
                                ->whereRaw(" PBO_NOKOLI = $row_koli->nokoli")
                                ->limit(1)
                                ->get();

                if (count($result) > 0) {
                    $NamaCheker = $result[0]->checker;
                }
                
                $nodspb = $this->DB_PGSQL
                               ->table("tbtr_idmkoli")
                               ->selectRaw("
                                    ikl_registerrealisasi
                               ")
                               ->distinct()
                               ->whereRaw("ikl_kodeidm = '$KodeOMI'")
                               ->whereRaw("ikl_nopb = '$nopb'")
                               ->whereRaw("TO_CHAR(ikl_tglbpd, 'YYYYMMDD') = '".date("Ymd",strtotime($tglpb))."'")
                               ->get();




                $KodeOMI = 'Your_KodeOMI';
                $nopb = 'Your_nopb';
                $dtKoli = [
                    ['nokoli' => 'your_nokoli1'],
                    // add more if necessary
                ];

                $NamaOMI = '';
                $NoOrder = '';
                $KodeCustomer = '';
                $pdfee = 0;
                $NamaCheker = '';

              
                
            }
        }
    }
}