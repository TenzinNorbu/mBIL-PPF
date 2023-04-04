<html>
<head>
    <title>Daily Collection Report</title>
</head>
<body>
<style>

    @page {
        margin: 100px 50px;
    }

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
            @if($registrationType != NULL || $registrationType != '')
                <th colspan="2" style="border: none;"><h2>{{ $registrationType }} DAILY COLLECTION STATEMENT</h2></th>
            @else
                <th colspan="2" style="border: none;"><h2>DAILY COLLECTION STATEMENT</h2></th>
            @endif
        </tr>
        <tr>
            <th width="10%" align="left" style="border: none;">Start Date :</th>
            <th width="90%" align="left" style="border: none;">{{ $from_date }}</th>
        </tr>
        <tr>
            <th width="10%" align="left" style="border: none;">End Date :</th>
            <th width="90%" align="left" style="border: none;">{{ $to_date }}</th>
        </tr>
        <tr>
            <th width="10%" align="left" style="border: none;">Processing Date :</th>
            <th width="90%" align="left" style="border: none;">{{ $processing_date }}</th>
        </tr>
        <tr>
            <th width="10%" align="left" style="border: none;">Branch :</th>
            <th width="90%" align="left" style="border: none;">{{ $branch_name }}</th>
        </tr>
        <tr>
            <th width="10%" align="left" style="border: none;">Business Type :</th>
            @if($registrationType != NULL || $registrationType != '')
                <th width="90%" align="left" style="border: none;">{{ $registrationType }}</th>
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

    {{-- Table--}}
    <table class="table-bordered">
        <thead>
        <tr>
            <th>Sl.No</th>
            <th>Voucher Date</th>
            <th>Voucher No</th>
            <th>Organization</th>
            <th>MOU Date</th>
            <th>Branch</th>
            <th>Trans. Mode</th>
            <th>Cheque date</th>
            <th>Inst No</th>
            <th>Bank Name</th>
            <th>Cheque Amt.</th>
            <th>Cash Amt.</th>
        </tr>
        </thead>
        <tbody>

        <?php
        $account_transaction_mode = 0;
        $account_cheque_amount = 0;
        $account_cash_amount = 0;
        $total_cash_amount = 0;
        $total_cheque_amount = 0;
        $grandTotal = 0;
        ?>

        @if(empty($collection_data) || $collection_data == '' || $collection_data == NULL || $collection_data->count() == 0)
            @foreach($old_collection_data as $key => $data)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $data->pf_collection_date }}</td>
                    <td>{{ $data->pf_collection_no }}</td>
                    <td>{{ $data->collectionCompany->org_name }}</td>
                    <td></td>
                    <td>{{ $data->getBranchData->branch_name }}</td>
                    <td>{{ $data->collection_transaction_mode }}</td>
                    <td>{{ $data->collection_cheque_date }}</td>
                    <td>{{ $data->collection_instrument_no }}</td>
                    <td>{{ $data->collection_bank_type }}</td>

                    <?php
                    $account_transaction_mode = $data->collection_transaction_mode;
                    if ($account_transaction_mode == 'Cash') {
                        $account_cash_amount = $data->pf_collection_amount;
                        $account_cheque_amount = 0;
                    } else {
                        $account_cheque_amount = $data->pf_collection_amount;
                        $account_cash_amount = 0;
                    }

                    $total_cash_amount = $account_cash_amount + $total_cash_amount;
                    $total_cheque_amount = $account_cheque_amount + $total_cheque_amount;
                    $grandTotal = $total_cash_amount + $total_cheque_amount;
                    ?>

                    <td>{{ number_format($account_cheque_amount,2) }}</td>
                    <td>{{ number_format($account_cash_amount,2) }}</td>
                </tr>
            @endforeach
        @else
            {{--NEW DATA--}}
            @foreach($collection_data as $key => $data)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $data->account_voucher_date }}</td>
                    <td>{{ $data->account_voucher_number }}</td>
                    <td>{{ $data->pfColectionData->getCompanyData->org_name }}</td>
                    <td>{{ date('d-m-Y', strtotime($data->pfColectionData->getCompanyData->getPfMouDetails->mou_date)) }}</td>
                    <td>{{ $data->pfColectionData->getBranchData->branch_name }}</td>
                    <td>{{ $data->account_transaction_mode }}</td>
                    <td>{{ $data->account_cheque_date }}</td>
                    <td>{{ $data->account_collection_instrument_no }}</td>
                    <td>{{ $data->account_collection_bank }}</td>

                    <?php
                    $account_transaction_mode = $data->account_transaction_mode;
                    if ($account_transaction_mode == 'Cash') {
                        $account_cash_amount = $data->account_voucher_amount;
                        $account_cheque_amount = 0;
                    } else {
                        $account_cheque_amount = $data->account_voucher_amount;
                        $account_cash_amount = 0;
                    }

                    $total_cash_amount = $account_cash_amount + $total_cash_amount;
                    $total_cheque_amount = $account_cheque_amount + $total_cheque_amount;
                    $grandTotal = $total_cash_amount + $total_cheque_amount;
                    ?>

                    <td>{{ number_format($account_cheque_amount,2) }}</td>
                    <td>{{ number_format($account_cash_amount,2) }}</td>
                </tr>
            @endforeach
            {{--NEW DATA--}}
        @endif

        {{--Get Total--}}
        <tr>
            <td colspan="10"><strong>Total</strong></td>
            <td><strong>{{ number_format($total_cheque_amount,2) }}</strong></td>
            <td><strong>{{ number_format($total_cash_amount,2) }}</strong></td>
        </tr>
        <tr>
            <td colspan="10"><strong>Grand Total</strong></td>
            <td colspan="2"><strong>{{ number_format($grandTotal,2) }}</strong></td>
        </tr>
        {{--Get Total End--}}
        </tbody>
    </table>
    {{--Table End --}}
</div>

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
