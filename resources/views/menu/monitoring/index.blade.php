@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">MONITORING IDM</h1>
@endsection

@section('css')
<style>
    /** max width card */
        .max-width-card {
            max-width: 156px;
            min-width: 156px;
        }
    /* Add your styling table_plu_seasonal */
    #table_pengiriman {
        border-collapse: collapse;
        width: 100%;
    }

    #table_pengiriman th,
    #table_pengiriman td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    #table_pengiriman th {
        background-color: #f2f2f2;
    }

    #table_pengiriman tbody tr.selected {
        background-color: #a6e7ff; /* Change the background color when selected */
    }

</style>
@endsection

@section('content')

    <script> $(".nav-item-home").addClass("active"); </script>
   
    <div class="container-fluid">
        <h4 class="monitoring-label">Example Label <span class="badge badge-secondary monitoring-count">0</span></h4>
        <div class="card shadow mb-4">
            <div class="card-body" id="label-tag">
            <br>
                <div class="container mt-5">
                
                            <div class="row d-flex justify-content-center">
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>PB TOTAL</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title jmlhPb">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SEND JALUR</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title sendJalur">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>PICKING</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title picking">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SCANNING</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title scanning">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SIAP DSPB</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title siapDspb">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SELESAI CHECKING</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title selesaiLoading">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SELESAI DSPB</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title selesaiDspb">0</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <form action="{{url('/api/insert/bytanggal')}}" method="post" class="form_data">
                                                <div class="form-group">
                                                    <label for="datepicker">Tanggal</label>
                                                    <input type="date" class="form-control form-control-sm tanggal" placeholder="Masukan Tanggal Aktif" onchange="getDataMonitoring(this.value)">
                                                </div>
                                                <div class="form-group">
                                                    <label for="katb">Zona</label>
                                                    <div class="form-group">
                                                        <select class="form-control form-control-sm select2" name="zona" id="zona">
                                                            <option value="" disabled selected>Pilih Zona</option>
                                                            <option value="all">All</option>
                                                        
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group form-check">
                                                    <input type="checkbox" class="form-check-input" id="report_zona">
                                                    <label class="form-check-label" for="report_zona">Report Zona</label>
                                                </div>
                                                <div class="form-group form-check">
                                                    <input type="checkbox" class="form-check-input" id="report_qr">
                                                    <label class="form-check-label" for="report_qr">Report QR Code</label>
                                                </div>
                                                <div class="form-group d-flex justify-content-center">
                                                    <button class="btn btn-sm btn-primary" style="width:248px;height:52px;" type="button" onclick="file_rekon()"> File Rekon (AMS)</button>
                                                </div>
                                                <div class="form-group d-flex justify-content-center">
                                                    <button class="btn btn-sm btn-primary" style="width:248px;height:52px;" type="button" onclick="list_kubikasi_pb_idm()"> List Kubikasi PB IDM</button>
                                                </div>
                                                <div class="form-group d-flex justify-content-center">
                                                    <button class="btn btn-sm btn-primary" style="width:248px;height:52px;" type="button" data-toggle="modal" data-target="#modal_pengiriman" onclick="getDataPaketPengirimanIDM()"> Listing Paket Pengiriman</button>
                                                </div>
                                                <div class="form-group d-flex justify-content-center">
                                                    <button class="btn btn-sm btn-primary" style="width:248px;height:52px;" type="button"> Laporan Monitoring Tampa DSPB/SJ-O</button>
                                                </div>
                                                <div class="form-group d-flex justify-content-center">
                                                    <button class="btn btn-sm btn-primary" style="width:248px;height:52px;" type="button"> Cetak List Isi Bronjong</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="card">
                                        <div class="card-header">Table</div>
                                        <div class="card-body">
                                             <!-- ============================ -->
                                                <!--             Table            -->
                                                <!-- ============================ -->
                                                <div class="table-container table-responsive" id="scrollContainer">
                                                    <table class="table table-bordered" id="table_cabang">
                                                    <thead>
                                                        <tr>
                                                        <th style="min-width: 100px;" scope="col">No</th>
                                                        <th style="min-width: 100px;" scope="col">TOKO</th>
                                                        <th style="min-width: 100px;" scope="col">NOPB</th>
                                                        <th style="min-width: 100px;" scope="col">TGLPB</th>
                                                        <th style="min-width: 100px;" scope="col">NOPICK</th>
                                                        <th style="min-width: 100px;" scope="col">NOSJ</th>
                                                        <th style="min-width: 100px;" scope="col">GATE</th>
                                                        <th style="min-width: 100px;" scope="col">ITEMPB</th>
                                                        <th style="min-width: 100px;" scope="col">ITEMVALID</th>
                                                        <th style="min-width: 100px;" scope="col">RUPIAH</th>
                                                        
                                                        </tr>
                                                    </thead>
                                                    <!-- <thead>
                                                        <tr>
                                                        <th style="min-width: 100px;" scope="col">No</th>
                                                        <th style="min-width: 100px;" scope="col">TOKO</th>
                                                        <th style="min-width: 100px;" scope="col">NOPB</th>
                                                        <th style="min-width: 100px;" scope="col">TGLPB</th>
                                                        <th style="min-width: 100px;" scope="col">NOPICK</th>
                                                        <th style="min-width: 100px;" scope="col">NOSJ</th>
                                                        <th style="min-width: 100px;" scope="col">GATE</th>
                                                        <th style="min-width: 100px;" scope="col">ITEMPB</th>
                                                        <th style="min-width: 100px;" scope="col">ITEMVALID</th>
                                                        <th style="min-width: 100px;" scope="col">RUPIAH</th>
                                                        <th style="min-width: 100px;" scope="col">%</th>
                                                        <th style="min-width: 100px;" scope="col">ITMPICK</th>
                                                        <th style="min-width: 100px;" scope="col">TTLPICK</th>
                                                        
                                                        </tr>
                                                    </thead> -->
                                                    <tbody id="table-content">
                                                        
                                                    </tbody>
                                                    </table>
                                                </div>
                                                <!-- ============================ -->
                                                <!--         End Table            -->
                                                <!-- ============================ -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_pengiriman" tabindex="-1" aria-labelledby="exampleModalXlLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size:20px;" id="pengiriman_label">LISTING PAKET PENGIRIMAN IDM</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">
                                <!-- ============================ -->
                                <!--             Table            -->
                                <!-- ============================ -->
                                <input type="text" placeholder="Search" class="form-control form-control-sm mb-2" name="search" id="search_pengiriman">
                                <div class="table-container table-responsive" id="scrollContainer">
                                    <table class="table table-bordered" id="table_pengiriman">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 100px;" scope="col">No Pengiriman</th>
                                            <th style="min-width: 100px;" scope="col">Toko IDM </th>
                                            <th style="min-width: 100px;" scope="col">NoPB</th>
                                            <th style="min-width: 100px;" scope="col">TglPB</th>
                                            <th style="min-width: 100px;" scope="col">No DSPB</th>
                                        <!-- Add more headers as needed -->
                                        </tr>
                                    </thead>
                                    <tbody id="table-content-pengiriman">
                                        
                                    </tbody>
                                    </table>
                                </div>
                                <!-- ============================ -->
                                <!--         End Table            -->
                                <!-- ============================ -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="cetak_data_paket_pengiriman_idm()">Cetak</button>
                    <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> -->
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    <script src="{{asset('js/monitoring/app-monitoring.js')}}"></script>
    <script src="{{asset('js/app-submitForm.js')}}"></script>
    <script src="{{asset('js/app-submitForm2.js')}}"></script>
    <script src="{{asset('js/app-hapus.js')}}"></script>
@endsection


