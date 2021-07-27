<?php

namespace App\Services;

use App\Exceptions\BusinessException;

class BaseServices
{
    protected static $instance;

    /**
     * 加注释有代码提示
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instance instanceof static) {
            return static::$instance;
        }
        static::$instance = new static();

        return static::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    protected function throwBusinessException(array $codeResponse, $msg = '')
    {
        throw new BusinessException($codeResponse, $msg);
    }
}

