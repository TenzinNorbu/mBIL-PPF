<html>
<head>
    <title>Account Ledger Report</title>
</head>
<body>
<style>
    table {
        width: 100%;
        max-width: 100%;
        border-spacing: 0;
        border-collapse: collapse;
        margin-left: 10px;
        margin-right: 10px;
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
      
    }
</style>
<?php
$debit = 0;
$credit = 0;
?>

<div>
    {{--table top header info--}}
    <table class="table table-bordered">
        <thead>
        <tr>
            <th colspan="2" style="border: none;">
                <img src="{{ asset('images/letter-head-bil.png') }}" width="500" height="95" alt="logo">
            </th>
        </tr>
        <tr>
            <th colspan="2" style="border: none;"><h2>{{ $businessType }} Account Ledger Report</h2></th>
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
            <th width="15%" align="left" style="border: none;">Account Group :</th>
            <th width="85%" align="left" style="border: none;">{{ $accountGroupName }}</th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Account Type :</th>
            <th width="85%" align="left" style="border: none;">{{ $accountTypeName }}</th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Business Type :</th>
                <th width="85%" align="left" style="border: none;">{{ $businessType }}</th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Branch Name :</th>
            <th width="85%" align="left" style="border: none;">{{ $branchName }}</th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Company Name :</th>
            <th width="85%" align="left" style="border: none;">{{ $companyName }}</th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Employee :</th>
            <th width="85%" align="left" style="border: none;">{{ $employeeName }}</th>
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
            <th>Voucher No</th>
            <th>Narration</th>
            <th>Instrument No</th>
            <th>Instrument Date</th>
            <th>Dr Amount</th>
            <th>Cr Amount</th>
        </tr>
        </thead>

        <?php        

        $opening_debit_value = 0;
        $opening_credit_value = 0;

  
        $total_ledger_dr_value = 0;
        $total_ledger_cr_value = 0;

        ?>

        <tbody>

        {{--opening for account ledger--}}
        @foreach($opening_query as $opening_data)
            <tr>
                <td colspan="5">
                    <strong>
                        <center>Opening Balance</center>
                    </strong>
                </td>

                <?php
                $opening_debit = floatval($opening_data->opening_debit_amount);
                $opening_credit = floatval($opening_data->opening_credit_amount);

                if ($opening_debit > $opening_credit) {

                    $opening_debit_value = $opening_debit - $opening_credit;
                    $opening_credit_value = 0;

                } else {

                    $opening_credit_value = $opening_credit - $opening_debit;
                    $opening_debit_value = 0;
                }
                ?>
                <td><b>{{ number_format($opening_debit_value,2) }}</b></td>
                <td><b>{{ number_format($opening_credit_value,2) }}</b></td>
            </tr>
        @endforeach
        {{--opening for account ledger end--}}

        
        @foreach($account_ledger_sql as $key=>$ledger_data)
            <?php
            $credit_value = floatval($ledger_data->acc_credit_amount);
            $debit_value = floatval($ledger_data->acc_debit_amount);

            if ($credit_value > $debit_value) {
                $credit_value = $credit_value - $debit_value;
                $debit_value = 0;

            } else {
                $debit_value = $debit_value - $credit_value;
                $credit_value = 0;
            }

            $total_ledger_dr_value = $debit_value + $total_ledger_dr_value;
            $total_ledger_cr_value = $credit_value + $total_ledger_cr_value;
            
            ?>

            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $ledger_data->account_voucher_number }}</td>
                <td>{{ $ledger_data->account_voucher_narration }}</td>
                <td>{{ $ledger_data->account_collection_instrument_no }}</td>
                <td>{{ $ledger_data->account_cheque_date }}</td>
                <td>{{ number_format($debit_value,2) }}</td>
                <td>{{ number_format($credit_value,2) }}</td>
            </tr>

        @endforeach

        {{--Get Total--}}
        <tr>
            <td colspan="5">
                <Strong>
                    <center>Total Amount</center>
                </Strong>
            </td>
            <td><strong>{{ number_format(($total_ledger_dr_value + $opening_debit_value),2) }}</strong></td>
            <td><strong>{{ number_format(($total_ledger_cr_value + $opening_credit_value),2) }}</strong></td>
        </tr>

        {{--Get Total End--}}

        {{--Net Amount Start--}}

        <?php
                $net_dr_balance = $total_ledger_dr_value + $opening_debit_value;
                $net_cr_balance = $total_ledger_cr_value + $opening_credit_value;

        ?>

        <tr class="bg-white">
            <td colspan="5">
                <Strong>
                    <center>Net Amount</center>
                </Strong>
            </td>
            @if($net_dr_balance > $net_cr_balance)
                <td><strong>{{ number_format(($net_dr_balance - $net_cr_balance),2) }}</strong></td>
                <td><strong>0.00</strong></td>
            @else
                <td><strong>0.00</strong></td>
                <td><strong>{{ number_format(($net_cr_balance - $net_dr_balance),2) }}</strong></td>
            @endif

            
        </tr>
        {{--Net Amount--}}

        <tr>
            <td colspan="7" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="7" style="border:none;">&nbsp;</td>
        </tr>

        {{--Get Total End--}}
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
