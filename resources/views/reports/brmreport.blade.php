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
                <img src="{{ public_path('images/letter-head-bil.png') }}" width="500" height="98" alt="logo">
            </th>
        </tr>
        <tr>
            <th colspan="2" style="border: none;"><h2>{{ $registrationType }} BRM REPORT</h2></th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">From Date : &nbsp; {{ $fromDate }}</th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">To Date : &nbsp; {{ $toDate }}</th>
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
            <th>Company Account No</th>
            <th>Company Name</th>
            <th>Registration Date</th>
            <th>Additional</th>
            <th>New</th>
            <th>Business Code</th>
        </tr>
        </thead>

        <tbody>
          <?php
            $total_additional_amount = 0;
            $total_new_amount = 0;
          ?>
        @foreach($brm_data as $key=>$data)
            <tr>
                <td><center>{{ $key + 1 }}</center></td>
                <td>{{ $data->company_account_no }}</td>
                <td>{{ $data->company_name }}</td>
                <td>{{ $data->effective_start_date }}</td>

                <?php
                    $total_additional_amount = $data->additional + $total_additional_amount;
                    $total_new_amount = $data->new + $total_new_amount;

                    $additional_amount = $data->additional;
                    $new_amount = 0;
                    $branchData = App\Models\Branch::where('id','=',$data->pf_collection_branch_id)->get();

                    $branchCode = $branchData->first()->branch_code;
                    $branchName = $branchData->first()->branch_name;

                    if ($additional_amount == 0 && $new_amount == $data->new) {
                      $branchCode = $branchData->first()->branch_code;
                      $branchName = $branchData->first()->branch_name;
                    } else {
                      $branchCode = '';
                      $branchName = '';
                    }
                 ?>
                <td>{{ number_format($data->additional,2) }}</td>
                <td>{{ $data->new }}</td>
                <td>{{ $branchCode }} &nbsp; {{ $branchName }}</td>
            </tr>
        @endforeach
        <tr>
          <td colspan="4"><strong><center>Total</center></strong></td>
          <td><strong><center>{{ number_format($total_additional_amount,2) }}</center></strong></td>
          <td><strong><center>{{ number_format($total_new_amount,2) }}</center></strong></td>
          <td></td>
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
        <?php
          $userData = auth('api')->user();
          $user_name = $userData->name;
          $user_dept_id = $userData->users_department_id;
          $user_department = App\Models\Department::where('id','=',$user_dept_id)->get()->first()->department_name;
        ?>
        <div>
            <span><strong>{{ $user_name }}</strong></span>
        </div>
        <div>
            <br>
            <span><strong>{{ $user_department }}</strong></span>
        </div>
    </div>
</div>
</body>
</html>
