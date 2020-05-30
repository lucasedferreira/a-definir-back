<?php

namespace PasswordReset;

class Validations
{
    public static function validateToken($token)
    {
        $result = Repository::checkByToken($token);

        if(!$result) throw new \Exception('Invalid Token');
    }

    public static function tokenEmailMatch($token, $inputEmail)
    {
        $userEmail = Repository::getEmailByToken($token);

        if($inputEmail != $userEmail) throw new \Exception('Emails wont match');
    }
}