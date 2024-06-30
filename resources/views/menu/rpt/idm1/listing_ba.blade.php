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
        // dd($data);
    @endphp

    @section('content')
            <h2  style="margin-top: -65px;margin-bottom: 5px;text-align: center;">
                <b>LISTING BA</b>
            </h2>
            <br>
            <h5  style="margin-top: -10px;margin-bottom: 5px;text-align: center;">
               Tanggal : {{date('d-m-Y',strtotime($data->tgl1))}} - {{date('d-m-Y',strtotime($data->tgl2))}}
            </h5> 
                @php
                    $no = 1;
                    $jumlah_igrdpp= 0;
                    $jumlah_igrppn= 0;
                    $jumlah_idmdpp= 0;
                    $jumlah_idmppn= 0;
                @endphp
                <table class="table" style=" margin-top:10px;">
                    <thead style="border :1px dashed black;">
                        <tr>
                            <th colspan="7"></th>
                            <th colspan="2" style="text-align:center; width:160px; white-space:wrap;">IDM</th>
                            <th colspan="2" style="text-align:center; width:160px; white-space:wrap;">IGR
                        </tr>
                        <tr>
                            <th style="text-align:center; width:20px;">No</th>
                            <th style="text-align:center; width:20px;">@lang('KODE TOKO')</th>
                            <th style="text-align:center; width:100px;">@lang('DOC RETUR')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('TGL DOC')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('NRB')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('TGL NRB')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('NO BA IDM')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('DPP')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('PPN')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('DPP')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('PPN')</th>
                        </tr>
                    </thead>
                    <tbody>
                
                    
                    @foreach($data->data->data['DATA'] as $key => $value)
                    @php
                        $jumlah_idmdpp += (int)$value->idmdpp;
                        $jumlah_idmppn += (int)$value->idmppn;
                        $jumlah_igrdpp += (int)$value->igrdpp;
                        $jumlah_igrppn += (int)$value->igrppn;
                    @endphp
                      
                        <tr>
                            <td style="text-align:center;">{{ $no++}}</td>
                            <td style="text-align:center;">{{ isset($value->tko_kodeomi)? $value->tko_kodeomi :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->trpt_salesinvoiceno)? $value->trpt_salesinvoiceno :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->trpt_salesinvoicedate)? date('d-m-Y',strtotime($value->trpt_salesinvoicedate)) :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->trpt_invoicetaxno)? $value->trpt_invoicetaxno :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->trpt_invoicetaxdate)? date('d-m-Y',strtotime($value->trpt_invoicetaxdate)) :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->bth_nodoc)? $value->bth_nodoc :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->idmdpp)? $value->idmdpp :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->idmppn)? $value->idmppn :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->igrdpp)? $value->igrdpp :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->igrppn)? $value->igrppn :'-'}}</td>
                        </tr>
                    
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="border :none">
                            <td colspan="11"></td>
                        </tr> 
                        <tr style="border :none">
                            <th colspan="7"></th>
                            <th colspan="2" style="text-align:right; width:160px; white-space:wrap;">BA DPP IDM</th>
                            <th colspan="2" style="text-align:center; width:160px; white-space:wrap;">{{$jumlah_idmdpp}}
                        </tr>
                        <tr style="border :none">
                            <th colspan="7"></th>
                            <th colspan="2" style="text-align:right; width:160px; white-space:wrap;">BA PPN IDM</th>
                            <th colspan="2" style="text-align:center; width:160px; white-space:wrap;">{{$jumlah_idmppn}}
                        </tr>
                        <tr style="border :none">
                            <td colspan="11"></td>
                        </tr> 
                        <tr style="border :none">
                            <th colspan="7"></th>
                            <th colspan="2" style="text-align:right; width:160px; white-space:wrap;">BA DPP IGR</th>
                            <th colspan="2" style="text-align:center; width:160px; white-space:wrap;">{{$jumlah_igrdpp}}
                        </tr>
                        <tr style="border :none">
                            <th colspan="7"></th>
                            <th colspan="2" style="text-align:right; width:160px; white-space:wrap;">BA PPN IGR</th>
                            <th colspan="2" style="text-align:center; width:160px; white-space:wrap;">{{$jumlah_igrppn}}
                        </tr>
                    </tfoot>
                   -->
                </table>  
    @endsection¸
@endif