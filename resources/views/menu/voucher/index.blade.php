@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">VOUCHER & MATERIAL</h1>
@endsection

@section('css')
<style>
    /** max width card */
        .max-width-card {
            max-width: 156px;
            min-width: 156px;
        }
    /* Add your styling table_plu_seasonal */
    #table_plu {
        border-collapse: collapse;
        width: 100%;
    }

    #table_plu th,
    #table_plu td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    #table_plu th {
        background-color: #f2f2f2;
    }

    #table_plu tbody tr.selected {
        background-color: #a6e7ff; /* Change the background color when selected */
    }

    .selected-row {
        background-color: #007bff;
        color: #ffffff;
    }
</style>
@endsection

@section('content')

    <script> $(".nav-item-home").addClass("active"); </script>
   
    <div class="container-fluid">
        <h4 class="voucher-label"  style="display: inline-block;">Example Label </h4> <h4 style="display: inline-block;"><span class="badge badge-secondary voucher-count">0</span></h4>
        <div class="card shadow mb-4">
            <div class="card-body" id="label-tag">
            <br>
                <div class="container mt-5">
                
                            <div class="row d-flex justify-content-center">
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>PB TOTAL</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title pb_total">0</h5>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SIAP PICKING</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title siap_picking">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SIAP DSPB</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title siap_dspb">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SELESAI DSPB</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title selesai_dspb">0</h5>
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
                                                    <input type="date" class="form-control form-control-sm" placeholder="Masukan Tanggal Aktif" onchange="changeDate(this)">
                                                </div>

                                                <!-- <div class="form-group form-check">
                                                    <input type="checkbox" class="form-check-input" id="report_zona">
                                                    <label class="form-check-label" for="report_zona">Report Zona</label>
                                                </div> -->
                                                <div class="form-group form-check">
                                                    <input type="checkbox" class="form-check-input" id="report_qr" onchange="print_laporan(this)">
                                                    <label class="form-check-label" for="report_qr">Report QR Code</label>
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
                                                <input type="text" placeholder="Search" class="form-control form-control-sm mb-2" name="search" id="search_data">
                                                <div class="table-container table-responsive" id="scrollContainer">
                                                    <table class="table table-bordered" id="table_voucher">
                                                    <thead>
                                                        <tr>
                                                        <th style="min-width: 100px;" scope="col">No</th>
                                                        <th style="min-width: 100px;" scope="col">STAT</th>
                                                        <th style="min-width: 100px;" scope="col">KODETOKO</th>
                                                        <th style="min-width: 100px;" scope="col">NOPB</th>
                                                        <th style="min-width: 100px;" scope="col">TGLPB</th>
                                                        <!-- Add more headers as needed -->
                                                        </tr>
                                                    </thead>
                                                    <tbody id="table-content-voucher">
                                                        
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

 
    <div class="modal fade" id="modal_picking" tabindex="-1" aria-labelledby="exampleModalXlLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size:20px;" id="picking_label"></h5>
                    <i class="fa fa-check-circle checked" style="font-size:48px;color:green text-align:center;"></i>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                        <div class="card-body">

                                <!-- ============================ -->
                                <!--             FORM             -->
                                <!-- ============================ -->
                                <form action="{{url('/api/insert/bytanggal')}}" method="post" class="form_data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="plu">Kode PLU</label>
                                                <input type="text" class="form-control form-control-sm" placeholder=""id="plu_picking" name="plu_picking"">
                                            </div>
                                            <div class="form-group">
                                                <label for="deskripsi_picking">Deskripsi</label>
                                                <p id="deskripsi_picking"><u>This line of text will render as underlined</u></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="qty_order_picking">Quantity Order</label>
                                                <input type="text" class="form-control form-control-sm" placeholder="" id="qty_order_picking" name="qty_order_picking">
                                            </div>

                                            <div class="form-group">
                                                <label for="qty_realisasi_picking">Quantity Realisasi</label>
                                                <input type="text" class="form-control form-control-sm" placeholder="" id="qty_realisasi_picking" name="qty_realisasi_picking">
                                            </div>
                                            <div class="form-group">
                                                <label for="no_picking1">No Realisasi</label>
                                                <input type="text" class="form-control form-control-sm" placeholder="" id="no_picking1" name="no_picking1">
                                                <span>s/d</span>
                                                <input type="text" class="form-control form-control-sm" placeholder="" id="no_picking2" name="no_picking2">
                                                <input type="hidden" class="form-control form-control-sm" placeholder="" id="nopb" name="nopb">
                                                <input type="hidden" class="form-control form-control-sm" placeholder="" id="tglpb" name="tglpb">
                                                <input type="hidden" class="form-control form-control-sm" placeholder="" id="kodetoko" name="kodetoko">
                                            </div>
                                            <div class="form-group">
                                                <label for="datepicker">Jumlah No Seri</label>
                                                <input type="text" class="form-control form-control-sm"  value="0" name="jmlh_seri" id="jmlh_seri" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <!-- ============================ -->
                                <!--            END FORM            -->
                                <!-- ============================ -->
                                <!-- ============================ -->
                                <!--             Table            -->
                                <!-- ============================ -->
                                <input type="text" placeholder="Search" class="form-control form-control-sm mb-2" name="search" id="search_refrensi">
                                <div class="table-container table-responsive" id="scrollContainer">
                                    <table class="table table-bordered" id="table_picking">
                                    <thead>
                                        <tr>
                                        <th style="min-width: 100px;" scope="col">No Refrensi</th>
                                        <!-- Add more headers as needed -->
                                        </tr>
                                    </thead>
                                    <tbody id="table-content-picking">
                                        
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
                    <button type="button" class="btn btn-primary">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
    <script src="{{asset('js/voucher/app-voucher.js')}}"></script>
    <script src="{{asset('js/app-submitForm.js')}}"></script>
    <script src="{{asset('js/app-submitForm2.js')}}"></script>
    <script src="{{asset('js/app-hapus.js')}}"></script>
@endsection


