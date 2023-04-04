<html>
<head>
    <title>CLosing Report</title>
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
                <img src="{{ public_path('images/letter-head-bil.png') }}" width="500" height="98" alt="logo">
            </th>
        </tr>
        <tr>
            <th colspan="2" style="border: none;"><h2>{{ $registrationType }} CLOSING REPORT</h2></th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">From Date: &nbsp; {{ $fromDate }}</th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">To Date: &nbsp; {{ $toDate }}</th>
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

    {{-- Table--}}
    <table class="table-bordered">
        <thead>
        <tr>
            <th colspan="9" align="left" style="border:none;"></th>
        </tr>
        <tr style="border: 1px;">
            <th rowspan="2">Sl.No</th>
            <th rowspan="2">Account No</th>
            <th rowspan="2">Company Name</th>
            <th rowspan="2">Interest Rate</th>
            <th rowspan="2">MOU Date</th>
            <th rowspan="1" colspan="2">Contribution Details</th>
            <th rowspan="1" colspan="2">Interest</th>
            <th rowspan="2" colspan="1" style="bordered">Total</th>
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
        $total_employee_cont = 0;
        $total_employer_cont = 0;
        $total_interest_employee = 0;
        $total_interest_employer = 0;
        ?>

        @foreach($closing_data as $key=> $data)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $data->account_no }}</td>
                <td>{{ $data->company_name }}</td>
                <td>{{ number_format($data->interest_rate,2) }}</td>
                <td>{{ $data->mou_date }}</td>  
                <td>{{ number_format($data->employee_contribution,2) }}</td>
                <td>{{ number_format($data->employer_contribution,2) }}</td>
                <td>{{ number_format($data->interest_employee,2) }}</td>
                <td>{{ number_format($data->interest_employer,2) }}</td>
                <?php
                  $total = $data->employee_contribution + $data->employer_contribution + $data->interest_employee + $data->interest_employer;
                  $total_employee_cont = $data->employee_contribution + $total_employee_cont;
                  $total_employer_cont = $data->employer_contribution + $total_employer_cont;
                  $total_interest_employee = $data->interest_employee + $total_interest_employee;
                  $total_interest_employer = $data->interest_employer + $total_interest_employer;
                 ?>
                <td>{{ number_format($total,2) }}</td>
            </tr>
            
        @endforeach
        <tr>
            <th colspan="5">Total</th>
            <th>{{ number_format($total_employee_cont,2) }}</th>
            <th>{{ number_format($total_employer_cont,2) }}</th>
            <th>{{ number_format($total_interest_employee,2) }}</th>
            <th>{{ number_format($total_interest_employer,2) }}</th>
            <th>{{ number_format(($total_employee_cont + $total_employer_cont +  $total_interest_employee + $total_interest_employer),2) }}</th>
        </tr>

        <tr>
            <td colspan="10" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="10" style="border:none;">&nbsp;</td>
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
