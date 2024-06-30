@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('CETAK_HITORY_DSPB')
@endsection


@section('subtitle')

@endsection


@section('content')
@php
//dd($data);
@endphp
    @if($data)
        <h2  style="margin-top: -15px;margin-bottom: 5px;text-align: center;">
            <b>DAFTAR STRUK PESANAN BARANG / SURAT BARANG</b>
        </h2>
            <h5 style="font-size:8px;text-align:center; margin:0px;"><b>No.&nbsp; : {{$data->data->header[0]->dspb}}</b></h5>
            <h5 style="font-size:8px;text-align:center; margin:0px;"><b>Tgl&nbsp; : {{date('d-m-Y',strtotime($data->data->header[0]->tgl_dspb))}}</b></h5>
            <br>
            <h5 style="font-size:8px;text-align:center; margin:0px;"><b>{{$data->data->list['cluster']}}</b></h5>
        <p style="margin-top:0px; font-size:11px;">
        <table style="text-align:left;">
                        <tr>
                            <th style="width:265px;text-align:left;"><b>Toko IDM</b></th>
                            <th style="width:10px;text-align:left;"><b>:</b></th>
                            <th style="width:265px;text-align:left;"><b>{{$data->data->toko[0]->tko_kodeomi}} 
                                <!-- <span style="margin-left:90px;">{{$data->data->header[0]->encrypt}}</span></b> -->
                                <img style="margin-left:30px;" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($data->data->header[0]->encrypt, 'C128',1.2,30) }}" alt="barcode" />
                            </th>
                            <th style="width:86px;text-align:right;"><b></b></th>
                            <th style="width:10px;text-align:left;"><b></b></th>
                            <th style="width:265px;text-align:left;"><b></b></th>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align:left;"><b>{{$data->data->toko[0]->tko_namaomi}}</b></td> 
                            <td style="text-align:right;"><b>Batch</b></td>
                            <td style="text-align:left;"><b>:</b></td>
                            <td style="text-align:left;"><b>{{$data->data->header[0]->ikl_nobpd}}</b></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align:left;"><b></b></td> 
                            <td style="text-align:right;"><b>No. PB </b></td>
                            <td style="text-align:left;"><b>:</b></td>
                            <td style="text-align:left;"><b>{{$data->data->header[0]->ikl_nopb}}</b></td>
                        </tr>
                        <tr>
                            <td style="text-align:left;"><b>Kts. Container (Kardus pengganti - jika ada)</b></td>
                            <td style="text-align:left;"><b>:</b></td>
                            <td style="text-align:left;"><b> {{$data->data->list['koli']}} </b></td>
                            <td style="text-align:right;"><b>No. PB-O</b></td>
                            <td style="text-align:left;"><b>:</b></td>
                            <td style="text-align:left;"><b> - </b></td>
                        </tr>
                        <tr>
                            <td style="text-align:left;"><b>Kts. Bronjong</b></td>
                            <td style="text-align:left;"><b>:</b></td>
                            <td style="text-align:left;"><b> {{$data->data->list['bronjong']}} </b></td>
                            <td style="text-align:right;"><b>Reff No. DSPB/SJ-O</b></td>
                            <td style="text-align:left;"><b>:</b></td>
                            <td style="text-align:left;"><b>-</b></td>
                        </tr>
                        <tr>
                            <td style="text-align:left;"><b>Kts. Dolly</b></td>
                            <td style="text-align:left;"><b>:</b></td>
                            <td style="text-align:left;"><b>{{$data->data->list['dolly']}} </b></td>
                            <td colspan="3"></td>
                        </tr>
                        <tr>
                            <td style="text-align:left;"><b>Kts. Container yang perlu dikembalikan oleh toko idm</b></td>
                            <td style="text-align:left;"><b>:</b></td>
                            <td style="text-align:left;"><b> - </b></td>
                            <td colspan="3"></td>
                        </tr>
        </table>

        </p>
        @php
            $no = 1;
        @endphp
        <table class="table" style=" margin-top:10px;">
            <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                <tr>
                    <th style="text-align:center; width:20px;">@lang('No.')</th>
                    <th style="text-align:center; width:100px;">@lang('Koli')</th>
                    <th style="text-align:center; width:80px; white-space:wrap;">@lang('Container')</th>
                    <th style="text-align:center; width:80px;">@lang('item')</th>
                    <th style="text-align:center; width:105px; white-space:wrap;">@lang('BKP')</th>
                    <th style="text-align:center; width:105px;">@lang('BTKP')</th>
                    <th style="text-align:center; width:105px;">@lang('PPN')</th>
                    <th style="text-align:center; width:105px; white-space:wrap; ">@lang('Keterangan')</th>
                </tr>
            </thead>
            <tbody style="border-bottom: 1px solid black;">
                
            @php
                $total_item = 0;
                $total_bkp = 0;
                $total_btkp = 0;
                $total_ppn = 0;
            @endphp
            @foreach($data->data->list_data as $value)
                @php
                    $total_item += (int)$value->item;
                    $total_bkp += (int)$value->bkp;
                    $total_btkp += (int)$value->btkp;
                    $total_ppn += isset($value->ppn)?(int)$value->ppn:0;
                @endphp
                <tr>
                    <td style="text-align:center;">{{$no++}}</td>
                    <td style="text-align:center;">{{$value->koli}}</td>
                    <td style="text-align:center;">{{$value->container}}</td>
                    <td style="text-align:center;">{{(int)$value->item}}</td>
                    <td style="text-align:center;">{{(int)$value->bkp}}</td>
                    <td style="text-align:center;">{{(int)$value->btkp}}</td>
                    <td style="text-align:center;">{{isset($value->ppn)?(int)$value->ppn:0}}</td>
                    <td style="text-align:center"> - </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td style="text-align:right;" colspan="3">@lang('Total')</td>
                    <td style="text-align:center;">@lang($total_item)</td>
                    <td style="text-align:center;">@lang($total_bkp)</td>
                    <td style="text-align:center;">@lang($total_btkp)</td>
                    <td style="text-align:center;">@lang($total_ppn)</td>
                    <td style="text-align:center;border-top: 1px solid black;"">-</td>
                </tr>
            </tfoot>
        </table>  
        <br>

        <table  style="margin-top:50px;width:100%;margin-bottom:50px; font-size:10px;">
            
            <tr>
                <td colspan="6"><br></td>
            </tr>
            <tr>
                <td class="left" colspan="2" style="text-align:center;">Penerima <br> Koli Barang</td>
                <td class="left" colspan="2" style="text-align:center;"> Penerima<br style="marigin-bottom:-0px;"><span style="padding-top:0px;">Dokumen</span></td>
                <td class="left" colspan="2" style="text-align:center;"> Diperiksa</td>
                <td class="left" colspan="2" style="text-align:center;"> Dibuat</td>
            </tr>
            <tr>
                <td colspan="6"><br></td>
            </tr>
            <tr>
                <td colspan="6"><br></td>
            </tr>
            <tr>
                <td  class="left" colspan="2" style="text-align:center;"><hr style="width:80%;">Delivery Driver/Man</td>
                <td  class="left" colspan="2" style="text-align:center;"><hr style="width:80%;">Chief Delivery / Ass</td>
                <td  class="left" colspan="2" style="text-align:center;"><hr style="width:80%;">Spcl. Issuing Clerk</td>
                <td  class="left" colspan="2" style="text-align:center;"><hr style="width:80%;">EDP Clerk - II</td>
            </tr>
            
        </table>
            <div style="margin-left:480px;">

            </div>
    @endif
@endsection¸