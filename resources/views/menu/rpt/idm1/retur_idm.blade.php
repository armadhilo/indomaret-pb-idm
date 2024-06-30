@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang($data->title_report)
@endsection


@section('subtitle')

@endsection
@if($data)
    @section('header_left')
    <h3>PT. Inti Cakrawala Citra</h3>
    <h3>{{ $perusahaan->prs_namacabang }}</h3>
    @endsection
    @section('header_right')
    <h3> Tgl. Cetak : {{date('d/m/Y')}}</h3>
    @endsection
    @php
         //dd($data);
    @endphp

    @section('content')
            <h2  style="margin-top: -65px;margin-bottom: 5px;text-align: center;">
                <b>RETUR IDM</b>
            </h2>
            <br>
            <h5  style="margin-top: -10px;margin-bottom: 5px;text-align: center;">
               Tanggal : {{date('d-m-Y',strtotime($data->tgl1))}} - {{date('d-m-Y',strtotime($data->tgl2))}}
            </h5> 
                @php
                    $no = 1;
                    $jumlah_igrqty= 0;
                    $jumlah_igrrupiah= 0;
                    $jumlah_idmqty= 0;
                    $jumlah_idmrupiah= 0;
                    $jumlah_dipiutang= 0;
                @endphp 
                <p style="width:45%;">
                    Retur No: {{$data->data->data[0]->trpt_salesinvoiceno}} Tanggal Retur : {{date('d/m/Y',strtotime($data->data->data[0]->trpt_salesinvoicedate))}} NRB No :{{$data->data->data[0]->no_nrb}} Tgl NRB {{date('d/m/Y',strtotime($data->data->data[0]->tgl_nrb))}} &#13;&#10;
                    <br>
                    @if( $data->data->data[0]->jns_retur == 'F') 
                    "RETRU FISIK"
                    @else
                    "RETRU PROFORMA"
                    @endif
                </p>
                    <p>
                    </p>
                <table class="table" style=" margin-top:10px;">
                    <thead style="border :1px dashed black;">
                        <tr>
                            <th colspan="2" style="text-align:center; width:100px;">@lang('TOKO')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('PLU')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('QTY RTR IDM')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('QTY RTR FSK')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Rupiah Retur Fisik')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('QTY BA IDM')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Rupiah BA IDM')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('QTY BA IGR')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Rupiah BA IGR')</th>
                        </tr>
                    </thead>
                    <tbody>
                
                    
                    @foreach($data->data->data as $key => $value)
                    @php
                        $jumlah_idmqty += (int)$value->qty_ba_idm;
                        $jumlah_idmrupiah += (int)$value->ba_idm;
                        $jumlah_igrqty += (int)$value->qty_ba_igr;
                        $jumlah_igrrupiah += (int)$value->ba_igr;
                        $jumlah_dipiutang += (int)$value->rph_rtr_fsk + (int)$value->ba_idm + (int)$value->ba_igr; 
                    @endphp
                      
                        <tr>
                            <td style="text-align:center;">{{ $no++}} </td>
                            <td style="text-align:center;"> {{ isset($value->tko_namaomi)? $value->tko_namaomi :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->plu)? $value->plu :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->qty_rtr_idm)? $value->qty_rtr_idm :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->qty_rtr_fsk)? $value->qty_rtr_fsk :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->rph_rtr_fsk)? $value->rph_rtr_fsk :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->qty_ba_idm)? (int)$value->qty_ba_idm :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->ba_idm)? (int)$value->ba_idm :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->qty_ba_igr)? (int)$value->qty_ba_igr :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->ba_igr)? (int)$value->ba_igr :'-'}}</td>
                        </tr>
                    
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border :none">
                            <td colspan="10"></td>
                        </tr> 
                        <tr style="border :none">
                            <th colspan="6"><b>Total</b></th>
                            <th style="text-align:center; width:80px; white-space:wrap;">{{$jumlah_idmqty}}
                            <th style="text-align:center; width:80px; white-space:wrap;">{{$jumlah_idmrupiah}}
                            <th style="text-align:center; width:80px; white-space:wrap;">{{$jumlah_igrqty}}
                            <th style="text-align:center; width:80px; white-space:wrap;">{{$jumlah_igrrupiah}}
                        </tr> 
                        <tr style="border :none">
                            <td style="text-align:left; width:80px; white-space:wrap;" colspan="10">Total Retur Rph Fisik + Rph BA IDM + Rph BA IGR : {{$jumlah_dipiutang}}</td>
                        </tr> 
                   -->
                </table>  
    @endsection¸
@endif