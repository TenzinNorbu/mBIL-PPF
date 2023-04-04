<html>
<head>
    <title>Company Wise Statement</title>
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
                <th colspan="2" style="border: none;"><h2>{{ $regType }} COMPANY WISE STATEMENT</h2></th>
            @else
                <th colspan="2" style="border: none;"><h2>COMPANY WISE STATEMENT</h2></th>
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
            <th width="90%" align="left" style="border: none;">{{ $company_name }}</th>
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

    @if($opening_contribution != NULL)
        <?php
        $opening_cont_employee = $opening_contribution->total_employee_contribution;
        $opening_cont_employer = $opening_contribution->total_employer_contribution;
        $opening_cont_interest_employee = $opening_contribution->total_interest_employee;
        $opening_cont_interest_employer = $opening_contribution->total_interest_employer;
        ?>
    @else
        <?php
        $opening_cont_employee = 0;
        $opening_cont_employer = 0;
        $opening_cont_interest_employee = 0;
        $opening_cont_interest_employer = 0;
        ?>
    @endif
    @if($opening_refund != NULL)
        <?php
        $opening_refund_employee = $opening_refund->total_employee_contribution;
        $opening_refund_employer = $opening_refund->total_employer_contribution;
        $opening_refund_interest_employee = $opening_refund->refund_interest_employee;
        $opening_refund_interest_employer = $opening_refund->refund_interest_employer;
        $opening_refund_disbursed_amount = $opening_refund->refund_disbursed_amount;

        ?>
    @else
        <?php
        $opening_refund_employee = 0;
        $opening_refund_employer = 0;
        $opening_refund_interest_employee = 0;
        $opening_refund_interest_employer = 0;
        $opening_refund_disbursed_amount = 0;
        ?>
    @endif

    @if($current_contribution != NULL)
        <?php
        $opening_current_cont_employee = $current_contribution->total_employee_contribution;
        $opening_current_cont_employer = $current_contribution->total_employer_contribution;
        $opening_current_cont_interest_employee = $current_contribution->total_interest_employee;
        $opening_current_cont_interest_employer = $current_contribution->total_interest_employer;
        ?>
    @else
        <?php
        $opening_current_cont_employee = 0;
        $opening_current_cont_employer = 0;
        $opening_current_cont_interest_employee = 0;
        $opening_current_cont_interest_employer = 0;
        ?>
    @endif

    @if($current_refund != NULL)
        <?php
        $opening_current_refund_employee = $current_refund->total_employee_contribution;
        $opening_current_refund_employer = $current_refund->total_employer_contribution;
        $opening_current_refund_interest_employee = ($current_refund->refund_interest_employee + $current_refund->as_on_refund_employee_interest);
        $opening_current_refund_interest_employer = ($current_refund->refund_interest_employer + $current_refund->as_on_refund_employer_interest);
        $opening_current_refund_disbursed = $current_refund->refund_disbursed_amount;
        ?>
    @else
        <?php
        $opening_current_refund_employee = 0;
        $opening_current_refund_employer = 0;
        $opening_current_refund_interest_employee = 0;
        $opening_current_refund_interest_employer = 0;
        $opening_current_refund_disbursed = 0;
        ?>
    @endif

    {{--summary report--}}
    <table class="table table-bordered">
        <thead>
        <tr>
            <th colspan="6" align="left" style="border:none;">SUMMARY REPORT</th>
        </tr>
        <tr>
            <th>Particulars</th>
            <th>Employee's Cont.</th>
            <th>Employer's Cont.</th>
            <th>Int. on Employee Cont.</th>
            <th>Int.on Employer Cont.</th>
            <th>Total</th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td>Opening Contribution</td>
            <td>{{ number_format($opening_cont_employee,2) }}</td>
            <td>{{ number_format($opening_cont_employer,2) }}</td>
            <td>{{ number_format($opening_cont_interest_employee,2) }}</td>
            <td>{{ number_format($opening_cont_interest_employer,2) }}</td>

            <?php
            $total_opening_contribution = $opening_cont_employee + $opening_cont_employer +
                $opening_cont_interest_employee + $opening_cont_interest_employer;
            ?>
            <td><strong>{{ number_format($total_opening_contribution,2) }}</strong></td>
        </tr>

        <tr>
            <td class="td">Opening Refund</td>
            <td>{{ number_format($opening_refund_employee,2) }}</td>
            <td>{{ number_format($opening_refund_employer,2) }}</td>
            <td>{{ number_format($opening_refund_interest_employee,2) }}</td>
            <td>{{ number_format($opening_refund_interest_employer,2) }}</td>
            <td>{{ number_format($opening_refund_disbursed_amount,2) }}</td>
        </tr>

        <tr>
            <td class="td">Current Contribution</td>
            <td>{{ number_format($opening_current_cont_employee,2) }}</td>
            <td>{{ number_format($opening_current_cont_employer,2) }}</td>
            <td>{{ number_format($opening_current_cont_interest_employee,2) }}</td>
            <td>{{ number_format($opening_current_cont_interest_employer,2) }}</td>
            <?php
            $total_current_contribution = $opening_current_cont_employee + $opening_current_cont_employer +
                $opening_current_cont_interest_employee + $opening_current_cont_interest_employer;
            ?>
            <td>{{ number_format($total_current_contribution,2) }}</td>
        </tr>

        <tr>
            <td class="td">Current Refund</td>
            <td>{{ number_format($opening_current_refund_employee,2) }}</td>
            <td>{{ number_format($opening_current_refund_employer,2) }}</td>
            <td>{{ number_format(($opening_current_refund_interest_employee),2) }}</td>
            <td>{{ number_format(($opening_current_refund_interest_employer),2) }}</td>
            <?php
                $current_total_opening_refund = $current_refund->refund_disbursed_amount;
            ?>

            <td>{{ number_format($current_total_opening_refund,2) }}</td>
        </tr>

        <tr class="table-striped">
            <td><strong>Net Balance</strong></td>
            <td><strong>{{ number_format(($opening_cont_employee + $opening_current_cont_employee),2) }}</strong></td>
            <td><strong>{{ number_format(($opening_cont_employer + $opening_current_cont_employer),2) }}</strong></td>
            <td><strong>{{ number_format(($opening_cont_interest_employee + $opening_current_cont_interest_employee),2) }}</strong></td>
            <td><strong>{{ number_format(($opening_cont_interest_employer + $opening_current_cont_interest_employer),2) }}</strong></td>
            <?php

            $net_total = $opening_cont_employee +  $opening_cont_employer + $opening_cont_interest_employee + $opening_cont_interest_employer - $opening_refund_disbursed_amount
            + $opening_current_cont_employee +  $opening_current_cont_employer + $opening_current_cont_interest_employee +  $opening_current_cont_interest_employer - $opening_current_refund_disbursed;
            ?>
            <td><strong>{{number_format($net_total,2) }}</strong></td>
        </tr>
        <tr>
            <td colspan="6" style="border:none;">&nbsp;</td>
        </tr>
        </tbody>
    </table>
    {{--summary report--}}

    {{--detail statement report--}}
    <table class="table table-bordered">
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

        <?php
        $total_employee_balance = 0;
        $total_employer_balance = 0;
        $total_employee_interest_balance = 0;
        $total_employer_interest_balance = 0;
        $total_disbursed_balance_amount = 0;
        $total_contribution_emp_emplr = 0;

        $opening_balance_amount = $opening_cont_employee +  $opening_cont_employer + $opening_cont_interest_employee + $opening_cont_interest_employer - $opening_refund_disbursed_amount;
        $closing_balance = $opening_balance_amount;
        ?>

        <tr>
            <th colspan='5'>Opening Balance</th>
            <th>{{ number_format($opening_cont_employee,2) }}</th>
            <th>{{ number_format($opening_cont_employer,2) }}</throw>
            <th>{{ number_format($opening_cont_interest_employee,2) }}</th>
            <th>{{ number_format($opening_cont_interest_employer,2) }}</th>
            <th>{{ number_format($opening_refund_disbursed_amount,2) }}</th>
            <th>{{ number_format($opening_balance_amount,2) }}</th>
        </tr>

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
                 $total_employee_balance = (float)$data->total_employee_contribution + $total_employee_balance;
                 $total_employer_balance = (float)$data->total_employer_contribution + $total_employer_balance;
                 $total_employee_interest_balance = (float)$data->total_interest_employee + $total_employee_interest_balance;
                 $total_employer_interest_balance = (float)$data->total_interest_employer + $total_employer_interest_balance;
                 $total_disbursed_balance_amount = (float)$data->disbursed_amount + $total_disbursed_balance_amount;

                if($data->transaction_type == 'Refund'){
                    $total = (float)$data->disbursed_amount;
                    $closing_balance =  $closing_balance + (float)$data->total_interest_employee + (float)$data->total_interest_employer - (float)$data->disbursed_amount;

                }else{
                    $total = (float)$data->total_employee_contribution + (float)$data->total_employer_contribution + (float)$data->total_interest_employee + (float)$data->total_interest_employer;
                    $closing_balance = $closing_balance + (float)$data->total_employee_contribution + (float)$data->total_employer_contribution + (float)$data->total_interest_employee + (float)$data->total_interest_employer;
                  }
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
            <td class="text-center"><strong>{{ number_format($closing_balance,2) }}</strong></td>
        </tr>

        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        Get Total End
        </tbody>
    </table>
    {{--detail statement report--}}

    <?php
    $today = \Carbon\Carbon::now()->format('Y-m-d');
    ?>

    <div>
        <strong>
            NET PAYABLE AMOUNT AS ON DATE : {{ $today }} &nbsp; : &nbsp; {{ number_format($net_total,2) }}/-
        </strong>
    </div>

    <div class="row" style="text-align: right">
        <br><br>
        <div>
            <strong>For Bhutan Insurance Limited</strong>
        </div>
        <br><br><br>
        <div>
            <strong>Authority Signatory</strong>
        </div>
    </div>
</div>

{{--Footer Part--}}
<div class="footer" id="footer">
    Bhutan Insurance Ltd, Chorten Lam, Post Box # 779, EPABX: +975 02 339893/339894, Fax #: +975 02 339895,
    Thimphu:Bhutan<br/>
    email: info@bhutaninsurance.com.bt, website: www.bhutaninsurance.com.bt
</div>
{{--Footer Part--}}

<style type="text/css">
    @page {
        margin: 100px 50px;
    }

    #header {
        position: fixed;
        left: 0px;
        top: -80px;
        right: 0px;
        height: 100px;
    }

    #footer {
        position: fixed;
        left: 0px;
        bottom: -90px;
        right: 0px;
        height: 50px;
        font-size: 11px;
        color: blue;
        text-align: center;
    }

    .header {
        width: 100%;
    }

    .footer {
        margin-top: 0;
        margin-bottom: 0;
    }

    .logo_and_bar_code {
        text-align: center;
    }
</style>

</body>
</html>
