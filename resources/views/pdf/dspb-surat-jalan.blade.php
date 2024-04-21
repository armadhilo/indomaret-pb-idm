<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REKAP SURAT JALAN PENGIRIMAN ROTI</title>
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
                    <h3 style="margin: 5px;">Rekap Surat Jalan Pengiriman Roti</h3>
                    <h4 style="margin: 5px">No : {{ $data[0]->norekap }}</h4>
                    <h4 style="margin: 5px">Tgl : {{ \Carbon\Carbon::parse($data[0]->tgl_dspb)->format('d/m/Y') }}</h4>
                    <h1>@ENCRYPT</h1>
                </div>
                <div style="margin: 0 0 20px 0">
                    <div style="margin-top: 18px;">
                        <div style="float: left">
                            <p>Delivery Van : {{ $data->kdcluster }}</p>
                        </div>
                    </div>
                </div>
                <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th colspan="2">Toko IDM</th>
                            <th rowspan="2">Kode Barcode</th>
                            <th rowspan="2">Tanda Tangan Chief of Store/Asst. dan Stampel Toko IDM</th>
                        </tr>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $item)
                            <td style="text-align: center">{{ $key + 1 }}</td>
                            <td style="text-align: center">{{ $item->kodetoko }}</td>
                            <td style="text-align: center">{{ $item->namatoko }}</td>
                            <td style="text-align: center"><h1>@ENCRYPT</h1></td>
                            <td style="text-align: center"></td>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
