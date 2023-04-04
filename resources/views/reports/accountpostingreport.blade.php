<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>Account Posting Voucher</title>
</head>
<body>
<style type="text/css">
    table {
        width: 100%;
        max-width: 100%;
        border-spacing: 0;
        border-collapse: collapse;
        page-break-before: avoid;
        page-break-after: avoid;
    }

    .table-bordered > thead > tr > td > thead > tr > th {
        border: 1px solid #000;
        font-size: 12px;
    }

    .table-bordered > tbody > tr > td > tbody > tr > th {
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
        margin: 10px 50px;
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
    <table class="table table-bordered" style="border-collapse: collapse;">
        <thead>
        <tr>
            <th colspan="2" style="border: none;">
                <img src="{{ public_path('images/letter-head-bil.png') }}" width="500" height="95" alt="logo">
            </th>
        </tr>
        <tr>
            <th colspan="2" style="border: none;"><h2>Account Posting Voucher</h2></th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Voucher No :</th>
            <th width="85%" align="left" style="border: none;">{{ $voucher_number }}</th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Voucher Type :</th>
            <th width="85%" align="left" style="border: none;">{{ $voucherType }}</th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Voucher Date :</th>
            <th width="85%" align="left" style="border: none;">{{ $voucherDate }}</th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Posting Amount :</th>
            <th width="85%" align="left" style="border: none;">{{ number_format($postingAmount,2) }}</th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Cheque Date :</th>
            <th width="85%" align="left" style="border: none;">{{ $instrumentDate }}</th>
        </tr>
        <tr>
            <th width="15%" align="left" style="border: none;">Cheque No :</th>
            <th width="85%" align="left" style="border: none;">{{ $instrumentNo }}</th>
        </tr>
        <tr>
            <th width="10%" align="left" style="border: none;">Narration:</th>
            <th width="90%" align="left" style="border: none;">{{ $voucherNarration }}</th>
        </tr>
        <tr>
            <td colspan="2" style="border:none;">&nbsp;</td>
        </tr>
        </thead>
    </table>
    {{--table top header info--}}

    {{--table start--}}
    <table class="table-bordered" style="border-collapse: collapse;">
        <thead>
        <tr>
            <th>Sl.No</th>
            <th>Voucher Details</th>
            <th>Amount DR</th>
            <th>Amount CR</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $total_credit_amount = 0;
        $total_debit_amount = 0;
        ?>
        @foreach($accountLedgerData as $key=>$value)
            <?php
            $ledgerName = $accountLedgerData[$key]['account_ledger'];
            $ledgerType = $accountLedgerData[$key]['debit_credit'];
            $ledgerAmount = $accountLedgerData[$key]['amount'];

            if ($ledgerType == 'DR') {
                $credit_amount = 0;
                $debit_amount = $ledgerAmount;
            } else {
                $credit_amount = $ledgerAmount;
                $debit_amount = 0;
            }

            $total_credit_amount = $total_credit_amount + $credit_amount;
            $total_debit_amount = $total_debit_amount + $debit_amount;
            ?>

            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $ledgerName }}</td>
                <td>{{ number_format($debit_amount,2) }}</td>
                <td>{{ number_format($credit_amount,2) }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="2">Total</td>
            <td>{{ number_format($total_credit_amount,2) }}</td>
            <td>{{ number_format($total_debit_amount,2) }}</td>
        </tr>
        <tr>
            <td colspan="4" style="border: none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" style="border: none;">&nbsp;</td>
        </tr>
        </tbody>
    </table>
    {{--Table End Section --}}

    {{--End Section--}}
    <table class="table table-bordered" style="border-collapse: collapse;">
        <tr>
            <td colspan="8" style="border: none;">&nbsp;</td>
        </tr>
        <tr>
            <th width="5%" align="left" style="border: none;">Prepared By :</th>
            <th width="20%" align="left" style="border: none;"></th>

            <th width="5%" align="left" style="border: none;">Received By :</th>
            <th width="20%" align="left" style="border: none;"></th>

            <th width="5%" align="left" style="border: none;">Verified By :</th>
            <th width="20%" align="left" style="border: none;"></th>

            <th width="5%" align="left" style="border: none;">Approved By :</th>
            <th width="20%" align="left" style="border: none;"></th>
        </tr>

    </table>
    {{--End Section--}}

</div>
</body>
</html>
{{--@endsection--}}
