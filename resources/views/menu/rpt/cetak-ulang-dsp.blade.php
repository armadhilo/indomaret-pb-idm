@if(!$data)
    <p style="text-align: center">Data tidak ditemukan</p>
@else
    <!DOCTYPE html>
    <html>
    <head>
        <title>DSP-TOKO-KOLI</title>
    </head>
    <body>

    <?php
    $datetime = new DateTime();
    $timezone = new DateTimeZone('Asia/Jakarta');
    $datetime->setTimezone($timezone);
    ?>
    <header>
        {{--    <p>--}}
        {{--        {{ $perusahaan->prs_namaperusahaan }}<br>--}}
        {{--        {{ $perusahaan->prs_namacabang }}<br><br>--}}
        {{--    </p>--}}
        {{--    <h3 style="text-align:laporan daftar retur center">--}}
        {{--        ** STRUK RESET KASIR **<br>--}}
        {{--        No. Reset : {{ $noreset }}--}}
        {{--    </h3>--}}
    </header>

    <h5 style="margin-top: 0px;margin-bottom: 0px;"><b>NPWP : {{ $perusahaan->prs_npwp }}</b></h5>
    <hr>
    <h2  style="margin-top: -15px;margin-bottom: -15px;text-align: center;">
        <b>DAFTAR STRUK PENJUALAN</b>
    </h2>
    <br>
    <p style="margin-top:0px;">
        <h5 style="text-align:left; margin:0px; display:inline;"><b>No. DSP : {{ $perusahaan->prs_npwp }}</b></h5>
        <h5 style="text-align:right; margin-left:110px; display:inline;"> <b  style="text-align:right; ">{{date('H:i:s')}}</b></h5>
        <h5 style="text-align:left; margin:0px;"><b>No. PB&nbsp;&nbsp; : {{ $perusahaan->prs_npwp }}</b></h5>
        <h5 style="text-align:left; margin:0px;"><b>No. Koli&nbsp;: {{ $perusahaan->prs_npwp }}</b></h5>
    </p>

    <main>
        <table width="100%">
            <thead>
            <tr>
                <td colspan="6" class="left top">NAMA BARANG / PLU</td>
            </tr>
            <tr>
                <td class="right bottom">QTY</td>
                <td class="right bottom" colspan="2">H. SATUAN</td>
                <td class="right bottom">DISC.</td>
                <td class="right bottom" colspan="2">TOTAL</td>
            </tr>
            </thead>
            <tbody>
            @php
                $total = 0;
                $ppn = 0;
            @endphp
            @foreach($data as $d)
                <tr>
                    <td colspan="4" class="left">{{ $d->prd_deskripsipendek }}</td>
                    <td colspan="2" class="right">({{ $d->cp_plu }})</td>
                </tr>
                <tr>
                    <td class="right">{{ $d->rom_qtyselisih }}</td>
                    <td class="right" colspan="2">{{ number_format($d->cp_hsat,0,'.',',') }}</td>
                    <td class="right">{{ number_format($d->trjd_discount,0,'.',',') }}</td>
                    <td class="right" colspan="2">{{ number_format($d->cp_total,0,'.',',') }}</td>
                </tr>

                @php
                    $total += $d->cp_total;
                    $ppn += $d->cp_ppn;
                @endphp
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td class="left" colspan="2">Harga Jual</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($total,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2"><span>1</span> Item PPN</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($total,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">DPP</td>
                <td class="left" >: {{ number_format($total,0,'.',',') }}</td>
                <td class="left" colspan="2">PPN</td>
                <td class="right" >: {{ number_format($total,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">Distribution Fee</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($total,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">PPN %</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($total,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">TOTAL ( Distribution Fee+PPN )</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($total+$ppn,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">Jumlah Yang Harus Dibayar</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($total+$ppn,0,'.',',') }}</td>
            </tr>
            <tr>
                <td colspan="6"><hr></td>
            </tr>
            <tr>
                <td class="left" colspan="2">CHECKER</td>
                <td class="left" >: {{'407'}}</td>
                <td class="right" colspan="2"># {{count($data)}}</td>
                <td class="right" > ITEM</td>
            </tr>
            <tr>
                <td class="left" colspan="2">TOKO</td>
                <td class="left">:</td>
                <td class="right"  colspan="3">{{ 'O594'.'-'.'KOCI MART MINIMART' }}</td>
            </tr>
            <tr>
                <td colspan="6"><hr></td>
            </tr>
            <tr>
                <td class="left" colspan="2"></td>
                <td class="left"><b>{{ $data[0]->cus_namamember }}-{{ $data[0]->cus_kodemember }} (MEMBER)</b></td>
                <td class="right"  colspan="3"></td>
            </tr>
            <tr>
                <td colspan="6" class="center"><h3>*** Terima Kasih ***</h3></td>
            </tr>
            <tr>
                <td colspan="6" class="center">
                    <h2 style="margin-bottom:-25px;">= {{$perusahaan->prs_namacabang}} =</h2>
                    <br style="margin-bottom:-25px;">
                    <h4 style="margin-bottom:-25px;"> {{$perusahaan->prs_alamat1}},Telp. {{$perusahaan->prs_telepon}} </h4>
                    <h4></h4>
                </td>
            </tr>  
            <tr style="width:80%; margin-top:-15px;">>
                <td class="left" colspan="2">
                    <h3>PT. INTI CAKRAWALA CITRA</h3>
                    <h5 style="width:80%; margin-top:-15px;">
                            <p style="margin-top:-13px;"><b>JL. ANCOL BARAT I NO.9-10 </b></p>
                            <p style="margin-top:-13px;"><b>ANCOL PADEMANGAN </b></p>
                            <p style="margin-top:-13px;"><b>JAKARTA UTARA, DKI </b></p>
                            <p style="margin-top:-13px;"><b>JAKARTA 14430</b></p>
                    </h5>
                </td>
                <td class="right" colspan="4"><img style="height:46px;" src="{{public_path().'/logo-igr/igr.png'}}" alt=""></td>
            </tr>
            </tfoot>
        </table>
    </main>

    <br>
    </body>
    <style>
        @page {
            margin-top: 10px;
            margin-bottom: 10px;
            /*margin: 25px 20px;*/
            /*size: 1071pt 792pt;*/
            /*size: 595pt 842pt;*/
            size: 298pt {{ 370+(count($data)*28) }}pt;
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
            margin-top: 0px;
            margin-bottom: 0px;
            font-size: 12px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-weight: 400;
            line-height: 1.25;
        }
        table{
            border-collapse: collapse;
        }
        thead{
            display: table-row-group;
        }
        tbody {
            vertical-align: middle;
            border-color: inherit;
        }
        tr {
            display: table-row;
            vertical-align: inherit;
            border-color: inherit;
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
        table{
            width: 100%;
            font-size: 10px;
            white-space: nowrap;
            color: #212529;
            /*padding-top: 20px;*/
            /*margin-top: 25px;*/
        }
        table tbody td {
            /*font-size: 6px;*/
            vertical-align: top;
            /*border-top: 1px solid #dee2e6;*/
            padding: 0.20rem 0;
            width: auto;
        }
        table th{
            vertical-align: top;
            padding: 0.20rem 0;
        }
        .judul, .table-borderless{
            text-align: center;
        }
        .table-borderless th, .table-borderless td {
            border: 0;
            padding: 0.50rem;
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
        .blank-row
        {
            line-height: 20px!important;
            color: white;
        }

        .bold td{
            font-weight: bold;
        }

        .top-bottom{
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }

        .top{
            border-top: 1px solid black;
        }

        .bottom{
            border-bottom: 1px solid black;
        }
    </style>
    </html>

@endif
