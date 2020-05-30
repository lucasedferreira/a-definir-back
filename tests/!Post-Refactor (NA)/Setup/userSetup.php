<?php
namespace TestSetup;

class User
{
    public $user;

    public function __construct()
    {
        $this->user = factory(\Model\User::class)->create([
            'name' => 'Lula Molusco',
            'email' => 'lulinha@siricascudo.com'
        ]);
    }
}