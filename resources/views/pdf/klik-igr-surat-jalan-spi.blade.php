<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>REPORT SURAT JALAN</title>
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
                <div style="float: left;">
                    <p style="font-size: .8rem;"><b>{{ strtoupper("PT. INTI CAKRAWALA CITRA") }}</b></p>
                    <p style="font-size: .8rem;"><b>{{ strtoupper(session("KODECABANG") . " - " . session("NAMACABANG")) }}</b></p>
                </div>
            </div>
            <div style="margin-top: 55px">
                <h2 style="text-align: center; margin: 0 0 10px 0">SURAT JALAN</h2>
                <div style="margin: auto; width: 100%; text-align: center; padding-bottom: 15px">
                    <table style="width: 80%; margin: 0 auto;">
                        <tr>
                            <?php
                            $path = public_path("/img/bardcode.jpg");
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            ?>
                            <td><img src="<?php echo $base64?>" width="150" height="70"/></td>
                            <td>
                                <div>
                                    <p style="display: inline-block; width: 70px"><b>No. AWB</b></p>
                                    <p style="display: inline-block"><b style="margin-right: 8px">:</b> {{ $dtDetailSJ[0]->no_awb }}</p>
                                </div>
                                <div style="margin-top: 5px">
                                    <p style="display: inline-block; width: 70px"><b>Tanggal</b></p>
                                    <p style="display: inline-block"><b style="margin-right: 8px">:</b>{{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <p style="display: inline-block; width: 70px"><b>No. PB</b></p>
                                    <p style="display: inline-block"><b style="margin-right: 8px">:</b>{{ $dtDetailSJ[0]->no_pb }}</p>
                                </div>
                                <div style="margin-top: 5px">
                                    <p style="display: inline-block; width: 70px"><b>Tanggal PB</b></p>
                                    <p style="display: inline-block"><b style="margin-right: 8px">:</b>{{ $request["no_pb"] }}</p>
                                </div>
                            </td>
                            
                        </tr>
                    </table>
                </div>
                <div style="margin-top: 15px">
                    <p style="font-size: .8rem"><b>Dikirim Kepada :</b></p>
                    <p style="padding: 10px 0 3px 15px">{{ $dtDetailSJ[0]->no_hp }}</p>
                    <p style="padding: 3px 0 3px 15px">{{ $dtDetailSJ[0]->alamat }}</p>
                </div>
            </div>

            <div class="body" style="margin-top: 15px">
                <div style="margin: auto; padding: 0 25px">
                    <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                        <thead>
                            <tr>
                                <th style="width: 30px">No.</th>
                                <th style="width: 390px">Nama Barang</th>
                                <th>Frac</th>
                                <th>Satuan</th>
                                <th style="width: 80px">Qty.</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                        $total_qty = 0;
                        @endphp
                        @if (count($dt) > 0)
                            @foreach ($dt as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->nama_barang }}</td>
                                    <td>{{ $item->frac }}</td>
                                    <td>{{ $item->satuan }}</td>
                                    <td>{{ (int)$item->qty }}</td>
                                </tr> 
                                @php
                                $total_qty += $item->qty;
                                @endphp
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5">No Data</td>
                            </tr>
                        @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">TOTAL</td>
                                <td>{{ $total_qty }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div style="padding: 40px 0 10px">
                    <table style="width: 100%" class="table-center">
                        <tr>
                            <td style="padding-bottom: 70px;">Diterima Oleh</td>
                            <td style="padding-bottom: 70px">Dikirim Oleh</td>
                            <td style="padding-bottom: 70px">Diserahkan Oleh</td>
                        </tr>
                        <tr>
                            <td>(Member Merah SPI)</td>
                            <td>(Kurir IndoPaket)</td>
                            <td>(Personil SPI)</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
