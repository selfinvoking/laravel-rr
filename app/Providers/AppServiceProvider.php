<?php

namespace App\Providers;

use App;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Pass laravel dump output to STDERR for roadrunner
        if (App::environment('local')) {
            VarDumper::setHandler(function ($var) {
                $var = (new VarCloner())->cloneVar($var);
                return (new CliDumper(STDERR))->dump($var);
            });
        }
    }
}
