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
        
    /**
     * IDM1
     */
        public function print_outstanding_dspb(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'outstanding_dspb';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_OUTSTANDING_DSPB';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_cetak_hitory_dspb(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'cetak_hitory_dspb';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
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
            // $data_report['filename'] = 'struk_hadiah';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
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
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'pemutihan_batch';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_PEMUTIHAN_BATCH';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_cetak_ba_ulang(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'cetak_ba_ulang';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_CETAK_BA_ULANG';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_cetak_bpbr_ulang(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'cetak_bpbr_ulang';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_CETAK_BPBR_ULANG';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_beban_retur_igr(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'beban_retur_igr';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_BEBAN_RETUR_IGR';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_analisa_crm(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'analisa_crm';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_ANALISA_CRM';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_absensi_wt(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'absensi_wt';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_ABSENSI_WT';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_listing_ba(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'listing_ba';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_LISTING_BA';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_retur_idm(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'retur_idm';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_RETUR_IDM';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_outstanding_retur(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'outstanding_retur';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_OUTSTANDING_RETUR';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }
        public function print_cetak_ba_bronjong(Request $request){
            $kodetoko = $request->toko;
            $nopb = explode(" / ",$request->nopb)[0];
            $tglpb = explode(" / ",$request->nopb)[1];
            
            $data_report =[];
            $data_report['kodetoko'] = $kodetoko;
            $data_report['nopb'] = $nopb;
            $data_report['tglpb'] = $tglpb;
            // $data_report['filename'] = 'cetak_ba_bronjong';
            // $data_report['folder_page'] = 'idm1';
            $data_report['filename'] ='rtbr';
            $data_report['folder_page'] = 'idm2';
            $data_report['jenis_page'] = 'struk-page';
            $data_report['title_report'] = 'PRINT_CETAK_BA_BRONJONG';
            $encrypt_data = base64_encode(json_encode($data_report));   
            $link = url('/api/print/report/'.$encrypt_data);
            
            return response()->json(['errors'=>false,'messages'=>'Berhasil','url'=>$link],200);
        }

        public function data_outstanding_dspb($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_cetak_hitory_dspb($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_struk_hadiah($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_pemutihan_batch($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_cetak_ba_ulang($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_cetak_bpbr_ulang($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_beban_retur_igr($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_analisa_crm($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_absensi_wt($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_listing_ba($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_retur_idm($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_outstanding_retur($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
        }
        public function data_cetak_ba_bronjong($kodetoko,$nopb,$tglpb){
             return (object)['errors'=>true];
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
                    $data->data = (object)$this->data_outstanding_dspb($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'cetak_hitory_dspb':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_hitory_dspb($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'struk_hadiah':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_struk_hadiah($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'pemutihan_batch':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_pemutihan_batch($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'cetak_ba_ulang':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_ba_ulang($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'cetak_bpbr_ulang':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_bpbr_ulang($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'beban_retur_igr':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_beban_retur_igr($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'analisa_crm':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_analisa_crm($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'absensi_wt':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_absensi_wt($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'listing_ba':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_listing_ba($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'retur_idm':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_retur_idm($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'outstanding_retur':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_outstanding_retur($data->kodetoko,$data->nopb,$data->tglpb);
                    if (isset($data->data->errors)) {
                        $data = null;
                    }
                    
                    break;
                case 'cetak_ba_bronjong':
                    $jenis_page = $data->jenis_page;
                    $folder_page = $data->folder_page;
                    $title_report = $data->title_report;
                    $data->data = (object)$this->data_cetak_ba_bronjong($data->kodetoko,$data->nopb,$data->tglpb);
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