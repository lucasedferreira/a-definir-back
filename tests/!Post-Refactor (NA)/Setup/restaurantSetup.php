<?php
namespace TestSetup;

class Restaurant
{
    public $restaurant;
    public $cashier;

    public function __construct()
    {
        $this->restaurant = factory(\Model\Restaurant::class)->create([
            'name' => 'Siri Cascudo',
            'phone' => '55047988489411',
            'restaurant_url' => 'testrestaurant',
            'street' => 'Benjamin Moacir dos Santos',
            'street_number'=> '09',
            'bairro' => 'Fenda do Biquini',
            'city' => 'Joinville',
            'uf' => 'SC'
        ]);

        factory(\Model\RestaurantModules::class)->create([
            'restaurant_id' => $this->restaurant->id
        ]);
    }

    public function associateWithUser($user)
    {
        $this->restaurant->users()->saveMany([$user]);
    }

    public function setPosModule($state = true)
    {
        $this->restaurant->modules()->update([
            'pos' => $state
        ]);
    }

    public function toggleCashier()
    {
        $this->cashier = \Cashier\Repository::create([
            'restaurant_id' => $this->restaurant->id,
            'open'          => true
        ]);
    }

    public function createRandomDummyOrders($numberOfOrders, $order = null, $cashierID = null)
    {
        if($this->cashier) $cashierID = $this->cashier->id;


        $baseOrder = [
            'restaurant_id' => $this->restaurant->id,
            'cashier_id' => $cashierID
        ];

        if($order) $baseOrder = array_merge($baseOrder, $order);

        Order::createRandomDummyOrders($baseOrder, $numberOfOrders);
    }
}