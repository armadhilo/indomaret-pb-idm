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
                <b>REPORT BEBAN RETUR IGR</b>
            </h2>
            <br>
            <h5  style="margin-top: -10px;margin-bottom: 5px;text-align: center;">
               No : {{$data->data->data['DATA'][0]->bri_id}} 
               Tgl : {{date('d-m-Y',strtotime($data->data->data['DATA'][0]->bri_tgl))}}
            </h5> 
                @php
                    $no = 1;
                    $jumlah_ppn= 0;
                @endphp
                <table class="table" style=" margin-top:10px;">
                    <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                        <tr>
                            <th style="text-align:center; width:20px;">No</th>
                            <th style="text-align:center; width:20px;">@lang('PLU')</th>
                            <th style="text-align:center; width:100px;">@lang('KETERANGAN')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('QTY')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('SATUAN')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('PPN')</th>
                        </tr>
                    </thead>
                    <tbody style="border-bottom: 1px solid black;">
                
                    
                    @foreach($data->data->data['DATA'] as $key => $value)
                        @php
                            $jumlah_ppn += (float)$value->bri_ppn;
                        @endphp
                        <tr>
                            <td style="text-align:center;">{{ $no++}}</td>
                            <td style="text-align:center;">{{ isset($value->bri_prdcd)? $value->bri_prdcd :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->prd_deskripsipendek)? $value->prd_deskripsipendek :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->bri_qty)? $value->bri_qty :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->bri_price)? $value->bri_price :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->bri_ppn)? $value->bri_ppn :'-'}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="text-align:right;" colspan="5">@lang('Grand Toal')</td>
                            <td style="text-align:center;">{{ $jumlah_ppn}}</td>
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