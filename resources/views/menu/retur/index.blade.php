@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">RETUR</h1>
@endsection

@section('css')
<style>
    /** max width card */
    .max-width-card {
        max-width: 156px;
        min-width: 156px;
    }
    /* Add your styling table_plu_seasonal */
    .selected-row {
        background-color: #007bff;
        color: #ffffff;
    }

</style>
@endsection

@section('content')

    <script> $(".nav-item-home").addClass("active"); </script>
   
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-body" id="retur_card">
            <br>
                <div class="container mt-5">
                            <div class="row mb-5">
                                <div class="col-md-6">
                                    <div class="card border-3" style="height:260px; background-color:#FAFAFA;">
                                        <form action="{{url('api/proseswt/send')}}" method="post" id="form_wt" class="p-4">
                                            @csrf 
                                            <div class="form-group ">
                                                <label for="katb">Toko</label>
                                                <select class="form-control form-control-sm select2" name="toko" id="toko" onchange="get_data_nrb()">
                                                    <option value="" disabled selected>Pilih Toko</option>
                                                    <!-- <option value="all">All</option> -->
                                                
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
                                                    <table class="table table-bordered" id="table_NRB">
                                                    <thead>
                                                        <tr>
                                                        <th style="min-width: 50px;" scope="col">No. NRB</th>
                                                        <th style="min-width: 60px;" scope="col">Tgl NRB</th>
                                                        <th style="min-width: 60px;" scope="col">Tipe</th>
                                                        <!-- Add more headers as needed -->
                                                        </tr>
                                                    </thead>
                                                    <tbody id="table-content-nrb" style="height:420px;">
                                                    
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
    <div class="modal" role="dialog" id="modalKoli">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">No Koli</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                
                    <table class="table table-bordered" id="table_koli">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;" scope="col">NO KOLI</th>
                            <th style="min-width: 100px;" scope="col">PLU</th>
                            <th style="min-width: 100px;" scope="col">QTY_DSPB</th>
                        <!-- Add more headers as needed -->
                        </tr>
                    </thead>
                    <tbody id="table-content-koli">
                        
                    </tbody>
                    </table>
                        
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="selected_modal_table(selectedTable)" class="btn btn-primary">OK</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{asset('js/retur/app-retur.js')}}"></script>
@endsection


