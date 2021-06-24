<?php

namespace JagdishJP\Packagify\Providers;

use Illuminate\Support\ServiceProvider;
use JagdishJP\Packagify\Console\Commands\PackageMake;
use JagdishJP\Packagify\Console\Commands\Packagify;

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
        $this->mergeConfigFrom(__DIR__ . '/../../Config/packagify.php', 'packagify');
        $this->publishes([__DIR__ . "/../../config/packagify.php" => config_path('packagify.php')]);
        if ($this->app->runningInConsole()) {
            $this->commands([
                Packagify::class,
                PackageMake::class,
            ]);
        }
    }
}
