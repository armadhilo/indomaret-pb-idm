@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">DSPB ROTI</h1>
@endsection

@section('css')
<style>
    .btn-lg{
        height: 50px;
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
                        <div class="col-4">
                            <div class="form-group d-flex" style="gap: 15px;">
                                <label for="tanggal_pick" style="white-space: nowrap; width: 150px" class="detail-info">Tanggal Pick</label>
                                <input type="date" class="form-control" id="tanggal_pick" style="width: calc(100% - 172px)" name="tanggal-pick" onchange="queryDatatable();">
                            </div>
                            <div class="form-group d-flex" style="gap: 15px;">
                                <label for="cluster_mobil" style="white-space: nowrap; width: 150px" class="detail-info">Cluster Mobil</label>
                                <select name="cluster-mobil" id="cluster_mobil" class="form-control select2" style="width: calc(100% - 150px)" onchange="queryDatatable();">
                                </select>
                            </div>
                            <div class="form-group d-flex flex-column" style="gap: 15px">
                                <button class="btn btn-lg btn-primary" onclick="cetakDspb();">CETAK DSPB</button>
                                <button hidden class="btn btn-lg btn-warning">TRANSFER ULANG DSPB</button>
                            </div>
                            <label for="report_qr" class="checkbox-label">
                                <input type="checkbox" id="report_qr">
                                Report QR Code
                            </label>
                        </div>
                        <div class="col-8">
                            <div class="table-responsive position-relative">
                                <table class="table table-striped table-hover datatable-dark-primary w-100 table-center" id="tb">
                                    <thead>
                                        <tr>
                                            <th>No. Urut</th>
                                            <th>Status</th>
                                            <th>Kode Toko</th>
                                            <th>Tgl. Trans</th>
                                            <th>No. PB</th>
                                            <th>Tgl. PB</th>
                                            <th>Item Valid</th>
                                            <th>Rph Valid</th>
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
    </div>
</div>
@endsection

@push('page-script')
<script>
    let tb;
    $(document).ready(function(){
        setDateNow("#tanggal_pick");
        getClusterMobil();
        tb = $('#tb').DataTable({
            language: {
                emptyTable: "<div class='datatable-no-data' style='color: #ababab'>Tidak Ada Data</div>",
            },
            columns: [
                { data: 'nourut'},
                { data: 'status'},
                { data: 'kodetoko'},
                { data: 'tgltrans',
                  "render": function(data, type, row, meta) {
                    return moment(data, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DD");
                   }
                },
                { data: 'nopb'},
                { data: 'tglpb',
                  "render": function(data, type, row, meta) {
                    return moment(data, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DD");
                   }
                },
                { data: 'itemvalid'},
                { data: 'rphvalid'},

            ],
            columnDefs: [
                { className: 'text-center-vh', targets: '_all' },
                { width: '12%', targets: [3, 5] },
            ],
            data: [],
            ordering: false,
        });
    });

    function getClusterMobil(){
        $('#modal_loading').modal('show');
        $.ajax({
            url: currentURL + `/get-cluster-mobil`,
            type: "GET",
            success: function(response) {
                setTimeout(function () { $('#modal_loading').modal('hide'); }, 500);
                $("#cluster_mobil").append(`<option disabled selected></option>`);
                response.data.forEach(item => {
                    $("#cluster_mobil").append(`<option value="${item.cri_kodecluster}">${item.cri_kodecluster}</option>`);
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
    };

    function queryDatatable(){
        let tanggal_pick = $("#tanggal_pick").val();
        if(tanggal_pick === '' || tanggal_pick === null){
            return;
        }

        let cluster_mobil = $("#cluster_mobil").val();
        if(cluster_mobil === '' || cluster_mobil === null){
            return;
        }
        tb.clear().draw();
        $('.datatable-no-data').css('color', '#F2F2F2');
        $('#loading_datatable').removeClass('d-none');
        $.ajax({
            url: currentURL + `/datatables/${encodeURIComponent(tanggal_pick)}/${encodeURIComponent(cluster_mobil)}`,
            type: "GET",
            contentType: false,
            processData: false,
            success: function(response) {
                setTimeout(function () { $('#loading_datatable').addClass('d-none'); }, 500);
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
    };

    function cetakDspb(){
        var tableData = tb.rows().data().toArray();
        var statusReady = false;
        tableData.forEach(function(row) {
            if (row.status === "SIAP DSPB") {
                statusReady = true;
            }
        });
        if (statusReady) {
            Swal.fire({
                title: 'Yakin?',
                html: `DSPB CLUSTER <b>${$("#cluster_mobil").val()}</b> Tanggal <b>${$("#tanggal_pick").val()}</b> ini ?`,
                icon: 'info',
                showCancelButton: true,
            })
            .then((result) => {
                if (result.value) {
                    tableData = tableData.filter(function(row) {
                        return row.status === "SIAP DSPB";
                    });

                    $('#modal_loading').modal('show');
                    $.ajax({
                        url: currentURL + `/action/cetak-dspb`,
                        type: "POST",
                        data: {datatables: tableData, cluster: $("#cluster_mobil").val()},
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
            });
        } else {
            Swal.fire('Oops!','Tidak ada data yang siap DSPB..!','warning');
        }

    };
</script>
@endpush
