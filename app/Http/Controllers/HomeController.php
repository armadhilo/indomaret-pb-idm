<?php

namespace App\Http\Controllers;

use App\Helper\DatabaseConnection;
use App\Http\Requests\DetailKasirRequest;
use App\Http\Requests\TableRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{

    public function __construct(Request $request){
        DatabaseConnection::setConnection(session('KODECABANG'), "PRODUCTION");
    }

    public function index(){

        $this->checkICM();
        $this->addMonitoringWT();
        $this->addReturTT();
        $this->addSortasi();
        $this->addHistoryContainer_Idm_Omi();
        $this->updateMenuSPI();

        $this->CreateMenuAll();
        $this->addColKardus();

        return view('home');
    }
}
