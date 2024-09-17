<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class OpenAIException extends Exception
{
    // 以下二つのプロパティは親クラスに元々あるため、再定義の必要はなし
    // あくまで、明示的に再定義することで、保守/可読性の向上に繋げる
    protected $message;
    protected $code;

    // 開発者向けの詳細エラーメッセージ
    protected $detail;

    public function __construct($message = "", $code = Response::HTTP_INTERNAL_SERVER_ERROR, $detail = '')
    {
        // 親クラスプロパティの初期化
        // Exception(親クラス)には、getMessage()やgetCode()メソッドなど定義済み
        parent::__construct($message, $code);

        $this->detail = $detail;
    }

    // 開発者向けの詳細エラーメッセージ
    public function getDetail()
    {
        return $this->detail;
    }
}
