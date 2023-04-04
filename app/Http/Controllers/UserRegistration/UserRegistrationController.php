<?php

namespace App\Http\Controllers\UserRegistration;

use App\Http\Controllers\Controller;
use App\Models\Forgotpassword;
use App\Models\Passwordreset;
use App\Models\User;
use App\Models\UserLog;
use App\Providers\RouteServiceProvider;
use DB;
use Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator,Location,RateLimiter;
use Laravel\Passport\HasApiTokens;
use Mail;
use Response;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use ESolution\DBEncryption\Encrypter;
use Illuminate\Validation\Rules\Password;
use App\Mail\PasswordChangeNotificationMail;
use Exception;


class UserRegistrationController extends Controller
{
    //** Outside Users Registration */
    use HasApiTokens;
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:3',
            'mobile_number' => 'required|digits:8',
            'password' => ['required','max:50',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()],
            'cid' => ['required', 'string', 'min:11', 'max:11']
        ]);
        
        try{
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
            'cid' => $request->cid,
            'password_created_date' => Carbon::now()->format('Y-m-d'),
            'password_reset_date' => Carbon::now()->addDays(45),
            'gender' => $request->gender,
            'status' => "Inactive",
            'password_status'=> "isChanged",
            'encrypted'=>1,
        ]);
        return $user;
            if(!($request->email === $request->password)){
                $token = $user->createToken('PF/GF API Token')->accessToken;
                return response()->json($token);
            }else{
                return response()->json([
                    'status'=> false,
                    'message'=> 'Username and password cannot be same.'
                ]);
            }
        }catch(Exception $e){
            return $this->errorResponse('Page not found, Please check your email id and password');
        }
    }

    public function login(Request $request)
    {
        $this->validate($request,[
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $location = Location::get('http://'.$request->ip()); 
                    
        $user =  User::where('email', 'LIKE', '%' . Encrypter::encrypt($request['email']))->get()->first();
        try {
            if (RateLimiter::tooManyAttempts(request()->ip(), 3)) {
             DB::table('users')
             ->where('id', '=', $user->id)
             ->update([
                 'status' =>'Inactive'
             ]);
            return response()->json([
                    'success' => false,
                     'message' => 'Too many login attempts. Your Account is Locked. Contact System Administrator to Activate.' 
                ]);
            }
       
         if(!empty($user)){
            if($user->password_status == 'isChanged' && $user->status == 'Active'){
                if(Hash::check($request['password'], $user->password)) {
                    UserLog::create([
                        'user_id' => $user->id,
                        'action' => 'User login',
                        'login_date' => Carbon::now()->format('Y-m-d'),
                        'client_ip'=>$location->ip,
                        'country_name'=>$location->countryName,
                        'region_name'=>$location->regionName,
                        'city_name'=>$location->cityName,
                        'latitude'=>$location->latitude,
                        'longitude'=>$location->longitude,
                        'encrypted'=>1,
                        ]);

                    $token = $user->createToken('ppf_login_token')->accessToken;

                    return response()->json([
                        'success' => true,
                        'message' => 'User login successfully, Use token to authenticate.',
                        'user-data' => $user,
                        'token' => $token
                    ], 200);
                } else {
                    RateLimiter::hit(request()->ip());
                    return response()->json([
                        'success' => false,
                        'message' => 'User authentication failed.',
                        'user-data' => null,
                        'token' => null,
                    ], 401);
                }
            }
           else{
            return response()->json([
                        'success' => false,
                        'message' => 'Contact System Administrator to Activate.',
                        'user-data' => null,
                        'token' => null,
                ], 401);
             }

        }else{
            return response()->json([
                'success' => false,
                'message' => 'Email id doesnt exist in the system.Please check your email!.',
                'user-data' => null,
                'token' => null,
            ], 403);
        }
            RateLimiter::clear(request()->ip());
            return response()->json([ 'access_token' =>  $token ]);
        }catch (\Throwable $th) {
            throw $th;
        }
    }

    public function logout(Request $request)
    {
        $location = Location::get('http://'.$request->ip());                

        $token = $request->user()->token();
        $token->revoke();
    
        UserLog::create([
            'user_id' => $request->user()->id,
            'action' => 'User logout',
            'logout_date' => Carbon::now()->format('Y-m-d'),
            'client_ip'=>$location->ip,
            'country_name'=>$location->countryName,
            'region_name'=>$location->regionName,
            'city_name'=>$location->cityName,
            'latitude'=>$location->latitude,
            'longitude'=>$location->longitude,
            'encrypted'=>1,
        ]);

        $response = ['status'=>'success','message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }

    public function forgotPassword(Request $request)
    {
        $this->validate($request,[
            'user_email' => 'required',
            'cid' =>'required'
            ]);

        DB::beginTransaction();
        $user_email = $request->user_email;
        $user_cid = $request->cid;
        $otp = random_int(11111, 99999);

        $user_data = User::where('email', 'LIKE', '%' . Encrypter::encrypt($user_email) . '%')
        ->where('cid', 'LIKE', '%' . Encrypter::encrypt($user_cid) . '%')
        ->get()->first();

        $passwordreset = new Forgotpassword();
        $passwordreset->user_email = $request->user_email;
        $passwordreset->user_cid = $request->cid;
        $passwordreset->reset_otp = $otp;
        $passwordreset->opt_status = 'Sent';

        if($user_data == null) {

            return response()->json(['status'=>'error', 'message' => 'Your user ID does not found. Please try again latter']);

        }else if($user_data->status == 'Active'){

            $passwordreset->user_id = $user_data->id;
            if ($passwordreset->save()) {
                //** Mail */
                $details = array(
                    'title' => 'BIL : Password reset OPT',
                    'user_email' => $user_email,
                    'otp' => $otp,
                );

                try {
                    Mail::send('emails.password_reset', $details, function ($message) use ($user_email) {
                        $message->from('info.bhutaninsurance@gmail.com', 'PF/GF SYSTEM [BIL]');
                        $message->to($user_email);
                        $message->subject('Password Reset OTP');
                    });

                    DB::commit();
                    return response()->json(['status'=>'success', 'message' => 'The one-time password (OTP) is sent to the registered email address. Please check and verify']);

                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['status'=>'error', 'message' => 'Something went wrong while sending email. Please try again latter']);
                }

            }else{

                return response()->json(['status'=>'error', 'message' => 'Something went wrong while saving forgot password data. Please try again latter']);
            }

        }else{

            return response()->json(['status'=>'error', 'message' => 'Your user ID is not Active. Please contact Administrator']);
        }

    }

    public function verifyOTP(Request $request)
    {
        $forgot_password_data = Forgotpassword::where('reset_otp', '=', $request->user_otp)
            ->where('opt_status','=','Sent')->get()->first();

        if ($forgot_password_data != null) {

            DB::table('forgotpasswords')
                ->where('reset_otp', '=', $request->user_otp)
                ->where('opt_status','=','Sent')
                ->update(['opt_status' => 'Verified']);

            return response()->json(['status'=>'success', 'OTP'=>$request->user_otp,'message' => ' OTP Verified successfully !']);

        }else{

            return response()->json(['status'=>'error', 'message' => 'Invalid OTP number !']);
        }

    }

    public function UpdateResetPassword(Request $request)
    {
        $this->validate($request, [
            'user_otp' =>'required',
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
        $forgot_password_data = Forgotpassword::where('reset_otp', '=', $request->user_otp)
            ->where('opt_status','=','Verified')
            ->get()->first();

        if($forgot_password_data == null){

            return response()->json(['status'=>'error', 'message' => 'Could not update the data. OTP Expired']);

        }else{

            $user_id = $forgot_password_data->user_id;
            $userPassword = $request->password;
            $confirmPassword = $request->confirm_password;

            $user = User::where('id', '=', $user_id)->first();

            if(!($request->password === $user->email)){

                if(!Hash::check($request->password, $user->password)){

                    DB::table('users')
                        ->where('id', '=', $user_id)
                        ->where('status', '=', 'Active')
                        ->update([
                            'password' => Hash::make($confirmPassword),
                            'password_status'=>'isChanged',
                            'password_created_date'=> Carbon::now()->format('Y-m-d'),
                            'password_reset_date'=> Carbon::now()->addDays(45),
                            'password_change_date'=>Carbon::now()->format('Y-m-d'),
                        ]);

                    DB::commit();
                    return response()->json(['status'=>'success', 'message' => 'Password changed successfully']);
                }else {
                    DB::rollBack();
                    return response()->json(['status'=> 'error','message' => 'You are not allow to use the same password time and again.Please create different password!']);
                }
            }else{
                return response()->json(['status'=> 'error','message' => 'Username and password cannot be same.Please use different password!']);
            } 
        }         
    }
}
