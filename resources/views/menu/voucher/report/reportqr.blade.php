@extends('menu.pdf-template')

@section('table_font_size','7 px')

@section('page_title')
    @lang('REPORT QR')
@endsection



@section('subtitle')

@endsection
@section('header_left')
    <table>
        <tr style="text-align: left; font-size:14px;">
            <td> {{ $perusahaan->prs_namaperusahaan }}</td>
        </tr>
        <tr style="text-align: left; font-size:14px;">
            <td> </td>
        </tr>
        <tr style="text-align: left; font-size:14px;">
            <td> {{ $perusahaan->prs_namacabang }}</td>
        </tr>
    </table>
@endsection
@section('header_right')
    <table>
        <tr style="text-align: left; font-size:14px;">
            <td>{{ date("d/m/Y") }}</td>
            <td>/</td>
            <td> {{ date('H:i:s') }}</td>
        </tr>
    </table>
@endsection

@section('content')
<p class="center" style=" text-align: center; margin-bottom:50px; font-size:14px;">
    <span style="margin-right: 20px;"> Kode Toko : -</span>
    <span style="margin-right: 20px;"> No NPB : -</span>
    <span style="margin-right: 20px;"> Tgl NPB  : -</span>
</p>

<div class="center" style=" text-align: center; margin-top:100px; font-size:14px;">
    <div style="display: inline-block; text-align:center; margin-right:20px;">
        {!! DNS2D::getBarcodeHTML('GL13091280981290342423432423423423454354353453453453453458390812091309129038892031889012812939012389012890830919239081098920312839184238923840923809423890480923', 'QRCODE') !!}

        <span> header</span>
    </div>
    <div style="display: inline-block; text-align:center; margin-left:20px;">
        {!! DNS2D::getBarcodeHTML('GL13091280981290342423432423423423454354353453453453453458390812091309129038892031889012812939012389012890830919239081098920312839184238923840923809423890480923', 'QRCODE') !!}
        
        <span> 01/01</span>
    </div>
</div>
@endsection¸