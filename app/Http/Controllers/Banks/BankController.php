<?php

namespace App\Http\Controllers\Banks;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;
use Exception;

class BankController extends Controller
{
    // function __construct()
    // {
    //     $this->middleware('permission:bank-list|bank-create|bank-edit|bank-delete', ['only' => ['index', 'show']]);
    //     $this->middleware('permission:bank-create', ['only' => ['create', 'store']]);
    //     $this->middleware('permission:bank-edit', ['only' => ['edit', 'update']]);
    //     $this->middleware('permission:bank-delete', ['only' => ['destroy']]);
    // }

    public function index()
    {
    try{
            $bankLists = Bank::all();
        return $bankLists ? $this->sendResponse($bankLists,'Bank list Details'):$this->sendError('Bank List not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function store(Request $request)
    {
        request()->validate([
            'bank_code' => 'required',
            'bank_name' => 'required',
            'bnk_branch' => 'required',
        ]);
        try{
        $bankId = Uuid::generate();
        $saveBankId = new Bank();
        $saveBankId->bank_id = $bankId;
        $saveBankId->bank_code = $request->bank_code;
       
        $saveBankId->bank_name = $request->bank_name;
        $saveBankId->bnk_branch = $request->bnk_branch;

        

        if ($saveBankId->save()) {
            return response()->json('Bank Details Successfully Saved! ');
        } else {
            return response()->json('Error Saving Bank Details');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function show($id)
    {
    try{
        $bank = Bank::find($id);
        return $bank ? $this->sendResponse($bank,'Bank Details'):$this->sendError('Bank not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function edit($id)
    {
        $bank = Bank::find($id);
        return response()->json($bank);
    }

    public function update(Request $request, $id)
    {
        try{
            request()->validate([
            'bank_code' => 'required',
            'bank_name' => 'required',
            'bnk_branch' => 'required',
        ]);
        $bank = Bank::find($id);
        $bank->bank_code = $request->bank_code;
        $bank->bank_name = $request->bank_name;
        $bank->bnk_branch = $request->bnk_branch;
        if ($bank->save()) {
            return 'Success Update';
        } else {
            return 'Error Update';
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function destroy($id)
    {
        try{
            $bank = Bank::find($id);

        if ($bank->delete()) {
            return 'success delete';
        } else {
            return 'error delete';
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function getResourceFiles($file_name) {
      $doc_path = storage_path('app/resources/' . $file_name);

      $headers = [
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'attachment; filename="' . $file_name . '"',
          'Access-Control-Expose-Headers' => 'Content-Disposition'
      ];

      $filename = basename($file_name);

      return response()->download($doc_path, $filename, $headers);
    }
}
