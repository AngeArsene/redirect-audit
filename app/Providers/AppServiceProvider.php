<?php

namespace App\Providers;

use App\Actions\VerifyRedirect;
use App\Redirects\Checks\DestinationCheck;
use App\Redirects\Checks\FinalStatusCheck;
use App\Redirects\Checks\RedirectCountCheck;
use App\Redirects\Checks\RedirectStatusCheck;
use App\Redirects\Tracers\HttpClientTracer;
use App\Redirects\Tracers\RedirectTracer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RedirectTracer::class, function ($app) {
            return new HttpClientTracer((int) config('redirects.max_redirects', 10));
        });

        $this->app->singleton(VerifyRedirect::class, function ($app) {
            return new VerifyRedirect([
                $app->make(RedirectCountCheck::class),
                $app->make(RedirectStatusCheck::class),
                $app->make(DestinationCheck::class),
                $app->make(FinalStatusCheck::class),
            ]);
        });
    }
}
