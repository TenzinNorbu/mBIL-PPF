<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:department-list|department-create|department-edit|department-delete', ['only' => ['show']]);
        $this->middleware('permission:department-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:department-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:department-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        try{
            $departments = DB::table('departments')->get();
            return $departments ? $this->sendResponse($departments,'Departments Details'):$this->sendError('Departments not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'department_name' => 'required|string',
                'department_code' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            $department = new Department();
            $department->department_name = $request->department_name;
            $department->department_code = $request->department_code;

            if ($department->save()) {
                return response()->json('Department Saved Successfully');
            } else {
                return response()->json('Error Creating Department');
            }
        }catch(Exception $e){
            return response()->json('Error Creating Department');
        }
    }

    public function show($id)
    {
        try{
            $departments = DB::table('departments')->where('id', $id)->first();
            return $departments ? $this->sendResponse($departments,'Departments Details'):$this->sendError('Department not found');
        }catch(Exception $e){
            return $this->errorResponse('Pagenotfound');
        }
    }

    public function edit($id)
    {
        $department = DB::table('departments')->where('id', $id)->first();
        return response()->json($department);
    }

    public function update(Request $request, $id)
    {
        try{
            $this->validate($request, [
            'department_name' => 'required|string',
            'department_code' => 'required|string',
        ]);

        $department = DB::table('departments')->where('id', $id)->first();
        $department->department_name = $request->department_name;
        $department->department_code = $request->department_code;

        if ($department->save()) {
            return response()->json($department);
        } else {
            return response()->json('Error');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function destroy($id)
    {
        try{
            $department = DB::table('departments')->where('id', $id)->first();

        if ($department->delete()) {
            return response()->json('Deleted Successfully');
        } else {
            return response()->json('Failed Deleting');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

   public function DeptBybranchId($branch_id){
      try{
            $dept= DB::table('departments')->where('branch_id', '=', $branch_id)->get();
            return $dept ? $this->sendResponse($dept,'Department Details'):$this->sendError('Department not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
   }
}
