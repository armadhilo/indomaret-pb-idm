@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('SURAT JALAN')
@endsection


@section('subtitle')

@endsection


@section('content')

    <h2  style="margin-top: -15px;margin-bottom: 5px;text-align: center;">
        <b>Surat Jalan</b>
    </h2>
        <h5 style="font-size:8px;text-align:center; margin:0px;"><b>No. SJ&nbsp; : {{$data->data->NoSJ}}</b></h5>
        <h5 style="font-size:8px;text-align:center; margin:0px;"><b>Tgl SJ&nbsp; : {{$data->data->TglSJ}}</b></h5>
    <br>
    <p style="margin-top:0px; font-size:11px;">
    <table style="text-align:left;">
                    <tr>
                        <th style="width:265px;text-align:left;"><b>Kode -Nama OMI</b></th>
                        <th style="width:10px;text-align:left;"><b>:</b></th>
                        <th style="width:265px;text-align:left;"><b>{{$data->data->OMI}}</b></th>
                        <th style="width:86px;text-align:left;"><b>No. PB OMI</b></th>
                        <th style="width:10px;text-align:left;"><b>:</b></th>
                        <th style="width:265px;text-align:left;"><b>{{$data->data->NoPB}}</b></th>
                    </tr>
                    <tr>
                        <td style="text-align:left;"><b>Kts. Container (Kardus pengganti - jika ada)</b></td>
                        <td style="text-align:left;"><b>:</b></td>
                        <td style="text-align:left;"><b> - </b></td>
                        <td style="text-align:left;"><b>Tgl PB. OMI</b></td>
                        <td style="text-align:left;"><b>:</b></td>
                        <td style="text-align:left;"><b>{{$data->data->TglPB}}</b></td>
                    </tr>
                    <tr>
                        <td style="text-align:left;"><b>Kts. Dolly</b></td>
                        <td style="text-align:left;"><b>:</b></td>
                        <td style="text-align:left;"><b> - </b></td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td style="text-align:left;"><b>Kts. Bronjong</b></td>
                        <td style="text-align:left;"><b>:</b></td>
                        <td style="text-align:left;"><b> - </b></td>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td style="text-align:left;"><b>Kts. Container yang perlu dikembalikan oleh OMI</b></td>
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
                        <th style="text-align:center; width:100px;">@lang('No. Sarana')</th>
                        <th style="text-align:center; width:80px; white-space:wrap;">@lang('Jml Isi Koli (kts item)')</th>
                        <th style="text-align:center; width:80px;">@lang('Nilai (Rp.)')</th>
                        <th style="text-align:center; width:105px;">@lang('PPN (Rp.)')</th>
                        <th style="text-align:center; width:105px; white-space:wrap;">@lang('Distribution Fee (Rp.)')</th>
                        <th style="text-align:center; width:105px; white-space:wrap;">@lang('Total nilai(Rp.)')</th>
                    </tr>
                </thead>
                <tbody style="border-bottom: 1px solid black;">
              

                @foreach($data->data->list_koli as $value)
                    <tr>
                        <td style="text-align:center;">{{$no++}}</td>
                        <td style="text-align:center;">{{$value->koli}}</td>
                        <td style="text-align:center;">{{$value->isikoli}}</td>
                        <td style="text-align:center;">{{number_format((int)$value->nilai,0,'.',',') }}</td>
                        <td style="text-align:center;">{{number_format((int)$value->ppn,0,'.',',') }}</td>
                        <td style="text-align:center;">{{number_format((int)$value->df,0,'.',',') }}</td>
                        <td style="text-align:center;">{{number_format((int)$value->totalnilai,0,'.',',') }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align:right;" colspan="3">@lang('Total BKP')</td>
                        <td style="text-align:center;">@lang('0')</td>
                        <td style="text-align:center;">@lang('0')</td>
                        <td style="text-align:center;">@lang('0')</td>
                        <td style="text-align:center;">@lang('0')</td>
                    </tr>
                    <tr>
                        <td style="text-align:right;" colspan="3">@lang('Total BTKP')</td>
                        <td style="text-align:center;">@lang('0')</td>
                        <td style="text-align:center;">@lang('0')</td>
                        <td style="text-align:center;">@lang('0')</td>
                        <td style="text-align:center;">@lang('0')</td>
                    </tr>
                </tfoot>
            </table>  
            <br>
                <table style="border-collapse: collapse; border: 1px solid black; margin-top:150px;">
                
                    <tr>
                        <th class="left" style=" width:30px;text-align:center; border: 1px solid black;">No.</th>
                        <th class="left" style=" width:190px;text-align:center; border: 1px solid black;">Sarana Pengiriman</th>
                        <th class="left" style=" width:50px;text-align:center; border: 1px solid black;">Jumlah</th>
                    </tr>
                    <tr style="">
                        <td class="left" style="height: 340px;text-align:center; border: 1px solid black;"></td>
                        <td class="left" style="height: 340px;text-align:center; border: 1px solid black;"></td>
                        <td class="left" style="height: 340px;text-align:center; border: 1px solid black;"></th>
                    </tr>
                </table>
                <div style="margin-left:480px;">
                    <table  style="margin-top:50px;width:80%;margin-bottom:50px; font-size:10px;">
                        
                        <tr>
                            <td colspan="6"><br></td>
                        </tr>
                        <tr>
                            <td class="left" colspan="2" style="text-align:center;">Diterima</td>
                            <td class="left" colspan="2" style="text-align:center;"> Dikirim</td>
                            <td class="left" colspan="2" style="text-align:center;"> Dibuat</td>
                        </tr>
                        <tr>
                            <td colspan="6"><br></td>
                        </tr>
                        <tr>
                            <td colspan="6"><br></td>
                        </tr>
                        <tr>
                            <td  class="left" colspan="2" style="text-align:center;"><hr style="width:80%;">Pihak OMI.</td>
                            <td  class="left" colspan="2" style="text-align:center;"><hr style="width:80%;">Kurir IndoPaket.</td>
                            <td  class="left" colspan="2" style="text-align:center;"><hr style="width:80%;">Pihak Issuing Toko Igr. </td>
                        </tr>
                        
                    </table>
                    <table  style="width:80%;margin-bottom:50px; font-size:10px;">
                        
                        <tr>
                            <td colspan="6"><br></td>
                        </tr>
                        <tr>
                            <td class="left" colspan="3" style="text-align:center;">Diterima</td>
                            <td class="left" colspan="3" style="text-align:center;"> Dikembalikan</td>
                        </tr>
                        <tr>
                            <td colspan="6"><br></td>
                        </tr>
                        <tr>
                            <td colspan="6"><br></td>
                        </tr>
                        <tr>
                            <td  class="left" colspan="3" style="text-align:center;"><hr style="width:80%;">Pihak Issuing Toko Igr</td>
                            <td  class="left" colspan="3" style="text-align:center;"><hr style="width:80%;">Kurir Indo Paket</td>
                        </tr>
                        
                    </table>
    
                </div>
@endsection¸