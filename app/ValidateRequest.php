<?php

namespace App;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ValidateRequest
{
    public function verifyId($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer|digits_between:1,20|min:1');
    }

    public function verifyString($key, $default = null)
    {
        return $this->verifyData($key, $default, 'string');
    }

    public function verifyInteger($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer');
    }

    public function verifyBoolean($key, $default = null)
    {
        return $this->verifyData($key, $default, 'boolean');
    }

    public function verifyEnum($key, $default = null, $enum = [])
    {
        return $this->verifyData($key, $default, Rule::in($enum));
    }

    private function verifyData($key, $default, $rule)
    {
        $value = request()->input($key, $default);
        if (is_null($value) && is_null($default)) {
            return null;
        }

        $validate = Validator::make([$key => $value], [$key => $rule]);
        if ($validate->fails()) {
            throw new BusinessException(CodeResponse::PARAM_NOT_EMPTY);
        }

        return $value;
    }
}
