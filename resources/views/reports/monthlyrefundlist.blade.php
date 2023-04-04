<html>
<head>
    <title>Monthly Refund Report</title>
</head>
<body>
<style>

    table {
        width: 100%;
        max-width: 100%;
        border-spacing: 0;
        border-collapse: collapse;
        /* margin-left: 10px;
        margin-right: 10px; */
    }

    .table-bordered > thead > tr > td, .table-bordered > thead > tr > th {
        border: 1px solid #000;
        font-size: 12px;
    }

    .table-bordered > tbody > tr > td, .table-bordered > tbody > tr > th {
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
        margin: 5px 15px;
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
            <th colspan="2" style="border: none;"><h2>{{ $regType }} MONTHLY REFUND REPORT</h2></th>
        </tr>
        <tr>
            <th width="20%" align="left" style="border: none;">Start Date :</th>
            <th width="80%" align="left" style="border: none;">{{ $fromDate }}</th>
        </tr>
        <tr>
            <th width="20%" align="left" style="border: none;">End Date :</th>
            <th width="80%" align="left" style="border: none;">{{ $toDate }}</th>
        </tr>
        <tr>
            <th width="20%" align="left" style="border: none;">Business Type :</th>
            <th width="80%" align="left" style="border: none;">{{ $regType }}</th>
        </tr>
        <tr>
            <th width="20%" align="left" style="border: none;">Organization :</th>
            <th width="80%" align="left" style="border: none;">{{ $companyName }}</th>
        </tr>
        <tr>
            <th width="20%" align="left" style="border: none;">Employee Name :</th>
            <th width="80%" align="left" style="border: none;">{{ $employeeName }}</th>
        </tr>

        @if($refund_type == 'Excess Refund')
        <tr>
            <th width="20%" align="left" style="border: none;">Refund Type :</th>
            <th width="80%" align="left" style="border: none;">Excess Payment Refund</th>
        </tr>
        @else
        <tr>
            <th width="20%" align="left" style="border: none;">Refund Type :</th>
            <th width="80%" align="left" style="border: none;">Monthly Refund</th>
        </tr>
        @endif

        <tr>
            <td colspan="2" style="border:none;">&nbsp;</td>
        </tr>
        </thead>
    </table>
    {{--table top header info--}}

    {{--table start--}}
    <table class="table-bordered">
        <thead>
        <tr>
            <th>Sl.No</th>
            <th>Refund Date</th>
            <th>Company Name</th>
            <th>MOU Date</th>
            <th>Employee Name</th>
            <th>Contributions Refunded</th>
            <th>Interest Refunded</th>
            <th>Total Refund Amt</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $total_contribution = 0;
        $total_interest = 0;
        $overall_total = 0;
        ?>
        @foreach($monthly_refund_data_lists as $key=>$monthly_refund_data)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $monthly_refund_data->refund_date }}</td>
                <td>{{ $monthly_refund_data->company_name }}</td>
                <td>{{ $monthly_refund_data->mou_date }}</td>
                <td>{{ $monthly_refund_data->employee_name }}</td>
                <td style="text-align: right;">{{ number_format($monthly_refund_data->contributions_refunded,2) }}</td>
                <td style="text-align: right;">{{ number_format($monthly_refund_data->interest_refunded,2) }}</td>
                <td style="text-align: right;">{{ number_format($monthly_refund_data->total_refunded_amount,2) }}</td>
            </tr>
            <?php
            $total_contribution = $total_contribution + (float)$monthly_refund_data->contributions_refunded;
            $total_interest = $total_interest + (float)$monthly_refund_data->interest_refunded;
            $overall_total = $overall_total + (float)$monthly_refund_data->total_refunded_amount;
            ?>
        @endforeach
        <tr>
            <th colspan="5">Total</th>
            <th style="text-align: right;">{{ number_format($total_contribution,2) }}</th>
            <th style="text-align: right;">{{ number_format($total_interest,2) }}</th>
            <th style="text-align: right;">{{ number_format($overall_total,2) }}</th>
        </tr>

        <tr>
            <td colspan="8" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="8" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="8" style="border:none;">&nbsp;</td>
        </tr>
        </tbody>
    </table>
    {{--Table End Section --}}

    <div style="text-align: right">
        <div>
            <span><strong>For Bhutan Insurance Limited</strong></span>
        </div>

        <div>
            <br> <br> <br>
            <span><strong>Authority Signatory</strong></span>
        </div>
    </div>
</div>
</body>
</html>
