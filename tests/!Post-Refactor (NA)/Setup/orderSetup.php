<?php
namespace TestSetup;

class Order
{
    public static function createRandomDummyOrders($order, $numberOfOrders = 1)
    {
        $client = (new Client($order['restaurant_id']))->client;

        $order = array_merge([
            'client_id'	    => $client->id,

            'phone'	=> $client->phone,
            'name'	=> $client->name,

            'order_status' => 'APPROVED',
            'total'	=> 15,
            'delivery_fee'	=> 5,

            'bairro'	    => $client->bairro,
            'complemento'	=> $client->complemento,
            'address'	    => $client->street,
            'street_number'	=> $client->street_number
        ], $order);

        $orders = factory(\Model\Order::class, $numberOfOrders)->create($order)->each(function ($order) {
            $order->items()->save(factory(\Model\OrderItem::class)->make(['menu_price' => 15]));
        });

        if(sizeof($orders) == 1) return $orders[0];

        return $orders;
    }

    public function insertCombo($restaurantID, $orderID)
    {
        return DB::table('order_items')->insert([
            'restaurant_id' => $restaurantID,
            'order_id' => $orderID,

            'menu_name' => 'Combo Teste',
            'menu_price' => 15.00,
            'type' => 'combo',
            'quantity' => 1,
            'is_combo' => 1,
            'item_sub_total' => 15.00
        ]);
    }
}