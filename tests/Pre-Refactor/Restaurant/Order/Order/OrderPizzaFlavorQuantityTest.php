<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class OrderPizzaFlavorQuantityTest extends TestCase
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
     * @group Order_Pizza
     * @group Order_Pizza_Incremental
     * @group Order_Pizza_Flavor_Quantity
    */
    public function testNewPizzaIncrementalQuantityFlavors()
    {
        /*
        |--------------------------------------------------------------------------
        | Pizza Incremental com borda e 2/4 sabor especial camarão 1/4 sabor
        | não especial bacon e 1/4 sabor não especial milho 
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'pizza',
                'qty'   => 1,
                'id'    => 1, 
                'flavors'=> [
                    'selected' => [
                        ['id'  => 1, 'quantity' => 1],
                        ['id'  => 2, 'quantity' => 1],
                        ['id'  => 12, 'quantity' => 2], 
                    ]
                ],
                'crust' => ['id' => 1]
            ]));

            $response->seeStatusCode(200);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrderItem->menu_name, 'Pizza Gigante Incremental');
            $this->assertEquals($lastOrderItem->menu_price, 40);
            $this->assertEquals($lastOrderItem->type, 'pizza');
            $this->assertEquals($lastOrderItem->pizza_price_behavior, 'incremental');
            $this->assertEquals($lastOrderItem->item_sub_total, 40 + 5 + 23*2);

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($crust->name, 'Borda de cheddar');
            $this->assertEquals($crust->price, 5);

            $flavors = \Entities\OrderPizzaFlavor::where('order_item_id', $lastOrderItem->id)->orderBy('id', 'asc')->get();

            $this->assertEquals($flavors[0]->name, 'Bacon');
            $this->assertEquals($flavors[0]->price, 0);
            
            $this->assertEquals($flavors[1]->name, 'Milho');
            $this->assertEquals($flavors[1]->price, 0);
            
            $this->assertEquals($flavors[2]->name, 'Camarão');
            $this->assertEquals($flavors[2]->price, 23);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrder->total, 40 + 5 + 23*2);
    }

    /**
     * @group Order
     * @group Order_Pizza
     * @group Order_Pizza_Highest
     * @group Order_Pizza_Flavor_Quantity
    */
    public function testNewPizzaHighestQuantityFlavors()
    {
        /*
        |--------------------------------------------------------------------------
        | Pizza por maior sabor sem borda com um sabor sem preço
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'pizza',
                'qty'   => 1,
                'id'    => 2, 
                'flavors'=> [
                    'selected' => [
                        [
                            'id'  => 1,
                            'quantity' => 1
                        ]
                    ]
                ],
                'crust' => ['id' => 2]
            ]));

            $response->seeStatusCode(200);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrderItem->menu_name, 'Pizza Gigante Por maior valor');
            $this->assertEquals($lastOrderItem->menu_price, 40);
            $this->assertEquals($lastOrderItem->type, 'pizza');
            $this->assertEquals($lastOrderItem->pizza_price_behavior, 'highest');
            $this->assertEquals($lastOrderItem->item_sub_total, 40);

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($crust->name, 'Sem Borda');
            $this->assertEquals($crust->price, 0);

            $flavor = \Entities\OrderPizzaFlavor::where('order_item_id', $lastOrderItem->id)->get()->last();

            $this->assertEquals($flavor->name, 'Bacon');
            $this->assertEquals($flavor->price, 40);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->get()->first();
            $this->assertEquals($lastOrder->total, 40);
        /*
        |--------------------------------------------------------------------------
        | Pizza por maior sabor com borda e três sabores com diferentes preços cada
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'pizza',
                'qty'   => 1,
                'id'    => 2, 
                'flavors'=> [
                    'selected' => [
                        ['id'  => 1],
                        ['id'  => 11],
                        ['id'  => 12, 'quantity' => 2]
                    ]
                ],
                'crust' => ['id' => 1]
            ]));

            $response->seeStatusCode(200);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrderItem->menu_name, 'Pizza Gigante Por maior valor');
            $this->assertEquals($lastOrderItem->menu_price, 40);
            $this->assertEquals($lastOrderItem->type, 'pizza');
            $this->assertEquals($lastOrderItem->pizza_price_behavior, 'highest');
            $this->assertEquals($lastOrderItem->item_sub_total, 63 + 5);

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($crust->name, 'Borda de cheddar');
            $this->assertEquals($crust->price, 5);

            $flavors = \Entities\OrderPizzaFlavor::where('order_item_id', $lastOrderItem->id)->get();

            $this->assertEquals($flavors[0]->name, 'Bacon');
            $this->assertEquals($flavors[0]->price, 40);
            $this->assertEquals($flavors[0]->quantity, 1);

            $this->assertEquals($flavors[1]->name, 'Verduras Especial');
            $this->assertEquals($flavors[1]->price, 60);
            $this->assertEquals($flavors[1]->quantity, 1);

            $this->assertEquals($flavors[2]->name, 'Camarão');
            $this->assertEquals($flavors[2]->price, 63);
            $this->assertEquals($flavors[2]->quantity, 2);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            $this->assertEquals($lastOrder->total, 63 + 5);
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
                'deliveryFeeID' => 2,
                'needChange'    => $needChange,
                'changeValue'   => $changeValue
            ],
            'orders' => $orders
        ];
    }
}
