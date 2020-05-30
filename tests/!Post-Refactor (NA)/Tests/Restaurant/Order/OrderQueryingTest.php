
<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;

class OrderQuerying extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    public $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();

        $this->restaurantSetup = new RestaurantSetup();
        $this->restaurantSetup->setPosModule();
        $this->restaurantSetup->toggleCashier();
        $this->restaurantSetup->createRandomDummyOrders(5);

        $this->restaurant = $this->restaurantSetup->restaurant;
    }

    /**
     * @group Order
     * @group OrderQuerying
    */
    public function testCountOrders()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id.'/order/count');
        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $response = json_decode($response, true);

        $numberOfOrders = \Model\Order::where('restaurant_id', $this->restaurant->id)->count();

        $this->assertEquals($numberOfOrders, $response);
    }

    /**
     * @group Order
     * @group OrderQuerying
     * @group Pos
    */
    public function testCurrentCashierOrders()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id.'/order/query', [
            'columnsTerms' => [
                'cashierID' => 'open'
            ]
        ]);
        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $orders = json_decode($response, true);

        $numberOfOrders = \Model\Order::where('restaurant_id', $this->restaurant->id)->count();
        $this->assertEquals($numberOfOrders, sizeof($orders));

        $currentCashier = \Cashier\Repository::getCurrentCashier($this->restaurant->id);
        foreach($orders as $order){
            $this->assertEquals($order['cashierID'], $currentCashier->id);
        }
    }

    /**
     * @group Order
     * @group OrderQuerying
    */
    public function testFirstOrder()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id.'/order/query/first', [
            'sortSettings' => ['id' => 'ASC']
        ]);

        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $responseOrder = json_decode($response, true);

        $firstOrder = \Model\Order::where('restaurant_id', $this->restaurant->id)->orderBy('id', 'ASC')->first();

        $this->assertEquals($firstOrder->id, $responseOrder['id']);
    }

    /**
     * @group Order
     * @group OrderQuerying
    */
    public function testMissedToday()
    {
        $orders = \Model\Order::where('restaurant_id', $this->restaurant->id)->get();
        $oldOrdersIDs = $orders->map(function($order){
            return $order->id;
        })->toArray();

        $this->restaurantSetup->createRandomDummyOrders(5);

        $response = $this->json('POST', "restaurant/".$this->restaurant->id.'/order/query/', [
            'columnsTerms' => [
                'createdAt' => 'today',
                ['id', 'NOT IN', $oldOrdersIDs]
            ]
        ]);

        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $responseOrders = json_decode($response, true);
        $responseOrdersCollection = collect($responseOrders);
        $responseOrdersCollectionIDs = $responseOrdersCollection->map(function($order){
            return $order['id'];
        })->toArray();
        
        $this->assertCount(5, $responseOrdersCollectionIDs);
        $this->assertNotEquals($responseOrdersCollectionIDs, $oldOrdersIDs);

        $responseOrdersCollectionDates = $responseOrdersCollection->map(function($order){
            return $order['createdAt'];
        })->toArray();

        foreach($responseOrdersCollectionDates as $timestamp){
            $this->assertTrue(date('Ymd') == date('Ymd', strtotime($timestamp)));
        }
    }

    /**
     * @group Order
     * @group OrderQuerying
    */
    public function testMissedCurrentCashier()
    {
        $orders = \Model\Order::where('restaurant_id', $this->restaurant->id)->get();
        $oldOrdersIDs = $orders->map(function($order){
            return $order->id;
        })->toArray();

        $this->restaurantSetup->createRandomDummyOrders(5);

        $response = $this->json('POST', "restaurant/".$this->restaurant->id.'/order/query/', [
            'columnsTerms' => [
                'cashierID' => 'open',
                ['id', 'NOT IN', $oldOrdersIDs]
            ]
        ]);

        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $responseOrders = json_decode($response, true);
        $responseOrdersCollection = collect($responseOrders);
        $responseOrdersCollectionIDs = $responseOrdersCollection->map(function($order){
            return $order['id'];
        })->toArray();
        
        $this->assertCount(5, $responseOrdersCollectionIDs);
        $this->assertNotEquals($responseOrdersCollectionIDs, $oldOrdersIDs);

        $responseOrdersCollectionCashierID = $responseOrdersCollection->map(function($order){
            return $order['cashierID'];
        })->toArray();

        $currentCashier = \Cashier\Repository::getCurrentCashier($this->restaurant->id);
        foreach($responseOrdersCollectionCashierID as $cashierID){
            $this->assertEquals($cashierID, $currentCashier->id);
        }
    }

    /**
     * @group Order
     * @group OrderQuerying
    */
    public function testGetTodayOrders()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id.'/order/query/', [
            'columnsTerms' => [
                "createdAt" => "today"
            ]
        ]);

        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $responseOrders = json_decode($response, true);
        $responseOrdersCollection = collect($responseOrders);

        $responseOrdersCollectionDates = $responseOrdersCollection->map(function($order){
            return $order['createdAt'];
        })->toArray();

        foreach($responseOrdersCollectionDates as $timestamp){
            $this->assertTrue(date('Ymd') == date('Ymd', strtotime($timestamp)));
        }
    }
}