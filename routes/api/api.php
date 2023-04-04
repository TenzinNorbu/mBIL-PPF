<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AccountGroupRegistration\AccountgroupController;
use App\Http\Controllers\AccountPosting\AccountPostingController;
use App\Http\Controllers\AccountType\AccounttypeController;
use App\Http\Controllers\Banks\BankController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchesForUserController\BranchForUsersController;
use App\Http\Controllers\Closings\MonthlyClosingController;
use App\Http\Controllers\Closings\YearClosingController;
use App\Http\Controllers\Closings\GfYearClosingController;
use App\Http\Controllers\CompanyRegistration\CompanyRegistrationController;
use App\Http\Controllers\ContactPersonRegistration\ContactpersonController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentForUserController\DeptForUsersController;
use App\Http\Controllers\GF\GF_Refund\GfRefundApproveController;
use App\Http\Controllers\GF\GF_Refund\GfRefundDataListController;
use App\Http\Controllers\GF\GF_Refund\GfRefundProcessController;
use App\Http\Controllers\GF\GF_Reports\GfAccountLedgerStatementController;
use App\Http\Controllers\GF\GF_Reports\GfCompanyWiseStatementReportController;
use App\Http\Controllers\GF\GF_Reports\GfDailyCollectionReportController;
use App\Http\Controllers\GF\GF_Reports\GfEmployeeWiseStatementReportController;
use App\Http\Controllers\GF\GF_Reports\GfMonthlyDepositReportController;
use App\Http\Controllers\GF\GF_Reports\GfTrialBalanceStatementController;
use App\Http\Controllers\GF\GfcollectionController;
use App\Http\Controllers\GF\GfCompanyRegistrationController;
use App\Http\Controllers\GF\GfContactpersonController;
use App\Http\Controllers\GF\GfemployeeregistrationController;
use App\Http\Controllers\GF\GfIndividualdepositController;
use App\Http\Controllers\GF\GfIntroducerController;
use App\Http\Controllers\GF\GfMonthlyClosingController;
use App\Http\Controllers\GF\GfNomineeController;
use App\Http\Controllers\GF\GfProprietordetailsController;
use App\Http\Controllers\IntroducerRegistration\IntroducerController;
use App\Http\Controllers\MastersAll\GenderController;
use App\Http\Controllers\MastersAll\OrgtypeController;
use App\Http\Controllers\MastersAll\DzongkhagController;
use App\Http\Controllers\MastersAll\IdentificationTypeController;
use App\Http\Controllers\MastersAll\MaritalStatusController;
use App\Http\Controllers\MastersAll\MonthController;
use App\Http\Controllers\MastersAll\NationalityController;
use App\Http\Controllers\MastersAll\SectorTypeController;
use App\Http\Controllers\NomineeDetails\NomineeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Pfcollection\IndividualdepositController;
use App\Http\Controllers\Pfcollection\PfcollectionController;
use App\Http\Controllers\PfEmployeeRegistration\PfemployeeregistrationController;
use App\Http\Controllers\PfEmployeeRegistration\EmployeeTransferController;
use App\Http\Controllers\ProprietorDetails\ProprietordetailsController;
use App\Http\Controllers\Refunds\RefundApproveController;
use App\Http\Controllers\Refunds\RefundDataListController;
use App\Http\Controllers\Refunds\RefundPaymentController;
use App\Http\Controllers\Refunds\ExcessPaymentController;
use App\Http\Controllers\Refunds\RefundProcessController;
use App\Http\Controllers\Reports\AccountLedgerStatementController;
use App\Http\Controllers\Reports\CompanyWiseFundBalanceReportController;
use App\Http\Controllers\Reports\CompanyWiseStatementReportController;
use App\Http\Controllers\Reports\DailyCollectionReportController;
use App\Http\Controllers\Reports\EmployeeWiseStatementReportController;
use App\Http\Controllers\Reports\GetReportsController;
use App\Http\Controllers\Reports\MonthlyDepositReportController;
use App\Http\Controllers\Reports\MonthlyRefundReportController;
use App\Http\Controllers\Reports\RenewalListReportController;
use App\Http\Controllers\Reports\TrialBalanceStatementController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StakeholderRegistration\StakeholderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRegistration\UserRegistrationController;
use App\Http\Controllers\Reports\MonthlyLaborReportController;
use App\Http\Controllers\Reports\ClosingReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Refunds\RefundController;
use App\Http\Controllers\Refunds\ExcessRefundPaymentController;
use App\Http\Controllers\PfEmployeeRegistration\EmployeeTransferControlle;
use App\Http\Controllers\Pfcollection\ReverseCollectionController;
use App\Http\Controllers\Reports\BrmDataReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PartyTypeController;
use App\Http\Controllers\Api\mBILCompanyDetails;
use App\Http\Controllers\Api\CollectionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::middleware('auth:http://172.16.16.193:8086')->get('/', function (Request $request) {
//     return response()->json(['message' => 'Page Not Found or Endpoint resource might be Wrong.'], 404);
// });

Route::middleware('api.token')->group(function(){
    Route::get('company-details/{licenseNo}/{type}', [mBILCompanyDetails::class,'getCompanyDetails']);
});
Route::middleware('api.token')->group(function(){
    Route::post('create_mBILCollection', [CollectionController::class,'createCollection']);
});
Route::middleware('api.token')->group(function(){
    Route::get('getCollectionBankAccount', [CollectionController::class,'getCollectionAccoutByBranchId']);
});

//** login API */
Route::post('login', [UserRegistrationController::class, 'login']);

//** Outside Register API  */
Route::post('/register', [UserRegistrationController::class, 'register']);
Route::get('/department', [DepartmentController::class,'index']);
Route::get('/branche', [BranchController::class,'index']);

//** Forgot Password */
Route::post('forgot-password-mail', [UserRegistrationController::class, 'forgotPassword']);
Route::post('verify-otp', [UserRegistrationController::class, 'verifyOTP']);
Route::post('update-forgot-password', [UserRegistrationController::class, 'UpdateResetPassword']);

//** Home */
Route::get('/home', [HomeController::class, 'index']);

//** User-permission */
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('/logout', [UserRegistrationController::class, 'logout']);
    Route::resource('/users', UserController::class);
    Route::resource('/roles', RoleController::class);
    Route::resource('/permissions', PermissionController::class);
    Route::post('change-password', [UserController::class, 'changeUserPassword']);
});

