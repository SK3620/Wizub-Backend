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
                'detail' => $detail,
            ], $status);
        });

        // レスポンスマクロを用いて、Responseに'success'というカスタムメソッドを追加し、一貫したエラーレスポンスを返す
        // API通信成功時、フロント（SwiftUI側）に特に何も値を返却しない場合に使用
        Response::macro('success', function ($status, $message) {
            return response()->json([
                'code' => $status,
                'message' => $message,
            ], $status);
        });
    }
}
