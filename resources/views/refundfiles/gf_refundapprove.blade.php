<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>GF REFUND APPROVAL NOTE</title>
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
            <th colspan="2" style="border: none;"><h2>GF REFUND PAYMENT ADVISE</h2></th>
        </tr>
        <tr>
            <th width="50%" align="left" style="border: none;">Ref No : {{ $refundRefNo }}</th>
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
            <td>
                <p style="text-align: justify">
                    To<br>
                    The Head,<br>
                    Finance and Account Department,<br>
                    Bhutan Insurance Limited,<br><br>

                    Subject: <b>GF Refund Disbursement Advise</b><br><br>

                    Please kindly arrange to refund the sum of Nu. <b>{{ number_format($total_payable_amount,2) }}/-</b> (
                    Ngultrum. {{ $number_to_word }} ) against
                    the employee of {{ ESolution\DBEncryption\Encrypter::decrypt($orgName) }} as detailed below : <br>
                </p>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
    </table>

    {{--table start--}}
    <table class="table table-bordered" style="border: 1px solid #111">
        <thead>
        <tr>
            <th style="border: 1px solid #111">Sl.No</th>
            <th style="border: 1px solid #111">Employee Name</th>
            <th style="border: 1px solid #111">Employee CID</th>
            <th style="border: 1px solid #111">Total Amount</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $grossTotalContribution = 0;
        ?>
        @foreach($get_data as $key=>$approve_data)
            <tr>
                @if($approve_data['company_id'] == $paymentCompanyId)
                    <td style="border: 1px solid #111">{{ $key + 1 }}</td>
                    <?php
                    $total_refund_contribution = 0;

                    $EmpId = $approve_data['pf_employee_id'];
                    $getEmpData = \App\Models\Pfemployeeregistration::where('pf_employee_id', '=', $EmpId)
                        ->get();
                    $employee_cid = $getEmpData->first()->identification_no;
                    $employee_name = $getEmpData->first()->employee_name;

                    $total_refund_contribution = (float)$approve_data['refund_total_contr'] + $total_refund_contribution + (float)$approve_data['refund_total_interest'] ;
                    $grossTotalContribution = $total_refund_contribution + $grossTotalContribution;
                    ?>
                    <td style="border: 1px solid #111">{{ $employee_name }}</td>
                    <td style="border: 1px solid #111">{{ $employee_cid }}</td>
                    <td style="border: 1px solid #111">{{ number_format($total_refund_contribution,2) }}</td>
                @endif
            </tr>
        @endforeach

        {{-- Get Total--}}
        <tr>
            <td colspan="3" style="border: 1px solid #111">
                <Strong><center>Total</center></Strong>
            </td>
            <td colspan="1" style="border: 1px solid #111"><strong>{{ number_format($grossTotalContribution,2) }}</strong></td>
        </tr>
        <tr>
            <td colspan="4" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" style="border:none;">&nbsp;</td>
        </tr>
        </tbody>
    </table>
    {{--Table End Section --}}

    {{--content End Section --}}
    <table class="table table-bordered" style="border-collapse: collapse; border: none;">
        <tr style="border: none;">
            <th colspan="2" style="border: none;" align="left">Prepared by: {{ $refund_data->refund_verified_by }}</th>
        </tr>
        <tr style="border: none;">
            <th style="border: none;" width="60%" align="left">&nbsp;</th>
            <th style="border: none;" width="40%" align="left">(PPF/GF Department)</th>
        </tr>

        <tr style="border: none;"><td colspan="2"> &nbsp;</td></tr>
        <tr style="border: none;"><td colspan="2"> &nbsp;</td></tr>
        <tr style="border: none;"><td colspan="2"> &nbsp;</td></tr>

        <tr style="border: none;">
            <th style="border: none;" width="60%" align="left">Verified by: {{ $refund_data->refund_approved_by }}<br>Head, PPF/GF Department</th>
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
{{--@endsection--}}
