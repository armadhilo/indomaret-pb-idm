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
                                                <button type="button" class="btn btn-sm btn-primary col-md-4 " onclick="cetak_report('cek_kks')"> Cek KKS</button>
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
                                        <div class="card-header" style="height:61px;"></div>
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
                                            <div class="card-header" style="height:61px;"></div>
                                            <div class="card-body">
                                                    <!-- ============================ -->
                                                    <!--             Table            -->
                                                    <!-- ============================ -->
                                                    <div class="table-container table-responsive" id="scrollContainer">
                                                        <table class="table table-bordered" id="table_retur">
                                                        <thead>
                                                            <div class="NonF">
                                                                    <tr class="NonF">
                                                                        <th style="min-width: 80px;">NO</th>
                                                                        <th style="min-width: 80px;">PLU</th>
                                                                        <th style="min-width: 80px;">KETERANGAN</th>
                                                                        <th style="min-width: 80px;">RETUR</th>
                                                                        <div class="RECID_S">
                                                                            <th class="RECID_S" style="min-width: 80px;">FISIK</th>
                                                                            <th class="RECID_S" style="min-width: 80px;">BAIK</th>
                                                                            <th class="RECID_S" style="min-width: 80px;">LAYAKRETUR</th>
                                                                        </div>
                                                                        <th style="min-width: 80px;">BA</th>
                                                                        <th style="min-width: 80px;">PRICE</th>
                                                                        <th style="min-width: 80px;">PPN</th>
                                                                        <th style="min-width: 80px;">STATUS</th>
                                                                        <th style="min-width: 80px;">TAG</th>
                                                                        <th style="min-width: 80px;">AVGCOST</th>
                                                                        <th style="min-width: 80px;">RETMAJALAH</th>
                                                                        <th style="min-width: 80px;">LOKASI</th>
                                                                        <div class="RECID_S">
                                                                            <th style="min-width: 80px;">EXP_DT</th>
                                                                        </div>
                                                                        <th style="min-width: 80px;">FLAGPINDAH</th>
                                                                    </tr>
                                                            </div>
                                                            <!-- Add more headers as needed -->
                                                            <div class="F">
                                                                <tr  class="F">
                                                                    <th style="min-width: 80px;">'NO</th>
                                                                    <th style="min-width: 80px;">'PLU</th>
                                                                    <th style="min-width: 80px;">'QTY DSPB</th>
                                                                    <th style="min-width: 80px;">'RETUR</th>
                                                                    <th style="min-width: 80px;">'BA</th>
                                                                    <th style="min-width: 80px;">'BEBAN IDM</th>
                                                                    <th style="min-width: 80px;">'BEBAN IGR</th>
                                                                    <th style="min-width: 80px;">'PRICE</th>
                                                                    <th style="min-width: 80px;">'PPN</th>
                                                                    <th style="min-width: 80px;">'TOTREF</th>
                                                                    <th style="min-width: 80px;">'TAG</th>
                                                                    <th style="min-width: 80px;">'STATUS</th>
                                                                    <th style="min-width: 80px;">'AVGCOST</th>
                                                                    <th style="min-width: 80px;">'RETMAJALAH</th>
                                                                    <th style="min-width: 80px;">'KETERANGAN RETUR</th>
                                                                    <th style="min-width: 80px;">'NO BA KONTAINER TERTINGGAL</th>
                                                                </tr>
                                                            </div>
                                                        </thead>
                                                        <tbody id="table-content-retur" style="height:420px;">
                                                        
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