//** Master Setup */
Route::group(['middleware' => ['auth:api']], function () {
    Route::resource('/departments', DepartmentController::class);
    Route::resource('/branches', BranchController::class);
    Route::resource('/stakeholders', StakeholderController::class);
    Route::resource('/accountgroups', AccountgroupController::class);
    Route::resource('/accounttypes', AccounttypeController::class);
    Route::resource('/banks', BankController::class);
    Route::resource('/orgtypes', OrgtypeController::class);
});
//** Gratuity Fund Endpoints */

//** GF Companies Registrations & GF Collections */
Route::group(['middleware' => ['auth:api']], function () {
    Route::resource('/gf-companies', GfCompanyRegistrationController::class);
    Route::resource('/gf-proprietors', GfProprietordetailsController::class);
    Route::resource('/gf-contactpersons', GfContactpersonController::class);
    Route::resource('/gf-introducers', GfIntroducerController::class);
    Route::resource('/gf-nominees', GfNomineeController::class);
    Route::resource('/gf-employees', GfemployeeregistrationController::class);
    Route::resource('/gf-collections', GfcollectionController::class);
});

//** PF Companies Registrations & PF Collections */
Route::group(['middleware' => ['auth:api']], function () {
    Route::resource('/pfemployees', PfemployeeregistrationController::class);
    Route::resource('/pfcompanies', CompanyRegistrationController::class);
    Route::resource('/proprietors', ProprietordetailsController::class);
    Route::resource('/contactpersons', ContactpersonController::class);
    Route::resource('/introducers', IntroducerController::class);
    Route::resource('/nominees', NomineeController::class);
    Route::resource('/pfcollections', PfcollectionController::class);
});

