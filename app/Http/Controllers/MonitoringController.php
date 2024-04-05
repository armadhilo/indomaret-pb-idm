<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MonitoringController extends Controller
{
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
        return view("menu.monitoring.index");
    }

    public function load_zona(){
        $data = $this->DB_PGSQL
                     ->table("zona_idm")
                     ->select("zon_kode")
                     ->distinct()
                     ->orderBy("zon_kode","asc")
                     ->get();
        return $data;
    }
}
