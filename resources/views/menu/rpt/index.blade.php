@extends('layouts.master')
@section('title')
    <h1 class="pagetitle">REPORT</h1>
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
    iframe {
            min-width: 540px;
            height: 600px;
            border: 1px solid #ccc;
        }
    .select2-container--default .select2-results__option {
        display: flex;
        align-items: center;
        padding: 10px;
    }
    .select2-container--default .select2-results__option .column {
        flex: 1;
    }
    .select2-container--default .select2-results__option .column.name {
        flex: 2;
    }
    .select2-container--default.select2-container--open {
    border-color: #007bff;
    width: 100% !important;
}
.select2-container {
    box-sizing: border-box;
    display: inline-block;
    margin: 0;
    position: relative;
    vertical-align: middle;
    width: 100% !important;
}

</style>
@endsection

@section('content')

    <script> $(".nav-item-home").addClass("active"); </script>
   
    <div class="container-fluid">
        <!--  <h4 class="monitoring-label">Example Label <span class="badge badge-secondary monitoring-count">0</span></h4> -->
        <div class="card shadow mb-4">
            <div class="card-body" id="rpt_card">
                <br>
                <div class="container mt-5">
                    <div class="row mb-5">
                        <div class="col-md-3">
                            <div class="card border-3" style=" background-color:#FAFAFA;">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="IDM1-tab" data-toggle="tab" href="#IDM1" role="tab" aria-controls="IDM1" aria-selected="true">IDM1</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="IDM2-tab" data-toggle="tab" href="#IDM2" role="tab" aria-controls="IDM2" aria-selected="false">IDM2</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="OMI-tab" data-toggle="tab" href="#OMI" role="tab" aria-controls="OMI" aria-selected="false">OMI</a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="IDM1" role="tabpanel" aria-labelledby="IDM1-tab"> 
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Outstanding DSPB</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Cetak Hitory DSPB</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Struk Hadiah</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Pemutihan Batch</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Cetak BA Ulang</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Cetak BPBR Ulang</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Beban Retur IGR</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Analisa CRM</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Absensi WT</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Listing BA</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Retur IDM</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Outstanding Retur</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Cetak BA Bronjong</button>
                                    </div>
                                    <div class="tab-pane fade" id="IDM2" role="tabpanel" aria-labelledby="IDM2-tab">
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">RTBR</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Tolakan Retur</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Cetak BA Acost 0</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">History DSPB Roti</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Rekap DSPB Roti</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">History DSPB Voucher</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">History Rubah Status</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">History Paket IPP</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Rekap Pindah Lokasi</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">NPB Web Service</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Perubahan Status Retur</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Retur Supplier</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Serah Terima Retur</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="">Cetak History QRCode</button>
                                        
                                        
                                    </div>
                                    <div class="tab-pane fade" id="OMI" role="tabpanel" aria-labelledby="OMI-tab">
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('cetak-ulang-dsp','Cetak Ulang DSP','/api/report/cetak/dsp/ulang')">Cetak Ulang DSP</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('cetak-ulang-sj','Cetak Ulang SJ','/api/report/cetak/sj/ulang')">Cetak Ulang SJ</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb()">Struk Hadiah</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb()">Outstanding DSP</button>
                                    </div>
                                </div>
                                
                                <!-- <button type="button" class="btn btn-md btn-primary"> Proses SPH</button> -->
                            </div>
                        </div>
                        <div class="col-md-9">   
                            <iframe id="pdfFrame" src="{{url('test')}}" type="application/pdf"></iframe>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal" role="dialog" id="modal-pb-toko">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                <form action="" method="post" id="modal-form" class="form_data">
                        <div class="form-group ">
                            <div class="row">
                                <div class="col-md-12">
    
                                    <label for="katb">Toko</label>
                                    <select class="form-control form-control-sm select2 toko" name="toko" id="toko" onchange="get_pb(this.value)">
                                        <option value="" disabled selected>Pilih Toko</option>
                                        <!-- <option value="all">All</option> -->
                                    
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <label for="katb">No PB</label>
                            <!-- <select class="form-control form-control-sm select2 pb" name="nopb[]" id="nopb" disabled multiple=""> -->
                            <select class="form-control form-control-sm select2 pb" name="nopb" id="nopb" disabled>
                                <option value="" disabled selected>Pilih PB</option>
                                <!-- <option value="all">All</option> -->
                            
                            </select>
                            <input type="hidden" name="text" class="text" value="" >
                        </div>
                        
                        
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">OK</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{asset('js/rpt/app-rpt.js')}}"></script>
    <script src="{{asset('js/form/app-submitForm2.js')}}"></script>
@endsection


