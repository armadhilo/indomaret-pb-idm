@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('REPORT QR')
@endsection



@section('subtitle')

@endsection


@section('content')
@php
 //dd($data);
@endphp

<p class="center" style=" text-align: center; margin-bottom:50px; font-size:14px;">
    <span style="margin-right: 20px;"> Kode Toko : {{$data->kodetoko}}</span>
    <span style="margin-right: 20px;"> No NPB : {{$data->nopb}}</span>
    <span style="margin-right: 20px;"> Tgl NPB  : {{$data->tglpb}}</span>
</p>

<div class="center" style=" text-align: center; margin-top:100px; font-size:14px;">
<div style="display: inline-block; text-align:center; margin-right:20px;">
        {!! DNS2D::getBarcodeHTML($data->data[0]->QRbyte_L, 'QRCODE',2,2) !!}

        <span> {{$data->data[0]->Keterangan_L}}</span>
    </div>
    <div style="display: inline-block; text-align:center; margin-left:20px;">
        {!! DNS2D::getBarcodeHTML($data->data[0]->QRbyte_R, 'QRCODE',2,2) !!}
       
        <span> {{$data->data[0]->Keterangan_R}}</span>
    </div>
</div>
@endsection¸