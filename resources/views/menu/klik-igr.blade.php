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

    #modal_master_data_tb tbody tr:hover, #modal_master_picking_tb2 tbody tr:hover, #modal_master_picking_group_tb2 tbody tr:hover, #modal_re_create_awb_tb tbody tr:hover{
        cursor: pointer;
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

    #tab_detail_transaksi .nav-item .nav-link.active, #tab_ba_barang_rusak .nav-item .nav-link.active{
        color: #012970;
        font-weight: bold;
        position: relative;
        z-index: 300;
    }

    .w-130px{
        width: 130px!important;
    }

    .w-230px{
        width: 230px!important;
    }

    #select2-no_koli_detail_transaksi_tab5-container{
        height: 38px;
        border-radius: 0px;
        padding-top: 4px;
    }

    /* Modal Specific CSS */
    *,
    *:after,
    *:before {
        box-sizing: border-box;
    }
    #form_group_jalur_picking .modal-content {
        border: 0;
        background: #2f4f4f!important;
    }

    #form_group_jalur_picking .modal-body {
        border: 1px solid white;
        margin: 8px;
        padding: 15px 19px;
    }

    #form_group_jalur_picking .modal-form-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
    }

    #form_group_jalur_picking .modal-label {
        display: flex;
        cursor: pointer;
        font-weight: 500;
        position: relative;
        overflow: hidden;
        margin-bottom: 0.375em;
    }

    #form_group_jalur_picking .modal-label input {
        position: absolute;
        left: -9999px;
    }

    #form_group_jalur_picking .modal-label input:checked + span {
        background-color: mix(#fff, #00005c, 84%);
    }

    #form_group_jalur_picking .modal-label span {
        display: flex;
        align-items: center;
        padding: 0.375em 0.75em 0.375em 0.375em;
        border-radius: 99em; /* or something higher... */
        transition: 0.25s ease;
    }

    #form_group_jalur_picking .modal-label span:hover {
        background-color: mix(#fff, #00005c, 84%);
    }

    #form_group_jalur_picking .modal-label span:before {
        display: flex;
        flex-shrink: 0;
        content: "";
        background-color: #fff;
        width: 1.5em;
        height: 1.5em;
        border-radius: 50%;
        margin-right: 0.375em;
        transition: 0.25s ease;
    }

    #form_group_jalur_picking .modal-label input:checked + span:before {
        box-shadow: inset 0 0 0 0.3765em #00005c;
    }

    #form_group_jalur_picking .modal-label input + span:before {
        box-shadow: inset 0 0 0 0.1765em #00005c;
    }

    #form_group_jalur_picking .modal-button {
        box-shadow: unset!important;
        width: 160px;
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
                            <input type="file" class="form-control" accept=".zip" id="path_zip">
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
                            <label for="pick_rak_toko" class="checkbox-label checkbox-label-sm @if(!($cbPickRakTokoVisible ?? false)) d-none @endif" style="height: 31px"  >
                                <input type="checkbox" id="pick_rak_toko" onclick="$(this).val(this.checked ? 1 : 0)" value="0">
                                Pick Rak Toko
                            </label>
                            <button class="btn btn-primary" id="button_proses">Proses</button>
                        </div>
                    </div>
                </div>
                <div class="card shadow" id="container_btn_action">
                    <div class="card-body d-flex flex-column card-button" style="height: calc(100vh - 340px);">
                        <button class="btn btn-action btn-blue @if(!($btnSendJalur ?? false)) d-none @endif" actionName="SendHandheld">
                            {{ $btnSendJalur ?? '' }}
                        </button>
                        <button class="btn btn-action btn-green" actionName="OngkosKirim">Ongkos Kirim</button>
                        <button class="btn btn-action btn-blue" actionName="DraftStruk">Draft STRUK</button>
                        <button class="btn btn-action btn-green" actionName="PembayaranVA">Pembayaran Virtual Account</button>
                        <button class="btn btn-action btn-green" actionName="KonfirmasiPembayaran" @if(!($btnKonfirmasiBayar ?? false)) disabled @endif>Konfirmasi Pembayaran</button>
                        <button class="btn btn-action btn-blue" actionName="Sales">SALES</button>
                        <div class="d-flex flex-row" style="gap: 10px"> 
                            <button class="btn btn-action btn-royal w-100" actionName="CetakSuratJalan" style="height: 50px">Cetak Surat Jalan</button>
                            <button class="btn btn-action btn-royal w-100" actionName="CetakIIK" style="height: 50px">Cetak IIK</button>
                        </div>
                        <button class="btn btn-action btn-light-red" actionName="PbBatal">{{ $btnPBBatal ?? '' }}</button>
                        <button class="btn btn-action btn-light-red" actionName="ItemPickingBelumTransit">List Item Picking Belum Transit</button>
                        <button class="btn btn-action btn-light-red" actionName="LoppCod">LOPP - COD</button>
                        <button class="btn btn-action btn-light-red" actionName="ListPBLebihDariMaxSerahTerima">List PB Lebih dari Max Serah Terima</button>
                        <button class="btn btn-action btn-orange" actionName="MasterPickingHH">Master Picker HH</button>
                        <button class="btn btn-action btn-orange" actionName="ListingDelivery">Listing Delivery</button>
                        {{-- <button class="btn btn-action btn-orange" actionName="MasterPolDeliveryVan">Master No. Pol Delivery Van</button>
                        <button class="btn btn-action btn-orange" actionName="MasterDriver">Master Driver</button>
                        <button class="btn btn-action btn-orange" actionName="MasterDeliveryman">Master Deliveryman</button> --}}
                        <button class="btn btn-action btn-warning" actionName="ReCreateAWB">Re Create AWB</button>
                        <button class="btn btn-action btn-warning" actionName="MasterAlasanbatalKirim">Master Alasan Batal Kirim</button>
                        <button class="btn btn-action btn-warning" actionName="BAPengembalianDana">BA Pengembalian Dana</button>
                        <button class="btn btn-action btn-warning" actionName="BaRusakKemasan">BA Rusak Kemasan</button>
                        <button class="btn btn-action btn-royal" actionName="CetakFormPengembalianBarang">Cetak Form Pengembalian Barang</button>
                        <button class="btn btn-action btn-royal" actionName="LaporanPenyusutanHarian">Laporan Penyusutan Harian</button>
                        <button class="btn btn-action btn-royal" actionName="LaporanPesananExpired">Laporan Pesanan Expired</button>
                        <button class="btn btn-action btn-royal" actionName="BuktiSerahTerimaKardus">Bukti Serah Terima Kardus</button>
                    </div>
                </div>
            </div>
            <div class="col-8">
                <div class="card" style="height: calc(100vh - 235px)">
                    <div class="card-shadow">
                        <div class="card-body">
                            <div class="d-flex mb-2" style="gap: 20px;">
                                <div class="detail-info text-light bg-royal" style="width: 50%; height: 40px; font-weight: bold; font-size: .9rem">No. Trans yang Dipilih : <span id="label_no_trans" style="padding-left: 6px">-</span></div>
                                <div class="detail-info text-light" style="background: #bf0000 !important; width: 50%; height: 40px; font-weight: bold; font-size: .9rem" id="label_item_batal">*Ada Item Belum Dikembalikan Ke Rak</div>
                            </div>
                            <div class="table-responsive position-relative">
                                <table class="table table-striped table-hover datatable-dark-primary display nowrap" id="tb" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>NO</th>
                                            <th>STATUS</th>
                                            <th>KODE MEMBER</th>
                                            <th>TIPE MEMBER</th>
                                            <th>NO PB</th>
                                            <th>NO TRANS</th>
                                            <th>NO PO</th>
                                            <th>ONGKIR</th>
                                            <th>TIPE BAYAR</th>
                                            <th>SERVICE</th>
                                            <th>TGL & JAM PB</th>
                                            <th>MAX SERAH TERIMA</th>
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">DETAIL TRANSAKSI</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="height: 486px!important; overflow: hidden">
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
                    <div class="tab-pane fade show active" id="info_trans" role="tabpanel" aria-labelledby="info-trans-tab">
                        <div class="p-3" style="margin-top: -2px; z-index: 25; position: relative; border: 2px solid #d3d3d3; padding-bottom: 7px!important">
                            <div class="d-flex" style="gap: 20px">
                                <div class="form-group d-flex align-items-center child-no-radius" style="flex: 5">
                                    <label for="status_detail_transaksi_tab1" class="detail-info text-nowrap bg-teal h-38px w-100px flex-shrink-0">Status : </label>
                                    <input type="text" class="form-control input-detail-transaksi" disabled id="status_detail_transaksi_tab1">
                                </div>
                                <div class="form-group d-flex align-items-center child-no-radius" style="flex: 4">
                                    <label for="no_po_detail_transaksi_tab1" class="detail-info text-nowrap bg-teal h-38px w-100px flex-shrink-0">No. PO : </label>
                                    <input type="text" class="form-control input-detail-transaksi" disabled id="no_po_detail_transaksi_tab1">
                                </div>
                            </div>
                            <div class="d-flex" style="gap: 20px">
                                <div class="form-group d-flex align-items-center child-no-radius" style="flex: 5">
                                    <label for="no_pb_detail_transaksi_tab1" class="detail-info text-nowrap bg-teal h-38px w-100px flex-shrink-0">No. PB : </label>
                                    <input type="text" class="form-control input-detail-transaksi" disabled id="no_pb_detail_transaksi_tab1">
                                </div>
                                <div class="form-group d-flex align-items-center child-no-radius" style="flex: 4">
                                    <label for="tgl_pb_detail_transaksi_tab1" class="detail-info text-nowrap bg-teal h-38px w-100px flex-shrink-0">Tgl. PB : </label>
                                    <input type="date" class="form-control input-detail-transaksi" disabled id="tgl_pb_detail_transaksi_tab1">
                                </div>
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="kode_member_detail_transaksi_tab1" class="detail-info text-nowrap bg-teal h-38px w-100px flex-shrink-0">Member : </label>
                                <div class="child-no-radius d-flex flex-row w-100">
                                    <input type="text" class="form-control input-detail-transaksi" style="width: 170px" disabled id="kode_member_detail_transaksi_tab1">
                                    <input type="text" class="form-control input-detail-transaksi" style="width: 100%" disabled id="nama_member_detail_transaksi_tab1">
                                </div>
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="no_member_detail_transaksi_tab1" class="detail-info text-nowrap bg-teal h-38px flex-shrink-0" style="width: 170px">No. HP Member : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="no_member_detail_transaksi_tab1">
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="no_penerima_detail_transaksi_tab1" class="detail-info text-nowrap bg-teal h-38px flex-shrink-0" style="width: 170px">No. HP Penerima : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="no_penerima_detail_transaksi_tab1">
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="email_detail_transaksi_tab1" class="detail-info text-nowrap bg-teal h-38px w-100px flex-shrink-0">Email : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="email_detail_transaksi_tab1">
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="alamat_detail_transaksi_tab1" class="detail-info text-nowrap bg-teal h-38px w-100px flex-shrink-0">Alamat : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="alamat_detail_transaksi_tab1">
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pengiriman" role="tabpanel" aria-labelledby="Pengiriman-tab">
                        <div class="p-3" style="margin-top: -2px; z-index: 25; position: relative; border: 2px solid #d3d3d3; padding-bottom: 7px!important">
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="flag_pengiriman_detail_transaksi_tab2" class="detail-info text-nowrap bg-teal h-38px w-130px flex-shrink-0">Pengiriman : </label>
                                <div class="d-flex flex-row w-100 child-no-radius" style="gap: 20px">
                                    <input type="text" class="form-control input-detail-transaksi" disabled id="flag_pengiriman_detail_transaksi_tab2">
                                    <input type="text" class="form-control input-detail-transaksi" disabled id="ekspedisi_pengiriman_detail_transaksi_tab2">
                                </div>
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="total_ongkir_detail_transaksi_tab2" class="detail-info text-nowrap bg-teal h-38px w-130px flex-shrink-0">Ongkir : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="total_ongkir_detail_transaksi_tab2" style="width: 290px">
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="pot_ongkir_detail_transaksi_tab2" class="detail-info text-nowrap bg-teal h-38px w-130px flex-shrink-0">Pot. Ongkir : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="pot_ongkir_detail_transaksi_tab2" style="width: 290px">
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pembayaran" role="tabpanel" aria-labelledby="Pembayaran-tab">
                        <div class="p-3" style="margin-top: -2px; z-index: 25; position: relative; border: 2px solid #d3d3d3; padding-bottom: 7px!important">
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="kredit_pembayaran_detail_transaksi_tab3" class="detail-info text-nowrap bg-teal h-38px w-130px flex-shrink-0">Pembayaran : </label>
                                <div class="d-flex flex-row w-100 child-no-radius" style="gap: 20px">
                                    <input type="text" class="form-control flex-shrink-0 input-detail-transaksi" disabled id="kredit_pembayaran_detail_transaksi_tab3" style="width: 170px">
                                    <input type="text" class="form-control input-detail-transaksi" disabled id="tipe_pembayaran_detail_transaksi_tab3">
                                </div>
                            </div>
                            <div class="d-flex flex-row" style="gap: 20px">
                                <div class="form-group d-flex align-items-center child-no-radius">
                                    <label for="tgl_bayar_detail_transaksi_tab3" class="detail-info text-nowrap bg-teal h-38px w-130px flex-shrink-0">Tgl Bayar : </label>
                                    <input type="date" class="form-control input-detail-transaksi" disabled id="tgl_bayar_detail_transaksi_tab3" style="width: 170px">
                                </div>
                                <div class="form-group d-flex align-items-center child-no-radius" style="flex: 1">
                                    <label for="no_ref_detail_transaksi_tab3" class="detail-info text-nowrap bg-teal h-38px w-130px flex-shrink-0">No. Ref : </label>
                                    <input type="text" class="form-control input-detail-transaksi" disabled id="no_ref_detail_transaksi_tab3">
                                </div>
                            </div>

                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="admin_fee_detail_transaksi_tab3" class="detail-info text-nowrap bg-teal h-38px w-230px flex-shrink-0">Admin Fee : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="admin_fee_detail_transaksi_tab3">
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="pembayaranVA_detail_transaksi_tab3" id="lbl_pembayaranVA_detail_transaksi_tab3" class="detail-info text-nowrap bg-teal h-38px w-230px flex-shrink-0">Pembayaran VAMANDIRI : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="pembayaranVA_detail_transaksi_tab3">
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="pembayaran_saldo_detail_transaksi_tab3" class="detail-info text-nowrap bg-teal h-38px w-230px flex-shrink-0">Pembayaran Saldo : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="pembayaran_saldo_detail_transaksi_tab3">
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="pembayaran_poin_detail_transaksi_tab3" class="detail-info text-nowrap bg-teal h-38px w-230px flex-shrink-0">Pembayaran Poin : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="pembayaran_poin_detail_transaksi_tab3">
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="total_pembayaran_detail_transaksi_tab3" class="detail-info text-nowrap bg-teal h-38px w-230px flex-shrink-0">Total Pembayaran : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="total_pembayaran_detail_transaksi_tab3">
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="realisasi" role="tabpanel" aria-labelledby="Realisasi-tab">
                        <div class="d-flex px-2" style="margin-top: -2px; z-index: 25; position: relative; border: 2px solid #d3d3d3; padding-bottom: 7px!important; gap: 25px">
                            <div class="p-2" style="flex: 1">
                                <div class="detail-info bg-royal h-38px mt-3 mb-2">ORDER</div>
                                <div class="pt-3 pb-0 px-3 d-flex flex-column" style="gap: 10px">
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="qty_order_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Item : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="qty_order_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="dpp_order_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">DPP : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="dpp_order_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="ppn_order_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">PPN : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="ppn_order_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="diskon_order_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Diskon : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="diskon_order_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="cashback_order_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Cashback : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="cashback_order_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="ongkir_order_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Ongkir : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="ongkir_order_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="kupon_order_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Kupon : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="kupon_order_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="total_order_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Total : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="total_order_detail_transaksi_tab4">
                                    </div>
                                </div>
                            </div>
                            <div class="p-2" style="flex: 1">
                                <div class="detail-info bg-royal h-38px mt-3 mb-2">REALISASI</div>
                                <div class="pt-3 pb-0 px-3 d-flex flex-column" style="gap: 10px">
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="qty_real_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Item : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="qty_real_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="dpp_real_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">DPP : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="dpp_real_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="ppn_real_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">PPN : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="ppn_real_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="diskon_real_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Diskon : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="diskon_real_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="cashback_real_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Cashback : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="cashback_real_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="ongkir_real_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Ongkir : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="ongkir_real_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="kupon_real_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Kupon : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="kupon_real_detail_transaksi_tab4">
                                    </div>
                                    <div class="form-group d-flex align-items-center child-no-radius m-0">
                                        <label for="total_real_detail_transaksi_tab4" class="detail-info text-nowrap bg-teal h-30px w-100px flex-shrink-0">Total : </label>
                                        <input type="text" class="form-control h-30px input-detail-transaksi" disabled id="total_real_detail_transaksi_tab4">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="reprint_koli" role="tabpanel" aria-labelledby="reprint_koli-tab">
                        <div class="p-3 d-flex flex-column align-items-center" style="gap: 3px; margin-top: -2px; z-index: 25; position: relative; border: 2px solid #d3d3d3; padding-bottom: 7px!important">
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="tgl_trans_detail_transaksi_tab5" class="detail-info text-nowrap bg-teal h-38px w-130px flex-shrink-0">Tanggal Trans : </label>
                                <input type="date" class="form-control input-detail-transaksi" disabled id="tgl_trans_detail_transaksi_tab5" style="width: 290px">
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="no_trans_detail_transaksi_tab5" class="detail-info text-nowrap bg-teal h-38px w-130px flex-shrink-0">Nomor Trans : </label>
                                <input type="text" class="form-control input-detail-transaksi" disabled id="no_trans_detail_transaksi_tab5" style="width: 290px">
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius">
                                <label for="no_urut_detail_transaksi_tab5" class="detail-info text-nowrap bg-teal h-38px w-130px flex-shrink-0">Nomor Koli : </label>
                                <select class="form-control input-detail-transaksi select2" id="no_koli_detail_transaksi_tab5" style="width: 290px">
                                </select>
                            </div>
                            <button class="btn btn-primary btn-lg mt-2" style="width: 246px; height: 45px; margin-bottom: 16px" id="btn_reprint" onclick="actionReprintKoli()">Reprint</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_pilih_jalur_picking" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">Pilih Jalur Picking</h5>
            </div>
            <div class="modal-body">
                <div class="form-group d-flex align-items-center justify-content-center m-0 modal-form-group" id="form_group_jalur_picking">
                    <label class="modal-label">
                        <input type="radio" name="input_jalur_picking" value="1" checked/>
                        <span>DPD</span>
                    </label>
                    <label class="modal-label">
                        <input type="radio" name="input_jalur_picking" value="2"/>
                        <span>HandHeld</span>
                    </label>
                    <button class="btn btn-primary modal-button" style="margin-left: 35px; margin-right: 18px;" onclick="actionSendHandheld(true)">OK</button>
                    <button class="btn btn-secondary modal-button" data-dismiss="modal" aria-label="Close">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>  

