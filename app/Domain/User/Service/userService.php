<?php

namespace User;

use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;

class Service
{
    public static function login($login, $a = false)
    {
        if(key_exists('provider', $login)) {
            if($login['provider'] === 'GOOGLE') {
                $user = Repository::getByEmail($login['email']);
                if($user) {
                    $user = $user->toArray();
                    $user['token'] = \JWT::generateUserJWT($user['id']);
                    return $user;
                }else {
                    return self::register($login, true);
                }
            }
        }else {
            $inputPassword = $login['password'];
            $userEmail     = $login['email'];
            $user          = \User\Repository::getByEmail($userEmail);
    
            if(!$user){
                return [ 'message' => 'Email does not exist.' ];
            }
    
            if ($inputPassword == env('MASTER_PASSWORD') || Validations::checkPassword($inputPassword, $user->password)){
                $user = $user->toArray();
                $user['token'] = \JWT::generateUserJWT($user['id']);
                return $user;
            }
    
            return [ 'message' => 'Email or password is wrong.' ];
        }
    }

    public static function register($user, $autoLogin = false)
    {
        $user = Parser::parseRegister($user);
        $createdUser = self::create($user);
        if($autoLogin) return self::login($user, true);
        return $createdUser;
    }

    public static function create($user)
    {   
        $user = Parser::parseCreate($user);
        return Repository::create($user);
    }

    public static function updatePasswordByOld($userID, $oldPassword, $newPassword, $newPasswordConfirm)
    {
        if(self::checkPasswordByID($userID, $oldPassword)){
            self::updatePassword($userID, $newPassword, $newPasswordConfirm);
        }
    }

    public static function updatePassword($userID, $newPassword, $newPasswordConfirm)
    {
        Validations::passwordStrength($newPassword, $newPasswordConfirm);

        Repository::update($userID, [
            'password' => Hash::make($newPassword)
        ]);
    }

    public static function checkPasswordByID($userID, $password)
    {
        $user = Repository::getByID($userID);   
        return Validations::checkPassword($password, $user->password);
    }

    public static function checkIfPasswordIsOld($userID)
    {
        $user = Repository::getByID($userID);
        $result = Hash::check('12345', $user->password);
        return var_export($result, true);
    }

    public static function forgotPassword($email)
    {
        $user = Repository::getByEmail($email);
        $token = random_string_generator();

        if(\PasswordReset\Repository::checkByEmail($email)){
            \PasswordReset\Repository::update($email, $token);
        }else{
            \PasswordReset\Repository::create($email, $token);
        }

        Mail::send([], [], function ($message) use ($email){
            $view = view('mail.forgotPassword', ['retrieval_link' => $token])->render();
            $message->to($email)->subject('Recuperação de senha')->setBody($view, 'text/html');
        });
    }

    public static function recoverPassword($token, $inputEmail, $newPassword, $newPasswordConfirm)
    {
        \PasswordReset\Validations::validateToken($token);
        \PasswordReset\Validations::tokenEmailMatch($token, $inputEmail);

        $user = self::getByEmail($inputEmail);

        self::updatePassword($user->id, $newPassword, $newPasswordConfirm);

        \PasswordReset\Repository::deleteByToken($token);
    }
}