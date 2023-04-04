<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PartyType;
use Exception;

class PartyTypeController extends Controller
{
    public function PartyTypeList(){

        try{
            $partytypelist=Partytype::all();
            return $partytypelist ? $this->sendResponse($partytypelist,'Partytypelist Details'):$this->errorResponse($partytypelist,'Partytypelist not found',200);
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }
    public function PartyType(){
        try{
            $partytype= Partytype::where('party_type_code', '=', 'Employee')->get();
        return $partytype ? $this->sendResponse($partytype,'Partytype Details'):$this->errorResponse($partytype,'Partytype not found',200);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }
}
