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
                <b>DAFTAR MEMBER MERAH</b>
            </h2>
            <br>
            <h5  style="margin-top: -10px;margin-bottom: 5px;text-align: center;">
               Sales tanggan : {{date('d/m/Y',strtotime($data->tgl1))}} - {{date('d/m/Y',strtotime($data->tgl2))}} 
            </h5> 
                @php
                    $no = 1;
                    $jumlah_sales= 0;
                @endphp
                <table class="table" style=" margin-top:10px;">
                    <!-- <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                        <tr>
                            <th style="text-align:center; width:20px;">@lang('PROVINSI')</th>
                            <th style="text-align:center; width:20px;">@lang('KOTA')</th>
                            <th colspan="2" style="text-align:center; width:100px;">@lang('KECAMATAN')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('DESA')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('TPS')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('DPT')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Rata2 DPT KCMTN')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('Rata2 DPT KLRHN')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('LUAS')</th>
                            <th style="text-align:center; width:80px; white-space:wrap;">@lang('LUAS')</th>
                            <th colspan="3" style="text-align:center; width:100px;">@lang('')</th>
                        </tr>
                    </thead> -->
                    <tbody style="border-bottom: 1px solid black;">
                
                    
                    @foreach($data->data->data['DATA'] as $key => $value)
                        @php
                            $jumlah_sales = isset($value->sale)?(int)$value->sale:0;
                        @endphp
                        <tr>
                            <th style=" border:1px solid black; text-align:center; width:20px;">@lang('PROVINSI')</th>
                            <th style=" border:1px solid black; text-align:center; width:20px;">@lang('KOTA')</th>
                            <th colspan="2" style=" border:1px solid black; text-align:center; width:100px;">@lang('KECAMATAN')</th>
                            <th style=" border:1px solid black; text-align:center; width:80px; white-space:wrap;">@lang('DESA')</th>
                            <th style=" border:1px solid black; text-align:center; width:80px; white-space:wrap;">@lang('TPS')</th>
                            <th style=" border:1px solid black; text-align:center; width:80px; white-space:wrap;">@lang('DPT')</th>
                            <th style=" border:1px solid black; text-align:center; width:80px; white-space:wrap;">@lang('Rata2 DPT KCMTN')</th>
                            <th style=" border:1px solid black; text-align:center; width:80px; white-space:wrap;">@lang('Rata2 DPT KLRHN')</th>
                            <th style=" border:1px solid black; text-align:center; width:80px; white-space:wrap;">@lang('LUAS')</th>
                            <th style=" border:1px solid black; text-align:center; width:80px; white-space:wrap;">@lang('LUAS')</th>
                            <th colspan="3" style="border:1px solid black; text-align:center; width:100px;">@lang('')</th>
                        </tr>
                        <tr style="border:1px solid black;">
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ isset($value->provinsi)?$value->provinsi:'-'}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ isset($value->kabupaten)?$value->kabupaten:'-'}}</td>
                            <td colspan="2" style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ isset($value->kecamatan)?$value->kecamatan:'-'}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ isset($value->kelurahan)?$value->kelurahan:'-'}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ isset($value->kode2010)?$value->kode2010:'-'}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ isset($value->tps)?$value->tps:'-'}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ isset($value->dpt)?$value->dpt:'-'}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ isset($value->avg_dptkec)?$value->avg_dptkec:0}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ isset($value->avg_dptkel)?$value->avg_dptkel:0}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ isset($value->luas)?$value->luas:'0'}} KM</td>
                            <td colspan="3" style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border:none;text-align:center;"> </td>
                            <td style="border-left: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">@lang('No.')</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">@lang('Nama')</td>
                            <td colspan="5" style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">@lang('Alamat')</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">@lang('Koordinat')</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">@lang('Kategori')</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">@lang('Segment')</td>
                            <td colspan="2" style="border-right: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">@lang('Sales')</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border:none;text-align:center;"> </td>
                            <td style="border-left: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{ $no++}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{isset($value->kode_member)?$value->kode_member:'( )'}}- {{isset($value->nama_member)?$value->nama_member:'UNKNOWN'}}</td>
                            <td colspan="5" style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{isset($value->cus_alamatmember1)?$value->cus_alamatmember1:'-'}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{isset($value->crm_koordinat)?$value->crm_koordinat:'-'}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{isset($value->kategori)?$value->kategori:'-'}}</td>
                            <td style="border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{isset($value->segmentasi_crm)?$value->segmentasi_crm:'-'}}</td>
                            <td colspan="2" style="border-right: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black; text-align:center;">{{isset($value->sale)?(int)$value->sale:0}}</td>
                        </tr>
                        <tr>
                            <td colspan="13" style="border:none;text-align:right;color:red;">Total </td>
                            <td style="border:none;text-align:right;color:red;">{{$jumlah_sales}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table> 
            
        </table>
    @endsection¸
@endif