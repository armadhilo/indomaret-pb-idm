@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('LIST PAKET PENGIRIMAN IDM')
@endsection



@section('content')
<style>
    table, th, td {
  border: 1px solid black; border-collapse: collapse;
}
</style>
        <div style=" text-align: center;">
                <p style="font-weight:bold;font-size:14px;text-align: center;padding: 0px; margin-top:-30px;">
                     LIST PAKET PENGIRIMAN IDM
                </p>
                <p style="">
                    Tgl : {{date('d-m-Y',strtotime($tanggal))}}
                </p>
            </div>
            @php
                $no = 0;
            @endphp
            <table class="table" style="">
                <thead style="">
                    <tr>
                        <th style="width:40px;">@lang('No.')</th>
                        <th style="width:40px;">@lang('Kode Toko')</th>
                        <th style="width:40px;">@lang('No PB')</th>
                        <th style="width:40px;">@lang('Tgl PB')</th>
                        <th style="width:40px;">@lang('No DSPB')</th>
                        <th style="width:40px;">@lang('Container')</th>
                        <th style="width:40px;">@lang('Bronjong')</th>
                        <th style="width:40px;">@lang('Kardus')</th>
                        <th style="width:40px;">@lang('Mobil Ke.')</th>
                    </tr>
                </thead>
                <tbody style="border-bottom: 1px solid black;">
                @php
                    $no = 1;
                    $total_container = 0;
                    $total_bronjong = 0;
                    $total_kardus = 0;
                @endphp
                @foreach($data as $key => $value)
                    <tr>
                        <td colspan="9" style="text-align:left; padding-left:15px;" ><b>No. Pengiriman {{$key}}</b></td>
                    </tr>
                    @foreach($value as $value_array)
                    <tr>
                        <td>{{$no++}}</td>
                        <td style="width:40px;">{{$value_array->kode_toko}}</td>
                        <td style="width:40px;">{{$value_array->no_pb}}</td>
                        <td style="width:40px;">{{$value_array->tgl_pb}}</td>
                        <td style="width:40px;">{{$value_array->no_dspb}}</td>
                        <td style="width:40px;">{{$value_array->jml_container}}</td>
                        <td style="width:40px;">{{$value_array->jml_bronjong}}</td>
                        <td style="width:40px;">{{$value_array->jml_kardus}}</td>
                        <td style="width:40px;">{{$value_array->mobil_ke}}</td>
                    </tr>
                    @php
                    $total_container += $value_array->jml_container;
                    $total_bronjong += $value_array->jml_bronjong;
                    $total_kardus += $value_array->jml_kardus;
                    @endphp
                    @endforeach
                    <tr>
                        <td colspan="5" style="text-align:right; padding-right:15px;" ><b> Sub Total</b></td>
                        <td style="text-align:center;" ><b>{{$total_container}}</b></td>
                        <td style="text-align:center; " ><b>{{$total_bronjong}}</b></td>
                        <td style="text-align:center; " ><b>{{$total_kardus}}</b></td>
                        <td style="text-align:center; " ><b> </b></td>
                    </tr>
                    @php
                    $total_container = 0;
                    $total_bronjong = 0;
                    $total_kardus = 0;
                    @endphp
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