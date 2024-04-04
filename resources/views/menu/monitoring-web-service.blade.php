@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">MONITORING WT WEB SERVICE</h1>
@endsection

@section('css')
<style>
    .table thead th{
        border-left: 2px solid #d7d7d7;
        border-right: 2px solid #d7d7d7;
    }

    .table tbody td{
        color: black;
    }
</style>
@endsection

@section('content')
    <script src="{{ url('js/home.js?time=') . rand() }}"></script>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="form-group d-flex" style="gap: 15px; width: 600px">
                            <label for="date_awal" class="detail-info" style="white-space: nowrap">Tanggal NPB &nbsp;:</label>
                            <input type="date" class="form-control" id="date_awal">
                            <input type="date" class="form-control" id="date_akhir">
                            <button class="btn btn-success btn-lg" style="padding: 8px 18px; white-space: nowrap" onclick="filterDatatable();">Filter</button>
                        </div>
                        <div class="table-responsive position-relative">
                            <table class="table table-striped datatable-dark-primary w-100 table-center" id="tb">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Tanggal NPB</th>
                                        <th rowspan="2">Toko</th>
                                        <th rowspan="2">Nama Toko</th>
                                        <th rowspan="2">Nama Data</th>
                                        <th colspan="2">Toko Igr.</th>
                                        <th>Web Service</th>
                                        <th>BPB Toko Idm.</th>
                                        <th rowspan="2">Data WT</th>
                                        <th>Toko Igr.</th>
                                    </tr>
                                    <tr>
                                        <th>Jam Create Web</th>
                                        <th>Jam Kirim</th>
                                        <th>Jam Konfirm</th>
                                        <th>Tgl & Jam</th>
                                        <th>Tgl & Jam Terima WT</th>
                                    </tr>
                                </thead>
                            </table>
                            <button class="btn btn-lg btn-primary d-none" id="loading_datatable" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                                    <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                    Loading...
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('page-script')
<script>
    let tb;
    $(document).ready(function() {
        setDateNow("#date_awal");
        setDateNow("#date_akhir");
        tb = $('#tb').DataTable({
            language: {
                emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
            },
            processing: true,
            ajax: {
                url: currentURL + `/datatables/${encodeURIComponent($("#date_awal").val())}/${encodeURIComponent($("#date_akhir").val())}`,
                type: 'GET'
            },
            columnDefs: [
                { className: 'text-center', targets: [0] },
            ],
            order: [],
            ordering: false,
            columns: [
                { data: 'tanggal_proses' },
                { data: 'kd_toko' },
                { data: 'nama_toko' },
                { data: 'nama_data' },
                { data: 'jam_create_web' },
                { data: 'jam_kirim' },
                { data: 'tgl_konfirm' },
                { data: 'tgl_bpb' },
                { data: 'data_wt' },
                { data: 'tgl_wt' },
            ],
        });
    })

    function filterDatatable(){
        tb.settings()[0].ajax.url = currentURL + `/datatables/${encodeURIComponent($("#date_awal").val())}/${encodeURIComponent($("#date_akhir").val())}`;
        tb.ajax.reload();
    }
</script>
@endpush