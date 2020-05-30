<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class OrderAdditionalToppingTest extends TestCase
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
     * @group Order_Pizza_Additional_Toppings
    */
    public function testNewPizzaIncrementalWithAdditionalToppings()
    {
        // $this->markTestSkipped('Teste imcompleto');
        /*
        |--------------------------------------------------------------------------
        | Pizza Incremental sem borda com sabor não especial e Adicionais
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'pizza',
                'qty'   => 1,
                'id'    => 1, 
                'flavors'=> [
                    'selected' => [
                        [
                            'id'  => 1,
                            'additionalToppings' => [
                                ['id'  => 1],
                                ['id'  => 2],
                                ['id'  => 3]
                            ]
                        ]
                    ]
                ],
                'crust' => ['id' => 2]
            ]));

            $response->seeStatusCode(200);

            $additionalToppings_MENU = \Entities\AdditionalToppings::all();
            $additionalToppings_ORDER = \Entities\OrderPizzaAdditionalToppings::orderBy('id', 'asc')->get();

            $totalToppings = 0;

            foreach($additionalToppings_MENU as $index => $additionalTopping_MENU){
                $this->assertEquals($additionalTopping_MENU->name, $additionalToppings_ORDER[$index]->name);
                $this->assertEquals($additionalTopping_MENU->price, $additionalToppings_ORDER[$index]->price);

                $totalToppings += $additionalToppings_ORDER[$index]->price; 
            }

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->first();

            $this->assertEquals($lastOrder->total, 40 + $totalToppings);
    }

    /**
     * @group Order
     * @group Order_Combo
     * @group Order_Pizza_Additional_Toppings
    */
    public function testNewComboWithAdditionalToppings()
    {
        $additionalToppings_MENU = \Entities\AdditionalToppings::all();

        $totalToppings = 0;
        foreach($additionalToppings_MENU as $index => $additionalTopping_MENU){
            $totalToppings += $additionalTopping_MENU->price; 
        }

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
                                [
                                    'id'  => 1,
                                    'additionalToppings' => [
                                        ['id'  => 1],
                                        ['id'  => 2],
                                        ['id'  => 3]
                                    ]
                                ]
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

            $additionalToppings_ORDER = \Entities\OrderPizzaAdditionalToppings::orderBy('id', 'asc')->get();

            foreach($additionalToppings_MENU as $index => $additionalTopping_MENU){
                $this->assertEquals($additionalTopping_MENU->name, $additionalToppings_ORDER[$index]->name);
                $this->assertEquals($additionalTopping_MENU->price, $additionalToppings_ORDER[$index]->price);
            }

            $pizzaCombo = \Entities\OrderItem::orderBy('id', 'desc')->limit(2)->get()[0];
            $this->assertEquals($pizzaCombo->item_sub_total, 40 + $totalToppings);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            $this->assertEquals($lastOrder->total, 70 + $totalToppings);
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
                                [
                                    'id'  => 1,
                                    'additionalToppings' => [
                                        ['id'  => 1],
                                        ['id'  => 2],
                                        ['id'  => 3]
                                    ]
                                ]
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

            $pizzaCombo = \Entities\OrderItem::orderBy('id', 'desc')->limit(1)->get()[0];
            $this->assertEquals($pizzaCombo->item_sub_total, 40 + 5 + $totalToppings);

            $lastOrder = \Entities\Order::orderBy('id', 'asc')->get()->last();
            $this->assertEquals($lastOrder->total, 79 + $totalToppings);
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
                                [
                                    'id'  => 12 , 
                                    'additionalToppings' => [
                                        ['id'  => 1],
                                        ['id'  => 2],
                                        ['id'  => 3]
                                    ]
                                ]
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

            $pizzaCombo = \Entities\OrderItem::orderBy('id', 'desc')->limit(1)->get()[0];
            $this->assertEquals($pizzaCombo->item_sub_total, 40 + 9.70 + 5 + $totalToppings);

            // $lastOrder = \Entities\Order::orderBy('id', 'desc')->limit(1)->get()[0];
            // $this->assertEquals($lastOrder->total, ($pizzaCombo->item_sub_total - $pizzaCombo->menu_price) + 4 + 70 );
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
                                [
                                    'id'  => 11,
                                    'additionalToppings' => [
                                        ['id'  => 1],
                                        ['id'  => 2],
                                        ['id'  => 3]
                                    ]
                                ]
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

            $pizzaCombo = \Entities\OrderItem::orderBy('id', 'desc')->limit(1)->get()[0];
            $this->assertEquals($pizzaCombo->item_sub_total, ((60 + 40) / 2) + 5 + $totalToppings);

            // $lastOrder = \Entities\Order::orderBy('id', 'desc')->limit(1)->get()[0];
            // $this->assertEquals($lastOrder->total, ($pizzaCombo->item_sub_total - $pizzaCombo->menu_price) + 4 + 70 );
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
