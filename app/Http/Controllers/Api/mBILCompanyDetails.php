<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companyregistration;
use App\Models\pfmoudetails;
use ESolution\DBEncryption\Encrypter;
use Exception;

class mBILCompanyDetails extends Controller
{
    public function getCompanyDetails($licenseNo,$type){
       try{
       	$companyDetails = Companyregistration::join('pfmoudetails', 'pfmoudetails.pfmou_company_id', '=', 'companyregistrations.company_id')
		    ->select('companyregistrations.org_name as companyName', 'companyregistrations.company_account_no as companyAccountNo','companyregistrations.license_no as companyLicense', 'pfmoudetails.mou_date as MoUDate', 'pfmoudetails.mou_expiry_date as MoUExpiryDate', 'pfmoudetails.interest_rate as MoUInterestRate', 'companyregistrations.registration_type as RegistrationType')
		    ->where('companyregistrations.license_no', '=', Encrypter::encrypt($licenseNo))
		    ->where('companyregistrations.registration_type', '=',$type)->get();

	    $companyDetails->transform(function($data) {
	    	$data->companyName = Encrypter::decrypt($data->companyName);
	    	$data->companyAccountNo = Encrypter::decrypt($data->companyAccountNo);
	        $data->companyLicense = Encrypter::decrypt($data->companyLicense);
	        return  $data;
	 	});

     return $companyDetails ? $this->sendResponse($companyDetails,'Company details fetched successfully'):$this->sendError('Company details not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }
}
