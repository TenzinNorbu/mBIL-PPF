<html>
<head>
    <title>Employee Wise Statement</title>
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

<?php
$as_on_interest_accrued_employee = 0;
$as_on_interest_accrued_employer = 0;
$as_on_total_interest = 0;

$opening_cont_employee = 0;
$opening_cont_employer = 0;
$opening_cont_employee_interest = 0;
$opening_cont_employer_interest = 0;

$opening_refund_employee = 0;
$opening_refund_employer = 0;
$opening_refund_int_employee = 0;
$as_on_opening_refund_int_employee = 0;
$opening_refund_int_employer = 0;
$as_on_opening_refund_int_employer = 0;
$opening_refund_disbursed_amount = 0;

$opening_current_cont_employee = 0;
$opening_current_cont_employer = 0;
$opening_current_cont_employee_int = 0;
$opening_current_cont_employer_int = 0;

$current_openingrefund_employee = 0;
$current_openingrefund_employer = 0;
$current_openingrefund_employee_interest = 0;
$current_openingrefund_employer_interest = 0;
$as_on_current_openingrefund_employee_interest = 0;
$as_on_current_openingrefund_employer_interest = 0;
$current_opening_refund_disbursed_amount = 0;
?>

@if(!empty($as_on_data))
    <?php
    $as_on_interest_accrued_employee = $as_on_data['interest_accrued_employee'];
    $as_on_interest_accrued_employer = $as_on_data['interest_accrued_employer'];
    $as_on_total_interest = $as_on_interest_accrued_employee + $as_on_interest_accrued_employer;
    ?>
@else
    <?php
    $as_on_interest_accrued_employee = 0;
    $as_on_interest_accrued_employer = 0;
    $as_on_total_interest = 0;
    ?>
@endif

@if($opening_contribution != NULL ||  $opening_contribution != '')
    <?php
    $opening_cont_employee = (float)$opening_contribution->total_employee_contribution;
    $opening_cont_employer = (float)$opening_contribution->total_employer_contribution;
    $opening_cont_employee_interest = (float)$opening_contribution->total_interest_employee;
    $opening_cont_employer_interest = (float)$opening_contribution->total_interest_employer;

    $opening_total_contribution_for_net_balance = (float)$opening_contribution->total_employee_contribution + (float)$opening_contribution->total_employer_contribution;
    $opening_total_interest_for_net_balance = (float)$opening_contribution->total_interest_employee + (float)$opening_contribution->total_interest_employer;

    ?>
@else
    <?php
    $opening_cont_employee = 0;
    $opening_cont_employer = 0;
    $opening_cont_employee_interest = 0;
    $opening_cont_employer_interest = 0;
    $opening_total_contribution_for_net_balance= 0;
    $opening_total_interest_for_net_balance = 0;
    ?>
@endif

@if( $opening_refund != null || $opening_refund != '')
    <?php
    $opening_refund_employee = (float)$opening_refund->total_employee_contribution;
    $opening_refund_employer = (float)$opening_refund->total_employer_contribution;
    
    $opening_refund_int_employee = (float)$opening_refund->refund_interest_employee;
    $opening_refund_int_employer = (float)$opening_refund->refund_interest_employer;
   
    $as_on_opening_refund_int_employee = (float)$opening_refund->as_on_refund_employee_interest;    
    $as_on_opening_refund_int_employer = (float)$opening_refund->as_on_refund_employer_interest;
    
    $opening_refund_disbursed_amount = (float)$opening_refund->refund_disbursed_amount;
    $opening_refund_for_net_balance = (float)$opening_refund->refund_disbursed_amount;
    
    ?>
@else
    <?php
    $opening_refund_employee = 0;
    $opening_refund_employer = 0;
    $opening_refund_int_employee = 0;
    $as_on_opening_refund_int_employee = 0;
    $opening_refund_int_employer = 0;
    $as_on_opening_refund_int_employer = 0;
    $opening_refund_disbursed_amount = 0;
    $opening_refund_for_net_balance =0;
    ?>
@endif

