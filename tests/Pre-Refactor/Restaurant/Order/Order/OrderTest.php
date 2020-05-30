<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class OrderTest extends TestCase
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
    */
    public function testNewGenericProductOrder()
    {
        /*
        |--------------------------------------------------------------------------
        | Produto com extra obrigatório
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
            ]));

            $response->seeStatusCode(200);
            
            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'asc')->get()->last();
            
            $this->assertEquals($lastOrderItem->menu_name, 'X-Salada');
            $this->assertEquals($lastOrderItem->menu_price, 3.63);
            $this->assertEquals($lastOrderItem->type, 'general');
            $this->assertEquals($lastOrderItem->item_sub_total, 3.63 + 3);

            $lastOrderExtra = \Entities\OrderExtra::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrderExtra->extra_name, 'Batata Frita');
            $this->assertEquals($lastOrderExtra->extra_price, 3);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total, 3.63 + 3);
        /*
        |--------------------------------------------------------------------------
        | Produto com extra de quantidades e quantidades
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 2,
                'id'    => 1,
                'extras'=> [
                    ['id' => 1],
                    ['id' => 6, 'qty' => 2]
                ]
            ]));
                
            $response->seeStatusCode(200);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrderItem->menu_name, 'X-Salada');
            $this->assertEquals($lastOrderItem->item_sub_total, (3.63 + (5 * 2) + 3) * 2);

            $lastOrdersExtra = \Entities\OrderExtra::orderBy('id', 'desc')->get();
            
            $this->assertEquals($lastOrdersExtra[0]->extra_name, 'Frango');
            $this->assertEquals($lastOrdersExtra[0]->extra_price, 5);
            $this->assertEquals($lastOrdersExtra[0]->quantity, 2);

            $this->assertEquals($lastOrdersExtra[1]->extra_name, 'Batata Frita');
            $this->assertEquals($lastOrdersExtra[1]->extra_price, 3);
            $this->assertEquals($lastOrdersExtra[1]->quantity, 1);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total, (3.63 + (5 * 2) + 3) * 2);
        /*
        |--------------------------------------------------------------------------
        | Produto indisponível
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 1,
                'id'    => 2
            ]));

            $response->seeStatusCode(400);
    }

    /**
     * @group Order
     * @group Order_Pizza
     * @group Order_Pizza_Incremental
    */
    public function testNewPizzaIncremental()
    {
        /*
        |--------------------------------------------------------------------------
        | Pizza Incremental sem borda com sabor não especial
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'pizza',
                'qty'   => 1,
                'id'    => 1, 
                'flavors'=> [
                    'selected' => [
                        ['id'  => 1, 'quantity' => 1]
                    ]
                ],
                'crust' => ['id' => 2]
            ]));

            $response->seeStatusCode(200);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'asc')->get()->last();
            
            $this->assertEquals($lastOrderItem->menu_name, 'Pizza Gigante Incremental');
            $this->assertEquals($lastOrderItem->menu_price, 40);
            $this->assertEquals($lastOrderItem->type, 'pizza');
            $this->assertEquals($lastOrderItem->pizza_price_behavior, 'incremental');
            $this->assertEquals($lastOrderItem->item_sub_total, 40);

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->first();

            $this->assertEquals($crust->name, 'Sem Borda');
            $this->assertEquals($crust->price, 0);

            $flavors = \Entities\OrderPizzaFlavor::where('order_item_id', $lastOrderItem->id)->orderBy('id', 'asc')->get()->first();
            
            $this->assertEquals($flavors->name, 'Bacon');
            $this->assertEquals($flavors->price, 0);
            
            $lastOrder = \Entities\Order::orderBy('id', 'desc')->get()->first();

            $this->assertEquals($lastOrder->total, 40);
        /*
        |--------------------------------------------------------------------------
        | Pizza Incremental com borda e metade sabor especial
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'pizza',
                'qty'   => 1,
                'id'    => 1, 
                'flavors'=> [
                    'selected' => [
                        ['id'  => 1],
                        ['id'  => 12],
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
            $this->assertEquals($lastOrderItem->item_sub_total, 40 + 5 + 9.70);

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($crust->name, 'Borda de cheddar');
            $this->assertEquals($crust->price, 5);

            $flavors = \Entities\OrderPizzaFlavor::where('order_item_id', $lastOrderItem->id)->orderBy('id', 'asc')->get();

            $this->assertEquals($flavors[0]->name, 'Bacon');
            $this->assertEquals($flavors[0]->price, 0);
            
            $this->assertEquals($flavors[1]->name, 'Camarão');
            $this->assertEquals($flavors[1]->price, 9.70);
            
            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrder->total, 40 + 5 + 9.70);
    }

    /**
     * @group Order
    */
    public function testNewPizzaHighest()
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
                        ['id'  => 1]
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
                        ['id'  => 12]
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

            $this->assertEquals($flavors[1]->name, 'Verduras Especial');
            $this->assertEquals($flavors[1]->price, 60);

            $this->assertEquals($flavors[2]->name, 'Camarão');
            $this->assertEquals($flavors[2]->price, 63);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            $this->assertEquals($lastOrder->total, 63 + 5);
        /*
        |--------------------------------------------------------------------------
        | Testa maiores quantidades
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'pizza',
                'qty'   => 3,
                'id'    => 2, 
                'flavors'=> [
                    'selected' => [
                        ['id'  => 1]
                    ]
                ],
                'crust' => ['id' => 2]
            ]));

            $response->seeStatusCode(200);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'desc')->get()->first();

            $this->assertEquals($lastOrderItem->item_sub_total, 40 * 3);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            $this->assertEquals($lastOrder->total, 40 * 3);
    }

    /**
     * @group Order
    */
    public function testNewPizzaAverage()
    {
        /*
        |--------------------------------------------------------------------------
        | Pizza por média de sabor sem borda com um sabor sem preço
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'pizza',
                'qty'   => 1,
                'id'    => 3, 
                'flavors'=> [
                    'selected' => [
                        ['id'  => 1]
                    ]
                ],
                'crust' => ['id' => 2]
            ]));

            $response->seeStatusCode(200);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'desc')->get()->first();

            $this->assertEquals($lastOrderItem->menu_name, 'Pizza Gigante Por média de valor');
            $this->assertEquals($lastOrderItem->menu_price, 40.0);
            $this->assertEquals($lastOrderItem->type, 'pizza');
            $this->assertEquals($lastOrderItem->pizza_price_behavior, 'average');
            $this->assertEquals($lastOrderItem->item_sub_total, 40);

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->last();
                    
            $this->assertEquals($crust->name, 'Sem Borda');
            $this->assertEquals($crust->price, 0);

            $flavor = \Entities\OrderPizzaFlavor::where('order_item_id', $lastOrderItem->id)->get()->last();

            $this->assertEquals($flavor->name, 'Bacon');
            $this->assertEquals($flavor->price, 40);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->total, 40);
        /*
        |--------------------------------------------------------------------------
        | Pizza por média de sabor com borda com três sabores com um preço cada
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'pizza',
                'qty'   => 1,
                'id'    => 3, 
                'flavors'=> [
                    'selected' => [
                        ['id'  => 1],
                        ['id'  => 11],
                        ['id'  => 12]
                    ]
                ],
                'crust' => ['id' => 1]
            ]));

            $response->seeStatusCode(200);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'desc')->get()->first();

            $this->assertEquals($lastOrderItem->menu_name, 'Pizza Gigante Por média de valor');
            $this->assertEquals($lastOrderItem->menu_price, 40);
            $this->assertEquals($lastOrderItem->type, 'pizza');
            $this->assertEquals($lastOrderItem->pizza_price_behavior, 'average');
            $this->assertEquals($lastOrderItem->item_sub_total, 54.33 + 5);

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($crust->name, 'Borda de cheddar');
            $this->assertEquals($crust->price, 5);

            $flavors = \Entities\OrderPizzaFlavor::where('order_item_id', $lastOrderItem->id)->get();

            $this->assertEquals($flavors[0]->name, 'Bacon');
            $this->assertEquals($flavors[0]->price, 40);

            $this->assertEquals($flavors[1]->name, 'Verduras Especial');
            $this->assertEquals($flavors[1]->price, 60);

            $this->assertEquals($flavors[2]->name, 'Camarão');
            $this->assertEquals($flavors[2]->price, 63);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            $this->assertEquals($lastOrder->total, 54.33 + 5);
        /*
        |--------------------------------------------------------------------------
        | Pizza por média de sabor com borda com três sabores com um preço cada
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'pizza',
                'qty'   => 3,
                'id'    => 3, 
                'flavors'=> [
                    'selected' => [
                        ['id'  => 1],
                        ['id'  => 11],
                        ['id'  => 12]
                    ]
                ],
                'crust' => ['id' => 1]
            ]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            $this->assertEquals(ceil(54.33 * 3) + (5 * 3), $lastOrder->total);
    }

    /**
     * @group Order
    */
    public function testNewCombo()
    {
        /*
        |--------------------------------------------------------------------------
        | Combo da pizza incremental com refrigerante, preço base
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'combo',
                'qty'   => 1,
                'id'    => 1,
                'comboItems' => [
                    [
                        'type'  => 'pizza',
                        'qty'   => 1,
                        'id'    => 4, 
                        'flavors'=> [
                            'selected' => [
                                ['id'  => 1]
                            ]
                        ],
                        'crust' => ['id' => 2]
                    ]
                ],
                'extras' => [
                    ['id'  => 9,'qty' => 1]
                ]
            ]));

            $response->seeStatusCode(200);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'desc')->limit(2)->get();

            $pizzaCombo = $lastOrderItem[0];

            $this->assertEquals($pizzaCombo->menu_name, 'Pizza Gigante Incremental (combo)');
            $this->assertEquals($pizzaCombo->menu_price, 40);
            $this->assertEquals($pizzaCombo->type, 'combo_pizza_item');
            $this->assertEquals($pizzaCombo->pizza_price_behavior, 'incremental');   

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($crust->name, 'Sem Borda');
            $this->assertEquals($crust->price, 0);

            $flavors = \Entities\OrderPizzaFlavor::where('order_item_id', $lastOrderItem[0]->id)->orderBy('id', 'asc')->get();

            $this->assertEquals($flavors[0]->name, 'Bacon');
            $this->assertEquals($flavors[0]->price, 0);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            $this->assertEquals($lastOrder->total, 70);
        /*
        |--------------------------------------------------------------------------
        | Combo da pizza incremental com refrigerante, borda de 5 reais
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'combo',
                'qty'   => 1,
                'id'    => 1,
                'comboItems' => [
                    [
                        'type'  => 'pizza',
                        'qty'   => 1,
                        'id'    => 4, 
                        'flavors'=> [
                            'selected' => [
                                ['id'  => 1]
                            ]
                        ],
                        'crust' => ['id' => 1]
                    ]
                ],
                'extras' => [
                    ['id'  => 8,'qty' => 1]
                ]
            ]));

            $response->seeStatusCode(200);

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($crust->name, 'Borda de cheddar');
            $this->assertEquals($crust->price, 5);

            $lastOrderExtra = \Entities\OrderExtra::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrderExtra->extra_name, 'Schin 1l');
            $this->assertEquals($lastOrderExtra->extra_price, 4);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrder->total, 79);
        /*
        |--------------------------------------------------------------------------
        | Combo da pizza incremental com refrigerante, borda de 5 reais e sabores
        | especiais
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'combo',
                'qty'   => 1,
                'id'    => 1,
                'comboItems' => [
                    [
                        'type'  => 'pizza',
                        'qty'   => 1,
                        'id'    => 4, 
                        'flavors'=> [
                            'selected' => [
                                ['id'  => 1],
                                ['id'  => 12]
                            ]
                        ],
                        'crust' => ['id' => 1]
                    ]
                ],
                'extras' => [
                    ['id'  => 8,'qty' => 1]
                ]
            ]));

            $response->seeStatusCode(200);

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($crust->name, 'Borda de cheddar');
            $this->assertEquals($crust->price, 5);

            $lastOrderExtra = \Entities\OrderExtra::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrderExtra->extra_name, 'Schin 1l');
            $this->assertEquals($lastOrderExtra->extra_price, 4);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'desc')->limit(2)->get();

            $flavors = \Entities\OrderPizzaFlavor::where('order_item_id', $lastOrderItem[0]->id)->orderBy('id', 'asc')->get();

            $this->assertEquals($flavors[0]->name, 'Bacon');
            $this->assertEquals($flavors[0]->price, 0);

            $this->assertEquals($flavors[1]->name, 'Camarão');
            $this->assertEquals($flavors[1]->price, 9.70);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            $this->assertEquals($lastOrder->total, 88.70);
        /*
        |--------------------------------------------------------------------------
        | Combo da pizza average com refrigerante, borda de 5 reais e sabores
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'combo',
                'qty'   => 1,
                'id'    => 3,
                'comboItems' => [
                    [
                        'type'  => 'pizza',
                        'qty'   => 1,
                        'id'    => 6, 
                        'flavors'=> [
                            'selected' => [
                                ['id'  => 1],
                                ['id'  => 11]
                            ]
                        ],
                        'crust' => ['id' => 1]
                    ],
                ],
                'extras' => [
                    ['id'  => 8,'qty' => 1]
                ]
            ]));

            $response->seeStatusCode(200);

            $crust = \Entities\OrderPizzaCrust::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($crust->name, 'Borda de cheddar');
            $this->assertEquals($crust->price, 5);

            $lastOrderExtra = \Entities\OrderExtra::orderBy('id', 'asc')->get()->last();

            $this->assertEquals($lastOrderExtra->extra_name, 'Schin 1l');
            $this->assertEquals($lastOrderExtra->extra_price, 4);

            $lastOrderItem = \Entities\OrderItem::orderBy('id', 'desc')->limit(2)->get();

            $flavors = \Entities\OrderPizzaFlavor::where('order_item_id', $lastOrderItem[0]->id)->orderBy('id', 'asc')->get();

            $this->assertEquals($flavors[0]->name, 'Bacon');
            $this->assertEquals($flavors[0]->price, 40);

            $this->assertEquals($flavors[1]->name, 'Verduras Especial');
            $this->assertEquals($flavors[1]->price, 60);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            // 89
            $this->assertEquals($lastOrder->total, 89);
    }

    /**
     * @group Order
    */
    public function testMultipleCombosInCart()
    {
        /*
        |--------------------------------------------------------------------------
        | Combo da pizza incremental com refrigerante, preço base, dois combos no
        | carrinho
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([[
                'type'  => 'combo',
                'qty'   => 1,
                'id'    => 1,
                'comboItems' => [
                    [
                        'type'  => 'pizza',
                        'qty'   => 1,
                        'id'    => 4, 
                        'flavors'=> [
                            'selected' => [
                                ['id'  => 1]
                            ]
                        ],
                        'crust' => ['id' => 2]
                    ]
                ],
                'extras' => [
                    ['id'  => 9,'qty' => 1]
                ]
            ], [
                'type'  => 'combo',
                'qty'   => 1,
                'id'    => 1,
                'comboItems' => [
                    [
                        'type'  => 'pizza',
                        'qty'   => 1,
                        'id'    => 4, 
                        'flavors'=> [
                            'selected' => [
                                ['id'  => 1]
                            ]
                        ],
                        'crust' => ['id' => 2]
                    ]
                ],
                'extras' => [
                    ['id'  => 9,'qty' => 1]
                ]
            ]]));

            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            $this->assertEquals($lastOrder->total, 140);
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
