<?php

namespace App\Http\Controllers;

use App\Traits\LibraryPDF;
use Illuminate\Http\Request;
use DB;

class ReturController extends Controller
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
        $flag = [
            "flagFTZ" => session()->get('flagFTZ'),
            "flagIGR" => session()->get('flagIGR'),
            "flagSPI" => session()->get('flagSPI'),
            "flagHHSPI" => session()->get('flagHHSPI')
        ];
        return view("menu.retur.index",compact('flag');
    }
    public function get_data_toko(){
        $data = $this->DB_PGSQL
             ->table("tbmaster_tokoigr")
             ->selectRaw("tko_kodeomi,tko_kodecustomer")
             ->whereRaw("tko_kodeigr = '".session('KODECABANG')."'")
             ->whereRaw("tko_namasbu = 'INDOMARET'")
             ->get();
        return $data;
    }
}
