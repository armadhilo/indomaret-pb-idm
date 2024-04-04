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
        return view('menu.monitoring-web-service');
    }

    public function datatables($dtAwal, $dtAkhir){
        $query = '';
        $query .= "WITH log_btb_wt as ( ";
        $query .= "  SELECT btb_file filenpb, ";
        $query .= "         to_char(btb_stat_get,'DD/MM/YYYY') tgl_bpb, ";
        $query .= "         to_char(btb_stat_get,'HH24:MI:SS') jam_bpb, ";
        $query .= "         wtb_namawt data_wt, ";
        $query .= "         to_char(wtb_create_dt,'DD/MM/YYYY') tgl_wt, ";
        $query .= "         to_char(wtb_create_dt,'HH24:MI:SS') jam_wt ";
        $query .= "    FROM log_btb ";
        $query .= "    JOIN ( ";
        $query .= "          SELECT MAX(wtb_namawt) wtb_namawt,  ";
        $query .= "                 wtb_filenpb,  ";
        $query .= "                 wtb_create_dt ";
        $query .= "            FROM log_btb_status ";
        $query .= "           WHERE wtb_status LIKE '%SUKSES!'  ";
        $query .= "           GROUP BY wtb_filenpb, wtb_create_dt ";
        $query .= "    ) wtb ";
        $query .= "      ON wtb_filenpb = btb_file ";
        $query .= ") ";
        $query .= "SELECT to_char(npb_tgl_proses, 'DD/MM/YYYY') tanggal_proses, ";
        $query .= "       ' ' || npb_kodetoko kd_toko,  ";
        $query .= "       ' ' || tko_namaomi nama_toko,  ";
        $query .= "       '  ' || npb_file nama_data,  ";
        $query .= "       npb_create_web jam_create_web,  ";
        $query .= "       npb_kirim jam_kirim,  ";
        $query .= "       npb_confirm tgl_konfirm, ";
        $query .= "       coalesce(tgl_bpb, '-') || ' ' || coalesce(jam_bpb, '') tgl_bpb, ";
        $query .= "       coalesce('  ' || data_wt, '-') data_wt, ";
        $query .= "       coalesce(tgl_wt, '-') || ' ' || coalesce(jam_wt, '') tgl_wt ";
        $query .= "  FROM log_npb ";
        $query .= "  JOIN tbmaster_tokoigr  ";
        $query .= "    ON npb_kodetoko = tko_kodeomi ";
        $query .= " LEFT JOIN log_btb_wt ";
        $query .= "    ON npb_file = filenpb ";
        $query .= " WHERE npb_tgl_proses BETWEEN TO_DATE('" . $dtAwal . "','YYYY-MM-DD')  ";
        $query .= "                          AND TO_DATE('" . $dtAkhir . "','YYYY-MM-DD')  ";
        $query .= " ORDER BY npb_tgl_proses DESC, npb_kodetoko ASC ";
        $data = DB::select($query);

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);

        // .Columns.Add("tanggal", "Tanggal NPB") '0
        // .Columns.Add("kdToko", "Toko") '1
        // .Columns.Add("namaToko", "Nama Toko") '2
        // .Columns.Add("namaData", "Nama Data") '3
        // .Columns.Add("jamCreateWeb", "Jam Create Web") '4
        // .Columns.Add("jamKirim", "Jam Kirim") '5
        // .Columns.Add("jamKonfirm", "Jam Konfirm") '6
        // .Columns.Add("tglBPB", "Tgl & Jam") '7
        // .Columns.Add("dataWT", "Data WT") '8
        // .Columns.Add("tglT", "Tgl & Jam Terima WT") '9
    }
}
