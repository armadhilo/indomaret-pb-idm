<?php

use App\Http\Controllers\ActionProsesController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MonitoringWebServiceController;
use App\Http\Controllers\ReturTokoTutupIdmController;
use App\Http\Controllers\DspbRotiController;
use App\Http\Controllers\FormBaRusakController;
use App\Http\Controllers\FormPickerClickController;
use App\Http\Controllers\HistoryProdukController;
use App\Http\Controllers\KlikIgrController;
use App\Http\Controllers\KlikIgrFooterController;


use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ProsesWTController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\RPTController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//LOGIN
Route::post('/login', [LoginController::class, 'login']);
Route::get('/login', [LoginController::class, 'index']);
Route::get('/logout', [LoginController::class, 'logout']);

// Route::middleware(['mylogin'])->group(function () {
    //HOME
    Route::group(['prefix' => 'home'], function(){
        Route::get('/', [HomeController::class, 'index']);
    });

    Route::group(['prefix' => 'monitoring-web-service'], function(){
        Route::get('/', [MonitoringWebServiceController::class, 'index']);
        Route::get('/datatables/{dtAwal}/{dtAkhir}', [MonitoringWebServiceController::class, 'datatables']);
    });

    Route::group(['prefix' => 'rtt-idm'], function(){
        Route::get('/', [ReturTokoTutupIdmController::class, 'index']);
        Route::get('/datatables', [ReturTokoTutupIdmController::class, 'datatables']);
        Route::get('/datatables-detail/{no_rtt}/{toko_tutup}/{toko_tujuan}', [ReturTokoTutupIdmController::class, 'datatablesDetail']);

        Route::group(['prefix' => 'action'], function(){
            Route::post('/upload-excel', [ReturTokoTutupIdmController::class, 'actionUpload']);
            Route::get('/cetak', [ReturTokoTutupIdmController::class, 'actionCetak']);
            Route::post('/cetak', [ReturTokoTutupIdmController::class, 'actionCetak']);
        });
    });

    Route::group(['prefix' => 'klik-igr'], function(){
        Route::get('/', [KlikIgrController::class, 'index']);
        Route::get('/datatables/{tanggal_trans}/{statusSiapPicking}/{statusSiapPacking}', [KlikIgrController::class, 'datatables']);
        Route::post('/password-manager', [KlikIgrController::class, 'passwordManager']);
        Route::post('/detail-transaksi', [KlikIgrController::class, 'detailTransaksi']);
        Route::post('/connect', [KlikIgrController::class, 'connectToWebservice']);

        Route::group(['prefix' => 'action'], function(){
            Route::post("/download-zip", [KlikIgrController::class, 'actionGlobalDownloadZip']);
            Route::post("/download-pdf", [KlikIgrController::class, 'actionGlobalDownloadPdf']);
            Route::post("/proses-main", [ActionProsesController::class, 'listPB']);
            Route::post('/action-approve', [KlikIgrController::class, 'action_approve']);
            Route::post('/SendHH-Tick', [KlikIgrController::class, 'SendHH_Tick']);

            //* KeysFunction Additional
            Route::get("/f1-download-excel", [KlikIgrFooterController::class, 'actionF1DownloadCSV']);
            Route::post("/f4-validasi-rak", [KlikIgrFooterController::class, 'actionF4ValidasiRak']);
            Route::post("/f4-item-batal", [KlikIgrFooterController::class, 'actionF4ItemBatal']);
            Route::post("/f4-cetak-item-batal", [KlikIgrFooterController::class, 'actionF4PrintItemBatal']);
            Route::post("/f10-hitung-ulang", [KlikIgrFooterController::class, 'actionF10HitungUlang']);
            Route::get('/delete-alasan-pembatalan-pb', [KlikIgrFooterController::class, 'getAlasanPembatalanPB']);
            Route::post("/HitungUlang", [KlikIgrController::class, 'actionHitungUlang']);

            //* OngkosKirim Additional
            Route::get("/getOngkosHitungBiaya", [KlikIgrController::class, 'getOngkosHitungBiaya']);
            Route::post("/OngkosKirimWithForm", [KlikIgrController::class, 'ongkosKirimWithForm']);

            //* MasterAlasanBatalKirim Additional
            Route::get("/actionMasterAlasanBatalKirimDatatables/{flagMode}", [KlikIgrController::class, 'actionMasterAlasanBatalKirimDatatables']);
            Route::post("/actionMasterAlasanBatalKirimAdd", [KlikIgrController::class, 'actionMasterAlasanBatalKirimAdd']);
            Route::post("/actionMasterAlasanBatalKirimRemove", [KlikIgrController::class, 'actionMasterAlasanBatalKirimRemove']);

            //* MasterPicking Additional
            Route::post("/actionMasterPickingHHPrep", [KlikIgrController::class, 'actionMasterPickingHHPrep']);
            Route::get("/actionMasterPickingHHLoadGroup", [FormPickerClickController::class, 'loadGroup']);
            Route::get("/actionMasterPickingHHFilterRak/{group?}", [FormPickerClickController::class, 'loadKodeRak']);
            Route::get("/actionMasterPickingHHLoadUser/{group?}", [FormPickerClickController::class, 'loadUserID']);
            Route::post("/actionMasterPickingHHLoadRakAll", [FormPickerClickController::class, 'actionSelectKodeRak']);
            Route::get("/actionMasterPickingHHLoadRakUser/{group?}/{user?}", [FormPickerClickController::class, 'actionSelectUserId']);
            //* ADD GROUP FUNCTION
            Route::get("/actionMasterPickingHHAddGroup", [FormPickerClickController::class, 'actionAddGroup']);
            Route::get("/actionMasterPickingFilterGroup", [FormPickerClickController::class, 'actionFilterGroup']);
            Route::get("/actionMasterPickingLoadUser", [FormPickerClickController::class, 'actionLoadUser']);
            Route::get("/actionMasterPickingLoadPicking/{group?}", [FormPickerClickController::class, 'actionGroupPicker']);

            Route::post("/actionMasterPickingGroupSimpan", [FormPickerClickController::class, 'actionGroupSimpan']);
            Route::post("/actionMasterPickingGroupHapus", [FormPickerClickController::class, 'actionGroupHapus']);
            //* MasterPickingAction
            Route::post("/actionMasterPickingHHSimpan", [FormPickerClickController::class, 'actionSimpan']);
            Route::post("/actionMasterPickingHHHapus", [FormPickerClickController::class, 'actionHapus']);

            //* ListingDelivery Additional
            Route::post("/actionListingDeliveryPrep", [KlikIgrController::class, 'actionListingDeliveryPrep']);
            Route::get("/actionListingDeliveryDatatables", [KlikIgrController::class, 'actionListingDeliveryDatatables']);

            //* ReCreateAWB Additional
            Route::post("/actionReCreateAWBProses", [KlikIgrController::class, 'actionReCreateAWBProses']);

            //* BAPengembalianDana Additional
            Route::get("/actionBAPengembalianDanaGetHistory", [KlikIgrController::class, 'actionBAPengembalianDanaGetHistory']);
            Route::get("/actionBAPengembalianDanaDatatables/{noba}/{isHistory}", [KlikIgrController::class, 'actionBAPengembalianDanaDatatables']);
            Route::get("/actionBuktiSerahTerimaKardusDatatables/{history}", [KlikIgrController::class, 'actionBuktiSerahTerimaKardusDatatables']);

            //* BARusakKemasan Additional
            Route::get("/actionBaRusakKemasanPrep", [FormBaRusakController::class, 'actionPrep']);
            Route::get("/actionBaRusakKemasanLoadItem", [FormBaRusakController::class, 'loadItem']);
            Route::get("/actionBaRusakKemasanLoadBA", [FormBaRusakController::class, 'LoadBA']);
            Route::post("/actionBaRusakKemasanHitungUlang", [FormBaRusakController::class, 'actionHitungUlang']);
            Route::post("/actionBaRusakKemasanSimpan", [FormBaRusakController::class, 'actionSimpan']);
            Route::post("/actionBaRusakKemasanBatal", [FormBaRusakController::class, 'actionBatal']);
            Route::post("/actionBaRusakKemasanApprove", [FormBaRusakController::class, 'btnApprove_Click']);

            $buttonKeys = ['SendHandHelt', 'OngkosKirim', 'DraftStruk', 'PembayaranVA', 'KonfirmasiPembayaran', 'Sales', 'CetakSuratJalan', 'CetakIIK', 'PbBatal', 'ItemPickingBelumTransit', 'LoppCod', 'ListPBLebihDariMaxSerahTerima', 'BAPengembalianDana', 'ListingDelivery', 'ReCreateAWB', 'BaRusakKemasan', 'cetakFormPengembalianBarang', 'LaporanPenyusutanHarian', 'LaporanPesananExpired', 'BuktiSerahTerimaKardus'];

            foreach ($buttonKeys as $key) {
                Route::post("/$key", [KlikIgrController::class, "action$key"]);
            }

            $functionKeys = ['f1', 'f2', 'f3', 'f4', 'f5', 'f6', 'f7', 'f8', 'f9', 'f10', 'f12', 'delete'];

            foreach ($functionKeys as $key) {
                Route::post("/$key", [KlikIgrFooterController::class, "action$key"]);
            }
        });
    });

    Route::group(['prefix' => 'dspb-roti'], function(){
        Route::get('/', [DspbRotiController::class, 'index']);
        Route::get('/get-cluster-mobil', [DspbRotiController::class, 'getClusterMobil']);
        Route::get('/datatables/{date}/{cluster}', [DspbRotiController::class, 'datatables']);

        Route::group(['prefix' => 'action'], function(){
            Route::post('/cetak-dspb', [DspbRotiController::class, 'actionCetakDspb']);
            Route::get('/get-zip/{temp}', [DspbRotiController::class, 'getZipFile']);
        });
    });

    Route::group(['prefix' => 'history-produk'], function(){
        Route::get('/', [HistoryProdukController::class, 'index']);
        Route::get('/datatables', [HistoryProdukController::class, 'datatables']);

        Route::get('/datatables-report', [HistoryProdukController::class, 'datatablesReportKPH']);

        Route::group(['prefix' => 'action'], function(){
            Route::post('/proses', [HistoryProdukController::class, 'actionProses']);
            Route::post('/hit-kph', [HistoryProdukController::class, 'actionHitKPH']);
            Route::post('/report-kph', [HistoryProdukController::class, 'actionReportKPH']);
        });
    });


    Route::get('/monitoring', [MonitoringController::class, 'index']);
    Route::get('/voucher', [VoucherController::class, 'index']);
    Route::get('/proses_wt', [ProsesWTController::class, 'index']);
    Route::get('/retur', [ReturController::class, 'index']);
    Route::get('/report', [RPTController::class, 'index']);
    Route::get('/test', [RPTController::class, 'print_cetak_ulang_dsp_test']);
    Route::get('/test2', [RPTController::class, 'print_cetak_ulang_dsp_test2']);
    
    Route::prefix('/api')->group(function () {
        
        Route::get('print/report/{data}', [RPTController::class, 'print_report']);
    
        /*  Monitoring */
        Route::prefix('/monitoring')->group(function () {

            Route::get('/data', [MonitoringController::class, 'monitoring_load']);
            Route::get('/data/list_paket_pengiriman_idm', [MonitoringController::class, 'list_paket_pengiriman_idm']);
            Route::get('/download/filerekon', [MonitoringController::class, 'csv_rekon']);
            Route::get('/download/list_kubikasi_pb_idm', [MonitoringController::class, 'cetakk_list_kubikasi_pb_idm']);
            Route::get('/download/list_paket_pengiriman_idm', [MonitoringController::class, 'cetak_list_paket_pengiriman_idm']);

        });
        /*  Proses WT */
        Route::prefix('/proseswt')->group(function () {

            Route::post('/send', [ProsesWTController::class, 'list_file']);

        });
        /*  Retur */
        Route::prefix('/retur')->group(function () {

            Route::get('/data/toko', [ReturController::class, 'get_data_toko']);

        });
        /*  Report */
        Route::prefix('/report')->group(function () {

            Route::get('/pb/omi', [RPTController::class, 'get_no_pb']);
            Route::post('/cetak/dsp/ulang', [RPTController::class, 'print_cetak_ulang_dsp']);
            Route::post('/cetak/sj/ulang', [RPTController::class, 'print_cetak_ulang_sj']);
            Route::post('/struk/hadiah_omi', [RPTController::class, 'print_struk_hadiah_omi']);
            Route::post('/outstanding/dsp', [RPTController::class, 'print_outstanding_dsp']);
            
            Route::post('/cetak/rtbr', [RPTController::class, 'print_rtbr']);
            Route::post('/cetak/tolakan_retur', [RPTController::class, 'print_tolakan_retur']);
            Route::post('/cetak/cetak_ba_acost', [RPTController::class, 'print_cetak_ba_acost']);
            Route::post('/cetak/history_dspb_roti', [RPTController::class, 'print_history_dspb_roti']);
            Route::post('/cetak/rekap_dspb_roti', [RPTController::class, 'print_rekap_dspb_roti']);
            Route::post('/cetak/history_dspb_voucher', [RPTController::class, 'print_history_dspb_voucher']);
            Route::post('/cetak/history_rubah_status', [RPTController::class, 'print_history_rubah_status']);
            Route::post('/cetak/history_paket_ipp', [RPTController::class, 'print_history_paket_ipp']);
            Route::post('/cetak/rekap_pindah_lokasi', [RPTController::class, 'print_rekap_pindah_lokasi']);
            Route::post('/cetak/npb_web_service', [RPTController::class, 'print_npb_web_service']);
            Route::post('/cetak/perubahan_status_retur', [RPTController::class, 'print_perubahan_status_retur']);
            Route::post('/cetak/retur_supplier', [RPTController::class, 'print_retur_supplier']);
            Route::post('/cetak/serah_terima_retur', [RPTController::class, 'print_serah_terima_retur']);
            Route::post('/cetak/cetak_history_qrcode', [RPTController::class, 'print_cetak_history_qrcode']);
            
            Route::post('/cetak/outstanding_dspb', [RPTController::class, 'print_outstanding_dspb']);
            Route::post('/cetak/cetak_hitory_dspb', [RPTController::class, 'print_cetak_hitory_dspb']);
            Route::post('/cetak/struk_hadiah', [RPTController::class, 'print_struk_hadiah']);
            Route::post('/cetak/pemutihan_batch', [RPTController::class, 'print_pemutihan_batch']);
            Route::post('/cetak/cetak_ba_ulang', [RPTController::class, 'print_cetak_ba_ulang']);
            Route::post('/cetak/cetak_bpbr_ulang', [RPTController::class, 'print_cetak_bpbr_ulang']);
            Route::post('/cetak/beban_retur_igr', [RPTController::class, 'print_beban_retur_igr']);
            Route::post('/cetak/analisa_crm', [RPTController::class, 'print_analisa_crm']);
            Route::post('/cetak/absensi_wt', [RPTController::class, 'print_absensi_wt']);
            Route::post('/cetak/listing_ba', [RPTController::class, 'print_listing_ba']);
            Route::post('/cetak/retur_idm', [RPTController::class, 'print_retur_idm']);
            Route::post('/cetak/outstanding_retur', [RPTController::class, 'print_outstanding_retur']);
            Route::post('/cetak/cetak_ba_bronjong', [RPTController::class, 'print_cetak_ba_bronjong']);

            Route::post('/', [RPTController::class, 'print_']);
            Route::get('/data/toko', [RPTController::class, 'get_toko_omi']);

        });
        /*  Voucher */
        Route::prefix('/voucher')->group(function () {

            Route::post('/picking/save', [VoucherController::class, 'save_data_picker']);
            Route::get('/data', [VoucherController::class, 'voucher_load']);
            Route::get('/picking', [VoucherController::class, 'picking_load']);
            Route::get('/printqr', [VoucherController::class, 'print_qr']);
            Route::get('/printreport', [VoucherController::class, 'print_report']);
        });
    });
// });
