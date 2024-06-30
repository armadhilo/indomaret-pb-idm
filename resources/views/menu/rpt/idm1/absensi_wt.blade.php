@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang($data->title_report)
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
    @php
        //dd($data);
    @endphp

    @section('content')
            <h2  style="margin-top: -65px;margin-bottom: 5px;text-align: center;">
                <b>ABSENSI WT</b>
            </h2>
            <br>
            <h5  style="margin-top: -10px;margin-bottom: 5px;text-align: center;">
               Tanggal : {{date('d-m-Y',strtotime($data->tgl1))}} - {{date('d-m-Y',strtotime($data->tgl2))}}
            </h5> 
                @php
                    $no = 1;
                    $jumlah_ppn= 0;
                @endphp
                <table class="table" style=" margin-top:10px;">
                    <thead style="border :1px dashed black;">
                        <tr>
                            <th style="text-align:center; width:20px;">No</th>
                            <th style="text-align:center; width:20px;">@lang('Toko')</th>
                            <th style="text-align:center; width:100px;">@lang('Nama WT')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Tgl NRB')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Keterangan')</th>
                        </tr>
                    </thead>
                    <tbody>
                
                    
                    @foreach($data->data->data['DATA'] as $key => $data_list)
                       <tr>
                            <td colspan="5" style="margin-top:10px;text-align:left;"><b><u>{{$key}}</u></b></td>
                       </tr>
                       @foreach($data_list as $value)
                        <tr>
                            <td style="text-align:center;">{{ $no++}}</td>
                            <td style="text-align:center;">{{ isset($value->shop)? $value->shop :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->nm_wt)? $value->nm_wt :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->tgl1)? $value->tgl1 :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->keterangan)? $value->keterangan :'-'}}</td>
                        </tr>
                       @endforeach
                    
                    @endforeach
                    </tbody>
                   -->
                </table>  
    @endsection¸
@endif