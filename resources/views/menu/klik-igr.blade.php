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

    .checkbox-table{
        vertical-align: middle;
    }

    .w-40-center{
        text-align: center!important;
        width: 40%!important;
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
        box-shadow: none!important;
    }

    #button_proses{
        height: 40px;
        width: 98px;
        font-weight: bold;
        margin-top: 30px;
    }

    #tb tbody tr{
        cursor: pointer;
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

    .btn-warning:hover{
        background: #ab7a00;
    }

    .btn-orange{
        background: #ff6f00;
    }

    .btn-orange:hover{
        background: #c85700;
        color: white;
    }

    .btn-light-red{
        background: #d14745b0;
    }

    .btn-light-red:hover{
        background: #742726b0;
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

    .detail-edit-pb{
        height: 31px;
        justify-content: flex-start;
    }

    .detail-edit-pb p:first-child{
        width: 85px;
    }

    .detail-edit-pb p{
        display: inline-block;
        margin: 0;
        font-size: 1rem;
    }

    .validasi-struk-form-group label{
        width: 155px;
        height: 38px;
        flex-shrink: 0;
        margin-right: 15px;
    }

    #tab_detail_transaksi .nav-item .nav-link.active{
        color: #012970;
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
                            <input type="date" class="form-control" id="tanggal_trans">
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
                        <button class="btn btn-blue">{{ $btnSendJalur }}</button>
                        <button class="btn btn-green">Ongkos Kirim</button>
                        <button class="btn btn-blue">Draft STRUK</button>
                        <button class="btn btn-green">Pembayaran Virtual Account</button>
                        <button class="btn btn-green" @if($btnKonfirmasiBayar) disabled @endif>Konfirmasi Pembayaran</button>
                        <button class="btn btn-blue">SALES</button>
                        <div class="d-flex flex-row" style="gap: 10px">
                            <button class="btn btn-royal w-100" style="height: 50px">Cetak Surat Jalan</button>
                            <button class="btn btn-royal w-100" style="height: 50px">Cetak IIK</button>
                        </div>
                        <button class="btn btn-light-red">{{ $btnPBBatal }}</button>
                        <button class="btn btn-light-red">List Item Picking Belum Transit</button>
                        <button class="btn btn-light-red">LOPP - COD</button>
                        <button class="btn btn-light-red">List PB Lebih dari Max Serah Terima</button>
                        <button class="btn btn-orange">Master Picker HH</button>
                        <button class="btn btn-orange">Listing Delivery</button>
                        <button hidden class="btn btn-orange">Master No. Pol Delivery Van</button> {{-- tidak digunakan --}}
                        <button hidden class="btn btn-orange">Master Driver</button> {{-- tidak digunakan --}}
                        <button hidden class="btn btn-orange">Master Deliveryman</button> {{-- tidak digunakan --}}
                        <button class="btn btn-warning">Re Create AWB</button>
                        <button class="btn btn-warning">Master Alasan Batal Kirim</button>
                        <button class="btn btn-warning">BA Pengembalian Dana</button>
                        <button class="btn btn-warning">BA Rusak Kemasan</button>
                        <button class="btn btn-royal">Cetak Form Pengembalian Barang</button>
                        <button class="btn btn-royal">Laporan Penyusutan Harian</button>
                        <button class="btn btn-royal">Laporan Pesanan Expired</button>
                        <button class="btn btn-royal">Bukti Serah Terima Kardus</button>
                    </div>
                </div>
            </div>
            <div class="col-8">
                <div class="card" style="height: calc(100vh - 235px)">
                    <div class="card-shadow">
                        <div class="card-body">
                            <div class="table-responsive position-relative">
                                <table class="table table-striped table-hover datatable-dark-primary w-100" id="tb">
                                    <thead>
                                        <tr>
                                            <th>SERVICE</th>
                                            <th>STATUS SEND JALUR</th>
                                            <th>DETAIL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <button class="btn btn-lg btn-primary d-none" id="loading_datatable" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                        Loading...
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-shadow" style="margin-top: 20px">
                    <div class="card-body bg-royal" style="height: 100px; border-radius: 8px;">
                        <ul class="list-unstyled list-info">
                            <li>F1 : Detail PB</li>
                            <li>F4 : Edit PB</li>
                            <li>F7 : Cetak Jalur Kertas</li>
                            <li>F10 : Hitung Ulang DSP/SP</li>
                            <li>F2 : Detail Promo</li>
                            <li>F5 : Reaktivasi PB</li>
                            <li>F8 : Cetak Penyusutan</li>
                            <li>F12 : Pembatalan DSP</li>
                            <li>F3 : Detail Otv Picking</li>
                            <li>F6 : Validasi Struk</li>
                            <li>F9 : Cetak Picking List 999</li>
                            <li>DELETE : Pembatalan PB</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section("modal")
<div class="modal fade" role="dialog" id="modal_detail" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title detail-title" style="color: #012970; font-weight: 600"></h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive position-relative">
                    <table class="table table-striped table-hover datatable-dark-primary w-100" id="tb_detail">
                        <thead>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_edit_pb" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">Edit Item PB Untuk Pembatalan</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between">
                    <div class="d-flex flex-column" style="gap: 9px">
                        <div class="detail-edit-pb detail-info">
                            <p>No. PB</p>
                            <p>:&nbsp; &nbsp; <span id="no_pb_detail_edit">0</span></p>
                        </div>
                        <div class="detail-edit-pb detail-info">
                            <p>Tanggal PB</p>
                            <p>:&nbsp; &nbsp; <span id="tanggal_pb_detail_edit">0</span></p>
                        </div>
                        <label for="cek_item_bermasalah" class="checkbox-label checkbox-label-sm" style="width: unset!important; z-index: 1000; gap: 10px">
                            <input type="checkbox" id="cek_item_bermasalah" onclick="$(this).val(this.checked ? 1 : 0)" value="0" style="height: 18px;">
                            Cek Item Bermasalah (Reload Datatable)
                        </label>
                    </div>

                    <div class="d-flex flex-column" style="gap: 9px">
                        <div class="detail-edit-pb detail-info">
                            <p>No. Trans</p>
                            <p>:&nbsp; &nbsp; <span id="no_trans_detail_edit">0</span></p>
                        </div>
                        <div class="d-flex" style="gap: 15px; height: 31px">
                            <label for="action_form_pembatalan" style="white-space: nowrap;" class="detail-info">Action : </label>
                            <select id="action_form_pembatalan" class="form-control" onchange="draw_tb_edit_pb(tb.row('.select-r').data())" style="height: 31px; padding: .175rem .75rem">
                             <option value="VALIDASI RAK">Validasi Rak</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="table-responsive position-relative" style="margin-top: -31px">
                    <table class="table table-striped table-hover datatable-dark-primary w-100" id="tb_edit_pb">
                        <thead>
                            <tr>
                                <th>PLU</th>
                                <th>Nama Barang</th>
                                <th>Qty Order</th>
                                <th>Qty Real</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <button class="btn btn-lg btn-primary d-none" id="loading_datatable_edit_pb" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                            <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            Loading...
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-warning" onclick="actionAdditionalItemBatal([], true);">Cetak</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-primary" onclick="actionF4Proses();">Proses</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_validasi_struk" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title detail-title" style="color: #012970; font-weight: 600">VALIDASI STRUK KLIK INDOGROSIR</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between">
                    <div class="d-flex flex-column" style="gap: 9px">
                        <div class="form-group d-flex align-items-center validasi-struk-form-group">
                            <label for="no_trans_validasi_struk" class="detail-info">No. Trans : </label>
                            <input type="text" class="form-control" disabled id="no_trans_validasi_struk">
                        </div>
                        <div class="form-group d-flex align-items-center validasi-struk-form-group">
                            <label for="no_pb_validasi_struk" class="detail-info">No. PB : </label>
                            <input type="text" class="form-control" disabled id="no_pb_validasi_struk">
                        </div>
                    </div>
                    <div class="d-flex flex-column" style="gap: 9px">
                        <div class="form-group d-flex align-items-center validasi-struk-form-group">
                            <label for="tanggal_trans_validasi_struk" class="detail-info">Tanggal Trans : </label>
                            <input type="date" class="form-control" disabled id="tanggal_trans_validasi_struk">
                        </div>
                        <div class="form-group d-flex align-items-center validasi-struk-form-group">
                            <label for="member_validasi_struk" class="detail-info">Member : </label>
                            <input type="text" class="form-control" disabled id="member_validasi_struk">
                        </div>
                    </div>
                </div>
                <div class="input-detail-struk position-relative mt-4" style="border: 3px solid #008080; border-radius: 5px; padding: 35px 23px;">
                    <div class="position-absolute text-light fw-bold p-2 text-center" style="background: #008080; top: -22px; left: 50%; translate: -50%; width: 185px; font-size: 1rem; font-weight: bold">INPUT DETAIL STRUK</div>
                    <div class="d-flex justify-content-between">
                        <div class="d-flex flex-column" style="gap: 9px">
                            <div class="form-group d-flex align-items-center validasi-struk-form-group">
                                <label for="no_struk_validasi_struk" class="detail-info bg-teal">No. Struk : </label>
                                <input type="text" class="form-control" id="no_struk_validasi_struk" maxlength="5">
                            </div>
                            <div class="form-group d-flex align-items-center validasi-struk-form-group">
                                <label for="tanggal_struk_validasi_struk" class="detail-info bg-teal">Tanggal Struk : </label>
                                <input type="date" class="form-control" id="tanggal_struk_validasi_struk" style="margin-right: 15px; width: 353px; flex-shrink: 0;">
                                <input type="time" class="form-control" id="time_struk_validasi_struk">
                            </div>
                            <div class="d-flex align-items-center flex-row" style="gap: 21px;">
                                <div class="form-group d-flex align-items-center validasi-struk-form-group">
                                    <label for="station_validasi_struk" class="detail-info bg-teal">Station : </label>
                                    <input type="text" class="form-control" id="station_validasi_struk" maxlength="2">
                                </div>
                                <div class="form-group d-flex align-items-center validasi-struk-form-group">
                                    <label for="cashier_id_validasi_struk" class="detail-info bg-teal">Cashier ID : </label>
                                    <input type="text" class="form-control" id="cashier_id_validasi_struk" maxlength="3">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="float-right mt-4">
                    <button type="button" style="width: 180px; margin-right: 15px" class="btn btn-lg btn-secondary" data-dismiss="modal">CLOSE</button>
                    <button type="button" class="btn btn-primary btn-lg" onclick="action_f6(true)" style="width: 180px">SIMPAN</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_hitung_ulang" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">Hitung Ulang DSP / SP</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-center flex-column align-items-center" style="border-radius: 6px; padding: 12px 15px; background: #cbcbcba8">
                    <div style="width: 600px;" class="d-flex justify-content-between">
                        <div class="detail-edit-pb detail-info" style="width: 350px">
                            <p>No. PB</p>
                            <p>:&nbsp; &nbsp; <span id="no_pb_hitung_ulang">0</span></p>
                        </div>
                        <div class="detail-edit-pb detail-info" style="width: 230px">
                            <p>Tanggal PB</p>
                            <p>:&nbsp; &nbsp; <span id="tanggal_pb_hitung_ulang">0</span></p>
                        </div>
                    </div>
                    <div style="width: 600px;" class="d-flex justify-content-between mt-2">
                        <div class="detail-edit-pb detail-info" style="width: 230px">
                            <p>Member</p>
                            <p>:&nbsp; &nbsp; <span id="kode_member_hitung_ulang">0</span></p>
                        </div>
                        <div class="detail-edit-pb detail-info" style="width: 350px">
                            <p class="w-100"><span id="nama_member_hitung_ulang">0</span></p>
                        </div>
                    </div>
                </div>
                <div class="detail-info bg-royal float-right" style="height: 25px; width: 150px; margin-top: 20px; margin-bottom: 4px; font-size: .9rem">*Edit di Qty Real</div>
                <div class="table-responsive position-relative">
                    <table class="table table-striped table-hover datatable-dark-primary w-100" id="tb_hitung_ulang">
                        <thead>
                            <tr>
                                <th>PLU</th>
                                <th>Deskripsi</th>
                                <th>Frac</th>
                                <th>Qty PB</th>
                                <th>Qty Real</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <button class="btn btn-lg btn-primary d-none" id="loading_datatable_hitung_ulang" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                            <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            Loading...
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-warning" onclick="actionAdditionalHitungUlang();">Hitung Ulang</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-primary" onclick="actionF4Proses();">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_pembatalan_pb" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">Modal Pembatalan PB</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive position-relative">
                    <table class="table table-striped table-hover datatable-dark-primary w-100 table-center" id="tb_pembatalan_pb">
                        <thead>
                            <tr>
                                <th>No. PB</th>
                                <th>No. Trans</th>
                                <th>Tgl. Trans</th>
                                <th>Alasan</th>
                                <th>Lain-lain</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <button class="btn btn-lg btn-primary d-none" id="loading_datatable_hitung_ulang" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                            <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            Loading...
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-primary" onclick="actionAdditionalSimpanPembatalanPB();">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_detail_transaksi" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">DETAIL TRANSAKSI</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="tab_detail_transaksi" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="info_trans-tab" data-toggle="tab" data-target="#info_trans" type="button" role="tab" aria-controls="info_trans" aria-selected="true">Info Trans</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pengiriman-tab" data-toggle="tab" data-target="#pengiriman" type="button" role="tab" aria-controls="pengiriman" aria-selected="false">Pengiriman</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pembayaran-tab" data-toggle="tab" data-target="#pembayaran" type="button" role="tab" aria-controls="pembayaran" aria-selected="false">Pembayaran</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="realisasi-tab" data-toggle="tab" data-target="#realisasi" type="button" role="tab" aria-controls="realisasi" aria-selected="true">Realisasi</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reprint_koli-tab" data-toggle="tab" data-target="#reprint_koli" type="button" role="tab" aria-controls="reprint_koli" aria-selected="false">Reprint Koli</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="info_trans" role="tabpanel" aria-labelledby="info-trans-tab">Info Trans</div>
                    <div class="tab-pane fade" id="pengiriman" role="tabpanel" aria-labelledby="Pengiriman-tab">Pengiriman</div>
                    <div class="tab-pane fade" id="pembayaran" role="tabpanel" aria-labelledby="Pembayaran-tab">Pembayaran</div>
                    <div class="tab-pane fade" id="realisasi" role="tabpanel" aria-labelledby="Realisasi-tab">Realisasi</div>
                    <div class="tab-pane fade" id="reprint_koli" role="tabpanel" aria-labelledby="reprint_koli-tab">Reprint Koli</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-primary" onclick="actionAdditionalSimpanPembatalanPB();">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_password_manager" status="" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border: 0; background: #2f4f4f!important">
            <div class="modal-body" style="border: 1px solid white; margin: 8px; padding: 15px 19px;">
                <div class="form-group d-flex align-items-center justify-content-center m-0" style="gap: 20px">
                    <label for="password_manager" style="white-space: nowrap; margin: 0; font-weight: bold; color: white">Password Manager : </label>
                    <input type="password" class="form-control" style="border: unset;" id="password_manager">
                    <button class="btn btn-primary" style="box-shadow: unset!important; width: 160px; font-weight: bold" onclick="actionAdditionalPasswordManager()">OK</button>
                    <button class="btn btn-secondary" style="box-shadow: unset!important; width: 160px; font-weight: bold" onclick="closeModalPasswordManager();">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('page-script')
<script src="{{ asset("js/project/klikIGRKeyDown.js") }}"></script>
<script>
    var isFunctionRunning = false;
    let statusSiapPicking = "{{ $statusSiapPicking }}";
    let statusSiapPacking = "{{ $statusSiapPacking }}";

    $(document).ready(function() {
        setDateNow("#tanggal_trans");
        tb = $('#tb').DataTable({
            processing: true,
            ajax: {
                url: currentURL + `/datatables/${$("#tanggal_trans").val()}/${statusSiapPicking}/${statusSiapPacking}`,
                type: 'GET'
            },
            columnDefs: [
                { className: 'text-center', targets: [0,1] },
            ],
            order: [],
            "paging": false,
            "searching": false,
            "scrollY": "calc(100vh - 400px)",
            "scrollCollapse": true,
            ordering: false,
            columns: [
                { data: 'service' },
                { data: 'STATUS SEND JALUR' },
                {
                    data: null,
                    defaultContent: "<button class='btn btn-info btn-detail'>DETAIL</button>",
                    className: "text-center"
                },
            ],
            rowCallback: function(row, data){
                $(row).click(function() {
                    $('#tb tbody tr').removeClass('select-r');
                    $(this).toggleClass("select-r");
                });
            },
        });

        tb_edit_pb = $('#tb_edit_pb').DataTable({
            data: [],
            language: {
                emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
            },
            columnDefs: [{ className: 'text-center', targets: "_all" }],
            columns: [
                { data: 'plu' },
                { data: 'nama_barang' },
                { data: 'qty_order' },
                { data: 'qty_realisasi' },
                {
                    data: null,
                    defaultContent: `<input type="checkbox" class="form-control checkbox-table d-inline checkbox-group">`,
                    className: "text-center"
                },
            ],
            ordering: false,
            "paging": false,
            "scrollY": "calc(100vh - 600px)",
            "scrollCollapse": true,
            lengthChange: false,
        });

        tb_hitung_ulang = $('#tb_hitung_ulang').DataTable({
            data: [],
            language: {
                emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
            },
            columnDefs: [{ className: 'text-center', targets: "_all" }],
            columns: [
                { data: 'plu' },
                { data: 'deskripsi' },
                { data: 'frac' },
                {
                    data: 'qtypb',
                    render: function(data, type, row) {
                        return parseFloat(parseInt(data)).toFixed(0);
                    }
                },
                {
                    data: 'qtyreal',
                    render: function(data, type, row) {
                        return `<input type="number" class="form-control input-hitung-ulang" style="width: 80px" value="${parseFloat(parseInt(data)).toFixed(0)}">`;
                    },
                    className: "text-center"
                },
            ],
            ordering: false,
            "paging": false,
            "scrollY": "calc(100vh - 600px)",
            "scrollCollapse": true,
            lengthChange: false,
        });

        var hasBtnDetailColumn = false;
        tb.columns().every(function () {
            if ($(this.header()).text() === "DETAIL") {
                hasBtnDetailColumn = true;
                return false;
            }
        });

        if (!hasBtnDetailColumn) {
            tb.column.add({
                "title": "DETAIL",
                "defaultContent": "<button class='btn btn-primary btn-detail'>DETAIL</button>",
                "className": "text-center"
            }).draw();
        }

        $(document).keydown(function(event) {
            if ((event.key.startsWith("F") && !isNaN(event.key.substring(1))) || event.key === "Delete") {
                event.preventDefault();
                if (isFunctionRunning || isModalShowing()) {
                    return;
                }
                if (event.key !== "F8" && tb.row(".select-r").data() === undefined) {
                    Swal.fire('Peringatan!', 'Pilih Data Terlebih Dahulu..!', 'warning');
                    return;
                }
                var functionName = "action_" + event.key.toLowerCase();
                if (["F4", "F5", "F6", "F7", "F12", "Delete"].includes(event.key.toLowerCase())) {
                    if (typeof window[functionName] === 'function' && tb.row(".select-r").data().no_trans !== null) {
                        isFunctionRunning = true;
                        window[functionName]();
                        setTimeout(function() {
                        isFunctionRunning = false;
                    }, 300);
                }
            } else {
                if (typeof window[functionName] === 'function') {
                    isFunctionRunning = true;
                    window[functionName]();
                    setTimeout(function() {
                        isFunctionRunning = false;
                    }, 300);
                }
            }
        }
    });


    // LIST PLU BERMASALAH DIAMBIL DARI MANA ?
    $("#cek_item_bermasalah").on("change", function(){
        if ($(this).is(':checked')) {
            // tb_edit_pb.columns(STATUS_COLUMN_INDEX).search('true').draw();
        }
    });

    $(document).on('keypress', '.input-hitung-ulang', function(e) {
        var key = e.which || e.keyCode;

        var inputValue = $(this).val();
        if ((key !== 13 && key !== 8 && isNaN(String.fromCharCode(key))) || (inputValue.length >= 4)) {
            e.preventDefault();
        }
    });

    $(document).on('change', '.input-hitung-ulang', function(e) {
        if ($(this).val() === '') {
            $(this).val(0);
        }
    });

});
</script>
@endpush

