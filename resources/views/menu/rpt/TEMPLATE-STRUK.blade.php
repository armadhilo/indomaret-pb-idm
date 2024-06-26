
@if(!$data)
    <p style="text-align: center">Data tidak ditemukan</p>
@else
    <!DOCTYPE html>
    <html>
    <head>
        <title>STRUK RETUR OMI</title>
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
    <p style="text-align: center">
        <b></b>
        ** {{ $perusahaan->prs_namacabang }} **<br>
        ** {{ $perusahaan->prs_namaperusahaan }} **<br>
        {{ $perusahaan->prs_alamat1 }}<br>
        {{ $perusahaan->prs_alamat2 }}
        {{ $perusahaan->prs_alamat3 }}<br>
        NPWP : {{ $perusahaan->prs_npwp }}<br>
        Telp : {{ $perusahaan->prs_telepon }}<br>
        {{ date("d/m/Y") }} {{ $datetime->format('H:i:s') }} {{ $perusahaan->prs_kodemto }} {{ $data[0]->rom_kodekasir }} {{ $data[0]->rom_station }} {{ $data[0]->rom_jenistransaksi }}
    </p>
    <footer>

    </footer>

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
                <td class="left" colspan="2">PENJUALAN</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($total,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">PPN</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($ppn,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">TOTAL ( +PPN )</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($total+$ppn,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">TOTAL ITEM</td>
                <td class="left">:</td>
                <td class="left">{{ count($data) }}</td>
            </tr>
            <tr>
                <td colspan="6" class="center"><h3>*** Terima Kasih ***</h3></td>
            </tr>
            <tr>
                <td class="left" colspan="2">MEMBER</td>
                <td class="left" colspan="4">: {{ $data[0]->cus_namamember }} ( {{ $data[0]->cus_kodemember }} )</td>
            </tr>
            <tr>
                <td class="left" colspan="2">BUKTI RETUR</td>
                <td class="left" colspan="4">: {{ $data[0]->rom_noreferensi }} / {{ $data[0]->rom_nodokumen }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">DRIVER</td>
                <td class="left" colspan="4">: {{ $data[0]->rom_namadrive }}</td>
            </tr>
            </tfoot>
        </table>
    </main>

    <br>
    </body>
    <style>
        @page {
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
        tbody {
            display: table-row-group;
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
@php
    public function print_cetak_ulang_dsp_test(Request $request){
        $perusahaan ='[{"prs_namaperusahaan":"PT.INTI CAKRAWALA CITRA","prs_namacabang":"INDOGROSIR SEMARANG POST","customer":"105005 - PUJI RAHAYU","nomor_faktur":"010.007-23.30540947","tgl_faktur":"2023-05-31 00:00:00","dpp":"6432836.0000","ppn":"707624.0000","ppn_bkp":"707624.0000","ppn_bebas":"0","ppn_dtp":"0","prs_alamat1":"Jln. Testing","prs_alamat2":"Jln. Testing","prs_alamat3":"Jln. Testing","prs_npwp":"NPWP. 13123123123","prs_telepon":"Telp. 13123123123","prs_kodemto":"13123123123"}]';
        $data = '[{"cp_ppn" : 0,"cp_plu" : "","cp_hsat" : 0,"cp_total" : 0,"rom_nodokumen" : "O230674","tgldokumen" : "15\/09\/2023","rom_noreferensi" : 3230001,"rom_tglreferensi" : "08\/02\/2023","rom_prdcd" : "0035870","prd_deskripsipanjang" : "POP MIE MI INSTAN BASO CUP 75g","prd_deskripsipendek" : "POP MIE BASO 75G","kemasan" : "CTN\/24","cus_kodemember" : null,"cus_namamember" : null,"rom_namadrive" : "Putra","rom_kodekasir" : "SOS","rom_station" : "99","rom_jenistransaksi" : "0006","rom_qty" : 2,"rom_qtyselisih" : 2,"rom_hrg" : 4108.0,"rom_ttl" : 8216.0,"trjd_discount" : 0.0000,"prd_prdcd" : "0035870","rom_flagbkp" : "Y","rom_flagbkp2" : "Y","kfp_statuspajak" : "KENA PPN","rom_persenppn" : 11,"total" : 7401.801801801801,"ppn" : 814.198198198199},{"cp_ppn" : 0,"cp_total" : 0,"cp_plu" : "","cp_hsat" : 0,"rom_nodokumen" : "O230675","tgldokumen" : "21\/09\/2023","rom_noreferensi" : 3230008,"rom_tglreferensi" : "13\/02\/2023","rom_prdcd" : "0030180","prd_deskripsipanjang" : null,"prd_deskripsipendek" : "SPRITE 1500 ML","kemasan" : "CTN\/12","cus_kodemember" : null,"cus_namamember" : null,"rom_namadrive" : "Helvin","rom_kodekasir" : "SOS","rom_station" : "99","rom_jenistransaksi" : "0007","rom_qty" : 4,"rom_qtyselisih" : 1,"rom_hrg" : 14234.0,"rom_ttl" : 56934.0,"trjd_discount" : 0.0000,"prd_prdcd" : "0030180","rom_flagbkp" : "Y","rom_flagbkp2" : "Y","kfp_statuspajak" : "KENA PPN","rom_persenppn" : 11,"total" : 51291.891891891886,"ppn" : 5642.108108108114}]';
        $perusahaan = (json_decode($perusahaan))[0];
        $data = json_decode($data);
        $nodoc = "O230675";
        $tgldoc = "21/09/2023";
        $dompdf = new PDF();
        $pdf = PDF::loadview('menu.rpt.retur-struk-pdf',compact(['perusahaan','data','nodoc','tgldoc']));
 
        error_reporting(E_ALL ^ E_DEPRECATED);

        $pdf->output();
        $dompdf = $pdf->getDomPDF()->set_option("enable_php", true);
        $canvas = $dompdf ->get_canvas();
        $dompdf = $pdf;

        return $dompdf->stream('Bukti Penerimaan Barang.pdf');
    }
@endphp
