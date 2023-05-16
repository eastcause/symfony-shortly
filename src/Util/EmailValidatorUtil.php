<?php

namespace App\Util;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;

class EmailValidatorUtil
{

    public static function validateEmail(string $email): bool
    {
        $validator = Validation::createValidator();

        $emailConstraint = new Email([
            'message' => 'Invalid email address'
        ]);

        $violations = $validator->validate($email, $emailConstraint);

        return count($violations) === 0;
    }

}