@if(!$data)
    <p style="text-align: center">Data tidak ditemukan</p>
@else
    <!DOCTYPE html>
    <html>
    <head>
        <title>STRUK HADIAH</title>
    </head>
    <body>

    <?php
    $datetime = new DateTime();
    $timezone = new DateTimeZone('Asia/Jakarta');
    $datetime->setTimezone($timezone);
    ?>
    <header>
    </header>

    <h5 style="margin-top: 0px;margin-bottom: 0px;"><b>{{ $data->data->NPWP }}</b></h5>
    <hr>
    <h2  style="margin-top: -15px;margin-bottom: 0px;text-align: center;">
        <b>HADIAH</b>
    </h2>   
    <h4 style="margin-top: 0px;margin-bottom: 0px;text-align: center;">(BUKTI PENYERAHAN BRG. HADIAH)</h4> 
    <h5 style="margin-top: 0px;margin-bottom: 0px;text-align: center;">Tgl Struk : {{date('d-m-Y',strtotime($data->data->tglTran))}}</h5>
    <br>
    <p style="margin-top:-28px;">
        <h5 style="text-align:left; margin:0px;"><b>TOKO&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $data->data->NamaOMI }}</b></h5>
        <h5 style="text-align:left; margin:0px;"><b>NO. PB&nbsp;&nbsp; : {{ $data->nopb }}</b></h5>

    </p>

    <main>
        <table width="100%">
            <thead>
            <tr>
                <td colspan="5" class="left top"><b>Hadiah Langsung/ Stiker</b></td>
                <td colspan="1" class="right top"><b>Pembelian</b></td>
            </tr>
            <tr>
                <td class="left bottom" colspan="3"><b>Produk Berhadiah</b></td>
                <td class="right bottom"><b>QTY</b></td>
                <td class="right bottom" colspan="2"><b>FRAC</b></td>
            </tr>
            </thead>
            <tbody>
            @php
                $total = 0;
                $ppn = 0;
            @endphp
            @foreach($data->data->list_struk as $d)
                <tr>
                    <td colspan="4" class="left">{{ $d->plu }}</td>
                    <td colspan="2" class="right">({{ $d->prdcd }})</td>
                </tr>
                <tr>
                    <td class="right">{{ $d->qty }}</td>
                    <td class="right" colspan="3">{{ number_format($d->frac,0,'.',',') }}</td>
                    <td class="right" colspan="3">{{ number_format(($d->frac * $d->qty),0,'.',',') }}</td>
                </tr>

                @php
                    $total += ($d->frac * $d->qty);
                @endphp
            @endforeach
            </tbody>
            <tfoot>
                
            <tr>
                <td colspan="6"><br></td>
            </tr>
            <tr>
                <td colspan="6">1. <hr style="border:1px dashed black;"></td>
            </tr>
            <tr>
                <td colspan="6">2. <hr style="border:1px dashed black;"></td>
            </tr>
            <tr>
                <td colspan="6">3. <hr style="border:1px dashed black;"></td>
            </tr>
            <tr>
                <td class="left" colspan="1" style="text-align:center;"><b>Diterima</b></td>
                <td class="left" colspan="3" style="text-align:center;"><b>Diterima</b></td>
                <td class="right" colspan="2" style="text-align:center;"><b>Diserahkan</b></td>
            </tr>
            <tr>
                <td colspan="6"><br></td>
            </tr>
            <tr>
                <td colspan="6"><br></td>
            </tr>
            <tr>
                <td  class="left" colspan="1" style="text-align:center;"><hr style="width:70%;"><b>Custommer</b></td>
                <td  class="left" colspan="3" style="text-align:center;"><hr style="width:50%;"><b>Driver</b></td>
                <td  class="right" colspan="2" style="text-align:center;"><hr style="width:80%;"><b>Chief Delivery</b></td>
            </tr>
            <tr>
                <td colspan="6" class="center"><h3>#Harap Diparaf Dan Nama Jelas#</h3></td>
            </tr>
            <tr>
                <td colspan="6" class="center">
                    <h2 style="margin-bottom:-25px;">= {{$data->data->NamaCab}} =</h2>
                    <br style="margin-bottom:-25px;">
                    <h4 style="margin-bottom:-25px;"> {{$data->data->AlamatCab1." ".$data->data->AlamatCab2}} </h4>
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
            margin-bottom: 0px;
            /*margin: 25px 20px;*/
            /*size: 1071pt 792pt;*/
            /*size: 595pt 842pt;*/
            size: 298pt {{ 370+(count($data->data->list_struk)*28) }}pt;
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
