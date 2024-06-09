<?php

use App\Http\Controllers\CashierController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReturTokoTutupIdmController;
use App\Http\Controllers\StrukController;
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
Route::get('/', function () {
    // dd(Session::get('token'));
    return redirect()->route('login');
});
//LOGIN
Route::post('/login', [LoginController::class, 'login'])->name("login");
Route::get('/login', [LoginController::class, 'index']);
Route::get('/logout', [LoginController::class, 'logout']);

Route::middleware(['mylogin'])->group(function () {
    //HOME
    Route::group(['prefix' => 'home'], function(){
        Route::get('/', [HomeController::class, 'index']);
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

            Route::post('/send', [ProsesWTController::class, 'send_file']);

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
            Route::post('/struk/hadiah', [RPTController::class, 'print_struk_hadiah']);
            Route::post('/outstanding/dsp', [RPTController::class, 'print_outstanding_dsp']);

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

});
