<?php

namespace App\Providers;

use App\Helpers\ObserverHelper;
use App\Helpers\Proxy\ProxyHelper;
use App\Models\ApprovalDownload\ApprovalDownload;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('ProxyHelper', function ($app) {
            return new ProxyHelper();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        ObserverHelper::register();
        // Relation::enforceMorphMap([
        //     'App\Models\ApprovalDownload' => ApprovalDownload::class,
        // ]);
    }
}
