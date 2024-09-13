<?php

namespace App\Exceptions;

use Dotenv\Exception\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    // 不明なエラー
    public const UNKNOWN = 999;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // reportableはログ出力系
        $this->reportable(function (Throwable $e) {
            //
        });

        // renderableはレンダリング系（jsonレスポンス作成/HTML返却）
        // $this->renderable(function ()) ...
    }

    // renderメソッドをオーバーライドし、例外発生時に、その例外をどのようにレスポンスとして返すかを決定する
    // レンダリング系（jsonレスポンス作成/HTML返却）
    public function render($request, $exception)
    {
        // APIエラーの場合、apiErrorResponseを呼ぶ
        // WEBエラーの場合、ここでエラーハンドリングを完結する
        if ($request->is('api/*')) {

            // バリデーションエラーの場合
            if ($exception instanceof ValidationException) {
                return $this->validationErrorResponse($exception);
            }

            // HTTPエラー処理
            return $this->apiErrorResponse($request, $exception);
        }

        // 親クラスのrenderメソッド
        return parent::render($request, $exception);
    }

    // バリデーションエラー
    private function validationErrorResponse(ValidationException $exception)
    {
        // エラーメッセージをフォーマットして返す
        return response()->error(Response::HTTP_BAD_REQUEST, $exception->errors());
    }

    // HTTPエラー
    private function apiErrorResponse($request, $exception)
    {
        // HttpExceptionが発生しているかどうかを確認
        if ($this->isHttpException($exception)) {
            // 例外からステータスコード取得
            $statusCode = $exception->getStatusCode();

            // ApiServiceProviderで作成したレスポンスマクロ'error'を呼ぶ
            switch ($statusCode) {
                    // ステータスコードを表す定数名"$status"とエラーメッセージ"$message"を指定
                case 400:
                    return response()->error(Response::HTTP_BAD_REQUEST, '不正なリクエストです。');
                case 401:
                    return response()->error(Response::HTTP_UNAUTHORIZED, '認証エラーが発生しました。');
                case 403:
                    return response()->error(Response::HTTP_FORBIDDEN, 'アクセスエラーが発生しました。');
                case 404:
                    return response()->error(Response::HTTP_NOT_FOUND, '存在しないURLです。');
                case 405:
                    return response()->error(Response::HTTP_METHOD_NOT_ALLOWED, '無効なリクエストです。');
                case 406:
                    return response()->error(Response::HTTP_NOT_ACCEPTABLE, '受付不可能なリクエスト値です。');
                case 408:
                    return response()->error(Response::HTTP_REQUEST_TIMEOUT, 'リクエストがタイムアウトしました。');
                    // 500系エラー（500〜599をまとめて処理）
                case ($statusCode >= 500 && $statusCode <= 599):
                    return response()->error($statusCode, 'サーバーでエラーが発生しました。');
                default:
                    return response()->error(self::UNKNOWN, 'サーバーで不明なエラーが発生しました。');
            }
        }
    }
}
