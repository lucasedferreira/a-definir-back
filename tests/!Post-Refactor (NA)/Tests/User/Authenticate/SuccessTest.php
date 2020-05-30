<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

use TestSetup\User as UserSetup;
use TestSetup\Restaurant as RestaurantSetup;

class UserAuthenticateSuccess extends TestCase
{
    use DatabaseTransactions;

    public $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();

        $this->userSetup = new UserSetup();
        $this->user = $this->userSetup->user;

        $this->restaurantSetup = new RestaurantSetup();
        $this->restaurantSetup->associateWithUser($this->user);

        $this->restaurant = $this->restaurantSetup->restaurant;
    }

    public function login()
    {
        $response = $this->json('POST', "auth/login", [
            'email' => $this->user->email,
            'password' => '12345'
        ]);
        $response->seeStatusCode(200);

        return json_decode($response->response->getContent())->token;
    }

    /**
     * @group User
     * @group UserAuthenticate
    */
    public function testLogin()
    {
        $token = $this->login();
        $credentials = JWT::decode($token, env('JWT_SECRET'), app('hash'));
        $user = \Model\User::find($credentials->sub);

        $this->assertEquals($user->name, $this->user->name);
        $this->assertEquals($user->email, $this->user->email);
    }

    /**
     * @group User
     * @group UserAuthenticate
     * @group CheckToken
    */
    public function testCheckToken()
    {
        $token = $this->login();
        $response = $this->json('POST', "auth/check", [
            'token' => $token
        ]);
        $response->seeStatusCode(200);
    }

    /**
     * @group User
     * @group UserAuthenticate
     * @group RefreshToken
    */
    public function testRefreshToken()
    {
        $token = $this->login();
        $response = $this->json('POST', "auth/refresh-token", [
            'token' => $token
        ]);
        $response->seeStatusCode(200);
    }

    // /**
    //  * @group User
    //  * @group UserAuthenticate
    //  * @group ForgotPassword
    // */
    // public function testForgotPassword()
    // {
    //     $response = $this->json('POST', "auth/forgot-password", [
    //         'email' => $this->user->email
    //     ]);
    //     $response->seeStatusCode(200);
    // }

    /**
     * @group User
     * @group UserAuthenticate
     * @group DeleteToken
    */
    public function testDeleteToken()
    {
        $token = $this->login();
        $response = $this->json('POST', "auth/delete-token", [
            'token' => $token
        ]);
        $response->seeStatusCode(200);
    }

    // /**
    //  * @group User
    //  * @group UserAuthenticate
    //  * @group RecoverPassword
    // */
    // public function testRecoverPassword()
    // {
    //     $token = $this->login();
    //     $response = $this->json('POST', "auth/recover-password", [
    //         'token' => $token,
    //         'email' => $this->user->email,
    //         'newPassword' => '54321',
    //         'newPasswordConfirm' => '54321'
    //     ]);
    //     $response->seeStatusCode(200);
    // }

    /**
     * @group User
     * @group UserAuthenticate
     * @group GetUserByJWT
    */
    public function testGetUserByJWT()
    {
        $token = $this->login();
        $response = $this->json('GET', "auth/user", [], ['Authorization' => "Bearer $token"]);
        $response->seeStatusCode(200);

        $user = json_decode($response->response->getContent());
        $this->assertEquals($user->name, $this->user->name);
        $this->assertEquals($user->email, $this->user->email);
    }

    /**
     * @group User
     * @group UserAuthenticate
     * @group Invalidate
    */
    public function testInvalidate()
    {
        $token = $this->login();
        $response = $this->json('DELETE', "auth/invalidate", [], ['Authorization' => "Bearer $token"]);
        $response->seeStatusCode(200);
    }
}