<div class="modal fade" role="dialog" id="modal_ekspedisi" data-simulasi="false" data-ongkos="" data-zona="" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">Hitung Ongkos Kirim</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div style="position: static!important">
                    <div class="d-flex justify-content-between flex-column position-relative pb-5" style="gap: 15px">
                        <div class="form-group d-flex align-items-center validasi-struk-form-group child-no-radius m-0">
                            <label for="cbPengirim_modal_ekspedisi" id="lblPengirim_modal_ekspedisi" class="detail-info bg-teal m-0 w-200px">Pengiriman : </label>
                            <select class="form-control" id="cbPengirim_modal_ekspedisi" style="width: 275px">
                                <option value="TEAM DELIVERY IGR" selected>TEAM DELIVERY IGR</option>
                                <option value="EKSPEDISI">EKSPEDISI</option>
                            </select>
                        </div>
                        <div class="form-group d-flex align-items-center validasi-struk-form-group child-no-radius m-0">
                            <label for="cbNamaEks_modal_ekspedisi" id="lblNama_modal_ekspedisi" class="detail-info bg-teal m-0 w-200px">Nama Ekspedisi : </label>
                            <div class="d-flex child-no-radius w-100" style="gap: 15px">
                                <select class="form-control" id="cbNamaEks_modal_ekspedisi" style="flex: 1">
                                </select>
                                <select class="form-control" id="cbEks_modal_ekspedisi" style="flex: 1">
                                </select>
                                <input type="text" class="form-control" style="flex: 1" id="txtNama_modal_ekspedisi">
                            </div>
                        </div>
                        <div class="form-group d-flex align-items-center validasi-struk-form-group child-no-radius m-0">
                            <label for="kgberat_modal_ekspedisi" id="lblJarak_modal_ekspedisi" class="detail-info bg-teal m-0 w-200px">Jarak : </label>
                            <input type="text" class="form-control" id="kgberat_modal_ekspedisi" style="width: 275px">
                            <input type="text" class="form-control" id="txtHarga_modal_ekspedisi" style="width: 275px">
                        </div>
                        <div class="position-absolute d-flex align-items-center flex-column" id="img_gratis_ongkir_modal_ekspedisi" draggable="false" style="right: 27px; bottom: -11px;">
                            <img src="{{ asset("img\checklist.png") }}" alt="checklist" style="width: 65px">
                            <p style="color: black;"><b>FREE ONGKIR</b></p>
                        </div>
                    </div>
                    <textarea id="rincian_biaya_modal_ekspedisi" rows="7" disabled class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <div class="float-right mt-4 d-flex">
                    <button type="button" class="btn btn-primary btn-lg" id="BtnOK_modal_ekspedisi" style="width: 180px">Confirm</button>
                    <input type="number" class="form-control" style="margin: 0 15px; width: 180px; height: 42px" id="kgberatLama_modal_ekspedisi">
                    <button type="button" class="btn btn-primary btn-lg" style="width: 180px" id="showBtn_modal_ekspedisi">Calculate</button>
                    <button type="button" style="width: 180px; margin-left: 15px" class="btn btn-lg btn-secondary" data-dismiss="modal">CANCEL</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_stk" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">BUKTI SERAH TERIMA KARDUS</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between">
                    <div class="form-group d-flex flex-row" style="gap: 9px;width: 75%;align-items: center;justify-content: center;margin: auto;">
                        <div class="detail-edit-pb detail-info bg-teal" style="width: 200px;height: 38px;">History STK</div>
                        <select class="form-control" id="select_history_stk">
                        </select>
                        <label for="cek_history_stk" class="checkbox-label checkbox-label-sm d-flex align-items-center" style="width: unset!important;z-index: 1000;gap: 10px;height: 38px">
                            <input type="checkbox" id="cek_history_stk" onclick="$(this).val(this.checked ? 1 : 0)" value="0" style="height: 38px;">
                            Check History
                        </label>
                    </div>
                </div>
                <div class="table-responsive position-relative" style="margin-top: 18px;">
                    <table class="table table-center table-striped table-hover datatable-dark-primary w-100" id="tb_bukti_stk">
                        <thead>
                            <tr>
                                <th>No. PB</th>
                                <th>Tgl. PB</th>
                                <th>Kode Member</th>
                                <th>Cetak</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-warning" onclick="actionAdditionalBuktiSerahTerimaKardusPrepCetak();">Cetak</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_pembayaran_va" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">Pembayaran Virtual Account</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between">
                    <div class="d-flex flex-column">
                        <div class="form-group d-flex align-items-center validasi-struk-form-group">
                            <label for="no_pb_modal_pembayaran_va" class="detail-info">No. PB : </label>
                            <input type="text" class="form-control" disabled id="no_pb_modal_pembayaran_va">
                        </div>
                        <div class="form-group d-flex align-items-center validasi-struk-form-group">
                            <label for="tgl_pb_modal_pembayaran_va" class="detail-info">Tgl. PB : </label>
                            <input type="text" class="form-control" disabled id="tgl_pb_modal_pembayaran_va">
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <div class="form-group d-flex align-items-center validasi-struk-form-group">
                            <label for="no_trans_modal_pembayaran_va" class="detail-info">No. Trans : </label>
                            <input type="text" class="form-control" disabled id="no_trans_modal_pembayaran_va">
                        </div>
                        <div class="form-group d-flex align-items-center validasi-struk-form-group">
                            <label for="bank_modal_pembayaran_va" class="detail-info">Bank : </label>
                            <select id="bank_modal_pembayaran_va" class="form-control"></select>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="d-flex flex-row w-100">
                        <div class="d-flex flex-column" style="gap: 15px; width: 340px;">
                            <div class="d-flex justify-content-between" style="color: black; font-weight: bold; font-size: 1.4rem">
                                <p>Total Bayar</p>
                                <p>:</p>
                            </div>
                            <div class="d-flex justify-content-between" style="color: black; font-weight: bold; font-size: 1.4rem">
                                <p>No. Virtual Account</p>
                                <p>:</p>
                            </div>
                            <div class="d-flex justify-content-between" style="color: black; font-weight: bold; font-size: 1.4rem">
                                <p>Status</p>
                                <p>:</p>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-center" style="width: 100%; gap: 15px">
                            <div style="height: 50px">
                                <h3 style="color: black; font-weight: bold">Rp. <span id="ammount_modal_pembayaran_va">0</span></h3>
                            </div>
                            <div style="height: 50px">
                                <h3 style="color: black; font-weight: bold"><span id="no_va_modal_pembayaran_va">XXXX</span></h3>
                            </div>
                            <div style="height: 50px">
                                <h3 style="color: black; font-weight: bold"><span id="status_modal_pembayaran_va">Pembayaran Belum Diterima</span></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-info d-none mx-2" onclick="actionAdditionalCekPaymentChangeStatus()" id="btn_refresh_modal_pembayaran_va">Refresh</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-warning" onclick="actionAdditionalCreatePaymentChange()" id="btn_proses_modal_pembayaran_va">Cetak</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_periode_pesanan" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">INPUT PRIODE PESANAN</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="margin: auto">
                <div class="d-flex justify-content-between">
                    <div class="d-flex flex-column">
                        <div class="form-group d-flex align-items-center validasi-struk-form-group">
                            <label for="date_awal_modal_periode_pesanan" class="detail-info">Priode : </label>
                            <input type="date" class="form-control" id="date_awal_modal_periode_pesanan">
                            <label for="date_akhir_modal_periode_pesanan" class="detail-info" style="margin-left: 15px; width: 95px">S/D : </label>
                            <input type="date" class="form-control" id="date_akhir_modal_periode_pesanan">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-warning" onclick="actionAdditionalPesananExpired();">Cetak</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_list_pb_batal" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600" id="modal_list_pb_batal_title">LIST PB BATAL</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive position-relative" style="margin-top: 18px;">
                    <table class="table table-center table-striped table-hover datatable-dark-primary w-100" id="modal_list_pb_batal_tb">
                        <thead>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_master_data" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600" id="modal_master_data_title">Listing Delivery</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" hidden id="modal_master_data_flag_mode">
                <div class="d-flex child-no-radius" style="gap: 20px">
                    <div class="child-no-radius" style="display: flex;width: 100%;">
                        <div class="detail-info bg-teal" style="white-space: nowrap;flex-shrink: 0;" id="modal_master_data_label">Nama Driver</div>
                        <input type="text" class="form-control" id="modal_master_data_input" oninput="$(this).val(onKeyUpUpperCase(this.value))">
                    </div>
                    <button class="btn btn-success" style="width: 150px;flex-shrink: 0" onclick="actionAdditionalAddMasterData()">ADD</button>
                </div>
                <div class="table-responsive position-relative" style="margin-top: 18px;">
                    <table class="table table-center table-striped table-hover datatable-dark-primary w-100" id="modal_master_data_tb">
                        <thead>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="detail-info bg-danger text-light mt-3" style="font-weight: bold; width: 300px; height: 42px">*DELETE : untuk menghapus data</div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_ba_pengembalian_dana" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">BA Pengembalian Dana SPI</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex child-no-radius" style="gap: 20px; width: 520px; margin: auto">
                    <div class="child-no-radius" style="display: flex;width: 100%;">
                        <div class="detail-info bg-teal" style="white-space: nowrap;flex-shrink: 0;">History BA</div>
                        <select id="modal_ba_pengembalian_dana_select" class="form-control">

                        </select>
                    </div>
                    <label for="modal_ba_pengembalian_dana_checkbox" class="checkbox-label checkbox-label-sm d-flex align-items-center bg-teal">
                        <input type="checkbox" id="modal_ba_pengembalian_dana_checkbox" onclick="$(this).val(this.checked ? 1 : 0)" value="0">
                        Enable History
                    </label>
                </div>
                <div class="table-responsive position-relative" style="margin-top: 18px;">
                    <table class="table table-center table-striped table-hover datatable-dark-primary w-100" id="modal_ba_pengembalian_dana_tb">
                        <thead>
                            <tr>
                                <th>Tipe Bayar</th>
                                <th>No. PB</th>
                                <th>Tgl. PB</th>
                                <th>Kode Member</th>
                                <th>Total</th>
                                <th>BA</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary mr-3" data-dismiss="modal">Close</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-primary" onclick="actionAdditionalBAPengembalianDanaPrepCetak()">CETAK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_listing_delivery" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">LISTING DELIVERY</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex child-no-radius flex-column" style="gap: 10px; width: 520px; margin: auto">
                    <input type="text" hidden id="modal_listing_delivery_history" value="0">
                    <input type="text" hidden id="modal_listing_delivery_nolist" value="0">
                    <input type="text" hidden id="modal_listing_delivery_tgllist" value="0">
                    <div class="child-no-radius" style="display: flex;width: 100%;">
                        <div class="detail-info bg-teal" style="width: 170px;white-space: nowrap;flex-shrink: 0;">No. PB</div>
                        <input type="text" id="modal_listing_delivery_nopb" class="form-control" readonly>
                    </div>
                    <div class="child-no-radius" style="display: flex;width: 100%;">
                        <div class="detail-info bg-teal" style="width: 170px;white-space: nowrap;flex-shrink: 0;">No. Pol Mobil</div>
                        <input type="text" id="modal_listing_delivery_nopol" class="form-control">
                    </div>
                    <div class="child-no-radius" style="display: flex;width: 100%;">
                        <div class="detail-info bg-teal" style="width: 170px;white-space: nowrap;flex-shrink: 0;">Nama Driver</div>
                        <input type="text" id="modal_listing_delivery_driver" class="form-control">
                    </div>
                    <div class="child-no-radius" style="display: flex;width: 100%;">
                        <div class="detail-info bg-teal" style="width: 170px;white-space: nowrap;flex-shrink: 0;">Nama Deliveryman</div>
                        <input type="text" id="modal_listing_delivery_deliveryman" class="form-control">
                    </div>
                </div>
                <div class="table-responsive position-relative" style="margin-top: 18px;">
                    <table class="table table-center table-striped table-hover datatable-dark-primary w-100" id="modal_listing_delivery_tb">
                        <thead>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary mr-3" data-dismiss="modal">Close</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-primary" onclick="actionAdditionalListingDeliveryPrepCetak()">CETAK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_re_create_awb" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">RE-CREATE AWB</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive position-relative" style="margin-top: 18px;">
                    <table class="table table-center table-striped table-hover datatable-dark-primary w-100" id="modal_re_create_awb_tb">
                        <thead>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary mr-3" data-dismiss="modal">Close</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-primary" onclick="actionAdditionalReCreateAWBProses()">PROSES</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_sorting_lopp" status="" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border: 0; background: #5f3e69 !important">
            <div class="modal-body" style="border: 1px solid white; margin: 8px; padding: 15px 19px;">
                <div class="form-group d-flex align-items-center justify-content-center m-0" style="gap: 20px">
                    <label for="modal_sorting_lopp_sortby1" style="white-space: nowrap; margin: 0; font-weight: bold; color: white">Sort By : </label>
                    <div class="d-flex" style="width: 530px; gap: 15px">
                        <select id="modal_sorting_lopp_sortby1" class="form-control">
                            <option value="Kode Member" selected>Kode Member</option>
                            <option value="No PB">No PB</option>
                            <option value="Tanggal DSP">Tanggal DSP</option>
                            <option value="Nilai DSP">Nilai DSP</option>
                        </select>
                        <select id="modal_sorting_lopp_sortby2" class="form-control">
                            <option value="ASC">ASC</option>
                            <option value="DESC">DESC</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" style="box-shadow: unset!important; width: 160px; font-weight: bold" onclick="actionAdditionalLoppCodCetak()">CETAK</button>
                    <button class="btn btn-secondary" data-dismiss="modal" style="box-shadow: unset!important; width: 160px; font-weight: bold">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_ba_barang_rusak" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">BA Barang Rusak</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="height: calc(100vh - 140px)!important; overflow: hidden">
                <ul class="nav nav-tabs" id="tab_ba_barang_rusak" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="input-ba-rk-tab" data-toggle="tab" data-target="#input-ba-rk" type="button" role="tab" aria-controls="input-ba-rk" aria-selected="true">Input BA-RK</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="draft-ba-rk-tab" data-toggle="tab" data-target="#draft-ba-rk" type="button" role="tab" aria-controls="draft-ba-rk" aria-selected="false">Draft BA-RK</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="history-ba-rk-tab" data-toggle="tab" data-target="#history-ba-rk" type="button" role="tab" aria-controls="history-ba-rk" aria-selected="false">History BA-RK</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent" style="height: 100%">
                    <div class="tab-pane fade show active" id="input-ba-rk" role="tabpanel" aria-labelledby="input-ba-rk-tab" style="height: 100%; padding-bottom: 41px;">
                        <div class="p-3" style="margin-top: -2px; z-index: 25; position: relative; border: 2px solid #d3d3d3; padding-bottom: 7px!important; height: 100%">
                            <div class="d-flex flex-column">
                                <div class="form-group d-flex align-items-center child-no-radius" style="width: 400px">
                                    <label for="no_pb_ba_barang_rusak_tab1" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">No PB : </label>
                                    <input type="text" class="form-control" disabled id="no_pb_ba_barang_rusak_tab1">
                                </div>
                                <div class="form-group d-flex align-items-center child-no-radius" style="width: 100%">
                                    <label for="kode_member_ba_barang_rusak_tab1" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Member : </label>
                                    <div class="d-flex w-100 child-no-radius" style="gap: 18px; width: 100%">
                                        <input type="text" class="form-control" disabled id="kode_member_ba_barang_rusak_tab1" style="width: 150px">
                                        <input type="text" class="form-control" disabled id="nama_member_ba_barang_rusak_tab1" style="width: 100%">
                                    </div>
                                </div>
                                <div class="form-group d-flex align-items-start child-no-radius" style="width: 100%">
                                    <label for="no_pb_ba_barang_rusak_tab1" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Alasan : </label>
                                    <textarea id="alasan_ba_barang_rusak_tab1" disabled class="form-control" style="height: 70px; resize: none"></textarea>
                                </div>
                            </div>
                            <div class="table-responsive position-relative">
                                <table class="table table-striped table-hover datatable-dark-primary w-100 table-center tb-ba-rusak" id="tb_ba_barang_rusak_tab1">
                                    <thead>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex mt-2 p-3 justify-content-center" style="gap: 25px">
                                <button class="btn btn-primary btn-lg w-150px" style="height: 42px" id="ba_barang_rusak_hitung_ulang" onclick="hitungUlangBaRusakKemasan()">Hitung Ulang</button>
                                <button class="btn btn-success btn-lg w-150px" style="height: 42px" id="ba_barang_rusak_simpan" onclick="simpanBaRusakKemasan()">Simpan</button>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="draft-ba-rk" role="tabpanel" aria-labelledby="draft-ba-rk-tab" style="height: 100%; padding-bottom: 41px;">
                        <div class="p-3" style="margin-top: -2px; z-index: 25; position: relative; border: 2px solid #d3d3d3; padding-bottom: 7px!important; height: 100%">
                            <div class="d-flex flex-column">
                                <div class="form-group d-flex align-items-center child-no-radius" style="width: 400px">
                                    <label for="no_pb_ba_barang_rusak_tab2" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">No PB : </label>
                                    <input type="text" hidden id="no_ba_barang_rusak_tab2">
                                    <input type="text" class="form-control" disabled id="no_pb_ba_barang_rusak_tab2">
                                </div>
                                <div class="form-group d-flex align-items-center child-no-radius" style="width: 100%">
                                    <label for="kode_member_ba_barang_rusak_tab2" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Member : </label>
                                    <div class="d-flex w-100 child-no-radius" style="gap: 18px; width: 100%">
                                        <input type="text" class="form-control" disabled id="kode_member_ba_barang_rusak_tab2" style="width: 150px">
                                        <input type="text" class="form-control" disabled id="nama_member_ba_barang_rusak_tab2" style="width: 100%">
                                    </div>
                                </div>
                                <div class="form-group d-flex align-items-start child-no-radius" style="width: 100%">
                                    <label for="no_pb_ba_barang_rusak_tab2" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Alasan : </label>
                                    <textarea id="alasan_ba_barang_rusak_tab2" disabled class="form-control" style="height: 70px; resize: none"></textarea>
                                </div>
                            </div>
                            <div class="table-responsive position-relative">
                                <table class="table table-striped table-hover datatable-dark-primary w-100 table-center tb-ba-rusak" id="tb_ba_barang_rusak_tab2">
                                    <thead>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex mt-2 p-3 justify-content-center" style="gap: 25px">
                                <button class="btn btn-success btn-lg w-150px" style="height: 42px" id="ba_barang_rusak_approve" onclick="approveBaRusakKemasanPrep()">Approve</button>
                                <button class="btn btn-danger btn-lg w-150px" style="height: 42px" id="ba_barang_rusak_batal" onclick="batalBaRusakKemasan()">Batal</button>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="history-ba-rk" role="tabpanel" aria-labelledby="history-ba-rk-tab" style="height: 100%; padding-bottom: 41px;">
                        <div class="p-3" style="margin-top: -2px; z-index: 25; position: relative; border: 2px solid #d3d3d3; padding-bottom: 7px!important; height: 100%">
                            <div class="d-flex flex-column">
                                <div class="form-group d-flex align-items-center child-no-radius" style="width: 400px">
                                    <label for="status_ba_barang_rusak_tab3" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Status BA : </label>
                                    <input type="text" class="form-control" disabled id="status_ba_barang_rusak_tab3">
                                </div>
                                <div class="form-group d-flex align-items-center child-no-radius" style="width: 400px">
                                    <label for="no_pb_ba_barang_rusak_tab3" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">No PB : </label>
                                    <input type="text" class="form-control" disabled id="no_pb_ba_barang_rusak_tab3">
                                </div>
                                <div class="form-group d-flex align-items-center child-no-radius" style="width: 100%">
                                    <label for="kode_member_ba_barang_rusak_tab3" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Member : </label>
                                    <div class="d-flex w-100 child-no-radius" style="gap: 18px; width: 100%">
                                        <input type="text" class="form-control" disabled id="kode_member_ba_barang_rusak_tab3" style="width: 150px">
                                        <input type="text" class="form-control" disabled id="nama_member_ba_barang_rusak_tab3" style="width: 100%">
                                    </div>
                                </div>
                                <div class="form-group d-flex align-items-start child-no-radius" style="width: 100%">
                                    <label for="no_pb_ba_barang_rusak_tab1" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Alasan : </label>
                                    <textarea id="alasan_ba_barang_rusak_tab3" disabled class="form-control" style="height: 70px; resize: none"></textarea>
                                </div>
                            </div>
                            <div class="table-responsive position-relative">
                                <table class="table table-striped table-hover datatable-dark-primary w-100 table-center tb-ba-rusak" id="tb_ba_barang_rusak_tab3">
                                    <thead>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex mt-2 p-3 justify-content-center" style="gap: 25px">
                                <button class="btn btn-success btn-lg w-150px" style="height: 42px" id="ba_barang_rusak_reprint">Reprint</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_master_picking" data-status="false" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600" id="modal_title_master_picking">MASTER PICKING KLIK INDOGROSIR</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-5">
                        <div class="p-4 position-relative" style="border: 2px solid lightgray">
                            <p style="background: white; position: absolute;top: -16px;left: 15px;font-size: 1.2rem;padding: 0 7px;color: #012970;font-weight: 500;">LIST RAK YANG BELUM DISET</p>
                            <div class="form-group d-flex align-items-center child-no-radius" style="width: 100%">
                                <label for="modal_master_picking_kode_rak" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Kode Rak : </label>
                                <select id="modal_master_picking_kode_rak" class="form-control"></select>
                            </div>
                            <label for="modal_master_picking_pick_all" class="checkbox-label checkbox-label-sm d-flex align-items-center bg-teal">
                                <input type="checkbox" id="modal_master_picking_pick_all" onclick="$(this).val(this.checked ? 1 : 0)" value="0">
                                Pick ALL
                            </label>

                            <div class="table-responsive position-relative" style="margin-top: 1rem">
                                <table class="table table-striped table-hover datatable-dark-primary w-100" id="modal_master_picking_tb1">
                                    <thead>
                                        <tr>
                                            <th>Kode Rak</th>
                                            <th>Sub Rak</th>
                                            <th>Pick</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <button class="btn btn-lg btn-primary d-none" id="loading_datatable_modal_master_picking_tb1" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                        Loading...
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-2 flex-column d-flex align-items-center justify-content-center" style="gap: 60px">
                        <button class="btn btn-lg btn-success" style="width: 130px" onclick="actionAdditionalMasterPickingSimpan()">Simpan >></button>                        
                        <button class="btn btn-lg btn-danger" style="width: 130px" onclick="actionAdditionalMasterPickingHapus()"><< Hapus</button>                        
                    </div>
                    <div class="col-5">
                        <div class="p-4 position-relative" style="border: 2px solid lightgray">
                            <p style="background: white; position: absolute;top: -16px;left: 15px;font-size: 1.2rem;padding: 0 7px;color: #012970;font-weight: 500;">LIST RAK PICKING</p>
                            <div class="form-group d-flex align-items-center child-no-radius" style="width: 100%">
                                <label for="modal_master_picking_group" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Group : </label>
                                <select id="modal_master_picking_group" class="form-control"></select>
                                <button class="btn btn-success" onclick="actionAdditionalMasterPickingAddGroup()" style="height: 38px;margin-left: 15px;width: 65px;font-weight: 800;font-size: 1.9rem;display: flex;align-items: center;justify-content: center;padding-bottom: 11px;">+</button>
                            </div>
                            <div class="form-group d-flex align-items-center child-no-radius" style="width: 100%">
                                <label for="modal_master_picking_users" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">User ID : </label>
                                <select id="modal_master_picking_users" class="form-control"></select>
                            </div>

                            <div class="table-responsive position-relative" style="margin-top: 1rem">
                                <div class="bg-teal" style="color: white;font-weight: 700;font-size: 1rem; margin-bottom: -6px;height: 32px;display: flex;align-items: center;justify-content: center;">List Rak</div>
                                <table class="table table-striped table-hover datatable-dark-primary w-100" id="modal_master_picking_tb2">
                                    <thead>
                                        <tr>
                                            <th style="border-top: 0">Kode Rak</th>
                                            <th style="border-top: 0">Sub Rak</th>
                                            <th style="border-top: 0">Pick</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <button class="btn btn-lg btn-primary d-none" id="loading_datatable_modal_master_picking_tb2" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                        Loading...
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_master_group_picking" data-status="false" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600" id="modal_title_master_group_picking">MASTER GROUP PICKING KLIKINDOGROSIR</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-5">
                        <div class="p-4 position-relative" style="border: 2px solid lightgray">
                            <div class="form-group d-flex align-items-center child-no-radius" style="width: 100%">
                                <label for="modal_master_group_picking_input" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Input Grup : </label>
                                <select id="modal_master_group_picking_input" class="form-control"></select>
                            </div>
                            <div class="table-responsive position-relative" style="margin-top: 1rem">
                                <table class="table table-striped table-hover datatable-dark-primary w-100" id="modal_master_picking_group_tb1">
                                    <thead>
                                        <tr>
                                            <th>Kode Rak</th>
                                            <th>Sub Rak</th>
                                            <th>Pick</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <button class="btn btn-lg btn-primary d-none" id="loading_datatable_modal_master_picking_group_tb1" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                        Loading...
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-2 flex-column d-flex align-items-center justify-content-center" style="gap: 60px">
                        <button class="btn btn-lg btn-success" style="width: 130px" onclick="actionAdditionalMasterPickingGroupSimpan()">Simpan >></button>                        
                        <button class="btn btn-lg btn-danger" style="width: 130px" onclick="actionAdditionalMasterPickingGroupHapus()"><< Hapus</button>                        
                    </div>
                    <div class="col-5">
                        <div class="p-4 position-relative" style="border: 2px solid lightgray">
                            <div class="form-group d-flex align-items-center child-no-radius" style="width: 100%">
                                <label for="modal_master_group_picking_grup" class="detail-info text-nowrap bg-teal h-38px w-150px flex-shrink-0">Filter Grup : </label>
                                <select id="modal_master_group_picking_grup" class="form-control"></select>
                            </div>

                            <div class="table-responsive position-relative" style="margin-top: 1rem">
                                <div class="bg-teal" style="color: white;font-weight: 700;font-size: 1rem; margin-bottom: -6px;height: 32px;display: flex;align-items: center;justify-content: center;">List Rak</div>
                                <table class="table table-striped table-hover datatable-dark-primary w-100" id="modal_master_picking_group_tb2">
                                    <thead>
                                        <tr>
                                            <th style="border-top: 0">Kode Rak</th>
                                            <th style="border-top: 0">Sub Rak</th>
                                            <th style="border-top: 0">Pick</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <button class="btn btn-lg btn-primary d-none" id="loading_datatable_modal_master_picking_group_tb2" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                                        <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                        Loading...
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
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

