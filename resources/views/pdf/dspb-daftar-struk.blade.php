<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DSPB / SJ-R</title>
    <style>
        body{
            font-family: sans-serif;
        }
        table{
            width: 100%;
        }
        table th{
            font-size: 11px;
            background: #e9e7e7;
        }
        table td{
            font-size: 11px;
        }
        p{
            font-size: .7rem;
            font-weight: 400;
            margin: 0;
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
        }
        .italic {
            font-style: italic;
        }

        .table-center thead tr th, .table-center tbody tr td, .table-center tfoot tr td {
            text-align: center!important;
        }

        .inline-block-content > *{
            display: inline-block;
        }

        .title > *{
            text-align: center;
        }

        .body{
            margin-top: 40px;
        }

        .text-center{
            text-align: center;
        }

        .page-number:before {
            content: counter(page);
        }

        .footer-table {
            width: 100%;
        }

        .footer-column {
            width: 25%;
            text-align: center;
            box-sizing: border-box;
        }

        .footer-title {
            margin-bottom: 100px;
        }

        .footer-text {
            margin-bottom: 100px;
        }

        .footer-hr {
            color: black;
            width: 85%;
            margin: auto;
            margin-bottom: 30px;
        }

        .footer-hr:last-child {
            margin-bottom: 0;
        }

    </style>
</head>
<body>
    <div class="container-fluid">
        <div style="width: 100%">
            <div class="header">
                <div style="float: left;">
                    <p style="font-size: .8rem;"><b>{{ strtoupper($namaCabang) }}</b></p>
                    <p style="font-size: .8rem;"><b>{{ strtoupper($toko->tko_namaomi) }}</b></p>
                </div>
                <div style="float: right">
                    <p>Tanggal Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                    <p>Jam Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                </div>
            </div>

            <div class="body">
                <div style="text-align: center">
                    <h3 style="margin: 5px;">DSPB / SJ-R</h3>
                    <h5 style="margin: 5px;">Daftar Struk Permintaan Barang / Surat Jalan Roti</h5>
                    <h4 style="margin: 5px">No : {{ $header[0]->dspb }}</h4>
                    <h4 style="margin: 5px">Tgl : {{ \Carbon\Carbon::parse($header[0]->tgl_dspb)->format('d/m/Y') }}</h4>
                    <h1>@ENCRYPT</h1>
                </div>
                <div style="margin: 0 0 20px 0">
                    <div style="margin-top: 18px;">
                        <div style="float: left">
                            <p>Toko : {{ $toko->tko_namaomi }}</p>
                        </div>
                        <div style="float: right; text-align: right">
                            <p>Cluster : {{ $cluster->cls_kode }}</p>
                        </div>
                    </div>
                </div>
                <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th colspan="2">DSP</th>
                            <th colspan="3">Kts. (pcs)</th>
                        </tr>
                        <tr>
                            <th>NOMOR</th>
                            <th>TIPE BAKERY</th>
                            <th>JML. ITEM</th>
                            <th>ROTI</th>
                            <th>KRAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_jmlItem = 0;
                        @endphp
                        @foreach ($data as $key => $item)
                        @php
                            $total_jmlItem += $item->jmlitem;
                        @endphp
                            <td style="text-align: center">{{ $key + 1 }}</td>
                            <td style="text-align: center">{{ $item->nopb }}</td>
                            <td style="text-align: center">{{ $item->tipe }}</td>
                            <td style="text-align: center">{{ $item->jmlitem }}</td>
                            <td style="text-align: center">{{ $item->item }}</td>
                            <td style="text-align: center">{{ $item->krat }}</td>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right;"><b>Total </b></td>
                            <td style="text-align: center;"><b>{{ $total_jmlItem }}</b></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="footer" style="margin-top: 25px">
                <table class="footer-table">
                    <tr>
                        <td class="footer-column">
                            <p class="footer-title">Penerima Koli Barang</p>
                            <hr class="footer-hr">
                            <p class="footer-text">Chief of Store/Asst. (IDM)</p>
                            <hr class="footer-hr">
                        </td>
                        <td class="footer-column">
                            <p class="footer-title">Penerima Dokumen</p>
                            <hr class="footer-hr">
                            <p class="footer-text">Chief of Delivery/Asst. (IDM)</p>
                            <hr class="footer-hr">
                        </td>
                        <td class="footer-column">
                            <p class="footer-title">Pemeriksa</p>
                            <hr class="footer-hr">
                            <p class="footer-text">Issuing Supv./Jr Spuv (IGR)</p>
                            <hr class="footer-hr">
                        </td>
                        <td class="footer-column">
                            <p class="footer-title">Pembuat</p>
                            <hr class="footer-hr">
                            <p class="footer-text">EDP Clerk - II (IGR)</p>
                            <hr class="footer-hr">
                        </td>
                    </tr>
                </table>

            </div>

        </div>
    </div>
</body>
</html>
