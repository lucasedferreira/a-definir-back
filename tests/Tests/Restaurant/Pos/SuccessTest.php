<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\Cashier as CashierSetup;
use TestSetup\Combo as ComboSetup;
use TestSetup\Size as SizeSetup;
use TestSetup\FlavorCategory as FlavorCategorySetup;
use TestSetup\AdditionalToppings as AdditionalToppingsSetup;
use TestSetup\ExtraCategory as ExtraCategorySetup;
use TestSetup\Extra as ExtraSetup;
use TestSetup\GenericCategory as GenericCategorySetup;
use TestSetup\GenericProduct as GenericProductSetup;
use TestSetup\Order as OrderSetup;
use \Resources\OrderCollection;

class PosSuccess extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    public $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();

        $this->restaurantSetup = new RestaurantSetup();
        $this->restaurant = $this->restaurantSetup->restaurant;

        $this->cashier[] = CashierSetup::createRandomDummyCashier($this->restaurant->id);
        $this->cashier[] = CashierSetup::createRandomDummyCashier($this->restaurant->id, [
            'restaurant_id' => $this->restaurant->id,
            'value' => 10,
            'open' => 1
        ]);

        $this->comboSetup = new ComboSetup($this->restaurant->id);
        $this->combo = $this->comboSetup->combo;

        $this->flavorCategorySetup = new FlavorCategorySetup($this->restaurant->id, true);
        $this->flavorCategory = $this->flavorCategorySetup->flavorCategory;
        $this->flavor = $this->flavorCategorySetup->flavor;

        $this->genericCategorySetup = new GenericCategorySetup($this->restaurant->id);
        $this->genericCategory = $this->genericCategorySetup->genericCategory;
        $this->genericProduct = GenericProductSetup::createRandomDummyProduct($this->restaurant->id, $this->genericCategory->id, 1);

        $this->extraCategorySetup = new ExtraCategorySetup($this->restaurant->id);
        $this->extraCategory = $this->extraCategorySetup->extraCategory;        
        $this->extra = ExtraSetup::createRandomDummyExtra($this->restaurant->id, $this->extraCategory->id);

        $this->toppingSetup = new AdditionalToppingsSetup($this->restaurant->id);
        $this->topping = $this->toppingSetup->topping;

        $this->size = SizeSetup::createRandomDummySize($this->restaurant->id, [] ,['flavorCategory' => $this->flavorCategory], true);

        $order = [
            'restaurant_id' => $this->restaurant->id,
            'cashier_id' => $this->cashier[0]->id
        ];

        $this->order = OrderSetup::createRandomDummyOrders($order);
    }

    /**
     * @group Pos
     * @group Create
    */
    public function testCreatePosOrder()
    {
        ComboSetup::makeComboAssociations($this->combo[2], ["pizzaSize" => $this->size->id, "extraCategory" => $this->extraCategory->id]);
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pos",
        [
            "user" =>
            [
                "name" => "Patrick",
                "phone" => 4777774577,
                "street" => "Rua do bob esponja",
                "street_number" => 0,
                "complemento" => "É literalmente uma pedra"
            ],
            "orders" =>
            [
                [
                    "type" => "combo",
                    "avilable" => 1,
                    "id" => $this->combo[2]->id,
                    "qty" => 1,
                    "comboItems" =>
                    [
                        [
                            "available" => 1,
                            "description" => "Pizza de Siri",
                            "id" => $this->size->id,
                            "type" => "pizza",
                            "priceBehavior" => "incremental",
                            "name" => "Pizza Do Siri Cascudo",
                            "hasAdditionalToppings" => 1,
                            "flavors" => 
                            [
                                "selected" =>
                                [
                                    [
                                        "id" => $this->flavor->id,
                                        "name" => "Siri",
                                        "price" => "0",
                                        "description" => "Apple?",
                                        "additionalToppings" =>
                                        [
                                            [
                                                "available" => 1,
                                                "id" => $this->topping->id,
                                                "name" => "Mexilhão",
                                                "price" => 2
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "extras" =>
                    [
                        [
                            "id" => $this->extra->id,
                            "available" => 1,
                            "name" => "Molho tártaro",
                            "extraID" => $this->extraCategory->id,
                            "qty" => 1
                        ]
                    ]
                ],
                [
                    "id" => $this->genericProduct->id,
                    "type" => "general",
                    "avilable" => 1,
                    "category_id" => $this->genericCategory->id,
                    "name" => "Suco de algas",
                    "price" => 10,
                    "order" => 0,
                    "qty" => 1,
                    "extras" =>
                    [
                        [
                            "id" => $this->extra->id,
                            "available" => 1,
                            "name" => "Fritas",
                            "extraID" => $this->extraCategory->id,
                            "qty" => 1
                        ]
                    ]
                ]
            ],
            "details" => 
            [
                "deliveryType" => "balcony",
                "source" => "pos",
                "payments" =>
                [
                    [
                        "id" => 1,
                        "name" => "pegador 1",
                        "paymentType" => "money",
                        "value" => 2
                    ],
                    [
                        "id" => 2,
                        "name" => "pegador 2",
                        "paymentType" => "creditCard",
                        "value" => 1,
                        "cardType" => "BancoDoSiri"
                    ]
                ] 
            ]
        ]);
        $response->seeStatusCode(200);
        $cashierResponse = json_decode($response->response->getContent());

        $this->assertEquals($cashierResponse->status, 'CREATED');
        $this->assertEquals($cashierResponse->source, "pos");
        $this->assertEquals($cashierResponse->deliveryType, "balcony");

        $this->assertEquals($cashierResponse->client->name, 'Patrick');
        $this->assertEquals($cashierResponse->client->phone, 4777774577);
        $this->assertEquals($cashierResponse->client->street, "Rua do bob esponja");
        $this->assertEquals($cashierResponse->client->street_number, 0);
        $this->assertEquals($cashierResponse->client->complemento, "É literalmente uma pedra");

        $this->assertEquals($cashierResponse->payments[0]->order_id, $cashierResponse->id);
        $this->assertEquals($cashierResponse->payments[0]->value, 2);
        $this->assertEquals($cashierResponse->payments[0]->payment_method, "money");
        $this->assertEquals($cashierResponse->payments[1]->order_id, $cashierResponse->id);
        $this->assertEquals($cashierResponse->payments[1]->value, 1);
        $this->assertEquals($cashierResponse->payments[1]->payment_method, "creditCard");
        $this->assertEquals($cashierResponse->payments[1]->card_type, "BancoDoSiri");

        $this->assertEquals($cashierResponse->items[0]->name, 'Pizza e suco de algas');
        $this->assertEquals($cashierResponse->items[0]->price, 45);
        $this->assertEquals($cashierResponse->items[0]->type, "combo");

        $this->assertEquals($cashierResponse->items[0]->extras[0]->name, "Salmão");
        $this->assertEquals($cashierResponse->items[0]->extras[0]->quantity, 1);

        $this->assertEquals($cashierResponse->items[0]->comboItems[0]->name, "Grande");
        $this->assertEquals($cashierResponse->items[0]->comboItems[0]->type, "combo_pizza_item");
        $this->assertEquals($cashierResponse->items[0]->comboItems[0]->priceBehavior, "incremental");
        $this->assertEquals($cashierResponse->items[0]->comboItems[0]->flavors[0]->name, "Calabresa");
        $this->assertEquals($cashierResponse->items[0]->comboItems[0]->flavors[0]->quantity, 1);
        $this->assertEquals($cashierResponse->items[0]->comboItems[0]->flavors[0]->additionalToppings[0]->name, "Fórmula Secreta");
        $this->assertEquals($cashierResponse->items[0]->comboItems[0]->flavors[0]->additionalToppings[0]->price, 9.99);
    }

    /**
     * @group Pos
     * @group Update
    */
    public function testUpdatePosOrder()
    {
        $orderCollection = new OrderCollection(\Order\Repository::getByID($this->order->id));
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pos",
        [
            "id" => $this->order->id,
            "user" =>
            [
                "name" => "Patrick Estrela",
                "phone" => $this->order->phone,
            ],
            "orders" =>
            [
                [
                    "id" => $this->genericProduct->id,
                    "type" => "general",
                    "avilable" => 1,
                    "category_id" => $this->genericCategory->id,
                    "qty" => 1,
                    "extras" =>
                    [
                        [
                            "id" => $this->extra->id,
                            "available" => 1,
                            "name" => "Fritas",
                            "extraID" => $this->extraCategory->id,
                            "qty" => 1
                        ]
                    ]
                ],
                [
                    "id" => $this->genericProduct->id,
                    "type" => "general",
                    "avilable" => 1,
                    "category_id" => $this->genericCategory->id,
                    "qty" => 1,
                    "extras" =>
                    [
                        [
                            "id" => $this->extra->id,
                            "available" => 1,
                            "name" => "Fritas",
                            "extraID" => $this->extraCategory->id,
                            "qty" => 1
                        ]
                    ]
                ]

            ],
            "removedItems" =>
            [
                $orderCollection->items[0]->id
            ],
            "details" => 
            [
                "deliveryType" => "balcony",
                "source" => "pos",
                "status" => "APPROVED",
                "payments" =>
                [
                    [
                        "id" => 1,
                        "name" => "pegador 1",
                        "paymentType" => "money",
                        "value" => 2
                    ],
                    [
                        "id" => 2,
                        "name" => "pegador 2",
                        "paymentType" => "creditCard",
                        "value" => 1,
                        "cardType" => "BancoDoSiri"
                    ]
                ] 
            ]
        ]);
        $response->seeStatusCode(200);
        $cashierResponse = json_decode($response->response->getContent());

        $this->assertEquals($cashierResponse->status, 'APPROVED');
        $this->assertEquals($cashierResponse->source, "pos");
        $this->assertEquals($cashierResponse->deliveryType, "balcony");

        $this->assertEquals($cashierResponse->client->name, 'Patrick Estrela');
        $this->assertEquals($cashierResponse->client->phone, $this->order->phone);

        $this->assertEquals(count($cashierResponse->items), 2);

        $this->assertEquals($cashierResponse->payments[0]->order_id, $cashierResponse->id);
        $this->assertEquals($cashierResponse->payments[0]->value, 2);
        $this->assertEquals($cashierResponse->payments[0]->payment_method, "money");
        $this->assertEquals($cashierResponse->payments[1]->order_id, $cashierResponse->id);
        $this->assertEquals($cashierResponse->payments[1]->value, 1);
        $this->assertEquals($cashierResponse->payments[1]->payment_method, "creditCard");
        $this->assertEquals($cashierResponse->payments[1]->card_type, "BancoDoSiri");
    }
}