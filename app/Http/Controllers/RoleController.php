<?php

namespace App\Http\Controllers;

use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Exception;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        try{
            $roles = Role::orderBy('id', 'ASC')->paginate(10);
        return $roles ? $this->sendResponse($roles,'Role Details'):$this->sendError('Role not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function store(Request $request)
    {
        try{
            $role = new Role();
        $role->name = $request->role_name;
        $role->guard_name = $request->guard_name;
        $permissions = $request->permissions;
        $role->syncPermissions($permissions);

        if ($role->save()) {
            return response()->json('Success');
        } else {
            return response()->json('Error');
        }
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    public function edit($id)
    {
        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
            ->get('role_has_permissions.permission_id')
            ->all();
        return [$rolePermissions, $role, $permission];
    }

    public function show($id)
    {
        try{
            $role = Role::find($id);
            $permission = Permission::get();
            $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
                ->get('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
                ->all();
        return response()->json([$role, $permission, $rolePermissions]);
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function update(Request $request, $id)
    {
        try{
            $role = Role::find($id);
        $role->name = $request->role_name;
        $role->guard_name = $request->guard_name;

        $permissions = $request->permissions;
        $role->syncPermissions($permissions);

        if ($role->save()) {
            return response()->json('Success update');
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
            DB::table("roles")->where('id', $id)->delete();
        return response()->json('Success Delete');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    /**
     * List Role by User ID
     */

    public function getrolelistbyuserid($id)
    {
        try{
            $roles = User::join("model_has_roles", "model_has_roles.model_id", "=", "users.id")
            ->join("roles", "roles.id", "=", "model_has_roles.role_id")
            ->where("users.id", "=", $id)
            ->orderBy("status", "DESC")
            ->get(["roles.id as role_id", "roles.name as role_name"]);
        return $roles ? $this->sendResponse($roles,'Role Details'):$this->sendError('Role not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }

    }
}
