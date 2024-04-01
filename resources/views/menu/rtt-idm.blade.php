@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">RTT - RETUR TOKO TUTUP IDM</h1>
@endsection

@section('css')
<style>
    .header{
        padding: 0 15px
    }

    input.form-no-style{
        border: none!important;
        background-color: transparent!important;
        box-shadow: none!important;
        outline: none;
        padding: 0;
        text-align: center;
    }

    .price-tag{
        border-radius: 8px;
        display: flex;
        justify-content: center;
        flex-direction: column;
        padding: 0 20px;
    }

    .price-tag h3{
        font-weight: bold;
        color: white;
    }

    .price-tag h5{
        font-weight: 600;
        color: #e0e0e0;
    }

    #tb_detail thead tr th, #tb_detail tbody tr td{
        padding: .75rem .50rem!important;
        font-size: .95rem!important;
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
                        <div class="row">
                            <div class="col-5">
                                <div class="form-group d-flex" style="gap: 15px">
                                    <label for="upload_rtt" class="detail-info" style="white-space: nowrap">Upload RTT &nbsp;:</label>
                                    <input type="file" id="upload_rtt" class="form-control" multiple accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                    <button class="btn btn-success btn-lg" style="padding: 8px 18px; white-space: nowrap" onclick="uploadExcel();">Upload Excel</button>
                                </div>
                                <div class="d-flex" style="gap: 15px">
                                    <div class="detail-info" style="flex: 1">* Tahan CTRL / SHIFT untuk Multiple Select File Excel</div>
                                    <button class="btn btn-warning btn-lg float-right" style="padding: 8px 26px;" onclick="prosesRTT();">Proses RTT</button>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="bg-royal w-100 h-100 price-tag">
                                    <h3>Total Retur Rp. <span id="total_retur">0,00</span></h3>
                                    <h5>NOODLE BOWL 15</h5>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-5">
                                <table class="table table-striped table-hover datatable-dark-primary w-100 table-center" id="tb">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>RTT</th>
                                            <th>Tgl RTT</th>
                                            <th>Toko Tutup</th>
                                            <th>Toko Tujuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-7">
                                <div class="table-responsive position-relative">
                                    <table class="table table-striped table-hover datatable-dark-primary w-100 table-center" id="tb_detail">
                                        <thead>
                                            <tr>
                                                <th>NO</th>
                                                <th>PLU</th>
                                                <th>KETERANGAN</th>
                                                <th>RETUR</th>
                                                <th>BAIK</th>
                                                <th>BA</th>
                                                <th>PRICE</th>
                                                <th>PPN</th>
                                                <th>STATUS</th>
                                                <th>TAG IDM</th>
                                                <th>LOKASI</th>
                                            </tr>
                                        </thead>
                                    </table>
                                    <button class="btn btn-lg btn-primary d-none" id="loading_datatable_detail" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);" type="button" disabled>
                                            <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                            Loading...
                                    </button>
                                </div>
                            </div>
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

    function uploadExcel(){
        var files = $('#upload_rtt')[0].files;
        if (files.length === 0) {
            Swal.fire('Oops!','Mohon Pilih File Excel Terlebih Dahulu..!','warning');
            return;
        }

        var fileNames = Array.from(files).map(file => file.name).join(', ');

        Swal.fire({
            title: 'Yakin?',
            html: `<b>Yakin Akan Melakukan Upload File Berikut..?</b> <br> ${fileNames}.`,
            icon: 'info',
            showCancelButton: true,
            buttons: ["Cancel", "Ya, Lanjutkan"],
        })
        .then((result) => {
            if (result.value) {
                var formData = new FormData();
                for (var i = 0; i < files.length; i++) {
                    formData.append('files[]', files[i]);
                }
                $('#modal_loading').modal('show');
                $.ajax({
                    url: currentURL + `/action/upload-excel`,
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
        });


    }

    function queryDatatableDetail(no_rtt, toko_tutup, toko_tujuan){
        tb_detail.clear().draw();
        $('.datatable-no-data').css('color', '#F2F2F2');
        $('#loading_datatable_detail').removeClass('d-none');
        $.ajax({
            url: currentURL + `/datatables-detail/${no_rtt}/${toko_tutup}/${toko_tujuan}`,
            type: "GET",
            contentType: false,
            processData: false,
            success: function(response) {
                setTimeout(function () { $('#loading_datatable_detail').addClass('d-none'); }, 500);
                let total_retur = 0;
                total_retur = response.data.reduce(function(acc, curr) {
                    return acc + ((parseInt(curr.price) * parseInt(curr.retur)) + parseInt(curr.ppn));
                }, 0);
                $("#total_retur").text(fungsiRupiah(total_retur));
                $('.datatable-no-data').css('color', '#ababab');
                tb_detail.rows.add(response.data).draw();
                $('#tb_detail tbody tr:first').dblclick();
            }, error: function(jqXHR, textStatus, errorThrown) {
                setTimeout(function () { $('#loading_datatable_detail').addClass('d-none'); }, 500);
                $('.datatable-no-data').css('color', '#ababab');
                Swal.fire({
                    text: "Oops! Terjadi kesalahan segera hubungi tim IT (" + errorThrown + ")",
                    icon: "error"
                });
            }
        });
    };

    function checkboxTableChange(element){
        if($(element).is(':checked')) {
            $(".checkbox-table").not(element).prop('checked', false);
            queryDatatableDetail($(element).data('rtt'), $(element).data('toko-tutup'), $(element).data('toko-tujuan'));
        } else {
            tb_detail.clear().draw();
            $("#total_retur").text(fungsiRupiah(0));
        }
    };

    function prosesRTT(){
        let checkboxElement = $(".checkbox-table:checked");
        if(checkboxElement.length !== 1){
            Swal.fire('Oops!','Mohon Centang List RTT Terlebih Dahulu','warning');
            $(".checkbox-table").prop('checked', false);
            return;
        }
        Swal.fire({
            title: 'Yakin?',
            text: `Proses RTT ${$(checkboxElement).data('rtt')} RTT IDM...?`,
            icon: 'warning',
            showCancelButton: true,
        })
        .then((result) => {
            if (result.value) {
                $('#modal_loading').modal('show');
                $.ajax({
                    url: currentURL + `/action/cetak`,
                    type: "POST",
                    data: {no_rtt: $(checkboxElement).data('rtt'), tgl_rtt: $(checkboxElement).data('tgl-rtt'), shop: $(checkboxElement).data('toko-tutup')},
                    success: function(response) {
                        setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                        var encodedRtt = encodeURIComponent($(checkboxElement).data('rtt'));
                        var encodedTglRtt = encodeURIComponent($(checkboxElement).data('tglRtt'));
                        var encodedTokoTutup = encodeURIComponent($(checkboxElement).data('tokoTutup'));
                        var url = currentURL + `/action/cetak?no_rtt=${encodedRtt}&tgl_rtt=${encodedTglRtt}&shop=${encodedTokoTutup}`;
                        window.open(url, '_blank');
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
</script>
@endpush

