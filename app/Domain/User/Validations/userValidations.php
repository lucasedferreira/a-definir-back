<?php
    namespace User;

    use Illuminate\Support\Facades\Hash;

    class Validator
    {
        public static function checkPassword($inputPassword, $userPassword)
        {
            if(!Hash::check($inputPassword, $userPassword)) return false;
            return true;
        }

        public static function passwordStrength($newPassword, $newPasswordConfirm)
        {
            if(!strlen($newPassword) >= 8){
                throw new \Exception("Password is smaller than eight characters");
            }
    
            if (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $newPassword)){
                throw new \Exception("Password has no letters or numbers");
            }
    
            if($newPassword != $newPasswordConfirm){
                throw new \Exception("Passwords don't match");
            }
        }
    }