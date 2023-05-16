<?php

namespace App\Util;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UrlValidatorUtil
{

    public static function validate(string $url, ValidatorInterface $validator) : bool
    {
        $urlValidate = $validator->validate($url, new Url());
        if (count($urlValidate) > 0) {
            return false;
        }
        return true;
    }

}