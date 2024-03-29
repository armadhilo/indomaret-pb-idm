@extends('master')
@section('title')
    <h1 class="pagetitle">CEK JUAL</h1>
@endsection

@section('css')
<style>
    .header{
        margin-bottom: 40px;
    }

    .header-action > *{
        width: unset;
        display: inline-block;
    }

    .table tbody tr.deactive td{
        background-color: #ffb6c19e;
    }

    .select-r td {
        background-color: #566cfb !important;
        color: white!important;
    }

    .table td{
        color: black;
        border-top: 1px solid #d5d7db!important;
    }
</style>
@endsection

@section('content')
    <script src="{{ url('js/home.js?time=') . rand() }}"></script>

    <div class="container-fluid">
        <div class="card shadow mb-4">
        <div class="card-body">
                <div class="d-flex flex-wrap header-action align-items-center mb-4 mt-1" style="gap: 12px;">
                    <div class="header-action">
                        <label for="tanggal_transaksi" class="mr-2" style="color: #012970; font-size: 1rem;">Pilih Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="tanggal_transaksi" class="tanggal_transaksi">
                    </div>
                    <button class="btn btn-warning" onclick="queryDataTables()">View</button>
                    <button class="btn btn-success">Hitung Tunai Fisik</button>
                </div>
                <table class="table table-striped table-hover datatable-dark-primary w-100" id="table_transaksi">
                    <thead>
                        <tr>
                            <th>Kasir</th>
                            <th>Station</th>
                            <th>Summary</th>
                            <th>Header</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('page-script')
    <script>
        function initializeDatatables(){
            tb = $('#table_transaksi').DataTable({
                ajax: {
                    url: '/home/datatables',
                    method: 'GET',
                    data: {
                        transaction_date: $('#tanggal_transaksi').val()
                    },
                    dataType: 'json',
                },
                language: {
                    emptyTable: "Tidak Ada Data Transaksi"
                },
                columnDefs: [
                    { className: 'text-center-vh', targets: '_all' },
                ],
                columns: [
                    { data: 'cashier' },
                    { data: 'station' },
                    { data: 'js_totsalesamt', render: function(data){ return "Rp. " + fungsiRupiah(data); } },
                    { data: 'jh_transactionamt', render: function(data){ return "Rp. " + fungsiRupiah(data); } },
                    { data: 'trjd_nominalamt', render: function(data){ return "Rp. " + fungsiRupiah(data); } },
                ],
                rowCallback: function(row, data){
                    $(row).on('click', function() {
                        $('#table_transaksi tbody tr').removeClass('select-r');
                        $(this).addClass("select-r");
                    });

                    $(row).dblclick(function() {
                        window.location.href = `/cashier/${data.cashier}/${data.station}/${tb.settings()[0].ajax.data.transaction_date}`;
                    });
                },
                createdRow: function (row, data, dataIndex) {
                    if (data.is_pink === 1) {
                        $(row).addClass('deactive');
                    }
                },
            });

        }

        function queryDataTables(){
            tb.settings()[0].ajax.data.transaction_date = $('#tanggal_transaksi').val();
            tb.ajax.reload()
        }

        $(document).ready(function() {
            let tb;
            let today = new Date().toISOString().split('T')[0];
            $('#tanggal_transaksi').val(today);
            initializeDatatables();
        });


        $(document.body).on('click', function (event) {
            if (!$(event.target).closest('#table_transaksi').length) {
                $('#table_transaksi tbody tr').removeClass('select-r');
            }
        });
    </script>
    @endpush
@endsection
