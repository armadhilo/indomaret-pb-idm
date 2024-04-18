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
                        <div class="detail-info" style="width: 750px; height: 44px; margin-bottom: 20px" id="status_text">Status : Pilih Path History Produk!</div>
                        <div class="d-flex" style="gap: 15px;">
                            <div class="form-group d-flex" style="gap: 15px; flex: 5;">
                                <label for="file_input" style="white-space: nowrap; width: 150px" class="detail-info">Pilih Path &nbsp;:&nbsp;</label>
                                <input type="file" class="form-control" accept=".txt" id="file_input">
                            </div>
                            <div class="form-group d-flex" style="gap: 15px; flex: 2;">
                                <label for="periode" style="white-space: nowrap; width: 150px" class="detail-info">Periode &nbsp;:&nbsp;</label>
                                <input type="month" class="form-control" id="periode" name="periode">
                            </div>
                            <div class="form-group d-flex" style="gap: 15px; flex: 3;">
                                <label for="mode" style="white-space: nowrap; width: 150px" class="detail-info">Mode &nbsp;:&nbsp;</label>
                                <select name="mode" id="mode" class="form-control form-select">
                                    <option value="KPH MEAN">KPH MEAN</option>
                                    <option value="PRODUK BARU">PRODUK BARU</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex flex-row child-flex-1 mt-2" style="gap: 15px">
                            <button class="btn btn-lg btn-cust-warning" id="btn_browse">Browse</button>
                            <button class="btn btn-lg btn-info" id="btn_proses" onclick="actionProses();">Proses</button>
                            <button class="btn btn-lg btn-cust-success" id="btn_upload">Upload CSV</button>
                            <button class="btn btn-lg btn-royal" id="btn_hit" onclick="actionHitKPH();">Hit. KPH</button>
                            <button class="btn btn-lg btn-danger" id="btn_reprt">Report KPH</button>
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

@push('page-script')
<script>
    let tb;
    $(document).ready(function(){
        $("#periode").val(moment().format('YYYY-MM'))
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
                { data: null, defaultContent: '' },
                { data: null, defaultContent: '' },

            ],
            columnDefs: [
                { className: 'text-center-vh', targets: '_all' },
            ],
            ordering: false,
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
            $("#btn_browse").attr("disabled", browseDisabled);
            $("#btn_hit").attr("disabled", hitDisabled);
            $("#periode").attr("disabled", periodeDisabled);
            $("#periode").val(periodeValue);

            $("#btn_proses").attr("disabled", false);
        });
    });

    function actionProses(){
        if ($("#file_input").val() === '') {
            Swal.fire('Peringatan!', 'File Path Masih Kosong..!', 'warning');
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
                var formData = new FormData();
                formData.append('txtFile', $("#file_input").val()[0]);
                formData.append('date', $("#periode").val());
                formData.append('mode', $("#mode").val());
                $('#modal_loading').modal('show');
                $.ajax({
                    url: currentURL + `/action/proses`,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                        Swal.fire('Success!', response.message,'success');
                        tb.ajax.reload();
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
                        tb.ajax.reload();
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
</script>
@endpush
