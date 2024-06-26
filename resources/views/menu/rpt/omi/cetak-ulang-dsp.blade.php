@if(!$data)
    <p style="text-align: center">Data tidak ditemukan</p>
@else
    <!DOCTYPE html>
    <html>
    <head>
        <title>{{$data->filename}}</title>
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

    <h5 style="margin-top: 0px;margin-bottom: 0px;"><b>{{ $data->data->data_perusahaan->NPWP }}</b></h5>
    <hr>
    <h2  style="margin-top: -15px;margin-bottom: -15px;text-align: center;">
        <b>DAFTAR STRUK PENJUALAN</b>
    </h2>
    <br>
    <p style="margin-top:0px;">
        <h5 style="text-align:left; margin:0px; display:inline;"><b>No. DSP : {{ $data->data->list_data[0]->NoDSP }}</b></h5>
        <h5 style="text-align:right; margin-left:110px; display:inline;"> <b  style="text-align:right; ">{{$data->data->list_data[0]->JamDSP}}</b></h5>
        <h5 style="text-align:left; margin:0px;"><b>No. PB&nbsp;&nbsp; : {{ $data->nopb }}</b></h5>
        <h5 style="text-align:left; margin:0px;"><b>No. Koli&nbsp;: {{ $data->data->list_data[0]->NoKoli }}</b></h5>
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
                $jmlh = 0;
                $jmlh_tampa_ppn = 0;
                $jmlh_kena_cukai = 0;
                $jmlh_ppn_dtp = 0;
                $jmlh_bebas_ppn = 0;
                $TotalBrg = 0;
                $ppn = 0;
                $DPPBBS = 0;
                $DPPRPH = 0;
                $DPPDTP = 0;
                $DPPCUK = 0;
                $DPPTKP = 0;
                $DPPRPH = 0;
                $DPPDTP = 0;
                $valueTaxBrg = 0;
                $TaxBrg = 0;
                $TaxBrgDtp = 0;
                $TaxBrgBbs = 0;
                $valueRpDF = 0;
                $RpDF = 0;
                $RpBrgPpn = 0;
                $valuetaxDF = 0;
                $taxDF = 0;
                $TotalDF = 0;
                $total_bayar = 0;
            @endphp
            @foreach($data->data->list_data as $d)
                <tr>
                    <td colspan="4" class="left">{{ $d->dataKoli->desk }}</td>
                    <td colspan="2" class="right">{{ $d->dataKoli->plu }}</td>
                </tr>
                <tr>
                    <td class="right">{{ $d->dataKoli->qt }}</td>
                    <td class="right" colspan="2">{{ number_format((int)$d->dataKoli->hg,0,'.',',') }}</td>
                    <td class="center">{{ number_format(0,0,'.',',') }}</td>
                    <td class="right" colspan="2">{{ number_format( ((int)$d->dataKoli->hg*(int)$d->dataKoli->qt ),0,'.',',') }}</td>
                </tr>

                @php
                    if(str_contains($d->dataKoli->plu, '*   ')){
                        $jmlh_tampa_ppn += 1;
                    }elseif(str_contains($d->dataKoli->plu, '**  ')){
                        
                        $jmlh_kena_cukai += 1;
                    }elseif(str_contains($d->dataKoli->plu, '*** ')){
                        
                        $jmlh_ppn_dtp += 1;
                    }elseif(str_contains($d->dataKoli->plu, '*****')){
                        $jmlh_bebas_ppn += 1;
                    }else{
                        $jmlh += 1;
                    }
                    $TotalBrg += ( (int)$d->dataKoli->hg*(int)$d->dataKoli->qt );
                    $ppn += 0;
                    $DPPBBS += $d->DPPBBS;
                    $DPPRPH += $d->DPPRPH;
                    $DPPDTP += $d->DPPDTP;
                    $DPPCUK += $d->DPPCUK;
                    $DPPTKP += $d->DPPTKP;
                    $DPPRPH += $d->DPPRPH;
                    $DPPDTP += $d->DPPDTP;
                    $valueTaxBrg = $d->DPPRPH*$d->ppnRatePrd;
                    $TaxBrg += $d->DPPRPH*$d->ppnRatePrd;
                    $valueTaxBrgDtp = $d->DPPDTP*$d->ppnRatePrd;
                    $TaxBrgDtp += $d->DPPDTP*$d->ppnRatePrd;
                    $TaxBrgBbs += $d->DPPBBS*$d->ppnRatePrd;
                    $valueRpDF = $d->pdFee;
                    $RpDF += $d->pdFee;
                    $RpBrgPpn += $d->DPPRPH+$valueTaxBrg;
                    $valuetaxDF = $d->DPPRPH * $valueRpDF;
                    $taxDF += $d->DPPRPH * $RpDF;
                    $TotalDF += $valueRpDF+$valuetaxDF ;
                    $total_bayar += $d->DPPRPH+$d->DPPBBS+$d->DPPDTP+$d->DPPTKP+$d->DPPCUK+$d->DPPCUK+$valueTaxBrg
                @endphp
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td class="left" colspan="2">Harga Jual</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($TotalBrg,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2"><span>{{$jmlh}}</span> Item PPN</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($RpBrgPpn,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">DPP</td>
                <td class="left" >: {{ number_format($DPPRPH,0,'.',',') }}</td>
                <td class="left" colspan="2">PPN</td>
                <td class="right" >: {{ number_format($TaxBrg,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="3"><span>*&nbsp;&nbsp;&nbsp;: {{$jmlh_tampa_ppn}}</span> Item Tampa PPN</td>
                <td class="left">:</td>
                <td class="right" colspan="2">{{ number_format($DPPTKP,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="3"><span>**&nbsp;&nbsp;: {{$jmlh_kena_cukai}}</span> Item Kena Cukai</td>
                <td class="left">:</td>
                <td class="right" colspan="2">{{ number_format($DPPCUK,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="3"><span>***&nbsp;: {{$jmlh_ppn_dtp}}</span> Item PPN DTP</td>
                <td class="left">:</td>
                <td class="right" colspan="2">{{ number_format($DPPDTP,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">DPP</td>
                <td class="left" >: {{ number_format($DPPDTP,0,'.',',') }}</td>
                <td class="left" colspan="2">PPN</td>
                <td class="right" >: {{ number_format($TaxBrgDtp,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="3"><span>****: {{$jmlh_bebas_ppn}}</span> Item PPN Bebas</td>
                <td class="left">:</td>
                <td class="right" colspan="2">{{ number_format($TaxBrgBbs,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">DPP</td>
                <td class="left" >: {{ number_format($DPPBBS,0,'.',',') }}</td>
                <td class="left" colspan="2">PPN</td>
                <td class="right" >: {{ number_format(0,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">Distribution Fee</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($RpDF,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">PPN %</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ (float) $data->data->list_data[0]->ppnRatePersh*100  }} %</td>
            </tr>
            <tr>
                <td class="left" colspan="2">TOTAL ( Distribution Fee+PPN )</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($TotalDF,0,'.',',') }}</td>
            </tr>
            <tr>
                <td class="left" colspan="2">Jumlah Yang Harus Dibayar</td>
                <td class="left">:</td>
                <td class="right" colspan="3">{{ number_format($total_bayar,0,'.',',') }}</td>
            </tr>
            <tr>
                <td colspan="6"><hr></td>
            </tr>
            <tr>
                <td class="left" colspan="2">CHECKER</td>
                <td class="left" >: {{$data->data->list_data[0]->NamaChecker}}</td>
                <td class="right" colspan="2"># {{count($data->data->list_data)}}</td>
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
                <td class="left"><b>{{ $data->data->list_data[0]->NamaOMI }}-{{ $data->data->list_data[0]->KodeMember}} (MEMBER)</b></td>
                <td class="right"  colspan="3"></td>
            </tr>
            <tr>
                <td colspan="6" class="center"><h3>*** Terima Kasih ***</h3></td>
            </tr>
            <tr>
                <td colspan="6" class="center">
                    <h2 style="margin-bottom:-25px;">= {{$data->data->data_perusahaan->NamaCab2}} =</h2>
                    <br style="margin-bottom:-25px;">
                    <h4 style="margin-bottom:-25px;"> {{$data->data->data_perusahaan->AlamatCab1}},{{$data->data->data_perusahaan->AlamatCab2}} </h4>
                    <h4></h4>
                </td>
            </tr>  
            <tr style="width:80%; margin-top:-15px;">>
                <td class="left" colspan="2">
                    <h3>{{$data->data->data_perusahaan->NamaPersh}}</h3>
                    <h5 style="width:80%; margin-top:-15px;">
                            <p style="margin-top:-13px;"><b>{{-- $data->data->data_perusahaan->AlamatPersh1 --}} JL. ANCOL BARAT I NO.9-10 </b></p>
                            <p style="margin-top:-13px;"><b>{{-- $data->data->data_perusahaan->AlamatPersh2 --}} ANCOL PADEMANGAN </b></p>
                            <p style="margin-top:-13px;"><b>{{-- $data->data->data_perusahaan->AlamatPersh3 --}} JAKARTA UTARA, DKI </b></p>
                            <p style="margin-top:-13px;"><b>{{-- $data->data->data_perusahaan->AlamatPersh3 --}} JAKARTA 14430</b></p>
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
            size: 298pt {{ 370+(count($data->data->list_data)*28) }}pt;
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
