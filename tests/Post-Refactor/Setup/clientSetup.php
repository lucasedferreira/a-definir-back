<?php
namespace TestSetup;

class Client
{
    public $client;

    public function __construct($restaurantID, $number_of_clients = 1, $client = [])
    {
        $this->client = self::createRandomDummyClient($restaurantID, $number_of_clients, $client);
    }

    public static function createRandomDummyClient($restaurantID, $number_of_clients = 1, $client = [])
    {
        $client = array_merge([
            'name' => 'Patrick Estrela',
            'phone' => '40028922',
            'restaurant_id' => $restaurantID
        ], $client);

        $clients = factory(\Model\Client::class, $number_of_clients)->create($client);

        if(sizeof($clients) == 1) return $clients[0];

        return $clients;
    }
}
