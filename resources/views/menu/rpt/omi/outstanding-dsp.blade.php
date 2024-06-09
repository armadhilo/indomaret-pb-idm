@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('OUTSTANDING DSP')
@endsection


@section('subtitle')

@endsection
@section('header_left')
<h3>{{ $data->data->PERUSAHAAN->prs_namacabang }}</h3>
@endsection
@section('header_right')
<h3> Tgl. Cetak : {{date('d/m/Y')}}</h3>
@endsection


@section('content')
    @if($data)
        <h2  style="margin-top: -65px;margin-bottom: 5px;text-align: center;">
            <b>LAPORAN OUTSTANDING DSPB</b>
        </h2>
        <br>
        @php
            $no = 1;
        @endphp
        <table class="table" style=" margin-top:10px;">
            <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                <tr>
                    <th style="text-align:center; width:20px;">@lang('No.')</th>
                    <th style="text-align:center; width:100px;">@lang('No. Sarana')</th>
                    <th style="text-align:center; width:80px; white-space:wrap;">@lang('Jml Isi Koli (kts item)')</th>
                    <th style="text-align:center; width:80px;">@lang('Nilai (Rp.)')</th>
                    <th style="text-align:center; width:105px;">@lang('PPN (Rp.)')</th>
                    <th style="text-align:center; width:105px; white-space:wrap;">@lang('Distribution Fee (Rp.)')</th>
                    <th style="text-align:center; width:105px; white-space:wrap;">@lang('Total nilai(Rp.)')</th>
                </tr>
            </thead>
            <tbody style="border-bottom: 1px solid black;">
            

                <tr>
                    <td style="text-align:center;">{{$no++}}</td>
                    <td style="text-align:center;">1111111}</td>
                    <td style="text-align:center;">1</td>
                    <td style="text-align:center;">1</td>
                    <td style="text-align:center;">1</td>
                    <td style="text-align:center;">1</td>
                    <td style="text-align:center;">1</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td style="text-align:right;" colspan="3">@lang('Total BKP')</td>
                    <td style="text-align:center;">@lang('0')</td>
                    <td style="text-align:center;">@lang('0')</td>
                    <td style="text-align:center;">@lang('0')</td>
                    <td style="text-align:center;">@lang('0')</td>
                </tr>
                <tr>
                    <td style="text-align:right;" colspan="3">@lang('Total BTKP')</td>
                    <td style="text-align:center;">@lang('0')</td>
                    <td style="text-align:center;">@lang('0')</td>
                    <td style="text-align:center;">@lang('0')</td>
                    <td style="text-align:center;">@lang('0')</td>
                </tr>
            </tfoot>
        </table>  
    @endif
@endsection¸