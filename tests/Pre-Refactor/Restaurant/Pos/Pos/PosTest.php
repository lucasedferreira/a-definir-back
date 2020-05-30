<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class PosTest extends TestCase
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
     * @group Pos
    */
    public function testPos()
    {
        /*
        |--------------------------------------------------------------------------
        | Cria Pedido com o caixa FECHADO
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/7/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 7
            ]));

            $response->seeStatusCode(400);
        /*
        |--------------------------------------------------------------------------
        | Abre o Caixa
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('PUT', 'restaurant/7/pos/cashier/open-cashier', [
                'value' => 300.00
            ]);
            
            $response->seeStatusCode(200);

            $cashier = \Entities\Cashier::latest()->first();

            $this->assertEquals($cashier->value, 300.00);
            $this->assertEquals($cashier->open, true);
            $this->assertEquals($cashier->restaurant_id, 7);
        /*
        |--------------------------------------------------------------------------
        | Cria Pedido com o caixa ABERTO
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('PUT', 'restaurant/7/pos', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 7
            ]));

            // dd($response->response->getContent());
            $response->seeStatusCode(200);
            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();
            // dd($lastOrder);
            $this->assertEquals($lastOrder->cashier_id, $cashier->id);
            $this->assertEquals($lastOrder->name, 'Lucas Eduardo Ferreira');
            $this->assertEquals($lastOrder->total_net_value, 3.63);
        /*
        |--------------------------------------------------------------------------
        | Adiciona outro item no pedido
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/7/pos', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 2,
                'id'    => 8
            ], [
                'name'          => 'Lucas Eduardo Batista Ferreira',
                'CEP'           => '89224356',
                'address'       => 'Rua Benjamin Moacir dos Santos',
                'number'        => 9,
                'phone'         => '47988489411',
                'zone'          => 'Jardim Iririú',
                'complemento'   => '',
                'debugMode'     => true                
            ], $lastOrder->id));

            $response->seeStatusCode(200);
            $updatedOrder = \Entities\Order::orderBy('id', 'desc')->first();
            
            // $response = $this->json('GET', 'restaurant/7/order/'.$updatedOrder->id);
            // dd($response->response->getContent());
            $this->assertEquals($updatedOrder->name, 'Lucas Eduardo Batista Ferreira');
            $this->assertEquals($updatedOrder->total_net_value, 17.63);
        /*
        |--------------------------------------------------------------------------
        | Remove um item e adiciona outro no pedido
        |--------------------------------------------------------------------------
        |
        */
            $order = \Order::getById($lastOrder->id);

            $response = $this->json('POST', 'restaurant/7/pos', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 3,
                'id'    => 7
            ], [
                'name'          => 'Lucas Eduardo Batista Ferreira',
                'CEP'           => '89224356',
                'address'       => 'Rua Benjamin Moacir dos Santos',
                'number'        => 9,
                'phone'         => '47988489411',
                'zone'          => 'Jardim Iririú',
                'complemento'   => '',
                'debugMode'     => true
            ],
            $lastOrder->id,
            [$order->items[0]->id]
            ));

            $response->seeStatusCode(200);
            $updatedOrder = \Entities\Order::orderBy('id', 'desc')->first();
            
            $this->assertEquals($updatedOrder->name, 'Lucas Eduardo Batista Ferreira');
            $this->assertEquals($updatedOrder->total_net_value, 24.89);
    }

    public function createOrderShell($orders, $user = [], $orderID = 0, $removedItems = [])
    {
        if(is_assoc($orders)){
            $orders = [$orders];
        }

        if(count($user) == 0){
            $user = [
                'name'          => 'Lucas Eduardo Ferreira',
                'CEP'           => '89224356',
                'address'       => 'Rua Benjamin Moacir dos Santos',
                'number'        => 9,
                'phone'         => '47988489411',
                'zone'          => 'Jardim Iririú',
                'complemento'   => '',
                'debugMode'     => true,
                'email'         => 'teste@multipedidos.com.br'                
            ];
                
        }

        return [
            'id' => $orderID,
            'removedItems' => $removedItems,
            'user' => $user,
            'details' => [
                'id'            => $orderID,
                'deliveryType'  => 'balcony',
                'paymentType'   => 'creditCard',
                'source'        => 'pos',
                'deliveryFee'   => 0,
                'needChange'    => false,
                'changeValue'   => 0
            ],
            'orders' => $orders
        ];
    }
}
