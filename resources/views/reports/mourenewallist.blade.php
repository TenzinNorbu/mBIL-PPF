<html>
<head>
    <title>MOU Renewal List Report</title>
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
            <th colspan="2" style="border: none;"><h2>MOU Renewal List Report</h2></th>
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
            <th>Monthly Cont Amount</th>
            <th>MOU Date</th>
            <th>MOU Expiry Date</th>
            <th>Interest Rate (%)</th>
            <th>Contact Person Details</th>
            <th>Proprietor Details</th>
        </tr>
        </thead>

        <tbody>
        @if(!empty($renewal_lists))
            @foreach($renewal_lists as $key=>$renewal_data)
                <tr>
                    
        
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $renewal_data->company_name }}</td>
                    <td>{{ $renewal_data->monthly_cont_amount }}</td>
                    <td>{{ $renewal_data->mou_date }}</td>
                    <td>{{ $renewal_data->mou_expiry_date }}</td>
                    <td>{{ $renewal_data->interest_rate }}</td>
                    <td>{{str_replace(';',"\n",$renewal_data->contact_person_details)}}</td>
                    <td>{{str_replace(';',"\n",$renewal_data->proprietor_details)}}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
            </tr>
        @endif
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