Route::group(['middleware' => 'auth:api'], function () {
    //** Get Role List by User ID */
    Route::get('listrolebyuserid/{id}', [RoleController::class, 'getrolelistbyuserid']);

    //** Get Permission List by User ID */
    Route::get('listpermissionbyuserid/{id}', [PermissionController::class, 'getpermissionlistbyuserid']);

    //** Get Permission List by User ID */
    Route::get('get-permission-list-by-user-id/{id}', [UserController::class, 'getPermissionByUserId']);

    //** Get Permission List By Role ID */
    Route::get('listpermissionbyroleid/{id}', [PermissionController::class, 'getpermissionbyroleid']);

    //** Get Proprietor List by Company ID */
    Route::get('getproprietorbycompanyid/{company_id}', [ProprietordetailsController::class, 'getproprietorbycompanyid']);

    //** Get Contact Person By Company ID */
    Route::get('getcontactpersoncompanyid/{company_id}', [ContactpersonController::class, 'getcontactpersoncompanyid']);

    //** Get Introducer By Company ID */
    Route::get('getintroducerbycompanyid/{company_id}', [IntroducerController::class, 'getintroducerbycompanyid']);

    //** Get Employee By Employee ID */
    Route::get('get-employee-id/{employee_id}', [PfemployeeregistrationController::class, 'getEmployeeByEmployeeId']);

    //** Get Employee List by Company ID */
    Route::get('get-employee-list-by-company-id/{company_id}', [PfemployeeregistrationController::class, 'getEmployeeListByCompanyId']);

    //** Get Employee List by Search Filter */
    Route::get('get-employee-list-by-search-filter/{employee_name}', [PfemployeeregistrationController::class, 'getEmployeeListByFilterSearch']);

    //** Get Nominee Details by Employee ID */
    Route::get('get-nominee-id/{employee_id}', [NomineeController::class, 'getNomineeDetailsByEmployeeId']);

    //** Get PF Collection Account by Branch ID */
    Route::get('getCollectionAccoutByBranchId/{branchId}', [PfcollectionController::class, 'getCollectionAccoutByBranchId']);

    //** Get Due Amounts by Company ID  */
    Route::get('getDueAmountByCompanyId/{companyId}', [PfcollectionController::class, 'getDueAmountByCompanyId']);

    //** Get Collection Receipt No */
    Route::get('get-collection-receipt-by-user-branch', [PfcollectionController::class, 'getCollectionReceiptByUserBranch']);

    //** Get Pending Deposit Lists */
    Route::get('list-pending-deposit-list', [IndividualdepositController::class, 'listPendingDeposits']);

    //** Get Approve Deposit Lists */
    Route::get('list-approved-deposits-list', [IndividualdepositController::class, 'listApproveDeposits']);

    //** Get Collection data list for collection dashboard */
    Route::get('getCollectionReceiptNo/{collectionId}', [PfcollectionController::class, 'getCollectionReceiptNo']);
    Route::get('view-approved-deposits/{collectionId}', [PfcollectionController::class, 'viewApprovedDeposits']);

    //** Get Active Pf Company List */
    Route::get('getactivepfcompanylist', [RefundDataListController::class, 'getActiveCompanyByCompanyName']);

    //** Get Active Employee List by Company ID  */
    Route::get('getactivepfemployeelist/{companyid}', [RefundDataListController::class, 'getActiveEmployeeListByCompanyId']);

    //** Get Refunds by Employee ID */
    Route::get('get-employee-data-by-pf-emp-id/{pf_emp_id}', [RefundDataListController::class, 'getEmployeeDataByEmpCodeId']);

    //** Get Refunds by Employee ID */
    Route::get('getrefundbyemployeeid/{employeeid}', [RefundDataListController::class, 'getRefundDetailsByEmployeeId']);

    //** Get Refunds by Employee ID */
    Route::get('get-refund-by-employee-cid-no/{employee_cid}', [RefundDataListController::class, 'getRefundByEmployeeCidNo']);

    //** Get Refunds by Employee ID NEW Resolved */
    Route::get('get-refund-by-employee-id-no/{employeeid}', [RefundDataListController::class, 'getRefundByEmployeeIdNo']);

    //** Get GF Refunds by Employee ID NEW Resolved */
    Route::get('get-gf-refund-by-employee-id-no/{employeeid}', [GfRefundDataListController::class, 'getRefundByEmployeeIdNo']);

    //** Refund Processed List */
    Route::get('refund-processed-list', [RefundDataListController::class, 'getRefundProcessDataList']);

    //** Refund Approval list */
    Route::get('/refund-approve-list', [RefundDataListController::class, 'getRefundApprovedDataList']);

    //** Refund Completed List */
    Route::get('refund-completed-list', [RefundDataListController::class, 'getRefundCompletedDataList']);

    //** Refund Data List by Refund Reference Number */
    Route::get('refund-data-list/{refund_ref_no}', [RefundDataListController::class, 'getRefundDataByRefundRefNo']);

    //** Refund Approved DataList by Refund Reference No with Approval Note */
    Route::get('refund-approval-list/{refund_ref_no}', [RefundDataListController::class, 'getApprovedRefundList']);

    //** Verify Refund by Refund Reference No */
    Route::get('verify-refund/{refund_ref_no}', [RefundDataListController::class, 'verifyRefund']);

    //** Refund Process End Point */
    Route::post('refunds', [RefundProcessController::class, 'refundProcess']);

    //** Refund Approve Endpoint */
    Route::post('refund-approve', [RefundApproveController::class, 'SaveRefundApprovalData']);

    //** Refund Payment Endpoint */
    Route::post('refund-payment', [RefundPaymentController::class, 'SaveRefundPayment']);

    //** Excess Payment Endpoint */
    Route::post('excess-payment', [ExcessPaymentController::class, 'saveExccessPayment']);

    //** Refund Payment Pending List Endpoint */
    Route::get('refund-payment-pending-list', [RefundDataListController::class, 'refundPaymentPendingLists']);

    //** Refund Payment Completed List Endpoint */
    Route::get('refund-payment-completed-list', [RefundDataListController::class, 'refundPaymentCompletedLists']);

    Route::get('get-payment-document/{payment_advise_no}', [RefundDataListController::class, 'getPaymentVoucherByPaymentAdviseNo']);

    //** Refund Payment Completed List Endpoint */
    Route::get('refund-payment-Varified-list', [RefundDataListController::class, 'getRefundPendingVerifiedDataList']);

    //** Get Refund PaymentDetails List by PaymentRefNo */
    Route::get('refund-paymentdetail-lists/{paymentrefno}', [RefundDataListController::class, 'listsRefundPaymentDetails']);

    //** Get Refund PaymentDetails Completed List by PaymentRefNo */
    Route::get('refund-paymentdetail-completed-lists/{refund_ref_no}', [RefundDataListController::class, 'listsRefundPaymentCompletedDetails']);

    //** Get Refund Uploaded File */
    Route::get('download-refund-file/{docpath}', [RefundDataListController::class, 'getRefundProcessUploadedfile']);

    //** Get Collection File */
    Route::get('download-collection-file/{docpath}', [PfcollectionController::class, 'getCollectionDocument']);

    //** Monthly Closing */
    Route::post('monthly-closing', [MonthlyClosingController::class, 'MonthlyClosing']);

    //** Yearly Closing */
    Route::post('pf-year-closing', [YearClosingController::class, 'yearlyClosing']);

    //** GF Yearly Closing */
    Route::post('gf-year-closing', [GfYearClosingController::class, 'yearClosing']);
});

