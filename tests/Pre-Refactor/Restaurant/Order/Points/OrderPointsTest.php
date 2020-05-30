<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class OrderPointsTest extends TestCase
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
     * @group Order
     * @group Points
    */
    public function testNewOrderWithPoints__PER_SALE()
    {
        /*
        |--------------------------------------------------------------------------
        | Pedido em restaurante com pontos por venda, com mínimo de pontuação de 
        | 10 reais e 5 pontos por compra
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/8/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 3,
                'id'    => 9
            ]));
            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->approveOrder(8, $lastOrder->id);

            $client = \Entities\Client::where('id', $lastOrder->client_id)->first();

            $this->assertEquals(5, $client->points);
        /*
        |--------------------------------------------------------------------------
        | Refaz o pedido, o mesmo cliente deve possuir o dobro de pontos agora
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/8/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 3,
                'id'    => 9
            ]));
            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->approveOrder(8, $lastOrder->id);

            $client = \Entities\Client::where('id', $lastOrder->client_id)->first();

            $this->assertEquals(10, $client->points);
        /*
        |--------------------------------------------------------------------------
        | Faz um pedido com um prêmio que custa 10 pontos
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/8/order', $this->createOrderShell([
                'type'  => 'prize',
                'qty'   => 1,
                'id'    => 1
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->approveOrder(8, $lastOrder->id);

            $client = \Entities\Client::where('id', $lastOrder->client_id)->first();

            $this->assertEquals(0, $client->points);
    }

    /**
     * @group Order
     * @group Points
    */
    public function testNewOrderWithPoints__POINTS_PER_MONEY()
    {
        /*
        |--------------------------------------------------------------------------
        | Pedido em restaurante com pontos por dinheiro gasto, cada 1 real gasto 
        | equivale a 2 pontos
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/9/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 10
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->approveOrder(9, $lastOrder->id);

            $client = \Entities\Client::where('id', $lastOrder->client_id)->first();

            $this->assertEquals(10, $client->points);
        /*
        |--------------------------------------------------------------------------
        | Refaz o pedido, o mesmo cliente deve possuir o dobro de pontos agora
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/9/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 10
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->approveOrder(9, $lastOrder->id);

            $client = \Entities\Client::where('id', $lastOrder->client_id)->first();

            $this->assertEquals(20, $client->points);
        /*
        |--------------------------------------------------------------------------
        | Faz um pedido com um prêmio que custa 10 pontos
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/9/order', $this->createOrderShell([
                'type'  => 'prize',
                'qty'   => 1,
                'id'    => 2
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->approveOrder(9, $lastOrder->id);

            $client = \Entities\Client::where('id', $lastOrder->client_id)->first();

            $this->assertEquals(10, $client->points);
    }

    public function approveOrder($restaurantID, $orderID)
    {
        $response = $this->json('POST', "restaurant/$restaurantID/order/$orderID/status", ['status' => 'APPROVED']);
        $response->seeStatusCode(200);
    }

    public function createOrderShell($orders, $deliveryType = 'balcony', $paymentType = 'creditCard', $needChange = false, $changeValue = 0)
    {
        if(is_assoc($orders)){
            $orders = [$orders];
        }

        return [
            'user' => [
                'name'          => 'Marlon de Oliveira dos Santos',
                'CEP'           => '89227740',
                'address'       => 'Rua Quefren',
                'number'        => 35,
                'phone'         => '47996758035',
                'zone'          => 'Iririú',
                'complemento'   => '',
                'debugMode'     => true,
                'email'         => 'teste@multipedidos.com.br'
            ],
            'details' => [
                'deliveryType'  => $deliveryType,
                'paymentType'   => $paymentType,
                'source'        => 'web',
                'deliveryFee'   => 0,
                'needChange'    => $needChange,
                'changeValue'   => $changeValue
            ],
            'orders' => $orders
        ];
    }
}
