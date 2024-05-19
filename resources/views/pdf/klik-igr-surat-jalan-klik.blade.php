<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @if($tipe_pdf == "Klik")
    <title>REPORT SURAT JALAN</title>
    @else
    <title>REPORT SURAT JALAN GURIH</title>
    @endif
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

        ul.list-gift{
            list-style: none!important;
            margin: 0;
        }

        ul.list-gift li{
            font-size: 11px;
            margin-bottom: 4px;
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
                <div style="float: right; border: 1px solid black; padding: 6px 20px;">
                    <p style="font-weight: bold">Lembar - 1 (asli)</p>
                    <p style="font-weight: bold">Kembali Ke Indogrosir</p>
                </div>
            </div>
            <div style="margin-top: 55px">
                <h2 style="text-align: center; margin: 0 0 10px 0">SURAT JALAN</h2>
                <table style="width: 100%; margin: 0 auto;">
                    <tr>
                        <?php
                        $path = public_path("/img/bardcode.jpg");
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        ?>
                        <td align="center" style="width: 50%!important"><img src="<?php echo $base64?>" width="150" height="70"/><p style="text-align: center; margin-top: -15px; padding-bottom: 30px">No. SJ : {{ $dtDetailSJ[0]->no_awb }}</p></td>
                        <td align="center"><img src="<?php echo $base64?>" width="150" height="70"/><p style="text-align: center; margin-top: -15px; padding-bottom: 30px">No. SP : {{ strtoupper($dtDetailSJ[0]->nopb) }}</p></td>
                    </tr>
                    <tr>
                        <td>
                            <p style="display: inline-block; width: 70px; margin-bottom: 5px"><b>Kepada</b></p>
                            <p style="display: inline-block; margin-bottom: 5px"><b style="margin-right: 15px">:</b> {{ $dtDetailSJ[0]->nama }}</p>
                        </td>
                        <td>
                            <p style="display: inline-block; width: 120px; margin-bottom: 5px"><b>No. SJ</b></p>
                            <p style="display: inline-block; margin-bottom: 5px"><b style="margin-right: 15px">:</b> {{ $dtDetailSJ[0]->no_awb }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td rowspan="3">
                            <p style="display: inline-block; width: 70px; margin-bottom: 5px"><b>Alamat</b></p>
                            <p style="display: inline-block; margin-bottom: 5px"><b style="margin-right: 15px">:</b> {{ $dtDetailSJ[0]->alamat }}</p>
                        </td>
                        <td>
                            <p style="display: inline-block; width: 120px; margin-bottom: 5px"><b>Tanggal pesan</b></p>
                            <p style="display: inline-block; margin-bottom: 5px"><b style="margin-right: 15px">:</b> {{ $dtDetailSJ[0]->tgl_pesan }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="display: inline-block; width: 120px; margin-bottom: 5px"><b>Tanggal Maks. Kirim</b></p>
                            <p style="display: inline-block; margin-bottom: 5px"><b style="margin-right: 15px">:</b> {{ $dtDetailSJ[0]->tgl_maks_kirim }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="display: inline-block; width: 120px; margin-bottom: 5px"><b>Ref. No. SP</b></p>
                            <p style="display: inline-block; margin-bottom: 5px"><b style="margin-right: 15px">:</b> {{ strtoupper($dtDetailSJ[0]->nopb) }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td rowspan="3" style="vertical-align: top;">
                            @if($tipe_pdf == "Klik")
                            <p style="display: inline-block; width: 70px; margin-bottom: 5px"><b>No. HP Penerima</b></p>
                            @else
                            <p style="display: inline-block; width: 70px; margin-bottom: 5px"><b>No. HP</b></p>
                            @endif 
                            <p style="display: inline-block; margin-bottom: 5px"><b style="margin-right: 15px">:</b> {{ $dtDetailSJ[0]->telp }}</p>
                        </td>
                        <td>
                            <p style="display: inline-block; width: 120px; margin-bottom: 5px"><b>Ref. No. Pesanan</b></p>
                            <p style="display: inline-block; margin-bottom: 5px"><b style="margin-right: 15px">:</b> {{ $dtDetailSJ[0]->nopo }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="display: inline-block; width: 120px; margin-bottom: 5px"><b>Pengiriman</b></p>
                            <p style="display: inline-block; margin-bottom: 5px"><b style="margin-right: 15px">:</b> {{ $dtDetailSJ[0]->ekspedisi }}</p>   
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="display: inline-block; width: 120px; margin-bottom: 5px"><b>Biaya layanan</b></p>
                            <p style="display: inline-block; margin-bottom: 5px"><b style="margin-right: 15px">:</b> {{ str_replace(',', '.', $dtDetailSJ[0]->ongkir) }}</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="body" style="margin-top: 15px">
                <div style="margin: auto;">
                    @if($tipe_pdf == "Klik")
                    <p>Bersama surat ini kami kirimkan barang dagangan yang dipesan Melalui Klik Indogrosir</p>
                    @else 
                    <p>Bersama surat ini kami kirimkan barang dagangan yang dipesan.</p>
                    @endif
                    <table border="1" style="border-collapse: collapse; margin-top:10px" class="table-center" cellpadding="2">
                        <thead>
                            <tr>
                                <th style="width: 140px">Jumlah Koli</th>
                                <th style="width: 450px">Nomor Koli</th>
                                <th>Kondisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $dtKoliCount }}</td>
                                <td>{{ $dtKoli }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <h5 style="margin-bottom: 10px">GIFT :</h5>
                    <ul class="list-gift">
                        @foreach ($dtHadiah as $item)
                        <li>{{ $item->gift }}</li>
                        @endforeach
                    </ul>
                </div>
                <div style="padding: 25px 0 10px">
                    @if($tipe_pdf == "Klik")
                    <table style="width: 100%" class="table-center" border="2" style="border-collapse: collapse; margin-bottom: 15px;">
                        <tr>
                            <td>
                                <div style="height: 90px; position: relative">
                                    <div style="font-weight: bold; color: black">Penerima</div>
                                    <p style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: .3; font-size: .8rem; white-space: nowrap; font-weight: bold; color: gray">Tanda Tangan & Stampel</p>
                                    <p style="position: absolute; bottom: 0">Tgl Terima :</p>
                                </div>
                            </td>
                            <td>
                                <div style="height: 90px; font-weight: bold; color: black">
                                    Pengirim
                                </div>
                            </td>
                            <td>
                                <div style="height: 90px; font-weight: bold; color: black">
                                    Dicetak                                    
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 15px; font-weight: bold; color: black">(Member Klik Indogrosir/<br>Petugas Ekspedisi*)</td>
                            <td style="padding: 3px 15px; font-weight: bold; color: black">(Team Delivery IPP)</td>
                            <td style="padding: 3px 15px; font-weight: bold; color: black">(Admin Klik Indogrosir<br> Di Toko Indogrosir)</td>
                        </tr>
                    </table>
                    <hr style="width: 100%;">
                    <p>SJ - 2 dibawa oleh Member/Petugas ekspedisi sebagai dasar serah terima barang dagangan.</p>
                    <p>SJ - 1 diserahkan kembali ke Toko Indogrosir sebagai bukti penerimaan barang oleh Member /<br> Petugas ekspedisi dan dijadikan dasar untuk meminta Member menyerahkan BPB asli.</p>
                    @else 
                    <table style="width: 100%" class="table-center" border="2" style="border-collapse: collapse; margin-bottom: 15px;">
                        <tr>
                            <td colspan="2">Diterima</td>
                            <td>Diserahkan</td>
                            <td>Dicetak</td>
                        </tr>
                        <tr>
                            <td style="width: 180px">
                                <div style="height: 90px; position: relative">
                                    <p style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: .3; font-size: .8rem; white-space: nowrap; font-weight: bold; color: gray">Tanda Tangan & Stampel</p>
                                    <p style="position: absolute; bottom: 0">Tgl Terima :</p>
                                </div>
                            </td>
                            <td style="width: 180px">
                                <div style="height: 90px; position: relative">
                                    <p style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: .3; font-size: .8rem; white-space: nowrap; font-weight: bold; color: gray">Tanda Tangan</p>
                                </div>
                            </td>
                            <td>
                                <div style="height: 90px; font-weight: bold; color: black">                  
                                </div>
                            </td>
                            <td>
                                <div style="height: 90px; font-weight: bold; color: black">                  
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 15px; font-weight: bold; color: black">(Member)</td>
                            <td style="padding: 3px 15px; font-weight: bold; color: black">(Petugas Ekspedisi)</td>
                            <td style="padding: 3px 15px; font-weight: bold; color: black">(Team Delivery<br> Toko Indogrosir)</td>
                            <td style="padding: 3px 15px; font-weight: bold; color: black">(Admin E-Commerce<br> Toko Indogrosir)</td>
                        </tr>
                    </table>
                    <hr style="width: 100%;">
                    <p>SJ - 2 dibawa oleh Member/Petugas Ekspedisi sebagai dasar serah terima barang dagangan.</p>
                    <p>SJ - 1 diserahkan kembali ke Toko Indogrosir sebagai bukti penerimaan barang oleh Member /<br> Petugas ekspedisi dan dijadikan dasar untuk meminta Member menyerahkan BPB asli.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
