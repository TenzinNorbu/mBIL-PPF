<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>GF REFUND PAYMENT VOUCHER</title>
</head>
<body>
<style type="text/css">
    table {
        width: 100%;
        max-width: 100%;
        border-spacing: 0;
        border-collapse: collapse;
        page-break-before:avoid;
        page-break-after:avoid;
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
            <th colspan="2" style="border: none;"><h2>GF REFUND PAYMENT VOUCHER</h2></th>
        </tr>
        <tr>
            <th colspan="2" style="border: none;">&nbsp;</th>
        </tr>
        <tr>
            <th width="50%" align="left">Voucher No : </th>
            <th width="50%" align="left"> &nbsp; {{ $voucher_number }}</th>
        </tr>

        <tr>
            <th width="50%" align="left">Voucher Date : </th>
            <th width="50%" align="left">&nbsp; {{ $paymentDate }}</th>
        </tr>

        <tr>
            <th width="50%" align="left">Voucher Amount : </th>
            <th width="50%" align="left">&nbsp; {{ number_format($total_payable_amount,2) }}</th>
        </tr>

        <tr>
            <th width="50%" align="left">Account No  : </th>
            <th width="50%" align="left">&nbsp; {{ $col_bank_acc_no }}</th>
        </tr>

        <tr>
            <th width="50%" align="left">Cheque No  : </th>
            <th width="50%" align="left">&nbsp; {{ $instrument_no }}</th>
        </tr>

        <tr>
            <th width="50%" align="left">Cheque Date  : </th>
            <th width="50%" align="left">&nbsp; {{ $cheque_date }}</th>
        </tr>

        <tr>
            <th width="100%" align="left" colspan="2">Payment Narration : {{ $refundPaymentRemarks }}</th>
        </tr>
        <tr>
            <td colspan="2" style="border:none;">&nbsp;</td>
        </tr>
        </thead>
    </table>
    {{--table top header info--}}

    <table class="table table-bordered" style="border: 1px solid #111">
        <thead>
        <tr>
            <th style="border: 1px solid #111">Particulars</th>
            <th style="border: 1px solid #111">Voucher Details</th>
            <th style="border: 1px solid #111">Amount Debited</th>
            <th style="border: 1px solid #111">Amount Credited</th>
        </tr>
        </thead>

        <tbody>
        <?php
        $refundTransactionCategory = [];
        $total_debit = 0;
        $total_credit = 0;
        ?>

        @foreach($refund_payment_requests as $refund_detail_data)

            <?php
            $refundTransactionCategory = ['PfColRefundPayableAc', 'BankAccount'];
            ?>
            @foreach($refundTransactionCategory as $refundTransaction)

                @if($refundTransaction == 'PfColRefundPayableAc')
                    <?php
                    //GF Collection Refund Payable Acc
                    $particular_pfCollectionPayableAc = \App\Models\Accounttype::where('account_type_id', '=', 'A2AC6540-C3BC-11EC-A548-07FC7107F9BF')
                        ->where('account_group_id', '=', '987A9B20-45DE-11EC-973C-47DC726D5DD3')
                        ->get();
                    $particular01 = $particular_pfCollectionPayableAc->first()->acc_name;
                    $particular01_code = $particular_pfCollectionPayableAc->first()->acc_code;
                    ?>

                    <tr>
                        <td style="border: 1px solid #111">GF Refund Payable</td>
                        <td style="border: 1px solid #111">{{ ESolution\DBEncryption\Encrypter::decrypt($companyAccountNumber) }} [{{ $particular01_code }}]/{{ $particular01 }}
                            /{{ $refund_detail_data['payment_refund_ref_no'] }}</td>
                        <td style="border: 1px solid #111">{{ number_format((float)$refund_detail_data['payment_total_amount'],2) }}</td>
                        <td style="border: 1px solid #111">0</td>
                    </tr>
                    <?php
                        $total_debit = (float)$refund_detail_data['payment_total_amount'] + $total_debit;
                    ?>
                @else
                    <?php
                    $particular03_bankaccount = \App\Models\Accounttype::where('account_type_id', '=', $acc_account_type_id)
                        ->where('account_group_id', '=', 'A7621450-421A-11EC-858F-9BE7FA733BC4')
                        ->get();
                    $particular03 = $particular03_bankaccount->first()->acc_name;
                    ?>

                    <tr>
                        <td style="border: 1px solid #111">{{ $particular03 }}</td>
                        <td style="border: 1px solid #111">{{ ESolution\DBEncryption\Encrypter::decrypt($companyAccountNumber) }}/{{ $particular03 }}
                            /{{ $refund_detail_data['payment_refund_ref_no'] }}</td>
                        <td style="border: 1px solid #111">0</td>
                        <td style="border: 1px solid #111">{{ number_format((float)$refund_detail_data['payment_total_amount'],2) }}</td>
                    </tr>

                    <?php
                         $total_credit = (float)$refund_detail_data['payment_total_amount'] + $total_credit;
                    ?>
                @endif

            @endforeach
           
        @endforeach

        <tr style="border: 1px solid #111">
            <td colspan="2" style="border: 1px solid #111"><b>Total</b></td>
            <td style="border: 1px solid #111"><b>{{ number_format($total_debit,2) }}</b></td>
            <td style="border: 1px solid #111"><b>{{ number_format($total_credit,2) }}</b></td>
        </tr>

    </table>

    <table class="table table-bordered" style="border-collapse: collapse; border: none;">
        <tr>
            <td colspan="4" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="4" style="border:none;">
                <span><i>(Ngultrum in words) </i><strong> {{ $numbertowords_payable }} only</strong></span>
                <br><br><br><br>
            </td>
        </tr>
        </tbody>

        <tr style="border: none;">
            <th style="border: none;" width="50%" align="left">Prepared by: &nbsp; Sagar Rai <br>
                (Finance Department)
            </th>
            <th style="border: none;" width="50%" align="left">Verified by: &nbsp; Sangay Tshomo</th>
        </tr>

        <tr style="border: none;">
            <td colspan="2"> &nbsp;</td>
        </tr>
        <tr style="border: none;">
            <td colspan="2"> &nbsp;</td>
        </tr>
        <tr style="border: none;">
            <td colspan="2"> &nbsp;</td>
        </tr>
        <tr style="border: none;">
            <th style="border: none;" width="50%" align="left">Passed By : Jigme Yonten <br>
                (Finance Manager)
            </th>
            <th style="border: none;" width="50%" align="left">Received By:</th>
        </tr>
        <tr style="border: none;">
            <td colspan="2"> &nbsp;</td>
        </tr>
        <tr style="border: none;">
            <td colspan="2"> &nbsp;</td>
        </tr>
        <tr style="border: none;">
            <td colspan="2"> &nbsp;</td>
        </tr>
        <tr style="border: none;">
            <th style="border: none;" width="100%" align="left">ID No. & Contact No:</th>
        </tr>
    </table>

</div>
</body>
</html>
