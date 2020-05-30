<?php
namespace User;

use Model\User;

class Repository
{
    public static function getRandomAdmin()
    {
        return User::where('role', 'admin')->first();
    }

    public static function getByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public static function getByID($userID)
    {
        return User::find($userID);
    }

    public static function create($user)
    {
        return User::create($user);
    }

    public static function update($userID, $userData)
    {
        return User::find($userID)->fill($userData)->save();
    }

    public static function delete($userID)
    {
        $user = User::find($userID);
        $user->delete();
    }
}