<?php

namespace Jagdish_J_P\Packagify\Providers;

use Illuminate\Support\ServiceProvider;
use Jagdish_J_P\Packagify\Console\Commands\Packagify;

class PackagifyServiceProvider extends ServiceProvider
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
        if ($this->app->runningInConsole()) {
            $this->commands([
                Packagify::class,
            ]);
        }
    }
}
