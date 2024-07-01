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
@php
//dd($data);
@endphp
<style></style>
    <table>
        <tr style="text-align: left;">
            <td>TOKO IDM</td>
            <td>:</td>
            <td>{{$data->data->toko[0]->tko_kodeomi.'-'.$data->data->toko[0]->tko_namaomi}}</td>
        </tr>
        <tr style="text-align: left;">
            <td>NO PB / TGL PB</td>
            <td>:</td>
            <td>{{$data->data->header[0]->nopb.' / '.date('d/m/Y',strtotime($data->data->header[0]->tglpb))}}</td>
        </tr>
        <tr style="text-align: left;">
            <td>NO CONTAINER</td>
            <td>:</td>
            <td>{{$data->data->header[0]->nodspb}}</td>
        </tr>
    </table>
@endsection
@section('header_right')
    <table>
        <tr style="text-align: left;">
            <td>NO REF</td>
            <td>:</td>
            <td> {{$data->data->header[0]->nokoli}}</td>
        </tr>
    </table>
@endsection

@section('content')
            @php
                $no = 0;
            @endphp
            <table class="table" style="">
                <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                    <tr>
                        <th style="width:100px;">@lang('NO.')</th>
                        <th style="width:100px;">@lang('PLU')</th>
                        <th style="width:300px;">@lang('DESKRIPSI')</th>
                        <th style="width:80px;">@lang('QTY')</th>
                        <th style="width:265px;">@lang('NO SERI')</th>
                    </tr>
                </thead>
                <tbody style="border-bottom: 1px solid black;">
                @foreach($data->data->rinci_noseri as $key =>$value)

                    @php
                        $no++;
                        $seri1 = "00000";
                        $seri2 = "0000";
                        $seri3 = "000";
                    @endphp
                    <tr>
                        <td>{{$no}}</td>
                        <td>{{isset($value->pluigr)?$value->pluigr:'-'}}</td>
                        <td  style="text-align: left;">
                            {{$value->desc2}}
                        </td>
                        <td>{{$value->qty}}</td>
                        <td style="text-align: left; white-space:wrap;">
                        
                            {{$value->nopb}}
                            <!-- @for($i=1; $i<=$value->qty; $i++)
                                
                                @if($i < 10)
                                    {{$seri1.$i}} 
                                @endif
                                @if($i > 10 && $i < 100)
                                    {{$seri2.$i}} 
                                @endif
                                @if($i > 10 && $i > 100&& $i < 1000)
                                    {{$seri3.$i}} 
                                @endif
                                @if($i < $value->qty)
                                    ,
                                @endif
                                @if(!($i % 6))
                                    <br>
                                @endif
                            @endfor -->
                        </td>
                    </tr>
                @endforeach

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