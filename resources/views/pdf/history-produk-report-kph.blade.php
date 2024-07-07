<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REPORT HASIL PERHITUNGAN KPH MEAN</title>
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
    <div class="container">
        <div style="width: 100%">
            <div class="body">
                <div style="text-align: center;">
                    <h3 style="margin: 5px;"><u>REPORT HASIL PERHITUNGAN KPH MEAN</u></h3>
                </div>
                <div style="margin: auto; width: 100%; text-align: center; padding-bottom: 15px; margin-top: 40px!important">
                    <table style="width: 100%; margin: 0 auto;">
                        <tr>
                            <td>
                                <div>
                                    <p style="display: inline-block; width: 130px"><b>Periode</b></p>
                                    <p style="display: inline-block"><b style="margin-right: 8px">:</b>{{ $periode }}</p>
                                </div>
                                <div style="margin-top: 5px">
                                    <p style="display: inline-block; width: 130px"><b>Estimasi Toko Baru</b></p>
                                    <p style="display: inline-block"><b style="margin-right: 8px">:</b>{{ $jmlToko }}</p>
                                </div>
                            </td>
                            <td>
                                <div style="float: right">
                                    <p style="display: inline-block; width: 130px"><b>Jumlah Toko Diproses</b></p>
                                    <p style="display: inline-block"><b style="margin-right: 8px">:</b>{{ $jmltokoProses[0]->jml_toko_proses }}</p>
                                </div>
                            </td>
                            
                        </tr>
                    </table>
                </div>
                <table border="1" style="border-collapse: collapse; margin-top:0px" class="table-center" cellpadding="2">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>PLU IDM</th>
                            <th>PLU IGR</th>
                            <th>DESKRIPSI BARANG</th>
                            <th>KPH MEAN</th>
                            <th>3X KPH MEAN</th>
                            <th>4X KPH MEAN</th>
                            <th>MINOR SUPP</th>
                            <th>MINOR IDM</th>
                            <th>MINOR KPH</th>
                            <th>TAG IGR</th>
                            <th>TAG IDM</th>
                            <th>FRAC IGR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item->pluidm }}</td>
                            <td>{{ $item->pluigr }}</td>
                            <td>{{ $item->deskripsi }}</td>
                            <td>{{ $item->kph }}</td>
                            <td>{{ $item->kph_3 }}</td>
                            <td>{{ $item->kph_4 }}</td>
                            <td>{{ $item->minor_igr }}</td>
                            <td>{{ $item->minor_crm }}</td>
                            <td>{{ $item->minor }}</td>
                            <td>{{ $item->tag_prd }}</td>
                            <td>{{ $item->tag_crm }}</td>
                            <td>{{ $item->frac }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
