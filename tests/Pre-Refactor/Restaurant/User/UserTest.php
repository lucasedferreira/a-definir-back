<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class UserTest extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    public $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }

    /**
     * @group User
    */
    public function testUser()
    {
        $response = $this->json('POST', 'user/restaurant/1', [
            'name' => 'Midoriya',
            'email' => 'bokunohero@academy.com'
        ]);
        
        $response->seeStatusCode(200);

        $user = \Entities\User::latest()->first();

        $this->assertEquals($user->name, 'Midoriya');
        $this->assertEquals($user->email, 'bokunohero@academy.com');


        $response = $this->json('GET', 'user/restaurant/1');
        
        $response->seeStatusCode(200);


        $response = $this->json('PUT', "user/restaurant/1/$user->id/", [
            'name' => 'Deku'
        ]);

        $response->seeStatusCode(200);

        $user = \Entities\User::latest()->first();
        $this->assertEquals($user->name, 'Deku');


        // dd($user->id);
        $response = $this->json('DELETE', "user/restaurant/1/$user->id/");

        $response->seeStatusCode(200);
    }
}
