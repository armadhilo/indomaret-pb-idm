<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BERITA ACARA PENGEMBALIAN DANA</title>
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
            <div class="header" style="border-bottom: 5px double black">
                <h2 style="text-align: center; margin: 0 0 10px 0">BERITA ACARA PENGEMBALIAN DANA<br> TRANSAKSI PESANAN MEMBER MERAH SPI</h2>
                <div style="margin: auto; width: 100%; text-align: center; padding-bottom: 15px; margin-top: 15px">
                    <table style="width: 65%; margin: 0 auto;">
                        <tr>
                            <td>Tanggal</td>
                            <td>:</td>
                            <td style="width: 180px">{{ $tglBA }}</td>
                        </tr>
                        <tr>
                            <td>SPI</td>
                            <td>:</td>
                            <td style="width: 180px">{{ session('NAMACABANG') }}</td>
                        </tr>
                        <tr>
                            <td>Nomor</td>
                            <td>:</td>
                            <td style="width: 180px">{{ $noBA }}</td>
                        </tr>
                        <tr>
                            <td rowspan="3" style="vertical-align: top;">Nominal Pengembalian Dana * </td>
                            <td rowspan="3" style="vertical-align: top;">:</td>
                            <td style="width: 180px;">1. < Rp. 1.000.000</td>
                        </tr>
                        <tr>
                            <td>2. Rp. 1.000.000 s/d Rp. 5.000.000</td>
                        </tr>
                        <tr>
                            <td>3. > Rp. 5.000.000</td>
                        </tr>
                        <tr>
                            <td>Toko Igr. Induk</td>
                            <td>:</td>
                            <td style="width: 180px">{{ "INDUK " . session('NAMACABANG') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="body">
                <div style="margin-top: -15px">
                    <div style="padding: 0px 0 0 25px">
                        <p>Berikut kami sampaikan permintaan pengembalian dana (karena ada item pesanan Member Merah yang stock out di SPI) dari Finance Igr. HO, yang sudah disetujui oleh SPI Leader/Asst. yang melakukan edit/cancel pesanan.</p>
                        <p style="margin: 15px 0">Berikut summary data pengembalian dana :</p>
                    </div>
                </div>
                <div style="margin: auto; padding: 0 55px">
                    <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                        <thead>
                            <tr>
                                <th>Tujuan Rekening <br> Pengembalian Dana</th>
                                <th>Jumlah Transaksi <br> Pengembalian Dana</th>
                                <th>Jumlah Rp. <br> Pengembalian Dana</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                              $totalTrans = 0;  
                              $totalRefund = 0;  
                            @endphp
                            @foreach ($data as $item)
                            <tr>
                                <td>{{ $item->tipeBayar }}</td>
                                <td>{{ $item->jmlTrans }}</td>
                                <td>{{ $item->jmlRefund }}</td>
                            </tr>
                            @php
                              $totalTrans = $item->jmlTrans;  
                              $totalRefund = $item->jmlRefund;  
                            @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th>{{ $totalTrans }}</th>
                                <th>{{ $totalRefund }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <p style="margin: 25px 0 15px 0">Untuk rincian data pengembalian dana tsb. di atas dapat dilihat di LPDS (Listing Pengembalian Dana Transaksi SPI) terlampir.</p>
                <p>Demikian Berita Acara pengembalian dana ini dibuat dengan sebenar-benarnya.</p>
                <div style="padding: 40px 0 10px">
                    <table style="width: 100%" class="table-center">
                        <tr>
                            <td style="padding-bottom: 70px;">Disetujui</td>
                            <td style="padding-bottom: 70px">Disetujui</td>
                            <td style="padding-bottom: 70px">Disetujui</td>
                        </tr>
                        <tr>
                            <td>(Regional Sr. Mgr.) **</td>
                            <td>(Store Mgr. Igr. Induk) **</td>
                            <td>(SPI Leader/Asst) **</td>
                        </tr>
                    </table>
                </div>
                <ul class="custom-list">
                    <li class="hash">Pilih Salah Satu</li>
                    <li class="double-hash">Sesuai ketentuan Pejabat Igr. yang bertanggungjawab untuk mendatangani dokumen BA pengembalian dana</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
