<?php

namespace PasswordReset;

use Illuminate\Support\Facades\Hash;

use Model\PasswordReset;

class Repository
{
    public static function create($email, $token)
    {
        return PasswordReset::create([
            'email' => $email,
            'token' => $token
        ]);
    }

    public static function update($email, $token)
    {
        return PasswordReset::where('email', $email)->first()->fill([
            'email' => $email,
            'token' => $token
        ])->save();
    }

    public static function checkByEmail($email)
    {
        $tokens = PasswordReset::where('email', $email)->get();

        if($tokens->count() > 0){
            return true;
        }else{
            return false;
        }
    }

    public static function checkByToken($token)
    {
        $tokens = PasswordReset::where('token', $token)->get();

        if($tokens->count() > 0){
            return true;
        }else{
            return false;
        }
    }

    public static function getEmailByToken($token)
    {
        $token = PasswordReset::where('token', $token)->first()->toArray();
        return $token['email'];
    }

    public static function deleteByToken($token)
    {
        PasswordReset::where('token', $token)->delete();
    }
}