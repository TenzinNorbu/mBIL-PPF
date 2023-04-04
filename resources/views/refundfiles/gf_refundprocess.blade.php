<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>GF REFUND REQUEST SLIP</title>
</head>
<body>
<style type="text/css">
    table {
        width: 100%;
        max-width: 100%;
        border-spacing: 0;
        border-collapse: collapse;
        page-break-before:avoid;
        page-break-after:avoid;
    }

    .table-bordered > thead > tr > td > thead > tr > th {
        border: 1px solid #000;
        font-size: 12px;
    }

    .table-bordered > tbody > tr > td > tbody > tr > th {
        border: 1px solid #000;
        font-size: 12px;
    }

    table thead tr {
        page-break-before: auto;
        page-break-after: auto;
    }

    table thead tr td, table thead tr th {
        page-break-inside: avoid;
    }

    table tbody tr {
        page-break-before: auto;
        page-break-after: auto;
    }

    table tbody tr td, table tbody tr th {
        page-break-inside: auto;
    }

    html {
        /* top / bottom | | right / left */
        margin: 10px 50px;
    }

    @media print {
        body {
            display: none;
        }
    }
    
    body {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
</style>

<div>
    {{--table top header info--}}
    <table class="table table-bordered">
        <thead>
        <tr>
            <th colspan="2" style="border: none;">
                <img src="{{ public_path('images/letter-head-bil.png') }}" width="500" height="95" alt="logo">
            </th>
        </tr>
        <tr>
            <th colspan="2" style="border: none;"><h2>GF REFUND REQUEST NOTE</h2></th>
        </tr>
        <tr>
            <th width="50%" align="left" style="border: none;">Ref No : {{ $refundRefNo }}</th>
            <th width="50%" align="right" style="border: none;">Date : {{ $date }}</th>
        </tr>
        <tr>
            <td colspan="2" style="border:none;">&nbsp;</td>
        </tr>
        </thead>
    </table>
    {{--table top header info--}}

    {{--content start--}}
    <table class="table table-bordered" style="border-collapse: collapse; border: none;">
        <tr style="border: none;">
            <td style="border: none;">
                <p style="text-align: justify">
                    <br>
                    To <br>
                    The Head, <br>
                    PF/GF Department, <br>
                    Bhutan Insurance Limited, <br>
                    <br>
                    Subject: <b>Forwarding documents - GF Refund </b> <br> <br>

                    Sir/Madam, <br><br>
                    We are forwarding here with the attached documents of GF refund against GF account No.
                    <u>{{ ESolution\DBEncryption\Encrypter::decrypt($pfEmployeeNo) }}</u>,
                    belonging to Dasho/Mr/Mrs. <b>{{ ESolution\DBEncryption\Encrypter::decrypt($empName) }}</b> of <strong>{{ ESolution\DBEncryption\Encrypter::decrypt($companyName) }}</strong>, amounting
                    to Nu. <b>{{ number_format($refundProcessAmount,2) }}</b>/- ( Ngultrum, {{ $numInWords }} only).
                    <br><br>
                    Therefore, kindly process the GF refund request of {{ ESolution\DBEncryption\Encrypter::decrypt($companyName) }},
                    amounting to Nu. {{ number_format($refundProcessAmount,2) }}/- as described below :
                    <br><br>
                    {!! nl2br(e($refund_remarks)) !!}
                    <br> <br> <br>
                </p>
            </td>
        </tr>
        <tr style="border: none">
            <td  style="border: none;">&nbsp;</td>
        </tr>
        <tr style="border: none">
            <td  style="border: none;">&nbsp;</td>
        </tr>
    </table>
    {{--content End Section --}}

    <table class="table table-bordered" style="border-collapse: collapse; border: none;">
        <tr style="border: none">
            <td colspan="2"  style="border: none;">Thanking you, <br><br><br>
                Yours obediently,
            </td>
        </tr>
        <tr style="border: none">
            <td colspan="2"  style="border: none;">&nbsp;</td>
        </tr>
        <tr style="border: none">
            <td  style="border: none;">Process By</td>
            <td  style="border: none;">Verified By</td>
        </tr>
        <tr style="border: none">
            <td colspan="2"  style="border: none;">&nbsp;</td>
        </tr>
        <tr style="border: none">
            <td colspan="2"  style="border: none;">&nbsp;</td>
        </tr>
        <tr style="border: none">
            <td colspan="2"  style="border: none;">&nbsp;</td>
        </tr>
        <tr style="border: none">
            <td  style="border: none;">{{ $process_by_name }}</td>
            <td  style="border: none;">Head</td>
        </tr>
        <tr style="border: none">
            <td  style="border: none;">{{ $process_by_branch }}</td>
            <td  style="border: none;">{{ $process_by_branch }}</td>
        </tr>
        <tr style="border: none">
            <td colspan="2"  style="border: none;">&nbsp;</td>
        </tr>
        <tr style="border: none">
            <td colspan="2"  style="border: none;">&nbsp;</td>
        </tr>
        <tr style="border: none">
            <td colspan="2"  style="border: none;">&nbsp;</td>
        </tr>
    </table>
</div>
</body>
</html>
