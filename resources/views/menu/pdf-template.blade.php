<!DOCTYPE html>
<html>
<head>
    <title>@yield('page_title')</title>
</head>
<body>

<header>
    @if(!isset($header_cetak_custom))
        <!-- default -->
        <!-- upper header -->
        <div style="float:left; margin-top: 0px; line-height: 8px !important;">
            <p>
                {{ $perusahaan->prs_namaperusahaan }}
            </p>
            <p>
                {{ $perusahaan->prs_namacabang }}
            </p>
            @yield('header_left')
        </div>
        <div style="float:right; margin-top: 0px;margin-right: 0px; line-height: 3px !important;">
            <p>
                @lang('Tgl. Cetak') : {{ date("d/m/Y") }}
            </p>
            <p>
                @lang('Jam Cetak') : {{ date('H:i:s') }}
            </p>
            <p>
                <i>@lang('User ID')</i> : {{ session()->get('userid')}}
            </p>
            <p>
            <i>@lang('Hal.')</i> :
            </p>
            @yield('header_right')
        </div>
       
    @elseif($header_cetak_custom == 'bellow')
        <!-- bellow header -->
        <div style="float:left; margin-top: 45px; line-height: 8px !important;">
            @yield('header_left')
        </div>
        <div style="float:right; margin-top: 45px;margin-right: 0px; line-height: 3px !important;">
            @yield('header_right')
        </div>
    @elseif($header_cetak_custom == 'upper')
         <!-- upper header -->
        <div style="float:left; margin-top: 0px; line-height: 8px !important;">
            @yield('header_left')
        </div>
        <div style="float:right; margin-top: 0px;margin-right: 0px; line-height: 3px !important;">
            @yield('header_right')
        </div>
    @endif
    <div style=" text-align: center;">
        <p style="font-weight:bold;font-size:14px;text-align: center;padding: 0">
            @yield('title')
        </p>
        <p style="">
            @yield('subtitle','')
        </p>
    </div>
</header>



