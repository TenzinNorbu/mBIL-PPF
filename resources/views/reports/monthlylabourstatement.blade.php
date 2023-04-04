<html>
<head>
    <title>Monthly Labor Report</title>
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
            <th colspan="2" style="border: none;"><h2>{{ $registrationType }} MONTHLY LABOUR REPORT</h2></th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">For the Month : &nbsp; {{ $month }}</th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">For the Year : &nbsp; {{ $forTheYear }}</th>
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

    {{--table start--}}
    <table class="table-bordered">
        <thead>
        <tr>
            <th>Sl.No</th>
            <th>Cmp Account No</th>
            <th>Org. Name</th>
            <th>Org. Address</th>
            <th>Contact No . </th>
            <th>No. of Employees</th>
            <th>Last Payment Date</th>
            <th>Last Payment Amount</th>
            <th>For the Month/Year</th>
            <th>Due For the Month/Year</th>
            <th>OD Days</th>
        </tr>
        </thead>

        <tbody>
        @foreach($monthly_data_sql as $key=>$mthly_data)
            <tr>
                <td><center>{{ $key + 1 }}</center></td>
                <td>{{ $mthly_data->company_account_no }}</td>
                <td>{{ $mthly_data->company_name }}</td>
                <td>{{ $mthly_data->company_address }}</td>
                <td>{{ $mthly_data->contact_no }}</td>
                <td><center>{{ $mthly_data->no_of_employees }}</center></td>
                <td>{{ $mthly_data->last_payment_date }}</td>
                <td>{{ number_format($mthly_data->last_payment_amount,2) }}</td>
           
                @if($month != 'ALL')
                <td>{{ $month }}, {{ $forTheYear }}</td>
                @else
                <td></td>
                @endif

                @if($month != 'ALL')
                <?php
                
                  if($forTheMonth == 12){
                    $forTheMonth = 0;
                  } 

                  $dueMonth = $forTheMonth + 1;
                  $dueMonthName = App\Models\Month::where('id','=',$dueMonth)->get()->first()->month_name;
                 ?>
                <td>{{ $dueMonthName }}, {{ $forTheYear }}</td>
                @else
                <td></td>
                @endif
              
                <td><center>{{ $mthly_data->od_days }}</center></td>
            </tr>
        @endforeach

        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
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
