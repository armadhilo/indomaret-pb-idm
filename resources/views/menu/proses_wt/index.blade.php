@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">PROSES WT</h1>
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
        <!--  <h4 class="monitoring-label">Example Label <span class="badge badge-secondary monitoring-count">0</span></h4> -->
        <div class="card shadow mb-4">
            <div class="card-body" id="proses-wt">
            <br>
                <div class="container mt-5">
                
                            <!-- <div class="row d-flex justify-content-center">
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>PB TOTAL</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SEND JALUR</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>PICKING</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SCANNING</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SIAP DSPB</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SELESAI CHECKING</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title">0</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-width-card col-1 col-md-2 p-sm-1">
                                    <div class="card">
                                        <div class="max-width-card d-flex justify-content-center card-header px-1" style="font-size: 12px;"><b>SELESAI DSPB</b></div>
                                        <div class="max-width-card d-flex justify-content-center card-body">
                                            <h5 class="card-title">0</h5>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="row p-5">
                                <div class="col-md-6">
                                    <div class="card border-3 p-5" style="height:210px; background-color:#FAFAFA;">
                                        @if((int)session()->get('flagIGR'))
                                        <form action="{{url('api/proseswt/send')}}" method="post" id="form_wt">
                                            @csrf
                                            <input type="file" name="file" id="file" onchange="submit_wt()">
                                        </form>
                                        <button type="button" class="btn btn-md btn-primary"> Proses WT</button>
                                        @else
                                        <button type="button" class="btn btn-md btn-primary"> Proses SPH</button>
                                        @endif

                                    </div>
                                </div>
                                <div class="col-md-6">
                                        <div class="card border-3 p-5" style="height:210px; background-color:#FAFAFA;">
                                            <h2 class="selisih"><b>Selisih Rp.<span class="total_selisih"> 0</span></b></h2>
                                        </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header" style="height:61px;">Table</div>
                                        <div class="card-body">
                                             <!-- ============================ -->
                                                <!--             Table            -->
                                                <!-- ============================ -->
                                                <div class="table-container table-responsive" id="scrollContainer">
                                                    <table class="table table-bordered" id="table_proseswt">
                                                    <thead>
                                                        <tr>
                                                        <th style="min-width: 50px;" scope="col">No</th>
                                                        <th style="min-width: 100px;" scope="col">TOKO</th>
                                                        <th style="min-width: 120px;" scope="col">NAMA TOKO</th>
                                                        <th style="min-width: 100px;" scope="col">HARI BLN</th>
                                                        <th style="min-width: 100px;" scope="col">FILE WT</th>
                                                        <!-- Add more headers as needed -->
                                                        </tr>
                                                    </thead>
                                                    <!-- <tbody id="table-content-proseswt" style="height:420px;"> -->
                                                    <tbody id="table-content-proseswt" style="">
                                                    
                                                    </tbody>
                                                    </table>
                                                </div>
                                                <!-- ============================ -->
                                                <!--         End Table            -->
                                                <!-- ============================ -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4><b>Indomaret</b></h4>
                                        </div>
                                        <div class="card-body">

                                            <label for="">  </label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">DPP</div>
                                                    </div>
                                                    <input type="text" class=" dpp_idm form-control" placeholder="0" readonly name="dpp_idm">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">PPN</div>
                                                    </div>
                                                    <input type="text" class=" ppn_idm form-control" placeholder="0" readonly name="ppn_idm">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">Total</div>
                                                    </div>
                                                    <input type="text" class=" total_idm form-control" placeholder="0" readonly name="total_idm">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="">Retur Fisik</label>
                                                <input type="text" class="retur_fisik form-control form-control-sm" placeholder="0" readonly name="retur_fisik">
                                            </div>
                                            <div class="form-group">
                                                <label for="">Retur Peforma</label>
                                                <input type="text" class="retur_peforma form-control form-control-sm" placeholder="0" readonly name="retur_peforma">
                                            </div>

                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                        <div class="card-header">
                                            <h4><b>Indogrosir</b></h4>
                                        </div>
                                    <div class="card">
                                        <div class="card-body">
                                            <label for=""> Sales</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">DPP</div>
                                                    </div>
                                                    <input type="text" class=" dpp_igr form-control" placeholder="0" readonly name="dpp_igr">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">PPN</div>
                                                    </div>
                                                    <input type="text" class=" ppn_igr form-control" placeholder="0" readonly name="ppn_igr">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">Total</div>
                                                    </div>
                                                    <input type="text" class=" total_igr form-control" placeholder="0" readonly name="total_igr">
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                
                            </div>

                </div>

            </div>
        </div>
    </div>

    <script src="{{asset('js/proses_wt/app-proses_wt.js')}}"></script>
@endsection


