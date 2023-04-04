<?php

namespace App\Providers;

use App\Models\Passport\AuthCode;
use App\Models\Passport\Client;
use App\Models\Passport\PersonalAccessClient;
use App\Models\Passport\Token;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();

        if (!$this->app->routesAreCached()) {

            Passport::routes();
        }

//        Passport::loadKeysFrom(__DIR__.'/../secrets/oauth');
//        Passport::personalAccessTokensExpireIn(Carbon::now()->addHours(24));
//        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
    }
}
