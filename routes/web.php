<?php

use App\Http\Controllers\ActionProsesController;
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

            //* KeysFunction Additional
            Route::get("/f1-download-excel", [KlikIgrFooterController::class, 'actionF1DownloadCSV']);
            Route::post("/f4-validasi-rak", [KlikIgrFooterController::class, 'actionF4ValidasiRak']);
            Route::post("/f4-item-batal", [KlikIgrFooterController::class, 'actionF4ItemBatal']);
            Route::post("/f4-cetak-item-batal", [KlikIgrFooterController::class, 'actionF4PrintItemBatal']);
            Route::post("/f10-hitung-ulang", [KlikIgrFooterController::class, 'actionF10HitungUlang']);
            Route::get('/delete-alasan-pembatalan-pb', [KlikIgrFooterController::class, 'getAlasanPembatalanPB']);
            Route::post("/HitungUlang", [KlikIgrController::class, 'actionHitungUlang']);

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

            $buttonKeys = ['SendHandHelt', 'OngkosKirim', 'DraftStruk', 'PembayaranVA', 'KonfirmasiPembayaran', 'Sales', 'CetakSuratJalan', 'CetakIIK', 'PbBatal', 'ItemPickingBelumTransit', 'LoppCod', 'ListPBLebihDariMaxSerahTerima', 'BAPengembalianDana', 'ListingDelivery', 'ReCreateAWB', 'BaRusakKemasan', 'cetakFormPengembalianBarang', 'LaporanPenyusutanHarian', 'LaporanPesananExpired', 'BuktiSerahTerimaKardus'];

            foreach ($buttonKeys as $key) {
                Route::post("/$key", [KlikIgrController::class, "action$key"]);
            }

            $functionKeys = ['f1', 'f2', 'f3', 'f4', 'f5', 'f6', 'f7', 'f8', 'f9', 'f10', 'f12', 'delete'];

            foreach ($functionKeys as $key) {
                Route::post("/$key", [KlikIgrFooterController::class, "action$key"]);
            }

            //! KEVIN ROUTE
            Route::post("/action-cetak-surat-jalan", [KlikIgrController::class, 'actionCetakSuratJalan']);
            Route::post("/action-cetak-ikk", [KlikIgrController::class, 'actionCetakIKK']);
            Route::post("/action-list-item-pb-batal", [KlikIgrController::class, 'actionListItemPBBatal']);
            Route::post("/action-item-picking-belum-transit", [KlikIgrController::class, 'actionItemPickingBelumTransit']);
            Route::post("/action-listing-delivery", [KlikIgrController::class, 'actionListingDelivery']);
            Route::post("/action-list-pb-lebih-dari-max-serah-terima", [KlikIgrController::class, 'actionListPBLebihDariMaxSerahTerima']);
            Route::post("/action-recreate-awb", [KlikIgrController::class, 'actionReCreateAWB']);
            Route::post("/action-bap-pengembalian-dana", [KlikIgrController::class, 'actionBAPengembalianDana']);
            Route::post("/action-ba-rusak-kemasan", [KlikIgrController::class, 'actionBARusakKemasan']);

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
// });
