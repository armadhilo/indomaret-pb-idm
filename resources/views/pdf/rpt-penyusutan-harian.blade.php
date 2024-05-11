<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REPORT PENYUSUTAN HARIAN</title>
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
                    <p style="font-size: .8rem;"><b>{{ strtoupper("PT INTI CAKRAWALA CITRA") }}</b></p>
                    <p style="font-size: .8rem;"><b>{{ strtoupper($perusahaan->prs_namacabang) }}</b></p>
                </div>
                <div style="float: right">
                    <p>Tanggal Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                    <p>Pukul Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                    <p>User ID : {{ session('userid') }}</p>
                    <p>Halaman : <span class="page-number"></span></p>
                </div>
            </div>

            <div class="body">
                <div style="text-align: center; margin-top: 35px;">
                    <h3 style="margin: 0">Laporan Usulan Penyusutan Harian Item(s) Perishable Klik Indogrosir</h3>
                    <h5 style="margin: 10px 0 0 0">Tanggal: {{ date('d - m - Y', strtotime($request->tanggal_trans)) }}</h5>
                </div>
                <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">PLU</th>
                            <th rowspan="2">Deskripsi</th>
                            <th colspan="3">Berat (gr)</th>
                        </tr>
                        <tr>
                            <th>Data Pesanan</th>
                            <th>Realisasi Picking</th>
                            <th>Usulan Susut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $item)
                            <td style="text-align: center">{{ $key + 1 }}</td>
                            <td style="text-align: center">{{ $item->pluigr }}</td>
                            <td style="text-align: center">{{ $item->deskripsi }}</td>
                            <td style="text-align: center">{{ $item->qty_order }}</td>
                            <td style="text-align: center">{{ $item->qty_real }}</td>
                            <td style="text-align: center">{{ $item->penyusutan }}</td>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
