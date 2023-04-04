<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Exception;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permission-list|permission-create|permission-edit|permission-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:permission-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:permission-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
       try{
        $permission = Permission::all();
        return $permission ? $this->sendResponse($permission,'Permission Details'):$this->sendError('Permission not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function store(Request $request)
    {
        try{
            $permission = new Permission();
        $permission->name = $request->permission_name;
        $permission->guard_name = 'api';

        if ($permission->save()) {
            return response()->json(['success', 'message' => 'Permission created successfully']);
        } else {
            return response()->json(['error', 'message' => 'Failed to create permission']);
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    // Tested
    public function show($id)
    {
     try{
        $permissions = Permission::find($id);
        return $permissions ? $this->sendResponse($permissions,'Permission Details'):$this->sendError('Permission not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    // Tested
    public function edit($id)
    {
        $permission = Permission::find($id);
        return response()->json($permission);
    }

    // Tested
    public function update(Request $request, $id)
    {
        try{
            $this->validate($request, [
            'permission_name' => 'required|min:3',
            'guard_name' => 'required',
        ]);

        $permission = Permission::find($id);
        $permission->name = $request->permission_name;
        $permission->guard_name = $request->guard_name;
        if ($permission->save()) {
            return response()->json('Success');
        } else {
            return response()->json('Error');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    // Tested
    public function destroy($id)
    {
        try{
            $permission = Permission::find($id);
        if ($permission->delete()) {
            return response()->json('Success');
        } else {
            return response()->json('Error');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    //Get Permission List by User ID
    public function getpermissionlistbyuserid($id)
    {
        try{
            $permissions = User::join("model_has_permissions", "model_has_permissions.model_id", "=", "users.id")
            ->join("permissions", "permissions.id", "=", "model_has_permissions.permission_id")
            ->where("users.id", "=", $id)
            ->orderBy("status", "DESC")
            ->get(["permissions.id as permission_id", "permissions.name as permission_name"]);
        return $permissions ? $this->sendResponse($permissions,'Permission Details'):$this->sendError('Permission not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }

    }

    //Get Permission List By Role ID
    public function getpermissionbyroleid($id)
    {
    try{
        $role = Role::find($id);
        $p = $role->permissions()->get();
        return response()->json($p);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }
}
