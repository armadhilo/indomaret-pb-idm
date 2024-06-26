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
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('outstanding_dspb','Outstanding DSPB','/api/report/cetak/outstanding_dspb',false,false)">Outstanding DSPB</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('cetak_hitory_dspb','Cetak Hitory DSPB','/api/report/cetak/cetak_hitory_dspb',false,false)">Cetak Hitory DSPB</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('struk_hadiah','Struk Hadiah','/api/report/cetak/struk_hadiah',false,false)">Struk Hadiah</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('pemutihan_batch','Pemutihan Batch','/api/report/cetak/pemutihan_batch',false,false)">Pemutihan Batch</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('cetak_ba_ulang','Cetak BA Ulang','/api/report/cetak/cetak_ba_ulang',false,false)">Cetak BA Ulang</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('cetak_bpbr_ulang','Cetak BPBR Ulang','/api/report/cetak/cetak_bpbr_ulang',false,false)">Cetak BPBR Ulang</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('beban_retur_igr','Beban Retur IGR','/api/report/cetak/beban_retur_igr',false,false)">Beban Retur IGR</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('analisa_crm','Analisa CRM','/api/report/cetak/analisa_crm',false,false)">Analisa CRM</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('absensi_wt','Absensi WT','/api/report/cetak/absensi_wt',false,false)">Absensi WT</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('listing_ba','Listing BA','/api/report/cetak/listing_ba',false,false)">Listing BA</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('retur_idm','Retur IDM','/api/report/cetak/retur_idm',false,false)">Retur IDM</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('outstanding_retur','Outstanding Retur','/api/report/cetak/outstanding_retur',false,false)">Outstanding Retur</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('cetak_ba_bronjong','Cetak BA Bronjong','/api/report/cetak/cetak_ba_bronjong',false,false)">Cetak BA Bronjong</button>
                                    </div>
                                    <div class="tab-pane fade" id="IDM2" role="tabpanel" aria-labelledby="IDM2-tab">
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('rtbr','RTBR','/api/report/cetak/rtbr',false,false)">RTBR</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('tolakan-retur','Tolakan Retur','/api/report/cetak/tolakan_retur',false,false)">Tolakan Retur</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('cetak-ba-acost','Cetak BA Acost','/api/report/cetak/cetak_ba_acost',false,false)">Cetak BA Acost 0</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('history-dspb-roti','History DSPB Roti','/api/report/cetak/history_dspb_roti',false,false)">History DSPB Roti</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('rekap-dspb-roti','Rekap DSPB Roti','/api/report/cetak/rekap_dspb_roti',false,false)">Rekap DSPB Roti</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('history-dspb-voucher','History DSPB Voucher','/api/report/cetak/history_dspb_voucher',false,false)">History DSPB Voucher</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('history-rubah-status','History Rubah Status','/api/report/cetak/history_rubah_status',false,false)">History Rubah Status</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('history-paket-ipp','History Paket IPP','/api/report/cetak/history_paket_ipp',false,false)">History Paket IPP</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('rekap-pindah-lokasi','Rekap Pindah Lokasi','/api/report/cetak/rekap_pindah_lokasi',false,false)">Rekap Pindah Lokasi</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('npb-web-service','NPB Web Service','/api/report/cetak/npb_web_service',false,false)">NPB Web Service</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('perubahan-status-retur','Perubahan Status Retur','/api/report/cetak/perubahan_status_retur',false,false)">Perubahan Status Retur</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('retur-supplier','Retur Supplier','/api/report/cetak/retur_supplier',false,false)">Retur Supplier</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('serah-terima-retur','Serah Terima Retur','/api/report/cetak/serah_terima_retur',false,false)">Serah Terima Retur</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('cetak-history-qrcode','Cetak History QRcode','/api/report/cetak/cetak_history_qrcode',false,false)">Cetak History QRCode</button>
                                        
                                        
                                    </div>
                                    <div class="tab-pane fade" id="OMI" role="tabpanel" aria-labelledby="OMI-tab">
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('cetak-ulang-dsp','Cetak Ulang DSP','/api/report/cetak/dsp/ulang',false,true)">Cetak Ulang DSP</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('cetak-ulang-sj','Cetak Ulang SJ','/api/report/cetak/sj/ulang',false,true)">Cetak Ulang SJ</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('struk-hadiah','Cetak Struk Hadiah','/api/report/struk/hadiah',false,true)">Struk Hadiah</button>
                                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="modal_toko_pb('outstanding-dsp','Cetak Outstanding DSP','/api/report/outstanding/dsp',true,true)">Outstanding DSP</button>
                                    </div>
                                </div>
                                
                                <!-- <button type="button" class="btn btn-md btn-primary"> Proses SPH</button> -->
                            </div>
                        </div>
                        <div class="col-md-9">   
                            <iframe id="pdfFrame" src="" type="application/pdf"></iframe>

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
                            <div class="nopb">
                                <label for="katb">No PB</label>
                                <!-- <select class="form-control form-control-sm select2 pb" name="nopb[]" id="nopb" disabled multiple=""> -->
                                <select class="form-control form-control-sm select2 pb" name="nopb" id="nopb" disabled>
                                    <option value="" disabled selected>Pilih PB</option>
                                    <!-- <option value="all">All</option> -->
                                
                                </select>
                            </div>
                            <div class="toko_2">
                                <label for="katb">s.d Toko</label>
                                <select class="form-control form-control-sm select2 toko hide" name="toko2" id="toko_2" onchange="get_pb(this.value)">
                                            <option value="" disabled selected>Pilih Toko</option>
                                            <!-- <option value="all">All</option> -->
                                        
                                </select>
                            </div>
                            <input type="hidden" name="text" class="text" value="" >
                        </div>
                        
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" onclick="submit_modal()" class="btn btn-primary">OK</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{asset('js/rpt/app-rpt.js')}}"></script>
    <script src="{{asset('js/form/app-submitForm2.js')}}"></script>
@endsection


