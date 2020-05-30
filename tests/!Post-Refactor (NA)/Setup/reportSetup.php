<?php
namespace TestSetup;

class Reposrt
{

    public $orderSubItemModels = ['OrderPizzaCrust', 'OrderPizzaDough', 'OrderPizzaFlavor', 'OrderPizzaAdditionalToppings', 'OrderExtra'];

    public function specificOrderItemReport($restaurantSetup)
    {
        $restaurantSetup->createRandomDummyOrders(5);
        // $restaurantSetup->restaurant->id
        // $order->id

        $orders = DB::table('orders')->where('restaurant_id', $restaurantSetup->restaurant->id)->get();

        foreach($orders as $order){
            DB::table('order_items')->where('order_id', $order->id)->delete();


        }

    }

    public function specificOrderSubItemReport()
    {
    }
}