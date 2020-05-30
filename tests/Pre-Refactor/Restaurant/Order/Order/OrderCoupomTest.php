<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class OrderCoupomTest extends TestCase
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
     * @group Coupom
    */
    public function testNewOrderWithCoupom__COUPOM_FIXED()
    {
        /*
        |--------------------------------------------------------------------------
        | Pedido com cupom de valor fixo de 10, mínimo de 10 
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 3,
                'id'    => 3
            ], 1));
            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->coupom_type, 'fixDiscount');
            $this->assertEquals($lastOrder->coupom_code, 'COUPOM-FIXED');
            $this->assertEquals($lastOrder->discount_value, 10);

            $this->assertEquals($lastOrder->total - $lastOrder->discount_value, 21 - 10);
    }

    /**
     * @group Order
     * @group Coupom
    */
    public function testNewOrderWithCoupom__COUPOM_PERCENT()
    {
        /*
        |--------------------------------------------------------------------------
        | Pedido com cupom de valor de 10% total do pedido, mínimo de 10 
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 3,
                'id'    => 3
            ], 2));
            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->coupom_type, 'percentDiscount');
            $this->assertEquals($lastOrder->coupom_code, 'COUPOM-PERCENT');
            $this->assertEquals($lastOrder->discount_value, 2.1);

            $this->assertEquals($lastOrder->total - $lastOrder->discount_value, 21 - 2.1);
    }

    /**
     * @group Order
     * @group Coupom
    */
    public function testNewOrderWithCoupom__COUPOM_DELIVERY()
    {
        /*
        |--------------------------------------------------------------------------
        | Pedido com cupom de valor de 10% total do pedido, mínimo de 10 
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 3,
                'id'    => 3
            ], 3, 'delivery'));
            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->coupom_type, 'freeDelivery');
            $this->assertEquals($lastOrder->coupom_code, 'COUPOM-FREE-DELIVERY');
            $this->assertEquals($lastOrder->discount_value, 6);

            $this->assertEquals($lastOrder->total - $lastOrder->discount_value, 21 - 6);
    }

    /**
     * @group Order
     * @group Coupom
    */
    public function testNewOrderWithCoupom__COUPOM_PRODUCT()
    {
        /*
        |--------------------------------------------------------------------------
        | Pedido com cupom de valor de 10% total do pedido, mínimo de 10 
        |--------------------------------------------------------------------------
        |
        */
            $response = $this->json('POST', 'restaurant/1/order', $this->createOrderShell([
                'type'  => 'general',
                'qty'   => 3,
                'id'    => 3
            ], 4));
            $response->seeStatusCode(200);

            $lastOrder = \Entities\Order::orderBy('id', 'desc')->first();

            $this->assertEquals($lastOrder->coupom_type, 'freeProduct');
            $this->assertEquals($lastOrder->coupom_code, 'COUPOM-PRODUCT');
            $this->assertEquals($lastOrder->coupom_free_product, 'X-Salada');
            $this->assertEquals($lastOrder->discount_value, 0);

            $this->assertEquals($lastOrder->total, 21);
    }

    public function createOrderShell($orders, $discountID, $deliveryType = 'balcony', $paymentType = 'creditCard', $needChange = false, $changeValue = 0)
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
                'changeValue'   => $changeValue,
                'discountID'    => $discountID
            ],
            'orders' => $orders
        ];
    }
}
