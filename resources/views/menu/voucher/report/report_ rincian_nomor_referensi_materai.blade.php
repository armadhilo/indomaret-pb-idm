@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('RINCIAN NOMOR REFERENSI MATERAI / VOUCHER')
@endsection

@section('title')
    @lang('RINCIAN NOMOR REFERENSI MATERAI / VOUCHER')
@endsection

@section('subtitle')

@endsection
@section('header_left')
    <table>
        <tr style="text-align: left;">
            <td>TOKO IDM</td>
            <td>:</td>
            <td> {{isset($tokoidm)}}</td>
        </tr>
        <tr style="text-align: left;">
            <td>NO PB / TGL PB</td>
            <td>:</td>
            <td> {{isset($nopb)}}/{{isset($tglpb)?date('d-m-Y',strtotime($tglpb)):'-'}}</td>
        </tr>
        <tr style="text-align: left;">
            <td>NO CONTAINER</td>
            <td>:</td>
            <td> {{isset($nocontainer)}}</td>
        </tr>
    </table>
@endsection
@section('header_right')
    <table>
        <tr style="text-align: left;">
            <td>NO REF</td>
            <td>:</td>
            <td> {{isset($noref)}}</td>
        </tr>
    </table>
@endsection

@section('content')
<table class="table" style="">
                <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                    <tr>
                        <th>@lang('NO.')</th>
                        <th>@lang('PLU')</th>
                        <th>@lang('DESKRIPSI')</th>
                        <th>@lang('QTY')</th>
                        <th>@lang('NO SERI')</th>
                    </tr>
                </thead>
                <tbody style="border-bottom: 1px solid black;">
                <!-- @for($i=1; $i<=10; $i++)
                    <tr>
                        <td    >@lang('NO.'.$i)</td>
                        <td    >@lang('PLU')</td>
                        <td    >@lang('DESKRIPSI')</td>
                        <td    >@lang('QTY')</td>ßß
                        <td    >@lang('NO SERI')</td>
                    </tr>
                @endfor -->
                </tbody>
            </table>
@endsection¸