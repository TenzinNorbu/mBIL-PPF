<html>
<head>
    <title>Company wise fund balance report</title>
</head>
<body>
<style>

    table {
        width: 100%;
        max-width: 100%;
        border-spacing: 0;
        border-collapse: collapse;
        /*margin-left: 10px;*/
        /*margin-right: 10px;*/
    }

    .table-bordered > thead > tr > td, .table-bordered > thead > tr > th {
        border: 1px solid #000;
        font-size: 11px;
    }

    .table-bordered > tbody > tr > td, .table-bordered > tbody > tr > th {
        border: 1px solid #000;
        font-size: 9px;
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
            @if($regType != NULL || $regType != '')
                <th colspan="2" style="border: none;"><h2>{{ $regType }} COMPANY WISE FUND BALANCE REPORT</h2></th>
            @else
                <th colspan="2" style="border: none;"><h2>COMPANY WISE FUND BALANCE REPORT</h2></th>
            @endif
        </tr>
        <tr>
            <th width="10%" align="left" style="border: none;">Start Date :</th>
            <th width="90%" align="left" style="border: none;">{{ $fromDate }}</th>
        </tr>
        <tr>
            <th width="10%" align="left" style="border: none;">End Date :</th>
            <th width="90%" align="left" style="border: none;">{{ $toDate }}</th>
        </tr>
        <tr>
            <th width="10%" align="left" style="border: none;">Organization :</th>
            <th width="90%" align="left" style="border: none;">{{ $companyName }}</th>
        </tr>
        <tr>
            <th width="10%" align="left" style="border: none;">Business Type :</th>
            @if($regType != NULL || $regType != '')
                <th width="90%" align="left" style="border: none;">{{ $regType }}</th>
            @else
                <th width="90%" align="left" style="border: none;">ALL</th>
            @endif
        </tr>
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
            <th>Company Name</th>
            <th>Int Rate</th>
            <th>Total Cont</th>
            <th>Total Interest</th>
            <th>Gross Cont</th>
            <th>Total Disbursed Amt</th>
            <th>Net Balance</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $total_contribution = 0;
        $total_interest = 0;
        $overall_contribution = 0;
        $total_disbursement = 0;
        $net_fund_balance = 0;
        ?>
        @foreach($company_wise_data as $key=>$fundbalance_data)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $fundbalance_data->company_name }}</td>
                <td>{{ number_format($fundbalance_data->int_rate,2) }}</td>
                <td>{{ number_format($fundbalance_data->total_contributions,2) }}</td>
                <td>{{ number_format($fundbalance_data->total_interest,2) }}</td>
                <td>{{ number_format(($fundbalance_data->total_contributions + $fundbalance_data->total_interest),2) }}</td>
                <td>{{ number_format($fundbalance_data->total_disbursed_amount,2) }}</td>
                <td>{{ number_format((($fundbalance_data->total_contributions + $fundbalance_data->total_interest) - $fundbalance_data->total_disbursed_amount),2) }}</td>
            </tr>

            <?php
            $total_contribution = $total_contribution + (float)$fundbalance_data->total_contributions;
            $total_interest = $total_interest + (float)$fundbalance_data->total_interest;
            $overall_contribution = $overall_contribution + (float)$fundbalance_data->total_contributions + (float)$fundbalance_data->total_interest;
            $total_disbursement = $total_disbursement + (float)$fundbalance_data->total_disbursed_amount;
            $net_fund_balance = $net_fund_balance + (float)(($fundbalance_data->total_contributions + (float)$fundbalance_data->total_interest) - (float)$fundbalance_data->total_disbursed_amount);
            ?>

        @endforeach

        <tr>
            <th colspan="3">Total</th>
            <th style="text-align: right;">{{ number_format($total_contribution,2) }}</th>
            <th style="text-align: right;">{{ number_format($total_interest,2) }}</th>
            <th style="text-align: right;">{{ number_format($overall_contribution,2) }}</th>
            <th style="text-align: right;">{{ number_format($total_disbursement,2) }}</th>
            <th style="text-align: right;">{{ number_format($net_fund_balance,2) }}</th>
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
