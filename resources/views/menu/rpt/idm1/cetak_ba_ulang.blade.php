@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('BA ULANG')
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


    @section('content')
            <h2  style="margin-top: -65px;margin-bottom: 5px;text-align: center;">
                <b>BERITA ACARA</b>
            </h2>
            <br>
            <h3  style="margin-top: -30px;margin-bottom: 5px;text-align: center;">
                Kekurangan Penerimaan Barang Retur / Tolakan 
                (Per NRB Toko Idm)
            </h3>
            @if($data->data->type == 'F')
                <p style="width="50%"">
                    Pada Hari ini {{date('d/m/Y',strtotime($data->data->data_header->bth_tgldoc))}}, telah dilaksanakan pemeriksaan penerimaan barang retur dari {{'Test'}}, sehubungan dengan NRB toko IDM No.{{$data->no_bpbr}}
                </p>
                @php
                    $no = 1;
                    $jumlah = 0;
                @endphp
                <table class="table" style=" margin-top:10px;">
                    <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                        <tr>
                            <th style="text-align:center; width:20px;">No</th>
                            <th style="text-align:center; width:20px;">@lang('PLU')</th>
                            <th style="text-align:center; width:100px;">@lang('Deskripsi')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Unit')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('NRB')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Fisik Terima')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Fisik Kurang')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Fisik Tolak')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Status /Tag')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Harga Satuan')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Beban Toko IDM')</th>
                        </tr>
                    </thead>
                    <tbody style="border-bottom: 1px solid black;">
                
                    
                    @foreach($data->data->data as $key => $value)
                        @php
                            $no++;
                            $jumlah += $value->ttl;
                        @endphp
                        <tr>
                            <td style="text-align:center;">{{ $no}}</td>
                            <td style="text-align:center;">{{ isset($value->rom_prdcd)? $value->rom_prdcd :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->prd_deskripsipendek)? $value->prd_deskripsipendek :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->pcs)? $value->pcs :'pcs'}}</td>
                            <td style="text-align:center;">{{ isset($value->qtynrb)? $value->qtynrb :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->fisik)? $value->fisik :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->fisikkrg)? $value->fisikkrg :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->fisiktolak)? $value->fisiktolak :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->tag)? $value->tag :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->rom_hrgsatuan)? $value->rom_hrgsatuan :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->ttl)? $value->ttl :'-'}}</td>
                            <td style="text-align:center;"></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="text-align:right;" colspan="10">@lang('Jumlah')</td>
                            <td style="text-align:center;">{{ $jumlah}}</td>
                        </tr>
                    </tfoot>
                </table>  
            @else
                <p>
                    Pada Hari ini {{date('d/m/Y',strtotime($data->data->data_header->bth_tgldoc))}}, telah dilaksanakan pemeriksaan penerimaan barang retur dari (Operator CCTV Igr  dengan pihak BIC IDM)Melalui Rekaman CCTV atas proses scraning dari Document Import Tool - {{$data->data->toko[0]->tko_kodeomi}} {{$data->data->toko[0]->tko_namaomi}}, sehubungan dengan NRB Performa No. {{$data->data->data_header->bth_nonrb}}
                </p>
                @php
                    $no = 1;
                    $jumlah_bigr = 0;
                    $jumlah_bidm = 0;
                @endphp
                <table class="table" style=" margin-top:10px;">
                    <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                        <tr>
                            <th style="text-align:center; width:20px;">No</th>
                            <th style="text-align:center; width:20px;">@lang('PLU')</th>
                            <th style="text-align:center; width:100px;">@lang('Deskripsi')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('DSP')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('CCTV')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('QTY NRB')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('QTY Beban IGR')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('QTY Beban IDM')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Harga Satuan')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Beban IGR')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Beban IDM')</th>
                        </tr>
                    </thead>
                    <tbody style="border-bottom: 1px solid black;">
                
                    
                    @foreach($data->data->data as $key => $value)
                        @php
                            $jumlah_bigr += (int)$value->bigr;
                            $jumlah_bidm += (int)$value->bidm;
                        @endphp
                        <tr>
                            <td style="text-align:center;">{{ $no++}}</td>
                            <td style="text-align:center;">{{ isset($value->btd_prdcd)? $value->btd_prdcd :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->prd_deskripsipendek)? $value->prd_deskripsipendek :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->btd_dspb)? $value->btd_dspb :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->btd_cctv)? $value->btd_cctv :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->btd_qtynrb)? $value->btd_qtynrb :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->qtybigr)? $value->qtybigr :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->qtybidm)? $value->qtybidm :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->btd_price)? (int)$value->btd_price :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->bigr)? (int)$value->bigr :'-'}}</td>
                            <td style="text-align:center;">{{ isset($value->bidm)? (int)$value->bidm :'-'}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="text-align:right;" colspan="9">@lang('Jumlah')</td>
                            <td style="text-align:center;">{{ $jumlah_bigr}}</td>
                            <td style="text-align:center;">{{ $jumlah_bidm}}</td>
                        </tr>
                    </tfoot>
                </table>  
            @endif
            <table  style="margin-top:50px;width:25%;margin-bottom:50px; font-size:10px;float:right;">
            
            <tr>
                <td colspan="6"><br></td>
            </tr>
            <tr>
                <td class="left" colspan="2" style="text-align:center;"> Ttd & Nama Jelas</td>
                <td class="left" colspan="2" style="text-align:center;"> Ttd & Nama Jelas</td>
            </tr>
            <tr>
                <td colspan="6"><br></td>
            </tr>
            <tr>
                <td colspan="6"><br></td>
            </tr>
            <tr>
                <td  class="left" colspan="2" style="text-align:center;"><hr style="width:80%;">BIC IDM</td>
                <td  class="left" colspan="2" style="text-align:center;"><hr style="width:80%;">Logistic Adm Clerk</td>
            </tr>
            
        </table>
    @endsection¸
@endif