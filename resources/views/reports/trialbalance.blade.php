<html>
<head>
    <title>Trial Balance Report</title>
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
        /*
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        */
    }
</style>

{{--table top header info--}}
<table class="table table-bordered">
    <thead>
    <tr>
        <th colspan="2" style="border: none;">
            <img src="{{ public_path('images/letter-head-bil.png') }}" width="500" height="95" alt="logo">
        </th>
    </tr>
    <tr>
        @if($businessType != NULL || $businessType != '')
            <th colspan="2" style="border: none;"><h2>{{ $businessType }} Trial Balance Report</h2></th>
        @else
            <th colspan="2" style="border: none;"><h2>Trial Balance Report</h2></th>
        @endif
    </tr>
    <tr>
        <th width="15%" align="left" style="border: none;">Start Date :</th>
        <th width="85%" align="left" style="border: none;">{{ $start_date }}</th>
    </tr>
    <tr>
        <th width="15%" align="left" style="border: none;">End Date :</th>
        <th width="85%" align="left" style="border: none;">{{ $end_date }}</th>
    </tr>
    <tr>
        <th width="15%" align="left" style="border: none;">Business Type :</th>
        @if($businessType != NULL || $businessType != '')
            <th width="85%" align="left" style="border: none;">{{ $businessType }}</th>
        @else
            <th width="85%" align="left" style="border: none;">ALL</th>
        @endif
    </tr>
    <tr>
        <th width="15%" align="left" style="border: none;">Processing Date :</th>
        <th width="85%" align="left" style="border: none;">{{ $run_date }}</th>
    </tr>
    <tr>
        <th width="15%" align="left" style="border: none;">Branch :</th>
        <th width="85%" align="left" style="border: none;">{{ $getBranchName }}</th>
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
        <th>Account Group</th>
        <th>Account Type</th>
        <th>Dr Balance</th>
        <th>Cr Balance</th>
    </tr>
    </thead>

    <?php
    $dr_total = 0;
    $cr_total = 0;
    $sl_count = 1;
    $trial_dr_amount_bal = 0;
    $trial_cr_amount_bal = 0;
    $prev_group_code = '';
    ?>

    <tbody>

    @foreach($trial_balance_sql as $trialBalanceData)
        @if(($trialBalanceData->dr_amount - $trialBalanceData->cr_amount) > 0 || ($trialBalanceData->cr_amount - $trialBalanceData->dr_amount)>0)
            <?php
            $trial_dr_amount = (float)$trialBalanceData->dr_amount;
            $trial_cr_amount = (float)$trialBalanceData->cr_amount;

            if ($trial_dr_amount > $trial_cr_amount) {

                $trial_dr_amount_bal = $trial_dr_amount - $trial_cr_amount;
                $trial_cr_amount_bal = 0;

            } else {

                $trial_cr_amount_bal = $trial_cr_amount - $trial_dr_amount;
                $trial_dr_amount_bal = 0;
            }

            $dr_total = $trial_dr_amount_bal + $dr_total;
            $cr_total = $trial_cr_amount_bal + $cr_total;
            ?>

            <tr>
                @if($prev_group_code == $trialBalanceData->group_code)
                    <td align="left"
                        style="border-top: none; border-bottom: none; border-top: none; border-right: none;">&nbsp;
                    </td>
                    <td align="left" style="border-top: none; border-bottom: none; border-top: none;">&nbsp;</td>
                    <td align="left">{{ $trialBalanceData->acc_type_name }} </td>
                @else
                    <td align="left" style="border-bottom: none;">{{ $sl_count++ }}</td>
                    <td align="left" style="border-bottom: none;">{{ $trialBalanceData->group_name }}
                        [ {{ $trialBalanceData->group_code }} ]
                    </td>
                    <td align="left">{{ $trialBalanceData->acc_type_name }}</td>
                @endif

                <td>{{ number_format($trial_dr_amount_bal,2) }}</td>
                <td>{{ number_format($trial_cr_amount_bal,2) }}</td>
            </tr>

            <?php $prev_group_code = $trialBalanceData->group_code; ?>
        @endif
    @endforeach


    {{--Get Net Total--}}
    <tr>
        <td colspan="3">
            <Strong>
                <center>Net Total</center>
            </Strong>
        </td>
        <td><strong>{{ number_format($dr_total,2) }}</strong></td>
        <td><strong>{{ number_format($cr_total,2) }}</strong></td>
    </tr>
    {{--Get Net Total--}}

    <tr>
        <td colspan="5" style="border:none;">&nbsp;</td>
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

</body>
</html>