//** Generate Reports */
Route::group(['middleware' => 'auth:api'], function () {

    //** Update Individual Employee During Deposit */
    Route::post('update-individual-employee-deposit', [IndividualdepositController::class, 'updateEmployeeWiseDeposit']);

    //** Generate Daily Collection Report */
    Route::post('generate-daily-collection-report', [DailyCollectionReportController::class, 'GenerateDailyCollectionReport']);

    //** Employee Wise Statement Report */
    Route::post('employee-wise-statement', [EmployeeWiseStatementReportController::class, 'EmployeeWiseStatementReport']);

    //** Company Wise Statement */
    Route::post('company-wise-statement', [CompanyWiseStatementReportController::class, 'CompanyWiseStatementReport']);

    //** Generate Monthly Deposit Statement */
    Route::post('monthly-deposit-statement', [MonthlyDepositReportController::class, 'GenerateMonthlyDepositReport']);

    //** Account Sub-Ledger Statement */
    Route::post('account-ledger-statement', [AccountLedgerStatementController::class, 'GenerateAccSubLedgerStatement']);

    //** Trial Balance Statement */
    Route::post('trial-balance-statement', [TrialBalanceStatementController::class, 'TrialBalanceReport']);

    //** Get All Reports */
    Route::post('get-all-statements', [GetReportsController::class, 'GetAllReports']);

    //** Download Statement */
    Route::get('download-statement/{docname}', [GetReportsController::class, 'downloadStatement']);

    //* BRM DATA Report
    Route::post('brm-data-report', [BrmDataReportController::class, 'BrmDataReport']);

    //* get resources file
    Route::get('get-resource-file/{file_name}', [BankController::class, 'getResourceFiles']);

    //* Monthly Labour Report
    Route::post('monthly-labor-report', [MonthlyLaborReportController::class, 'MonthlyLaborReport']);

    //* Closing Report Controller
    Route::post('closing-report', [ClosingReportController::class, 'ClosingReport']);
});

