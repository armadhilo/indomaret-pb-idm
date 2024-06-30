@extends('menu.pdf-template')

@section('table_font_size','14 px')

@section('page_title')
    @lang($data->title_report)
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
       // dd($data);
    @endphp

    @section('content')
            <h1  style="margin-top: -5px;margin-bottom: 5px;text-align: center;">
                <b>BERITA ACARA</b>
            </h1>
            <br>
            <h2  style="margin-top: 10px;margin-bottom: 5px;text-align: center;">
               Kekurangan Bronjong dan / atau Dolly
            </h2> 
            <h4  style="margin-top: 10px;margin-bottom: 5px;text-align: center;">
               Nomor BA : {{$data->data->data[0]->noba}}
            </h4> 
            
                <table class="table" style=" margin-top:40px;font-size:12px;">
                    <thead style="border:none;">
                        <tr>
                            <th style="text-align:center; width:100px;"></th>
                            <th style="text-align:center; width:100px;"></th>
                            <th style="text-align:center; width:100px;"></th>
                            <th style="text-align:center; width:100px; white-space:wrap;"></th>
                            <th style="text-align:center; width:100px; white-space:wrap;"></th>
                        </tr>
                    </thead>
                    
                    <tbody>
                
                        <tr style="border:none;">
                            <td style="text-align:center;"> </td>
                            <td colspan="3" style="text-align:left;">Adanya bronjong dan/ atau dolly dari toko Idm di Toko Igr pada :</td>
                            <td style="text-align:center;"> </td>
                        </tr>
                        <tr style="border:none;">
                            <td style="text-align:center;"></td>
                            <td colspan="2" style="text-align:left; padding-left:80px;">Hari/ Tanggal</td>
                            <td style="text-align:left;">: {{$data->data->data[0]->tglba}}</td>
                            <td style="text-align:center;"> </td>
                        </tr>
                        <tr style="border:none;">
                            <td style="text-align:center;"> </td>
                            <td colspan="2" style="text-align:left;  padding-left:80px;">Kode-Nama Toko Idm</td>
                            <td style="text-align:left;">: {{$data->data->data[0]->toko}}</td>
                            <td style="text-align:center;"> </td>
                        </tr>
                        <tr style="border:none;">
                            <td style="text-align:center;"> </td>
                            <td colspan="2" style="text-align:left;  padding-left:80px;">Nomor/Tanggal DSPB</td>
                            <td style="text-align:left;">: {{$data->data->data[0]->dspb}}</td>
                            <td style="text-align:center;"> </td>
                        <tr style="border:none;">
                            <td style="text-align:center;"> </td>
                            <td colspan="3" style="text-align:left; ">Rincian bronjong dan/ atau dolly dari Toko Idm di Toko Igr adalah sbb :</td>
                            <td style="text-align:center;"> </td>
                        </tr>
                        <tr style="border:none;">
                            <td style="text-align:center;"> </td>
                            <td colspan="2" style="text-align:left;  padding-left:80px;">Total Bronjong</td>
                            <td style="text-align:left;">: {{$data->data->data[0]->ba_bronjong}}</td>
                            <td style="text-align:center;"> </td>
                        </tr>
                        <tr style="border:none;">
                            <td style="text-align:center;"> </td>
                            <td colspan="2" style="text-align:left;  padding-left:80px;">Total Dolly</td>
                            <td style="text-align:left;">: {{$data->data->data[0]->ba_dolly}}</td>
                            <td style="text-align:center;"> </td>
                        </tr>
                    
                    </tbody>
                   -->
                </table>  
                <table  style="margin-top:50px;width:80%;margin-bottom:50px; font-size:10px;float:left; font-size:12px;">
                    <tr>
                        <td colspan="8"><br></td>
                    </tr>
                    <tr>
                        <td class="left" colspan="2" style="text-align:center;"> </td>
                        <td class="left" colspan="2" style="text-align:center;"> Diketahui</td>
                        <td class="left" colspan="2" style="text-align:center;"> </td>
                        <td class="left" colspan="2" style="text-align:center;"> Disetujui</td>
                        <td class="left" colspan="2" style="text-align:center;"> </td>
                        <td class="left" colspan="2" style="text-align:center;"> Diperiksa</td>
                    </tr>
                    <tr>
                        <td colspan="8"><br></td>
                    </tr>
                    <tr>
                        <td colspan="8"><br></td>
                    </tr>
                    <tr>
                        <td  class="left" colspan="2" style="text-align:center;"><hr style="width:90px; border: 0;"></td>
                        <td  class="left" colspan="2" style="text-align:center;"><hr style="width:60%;">Inventory Control Cabang (Idm.)</td>
                        <td  class="left" colspan="2" style="text-align:center;"><hr style="width:90px; border: 0;"></td>
                        <td  class="left" colspan="2" style="text-align:center;"><hr style="width:100%;">Logistic Adm Clerk</td>
                        <td  class="left" colspan="2" style="text-align:center;"><hr style="width:90px; border: 0;"></td>
                        <td  class="left" colspan="2" style="text-align:center;"><hr style="width:100%;">Logistic Adm Clerk</td>

                    </tr>
                    <br>
                    
                </table>
    @endsection¸
@endif