<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BUKTI PENERIMAAN BARANG RETUR</title>
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

        .table-center thead tr th, .table-center tbody tr td {
            text-align: center
        }

        .tr-not-center td {
            text-align: start!important;
        }

        .inline-block-content > *{
            display: inline-block;
        }

        .title > *{
            text-align: center;
        }

        .body{
            margin-top: 20px;
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
                </div>
                <div style="float: right">
                    <p>Print Date : {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
                </div>
            </div>

            <div class="body">
                <div style="text-align: center">
                    <h3 style="margin: 5px;">BUKTI PENERIMAAN BARANG RETUR</h3>
                    <p style="font-size: .8rem">No : {{ $data[0]->rom_nodokumen }}</p>
                    <p style="font-size: .8rem">Tgl : {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
                </div>
                <div style="margin: 0 0 20px 0">
                    <p style="float: right; text-align: right;">No. BA : {{ $data[0]->noba }}</p>
                    <div style="margin-top: 18px;">
                        <div style="float: left">
                            <p>Toko : {{ $toko[0]->tko_namaomi }}</p>
                        </div>
                        <div style="float: right; text-align: right">
                            <p>No. NRB : {{ $data[0]->rom_noreferensi }}</p>
                        </div>
                    </div>
                </div>
                <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">PLU</th>
                            <th rowspan="2">Nama Barang</th>
                            <th rowspan="2">FRAC</th>
                            <th colspan="2">NRB Toko</th>
                            <th colspan="3">Fisik Barang dan Harga</th>
                        </tr>
                        <tr>
                            <th>QTY</th>
                            <th>Rupiah</th>
                            <th>QTY</th>
                            <th>Avg. Cost</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grand_total = 0;
                        @endphp
                        @foreach ($data as $key => $item)
                        @php
                            $grand_total += $item->ttl_avg;
                        @endphp
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item->rom_prdcd }}</td>
                            <td>{{ $item->prd_deskripsipendek }}</td>
                            <td>{{ $item->prd_frac }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>{{ $item->rom_hrgsatuan }}</td>
                            <td>{{ $item->qtyf }}</td>
                            <td>{{ $item->rom_avgcost }}</td>
                            <td>{{ $item->ttl_avg }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8" style="text-align: right;"><b>Grand Total </b></td>
                            <td style="text-align: center;"><b>{{ $grand_total }}</b></td>
                        </tr>
                    </tfoot>
            </div>
        </div>
    </div>
</body>
</html>
