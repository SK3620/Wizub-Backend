<?php

namespace App\Exceptions;

use App\Models\Subtitle;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException as ValidationValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Google_Service_Exception;
use Google_Exception;


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
        if ($request->is('api/*')) {

            // バリデーションエラー
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return $this->validationErrorResponse($exception);
            }

            // アカウント削除時のエラー
            if ($exception instanceof AuthException) {
                return $this->authErrorResponse($exception);
            }

            // 動画/字幕に関するエラー
            if ($exception instanceof VideoSubtitleException) {
                return $this->videoSubtitleErrorResponse($exception);
            }

            // 字幕翻訳に関するエラー
            if ($exception instanceof OpenAIException) {
                return $this->openAIErrorResponse($exception);
            }

            //  GoogleAPIエラー（YouTube動画取得時のエラー)
            if ($exception instanceof GoogleException) {
                return $this->googleApiErrorResponse($exception);
            }

            // お試し利用中による制限機能へのアクセスエラー
            if ($exception instanceof FeatureAccessDeniedInTrial) {
                return $this->featureAccessDeniedInTrial($exception);
            }

            // HTTPエラー処理
            if ($this->isHttpException($exception)) {
                return $this->apiErrorResponse($request, $exception);
            }
        }

        // 親クラスのrenderメソッド
        return parent::render($request, $exception);
    }

    // バリデーションエラー
    private function validationErrorResponse(ValidationValidationException $exception)
    {
        // エラーメッセージをフォーマットして返す
        return response()->error(Response::HTTP_BAD_REQUEST, '不正なリクエストです。', $exception->errors());
    }

    // アカウント削除に関するエラー
    public function authErrorResponse(AuthException $exception)
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $detail = $exception->getDetail();

        return response()->error($code, $message, $detail);
    }

    // 動画/字幕に関するエラー
    public function videoSubtitleErrorResponse(VideoSubtitleException $exception)
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $detail = $exception->getDetail();

        return response()->error($code, $message, $detail);
    }

    // 字幕翻訳に関するエラー
    public function openAIErrorResponse(OpenAIException $exception)
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $detail = $exception->getDetail();

        return response()->error($code, $message, $detail);
    }

    // GoogleAPIエラー（YouTube動画取得時のエラー)
    private function googleApiErrorResponse(GoogleException $exception)
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $detail = $exception->getDetail();

        return response()->error($code, $message, $detail);
    }

    // お試し利用中による制限機能へのアクセスエラー
    private function featureAccessDeniedInTrial(FeatureAccessDeniedInTrial $exception) 
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $detail = $exception->getDetail();

        return response()->error($code, $message, $detail);
    }

    // HTTPエラー
    private function apiErrorResponse($request, $exception)
    {
        // 例外からステータスコード取得
        $statusCode = $exception->getStatusCode();
        // 詳細なエラーメッセージを取得
        $detial = $exception->getMessage();

        // HttpExceptionが発生しているかどうかを確認
        if ($this->isHttpException($exception)) {

            // ApiServiceProviderで作成したレスポンスマクロ'error'を呼ぶ
            switch ($statusCode) {
                    // ステータスコードを表す定数名"$status"とエラーメッセージ"$message"を指定
                case 400:
                    return response()->error(Response::HTTP_BAD_REQUEST, '不正なリクエストです。', $detial);
                case 401:
                    // app/Http/Middleware/Authenticate.php内でのabort処理によりHandler.phpでUnauthorizedエラーを補足させる
                    return response()->error(Response::HTTP_UNAUTHORIZED, '認証情報が正しくありません。', $detial);
                case 403:
                    return response()->error(Response::HTTP_FORBIDDEN, 'アクセスエラーが発生しました。', $detial);
                case 404:
                    return response()->error(Response::HTTP_NOT_FOUND, '存在しないURLです。', $detial);
                case 405:
                    return response()->error(Response::HTTP_METHOD_NOT_ALLOWED, '無効なリクエストです。', $detial);
                case 406:
                    return response()->error(Response::HTTP_NOT_ACCEPTABLE, '受付不可能なリクエスト値です。', $detial);
                case 408:
                    return response()->error(Response::HTTP_REQUEST_TIMEOUT, 'リクエストがタイムアウトしました。', $detial);
                    // 500系エラー（500〜599をまとめて処理）
                case ($statusCode >= 500 && $statusCode <= 599):
                    return response()->error($statusCode, 'サーバーでエラーが発生しました。', $detial);
                default:
                    return response()->error(self::UNKNOWN, 'サーバーで不明なエラーが発生しました。', $detial);
            }
        }

        return response()->error(self::UNKNOWN, 'サーバーで不明なエラーが発生しました。', $detial);
    }
}
