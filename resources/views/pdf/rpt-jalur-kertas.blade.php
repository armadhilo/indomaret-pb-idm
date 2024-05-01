<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REKAP SURAT JALUR KERTAS</title>
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
                    <p style="font-size: .8rem;"><b>{{ strtoupper($perusahaan->prs_namacabang) }}</b></p>
                </div>
                <div style="float: right">
                    <p>Tanggal Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                    <p>Pukul Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                    <p>Halaman : <span class="page-number"></span></p>
                </div>
            </div>

            <div class="body">
                <div style="text-align: center">
                    <h3 style="margin: 30px 5px 5px 5px;">Draft Item Picking Jalur Kertas KlikIndogrosir</h3>
                </div>
                <div style="margin: 18px 0 40px 0">
                    <div style="float: left">
                        <p style="margin-bottom: 5px;">Nomor PB : {{ $nopb }}</p>
                        <p style="margin-bottom: 5px;">Kode Member : {{ $kodemember }}</p>
                    </div>
                    <div style="float: right; text-align: right">
                        <p style="margin-bottom: 5px;">No. Trans : {{ $notrans }}</p>
                        <p style="margin-bottom: 5px;">Tgl. Trans : {{ $tanggaltrans }}</p>
                    </div>
                </div>
                <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">PLU</th>
                            <th rowspan="2">Deskripsi</th>
                            <th colspan="2">Konversi</th>
                            <th colspan="2">Qty. Pesanan</th>
                            <th colspan="2">Qty. Toleransi</th>
                        </tr>
                        <tr>
                            <th>Pcs</th>
                            <th>Gram</th>
                            <th>Pcs</th>
                            <th>Gram</th>
                            <th>Awal</th>
                            <th>Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $item)
                            <td style="text-align: center">{{ $key + 1 }}</td>
                            <td style="text-align: center">{{ $item->plu }}</td>
                            <td style="text-align: center">{{ $item->deskripsi }}</td>
                            <td style="text-align: center">{{ $item->konversi_pcs }}</td>
                            <td style="text-align: center">{{ $item->konversi_gram }}</td>
                            <td style="text-align: center">{{ $item->qty_pcs }}</td>
                            <td style="text-align: center">{{ $item->qty_gram }}</td>
                            <td style="text-align: center">{{ $item->toleransi_awal }}</td>
                            <td style="text-align: center">{{ $item->toleransi_akhir }}</td>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
