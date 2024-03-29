<?php

use App\Http\Controllers\CashierController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StrukController;
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
        Route::get('/datatables', [HomeController::class, 'datatables']);

        Route::get('/datatables-laporan', [HomeController::class, 'datatablesLaporan']);

    });

    Route::group(['prefix' => 'cashier'], function(){

        Route::get('/{id}/{station}/{date}', [CashierController::class, 'index']);
        Route::get('/datatables', [CashierController::class, 'datatables']);
    });

    Route::group(['prefix' => 'struk'], function(){

        Route::get('/', [StrukController::class, 'index']);
        Route::get('/datatables', [StrukController::class, 'datatables']);
    });
// });


    //tampilan only
    Route::view('detail-penjualan', 'detail-penjualan');
    Route::view('list-struk', 'list-struk');