<main>
    @if(sizeof($data) == 0 )
        <h4 class="center" style=" text-align: center;">@lang('TIDAK ADA DATA')</h4>

    @else
        @yield('content')
    @endif


    <!-- ========================================== -->
    <!--      example table with border all         -->
    <!-- ========================================== -->
    <!-- <table class="table" style="">
            <thead style="border-top: 1px solid black;border-bottom: 1px solid black;">
                <tr>
                    <th rowspan="2" class="">@lang('PLU')</th>
                    <th rowspan="2" class="">@lang('DESKRIPSI')</th>
                    <th rowspan="2" class="">@lang('SATUAN')</th>
                    <th rowspan="2" class="">@lang('TAG')</th>
                    <th colspan="2" class="">@lang('----STOK-----')</th>
                    <th colspan="2" class="">@lang('-----PKM------')</th>
                    <th rowspan="2" class="">@lang('QTY')<br>@lang('PO OUT')</th>
                    <th rowspan="2" class="">@lang('QTY')<br>@lang('PB OUT')</th>
                    <th colspan="2" class="">@lang('---ORDER---')</th>
                    <th rowspan="2" class="">@lang('MIN ORDER')</th>
                    <th rowspan="2" class="">@lang('JUMLAH')</th>
                    <th rowspan="2" class="">@lang('IDM')</th>
                    <th rowspan="2" class="">@lang('OMI')</th>
                    <th rowspan="2" class="">@lang('SP')</th>
                </tr>
                <tr>
                    <th class="right">@lang('QTYB')</th>
                    <th class="right">@lang('K')</th>
                    <th class="right">@lang('QTYB')</th>
                    <th class="right">@lang('K')</th>
                    <th class="right">@lang('QTYB')</th>
                    <th class="right">@lang('K')</th>
                </tr>
            </thead>
            <tbody></tbody>
    </table> -->
    
    <!-- ========================================== -->
    <!-- example table with border up and down only -->
    <!-- ========================================== -->
    <!-- <table class="table table-bordered table-responsive" style="border-collapse: collapse;border: 2px solid black;">
        <thead>
            <tr style="border: 2px solid black;">
                <th rowspan="2" style="text-align: center;vertical-align: middle;border: 1px solid black;">No.</th>
                <th colspan="2" style="text-align: center;vertical-align: middle;border: 1px solid black;">Member Toko Igr.</th>
                <th colspan="2" style="text-align: center;vertical-align: middle;border: 1px solid black;">Tipe Outlet</th>
                <th rowspan="2" style="text-align: center;vertical-align: middle;border: 1px solid black;">Frekuensi <br>Kunjungan</th>
                <th rowspan="2" style="text-align: center;vertical-align: middle;border: 1px solid black;">Jumlah SP</th>
                <th rowspan="2" style="text-align: center;vertical-align: middle;border: 1px solid black;">Jumlah Item</th>
                <th rowspan="2" style="text-align: center;vertical-align: middle;border: 1px solid black;">Nilai(Rp.) SP**</th>
                <th colspan="2" style="text-align: center;vertical-align: middle;border: 1px solid black;">Margin Igr.</th>
            </tr>
            <tr style="border: 2px solid black;">
                <td style="text-align: center;vertical-align: middle;border: 1px solid black;">Kode</td>
                <td style="text-align: center;vertical-align: middle;border: 1px solid black;">Nama</td>
                <td style="text-align: center;vertical-align: middle;border: 1px solid black;">Kode</td>
                <td style="text-align: center;vertical-align: middle;border: 1px solid black;">Nama</td>
                <td style="text-align: center;vertical-align: middle;border: 1px solid black;">Rp.</td>
                <td style="text-align: center;vertical-align: middle;border: 1px solid black;">%</td>
            </tr>
        </thead>
        {{-- <tbody>
            <?php

                $temp_margin_rp = 0;
                $temp_margin_per = 0;

                $total_freq_kunjungan = 0;
                $total_jumlah_sp = 0;
                $total_jumlah_item = 0;
                $total_nilai_sp = 0;
                $total_margin_rp = 0;
                $total_margin_per = 0;

                $number = 0;
                $total_data = count($data);
                // dd($data);
            ?>

            @for($i=0;$i<$total_data;$i++)
                @php
                    $temp_margin_rp = (($data[$i]->fwamt - $data[$i]->csb_igr)/ 1.1) - $data[$i]->hpp;
                    $temp_margin_per = (((($data[$i]->fwamt - $data[$i]->csb_igr)/ 1.1) - $data[$i]->hpp) / (($data[$i]->fwamt - $data[$i]->csb_igr)/ 1.1));

                    $total_freq_kunjungan += $data[$i]->fwfreq ;
                    $total_jumlah_sp += $data[$i]->fwslip;
                    $total_jumlah_item += $data[$i]->fwprod;
                    $total_nilai_sp += $data[$i]->fwamt;
                    $total_margin_rp += $temp_margin_rp;
                    $total_margin_per += $temp_margin_per;
                @endphp
                <tr>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ $number+1 }}</td>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ $data[$i]->fcusno }}</td>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ $data[$i]->fnama }}</td>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ $data[$i]->foutlt }}</td>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ $data[$i]->out_namaoutlet }}</td>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ $data[$i]->fwfreq }}</td>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ $data[$i]->fwslip }}</td>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ $data[$i]->fwprod }}</td>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ rupiah($data[$i]->fwamt) }}</td>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ rupiah($temp_margin_rp) }}</td>
                    <td style="text-align: center;vertical-align: middle;border: 1px solid black">{{ percent($temp_margin_per)}} %</td>
                </tr>

                @php
                    $number++;
                    $temp_margin_rp = 0;
                    $temp_margin_per = 0;
                @endphp    
            @endfor

        <tr>
            <td colspan="5" style="text-align: center; font-weight: bold;border: 1px solid black">TOTAL</td>
            <td style="text-align: center; font-weight: bold;border: 1px solid black">{{$total_freq_kunjungan}}</td>
            <td style="text-align: center; font-weight: bold;border: 1px solid black">{{$total_jumlah_sp}}</td>
            <td style="text-align: center; font-weight: bold;border: 1px solid black">{{$total_jumlah_item}}</td>
            <td style="text-align: center; font-weight: bold;border: 1px solid black">{{rupiah($total_nilai_sp)}}</td>
            <td style="text-align: center; font-weight: bold;border: 1px solid black">{{rupiah($total_margin_rp)}}</td>
            <td style="text-align: center; font-weight: bold;border: 1px solid black">{{percent($total_margin_per) }} %</td>
        </tr>
        </tbody> --}}
    </table> -->