Route::group(['middleware' => 'auth:api'], function () {

    //** Introducer Lists */
        Route::get('select-introducer', [PartyTypeController::class, 'PartyType']);
        //** Save Individual Deposit */
        Route::post('/saveindividualdeposit', [IndividualdepositController::class, 'saveIndividualDeposit']);
        Route::get('/departmentsList', [DeptForUsersController::class, 'index']);
        Route::get('/branchesList', [BranchForUsersController::class, 'index']);

        //** Genders */
        Route::get('/genders', [GenderController::class, 'getGenderList']);

        //** Fetch Identification Types */
        Route::get('/identificationtypes', [IdentificationTypeController::class, 'IdentificationType']);

        //** Maritial Status  */
        Route::get('/maritalstatus', [MaritalStatusController::class, 'Status']);

        //** Nationalities */
        Route::get('/nationalities', [NationalityController::class, 'Nationality']);

        //** Party Types */
        Route::get('/partytypelists', [PartyTypeController::class, 'PartyTypeList']);

        //** Employee List */
        Route::get('/employeelist', [StakeholderController::class, 'index']);

        //** Dzongkhag List */
        Route::get('/dzongkhaglist', [DzongkhagController::class, 'DzongkhagList']);

        //** Months List */
        Route::get('/months', [MonthController::class, 'Month']);
});


