<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\BaseException;

class GoogleException extends BaseException
{
    public function __construct($message = "動画の取得に失敗しました。", $code = Response::HTTP_INTERNAL_SERVER_ERROR, $detail = "")
    {
        parent::__construct($message, $code, $detail);
    }
}