<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\Exceptions\BaseException;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
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

    
    // 例外発生時にその例外をどのようにレスポンスとして返すかを決定する
    public function render($request, $exception)
    {
        if ($request->is('api/*')) {
            return $this->handleApiException($exception);
        }

        return parent::render($request, $exception);
    }

    // API例外を処理して適切なレスポンスを返す
    private function handleApiException($exception)
    {

        // BaseExceptionを継承するカスタム例外クラスを捕捉
        if ($exception instanceof BaseException) {
        Log::debug($exception);
        return $this->formatErrorResponse($exception);
        }

        // バリデーションエラー
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return $this->validationErrorResponse($exception);
        }
        
        // HTTPエラー
        if ($this->isHttpException($exception)) {
            return $this->apiErrorResponse($exception);
        }

        // 不明なエラー
        return $this->defaultErrorResponse($exception);
    }

     // 共通のエラーレスポンスフォーマット
    private function formatErrorResponse(BaseException $exception)
    {
        return response()->error(
            $exception->getCode(),
            $exception->getMessage(),
            $exception->getDetail()
        );
    }

    // バリデーションエラーのハンドラー
    private function validationErrorResponse(\Illuminate\Validation\ValidationException $exception)
    {
        return response()->error(Response::HTTP_BAD_REQUEST, '不正なリクエストです。', $exception->errors());
    }

    // HTTPエラーのハンドラー
    private function apiErrorResponse($exception)
    {
        $statusCode = $exception->getStatusCode();
        $detail = $exception->getMessage();
        
        $errorMessages = [
            Response::HTTP_BAD_REQUEST => '不正なリクエストです。',
            Response::HTTP_UNAUTHORIZED => '認証情報が正しくありません。',
            Response::HTTP_FORBIDDEN => 'アクセスエラーが発生しました。',
            Response::HTTP_NOT_FOUND => '存在しないURLです。',
            Response::HTTP_METHOD_NOT_ALLOWED => '無効なリクエストです。',
            Response::HTTP_NOT_ACCEPTABLE => '受付不可能なリクエスト値です。',
            Response::HTTP_REQUEST_TIMEOUT => 'リクエストがタイムアウトしました。',
        ];

        $message = $errorMessages[$statusCode] ?? 'サーバー側でエラーが発生しました。';

        if ($statusCode >= 500 && $statusCode <= 599) {
            $message = 'サーバー側でエラーが発生しました。';
        }

        return response()->error($statusCode, $message, $detail);
    }

    // 未対応例外のデフォルトレスポンス
    private function defaultErrorResponse($exception)
    {
        return response()->error(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            'サーバー側で不明なエラーが発生しました。',
            ['message' => $exception->getMessage()]
        );
    }
}
