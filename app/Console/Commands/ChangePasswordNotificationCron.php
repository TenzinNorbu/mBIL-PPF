<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\PasswordChangeNotificationMail;
use App\Mail\ChangePasswordNotifyMail;
use Carbon\Carbon;
use ESolution\DBEncryption\Encrypter;
use Illuminate\Support\Facades\Mail;
use App\Models\User;


class ChangePasswordNotificationCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'changepasswordnotification:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users_notify =User::whereRaw('datediff(Day,password_created_date,getdate())=20')->get();
        $users_notification =User::whereRaw('datediff(Day,password_created_date,getdate())=2')->get();
        
        if($users_notify){   
            foreach($users_notify as $key =>$user){
                $email = $user->email;
                Mail::to($email)->send(new ChangePasswordNotifyMail($user));
                User::where('email', 'LIKE', '%' . Encrypter::encrypt($email))->update([
                    'password_change_date'=> Carbon::now()->format('Y-m-d'),
                ]);
            }
        }if($users_notification){
            foreach($users_notification as $key =>$user){
                $email = $user->email;
                Mail::to($email)->send(new PasswordChangeNotificationMail($user));
                User::where('email', 'LIKE', '%' . Encrypter::encrypt($email))->update([
                    'password_change_date'=> Carbon::now()->format('Y-m-d'),
                    'password_status'=>'notChanged'
                ]);
         }
        }else{ 
            return response()->json([
                'success' => false,
                 'message' => 'There is no user to notify the password notification.' 
            ]);
        }
     }
}
