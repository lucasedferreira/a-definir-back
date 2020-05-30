<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\User as UserSetup;
use TestSetup\Restaurant as RestaurantSetup;

class UserRestaurantSuccess extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

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

    /**
     * @group User
     * @group UserRestaurant
    */
    public function testGetRestaurantByUser()
    {
        $response = $this->json('GET', "user/".$this->user->id."/restaurant");
        $response->seeStatusCode(200);

        $restaurant = json_decode($response->response->getContent());
        $this->assertEquals($restaurant->name, $this->restaurant->name);
        $this->assertEquals($restaurant->phone, $this->restaurant->phone);
        $this->assertEquals($restaurant->street, $this->restaurant->street);
    }


    /**
     * @group User
     * @group UserRestaurant
    */
    public function testGetUserByRestaurant()
    {
        $response = $this->json('GET', "user/restaurant/".$this->restaurant->id);
        $response->seeStatusCode(200);

        $user = json_decode($response->response->getContent());
        $this->assertEquals($user[0]->name, $this->user->name);
        $this->assertEquals($user[0]->email, $this->user->email);
    }

    /**
     * @group User
     * @group UserRestaurant
     * @group CreateUser
    */
    public function testCreateUser()
    {
        $response = $this->json('POST', "user/restaurant/".$this->restaurant->id, [
            'name' => 'Patrick',
            'email' => 'estrela@siricascudo.com'
        ]);
        $response->seeStatusCode(200);

        $lastUser = \Model\User::orderBy('id', 'desc')->get()->first();
        $this->assertEquals($lastUser->name, 'Patrick');
        $this->assertEquals($lastUser->email, 'estrela@siricascudo.com');
    }

    /**
     * @group User
     * @group UserRestaurant
     * @group UpdateUser
    */
    public function testUpdateUser()
    {
        $response = $this->json('PUT', "user/restaurant/".$this->restaurant->id."/".$this->user->id, [
            'name' => 'Patrick Estrela',
            'email' => 'patrick.estrela@siricascudo.com'
        ]);
        $response->seeStatusCode(200);

        $user = \Model\User::find($this->user->id);
        $this->assertEquals($user->name, 'Patrick Estrela');
        $this->assertEquals($user->email, 'patrick.estrela@siricascudo.com');
    }

    // /**
    //  * @group User
    //  * @group UserRestaurant
    //  * @group DeleteUser
    // */
    // public function testDeleteUser()
    // {
    //     $response = $this->json('DELETE', "user/restaurant/".$this->restaurant->id."/".$this->user->id);
    //     $response->seeStatusCode(200);

    //     $user = \Model\User::find($this->user->id);
    //     dd($user);
    // }
}