</main>

<footer>
    <p class="right" style="font-size: @yield('table_font_size','10px')">@yield('footer',(__('** Akhir dari laporan **')))</p>
</footer>

<br>
</body>
<style>
    @page {
        /*margin: 25px 20px;*/
        /*size: 1071pt 792pt;*/
        /* size: @yield('paper_size','595pt 842pt'); */
        size: @yield('paper_size','700pt 842pt');
        /*size: 842pt 638pt;*/
    }
    header {
        position: fixed;
        top: 0cm;
        left: 0cm;
        right: 0cm;
        height: 3cm;
    }
    body {
        margin-top: 80px;
        margin-bottom: 10px;
        font-size: 9px;
        /*Helvin 19/01/2023*/
        /*font-size: 9px;*/
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-weight: 400;
        line-height: @yield('body_line_height','1.8'); /* 1.25 */
    }
    table{
        border-collapse: collapse;
    }
    tbody {
        display: table-row-group;
        vertical-align: middle;
        border-color: black;
    }
    tr {
        display: table-row;
        vertical-align: inherit;
        border-color: black;
    }
    td {
        display: table-cell;
    }
    thead{
        text-align: center;
    }
    tbody{
        text-align: center;
    }
    tfoot{
        border-top: 1px solid black;
    }

    .keterangan{
        text-align: left;
    }
    .table{
        width: 100%;
        font-size: @yield('table_font_size','10px');
        white-space: nowrap;
        color: #212529;
        /*padding-top: 20px;*/
        /*margin-top: 25px;*/
    }
    .table-ttd{
        width: 100%;
        font-size: 9px;
        /*white-space: nowrap;*/
        color: #212529;
        /*padding-top: 20px;*/
        /*margin-top: 25px;*/
    }
    .table tbody td {
        /*font-size: 6px;*/
        vertical-align: top;
        /*border-top: 1px solid #dee2e6;*/
        padding: 0.20rem 0;
        width: auto;
    }
    .table th{
        vertical-align: top;
        padding: 0.20rem 0;
    }

    .table tfoot tr td{
        font-weight: bold;
    }

    .judul, .table-borderless{
        text-align: center;
    }
    .table-borderless th, .table-borderless td {
        border: 0;
        padding: 0.50rem;
    }

    .table tbody td.padding-right,.table tbody th.padding-right, .table thead th.padding-right, .table tfoot th.padding-right{
        padding-right: 10px !important;
    }

    .center{
        text-align: center;
    }

    .left{
        text-align: left;
    }

    .right{
        text-align: right;
    }

    .page-break {
        page-break-before: always;
    }

    .page-break-avoid{
        page-break-inside: avoid;
    }

    .table-header td{
        white-space: nowrap;
    }

    .tengah{
        vertical-align: middle !important;
    }

    .blank-row {
        line-height: 70px!important;
        color: white;
    }

    .bold td{
        font-weight: bold;
    }

    .border-top td{
        border-top: 1px solid black;
    }

    .top-bottom{
        border-top: 1px solid black;
        border-bottom: 1px solid black;
    }

    .nowrap{
        white-space: nowrap;
    }

    .overline{
        text-decoration: overline;
    }
    .pagebreak {
        page-break-before: always;
    }
    @yield('custom_style')
</style>
</html>
