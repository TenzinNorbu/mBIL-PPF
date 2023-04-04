<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Exception;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:branch-list|branch-create|branch-edit|branch-delete', ['only' => ['show']]);
        $this->middleware('permission:branch-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:branch-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:branch-delete', ['only' => ['destroy']]);
    }
    public function index()
    {
    try{
        $branch = Branch::all();
        return $branch ? $this->sendResponse($branch,'Branch Details'):$this->sendError('Branch not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function store(Request $request)
    {
        try{
             $this->validate($request, [
            'branch_name' => 'required',
            'branch_location' => 'required',
            'branch_code' => 'required|unique:branches',
        ]);
        $branch = Branch::create([
            'branch_name' => $request->branch_name,
            'branch_location' => $request->branch_location,
            'branch_code' => $request->branch_code,
        ]);
        return response()->json($branch->id);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function show($id)
    {
        try{
            $branches = Branch::find($id);
            return $branches ? $this->sendResponse($branches,'branch Details'):$this->sendError('Branch not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function edit($id)
    {
        $branch = Branch::find($id);
        return response()->json($branch);
    }

    public function update(Request $request, $id)
    {
        try{
            $this->validate($request, [
            'branch_name' => 'required',
            'branch_location' => 'required',
            'branch_code' => 'required',
        ]);
        $branch = Branch::find($id);

        $branch->branch_name = $request->branch_name;
        $branch->branch_location = $request->branch_location;
        $branch->branch_code = $request->branch_code;

        if ($branch->save()) {
            return response()->json($branch);
        } else {
            return response()->json('Error Updating Branch.');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function destroy($id)
    {
        try{
            $branch = Branch::find($id);
        if (empty($branch)) {
            return response()->json('No Branches to be Deleted');
        } else if ($branch->delete()) {
            return response()->json('Branch Deleted Successfully');
        } else {
            return response()->json('Error Deleting Branch');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }
}
