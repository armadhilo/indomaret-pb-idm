@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('BPBR ULANG')
@endsection


@section('subtitle')

@endsection
@if($data)
    @section('header_left')
    <!-- <h3>{{ $perusahaan->prs_namacabang }}</h3> -->
    @endsection
    @section('header_right')
    <!-- <h3> Tgl. Cetak : {{date('d/m/Y')}}</h3> -->
    @endsection
    @php
        //dd($data);
    @endphp

    @section('content')
            <h2  style="margin-top: -65px;margin-bottom: 5px;text-align: center;">
                <b>BUKTI PENERIMAAN BARANG RETUR</b>
            </h2>
            <br>
            <h5  style="margin-top: -10px;margin-bottom: 5px;text-align: center;">
               No :{{$data->data->data['DATA'][0]->rom_nodokumen}}
            </h5>
            <h5  style="margin-top: -28px;margin-bottom: 5px;text-align: center;">
               Tgl ::{{date('d-m-Y',strtotime($data->data->data['DATA'][0]->rom_tgldokumen))}}
            </h5>
                @php
                    $no = 1;
                    $jumlah_ttg= 0;
                @endphp
                <table class="table" style=" margin-top:10px;">
                    <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                        <tr>
                            <th rowspan="2" style="text-align:center; width:20px;">No</th>
                            <th rowspan="2" style="text-align:center; width:20px;">@lang('PLU')</th>
                            <th rowspan="2" style="text-align:center; width:100px;">@lang('Nama Barang')</th>
                            <th rowspan="2" style="text-align:center; width:80px; white-space:wrap;">@lang('FRAC')</th>
                            <th colspan="2" style="text-align:center; width:80px; white-space:wrap;">@lang('NRB Toko')</th>
                            <th colspan="3" style="text-align:center; width:80px; white-space:wrap;">@lang('Fisik Barang Dan Harga')</th>
                        </tr>
                        <tr>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('QTY')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Rupiah')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('QTY')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Avg Cost')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Total')</th>
                        </tr>
                    </thead>
                    <tbody style="border-bottom: 1px solid black;">
                
                    
                    @foreach($data->data->data['DATA'] as $key => $value)
                        @php
                            $jumlah_ttg += (float)$value->ttl_avg;
                        @endphp
                        <tr>
                            <td style="text-align:center;">{{ $no++}}</td>
                            <td style="text-align:center;">{{ isset($value->rom_prdcd)? $value->rom_prdcd :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->prd_deskripsipendek)? $value->prd_deskripsipendek :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->prd_frac)? $value->prd_frac :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->qty)? $value->qty :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->rom_hrgsatuan)? $value->rom_hrgsatuan :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->qtyf)? $value->qtyf :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->rom_avgcost)? $value->rom_avgcost :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->ttl_avg)? $value->ttl_avg :'-'}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="text-align:right;" colspan="8">@lang('Grand Toal')</td>
                            <td style="text-align:center;">{{ $jumlah_ttg}}</td>
                        </tr>
                    </tfoot>
                </table>  
            <table  style="margin-top:50px;width:80%;margin-bottom:50px; font-size:10px;float:left;">
            
            <tr>
                <td colspan="8"><br></td>
            </tr>
            <tr>
                <td class="left" colspan="2" style="text-align:center;"> Disetujui</td>
                <td class="left" colspan="2" style="text-align:center;"> Diterima</td>
                <td class="left" colspan="2" style="text-align:center;"> </td>
                <td class="left" colspan="2" style="text-align:center;"> Dicetak</td>
            </tr>
            <tr>
                <td colspan="8"><br></td>
            </tr>
            <tr>
                <td colspan="8"><br></td>
            </tr>
            <tr>
                <td  class="left" colspan="2" style="text-align:center;"><hr style="width:40%;">Store Adm Mgr/Jr mgr</td>
                <td  class="left" colspan="2" style="text-align:center;"><hr style="width:100%;">Receiving Clerk</td>
                <td  class="left" colspan="2" style="text-align:center;"><hr style="width:90px; border: 0;"></td>
                <td  class="left" colspan="2" style="text-align:center;"><hr style="width:100%;">Logistic Adm Clerk</td>

            </tr>
            <br>
            <p>
                1/2 Collection Clerk, 2/2 Logistic Adm Clerk
            </p>
            
        </table>
    @endsection¸
@endif