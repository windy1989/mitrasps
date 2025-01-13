<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set('Asia/Jakarta');
        Relation::morphMap([
            'mitra_marketing_orders'    => 'App\Models\MitraMarketingOrder',
        ]);



        Response::macro('api', function($success, $status=200, $message, $data=null, $meta=[],){
            return response()->json([
                'success' => $success,
                'status'  => $status,
                'message' => $message,
                'data'    => $data, // can contain error ressponse
                'meta'    => array_merge($meta),
            ], $status);
        });
    }
}
