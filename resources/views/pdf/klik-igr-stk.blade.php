<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BUKTI SERAH TERIMA KARDUS</title>
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
                    <p style="font-size: .8rem;"><b>{{ strtoupper("PT. INTI CAKRAWALA CITRA") }}</b></p>
                    <p style="font-size: .8rem;"><b>{{ strtoupper("TOKO IGR / NOMOR IGR") }}</b></p>
                </div>
                <div style="float: right">
                    <p>Tanggal Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                    <p>Jam Cetak : {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                    <p>UserID : {{ session("userid") }}</p>
                    <p>Halaman : <span class="page-number"></span></p>
                </div>
            </div>

            <div class="body">
                <div style="text-align: center; margin-top: 50px">
                    <h2 style="margin: 0 0 10px 0">Bukti Serah Terima Kardus</h2>
                    <p style="font-size: .8rem; margin-bottom: 3px">No : 21/12/12</p>
                    <p style="font-size: .8rem; margin-bottom: 3px">Tanggal : 21/12/12</p>
                </div>
                <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>No. Koli</th>
                            <th>No. Struk</th>
                            <th>Jumlah Item(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4">NO.PESANAN</td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>0923148</td>
                            <td>302948</td>
                            <td>21</td>
                        </tr>
                    </tbody>
                </table>
                <table style="width: 80%; margin: 25px auto" class="table-center">
                    <tr>
                        <td style="font-weight: bold; margin: 0; border: 1px solid black">Diterima</td>
                        <td style="font-weight: bold; margin: 0; border: 1px solid black">Diserahkan</td>
                        <td style="font-weight: bold; margin: 0; border: 1px solid black">Dicetak</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding-bottom: 70px; border-left: 1px solid black;"></td>
                        <td style="font-weight: bold; padding-bottom: 70px; border-left: 1px solid black;"></td>
                        <td style="font-weight: bold; padding-bottom: 70px; border-left: 1px solid black; border-right: 1px solid black"></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; border: 1px solid black">Duty Mgr.</td>
                        <td style="font-weight: bold; border: 1px solid black">Helper Igr.</td>
                        <td style="font-weight: bold; border: 1px solid black">Logistic Igr.</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