@if($current_contribution != null && $current_contribution != '')
    <?php
    $opening_current_cont_employee = (float)$current_contribution->total_employee_contribution;
    $opening_current_cont_employer = (float)$current_contribution->total_employer_contribution;

    $opening_current_cont_employee_int = (float)$current_contribution->total_interest_employee;
    $opening_current_cont_employer_int = (float)$current_contribution->total_interest_employer;

    $current_total_contribution_for_net_balance = (float)$current_contribution->total_employee_contribution + (float)$current_contribution->total_employer_contribution;
    $current_total_interest_for_net_balance = (float)$current_contribution->total_interest_employee + (float)$current_contribution->total_interest_employer;

    ?>
@else
    <?php
    $opening_current_cont_employee = 0;
    $opening_current_cont_employer = 0;
    $opening_current_cont_employee_int = 0;
    $opening_current_cont_employer_int = 0;   

    $current_total_contribution_for_net_balance = 0;
    $current_total_interest_for_net_balance = 0;

   
    ?>
@endif

@if($current_opening_refund != NULL && $current_opening_refund != '')
    <?php
    $current_openingrefund_employee = $current_opening_refund->total_employee_contribution;
    $current_openingrefund_employer = $current_opening_refund->total_employer_contribution;
    $current_openingrefund_employee_interest = $current_opening_refund->refund_interest_employee;
    $current_openingrefund_employer_interest = $current_opening_refund->refund_interest_employer;
    $as_on_current_openingrefund_employee_interest = $current_opening_refund->as_on_refund_employee_interest;
    $as_on_current_openingrefund_employer_interest = $current_opening_refund->as_on_refund_employer_interest;
    $current_opening_refund_disbursed_amount = (float)$current_opening_refund->refund_disbursed_amount;
    $current_total_refund_for_net_balance  = (float)$current_opening_refund->refund_disbursed_amount;
    ?>
@else
    <?php
    $current_openingrefund_employee = 0;
    $current_openingrefund_employer = 0;
    $current_openingrefund_employee_interest = 0;
    $current_openingrefund_employer_interest = 0;
    $as_on_current_openingrefund_employee_interest = 0;
    $as_on_current_openingrefund_employer_interest = 0;
    $current_opening_refund_disbursed_amount = 0;
    $current_total_refund_for_net_balance = 0;
    ?>
