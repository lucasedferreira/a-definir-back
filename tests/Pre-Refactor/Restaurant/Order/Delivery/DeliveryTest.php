<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class DeliveryTest extends TestCase
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
     * @group Delivery 
    */
    public function test__Delivery_Neigborhood_NoManualSelection_UsingGMAPS__ShouldBeFree()
    {
        /*
        |--------------------------------------------------------------------------
        | Testa delivery por bairro, sem seleção manual, usando endereço do gmaps, 
        | bairro gratuito
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 1,
                'extras'=> [
                    ['id' => 1]
                ]
            ], [
                'address' => 'Rua Prefeito Helmuth Fallgatter',
                'zone' => 'Boa Vista',
                'number' => 35,
                'deliveryFeeID' => 1
            ]));
            
            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total + $lastOrder->delivery_fee, 3.63 + 3);
    }

    /**
     * @group Order
     * @group Delivery 
    */
    public function test__Delivery_Neigborhood_NoManualSelection_UsingCEP__ShouldBeFree()
    {
        /*
        |--------------------------------------------------------------------------
        | Testa delivery por bairro, sem seleção manual, usando CEP, bairro gratuito
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 1,
                'extras'=> [
                    ['id' => 1]
                ]
            ], [
                'address' => '89206-210',
                'CEP' => '89206-210',
                'deliveryFeeID' => 1
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total + $lastOrder->delivery_fee, 3.63 + 3);
    }

    /**
     * @group Order
     * @group Delivery 
    */
    public function test__Delivery_Neigborhood_NoManualSelection_UsingGMAPS__ShouldBePaid()
    {
        /*
        |--------------------------------------------------------------------------
        | Testa delivery por bairro, sem seleção manual, usando endereço do gmaps
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 1,
                'extras'=> [
                    ['id' => 1]
                ]
            ], [ 
                'deliveryFeeID' => 2
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total + $lastOrder->delivery_fee, 3.63 + 3 + 6);
    }

    /**
     * @group Order
     * @group Delivery 
    */
    public function test__Delivery_Neigborhood_WithManualSelection_UsingGMAPS__ShouldBePaid()
    {
        /*
        |--------------------------------------------------------------------------
        | Testa delivery por bairro, com seleção manual, usando endereço do gmaps
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/2/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 4
            ], [ 
                'deliveryFeeID' => 3
            ]));

            // dd($response->response->getContent());
            $response->seeStatusCode(200);


            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total + $lastOrder->delivery_fee, 3.63 + 6);
    }

    /**
     * @group Order
     * @group Delivery 
    */
    public function test__Delivery_Neigborhood_NoManualSelection_UsingCEP__ShouldBePaid()
    {
        /*
        |--------------------------------------------------------------------------
        | Testa delivery por bairro, sem seleção manual, usando CEP
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 1,
                'extras'=> [
                    ['id' => 1]
                ]
            ], [
                'address' => '89227740',
                'CEP' => '89227740',
                'deliveryFeeID' => 2
            ]));

            $response->seeStatusCode(200);
            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total + $lastOrder->delivery_fee, 3.63 + 3 + 6);
    }

    /**
     * @group Order
     * @group Delivery 
    */
    public function test__Delivery_Neigborhood_WithManualSelection_UsingCEP__ShouldBePaid()
    {
        /*
        |--------------------------------------------------------------------------
        | Testa delivery por bairro, com seleção manual, usando CEP
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/2/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 4
            ], [
                'address' => '89227740',
                'CEP' => '89227740',
                'deliveryFeeID' => 3
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total + $lastOrder->delivery_fee, 3.63 + 6);
    }




 

    /**
     * @group Order
     * @group Delivery 
    */
    public function test__Delivery_Area_UsingGMAPS__ShouldBePaid()
    {
        /*
        |--------------------------------------------------------------------------
        | Testa delivery por area, usando gmaps
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/3/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 5
            ], [
                'address' => 'R. do Ouro',
                'number' => 185,
                'zone' => 'Iririú',
                'deliveryFeeID' => '00108d8c-f25e-0f42-3511-306bb73e1f15'
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total + $lastOrder->delivery_fee, 3.63 + 5);
    }

    /**
     * @group Order
     * @group Delivery 
    */
    public function test__Delivery_Area_UsingCEP__ShouldBePaid()
    {
        /*
        |--------------------------------------------------------------------------
        | Testa delivery por area, usando ceps
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/3/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 5
            ], [
                'address' => '89221226',
                'CEP' => '89221226',
                'deliveryFeeID' => '00108d8c-f25e-0f42-3511-306bb73e1f15'
                
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total + $lastOrder->delivery_fee, 3.63 + 5);
    }

    /**
     * @group Order
     * @group Delivery 
    */
    public function test__Delivery_Distance_UsingGMAP__ShouldBePaid()
    {
        /*
        |--------------------------------------------------------------------------
        | Testa delivery por distancia, usando gmaps
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/4/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 6
            ], [
                'address' => 'Rua Madeira',
                'number' => 222,
                'zone' => 'Guanabará',
                'deliveryFeeID' => 1
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total + $lastOrder->delivery_fee, 3.63 + 6);
    }

    /**
     * @group Order
     * @group Delivery 
    */
    public function test__Delivery_Distance_UsingCEP__ShouldBePaid()
    {
        /*
        |--------------------------------------------------------------------------
        | Testa delivery por distancia, usando gmaps
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/4/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 6
            ], [
                'address' => '89207790',
                'CEP' => '89207790',
                'deliveryFeeID' => 1
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total + $lastOrder->delivery_fee, 3.63 + 6);
    }

    public function createOrderShell($orders, $deliveryDetails = [], $deliveryType = 'delivery', $paymentType = 'creditCard', $needChange = false, $changeValue = 0)
    {
        if(is_assoc($orders)){
            $orders = [$orders];
        }

        return [
            'user' => [
                'name'          => 'Marlon de Oliveira dos Santos',
                'address'       => key_exists('address', $deliveryDetails) ? $deliveryDetails['address'] : 'Rua Quefren',
                'number'        => key_exists('number', $deliveryDetails) ? $deliveryDetails['number'] : 35,
                'phone'         => '47996758035',
                'zone'          => key_exists('zone', $deliveryDetails) ? $deliveryDetails['zone'] : 'Iririu',
                'complemento'   => '',
                'CEP'           => key_exists('CEP', $deliveryDetails) ? $deliveryDetails['CEP'] : '',
                'email'         => 'teste@multipedidos.com.br'
            ],
            'details' => [
                'deliveryType'  => $deliveryType,
                'paymentType'   => $paymentType,
                'source'        => 'web',
                'deliveryFee'   => 0,
                'needChange'    => $needChange,
                'changeValue'   => $changeValue,
                'deliveryFeeID' => key_exists('deliveryFeeID', $deliveryDetails) ? $deliveryDetails['deliveryFeeID'] : 2,
            ],
            'orders' => $orders
        ];
    }
}
