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
       // dd($data);
    @endphp


    @section('content')
            <h2  style="margin-top: -65px;margin-bottom: 5px;text-align: center;">
                <b><u>LAPORAN OUTSTANDING RETUR</u></b>
            </h2>
            <br>
            <h3  style="margin-top: -30px;margin-bottom: 5px;text-align: center;">
                {{$perusahaan->prs_namacabang}}
            </h3>
                @php
                    $no = 1;
                    $jumlah= 0;
                    $jumlah_seluruh = 0;
                @endphp
                <p>Tanggal Cetak : {{date('d/m/Y')}}</p>
                <table class="table" style=" margin-top:10px;">
                    <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                        <tr>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('TIPE')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('PLUIDM')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('PLUIGR')</th>
                            <th style="text-align:center; width:100px;">@lang('NAMA BARANG')</th>
                            <th style="text-align:center; width:40px; white-space:wrap;">@lang('QTY')</th>
                            <th style="text-align:center; width:40px; white-space:wrap;">@lang('HARGA')</th>
                            <th style="text-align:center; width:40px; white-space:wrap;">@lang('PPN')</th>
                            <th style="text-align:center; width:40px; white-space:wrap;">@lang('TOTAL')</th>
                        </tr>
                    </thead>
                    <tbody style="border-bottom: 1px solid black;">
                
                    
                    @foreach($data->data->data as $toko => $data_toko)
                        @php
                            $no++;
                            $jumlah= 0;
                            $jumlah_seluruh = 0;
                        @endphp
                        <tr>
                            <td  style="text-align:left;"><b>Kode Toko</b></td>
                            <td colspan="7" style="text-align:left;"><b>: {{$toko}}</b></td>
                        </tr>
                        @foreach($data_toko as $doc => $list_data)
                        <tr>
                            <td  style="text-align:left;"><b>Doc Retur</b></td>
                            <td colspan="7" style="text-align:left;"><b>: {{$doc}}</b></td>
                        </tr>
                            @foreach($list_data as $key =>$value)
                            @php
                                $jumlah += $value->total;
                            @endphp
                            <tr>
                                <td style="text-align:center;">{{ isset($value->tipe)? $value->tipe :'-'}}</td>
                                <td style="text-align:center;">{{ isset($value->pluidm)? $value->pluidm :'-'}}</td>
                                <td style="text-align:center;">{{ isset($value->pluigr)? $value->pluigr :'-'}}</td>
                                <td style="text-align:left;">{{ isset($value->nmbrg)? $value->nmbrg :'-'}}</td>
                                <td style="text-align:center;">{{ isset($value->rom_prdcd)?(int)$value->rom_prdcd :0}}</td>
                                <td style="text-align:center;">{{ isset($value->qty)?(int)$value->qty :0}}</td>
                                <td style="text-align:center;">{{ isset($value->ppn)?(int)$value->ppn :0}}</td>
                                <td style="text-align:center;">{{ isset($value->total)?(int)$value->total :0}}</td>
                            </tr>

                            @endforeach
                            @php
                                $no++;
                                $jumlah_seluruh += $jumlah;
                            @endphp
                        <tr style="border:none;">
                            <td colspan="6" style="text-align:right;"><b>SUB TOTAL</b></td>
                            <td colspan="2" style="text-align:right;"><b>: {{$jumlah}}</b></td>
                        </tr>
                        @endforeach
                        <tr style="border-top: 1px solid black;border-bottom: 1px solid black;">
                            <td colspan="6" style="text-align:right;"><b>TOTAL PER TOKO</b></td>
                            <td colspan="2" style="text-align:right;"><b>: {{$jumlah}}</b></td>
                        </tr> 
                    @endforeach
                    </tbody>
                    <!-- <tfoot>
                        <tr>
                            <td style="text-align:right;" colspan="10">@lang('Jumlah')</td>
                            <td style="text-align:center;">{{ $jumlah}}</td>
                        </tr>
                    </tfoot> -->
                </table>  


    @endsection¸
@endif