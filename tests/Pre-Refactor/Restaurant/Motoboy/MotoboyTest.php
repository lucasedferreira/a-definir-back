<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class MotoboyTest extends TestCase
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
     * @group Motoboy
    */
    public function testMotoboy()
    {
        $response = $this->json('POST', 'restaurant/1/motoboy', [
            'name' => 'Cléiton'
        ]);
        
        $response->seeStatusCode(201);

        $motoboy = \Entities\Motoboy::latest()->first();
        // dd($motoboy->id);
        $this->assertEquals($motoboy->name, 'Cléiton');
        $this->assertEquals($motoboy->restaurant_id, 1);

        $response = $this->json('GET', 'restaurant/1/motoboy');
        
        $response->seeStatusCode(200);

        $response = $this->json('PUT', "restaurant/1/motoboy/$motoboy->id/", [
            'name' => 'Craudio'
        ]);

        $response->seeStatusCode(200);

        $motoboy = \Entities\Motoboy::latest()->first();
        $this->assertEquals($motoboy->name, 'Craudio');
        $this->assertEquals($motoboy->restaurant_id, 1);

        $this->createOrder();
        $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

        $response = $this->json('PUT', "restaurant/1/order/$lastOrder->id/motoboy", [
            'motoboyID' => $motoboy->id
        ]);

        $response->seeStatusCode(200);

        $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();
        $this->assertEquals($lastOrder->motoboy_id, $motoboy->id);

        $response = $this->json('DELETE', "restaurant/1/motoboy/$motoboy->id/");

        $response->seeStatusCode(200);

        $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();
        $this->assertEquals($lastOrder->motoboy_id, NULL);
    }


    public function createOrder()
    {
        $client = factory(Entities\Client::class)->create([
            'restaurant_id' => 1,
        ]);

        Entities\Order::unguard();
        $order = factory(Entities\Order::class)->create([
            'restaurant_id' => 1,
            'address' => $client->street,
            'street_number' => $client->street_number,
            'complemento' => $client->complemento,
            'client_id' => $client->id,
            'bairro' => $client->bairro,
            'phone' => $client->phone,
            'name' => $client->name,
            'total' => 10,
            'order_no' => 1,
            'created_at' => (\Carbon\Carbon::now())->addHours(1)
        ]);
        Entities\Order::reguard();

        $order->items()->create([
            'menu_name' => 'Teste',
            'menu_price' => 10,
            'quantity' => 1,
            'notes' => ''
        ]);
    }
}
