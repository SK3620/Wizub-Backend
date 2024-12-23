<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\BaseException;

class VideoSubtitleException extends BaseException
{
    public function __construct($message = "", $code = Response::HTTP_INTERNAL_SERVER_ERROR, $detail = "")
    {
        parent::__construct($message, $code, $detail);
    }
}