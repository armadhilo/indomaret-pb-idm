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
            <div class="header" style="border-bottom: 5px double black">
                <h2 style="text-align: center; margin: 0 0 10px 0">FORMULIR PENGEMBALIAN BARANG</h2>
                <div style="margin: auto; width: 100%; text-align: center; padding-bottom: 15px">
                    <table style="width: 45%; margin: 0 auto;">
                        <tr>
                            <td>Nomor</td>
                            <td>:</td>
                            <td style="width: 180px"></td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>:</td>
                            <td style="width: 180px"></td>
                        </tr>
                        <tr>
                            <td>Kode / Nama SPI</td>
                            <td>:</td>
                            <td style="width: 180px">{{ session("KODECABANG") . " / " . session("NAMACABANG") }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="body">
                <div style="margin-top: -15px">
                    <p>Berikut kami sampaikan permintaan pengembalian barang sbb</p>
                    <div style="padding: 10px 0 10px 25px;">
                        <table style="width: 70%">
                            <tr>
                                <td style="width: 150px">Nomor AWB</td>
                                <td>:</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td style="width: 150px">Alasan Pengembalian</td>
                                <td>:</td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                    <p>Berikut Rincian Pengembalian Barang</p>
                </div>
                <div style="margin: auto; padding: 0 25px">
                    <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                        <thead>
                            <tr>
                                <th style="width: 50px">No.</th>
                                <th>PLU</th>
                                <th style="width: 410px">Nama Barang</th>
                                <th style="width: 60px">Kuantitas (Pcs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="height: 340px"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">TOTAL</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <table style="margin-top: 10px; margin-bottom: 25px">
                        <tr>
                            <td style="width: 300px">
                                Nilai (Rp.) barang dagangan yang dikembalikan ke {{ $type === "SPI" ? 'SPI*' : 'Toko Igr*' }}
                            </td>
                            <td style="width: 30px">:</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="width: 300px">
                                Nilai (Rp.) yang seharusnya dibayar oleh {{ $type === "SPI" ? 'member*' : 'Member Toko Igr*' }}
                            </td>
                            <td style="width: 30px">:</td>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <p>Demikian formulir pengembalian barang ini dibuat dengan sebenar-benarnya dan sudah dipastikan kebenarannya data-nya oleh {{ $type === "SPI" ? 'Member Merah SPI' : 'Member Toko IGR' }}. Dalam hal ada kesalahan pengisian data bukan tanggung jawab dari pihak {{ $type === "SPI" ? 'SPI' : 'Toko Igr' }}</p>
                <div style="padding: 40px 0 10px">
                    <table style="width: 100%" class="table-center">
                        <tr>
                            <td style="padding-bottom: 70px;">Diterima</td>
                            <td style="padding-bottom: 70px">Disetujui</td>
                            <td style="padding-bottom: 70px">Disetujui</td>
                        </tr>
                        <tr>
                            <td>({{ $type === "SPI" ? 'Stock Point Supv.' : 'Admin. E-Commerce Toko Igr.**' }})</td>
                            <td>({{ $type === "SPI" ? 'Member Merah SPI' : 'Member Toko Igr.*' }})</td>
                            <td>(Kurir Indopaket)</td>
                        </tr>
                    </table>
                </div>
                <ul class="custom-list">
                    <li class="star">Berdasarkan informasi dari hasil koordinasi antara Kurir IndoPaket dengan {{ $type === "SPI" ? 'SPI' : 'Admin E-commerce Toko IGR' }}</li>
                    <li class="hash">{!! $type === "SPI" ? 'Merupakan nilai (Rp.) yang akan ditransfer ke MMS (jika transaksi VA) / pengurang nilai (Rp.) yang dibayar oleh MMS (jika transaksi <span style="margin-left: 21px;">COD</span>).' : 'Dapat ditandatangani oleh Logistic Supv./Jr.Supv.Toko Igr.' !!}</li>
                    <li class="double-hash">{{ $type === "SPI" ? 'Hanya dilengkapi jika transaksi COD.' : 'Merupakan nilai (Rp.) yang harus dibayar oleh Member Toko Igr.*' }}</li>
                </ul>
                <p style="font-style: italic">- 1/3 : arsip {{ $type === "SPI" ? 'Pihak SPI' : 'Admin. E-Commerce Toko Igr.' }}</p>
                <p style="font-style: italic">- 2/3 : arsip Pihak Indopaket</p>
                <p style="font-style: italic">- 3/3 : arsip {{ $type === "SPI" ? 'Member*' : 'arsip Member Toko Igr.' }}</p>
            </div>
        </div>
    </div>
</body>
</html>
