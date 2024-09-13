<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ApiResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // レスポンスマクロを用いて、Responseに'error'というカスタムメソッドを追加し、一貫したエラーレスポンスを返す
        Response::macro('error', function ($status, $message, $detail = '') {
            return response()->json([
                'code' => $status,
                'message' => $message,
                'detaial' => $detail,
            ], $status);
        });
    }
}
