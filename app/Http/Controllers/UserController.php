<?php

namespace App\Http\Controllers;

use App\Models\User;
use DB;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Hash;
use ESolution\DBEncryption\Encrypter;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;


class UserController extends Controller
{
    use HasRoles;
    public function __construct()
    {
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        try{
            $listUsers = User::join("branches", "branches.id", "=", "users_branch_id")
            ->join("departments", "departments.id", "=", "users_department_id")
            ->select('users.id as user_id', 'users.name', 'users.dob', 'users.designation', 'users.employee_id', 'users.mobile_number',
            'users.phone_number', 'users.users_branch_id', 'users.users_department_id', 'users.cid', 'users.gender', 'users.status',
            'users.email', 'branch_name', 'branch_code', 'branch_location', 'department_name', 'department_code')
            ->get();
           
        return $listUsers ? $this->sendResponse($listUsers,'User list Details'):$this->sendError('User lists not found');
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }
    }

    //** Create New User by Admin tested */
    public function store(Request $request)
    {
     try{
        $this->validate($request, [
            'name' => 'required|min:3',
            'email' => 'required|unique:users',
            'employee_id' => 'required|unique:users',
            'mobile_number' => 'required|digits:8',
            'password' => ['required','max:50',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()],
            // 'password_confirmation' => 'required|same:password',
            'cid' => ['required', 'string', 'min:11', 'max:11', 'unique:users']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'dob' => $request->dob,
            'designation' => $request->designation,
            'employee_id' => $request->employee_id,
            'mobile_number' => $request->mobile_number,
            'phone_number' => $request->phone_number,
            'users_branch_id' => $request->users_branch_id,
            'users_department_id' => $request->users_department_id,
            'password_created_date' => Carbon::now()->format('Y-m-d'),
            'password_reset_date' => Carbon::now()->addDays(45),
            'cid' => $request->cid,
            'encrypted'=>1,
            'password_status'=> "isChanged",
            'gender' => $request->gender,
            'status' => $request->status == "1" ? 'Active' : 'Inactive',
        ]);
    }catch(Exception $e){
        return $this->errorResponse('Page not found');
    }

        // Assign Roles
        $roles = $request->roles;
        $assignrole = $user->syncRoles($roles);

        // Assign Permissions
        $permissions = $request->permissions;
        $assignPermission = $user->syncPermissions($permissions);

        if ($assignrole && $assignPermission) {
            return response()->json('Success');
        } else {
            return response()->json('Error');
        }
    }

    public function edit($id = NULL)
    {
        $roles = Role::all();
        $user = User::find($id);
        $permission = Permission::all();
        $user_permissions = $user->getAllPermissions();

        $user_roles = User::join("model_has_roles", "model_has_roles.model_id", "=", "users.id")
            ->join("roles", "roles.id", "=", "model_has_roles.role_id")
            ->where("users.id", "=", $id)
            ->where('users.status', '=', 'Active')
            ->get(["roles.id as role_id", "roles.name as role_name"]);

        return response()->json(['user-data' => $user, 'user-role' => $user_roles, 'user-permissions' => $user_permissions, 'roles' => $roles, 'permission' => $permission]);
    }
    public function show($id)
    {
        try{
         $user=User::join("branches", "branches.id", "=", "users_branch_id")
            ->join("departments", "departments.id", "=", "users_department_id")
            ->find($id);
        return $user ? $this->sendResponse($user,'User Details'):$this->sendError('User not found');
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);
            $user->status = $request->status == "1" ? 'Active' : 'Inactive';
            $user->name = $request->name;
            $user->email = $request->email;
            $user->dob = $request->dob;
            $user->designation = $request->designation;
            $user->employee_id = $request->employee_id;
            $user->mobile_number = $request->mobile_number;
            $user->phone_number = $request->phone_number;
            $user->users_branch_id = $request->users_branch_id;
            $user->users_department_id = $request->users_department_id;
            $user->cid = $request->cid;
            $user->gender = $request->gender;
            $user->save();
            
            //** Assign Roles */
            $roles = $request->roles;
            $assignrole = $user->syncRoles($roles);

            //** Assign Permissions */
            $permissions = $request->permissions;
            $assignPermission = $user->syncPermissions($permissions);

            if (($assignrole && $assignPermission)) {
                return response()->json(['success','message'=>'Success Updating the User']);
            } else {
                return response()->json(['error','message'=>'Error Updating the User']);
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    public function destroy($id)
    {
        try{
            if(User::find($id)->delete()){
            return response()->json('Success');
        } else {
            return response()->json('Error Deleting User');
        }
    }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }
    }

    // profile list
    public function showUserProfile($cid)
    {
        return User::where('cid', '=', $cid)->get();
    }

    public function changeUserPassword(Request $request)
    {
        $this->validate($request, [
            'password' => ['required','max:50',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()],
            'confirm_password' => 'required|same:password',
        ]);

        DB::beginTransaction();
        $user_id = auth('api')->user()->id;
        $user = User::where('id','=',$user_id)->first();
        $userPassword = $request->password;
        $confirmPassword = $request->confirm_password;

        if(!($request->password === $user->email)){
            if(!Hash::check($request->password, $user->password)){
                    DB::table('users')
                        ->where('id', '=', $user_id)
                        ->where('status', '=', 'Active')
                        ->update([
                            'password' => Hash::make($userPassword),
                            'password_created_date'=> Carbon::now()->format('Y-m-d'),
                            'password_reset_date'=> Carbon::now()->addDays(45),
                            'password_change_date'=>Carbon::now()->format('Y-m-d'),
                        ]);
                    DB::commit();
                    return response()->json(['status'=>'success', 'message' => 'Password changed successfully']);
            }else{
                return response()->json([
                    'status'=> 'error',
                    'message' => 'You are not allow to use the same password time and again.Please create different password!']);
            }
        }else{
            return response()->json(['status'=> 'error','message' => 'Username and password cannot be same.Please use different password!']);
        }     
    }

    public function getPermissionByUserId($id)
    {
        try{
            $user = User::find($id);
            $user_permissions = $user->getAllPermissions();

            return response()->json(['user-data' => $user, 'user-permissions-list' => $user_permissions]);
        }catch(Exception $e){
            return $this->errorResponse('Page not found');
        }

    }

}
