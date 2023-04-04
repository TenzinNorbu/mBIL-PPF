<html>
<head>
    <title>Company Employees Report</title>
</head>

<!---- Header & Footer Section start------->
<div class="header" id="header">
    <div class="logo_and_bar_code">
        <img src="{{ public_path('images/letter-head-bil.png') }}" width="500" height="95" alt="logo">
    </div>
</div>

<div class="footer" id="footer">
    Bhutan Insurance Ltd, Chorten Lam, Post Box # 779, EPABX: +975 02 339893/339894, Fax #: +975 02 339895, Thimphu:Bhutan<br/>
    email: info@bhutaninsurance.com.bt, website: www.bhutaninsurance.com.bt
</div>
<!---- Header & Footer Section Ends------->

<body>
<?php
    $to_Date = \Carbon\Carbon::parse($request->to_date)->format('d-m-Y');
    $from_Date = \Carbon\Carbon::parse($request->from_date)->format('d-m-Y');
?>
@foreach($employeeWiseData as $key=>$employeeData)

<?php
    $opening_contribution = collect(DB::select("SELECT company_ref_id,
                sum(COALESCE(employee_contribution,0)) as total_employee_contribution,
                sum(COALESCE(employer_contribution,0)) as total_employer_contribution,
                sum(interest_accrued_employee_contribution) as total_interest_employee,
                sum(interest_accrued_employer_contribution) as total_interest_employer
                from pfstatements
                where registration_type = '$registrationType' AND transaction_date < '$fromDate'
                AND employee_ref_id = '$employeeData->pf_employee_id'
                GROUP BY company_ref_id"))->first();

    $opening_refund = collect(DB::SELECT("SELECT
                COALESCE(SUM(refund_employee_contribution),0) as total_employee_contribution,
                COALESCE(SUM(refund_employer_contribution),0) as total_employer_contribution,
                COALESCE(SUM(refund_interest_on_employee_contr),0) as refund_interest_employee,
                COALESCE(SUM(refund_interest_on_employer_contr),0) as refund_interest_employer,
                COALESCE(SUM(refund_as_on_interest_employee),0) as as_on_refund_employee_interest,
                COALESCE(SUM(refund_as_on_interest_employer),0) as as_on_refund_employer_interest,
                COALESCE(SUM(refund_total_disbursed_amount),0) as refund_disbursed_amount
                from pfstatements
                INNER JOIN refunds ON refunds.refund_ref_no = pfstatements.transaction_ref_no
                WHERE transaction_date < '$fromDate'
                AND refunds.registration_type = '$registrationType'
                AND employee_ref_id = '$employeeData->pf_employee_id'"))->first();

    $current_contribution = collect(DB::select("SELECT
            company_ref_id,
            sum(employee_contribution) as total_employee_contribution,
            sum(employer_contribution) as total_employer_contribution,
            sum(interest_accrued_employee_contribution) as total_interest_employee,
            sum(interest_accrued_employer_contribution) as total_interest_employer
            FROM pfstatements
            WHERE pfstatements.registration_type = '$registrationType'
            AND pfstatements.transaction_date BETWEEN '$fromDate' AND '$toDate' AND employee_ref_id = '$employeeData->pf_employee_id'
            GROUP BY company_ref_id"))->first();

    $current_opening_refund = collect(DB::select("SELECT
            COALESCE(SUM(refund_employee_contribution),0) as total_employee_contribution,
            COALESCE(SUM(refund_employer_contribution),0) as total_employer_contribution,
            COALESCE(SUM(refund_interest_on_employee_contr),0) as refund_interest_employee,
            COALESCE(SUM(refund_interest_on_employer_contr),0) as refund_interest_employer,
            COALESCE(SUM(refund_as_on_interest_employee),0) as as_on_refund_employee_interest,
            COALESCE(SUM(refund_as_on_interest_employer),0) as as_on_refund_employer_interest,
            COALESCE(SUM(refund_total_disbursed_amount),0) as refund_disbursed_amount
            from pfstatements
            INNER JOIN refunds ON refunds.refund_ref_no = pfstatements.transaction_ref_no
            WHERE transaction_date BETWEEN '$fromDate' AND '$toDate' AND employee_ref_id = '$employeeData->pf_employee_id'
            AND refunds.registration_type = '$registrationType'"))->first();

    $current_year_cont_details = collect(DB::select("SELECT
            pfstatements.id,
            company_ref_id,
            for_the_month,
            for_the_year,
            transaction_ref_no,
            transaction_date,
            transaction_type,
            sum(employee_contribution) as total_employee_contribution,
            sum(employer_contribution) as total_employer_contribution,
            sum(interest_accrued_employee_contribution) as total_interest_employee,
            sum(interest_accrued_employer_contribution) as total_interest_employer,
            (CASE WHEN transaction_type = 'Refund' THEN
                (SELECT sum(refund_total_disbursed_amount) FROM refunds WHERE pfstatements.transaction_ref_no = refunds.refund_ref_no GROUP BY refunds.refund_ref_no)
            ELSE 0 END) AS disbursed_amount

            FROM pfstatements
            WHERE pfstatements.registration_type = '$registrationType'
            AND pfstatements.transaction_date BETWEEN '$fromDate' AND '$toDate' AND employee_ref_id = '$employeeData->pf_employee_id'

            GROUP BY pfstatements.id,company_ref_id, transaction_date, transaction_type,
                    transaction_ref_no,for_the_month,for_the_year,transaction_ref_no
                    ORDER BY pfstatements.id"));

    $employee_wise_data = \App\Models\Pfemployeeregistration::with(['empOrganization' => function ($query) {
            return $query->with('getPfMouDetails')
                ->where('effective_end_date', '=', NULL)->get()->first();
        }])
            ->where('pf_employee_id', '=', $employeeData->pf_employee_id)
            ->with('lastTransactionData')
            ->where('registration_type', '=', $registrationType)
            ->where('effective_end_date', '=', NULL)
            ->get()->first();

    $ref_interest_rate = $employee_wise_data->empOrganization->getPfMouDetails->interest_rate;
    $last_tran_date = $employee_wise_data->lastTransactionData->transaction_date;
    $no_of_days = round(((strtotime($toDate) - strtotime($last_tran_date)) / 24 / 3600), 0);

    if ($no_of_days > 0) {

            $employee_id = $employee_wise_data->pf_employee_id;
            $company_id = $employee_wise_data->pf_employee_company_id;

            $get_transaction_details = \App\Models\Pfstatement::where('employee_ref_id', '=', "$employee_id")
                ->where('registration_type', '=', $registrationType)
                ->where('company_ref_id', '=', "$company_id")
                ->whereRaw("transaction_version_no = (select max(transaction_version_no) from pfstatements where employee_ref_id = $employee_id)")
                ->get()->first();

            $totalEmployeeContribution = $get_transaction_details->total_employee_contribution;
            $totalEmployerContribution = $get_transaction_details->total_employer_contribution;

            $totalInterestOnEmployee = $get_transaction_details->total_interest_on_employee_contribution;
            $totalInterestOnEmployer = $get_transaction_details->total_interest_on_employer_contribution;

            $interest_chargeable_amount01 = $get_transaction_details->interest_chargeable_amount_1;
            $interest_chargeable_amount02 = $get_transaction_details->interest_chargeable_amount_2;

            $gross_os_balance_employee = $get_transaction_details->gross_os_balance_employee;
            $gross_os_balance_employer = $get_transaction_details->gross_os_balance_employer;

            $interestAccruedEmployee = ($interest_chargeable_amount01 * $ref_interest_rate / 100 * $no_of_days) / 365;
            $interestAccruedEmployer = ($interest_chargeable_amount02 * $ref_interest_rate / 100 * $no_of_days) / 365;

            $employeeContribution = 0;
            $employerContribution = 0;

            $as_on_int_chargeable_amt_1 = $interest_chargeable_amount01 + $employeeContribution;
            $as_on_int_chargeable_amt_2 = $interest_chargeable_amount02 + $employerContribution;
            $as_on_outstanding_01 = $gross_os_balance_employee + $interestAccruedEmployee + $employeeContribution;
            $as_on_outstanding_02 = $gross_os_balance_employer + $interestAccruedEmployer + $employerContribution;
            $as_on_int_outstanding_01 = $totalInterestOnEmployee + $interestAccruedEmployee;
            $as_on_int_outstanding_02 = $totalInterestOnEmployer + $interestAccruedEmployer;

            $as_on_data = [
                'pf_employee_id' => $employee_id,
                'pf_employee_company_id' => $company_id,
                'employee_contribution' => $employeeContribution,
                'employer_contribution' => $employerContribution,

                'total_employee_contribution' => $totalEmployeeContribution,
                'total_employer_contribution' => $totalEmployerContribution,

                'interest_accrued_employee' => $interestAccruedEmployee,
                'interest_accrued_employer' => $interestAccruedEmployer,

                'interest_chargable_amount_employee' => $as_on_int_chargeable_amt_1,
                'interest_chargable_amount_employer' => $as_on_int_chargeable_amt_2,

                'total_int_outstanding_employee' => $as_on_int_outstanding_01,
                'total_int_outstanding_employer' => $as_on_int_outstanding_02,

                'total_outstanding_bal_employee' => $as_on_outstanding_01,
                'total_outstanding_bal_employer' => $as_on_outstanding_02,
            ];

        
            } else {

                $as_on_data = NULL;
            }


    // index declaration
    $current_openingrefund_employee = 0;
    $current_openingrefund_employer = 0;
    $current_openingrefund_employee_interest = 0;
    $current_openingrefund_employer_interest = 0;
    $as_on_current_openingrefund_employee_interest = 0;
    $as_on_current_openingrefund_employer_interest = 0;
    $current_opening_refund_disbursed_amount = 0;

    $as_on_interest_accrued_employee = 0;
    $as_on_interest_accrued_employer = 0;
    $as_on_total_interest = 0;

    $opening_refund_employee = 0;
    $opening_refund_employer = 0;
    $opening_refund_int_employee = 0;
    $as_on_opening_refund_int_employee = 0;
    $opening_refund_int_employer = 0;
    $as_on_opening_refund_int_employer = 0;
    $opening_refund_disbursed_amount = 0;

    $opening_current_cont_employee = 0;
    $opening_current_cont_employer = 0;
    $opening_current_cont_employee_int = 0;
    $opening_current_cont_employer_int = 0;

    $current_openingrefund_employee = 0;
    $current_openingrefund_employer = 0;
    $current_openingrefund_employee_interest = 0;
    $current_openingrefund_employer_interest = 0;
    $as_on_current_openingrefund_employee_interest = 0;
    $as_on_current_openingrefund_employer_interest = 0;
    $current_opening_refund_disbursed_amount = 0;

    // opening contribution
    if($opening_contribution != null ||  $opening_contribution != '') {
        $opening_cont_employee = (float)$opening_contribution->total_employee_contribution;
        $opening_cont_employer = (float)$opening_contribution->total_employer_contribution;
        $opening_cont_employee_interest = (float)$opening_contribution->total_interest_employee;
        $opening_cont_employer_interest = (float)$opening_contribution->total_interest_employer;

        $opening_total_contribution_for_net_balance = (float)$opening_contribution->total_employee_contribution + (float)$opening_contribution->total_employer_contribution;
        $opening_total_interest_for_net_balance = (float)$opening_contribution->total_interest_employee + (float)$opening_contribution->total_interest_employer;
    } else {
        $opening_cont_employee = 0;
        $opening_cont_employer = 0;
        $opening_cont_employee_interest = 0;
        $opening_cont_employer_interest = 0;
        $opening_total_contribution_for_net_balance= 0;
        $opening_total_interest_for_net_balance = 0;
    }

    // opening refund
    if( $opening_refund != null || $opening_refund != '') {
    $opening_refund_employee = (float)$opening_refund->total_employee_contribution;
        $opening_refund_employer = (float)$opening_refund->total_employer_contribution;
        
        $opening_refund_int_employee = (float)$opening_refund->refund_interest_employee;
        $opening_refund_int_employer = (float)$opening_refund->refund_interest_employer;
    
        $as_on_opening_refund_int_employee = (float)$opening_refund->as_on_refund_employee_interest;    
        $as_on_opening_refund_int_employer = (float)$opening_refund->as_on_refund_employer_interest;
        
        $opening_refund_disbursed_amount = (float)$opening_refund->refund_disbursed_amount;
        $opening_refund_for_net_balance = (float)$opening_refund->refund_disbursed_amount;
    } else {
        $opening_refund_employee = 0;
        $opening_refund_employer = 0;
        $opening_refund_int_employee = 0;
        $as_on_opening_refund_int_employee = 0;
        $opening_refund_int_employer = 0;
        $as_on_opening_refund_int_employer = 0;
        $opening_refund_disbursed_amount = 0;
        $opening_refund_for_net_balance = 0;
    }

    // current contribution
    if($current_contribution != null && $current_contribution != '') {
        $opening_current_cont_employee = (float)$current_contribution->total_employee_contribution;
        $opening_current_cont_employer = (float)$current_contribution->total_employer_contribution;

        $opening_current_cont_employee_int = (float)$current_contribution->total_interest_employee;
        $opening_current_cont_employer_int = (float)$current_contribution->total_interest_employer;

        $current_total_contribution_for_net_balance = (float)$current_contribution->total_employee_contribution + (float)$current_contribution->total_employer_contribution;
        $current_total_interest_for_net_balance = (float)$current_contribution->total_interest_employee + (float)$current_contribution->total_interest_employer;
    } else {
        $opening_current_cont_employee = 0;
        $opening_current_cont_employer = 0;
        $opening_current_cont_employee_int = 0;
        $opening_current_cont_employer_int = 0;   

        $current_total_contribution_for_net_balance = 0;
        $current_total_interest_for_net_balance = 0;
    }

    // current opening refund
    if($current_opening_refund != NULL && $current_opening_refund != '') {
        $current_openingrefund_employee = $current_opening_refund->total_employee_contribution;
        $current_openingrefund_employer = $current_opening_refund->total_employer_contribution;
        $current_openingrefund_employee_interest = $current_opening_refund->refund_interest_employee;
        $current_openingrefund_employer_interest = $current_opening_refund->refund_interest_employer;
        $as_on_current_openingrefund_employee_interest = $current_opening_refund->as_on_refund_employee_interest;
        $as_on_current_openingrefund_employer_interest = $current_opening_refund->as_on_refund_employer_interest;
        $current_opening_refund_disbursed_amount = (float)$current_opening_refund->refund_disbursed_amount;
        $current_total_refund_for_net_balance  = (float)$current_opening_refund->refund_disbursed_amount;
    } else {
        $current_openingrefund_employee = 0;
        $current_openingrefund_employer = 0;
        $current_openingrefund_employee_interest = 0;
        $current_openingrefund_employer_interest = 0;
        $as_on_current_openingrefund_employee_interest = 0;
        $as_on_current_openingrefund_employer_interest = 0;
        $current_opening_refund_disbursed_amount = 0;
        $current_total_refund_for_net_balance = 0;
    }

    // as on interest acrrued emp
    if($as_on_data != '' || $as_on_data != NULL) {
        $as_on_interest_accrued_employee = $interestAccruedEmployee;
        $as_on_interest_accrued_employer = $interestAccruedEmployer;
        $as_on_total_interest = $as_on_interest_accrued_employee + $as_on_interest_accrued_employer;
    } else {
            $as_on_interest_accrued_employee = 0;
            $as_on_interest_accrued_employer = 0;
            $as_on_total_interest = 0;
            }

?>

<div>
    {{--table top header info--}}
    <table class="table table-bordered">
        <thead>
        <tr>
            <th colspan="2" style="border: none;"><h2>{{ $registrationType }} Company Wise Employee Statement</h2></th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Organization Name : &nbsp; {{ $organization_name }}</th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Name & CID :
                &nbsp; {{ $employee_wise_data->employee_name }} &nbsp; ,
                &nbsp; {{ $employee_wise_data->identification_no }}</th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">A/c No & A/c Status :
                &nbsp; {{ $employee_wise_data->employee_id_no }} &nbsp; , &nbsp; [ {{ $employee_wise_data->status }} ]
            </th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Date of Registration :
                &nbsp; {{ $employee_wise_data->registration_date }}</th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Start & End Date : &nbsp; {{ $fromDate }} &nbsp; ,
                &nbsp; [ To: {{ $toDate }}]
            </th>
        </tr>
        <tr>
            <th width="100%" align="left" style="border: none;">Processing Date:
                &nbsp; {{ \Carbon\Carbon::now()->format('d-m-Y') }}</th>
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


    <!-- Summary Report -->
    <table class="table-bordered">
        <thead>
        <tr>
            <th colspan="6" align="left" style="border:none;">SUMMARY REPORT</th>
        </tr>
        <tr>
            <td><strong>Particulars</strong></td>
            <td><strong>Employee Contribution</strong></td>
            <td><strong>Employer Contribution</strong></td>
            <td><strong>Interest (Employee)</strong></td>
            <td><strong>Interest (Employer)</strong></td>
            <td><strong>Total</strong></td>
        </tr>
        </thead>
        <tbody>

        <!-- Opening balance contribution start -->
        <tr>
            <td>Opening Contribution</td>
            <td>{{ number_format($opening_cont_employee,2) }}</td>
            <td>{{ number_format($opening_cont_employer,2) }}</td>
            <td>{{ number_format($opening_cont_employee_interest,2) }}</td>
            <td>{{ number_format($opening_cont_employer_interest,2) }}</td>
            <?php
            $opening_total = $opening_cont_employee + $opening_cont_employer +
                            $opening_cont_employee_interest + $opening_cont_employer_interest;
            ?>
            <td>{{ number_format($opening_total,2) }}</td>
        </tr>

        <!-- Opening balance Refund start -->
        <tr>
            <td>Opening Refund</td>
            <td>{{ number_format($opening_refund_employee,2) }}</td>
            <td>{{ number_format($opening_refund_employer,2) }}</td>
            <td>{{ number_format(($opening_refund_int_employee + $as_on_opening_refund_int_employee),2) }}</td>
            <td>{{ number_format(($opening_refund_int_employer + $as_on_opening_refund_int_employer),2) }}</td>
            <td>{{ number_format($opening_refund_disbursed_amount,2) }}</td>
        </tr>
        
        <!-- Current Contribution -->
        <tr>
            <td>Current Contribution</td>
            <td>{{ number_format($opening_current_cont_employee,2) }}</td>
            <td>{{ number_format($opening_current_cont_employer,2) }}</td>
            <td>{{ number_format($opening_current_cont_employee_int,2) }}</td>
            <td>{{ number_format($opening_current_cont_employer_int,2) }}</td>
            <td>{{ number_format(($opening_current_cont_employee + $opening_current_cont_employer +
                    $opening_current_cont_employee_int + $opening_current_cont_employer_int),2) }}</td>
        </tr>

        <!-- Current Refund -->
        <tr>
            <td>Current Refund</td>
            <td>{{ number_format($current_openingrefund_employee,2) }}</td>
            <td>{{ number_format($current_openingrefund_employer,2) }}</td>
            <td>{{ number_format(($current_openingrefund_employee_interest + $as_on_current_openingrefund_employee_interest),2) }}</td>
            <td>{{ number_format(($current_openingrefund_employer_interest + $as_on_current_openingrefund_employer_interest),2) }}</td>
            <td>{{ number_format($current_opening_refund_disbursed_amount,2) }}</td>
        </tr>

        <!-- as on date interest opening -->
        <tr>
            <td>As on Interest</td>
            <td>0.00</td>
            <td>0.00</td>
            <td>{{ number_format($as_on_interest_accrued_employee,2) }}</td>
            <td>{{ number_format($as_on_interest_accrued_employer,2) }}</td>
            <td>{{ number_format($as_on_total_interest,2) }}</td>
        </tr>

        {{--Net Opening Balance--}}
        <tr>
            <td><strong>Net Balance</strong></td>
            <td><strong>{{ number_format(($opening_cont_employee + $opening_current_cont_employee),2) }}</strong></td>
            <td><strong>{{ number_format(($opening_cont_employer + $opening_current_cont_employer),2) }}</strong></td>
            <td><strong>{{ number_format(($opening_cont_employee_interest + $opening_current_cont_employee_int + $as_on_interest_accrued_employee),2) }}</strong></td>
            <td><strong>{{ number_format(($opening_cont_employer_interest + $opening_current_cont_employer_int + $as_on_interest_accrued_employer),2) }}</strong></td>
            <?php           

            $netPayableBalance = (($opening_total - $opening_refund_disbursed_amount) + (($opening_current_cont_employee + $opening_current_cont_employer +
                    $opening_current_cont_employee_int + $opening_current_cont_employer_int) - $current_opening_refund_disbursed_amount) + $as_on_total_interest);
            ?>
            <td><strong>{{number_format($netPayableBalance,2) }}</strong></td>
        </tr>

        <tr><td colspan="6" style="border:none;">&nbsp;</td></tr>
        <tr><td colspan="6" style="border:none;">&nbsp;</td></tr>

        </tbody>
    </table>
    <!-- Summary Report End -->

    <?php
    $total_employee_balance = 0;
    $total_employer_balance = 0;
    $total_employee_interest_balance = 0;
    $total_employer_interest_balance = 0;
    $total_disbursed_balance_amount = 0;
    $total_contribution_emp_emplr = 0;
    ?>

    <!-- Detail statement Table -->
    <table class="table-bordered">
        <thead>
        <tr>
            <th colspan="11" align="left" style="border:none;">DETAIL STATEMENT REPORT</th>
        </tr>
        <tr>
            <td rowspan="2"><strong>Sl.No</strong></td>
            <td rowspan="2"><strong>Due Month</strong></td>
            <td rowspan="2"><strong>Reference No</strong></td>
            <td rowspan="2"><strong>Trns. Type</strong></td>
            <td rowspan="2"><strong>Receipt Date</strong></td>
            <td rowspan="1" colspan="2"><strong>Contribution Details</strong></td>
            <td rowspan="1" colspan="2"><strong>Interest Charged</strong></td>
            <td rowspan="2"><strong>Disbursed Amt</strong></td>
            <td rowspan="2"><strong>Total</strong></td>
        </tr>
        <tr>
            <td><strong>Employee Cont.</strong></td>
            <td><strong>Employer Cont.</strong></td>
            <td><strong>Int. on Employee Cont.</strong></td>
            <td><strong>Int. on Employer Cont.</strong></td>
        </tr>
        </thead>
        <tbody>

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
                $total = $data->total_employee_contribution + $data->total_employer_contribution + $data->total_interest_employee + $data->total_interest_employer + $data->disbursed_amount;
                $total_employee_balance = $data->total_employee_contribution + $total_employee_balance;
                $total_employer_balance = $data->total_employer_contribution + $total_employer_balance;
                $total_employee_interest_balance = $data->total_interest_employee + $total_employee_interest_balance;
                $total_employer_interest_balance = $data->total_interest_employer + $total_employer_interest_balance;
                $total_disbursed_balance_amount = $data->disbursed_amount + $total_disbursed_balance_amount;
                $total_contribution_emp_emplr = $total_employee_balance + $total_employer_balance +
                    $total_employee_interest_balance + $total_employer_interest_balance +
                     $opening_total-$total_disbursed_balance_amount+$as_on_total_interest;
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
            <td class="text-center"><strong>{{ number_format($total_contribution_emp_emplr,2) }}</strong></td>
        </tr>

        Total
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>

        <tr>
            <th colspan="11" align="left" style="border:none; font-size: small">NET PAYABLE AS ON DATE : {{ $to_Date }}, &nbsp;
                Nu. {{ number_format(($opening_total_contribution_for_net_balance + $opening_total_interest_for_net_balance - $opening_refund_for_net_balance + $current_total_contribution_for_net_balance + $current_total_interest_for_net_balance -  $current_total_refund_for_net_balance + $as_on_total_interest),2) }}/-
            </th>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="11" style="border:none;">&nbsp;</td>
        </tr>
        </tbody>
    </table>
    <!-- Table End Section -->

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

@endforeach
</body>
<style type="text/css">
    @page { margin: 100px 50px; }
    #header { position: fixed; left: 0px; top: -80px; right: 0px; height: 100px; }
    #footer { position: fixed; left: 0px; bottom: -90px; right: 0px; height: 50px; font-size: 11px; color: blue; text-align: center;}

    .header{
        width: 100%;
    }
    .footer{
        margin-top: 0; margin-bottom: 0;
    }
    .logo_and_bar_code{ text-align: center;}
    .img-header{ display: inline-block;margin-left: auto;margin-right: auto;}
    .main-heading > h4{

        font-size: 14px;
        text-align: center;
        margin-top: 0; margin-bottom: 2px;
    }
    table { page-break-before:avoid; page-break-after:avoid; width:100%; max-width:100%;  border-spacing:0; border-collapse:collapse;}
    table tbody tr { page-break-before: auto; page-break-after: auto;}
    table tbody tr td, table tbody tr th { page-break-inside: avoid;}
    .table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th {border: 1px solid #000;}
    tr    { page-break-inside:avoid; page-break-before:avoid; page-break-after:avoid; vertical-align: top; border-color: inherit;}

    table thead tr th {font-size:12px;}
    table thead tr td {font-size:11px;}

    table tbody tr th {font-size:12px;}
    table tbody tr td {font-size:11px;}

    table tfoot tr th {font-size:12px;}
    table tfoot tr td {font-size:11px;}

    table tbody tr th p {text-align: justify; }
    table tbody tr td p {text-align: justify; }

    .ForceBreak{
        page-break-after: always;
    }

    .noBreak {

        page-break-inside: avoid;

        page-break-before: avoid;

        position: absolute;
    }
</style>
</html>
