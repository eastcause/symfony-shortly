<?php

namespace App\Util;

class RandomStringGeneratorUtil
{

    public static function generateRandomString($length = 8): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        $charactersLength = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[rand(0, $charactersLength - 1)];
        }
        return $result;
    }

}