<?php

namespace App\Providers;
use Laravel\Passport\Passport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\PassportClient as Client;
use App\PassportToken as Token;
use App\PassportAuthCode as AuthCode;
use App\PassportPersonalAccessClient as PersonalAccessClient;
use App\PassportRefreshToken as RefreshToken;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
      
        $this->registerPolicies();
 
        Passport::routes();
        Passport::useTokenModel(Token::class);
        Passport::useClientModel(Client::class);
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
        
        //dd('ddd');
          

        //
    }
}
