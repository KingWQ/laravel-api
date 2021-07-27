<?php

namespace App\Exceptions;

use Exception;

/**
 * 业务异常
 * Class BusinessException
 * @package App\Exceptions
 */
class BusinessException extends Exception
{
    public function __construct(array $codeResponse, $info = '')
    {
        [$code, $message] = $codeResponse;
        parent::__construct($info ? : $message, $code);
    }

}