@endif

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
            <th colspan="2" style="border: none;"><h2>{{ $registrationType }} Employee Wise Statement</h2></th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Organization Name : &nbsp; {{ $organization_name }}</th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Name & CID :
                &nbsp; {{ $employee_wise_data->employee_name }} &nbsp; ,
                &nbsp; {{ $employee_wise_data->identification_no }}</th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">A/c No & A/c Status :
                &nbsp; {{ $employee_wise_data->employee_id_no }} &nbsp; , &nbsp; [ {{ $employee_wise_data->status }} ]
            </th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Date of Registration :
                &nbsp; {{ $employee_wise_data->registration_date }}</th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Start & End Date : &nbsp; {{ $from_Date }} &nbsp; ,
                &nbsp; [ To: {{ $to_Date }}]
            </th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Processing Date:
                &nbsp; {{ \Carbon\Carbon::now()->format('d-m-Y') }}</th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Business Type : &nbsp; {{ $registrationType }}</th>
        </tr>
        <tr>
            <td colspan="2" style="border:none;">&nbsp;</td>
        </tr>
        </thead>
    </table>
    {{--table top header info--}}

    {{--Summary Report--}}
    <table class="table-bordered">
        <thead>
        <tr>
            <th colspan="6" align="left" style="border:none;">SUMMARY REPORT</th>
        </tr>
        <tr>
            <th>Particulars</th>
            <th>Employee Contribution</th>
            <th>Employer Contribution</th>
            <th>Interest (Employee)</th>
            <th>Interest (Employer)</th>
            <th>Total</th>
        </tr>
        </thead>

        <tbody>
        {{--Opening balance contribution start--}}
        <tr>
            <td>Opening Contribution</td>
            <td>{{ number_format($opening_cont_employee,2) }}</td>
            <td>{{ number_format($opening_cont_employer,2) }}</td>
            <td>{{ number_format($opening_cont_employee_interest,2) }}</td>
            <td>{{ number_format($opening_cont_employer_interest,2) }}</td>
            <?php
            $opening_total = $opening_cont_employee + $opening_cont_employer +
                            $opening_cont_employee_interest + $opening_cont_employer_interest;
            ?>
            <td>{{ number_format($opening_total,2) }}</td>
        </tr>

        {{--Opening balance Refund start--}}
        <tr>
            <td>Opening Refund</td>
            <td>{{ number_format($opening_refund_employee,2) }}</td>
            <td>{{ number_format($opening_refund_employer,2) }}</td>
            <td>{{ number_format(($opening_refund_int_employee + $as_on_opening_refund_int_employee),2) }}</td>
            <td>{{ number_format(($opening_refund_int_employer + $as_on_opening_refund_int_employer),2) }}</td>
            <td>{{ number_format($opening_refund_disbursed_amount,2) }}</td>
        </tr>

        {{--Current Contribution --}}
        <tr>
            <td>Current Contribution</td>
            <td>{{ number_format($opening_current_cont_employee,2) }}</td>
            <td>{{ number_format($opening_current_cont_employer,2) }}</td>
            <td>{{ number_format($opening_current_cont_employee_int,2) }}</td>
            <td>{{ number_format($opening_current_cont_employer_int,2) }}</td>
            <td>{{ number_format(($opening_current_cont_employee + $opening_current_cont_employer +
                    $opening_current_cont_employee_int + $opening_current_cont_employer_int),2) }}</td>
        </tr>

        {{--Current Refund --}}
        <tr>
            <td>Current Refund</td>
            <td>{{ number_format($current_openingrefund_employee,2) }}</td>
            <td>{{ number_format($current_openingrefund_employer,2) }}</td>
            <td>{{ number_format(($current_openingrefund_employee_interest + $as_on_current_openingrefund_employee_interest),2) }}</td>
            <td>{{ number_format(($current_openingrefund_employer_interest + $as_on_current_openingrefund_employer_interest),2) }}</td>
            <td>{{ number_format($current_opening_refund_disbursed_amount,2) }}</td>
        </tr>

        {{--as on date interest opening--}}
        <tr>
            <td>As on Interest</td>
            <td>0.00</td>
            <td>0.00</td>
            <td>{{ number_format($as_on_interest_accrued_employee,2) }}</td>
            <td>{{ number_format($as_on_interest_accrued_employer,2) }}</td>
            <td>{{ number_format($as_on_total_interest,2) }}</td>
        </tr>
        {{--as on date interest opening--}}

        {{--Net Opening Balance--}}
        <tr>
            <td><strong>Net Balance</strong></td>
            <td><strong>{{ number_format(($opening_cont_employee + $opening_current_cont_employee),2) }}</strong></td>
            <td><strong>{{ number_format(($opening_cont_employer + $opening_current_cont_employer),2) }}</strong></td>
            <td><strong>{{ number_format(($opening_cont_employee_interest + $opening_current_cont_employee_int + $as_on_interest_accrued_employee),2) }}</strong></td>
            <td><strong>{{ number_format(($opening_cont_employer_interest + $opening_current_cont_employer_int + $as_on_interest_accrued_employer),2) }}</strong></td>
            <?php           

            $netPayableBalance = (($opening_total - $opening_refund_disbursed_amount) + (($opening_current_cont_employee + $opening_current_cont_employer +
                    $opening_current_cont_employee_int + $opening_current_cont_employer_int) - $current_opening_refund_disbursed_amount) + $as_on_total_interest);
            ?>
            <td><strong>{{number_format($netPayableBalance,2) }}</strong></td>
        </tr>
        {{--Net Opening Balance--}}
        <tr><td colspan="6" style="border:none;">&nbsp;</td></tr>
        <tr><td colspan="6" style="border:none;">&nbsp;</td></tr>
        </tbody>
    </table>
    {{--Summary Report END--}}

    <?php
    $total_employee_balance = 0;
    $total_employer_balance = 0;
    $total_employee_interest_balance = 0;
    $total_employer_interest_balance = 0;
    $total_disbursed_balance_amount = 0;
    $total_contribution_emp_emplr = 0;
    ?>

    {{--Detail statement Table--}}
    <table class="table-bordered">
        <thead>
        <tr>
            <th colspan="11" align="left" style="border:none;">DETAIL STATEMENT REPORT</th>
        </tr>
        <tr>
            <th rowspan="2">Sl.No</th>
            <th rowspan="2">Due Month</th>
            <th rowspan="2">Reference No</th>
            <th rowspan="2">Trns. Type</th>
            <th rowspan="2">Receipt Date</th>
            <th rowspan="1" colspan="2">Contribution Details</th>
            <th rowspan="1" colspan="2">Interest Charged</th>
            <th rowspan="2">Disbursed Amt</th>
            <th rowspan="2">Total</th>
        </tr>
        <tr>
            <th>Employee Cont.</th>
            <th>Employer Cont.</th>
            <th>Int. on Employee Cont.</th>
            <th>Int. on Employer Cont.</th>
        </tr>
        </thead>
        <tbody>

        @foreach($current_year_cont_details as $key=> $data)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $data->for_the_month }}, {{ $data->for_the_year }}</td>
                <td>{{ $data->transaction_ref_no }}</td>
                <td>{{ $data->transaction_type }}</td>
                <td>{{ $data->transaction_date }}</td>
                <td>{{ number_format($data->total_employee_contribution,2) }}</td>
                <td>{{ number_format($data->total_employer_contribution,2) }}</td>
                <td>{{ number_format($data->total_interest_employee,2) }}</td>
                <td>{{ number_format($data->total_interest_employer,2) }}</td>
                <td>{{ number_format($data->disbursed_amount,2) }}</td>
                <?php
                $total = $data->total_employee_contribution + $data->total_employer_contribution + $data->total_interest_employee + $data->total_interest_employer + $data->disbursed_amount;
                $total_employee_balance = $data->total_employee_contribution + $total_employee_balance;
                $total_employer_balance = $data->total_employer_contribution + $total_employer_balance;
                $total_employee_interest_balance = $data->total_interest_employee + $total_employee_interest_balance;
                $total_employer_interest_balance = $data->total_interest_employer + $total_employer_interest_balance;
                $total_disbursed_balance_amount = $data->disbursed_amount + $total_disbursed_balance_amount;
                $total_contribution_emp_emplr = $total_employee_balance + $total_employer_balance +
                    $total_employee_interest_balance + $total_employer_interest_balance +
                     $opening_total-$total_disbursed_balance_amount+$as_on_total_interest;
                ?>
                <td>{{ number_format($total,2) }}</td>
            </tr>
        @endforeach
        <tr class="bg-white">
            <td colspan="5" class="td">
                <Strong>
                    <center>Total</center>
                </Strong>
            </td>
            <td class="text-center"><strong>{{ number_format($total_employee_balance,2) }}</strong></td>
            <td class="text-center"><strong>{{ number_format($total_employer_balance,2) }}</strong></td>
            <td class="text-center"><strong>{{ number_format($total_employee_interest_balance,2) }}</strong></td>
            <td class="text-center"><strong>{{ number_format($total_employer_interest_balance,2) }}</strong></td>
            <td class="text-center"><strong>{{ number_format($total_disbursed_balance_amount,2) }}</strong></td>
            <td class="text-center"><strong>{{ number_format($total_contribution_emp_emplr,2) }}</strong></td>
        </tr>

        Total
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>

        <tr>
            <th colspan="11" align="left" style="border:none; font-size: small">NET PAYABLE AS ON DATE : {{ $to_Date }}, &nbsp;
                Nu. {{ number_format(($opening_total_contribution_for_net_balance + $opening_total_interest_for_net_balance - $opening_refund_for_net_balance + $current_total_contribution_for_net_balance + $current_total_interest_for_net_balance -  $current_total_refund_for_net_balance + $as_on_total_interest),2) }}/-
            </th>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        </tbody>
    </table>
    {{--Table End Section--}}

    <div style="text-align: right">
        <div>
            <span><strong>For Bhutan Insurance Limited</strong></span>
        </div>
        <br><br><br>
        <div>
            <span><strong>Authority Signatory</strong></span>
        </div>
    </div>
</div>
</body>
</html>