Route::group(['middleware' => 'auth:api'], function () {
    //** Get Proprietor List by Company ID */
    Route::get('get-gf-proprietor-by-companyid/{company_id}', [GfProprietordetailsController::class, 'getproprietorbycompanyid']);

    //** Get Contact Person By Company ID */
    Route::get('get-gf-contactperson-companyid/{company_id}', [GfContactpersonController::class, 'getcontactpersoncompanyid']);

    //** Get Introducer By Company ID */
    Route::get('get-gf-introducer-by-companyid/{company_id}', [GfIntroducerController::class, 'getintroducerbycompanyid']);

    //** Get Employee By Employee ID */
    Route::get('get-gf-employee-id/{employee_id}', [GfemployeeregistrationController::class, 'getEmployeeByEmployeeId']);

    //** Download GF Collection File */
    Route::get('download-gf-collection-file/{docpath}', [GfcollectionController::class, 'getCollectionDocument']);

    //** Get PF & GF Due Amounts by Company ID */
    Route::get('getGfDueAmountByCompanyId/{companyId}', [GfcollectionController::class, 'getDueAmountByCompanyId']);

    //** Get GF Collection Account by Branch ID */
    Route::get('getGfCollectionAccoutByBranchId/{branchId}', [GfcollectionController::class, 'getCollectionAccoutByBranchId']);

    //** Get Collection Receipt No */
    Route::get('get-gf-collection-receipt-by-user-branch', [GfcollectionController::class, 'getCollectionReceiptByUserBranch']);

    //** Get Collection data list for collection dashboard */
    Route::get('getGfCollectionReceiptNo/{collectionId}', [GfcollectionController::class, 'getCollectionReceiptNo']);

    //** Individual GF Deposit Starts */

    //** Get Approve Deposit Lists */
    Route::get('list-gf-approved-deposits-list', [GfIndividualdepositController::class, 'listApproveDeposits']);

    //** Save GF Individual Deposit */
    Route::post('/saveindividualgfdeposit', [GfIndividualdepositController::class, 'saveIndividualDeposit']);

    //** GF Monthly Closing */
    Route::post('gf-monthly-closing', [GfMonthlyClosingController::class, 'MonthlyClosing']);

    //** Refund Process */
    Route::post('gf-refund-process', [GfRefundProcessController::class, 'refundProcess']);

    /** Get Active GF Company List */
    Route::get('getactive-gf-companylist', [GfRefundDataListController::class, 'getActiveCompanyByCompanyName']);

    /** Verify Refund by Refund Reference No */
    Route::get('verify-gf-refund/{refund_ref_no}', [GfRefundDataListController::class, 'verifyRefund']);

    /** Refund GF Approve Endpoint */
    Route::post('refund-gf-approve', [GfRefundApproveController::class, 'SaveRefundApprovalData']);

    //** get-gf-refund-by-employee-cid-no */
    Route::get('get-gf-refund-by-employee-cid-no/{employee_cid}', [GfRefundDataListController::class, 'getRefundByEmployeeCidNo']);

    //** Dashboard Endpoint */ PF Company List */
    Route::get('/pf-company-count',[DashboardController::class,'PFCompanyCount']);

    //** GF Company List */
    Route::get('/gf-company-count',[DashboardController::class,'GFCompanyCount']);

    //** PF Individual list */
    Route::get('/pf-individual-count',[DashboardController::class,'PFIndividualCount']);

    //** GF Individual list */
    Route::get('/gf-individual-count',[DashboardController::class,'GFIndividualCount']);

    //** Total Collections Amount */
    Route::get('/total-collection-amount',[DashboardController::class,'TotalCollectionAmmount']);

    //** Bar Graph Data */
    Route::get('/monthly-pf-contributions',[DashboardController::class,'MonthlyPFContributions']);

    Route::get('/monthly-gf-contributions',[DashboardController::class,'MonthlyGFContributions']);

    //Password change Notification
    Route::get('/password-notify/{user_id}',[DashboardController::class,'Days_40']);

    //** MONTHLY CONTRIBUTIONS FOR PF REFUND */
    Route::get('monthly-pf-refund-cont', [RefundController::class, 'MonthlyPfRefundContributions']);
    //** MONTHLY CONTRIBUTIONS FOR GF REFUND */
    Route::get('monthly-gf-refund-cont', [RefundController::class, 'MonthlyGfRefundContributions']);

    //** Total Refunds Amount */
    Route::get('/total-refund-amount',[RefundController::class,'RefundTotalAmount']);

    //** Account Posting Endpoint */
    Route::get('get-account-type-name/{group_id}', [AccounttypeController::class, 'getAccountTypeName']);
    Route::post('get-account-posting-search', [AccounttypeController::class, 'getAccountPostingSearchItems']);
    Route::post('get-account-posting-refno', [AccounttypeController::class, 'getAccountPostingReferenceNumber']);
    Route::post('save-account-posting', [AccountPostingController::class, 'saveAccountPostingTransactions']);

    //** Newly Added Reports */
    Route::post('mou-renewal-list-report', [RenewalListReportController::class, 'RenewalListReport']);
    Route::post('company-wise-fundbalance-report', [CompanyWiseFundBalanceReportController::class, 'CompanyWiseFundBalanceReport']);
    Route::post('monthly-refund-report', [MonthlyRefundReportController::class, 'MonthlyRefundReport']);

    //** Excess Refund Payment */
    Route::get('list-pending-collections/{company_id}', [ExcessRefundPaymentController::class, 'collectionNos']);
    Route::get('pending-collections-data/{collection_no}', [ExcessRefundPaymentController::class, 'getPendingCollections']);
    Route::post('create-excess-refund-payment', [ExcessRefundPaymentController::class, 'createExcessRefundPayment']);

    //** Sector Type List */
    Route::get('sector-type-list', [SectorTypeController::class, 'SectorType']);

    //** GET Department by Branch ID */
    Route::get('department-by-branch-id/{branch_id}', [DepartmentController::class, 'DeptBybranchId']);

    //** Employee Transfer */
    Route::post('employee-transfer', [EmployeeTransferController::class, 'employeeTransfer']);

    //** Reverse Collections */
    Route::post('reverse-collections', [ReverseCollectionController::class, 'ReverseCollection']);
    Route::get('reverse-collection-data/{collection_id}', [ReverseCollectionController::class, 'reverseCollectionModeData']);
});

/**Api endpoint error handler */
Route::fallback(function () {
    abort(404);
});