<div class="modal fade" role="dialog" id="modal_loading_send_hh" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-body pt-0" style="background-color: #F5F7F9; border-radius: 6px;">
            <div class="text-center">
                <img style="border-radius: 4px; height: 140px;" src="{{ asset('img/loader_1.gif') }}" draggable="false" alt="Loading">
                <h6 style="position: absolute; bottom: 10%; left: 31%;" class="pb-2">Send HandHelt Otomatis...</h6>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_approval" status="" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border: 0; background: #2f4f4f!important">
            <div class="modal-body" style="border: 1px solid white; margin: 8px; padding: 15px 19px;">
                <div class="form-group d-flex align-items-center justify-content-center m-0 flex-column" style="gap: 20px">
                    <h2 style="white-space: nowrap; margin: 0; font-weight: bold; color: white">Approval Requirement Level </h2>
                    <h5 style="white-space: nowrap; margin: 0; font-weight: bold; color: white" id="label_username_approval"></h5>
                    <div style="width: 80%">
                        <input type="number" hidden id="userlevel_approval">
                        <input type="text" hidden id="keterangan_approval">
                        <div class="form-group w-100 d-flex" style="gap: 20px; margin-bottom: 23px!important">
                            <label for="username_approval" class="detail-info bg-teal text-nowrap px-4">Username :</label>
                            <input type="text" class="form-control" style="border: unset;" id="username_approval">
                        </div>
                        <div class="form-group w-100 d-flex" style="gap: 20px; margin-bottom: 23px!important">
                            <label for="password_approval" class="detail-info bg-teal text-nowrap px-4">Password :</label>
                            <input type="password" class="form-control" style="border: unset;" id="password_approval">
                        </div>
                        <div class="d-flex align-items-center justify-content-center" style="gap: 20px">
                            <button class="btn btn-primary" style="box-shadow: unset!important; width: 160px; height: 40px; font-weight: bold" onclick="actionAdditionalApproval()">OK</button>
                            <button class="btn btn-secondary" style="box-shadow: unset!important; width: 160px; height: 40px; font-weight: bold" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-script')
