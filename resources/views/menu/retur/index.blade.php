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
                            <div class="row mb-5">
                                <div class="col-md-6">
                                    <div class="card border-3" style="height:260px; background-color:#FAFAFA;">
                                        <form action="{{url('api/proseswt/send')}}" method="post" id="form_wt" class="p-4">
                                            @csrf 
                                            <div class="form-group ">
                                                <label for="katb">Zona</label>
                                                <select class="form-control form-control-sm select2" name="zona" id="zona">
                                                    <option value="" disabled selected>Pilih Zona</option>
                                                    <option value="all">All</option>
                                                
                                                </select>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-4"></div>
                                                <button type="button" class="btn btn-sm btn-primary col-md-4 "> Cek KKS</button>
                                                <div class="col-md-4"></div>
                                            </div>
                                            <div class="form-group row">
                                                <button type="button" class="btn btn-sm btn-primary m-0 col-md-12"> Retur Barang Baik</button>
                                            </div>
                                            <div class="form-group row">
                                                <button type="button" class="btn btn-sm btn-primary col-md-4 "> RTBR</button>
                                                <button type="button" class="btn btn-sm btn-primary col-md-4 "> Re-Create <span></span>RTBR</button>
                                                <button type="button" class="btn btn-sm btn-primary col-md-4 "> Proses</button>
                                            </div>
                                        </form>
                                        <!-- <button type="button" class="btn btn-md btn-primary"> Proses SPH</button> -->
                                    </div>
                                </div>
                                <div class="col-md-6">
                                        <div class="card border-3 p-5" style="height:260px; background-color:#FAFAFA;">
                                            <h2 class="selisih"><b><span class="total_retur">.  </span></b></h2>
                                            <h4 class="selisih"><b><span class="label_retur">.  </span></b></h4>
                                        </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
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
                                                        <th style="min-width: 50px;" scope="col">No. NRB</th>
                                                        <th style="min-width: 60px;" scope="col">Tgl NRB</th>
                                                        <th style="min-width: 60px;" scope="col">Tipe</th>
                                                        <!-- Add more headers as needed -->
                                                        </tr>
                                                    </thead>
                                                    <tbody id="table-content-proseswt" style="height:420px;">
                                                    
                                                    </tbody>
                                                    </table>
                                                </div>
                                                <!-- ============================ -->
                                                <!--         End Table            -->
                                                <!-- ============================ -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
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
                                                            <th style="min-width: 50px;" scope="col">No. NRB</th>
                                                            <th style="min-width: 100px;" scope="col">Tgl NRB</th>
                                                            <th style="min-width: 120px;" scope="col">Tipe</th>
                                                            <!-- Add more headers as needed -->
                                                            </tr>
                                                        </thead>
                                                        <tbody id="table-content-proseswt" style="height:420px;">
                                                        
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

    <script src="{{asset('js/proses_wt/app-proses_wt.js')}}"></script>
@endsection


