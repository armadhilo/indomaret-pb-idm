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
        return view("menu.retur.index");
    }
}
