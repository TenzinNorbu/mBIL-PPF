<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>PF REFUND APPROVAL NOTE</title>
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
    <table class="table table-bordered" style="border-collapse: collapse; border: none;">
        <thead>
        <tr>
            <th colspan="2" style="border: none;">
                <img src="{{ public_path('images/letter-head-bil.png') }}" width="500" height="95" alt="logo">
            </th>
        </tr>
        <tr>
            <th colspan="2" style="border: none;"><h2>PF REFUND APPROVAL NOTESHEET</h2></th>
        </tr>
        <tr>
            <th width="50%" align="left" style="border: none;">Ref No : {{ $refund_ref_no }}</th>
            <th width="50%" align="right" style="border: none;">Date : {{ $currentDate }}</th>
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
                  This is to inform the Management and the Committee members that <b>{{ ESolution\DBEncryption\Encrypter::decrypt($refund_data->org_name) }}</b> is requesting
                    to refund the PF for <b>Dasho/Mr/Mrs. {{ ESolution\DBEncryption\Encrypter::decrypt($refund_data->employee_name) }}</b>, CID No. <b>{{ ESolution\DBEncryption\Encrypter::decrypt($refund_data->identification_no) }}</b>
                    under PF Account No. <b> {{ ESolution\DBEncryption\Encrypter::decrypt($refund_data->employee_id_no) }}</b> vide Reference No. <b>{{ $refund_ref_no }}</b> dated
                    <b>{{  Carbon\Carbon::parse($refund_data->refund_processing_date)->format('d-M-Y') }}</b>,
                    amounting to<b> Nu. {{ number_format($refund_data->refund_total_disbursed_amount,2) }}</b> ( Ngultrum {{ $number_to_word }} ).
                </p>
            </td>
        </tr>
        <tr style="border: none">
            <td style="border: none;">
                <p style="text-align: justify">
                    Therefore, we would like to seek an approval for the payment amounting to
                    Nu. {{ number_format($refund_data->refund_total_disbursed_amount,2) }}/- as described below :
                    <br> <br>
                    {!! nl2br(e($refund_data->refund_processed_remarks)) !!}
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
        <tr style="border: none;">
            <th style="border: none;" width="60%" align="left">Processed by: {{ $refund_data->refund_processed_by }}</th>
            <th style="border: none;" width="40%" align="left">Prepared by: {{ $verified_by }}</th>
        </tr>
        <tr style="border: none;">
            <th style="border: none;" width="60%" align="left">&nbsp;</th>
            <th style="border: none;" width="40%" align="left">(PPF/GF Department)</th>
        </tr>

        <tr style="border: none;"><td colspan="2"> &nbsp;</td></tr>
        <tr style="border: none;"><td colspan="2"> &nbsp;</td></tr>
        <tr style="border: none;"><td colspan="2"> &nbsp;</td></tr>
        <tr style="border: none;">
            <th style="border: none;" width="60%" align="left">Verified by: Dawa Choden<br>Head, PPF/GF Department</th>
            <th style="border: none;" width="40%" align="left">Loan Verification <br>
                Head Investment Department</th>
        </tr>
        <tr style="border: none;"><td colspan="2"> &nbsp;</td></tr>
        <tr style="border: none;"><td colspan="2"> &nbsp;</td></tr>
        <tr style="border: none;"><td colspan="2"> &nbsp;</td></tr>
        <tr style="border: none;">
            <th style="border: none;" width="60%" align="left">Counter Verified by: Passang Waiba<br>(IT Department)</th>
            <th style="border: none;" width="40%" align="left">Approved By: Kinzang Tshering<br>
                (Director Finance)</th>
        </tr>
    </table>
</div>
</body>
</html>
