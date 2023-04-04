<?php

namespace App\Http\Controllers\DepartmentForUserController;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Exception;

class DeptForUsersController extends Controller
{
    public function index()
    {
        try{
            $departments = Department::all();
        return response()->json($departments);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }
}
