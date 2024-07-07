@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">HISTORY PRODUK</h1>
@endsection

@section('css')
<style>
    .btn-lg{
        height: 50px;
        font-size: 1.2rem;
    }

    .checkbox-table{
        vertical-align: middle;
    }

    .periode-text{
        vertical-align: middle;
        margin-left: 11px;
        font-weight: 600;
        color: black;
    }

    .btn-warning, .btn-warning:focus{
        box-shadow: none!important;
        background: #fd980b;
    }

    .btn-warning:hover{
        background: #d17f0e!important;
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
                    <div class="header mb-4">
                        <div class="d-flex w-100" style="gap: 20px">
                            <div class="detail-info w-100" style="height: 44px; margin-bottom: 20px" id="status_text">Status : Pilih Path History Produk!</div>
                            <div class="detail-info w-100" style="height: 44px; margin-bottom: 20px" id="status_text2">* Kosongkan Periode Untuk Menampilkan Semua Data</div>
                        </div>
                        <div class="d-flex" style="gap: 15px;">
                            <div class="form-group d-flex" style="gap: 15px; flex: 5;">
                                <label for="file_input" style="white-space: nowrap; width: 150px" class="detail-info">Pilih Path &nbsp;:&nbsp;</label>
                                <input type="file" class="form-control" id="file_input" webkitdirectory mozdirectory multiple>
                            </div>
                            <div class="form-group d-flex" style="gap: 15px; flex: 2;">
                                <label for="periode" style="white-space: nowrap; width: 150px" class="detail-info">Periode &nbsp;:&nbsp;</label>
                                <input type="month" class="form-control" id="periode" name="periode">
                            </div>
                            <div class="form-group d-flex" style="gap: 15px; flex: 3;">
                                <label for="mode" style="white-space: nowrap; width: 150px; " class="detail-info">Mode &nbsp;:&nbsp;</label>
                                <select name="mode" id="mode" class="form-control form-select">
                                    <option value="KPH MEAN">KPH MEAN</option>
                                    <option value="PRODUK BARU">PRODUK BARU</option>
                                    <option value="PINDAH SUPPLY">PINDAH SUPPLY</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex flex-row child-flex-1 mt-2" style="gap: 15px">
                            <button class="btn btn-lg btn-warning" id="btn_browse_main" onclick="actionBrowse();">Browse</button>
                            <button class="btn btn-lg btn-info" id="btn_proses" onclick="actionProses();" disabled>Proses</button>
                            <button class="btn btn-lg btn-cust-success" id="btn_upload">Upload CSV</button>
                            <button class="btn btn-lg btn-royal" id="btn_hit" onclick="actionHitKPH();">Hit. KPH</button>
                            <button class="btn btn-lg btn-danger" id="btn_reprt" onclick="showModalReport()">Report KPH</button>
                        </div>
                    </div>
                    <div class="table-responsive position-relative">
                        <table class="table table-striped table-hover datatable-dark-primary w-100 table-center" id="tb">
                            <thead>
                                <tr>
                                    <th>Kode Toko</th>
                                    <th>Nama Toko</th>
                                    <th>Status</th>
                                    <th>Periode</th>
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

@section('modal')
<div class="modal fade" role="dialog" id="modal_csv" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600" id="modal_csv_title">UPLOAD FILE CSV</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group d-flex" style="gap: 20px">
                    <label for="tipe_upload" class="detail-info" style="width: 150px">Tipe Upload</label>
                    <select name="tipe_upload" id="tipe_upload" class="form-control">
                    </select>
                </div>
                <div class="form-group d-flex" style="gap: 20px; height: 38px">
                    <label for="tipe_upload" class="detail-info" style="width: 150px">File CSV</label>
                    <input type="file" id="file_input_modal" name="file_input_modal" style="padding: 3px 8px; height: 38px!important" class="form-control">
                </div>
                <div class="form-group m-0 d-flex justify-content-end" style="gap: 25px">
                    <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal">Close</button>
                    {{-- <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-primary" id="btn_ftp">FTP</button> --}}
                    <button type="button" style="min-width: 150px; width: auto; white-space: nowarp; height: 44px" class="btn btn-lg btn-warning" id="btn_browse">BROWSE</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_report" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">PILIH Periode</h5>
                <button type="button" class="close clearButton" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive position-relative">
                    <table class="table table-striped table-hover datatable-dark-primary w-100" id="tb_report">
                        <thead>
                            <tr>
                                <th class="w-100">Periode</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <button class="btn btn-lg btn-primary d-none" id="loading_datatable_report" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                            <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            Loading...
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-secondary" data-dismiss="modal" onclick="$('#file_input_modal').val('')">Close</button>
                <button type="button" style="width: 150px; height: 44px" class="btn btn-lg btn-primary" onclick="actionReportKPH();">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" role="dialog" id="modal_absensi_file" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header br">
                <h5 class="modal-title" style="color: #012970; font-weight: 600">Absensi File History Produk</h5>
            </div>
            <div class="modal-body">
                <div class="table-responsive position-relative">
                    <table class="table table-striped table-hover datatable-dark-primary w-100" id="tb_absensi_file">
                        <thead>
                            <tr>
                                <th>Kode Toko</th>
                                <th>Nama Toko</th>
                                <th>Status File</th>
                                <th>Nama File</th>
                            </tr>
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
@endsection

@push('page-script')
<script>
    let tb, tb_report, tb_absensi_file;
    let kodeIGR = "{{ session('KODECABANG') }}";
    $(document).ready(function(){
        // $("#periode").val(moment().format('YYYY-MM'))   
        tb = $('#tb').DataTable({
            ajax: {
                url: currentURL + "/datatables",
                type: "GET"
            },
            language: {
                emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
            },
            columns: [
                { data: 'kodetk'},
                { data: 'namatk'},
                { data: 'stat', defaultContent: '' },
                { data: 'periode', defaultContent: '' },

            ],
            columnDefs: [
                { className: 'text-center-vh', targets: '_all' },   
            ],
            ordering: false,
        });

        tb_report = $('#tb_report').DataTable({
            processing: true,
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
                { data: 'periode' },
            ],
            rowCallback: function (row, data) {
                $('td:eq(0)', row).html(`<input type="checkbox" class="form-control checkbox-table d-inline checkbox-group" value="${data.periode}" name="periode-checkbox"><span class="periode-text">${data.periode}</span>`);
            }
            
        });

        tb_absensi_file = $('#tb_absensi_file').DataTable({
            processing: true,
            columnDefs: [
                { className: 'text-center', targets: '_all' },
            ],
            order: [],
            "paging": false, 
            "searching": false,
            "scrollY": "calc(100vh - 400px)",
            "scrollCollapse": true,
            ordering: false,
            columns: [
                { data: 'kodeToko' },
                { data: 'namaToko' },
                {
                    data: 'isFound',
                    render: function(data) {
                        return data == 1 ? 'OK' : 'File Tidak Ditemukan';
                    }
                },
                { data: 'filename' },
            ],
            rowCallback: function(row, data) {
                if (data.isFound == 1) {
                    $(row).css({
                        'background-color': 'green',
                        'color': 'white',
                        'font-weight': 'bold'
                    });
                } else {
                    $(row).css({
                        'background-color': '#a11111',
                        'color': 'white',
                        'font-weight': 'bold'
                    });
                }
            }
            
        });

        $("#mode").on("change", function(){
            var currentValue = $(this).val();
            var uploadText, statusText, browseDisabled, hitDisabled, periodeDisabled, periodeValue;

            if (currentValue === "KPH MEAN") {
                uploadText = "Upload CSV";
                statusText = "Status : Pilih Path History Produk!";
                browseDisabled = false;
                hitDisabled = false;
                periodeDisabled = false;
                periodeValue = moment().format('YYYY-MM');
            } else if (currentValue === "PRODUK BARU") {
                uploadText = "KPH Produk Baru";
                statusText = "Status : Tekan Tombol KPH Produk Baru Untuk Upload dan Proses KPH Produk Baru!";
                browseDisabled = true;
                hitDisabled = true;
                periodeDisabled = true;
                periodeValue = '';
            } else {
                uploadText = "Upload CSV";
                statusText = "Status : Pilih Path Item Pindah Supply!";
                browseDisabled = true;
                hitDisabled = true;
                periodeDisabled = true;
                periodeValue = '';
            }

            $("#btn_upload").text(uploadText);
            $("#status_text").text(statusText);
            $("#file_input").attr("disabled", browseDisabled);
            $("#btn_hit").attr("disabled", hitDisabled);
            $("#periode").attr("disabled", periodeDisabled);
            $("#periode").val(periodeValue);

            $("#btn_proses").attr("disabled", false);
        });

        $("#tipe_upload").on("change", function(){
            if($(this).val() == "MINOR"){
                $("#modal_csv_title").text("UPLOAD MINOR TOKO");
            } else {
                $("#modal_csv_title").text("UPLOAD FILE CSV");
            }
        })

        $("#btn_upload").on("click", function(){
            var mode = $("#mode").val();
            $("#modal_csv").modal("show");
            $("#tipe_upload").empty();
            if(mode === "KPH MEAN"){
                $("#btn_ftp").css("display", "block");
                $("#btn_browse").addClass("btn-warning");
                $("#btn_browse").removeClass("btn-info");
                $("#btn_browse").text("BROWSE");
                $("#tipe_upload").append(`<option value="MINOR">MINOR</option>`);
                $("#tipe_upload").append(`<option value="PLUIDM">PLUIDM</option>`);
                $("#modal_csv_title").text("UPLOAD FILE CSV");
            } else if(mode === "PRODUK BARU"){
                $("#btn_ftp").css("display", "none");
                $("#btn_browse").addClass("btn-warning");
                $("#btn_browse").removeClass("btn-info");
                $("#btn_browse").text("BROWSE");
                $("#tipe_upload").append(`<option value="PRODUK BARU">PRODUK BARU</option>`);
                $("#modal_csv_title").text("Upload & Hitung KPH Produk Baru");
            } else {
                $("#btn_ftp").css("display", "none");
                $("#btn_browse").removeClass("btn-warning");
                $("#btn_browse").addClass("btn-info");
                $("#btn_browse").text("HISTORY PINDAH SUPPLY");
                $("#tipe_upload").append(`<option value="PINDAH SUPPLY">PINDAH SUPPLY</option>`);
                $("#modal_csv_title").text("UPLOAD FILE CSV PINDAH SUPPLY");
            }
        });

        $('.checkbox-group').click(function() {
            $('.checkbox-group').not(this).prop('checked', false);
        });
    });

    $('#modal_absensi_file').on('shown.bs.modal', function() {
        tb_absensi_file.columns.adjust();
    });

    $('#modal_report').on('shown.bs.modal', function() {
        tb_report.columns.adjust();
    });

    function actionBrowse(){
        $("#file_input").click();
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

    $('#file_input').on('change', function() {
        if ($(this).val()) {
            actionCheckPath();
        } else {
            $('#btn_proses').prop('disabled', true);
            $('#btn_browse_main').text('Browse');
        }
    });
    
    function actionCheckPath(){
        var files = $('#file_input')[0].files;
        if (!files || files.length === 0) {
            Swal.fire("Peringatan!", "Path Kosong!", "warning");
            return;
        }
        
        $('#modal_loading').modal('show');
        var periode = $("#periode").val();
        var periodeParts = periode.split('-');

        var formData = new FormData();
        
        formData.append('blnPeriode', parseInt(periodeParts[1], 10));
        formData.append('thnPeriode', periodeParts[0]);
        formData.append('periode', periode);
        $.each(files, function(index, file) {
            formData.append('files[]', file);
        });

        tb_absensi_file.clear().draw();

        $.ajax({
            url: currentURL + `/action/checkPath`,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                if(response.data.checkAbsensi == 0){
                    $("#file_input").val('').trigger("change");
                    $("#status_text").text("Status : Pilih Path History Produk!");
                    Swal.fire("Peringatan!", "Masih ada file toko yang tidak terpenuhi. Proses Tidak Dapat Dilanjutkan..", "warning").then(function(){
                        $("#modal_absensi_file").modal("show");
                        tb_absensi_file.rows.add(response.data.matchingDt).draw();
                    });
                } else {
                    $('#btn_browse_main').text('Ubah Path');
                    $('#btn_proses').prop('disabled', false);
                    Swal.fire("Success!", response.message, "warning");
                    $("#status_text").text("Status : File Berhasil Diupload! Klik Button Proses Untuk Melanjutkan");
                }
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                $("#file_input").val('').trigger("change");
                $("#status_text").text("Status : Pilih Path History Produk!");
                Swal.fire({
                    text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                        ? jqXHR.responseJSON.message
                        : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
            }
        });
    }

    function actionProses(){
        var files = $('#file_input')[0].files;
        if (!files || files.length === 0) {
            Swal.fire("Peringatan!", "Path Kosong!", "warning");
            return;
        } else if ($("#periode").val() === '') {
            Swal.fire('Peringatan!', 'Harap pilih Periode Terlebih Dahulu...!', 'warning');
            return;
        }

        Swal.fire({
            title: 'Yakin?',
            html: `Proses file pada periode ${moment($("#periode").val(), 'YYYY-MM').format("MM-YYYY")} ?`,
            icon: 'info',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                var periode = $("#periode").val();
                var formData = new FormData();
                $.each(files, function(index, file) {
                    formData.append('files[]', file);
                });
                formData.append('periode', periode);
                $('#modal_loading').modal('show');
                $.ajax({
                    url: currentURL + `/action/proses`,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                        Swal.fire('Success!', response.message,'success').then(function(){
                            tb.ajax.reload();
                            $("#status_text").text("Status : Data Has Been Updated!");
                            isiData();
                        });
                    }, error: function(jqXHR, textStatus, errorThrown) {
                        setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                        Swal.fire({
                            text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                                ? jqXHR.responseJSON.message
                                : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                            icon: "error"
                        });
                        $("#file_input").val('').trigger("change");
                        $("#status_text").text("Status : Pilih Path History Produk!");
                    }
                });
            }
        })
    }

    function actionHitKPH(){
        Swal.fire({
            title: 'Yakin?',
            html: `Yakin akan melakukan proses Hitung KPH ?`,
            icon: 'info',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                $('#modal_loading').modal('show');
                $.ajax({
                    url: currentURL + `/action/hit-kph`,
                    type: "POST",
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
        })
    }

    function showModalReport(){
        $("#modal_report").modal("show");
        tb_report.clear().draw();
        $('.datatable-no-data').css('color', '#F2F2F2');
        $('#loading_datatable_report').removeClass('d-none');
        $.ajax({
            url: currentURL + "/datatables-report",
            type: "GET",
            success: function(response) {
                $('#loading_datatable_report').addClass('d-none');
                $('.datatable-no-data').css('color', '#ababab');
                tb_report.rows.add(response.data).draw();
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function () { $('#loading_datatable_report').addClass('d-none'); }, 500);
                $('.datatable-no-data').css('color', '#ababab');
                Swal.fire({
                    text: "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
            }
        });
    }

    function actionReportKPH(){
        var priodeValue = $('input[name="periode-checkbox"]:checked').val();
        if (priodeValue === undefined || priodeValue === ''){
            Swal.fire('Peringatan!', 'Harap pilih periode Terlebih Dahulu...!', 'warning');
            return;
        }

        Swal.fire({
            title: 'Yakin?',
            html: `Report KPH pada Priode ${priodeValue} ?`,
            icon: 'info',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                $("#modal_loading").modal("show");
                $.ajax({
                    url: currentURL + `/action/report-kph`,
                    type: "POST",
                    data: {periode: priodeValue},
                    success: function(response) {
                        setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                        actionGlobalDownloadPdf(response.data);
                        Swal.fire('Success!', response.message,'success').then(function(){
                            $("#modal_report").modal("hide");
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
        });
    }

    function cekNamaFile(fileName) {
        var fname = fileName.replace("REPORT PRODUK BARU IDM ", ""); 
        var datePart = fname.substring(0, 6);
        
        var dateRegex = /^([0-2][0-9]|(3)[0-1])((0)[0-9]|(1)[0-2])([0-9]{2})$/;
        if(dateRegex.test(datePart)) {
            try {
                var dateConvert = moment(datePart, "DDMMYY");
                return dateConvert.isValid();
            } catch (e) {
                return false;
            }
        } else {
            return false;
        }
    }

    function getPeriodNewProduct(fileName, kode) {
        var fname = fileName.replace("REPORT PRODUK BARU IDM ", ""); // 150915
        var dateConvert;
        var strPeriod = "";
        
        try {
            var datePart = fname.substring(0, 6);
            dateConvert = moment(datePart, "DDMMYY");
            
            if (!dateConvert.isValid()) {
                throw new Error("Invalid date");
            }
            
            if (kode === 1) { // PID
                strPeriod = dateConvert.format("MMyyyy");
                if (strPeriod.charAt(0) === '0') {
                    strPeriod = strPeriod.substring(1);
                }
            } else { // FILE PERIOD
                strPeriod = dateConvert.format("DDMMYYYY");
            }

            return strPeriod;
        } catch (e) {
            return "NOTHING";
        }
    }

    $("#btn_browse").click(function(){
        if($("#file_input_modal")[0].files.length == 0){
            Swal.fire("Peringatan!", "Harap Upload File CSV Terlebih Dahlulu!", "warning");
            return;
        }

        let tipeUpload = $("#tipe_upload").val();
        let fileInput = $("#file_input_modal")[0];
        
        var swalText = "";
        var fileName = fileInput.files[0].name;
        if(tipeUpload == "MINOR"){
            swalText = "Proses Upload Miror Toko ?";
        } else if (tipeUpload == "PLUIDM"){
            swalText = "Proses Upload Miror Toko ?";
            var fileExtension = fileName.split('.').pop();
            if(fileExtension !== KodeIGR || fileName.substring(0, 3) !== "IDM"){
                Swal.fire("Peringatan!", "File tidak sesuai dengan kode cabang !", "warning");
                return;
            }
        } else if(tipeUpload == "PRODUK BARU"){
            var result = cekNamaFile(fileName);
            if (!result) {
                Swal.fire("Peringatan!", "Nama File Tidak Sesuai. Tidak bisa ambil periode!", "warning");
                return;
            }

            result = getPeriodNewProduct(fileName, 2);
            if(result == "NOTHING"){
                Swal.fire("Peringatan!", "Tidak bisa ambil periode!", "warning");
                return;
            }   
            swalText = "Proses upload produk baru periode " + result + " ?";
        } else {
            // PINDAH SUPPLY
            var extension = fileName.split('.').pop().toUpperCase();
            if (extension !== "CSV") {
                Swal.fire("Peringatan!", "File harus .CSV!", "warning");
                return;
            }
            swalText = "Proses Upload File Pindah Supply ?";
        }
        Swal.fire({
            title: 'Yakin?',
            html: swalText,
            icon: 'info',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                prosesData(tipeUpload, fileName);
            }
        });
    });

    function prosesData(tipeUpload, fileName){
        $('#modal_loading').modal('show');
        var formData = new FormData();
        formData.append('pilUpload', tipeUpload);
        formData.append('filename', fileName);
        formData.append('excel_file', $("#file_input_modal")[0].files[0]);
        $.ajax({
            url: currentURL + "/action/uploadCsvBrowse",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                setTimeout(() => {$("#modal_loading").modal("hide");}, 500);
                Swal.fire("Success!", response.message, "success").then(function(){
                    $("#file_input_modal").val("");
                    $("#modal_csv").modal("hide");
                    tb.ajax.reload();
                });
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(() => { $('#modal_loading').modal('hide') }, 500);
                Swal.fire({
                    text: (jqXHR.responseJSON && jqXHR.responseJSON.code === 400)
                        ? jqXHR.responseJSON.message
                        : "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
            }
        });
    }

    $("#periode").change(function(){
        isiData();
    });

    function isiData(){
        var periode = $("#periode").val();
        var periodeRequest = 0;
        if(periode !== ''){
            var periodeParts = periode.split('-');
            periodeRequest = parseInt(periodeParts[1], 10) + periodeParts[0];
        }
        tb.clear().draw();
        $('.datatable-no-data').css('color', '#F2F2F2');
        $('#loading_datatable').removeClass('d-none');
        $.ajax({
            url: currentURL + "/isi-data-datatables/" + periodeRequest,
            type: "GET",
            success: function(response) {
                $('#loading_datatable').addClass('d-none');
                $('.datatable-no-data').css('color', '#ababab');
                tb.rows.add(response.data).draw();
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function () { $('#loading_datatable').addClass('d-none'); }, 500);
                $('.datatable-no-data').css('color', '#ababab');
                Swal.fire({
                    text: "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
            }
        });
    }
</script>
@endpush
