<?php

namespace App\Exceptions;

use Exception;

class BaseException extends Exception
{
    // 開発者向けの詳細エラーメッセージ
    protected $detail;

    public function __construct($message = "", $code = 0, $detail = "")
    {
        // 親クラスプロパティの初期化
        // Exception(親クラス)には、getMessage()やgetCode()メソッドなど定義済み
        parent::__construct($message, $code);
        $this->detail = $detail;
    }
    
    public function getDetail()
    {
        return $this->detail;
    }
}