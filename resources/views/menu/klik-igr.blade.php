@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">KLIK IGR</h1>
@endsection

@section('css')
<style>
    .header{
        padding: 0 15px
    }

    input[type=file]{
        padding: 3px 8px;
        height: 38px!important;
    }

    .checkbox-label-sm{
        font-size: .8rem;
        width: 150px;
        padding: 6px 10px;
        margin: 0;
    }

    .checkbox-label-sm input{
        width: 16px;
        height: 16px;
    }

    .card-button{
        gap: 8px;
        /* flex-basis: fit-content; */
        overflow-y: auto;
    }

    .card-button button{
        border-radius: 0;
        font-weight: bold;
    }

    .card-button button:hover{
        border: unset!important;
    }

    .card-button > button{
        border-radius: 0;
        flex: 0 0 50px;
        min-height: 0;
        color: black;
        border: 1px solid black;
    }

    #button_proses{
        height: 40px;
        width: 98px;
        font-weight: bold;
        margin-top: 30px;
    }

    .btn-blue{
        background: #2aaef8db;
    }

    .btn-blue:hover{
        background: #1D75A8DB;
        color: white;
    }

    .btn-green{
        background: #47b460b0;
    }
    
    .btn-green:hover{
        background: #307841B0;
        color: white;
    }

    ul.list-info {
        display: grid;
        grid-template-columns: repeat(4, 3fr);
        grid-auto-rows: auto;
        padding-left: 0;
        color: white;
    }
    ul.list-info li {
        font-size: .9rem;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
    <script src="{{ url('js/home.js?time=') . rand() }}"></script>

    <div class="container-fluid">
        <div class="row">
            <div class="col-4">
                <div class="card shadow mb-2">
                    <div class="card-body">
                        <div class="form-group d-flex" style="gap: 15px">
                            <label for="upload_rtt" class="detail-info" style="white-space: nowrap">Tgl Trans &nbsp;:</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="form-group d-flex" style="gap: 15px">
                            <label for="upload_rtt" class="detail-info" style="white-space: nowrap">Path CSV &nbsp;:</label>
                            <input type="file" class="form-control">
                        </div>
                        <div class="d-flex w-100 justify-content-between" style="gap: 15px">
                            <div class="d-flex flex-column" style="gap: 7px">
                                <label for="auto_refresh" class="checkbox-label checkbox-label-sm">
                                    <input type="checkbox" id="auto_refresh" onclick="$(this).val(this.checked ? 1 : 0)" value="0">
                                    Auto Refresh
                                </label>
                                <label for="auto_send_hh" class="checkbox-label checkbox-label-sm">
                                    <input type="checkbox" id="auto_send_hh" onclick="$(this).val(this.checked ? 1 : 0)" value="0">
                                    Auto Send HH
                                </label>
                            </div>
                            <button class="btn btn-primary" id="button_proses">Proses</button>
                        </div>
                    </div>
                </div>
                <div class="card shadow">
                    <div class="card-body d-flex flex-column card-button" style="height: calc(100vh - 340px);">
                        <button class="btn btn-blue">Send Hand Held</button>
                        <button class="btn btn-green">Ongkos Kirim</button>
                        <button class="btn btn-blue">Draft STRUK</button>
                        <button class="btn btn-green">Pembayaran Virtual Account</button>
                        <button class="btn btn-green">Konfirmasi Pembayaran</button>
                        <button class="btn btn-blue">Sales</button>
                        <div class="d-flex flex-row" style="gap: 10px"> 
                            <button class="btn btn-royal w-100" style="height: 50px">Cetak Surat Jalan</button>
                            <button class="btn btn-royal w-100" style="height: 50px">Cetak IIK</button>
                        </div>
                        <button class="btn btn-warning">List Item Picking Belum Transit</button>
                        {{-- <button class="btn btn-warning">List Item Batal</button>
                        <button class="btn btn-warning">List Item Batal</button>
                        <button class="btn btn-warning">List Item Batal</button>
                        <button class="btn btn-warning">List Item Batal</button> --}}
                    </div>
                </div>
            </div>
            <div class="col-8">
                <div class="card" style="height: calc(100vh - 235px)">
                    <div class="card-shadow">
                        <div class="card-body">
    
                        </div>
                    </div>
                </div>

                <div class="card-shadow" style="margin-top: 20px">
                    <div class="card-body bg-royal" style="height: 100px; border-radius: 8px;">
                        <ul class="list-unstyled list-info">
                            <li>F1 : Detail PB</li>
                            <li>F2 : Detail Promo</li>
                            <li>F3 : Detail Otv Picking</li>
                            <li>F4 : Edit PB</li>
                            <li>F5 : Reaktivasi PB</li>
                            <li>F6 : Validasi Struk</li>
                            <li>F7 : Cetak Jalur Kertas</li>
                            <li>F8 : Cetak Penyusutan</li>
                            <li>F9 : Cetak Picking List 999</li>
                            <li>F10 : Hitung Ulang DSP/SP</li>
                            <li>F11 : Pembatalan DSP</li>
                            <li>F12 : Pembatalan PB</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('page-script')
<script>
    let tb;
    let tb_detail;
    $(document).ready(function() {
        tb = $('#tb').DataTable({
            processing: true,
            ajax: {
                url: currentURL + "/datatables",
                type: 'GET'
            },
            columnDefs: [
                { className: 'text-center', targets: [0] },
            ],
            order: [],
            "paging": false,
            "searching": false,
            "scrollY": "calc(100vh - 400px)",
            "scrollCollapse": true,
            ordering: false,
            columns: [
                { data: null,searchable: false,orderable: false },
                { data: 'no_rtt' },
                { data: 'tgl_rtt' },
                { data: 'toko_tutup' },
                { data: 'toko_tujuan' },
            ],
            rowCallback: function (row, data) {
                $('td:eq(0)', row).html(`<input type="checkbox" class="form-control checkbox-table" data-rtt="${data.no_rtt}" data-tgl-rtt="${data.tgl_rtt}" data-toko-tutup="${data.toko_tutup}" data-toko-tujuan="${data.toko_tujuan}" onchange="checkboxTableChange(this)">`);
            }

        });

        tb_detail = $('#tb_detail').DataTable({
            language: {
                emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Detail</div>",
            },
            columns: [
                { data: 'no'},
                { data: 'plu'},
                { data: 'keterangan'},
                { data: 'retur'},
                { data: 'baik'},
                { data: 'ba'},
                { data: 'price'},
                { data: 'ppn'},
                { data: 'status'},
                { data: 'tag_idm'},
                { data: 'lokasi'},
            ],
            columnDefs: [
                { className: 'text-center-vh', targets: '_all' },
            ],
            data: [],
            ordering: false,
        });
    });
</script>
@endpush

