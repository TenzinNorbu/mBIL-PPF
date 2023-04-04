<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <title>Excess Payment Refund</title>
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
                <img src="{{ public_path('images/letter-head-bil.png') }}" width="500" height="99" alt="logo">
            </th>
        </tr>
        <tr>
            <th colspan="2" style="border: none;"><h3>EXCESS REFUND PAYMENT REQUEST NOTE</h3></th>
        </tr>
        <tr>
            <th width="50%" align="left" style="border: none;">Ref No : {{ $excess_refund_ref_no }}</th>
            <th width="50%" align="right" style="border: none;">Date : {{ $refund_processing_date }}</th>
        </tr>
        </thead>
    </table>
    {{--table top header info--}}

    {{--content start--}}
    <table class="table table-bordered" style="border-collapse: collapse; border: none;">
        <tr style="border: none">
            <td  style="border: none;">
                <p style="text-align: justify">
                    <br>
                    To <br>
                    The Finance Manager, <br>
                    Financing and Investment Department, <br>
                    Bhutan Insurance Limited, <br>
                    <br>
                    Subject: <b>{{ $registration_type }} Excess Payment Refund</b>
                    <br><br>
                    Please kindly arrange to disburse the amount of Nu. {{ $excess_amount }} ({{ $amount_in_words }} only) to
                    <u>{{ ESolution\DBEncryption\Encrypter::decrypt($organization_name) }}</u> {{ $registration_type }} account no. {{ ESolution\DBEncryption\Encrypter::decrypt($account_no) }} 
                    Excess Payment Refund against the collection No. {{ $col_ref_no }}.
                    <br><br>
                    Deposit Bank Account No: <b>{{ $bank_account_no }}</b>, Bank Name : <b>{{ $bank_name }}</b>
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
</div>
<div style="text-align: left">
    <div>
        <span><strong>For Bhutan Insurance Limited</strong></span>
    </div>

    <div>
        <br> <br> <br>
        <span><strong>Authority Signatory</strong></span>
    </div>
</div>
</body>
</html>
