<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\BaseException;

class AuthException extends BaseException
{
    public function __construct($message = "認証情報が正しくありません。", $code = Response::HTTP_UNAUTHORIZED, $detail = "")
    {
        parent::__construct($message, $code, $detail);
    }
}