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
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
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

        Gate::define('hasPermission', function ($user, $action) {

            $permission = $user->role()->first();

            $permission = $permission->permission()->value('name');

            $permission = explode(',', $permission); //seperate name string by ',' and push them to array

            if (in_array($action, $permission) || in_array('all', $permission)) {

                return true;
            } else {
                return false;
            }

        });

    }
}
