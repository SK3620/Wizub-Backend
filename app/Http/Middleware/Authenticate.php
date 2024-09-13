<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('api/*')) {
            // 即座にHTTPエラーレスポンスを生成して返すための関数 ステータスコードを指定して処理を終了
            // Handler.phpで補足される
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $request->expectsJson() ? null : route('login');
    }
}
