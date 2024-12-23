<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\BaseException;

class FeatureAccessDeniedInTrial extends BaseException
{
    public function __construct($message = "この機能はお試し期間中はご利用できません。アカウントを作成する必要があります。", $code = Response::HTTP_INTERNAL_SERVER_ERROR, $detail = "")
    {
        parent::__construct($message, $code, $detail);
    }
}