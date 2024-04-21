<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MonitoringWebServiceController;
use App\Http\Controllers\ReturTokoTutupIdmController;
use App\Http\Controllers\DspbRotiController;
use App\Http\Controllers\HistoryProdukController;
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
