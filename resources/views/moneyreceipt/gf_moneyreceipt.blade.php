<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Money Receipt</title>
</head>
<body>

<style>
    .dashline {
        border: none;
        border-top: 1px dashed #f00;
        color: #fff;
        background-color: #fff;
        width: 100%;
    }
    hr.dashhr {
        border-top: 1px dashed #191c1f;
    }
</style>

<div class="container" style="height: 994px; margin-top: -25px; margin-bottom: -25px; ">
    <div class="row" style="height: 329px;">
        <div style="height: 300px;">
            @for($i = 0; $i < 3; $i++)
                <hr class="dashhr">
                <table style="font-size: 11px; cellpadding:-2px; cellspacing:-2px; border: 1px">
                    <tr>
                        <td colspan="3" align="center">
                            <b class="dashline"></b>
                            <img src="{{ storage_path('images/gf-bannerbil.png') }}" alt=""
                                 style="width: 300px; height: 60px;">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="center"></td>
                    </tr>
                    <tr>
                        <td colspan="3">&nbsp;</td>
                    </tr>

                    {{--top header--}}
                    <tr>
                        <td width="33.33%" style="border-bottom:1px solid #000;">
                            <b>Receipt No: {{ $receiptNumber }}</b>
                        </td>
                        <td width="33.33%" align="center" style="border-bottom:1px solid #000;"><b> MONEY RECEIPT </b>
                        </td>
                        <td width="33.33%" align="right" style="border-bottom:1px solid #000;">
                            <b>Receipt Date: {{ $collectionDate }} </b>
                        </td>
                    </tr>
                    {{--top header end--}}

                    <tr>
                        <td colspan="3" style="text-align:left;">
                            <p>
                                @if($colType == "Cash")
                                    Received with thanks from <strong><i><u>{{ $colAccountName }}</u></i>
                                        ( {{ $companyAccountNumber }} )</strong>, sum
                                    of
                                    Ngultrum <b>{{ number_format($colAmount,2) }}</b>/- ( {{ $numInWords }} ) Only by
                                    <b>{{ $colType }}</b>
                                    dated: <b>{{ $collectionDate }}</b> being the payment of <u><i> Gratuity
                                            Fund</i></u> for
                                    the Month of <b>{{ $forTheMonth }}</b>, <b>{{ $forTheYear }}</b>.
                                @elseif($colType == "Cheque")
                                    Received with thanks from <strong><i><u>{{ $colAccountName }}</u></i>
                                        ( {{ $companyAccountNumber }} )</strong>, sum
                                    of
                                    Ngultrum <b>{{ number_format($colAmount,2) }}</b>/- ( {{ $numInWords }} )  Only
                                    by <b>{{ $colType }}</b>,
                                    dated: <b>{{ $chequeDate }}</b>,
                                    Cheque No <b>{{ $chequeNo }}</b>, <b>{{ $colBankName }}</b> being the payment of <u><i>Gratuity
                                            Fund</i></u>
                                    for the Month of <b>{{ $forTheMonth }}</b>, <b>{{ $forTheYear }}</b>.
                                @endif
                            </p>
                        </td>
                    </tr>

                    @if($colType == "Cash")
                    <tr>
                        <td colspan="3">
                          Remark : {!! nl2br(e($narration)) !!}
                        </td>
                    </tr>
                    <tr><td colspan="3"></td></tr>
                    @elseif($colType == "Cheque")
                        <tr>
                            <td colspan="3">
                                Remark : {!! nl2br(e($narration)) !!} <br><br>
                                NOTE* :
                                THIS MONEY RECEIPT IS VALID SUBJECT TO REALIZATION OF THE CHEQUE AND COUNTER SIGNED BY
                                AN
                                AUTHORIZED
                                OFFICIAL
                                OF THE COMPANY.
                            </td>
                        </tr>
                    @endif

                    {{--footer start--}}
                    <tr>
                        <td colspan="3"><p><img src="{{ storage_path('images/letter-head-footer.jpg') }}" alt=""
                                                style="max-width: 100%"></p> &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align:right;"><b>For Bhutan Insurance Limited</b></td>
                    </tr>
                    {{--footer end--}}
                </table>
            @endfor
        </div>
    </div>
</div>
</body>
</html>
