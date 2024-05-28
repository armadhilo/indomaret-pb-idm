<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LISTING DELIVERY</title>
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
                    <p style="font-size: .8rem;"><b>{{ session('flagIGR') ? 'INDOGROSIR' : 'STOCK POINT INDOGROSIR' }}</b></p>
                    <p style="font-size: .8rem;"><b>{{ strtoupper( session('NAMACABANG')) }}</b></p>
                </div>
                <div style="float: right">
                    <p>Tanggal Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                    <p>Jam Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                    <p>UserID : {{ session("userid") }}</p>
                </div>
            </div>

            <div class="body">
                <div style="text-align: center; margin-top: 50px">
                    <h2 style="margin: 0 0 10px 0">LISTING DELIVERY</h2>
                    <p style="font-size: .8rem; margin-bottom: 3px">No : {{ $noListing ? $noListing : '-' }}</p>
                    <p style="font-size: .8rem; margin-bottom: 3px">Tanggal : {{ $tglListing }}</p>
                </div>
                <table style="padding-left: 50px">
                    <tr>
                        <td style="width: 130px">No. Polisi Delivery Van</td>
                        <td>: {{ $nopol }}</td>
                    </tr>
                    <tr>
                        <td style="width: 130px">Nama Driver</td>
                        <td>: {{ $driver }}</td>
                    </tr>
                    <tr>
                        <td style="width: 130px">Nama Deliveryman</td>
                        <td>: {{ $deliveryman }}</td>
                    </tr>
                </table>
                <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th rowspan="2">No.</th>
                            <th rowspan="2">Kode - Nama Member <br> Alamat</th>
                            <th rowspan="2">Kode Pesanan</th>
                            <th colspan="2">DSP / SP</th>
                        </tr>
                        <tr>
                            <th>No. </th>
                            <th>Nilai (Rp.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_nilai_sp = 0;
                        @endphp
                        @foreach ($data as $key => $item)
                            <tr>
                                <td colspan="5"><b>Alterantif Penerimaan Pembayaraan : {{ $item->tipebayar }}</b></td>
                            </tr>
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{!! $item->kode_member . ' - ' . $item->nama_member . '<br>' . $item->alamat !!}</td>
                                <td>{{ $item->kode_pesanan }}</td>
                                <td>{{ $item->no_sp }}</td>
                                <td>{{ $item->nilai_sp }}</td>
                            </tr>

                            @php
                                $total_nilai_sp += $item->nilai_sp;
                            @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="font-weight: bold; background: rgb(230, 230, 230)" colspan="4">SubTotal</td>
                            <td style="font-weight: bold; background: rgb(230, 230, 230)">{{ $total_nilai_sp }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; background: rgb(230, 230, 230)" colspan="4">Total</td>
                            <td style="font-weight: bold; background: rgb(230, 230, 230)">{{ $total_nilai_sp }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
