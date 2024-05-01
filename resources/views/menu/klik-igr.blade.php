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
                        <button class="btn btn-orange">Master No. Pol Delivery Van</button>
                        <button class="btn btn-orange">Master Driver</button>
                        <button class="btn btn-orange">Master Deliveryman</button>
                        <button class="btn btn-orange">Master Driver</button>
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
                                <td>PLU</td>
                                <td>Nama Barang</td>
                                <td>Qty Order</td>
                                <td>Qty Real</td>
                                <td>Action</td>
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
<script>
    let tb, tb_list_pb, tb_edit_pb;
    let countPasswordManager = 0;
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
            if (event.key.startsWith("F") && !isNaN(event.key.substring(1)) && event.key !== "F12") {
                event.preventDefault();
                if(event.key !== "F8" && tb.row(".select-r").data() === undefined){
                    Swal.fire('Peringatan!', 'Pilih Data Terlebih Dahulu..!', 'warning');
                    return;
                }
                var functionName = "action_" + event.key.toLowerCase();
                if (typeof window[functionName] === 'function' && tb.row(".select-r").data().no_trans !== null) {
                    window[functionName]();
                }
            }
        });

        // LIST PLU BERMASALAH DIAMBIL DARI MANA ?
        $("#cek_item_bermasalah").on("change", function(){
            if ($(this).is(':checked')) {
                // tb_edit_pb.columns(STATUS_COLUMN_INDEX).search('true').draw();
            }
        });
    });

    function initialize_datatables_detail(data, columnsData, columnsDefsData = []){
        columnsDefsData.push({ className: 'text-center', targets: "_all" });
        tb_detail = $('#tb_detail').DataTable({
            data: data,
            language: {
                emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
            },
            columnDefs: columnsDefsData,
            columns: columnsData,
            ordering: false,
            pageLength: 5,
            lengthChange: false,
            destory: true,
        });
    }

    function draw_tb_edit_pb(selectedRow){
        tb_edit_pb.clear().draw();
        $('.datatable-no-data').css('color', '#F2F2F2');
        $('#loading_datatable_edit_pb').removeClass('d-none');
        $.ajax({
            url: currentURL + "/action/f4",
            type: "POST",
            data: { actionSelected: $("#action_form_pembatalan").val(), no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb},
            success: function(response) {
                $('#loading_datatable_edit_pb').addClass('d-none');
                $('.datatable-no-data').css('color', '#ababab');
                tb_edit_pb.rows.add(response.data).draw();
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function () { $('#loading_datatable_edit_pb').addClass('d-none'); }, 500);
                $('.datatable-no-data').css('color', '#ababab');
                Swal.fire({
                    text: "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
            }
        });
    }

    // START GLOBAL FUNCTION
    function showModalPasswordManager(status, mode = "isManager"){
        var modalElement = $('#modal_password_manager');
        modalElement.modal("show");
        modalElement.attr("status", status);
        modalElement.attr("mode", mode);
        $("#password_manager").val('');
        if(status === 'edit_pb'){
            $("#modal_edit_pb").addClass("brightness-blur");
        }
    }

    function closeModalPasswordManager(){
        var modalElement = $('#modal_password_manager');
        modalElement.modal('hide'); 
        $("#password_manager").val('');
        if(modalElement.attr('status') === 'edit_pb'){
            $("#modal_edit_pb").removeClass("brightness-blur");
        }
        modalElement.attr("status", "");    
        modalElement.attr("mode", "");    
    }

    function showValidasiStruk(){
        var selectedRow = tb.row(".select-r").data();
        $("#modal_validasi_struk").modal("show");
        $("#no_trans_validasi_struk").val(selectedRow.no_trans);
        $("#no_pb_validasi_struk").val(selectedRow.no_pb);
        $("#tanggal_trans_validasi_struk").val(moment(selectedRow.tgltrans, 'DD-MM-YYYY').format('YYYY-MM-DD'));
        $("#member_validasi_struk").val(selectedRow.kode_member);

        //DETAIL
        setDateNow("#tanggal_struk_validasi_struk");
        setTimeNow("#time_struk_validasi_struk");
    }

    function closeValidasiStruk(){
        $("#modal_validasi_struk").modal("hide");
        $("#no_trans_validasi_struk").val("");
        $("#no_pb_validasi_struk").val("");
        $("#tanggal_trans_validasi_struk").val("");
        $("#member_validasi_struk").val("");

        //DETAIL
        $("#no_struk_validasi_struk").val("");
        $("#tanggal_struk_validasi_struk").val("");
        $("#time_struk_validasi_struk").val("");
        $("#station_validasi_struk").val("");
        $("#cashier_id_validasi_struk").val("");
    }
    // END GLOBAL FUNCTION

    // START ADDITIONAL FUNCTION
    function actionAdditionalValidasiRak(rowsData){
        rowsData = rowsData.map(function(row) {
            return { plu: row.plu };
        });

        $('#modal_loading').modal('show');
        $.ajax({
            url: currentURL + `/action/f4-validasi-rak`,
            type: "POST",
            data: {datatables: rowsData, no_trans: tb.row(".select-r").data().no_trans, nopb: tb.row(".select-r").data().no_pb},
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                Swal.fire('Success!', response.message,'success');
                var selectedRow = tb.row(".select-r").data();
                draw_tb_edit_pb(selectedRow);
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

    function actionAdditionalItemBatal(rowsData, directCetak = false){
        $('#modal_loading').modal('show');
        var url_action = directCetak ? "/action/f4-cetak-item-batal" : "/action/f4-item-batal";
        $.ajax({
            url: currentURL + url_action,
            type: "POST",
            data: {datatables: rowsData, no_trans: tb.row(".select-r").data().no_trans, nopb: tb.row(".select-r").data().no_pb,},
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                var swalMessage = directCetak ? "Cetak Item Batal Berhasil" : response.message;
                Swal.fire('Success!', swalMessage,'success');
                var blob = new Blob([response.data.content], { type: "text/plain" });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = response.data.nama_file;
                link.click();
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

    function actionAdditionalPasswordManager(){
        var password_manager = $("#password_manager").val();
        if(password_manager === "" || password_manager === undefined){
            Swal.fire('Peringatan!', 'Harap isi Password Terlebih Dahulu..!', 'warning');
            return;
        }
        $('#modal_loading').modal('show');
        $.ajax({
            url: currentURL + `/password-manager`,
            type: "POST",
            data: { password_manager: $("#password_manager").val(), count: countPasswordManager, mode: $("#modal_password_manager").attr("mode") },
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                Swal.fire('Success!', response.message,'success').then(function(){
                    var status = $("#modal_password_manager").attr('status');
                    if(status === 'edit_pb'){
                        actionF4Proses(true);
                    } else if(status === 'reaktivasi_pb'){
                        action_f5(true);
                    } else if(status === 'validasi_struk'){
                        showValidasiStruk();
                    }
                    closeModalPasswordManager();
                });
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                countPasswordManager += 1;
                if(countPasswordManager >= 3){
                    countPasswordManager = 0;
                    Swal.fire('Peringatan!', jqXHR.responseJSON.message, 'warning').then(function(){
                        closeModalPasswordManager();
                    });
                    return;
                }
                Swal.fire({
                    text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                        ? jqXHR.responseJSON.message
                        : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
            }
        });


    }
    // END ADDITIONAL FUNCTION

    function action_f1(){
        $('#modal_loading').modal('show');
        $.ajax({
            url: currentURL + "/action/f1",
            type: "POST",
            data: {no_trans: tb.row(".select-r").data().no_trans, nopb: tb.row(".select-r").data().no_pb},
            success: function(response) {
                setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
                $("#modal_detail").modal("show");
                $(".detail-title").text("DETIAL PB");
                
                if ($.fn.DataTable.isDataTable('#tb_detail')) {
                    tb_detail.clear().draw();
                    $("#tb_detail").dataTable().fnDestroy();
                    $("#tb_detail thead").empty()
                }

                var newColumns = [
                    { data: 'plu', title: 'PLU' },
                    { data: 'barang', title: 'Barang' },
                    { data: 'jumlah', title: 'Jumlah' },
                    { data: 'harga', title: 'Harga' },
                    { data: 'diskon', title: 'Diskon' },
                    { data: 'subtotal', title: 'Subtotal' },
                    { data: 'tag', title: 'Tag' }
                ];

                response.data = response.data.map(function(item){
                    if(item.jumlah !== null){
                        item.jumlah = parseFloat(item.jumlah).toFixed(0);
                    }
                    return item;
                });

                initialize_datatables_detail(response.data, newColumns);
                
                if(response.data.length > 0){
                    window.open(currentURL + "/action/f1-download-excel", '_blank');
                }

            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
                Swal.fire({
                    text: "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
            }
        });
    }

    function action_f2(){
        $('#modal_loading').modal('show');
        var selectedRow = tb.row(".select-r").data();
        $.ajax({
            url: currentURL + `/action/f2`,
            type: "POST",
            data: {member_igr: selectedRow.kode_member, no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb},
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                $("#modal_detail").modal("show");
                $(".detail-title").text("DETAIL PROMO");

                if ($.fn.DataTable.isDataTable('#tb_detail')) {
                    tb_detail.clear().draw();
                    $("#tb_detail").dataTable().fnDestroy();
                    $("#tb_detail thead").empty()
                }

                var newColumns = [
                    { data: 'KODE PROMO', title: 'KODE PROMO' },
                    { data: 'potongan', title: 'POTONGAN' },
                    { data: 'promo', title: 'PROMO' },
                    { data: 'tipe', title: 'TIPE' },
                ];

                initialize_datatables_detail(response.data, newColumns);

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

    function action_f3(){
        $('#modal_loading').modal('show');
        var selectedRow = tb.row(".select-r").data();
        $.ajax({
            url: currentURL + `/action/f3`,
            type: "POST",
            data: {no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb},
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                $("#modal_detail").modal("show");
                $(".detail-title").text("DETAIL OTV PICKING");

                if ($.fn.DataTable.isDataTable('#tb_detail')) {
                    tb_detail.clear().draw();
                    $("#tb_detail").dataTable().fnDestroy();
                    $("#tb_detail thead").empty()
                }

                response.data = response.data.map(item => ({
                    ...item,
                    qty_order: item.qty_order !== null ? parseFloat(item.qty_order).toFixed(0) : item.qty_order,
                    qty_picking: item.qty_picking !== null ? parseFloat(item.qty_picking).toFixed(0) : item.qty_picking,
                    qty_packing: item.qty_packing !== null ? parseFloat(item.qty_packing).toFixed(0) : item.qty_packing
                }));

                var newColumns = [
                    { data: 'plu', title: 'PLU' },
                    { data: 'deskripsi', title: 'DESKRIPSI' },
                    { data: 'qty_order', title: 'QTY_ORDER' },
                    { data: 'qty_picking', title: 'QTY_PICKING' },
                    { data: 'status_picking', title: 'STATUS_PICKING' },
                    { data: 'group_name', title: 'GROUP' },
                    { data: 'picker', title: 'PICKER' },
                    { data: 'qty_packing', title: 'QTY_PACKING' },
                ];

                var newColumnDefs = [{ className: 'w-40-center', targets: 1 }];

                initialize_datatables_detail(response.data, newColumns, newColumnDefs);

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

    function action_f4(){
        Swal.fire({
            title: 'Yakin?',
            html: `Edit PB/Validasi Rak untuk Item Batal?`,
            icon: 'info',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                var selectedRow = tb.row(".select-r").data();
                $("#modal_edit_pb").modal("show");
                $("#no_pb_detail_edit").text(selectedRow.no_pb);
                $("#tanggal_pb_detail_edit").text(selectedRow.tgl_pb);
                $("#no_trans_detail_edit").text(selectedRow.no_trans);
                if (["Siap Picking", "Set Ongkir", "Siap Draft Struk", statusSiapPacking].includes(selectedRow.status)) {
                    $("#action_form_pembatalan").append(`<option value="ITEM BATAL">Item Batal</option>`);
                }
                draw_tb_edit_pb(selectedRow);
            }
        });
    }

    function actionF4Proses(passPasswordManager = false){
        var swalText, functionName, checkedRowsData = [];
        var isChecked = false;

        $(".checkbox-group").each(function() {
            if ($(this).prop('checked')) {
                checkedRowsData.push(tb_edit_pb.row($(this).closest('tr')).data())
                isChecked = true;
            }
        });
        
        if(isChecked === false){
            Swal.fire('Peringatan!', 'Item Belum Dipilih..!', 'warning');
            return;
        }

        if($("#action_form_pembatalan").val() === "VALIDASI RAK"){
            swalText = `Validasi ${$(".checkbox-group:checked").length} Item yang sudah dikembalikan ke rak ?`;
            functionName = "actionAdditionalValidasiRak";
        } else {
            if(passPasswordManager === false){
                showModalPasswordManager('edit_pb', 'isManager');
                return;
            }
            swalText = `Proses ${$(".checkbox-group:checked").length} Item Batal ?`;
            functionName = "actionAdditionalItemBatal";
        }

        Swal.fire({
            title: 'Yakin?',
            html: swalText,
            icon: 'info',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                if (typeof window[functionName] === 'function') {
                    window[functionName](checkedRowsData);
                }
            }
        });
    }

    function action_f5(passPasswordManager = false){
        var selectedRow = tb.row(".select-r").data();
        if(passPasswordManager === false){
            if(selectedRow.status === "Transaksi Batal" && selectedRow.flagbayar !== "Y"){
                Swal.fire({
                    title: 'Yakin?',
                    html: `Mengaktifkan Kembali Transaksi No.${selectedRow.no_trans} yang sudah batal?`,
                    icon: 'info',
                    showCancelButton: true,
                })
                .then((result) => {
                    if (result.value) {
                        showModalPasswordManager('reaktivasi_pb', 'isManager');
                    }
                });
            } else {
                Swal.fire('Peringatan!', 'Bukan data yang bisa diaktifkan kembali..!', 'warning');
                return;
            }
        } else {
            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/f5`,
                type: "POST",
                data: { status: selectedRow.status, flag_bayar: selectedRow.flagbayar, no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb, tanggal_trans: $("#tanggal_trans").val() },
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    Swal.fire('Success!', response.message,'success');
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
    }

    function action_f6(passPasswordManager = false){
        var selectedRow = tb.row(".select-r").data();
        if(passPasswordManager === false){
            if(selectedRow.status === "Siap Struk"){
                showModalPasswordManager('validasi_struk', 'isOTP');
            } else {
                Swal.fire('Peringatan!', 'Bukan data yang bisa validasi struk..!', 'warning');
                return;
            }
        } else {
            var no_struk = $("#no_struk_validasi_struk").val(),
                tanggal_struk = $("#tanggal_struk_validasi_struk").val(),
                time_struk = $("#time_struk_validasi_struk").val(),
                station = $("#station_validasi_struk").val(),
                cashier = $("#cashier_id_validasi_struk").val();

            if (!no_struk || !tanggal_struk || !time_struk || !station || !cashier) {
                Swal.fire('Peringatan!', 'Input Detail Struk Belum Lengkap..!', 'warning');
                return;
            }

            $('#modal_loading').modal('show');
            $.ajax({
                url: currentURL + `/action/f6`,
                type: "POST",
                data: { no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb, kode_member: selectedRow.kode_member, tanggal_trans: selectedRow.tgltrans, no_struk: no_struk, tanggal_struk: tanggal_struk, time_struk: time_struk, station: station, cashier: cashier, status: selectedRow.status },
                success: function(response) {
                    setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                    Swal.fire('Success!', response.message,'success').then(function(){
                        tb.ajax.reload();
                        closeValidasiStruk();
                    });
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
    }

    function action_f7(){
        var selectedRow = tb.row(".select-r").data();
        if(selectedRow.status !== "Siap Picking" || selectedRow.status !== statusSiapPacking){
            Swal.fire('Peringatan!', 'Bukan data yang dipicking/dipacking..!', 'warning');
            return;
        }
        var data = { status: selectedRow.status, status_siap_packing: statusSiapPacking, tanggal_trans: $("#tanggal_trans").val(), no_trans: selectedRow.no_trans, nopb: selectedRow.no_pb, kode_member: selectedRow.kode_member }
        $('#modal_loading').modal('show');
        $.ajax({
            url: currentURL + `/action/f7`,
            type: "POST",
            data: data,
            success: function(response) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', currentURL + '/action/f7', true);
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                xhr.responseType = 'blob';
                xhr.onload = function() {
                    if (this.status === 200) {
                        setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                        var blob = new Blob([xhr.response], { type: 'application/pdf' }); // Corrected line
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = 'RPT-JALUR-KERTAS.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                };
                xhr.send(data);
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
</script>
@endpush

