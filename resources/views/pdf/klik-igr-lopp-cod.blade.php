<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LAPORAN PESANAN EXPIRED</title>
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
    <div class="container">
        <div style="width: 100%">
            <div class="header">
                <div style="float: left">
                    <p style="font-size: .8rem;"><b>{{ strtoupper( session("flagIGR") ? 'INDOGROSIR' : 'STOCK POINT INDOGROSIR' ) }}</b></p>
                    <p style="font-size: .8rem;"><b>{{ strtoupper(session("NAMACABANG")) }}</b></p>
                </div>
                <div style="float: right">
                    <p>Tanggal Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                    <p>Jam Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                    <p>User ID : {{ session("userid") }}</p>
                </div>
            </div>

            <div class="body">
                <div style="text-align: center; margin-top: 30px;">
                    <h3 style="margin: 5px;">LISTING OUTSTANDING PENERIMAAN PEMBAYARAN -<br> CASH ON DELIVERY</h3>
                    <h6 style="margin: 10px 0 10px 0; font-size: .8rem; font-weight: normal">Priode Transaksi s/d {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d-m-Y') }}</h6>
                </div>
                <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th rowspan="2">No.</th>
                            <th rowspan="2">Kode Member</th>
                            <th rowspan="2">Kode Pesanan</th>
                            <th colspan="2">DSP</th>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nilai (Rp.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item->kode_member }}</td>
                            <td>{{ $item->kode_pesanan }}</td>
                            <td>{{ $item->tgl_dsp }}</td>
                            <td>{{ $item->nilai_dsp }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
