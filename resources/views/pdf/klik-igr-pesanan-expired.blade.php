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
    <div class="container-fluid">
        <div style="width: 100%">
            <div class="header">
                <div style="float: left;">
                    <p style="font-size: .8rem;"><b>{{ strtoupper(session("flagIGR") ? "Member KlikIgr" : "MMS") }}</b></p>
                    @if(session("flagIGR"))
                    <p style="font-size: .8rem;"><b>{{ strtoupper(session('KODECABANG') . " - " . session("NAMACABANG")) }}</b></p>
                    @else 
                    <p style="font-size: .8rem;"><b>{{ strtoupper($namaInduk[0]->nama . "<br>" . session("KODECABANG") . " - " . session("NAMACABANG")) }}</b></p>
                    @endif
                </div>
                <div style="float: right">
                    <p>Halaman : <span class="page-number"></span></p>
                    <p>Tanggal Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                    <p>Jam Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                    <p>UserID : {{ session("userid") }}</p>
                </div>
            </div>

            <div class="body">
                <div style="text-align: center; margin-top: 50px">
                    <h3 style="margin: 5px;">Laporan Pesanan {{ session("flagIGR") ? "Member KlikIgr" : "MMS" }} yang Expired</h3>
                    <h4 style="margin: 5px">Tgl : {{ \Carbon\Carbon::parse($request->periodeAwal)->format('d/m/Y') . " S/D " . \Carbon\Carbon::parse($request->periodeAkhir)->format('d/m/Y')}}</h4>
                </div>
                <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th colspan="2">{{ strtoupper(session("flagIGR") ? "Member KlikIgr" : "MMS") }}</th>
                            <th colspan="3">Pesanan</th>
                        </tr>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Tanggal</th>
                            <th>Kode</th>
                            <th>Nilai Transaksi (Rp.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $sum_nilai_pb = 0;   
                        @endphp
                        @foreach ($dtItem as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item->kode_member }}</td>
                            <td>{{ $item->nama_member }}</td>
                            <td>{{ $item->tgl_pb }}</td>
                            <td>{{ $item->no_pb }}</td>
                            <td>{{ $item->nilai_pb }}</td>
                        </tr>
                        @php
                        $sum_nilai_pb += (int)$item->nilai_pb;   
                        @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">TOTAL LOST OF SALES</td>
                            <td>{{ $sum_nilai_pb }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
