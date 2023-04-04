<html>
<head>
    <title>Monthly Deposit Report</title>
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
$getEmployeeId = '';
$employeeId = 0;
$departmentTotalEmployee = 0;
$departmentTotalEmployer = 0;
?>

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
            @if($registrationType != NULL || $registrationType != '')
                <th colspan="2" style="border: none;"><h2>{{ $registrationType }} MONTHLY DEPOSIT REPORT</h2></th>
            @else
                <th colspan="2" style="border: none;"><h2>MONTHLY DEPOSIT REPORT</h2></th>
            @endif
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">For Month : {{ $forTheMonth }} </th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;"> For Year : {{ $for_the_year }} </th>
        </tr>
        <tr>
            @if($registrationType != NULL || $registrationType != '')
                <th width="100%" align="left" style="border: none;">Business Type : {{ $registrationType }}</th>
            @else
                <th width="100%" align="left" style="border: none;">Business Type : ALL</th>
            @endif
        </tr>
        <tr>
            <th width=100%" align="left" style="border: none;">Company Name : {{ $companyName }}</th>
            <?php $processingDate = \Carbon\Carbon::now()->format('d-m-Y') ?>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Processing Date : {{ $processingDate }} </th>
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
            <th>Receipt No</th>
            <th>Account Number</th>
            <th>Employee Name</th>
            <th>From Month/Year</th>
            <th>Employee Cont.</th>
            <th>Employer Cont.</th>
            <th>Total</th>
        </tr>
        </thead>

        <tbody>
        @foreach($get_monthly_sql_data as $data)
            <tr>
                <td>{{ $data->voucher_no }}</td>
                <?php
                $departmentTotalEmployee = $departmentTotalEmployee + $data->total_employee_contribution;
                $departmentTotalEmployer = $departmentTotalEmployer + $data->total_employer_contribution;
                ?>
                <td>{{ $data->employee_ac_no }}</td>
                <td>{{ $data->employee_name }}</td>
                <td>{{ $data->for_the_month }} , {{ $data->for_the_year }}</td>
                <td>{{ number_format($data->total_employee_contribution,2) }}</td>
                <td>{{ number_format($data->total_employer_contribution,2) }}</td>
                <td>{{ number_format(($data->total_employee_contribution + $data->total_employer_contribution),2) }}  </td>
            </tr>
        @endforeach

        {{-- Get Total--}}
        <tr>
            <td colspan="4">
                <Strong>
                    <center>Total Amount</center>
                </Strong>
            </td>
            <td colspan="1"><strong>{{ number_format($departmentTotalEmployee,2) }}</strong></td>
            <td colspan="1"><strong>{{ number_format($departmentTotalEmployer,2) }}</strong></td>
            <td colspan="1">
                <strong>{{ number_format(($departmentTotalEmployee+$departmentTotalEmployer),2) }}</strong>
            </td>
        </tr>
        <tr>
            <td colspan="7" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="7" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="7" style="border:none;">&nbsp;</td>
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
