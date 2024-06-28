@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('OUTSTANDING DSPB')
@endsection


@section('subtitle')

@endsection
@if($data)
    @section('header_left')
    <h3>{{ $perusahaan->prs_namacabang }}</h3>
    @endsection
    
    @section('header_right')
    <h3> Tgl. Cetak : {{date('d/m/Y')}}</h3>
    @endsection


    @section('content')
            <h2  style="margin-top: -65px;margin-bottom: 5px;text-align: center;">
                <b>LAPORAN OUTSTANDING DSPB</b>
            </h2>
            <br>
            @php
                $no = 1;
                $totalDpp = 0;
                $totalPpn = 0;
                $totalGrandDpp = 0;
                $totalGrandPpn = 0;
            @endphp
            <table class="table" style=" margin-top:10px;">
                <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                    <tr>
                        <th style="text-align:center; width:20px;"></th>
                        <th style="text-align:center; width:20px;">@lang('No DSPB')</th>
                        <th style="text-align:center; width:100px;">@lang('DPP')</th>
                        <th style="text-align:center; width:80px; white-space:wrap;">@lang('PPN')</th>
                        <th style="text-align:center; width:80px;">@lang('FILE NPB')</th>
                    </tr>
                </thead>
                <tbody style="border-bottom: 1px solid black;">
                @foreach($data->data as $key => $value)
                    @php
                        $totalDpp += (int)$value->dpp;
                        $totalPpn += (int)$value->ppn;
                        $totalGrandDpp += (int)$value->dpp;
                        $totalGrandPpn += (int)$value->ppn;
                    @endphp
                    <tr>
                        <td colspan="4" style="text-align:left; padding-left:32px;">
                            <u>
                                {{$value->ikl_kodeidm.'-'.$value->tko_namaomi}}
                            </u>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">{{ $value->ikl_nopb}}</td>
                        <td style="text-align:center;">{{ $value->ikl_registerrealisasi}}</td>
                        <td style="text-align:center;">{{ number_format((int)$value->dpp,0,'.',',') }}</td>
                        <td style="text-align:center;">{{ number_format((int)$value->ppn,0,'.',',') }}</td>
                        <td style="text-align:center;"></td>
                    </tr>
                @endforeach
                    <tr>
                        <td colspan="4"></td>
                    </tr>
                    <tr>
                        <td style="text-align:right;" colspan="2">@lang('Total')</td>
                        <td style="text-align:center;">{{ number_format( $totalDpp,0,'.',',') }}</td>
                        <td style="text-align:left; padding-left:75px;"colspan="2">{{ number_format( $totalPpn,0,'.',',') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align:right;" colspan="2">@lang('Grand Total')</td>
                        <td style="text-align:center;">{{ number_format( $totalGrandDpp,0,'.',',') }}</td>
                        <td style="text-align:left; padding-left:75px;"colspan="2">{{ number_format( $totalGrandPpn,0,'.',',') }}</td>
                    </tr>
                </tfoot>
            </table>  
    @endsection¸
@endif