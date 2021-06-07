<?php

namespace Jagdish_J_P\EmailVerifier\Providers;

use Illuminate\Support\ServiceProvider;
use Jagdish_J_P\EmailVerifier\Console\Commands\EmailVerifier;
use Jagdish_J_P\EmailVerifier\Console\Commands\EmailSchedule;
use Jagdish_J_P\EmailVerifier\Console\Commands\EmailStatus;

class EmailVerifierServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
       $this->loadFactoriesFrom(__DIR__."/../../database/factories");
       $this->publishes([__DIR__."/../../config/emailverifier.php"]);
        if ($this->app->runningInConsole()) {
            $this->commands([
                EmailVerifier::class,
                EmailStatus::class,
                EmailSchedule::class,
            ]);
        }
    }
}
