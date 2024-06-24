@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('LIST KUBIKASI PB IDM')
@endsection


@section('subtitle')

@endsection



@section('content')
<style>
    table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>
        <div style=" text-align: center;">
                <p style="font-weight:bold;font-size:14px;text-align: center;padding: 0px; margin-top:-30px;">
                    LIST KUBIKASI PB IDM
                </p>
                <p style="">
                    {{date('d-m-Y',strtotime($tanggal))}}
                </p>
            </div>
            @php
                $no = 0;
            @endphp
            <table class="table" style="">
                <thead style="">
                    <tr>
                        <th rowspan="2" style="width:40px;">@lang('NO.')</th>
                        <th rowspan="2" style="width:40px;">@lang('KODE')</th>
                        <th rowspan="2" style="width:70px;">@lang('NAMA TOKO')</th>
                        <th rowspan="2" style="width:40px;">@lang('NO PB')</th>
                        <th rowspan="2" style="width:40px;">@lang('NO PICK')</th>
                        <th rowspan="2" style="width:40px;">@lang('NO SJ')</th>
                        <th rowspan="2" style="width:40px;">@lang('RUPIAH')</th>
                        <th colspan="3" style="width:40px;">@lang('SEND JALUR')</th>
                        <th colspan="3" style="width:40px;">@lang('SELESAI SCANNING')</th>
                        <th colspan="4" style="width:40px;">@lang('SELESAI DSPB')</th>
                    </tr>
                    <tr>
                        <td><b>Cntr. </b></td>
                        <td><b>Brjg. </b></td>
                        <td><b>Kbk Mbl. </b></td>
                        <td><b>Cntr. </b></td>
                        <td><b>Brjg. </b></td>
                        <td><b>Kbk Mbl. </b></td>
                        <td><b>Cntr. </b></td>
                        <td><b>Krds. </b></td>
                        <td><b>Brjg. </b></td>
                        <td><b>Kbk Mbl. </b></td>
                    </tr>
                </thead>
                <tbody style="border-bottom: 1px solid black;">
                @php
                    $no = 0;
                @endphp
                @foreach($data as $value)
                    <tr>
                        <td>{{$no++}}</td>
                        <td>{{$value->kodetoko}}</td>
                        <td>{{$value->namatoko}}</td>
                        <td>{{$value->nopb}}</td>
                        <td>{{$value->nopick}}</td>
                        <td>{{$value->nosj}}</td>
                        <td>{{$value->rupiah}}</td>
                        <td>{{$value->cntr_sendjalur}}</td>
                        <td>{{$value->brjg_sendjalur}}</td>
                        <td>{{$value->kbk_sendjalur}}</td>
                        <td>{{$value->cntr_scan}}</td>
                        <td>{{$value->brjg_scan}}</td>
                        <td>{{$value->kbk_scan}}</td>
                        <td>{{$value->cntr_dspb}}</td>
                        <td>{{$value->krds_dspb}}</td>
                        <td>{{$value->brjg_dspb}}</td>
                        <td>{{$value->kbk_dspb}}</td>
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