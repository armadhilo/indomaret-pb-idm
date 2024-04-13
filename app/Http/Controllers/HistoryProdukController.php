<?php

namespace App\Http\Controllers;

use App\Helper\DatabaseConnection;
use App\Http\Requests\DetailKasirRequest;
use App\Http\Requests\TableRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MonitoringWebServiceController extends Controller
{

    public function __construct(Request $request){
        DatabaseConnection::setConnection(session('KODECABANG'), "PRODUCTION");
    }

    public function index(){

        //* untuk mode ada 2 yaitu KPH MEAN dan PRODUK BARU
        //* untuk KPH MEAN button upload CSV name jadi 'Upload CSV' (pilihan default)
        //* untuk KPH MEAN button upload CSV name jadi 'KPH Produk Baru'

        //* diawal akan panggil function datatables

        return view('menu.monitoring-web-service');
    }

    public function datatables(){

        $query = '';
        $query .= "select DISTINCT TKO_KODEOMI KODETK, TKO_NAMAOMI NAMATK ";
        $query .= "from tbmaster_tokoigr ";
        $query .= "where tko_kodesbu = 'I'  ";
        $query .= "and TKO_FLAGKPH = 'Y' ";
        $query .= "and tko_kodeigr = '" . session('KODECABANG') . "'   ";
        $query .= "order by 1  ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);

        // dgvHP.Rows.Add(data.Rows(i).Item("KODETK").ToString, _
        //                       data.Rows(i).Item("NAMATK").ToString)
    }

    public function actionProses(){
        //! btnProses_Click
        //! initializeFile
    }

    public function actionUploadCsv(){
        //! btnMinor_Click

        //! form -> frmUploadMinor
    }

    public function actionHitKPH(){
        //! btnHitungKPH_Click
        //! initKPH
    }

    public function actionReportKPH(){
        //! btnReport_Click

        //! form -> frmPilPeriode
    }




}
