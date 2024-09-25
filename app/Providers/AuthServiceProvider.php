<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
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
     
        Passport::enableImplicitGrant();
        
        // Passport::tokensExpireIn(now()->addMinute(120));
        Passport::tokensExpireIn(now()->addHours(8));
        // Passport::refreshTokensExpireIn(now()->addMinute(240));
        Passport::refreshTokensExpireIn(now()->addHours(16));
    }
}
