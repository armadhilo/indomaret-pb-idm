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

        .custom-list {
            list-style: none; 
            padding-left: 0; 
            margin-top: 35px;
        }
        .custom-list li{
            font-size: .7rem;
            font-style: italic;
        }
        .custom-list li::before {
            content: attr(data-symbol); 
            padding-right: 10px; 
        }

        .custom-list li.star::before {
            content: '* ';
        }
        .custom-list li.hash::before {
            content: '# ';
        }
        .custom-list li.double-hash::before {
            content: '##';
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
                <div style="float: right">
                    <p>Tanggal Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                    <p>Jam Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                </div>
            </div>

            <div class="body">
                <div style="text-align: center; margin-top: 20px">
                    <h3 style="margin: 5px;">List Item yang sudah dipicking namun belum masuk Intransit</h3>
                </div>
                <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Div</th>
                            <th>Dept</th>
                            <th>Kat</th>
                            <th>PLU</th>
                            <th>Deskripsi</th>
                            <th>Frac</th>
                            <th>Total (in pcs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $item)
                        <tr>
                            <td>{{ $item->no }}</td>
                            <td>{{ $item->div }}</td>
                            <td>{{ $item->dept }}</td>
                            <td>{{ $item->kat }}</td>
                            <td>{{ $item->plu }}</td>
                            <td>{{ $item->deskripsi }}</td>
                            <td>{{ $item->frac }}</td>
                            <td>{{ $item->total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