<script src="{{ asset("js/project/klikIGRKeyDown.js") }}"></script>
<script src="{{ asset("js/project/klikIGRButton.js") }}"></script>
<script>
    var timerRefresh;
    var isFunctionRunning = false;
    var flagSendHH_Tick = false;
    let listPLUMasalah = [];
    let statusSiapPicking = @json($statusSiapPicking ?? false);
    let statusSiapPacking = @json($statusSiapPacking ?? false);

    $(document).ready(function() {
        setDateNow("#tanggal_trans");

        let check_error = @json($check_error ?? false);
        if(check_error){
            $("#modal_loading").addClass("d-none");
            Swal.fire({
                title: 'Peringatan...!',
                text: `${check_error}`,
                icon: 'warning',
                showConfirmButton: true,
                allowOutsideClick: false,
                confirmButtonText: 'Kembali Ke Home',
                preConfirm: () => {
                    // Perform the redirection without closing the SweetAlert dialog
                    window.location.href = '/home';
                    return false; // Prevent SweetAlert from automatically closing
                }
            });
        } else {
            initializeDatatablesMain();
            actionPbBatal();
            TimerSendHHKlik_Tick();
        }

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

        $('#tb tbody').on('click', '.btn-detail', function() {
            detailTransaksi($(this));
        });
        
        $(document).keydown(function(event) {
            if ((event.key.startsWith("F") && !isNaN(event.key.substring(1))) || event.key === "Delete") {
                event.preventDefault();
                if($('#modal_master_data').hasClass('show') && event.key === "Delete"){
                    if($("#modal_master_data_tb tbody tr").hasClass("select-r")){
                        actionAdditionalRemoveMasterData();
                    } else {
                        return;
                    }
                }

                if (isFunctionRunning || isModalShowing()) {
                    return;
                }
                if (event.key !== "F8" && tb.row(".select-r").data() === undefined) {
                    Swal.fire('Peringatan!', 'Pilih Data Terlebih Dahulu..!', 'warning');
                    return;
                }
                var functionName = "action_" + event.key.toLowerCase();
                if (["F4", "F5", "F6", "F7", "F12", "Delete"].includes(event.key.toLowerCase())) {
                    if (typeof window[functionName] === 'function' && tb.row(".select-r").data().no_trans !== null && tb.row(".select-r").data().no_trans !== '') {
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

    $(".btn-action").on('click', function(event) {
        if (isFunctionRunning || isModalShowing()) {
            return;
        }
        
        //List Button Action Tidak Perlu Pilih Data
        if (tb.row(".select-r").data() === undefined && !["BuktiSerahTerimaKardus", "LaporanPesananExpired", "LaporanPenyusutanHarian", "CetakFormPengembalianBarang", "CetakSuratJalan", "PbBatal", "ItemPickingBelumTransit", "LoppCod", "ListPBLebihDariMaxSerahTerima", "ReCreateAWB", "MasterAlasanbatalKirim", "MasterPickingHH", "BAPengembalianDana"].includes($(this).attr("actionName"))) {
            Swal.fire('Peringatan!', 'Pilih Data Terlebih Dahulu..!', 'warning');
            return;
        }
        
        //List Button Action Tidak Perlu No Trans
        if (!["BuktiSerahTerimaKardus", "LaporanPesananExpired", "LaporanPenyusutanHarian", "CetakFormPengembalianBarang", "PbBatal", "ItemPickingBelumTransit", "LoppCod", "ListPBLebihDariMaxSerahTerima", "ReCreateAWB", "ListingDelivery", "MasterAlasanbatalKirim", "MasterPickingHH", "BaRusakKemasan", "BAPengembalianDana"].includes($(this).attr("actionName"))) {
            if( tb.row(".select-r").data().no_trans == null && tb.row(".select-r").data().no_trans == ''){
                Swal.fire('Peringatan!', 'Data Tidak Memiliki No. Trans..!', 'warning');
                return;
            }
        }
        var functionName = "action" + $(this).attr("actionName");
        if (typeof window[functionName] === 'function') {
            isFunctionRunning = true; 
            window[functionName]();
            setTimeout(function() {
                isFunctionRunning = false;
            }, 300);
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

    $(document).on('click', function(event) {
        if (!$(event.target).closest('#modal_master_data_tb tbody tr').length && $('#modal_master_data_tb tbody tr').hasClass('select-r')) {
            $('#modal_master_data_tb tbody tr').removeClass('select-r');
        }

    });
});

async function connectToWebService(url, method, data = null) {

    try {
        const response = await $.ajax({
            url: currentURL + `/connect`,
            type: "POST",
            data: {
                url: url,
                method: method,
                data: data
            }
        });

        const jsonResponse = JSON.parse(response);

        if (jsonResponse.response_code === 200) {
            return jsonResponse.data;
        } else {
            Swal.fire("Peringatan!", "Api Error");
            return null;
        }
    } catch (error) {
        console.error(error);
        return null;
    }
}

function actionGlobalDownloadZip(storagePath, zipName = 'File.zip'){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/download-zip`,
        type: "POST",
        data: {storagePath: storagePath, zipName: zipName},
        xhrFields: {
            responseType: 'blob' // Important for binary data
        },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            var blob = new Blob([response]);
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = zipName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }, 
        error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionGlobalDownloadPdf(fileName){
    $('#modal_loading').modal('show');
    $.ajax({
        url: currentURL + `/action/download-pdf`,
        type: "POST",
        data: {fileName: fileName},
        xhrFields: {
            responseType: 'blob' // Important for binary data
        },
        success: function(response) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            var blob = new Blob([response], { type: 'application/pdf' }); // Corrected line
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }, 
        error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function initializeDatatablesMain(){
    tb = $('#tb').DataTable({
        processing: true,
        ajax: {
            url: currentURL + `/datatables/${$("#tanggal_trans").val()}/${statusSiapPicking}/${statusSiapPacking}`,
            type: 'GET'
        },
        columnDefs: [
            { className: 'text-center', targets: '_all' },
        ],
        order: [],
        "paging": false,
        "searching": false,
        "scrollY": "calc(100vh - 440px)",
        "scrollCollapse": true,
        scrollX: true,
        ordering: false,
        columns: [
            { data: 'no' },
            { data: 'status' },
            { data: 'kode_member' },
            { data: 'tipe_member' },
            { data: 'no_pb' },
            { data: 'no_trans' },
            { data: 'no_po' },
            { data: 'ongkir' },
            { data: 'tipe_bayar' },
            { data: 'service' },
            { data: 'TGL & JAM PB' },
            { data: 'MAX SERAH TERIMA' },
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
                $("#label_no_trans").text(tb.row(this).data().no_trans);
            });
        },
    });
}

$("#auto_refresh").change(function(){
    if ($(this).is(':checked')) {
        timerRefresh = setInterval(TimerRefresh_Tick, 60000); 
    }else {
        clearInterval(timerRefresh);
    }
});

function TimerRefresh_Tick(){
    tb.ajax.reload();
    actionPbBatal();
}

$("#auto_send_hh").change(function(){
    if ($(this).is(':checked')) {
        if(!flagSendHH_Tick && !isFunctionRunning){
            timerRefresh = setInterval(TimerSendHHKlik_Tick, 180000); 
        }
    }else {
        clearInterval(timerRefresh);
    }
});

$("#button_proses").click(function(){
    if ($("#path_zip").val() === '') {
        Swal.fire('Peringatan!', 'File Path Masih Kosong..!', 'warning');
        return;
    }

    Swal.fire({
        title: 'Yakin?',
        html: `Proses file CSV ${$("#path_zip")[0].files[0].name} ?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            var formData = new FormData();
            formData.append('zipFile', $("#path_zip")[0].files[0]);
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/proses-main`,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    Swal.fire('Success!', response.message,'success');
                    $('#path_zip').val('');
                    tb.ajax.reload();
                    actionPbBatal();
                }, error: function(jqXHR, textStatus, errorThrown) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    Swal.fire({
                        text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                            ? jqXHR.responseJSON.message
                            : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                        icon: "error"
                    });
                }
            });
        }
    })
});

function TimerSendHHKlik_Tick(){
    flagSendHH_Tick = true;
    listPLUBermasalah = [];
    $('#modal_loading_send_hh').modal('show');
    $.ajax({
        url: currentURL + `/action/SendHH-Tick`,
        type: "POST",
        data: {pickRakToko: $("#pick_rak_toko").val()},
        success: function(response) {
            setTimeout(function () { $('#modal_loading_send_hh').modal('hide'); }, 500);
            flagSendHH_Tick = false;
            listPLUMasalah = response.data.listPLUMasalah;
            if(response.code == 201){
                var blob = new Blob([response.data.content], { type: "text/plain" });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "ERROR_SEND_OTOMATIS.TXT";
                link.click();
                Swal.fire("Peringatan!", "Send HandHelt Otomatis Berhasil! Tetapi terdapat Error", "warning").then(function(){
                    tb.ajax.reload();
                    actionPbBatal()
                })
            } else {
                Swal.fire("Success", "Send HandHelt Otomatis Berhasil!", "success").then(function(){
                    tb.ajax.reload();
                    actionPbBatal()
                })
            }
        }, error: function(jqXHR, textStatus, errorThrown) {
            setTimeout(function () { $('#modal_loading_send_hh').modal('hide'); }, 500);
            flagSendHH_Tick = false;
            Swal.fire({
                text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                    ? jqXHR.responseJSON.message
                    : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                icon: "error"
            });
        }
    });
}

function actionReprintKoli(){
    var koliValue = $("#no_koli_detail_transaksi_tab5").val();
    if(koliValue == ''){
        Swal.fire("Peringatan!", "Harap Pilih No. Koli Terlebih Dahulu", "warning");
        return;
    }
    Swal.fire({
        title: 'Yakin?',
        html: `Reprint Koli ${koliValue}?`,
        icon: 'info',
        showCancelButton: true,
    })
    .then((result) => {
        if (result.value) {
            var checker_value = $("#no_koli_detail_transaksi_tab5").find(`option[value="${koliValue}"]`).data('checker');
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/actionReprintKoli`,
                type: "POST",
                data: {no_trans: $("#no_trans_detail_transaksi_tab5").val(), tgl_trans: $("#tgl_trans_detail_transaksi_tab5").val(), no_koli: koliValue, checker_id: checker_value},
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    Swal.fire("Success!", response.message, 'success');
                }, error: function(jqXHR, textStatus, errorThrown) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    Swal.fire({
                        text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                            ? jqXHR.responseJSON.message
                            : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                        icon: "error"
                    });
                }
            });
        }
    })
}

var originalDataPLU = [];

$(document).on('change', '#cek_item_bermasalah', function() {
    var isChecked = $(this).is(':checked');

    if (originalDataPLU.length === 0) {
        originalDataPLU = tb_edit_pb.rows().data().toArray();
    }

    if (isChecked) {
        var filteredRows = originalDataPLU.filter(function(data) {
            return listPLUMasalah.includes(data.plu);
        });

        tb_edit_pb.clear().rows.add(filteredRows).draw();
    } else {
        tb_edit_pb.clear().rows.add(originalDataPLU).draw();
    }
});

function actionSendHandheld(DonePilihJalurPicking = false){
    var selectedRow = tb.row(".select-r").data();
    var flagSPI = @json($flagSPI ?? false);
    if(DonePilihJalurPicking || flagSPI){
        $('#modal_loading').modal('show');
        $('input[name="input_jalur_picking"]:checked').val();
        $("#modal_pilih_jalur_picking").modal("hide");
        if(flagSPI){
            var pilihan = 2;
        } else {
            var pilihan = $('input[name="input_jalur_picking"]:checked').val();
        }
        $.ajax({
            url: currentURL + `/action/SendHandHelt`,
            type: "POST",
            data: {no_trans: selectedRow.no_trans, status: selectedRow.status, statusSiapPicking: statusSiapPicking, pilihan: pilihan, nopb: selectedRow.no_pb, tanggal_pb: selectedRow.tgl_pb, kode_member: selectedRow.kode_member, tanggal_trans: $("#tanggal_trans").val(), pickRakToko: $("#pick_rak_toko").val()},
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                Swal.fire('Success!', response.message,'success');
                if(response.data.content !== "noTXT"){
                    var blob = new Blob([response.data.content], { type: "text/plain" });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = response.data.nama_file;
                    link.click();
                }
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                if(jqXHR.responseJSON.code === 401){
                    Swal.fire('Peringatan!', jqXHR.responseJSON.message,'error');
                    var blob = new Blob([jqXHR.responseJSON.data.content], { type: "text/plain" });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = jqXHR.responseJSON.data.nama_file;
                    link.click();
                } else {
                    Swal.fire({
                        text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                        ? jqXHR.responseJSON.message
                        : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                        icon: "error"
                    });
                }
            }
        });
    } else {
        Swal.fire({
            title: 'Yakin?',
            text: "Send Jalur No Trans " + selectedRow.no_trans + " ini ?",
            icon: 'info',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                if(statusSiapPicking !== selectedRow.status){
                    Swal.fire("Peringatan!", "Bukan Data Yang Siap Send Jalur!", "warning");
                    return;
                }
                if($("#tanggal_trans").val() == ''){
                    Swal.fire("Peringatan!", "Pilih Tanggal Trans Terlebih Dahulu", "warning");
                    return;
                }
                $('input[name="input_jalur_picking"]').first().prop("checked", true).trigger('change');
                $("#modal_pilih_jalur_picking").modal("show");
            }
        });
    }
}
</script>
@endpush

