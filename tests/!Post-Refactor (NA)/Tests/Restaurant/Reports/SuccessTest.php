
<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;

class Reports extends TestCase
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
    }

    /**
     * @group Reports
    */
    public function testLastSevenDaysSales()
    {
        $this->restaurantSetup->createRandomDummyOrders(5);

        \Model\Order::where('restaurant_id', $this->restaurant->id)->update([
            'created_at' =>  date("Y-m-d H:i:s", strtotime("yesterday 12:00")) 
        ]);

        $response = $this->json('GET', "restaurant/".$this->restaurant->id.'/reports/last-seven-days-sales');
        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $response = json_decode($response, true);

        //-------------------------

        $weekDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom'];
        $lastSevenWeekDays = []; 

        $days = []; 
        for ($i = 7; $i > 0; $i--) {
            $datetimeObject = (new \DateTime($i.' days ago'));

            $lastSevenWeekDays[] = $datetimeObject->format('Y-m-d');;

            $day = $datetimeObject->format('d/m/Y');
            $day = substr($day, 0, 5);
            $day .= ' (' . $weekDays[$datetimeObject->format('N')] . ')';

            $days[] = $day;
        }

        foreach($response['days'] as $index => $responseDay){
            $this->assertEquals($responseDay, $days[$index]);
        }

        foreach($lastSevenWeekDays as $index => $lastSevenWeekDay){
            $result = \Model\Order::where([
                ['restaurant_id', '=', $this->restaurant->id],
                ['created_at', 'LIKE', $lastSevenWeekDay . '%'],
                ['order_status', '!=', 'CANCELED']
            ])->select(\DB::raw('sum(total_net_value) AS totalSales'))->first();

            $result = round($result['totalSales'], 2);

            $this->assertEquals($result, $response['revenues'][$index]);
        } 
    }

    /**
     * @group Reports
    */
    public function testNewClientsVsOldClients()
    {
        $this->restaurantSetup->createRandomDummyOrders(10, [
            'created_at' =>  "2020-04-01 00:00:00" 
        ]); // Cria 10 pedidos de cliente X

        \Model\Order::where('restaurant_id', $this->restaurant->id)->orderBy('id', 'asc')->take(5)->update([
            'created_at' =>  "2020-03-01 00:00:00" 
        ]); // 5 pedidos do cliente X são colocados para o mês passado

        $this->restaurantSetup->createRandomDummyOrders(5, [
            'created_at' =>  "2020-04-01 00:00:00" 
        ]); // Cria 5 pedidos de cliente Y para este mês

        \Reports\Service::generateReportByGivenMonthAndYearAndSave($this->restaurant->id, '04/2020');
        \Reports\Service::generateReportByGivenMonthAndYearAndSave($this->restaurant->id, '03/2020');

        $response = $this->json('POST', "restaurant/".$this->restaurant->id.'/reports/new-clients-versus-old-clients', [
            'year' => "2020"
        ]);

        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $report = json_decode($response, true);
        // dd($report);
        //-------------------------

        $monthNames = \Reports\Service::$monthNames;

        $pastMonth = 3;
        $pastMonthName = $monthNames[$pastMonth - 1];

        $presentMonth = 4;
        $presentMonthName = $monthNames[$presentMonth - 1];

        $reportPastMonthIndex = array_search($pastMonthName, $report['monthNames']);
        $reportPresentMonthIndex = array_search($presentMonthName, $report['monthNames']);

        //-------------------------

        $this->assertEquals(end($report['oldClients']), 0); // Primeiro mês de vendas do cliente, clientes antigos deve ser igual a zero

        //-------------------------

        $this->assertCount(2, $report['newClients']);
        $this->assertCount(2, $report['oldClients']);
        $this->assertCount(2, $report['monthNames']);

        //-------------------------

        $this->assertEquals($report['newClients'][$reportPresentMonthIndex], 1);
        $this->assertEquals($report['oldClients'][$reportPresentMonthIndex], 1);

        $this->assertEquals($report['newClients'][$reportPastMonthIndex], 1);
        $this->assertEquals($report['oldClients'][$reportPastMonthIndex], 0);

        //-------------------------

        $this->assertCount(1, $report['years']);
        $this->assertEquals($report['years'][0], "2020");
    }

    /**
     * @group Reports
    */
    public function testMonthlyRevenue()
    {
        for ($i = date('m'); $i > 0; $i--) { 
            $this->restaurantSetup->createRandomDummyOrders(10);

            $order = \Model\Order::where('restaurant_id', $this->restaurant->id)->orderBy('id', 'desc')->take(10)->update([
                'created_at' =>  date("Y-$i-d H:i:s") 
            ]);

            $order = \Model\Order::where('restaurant_id', $this->restaurant->id)->orderBy('id', 'desc')->take(10)->get();

            $reportedMonths[] = $i;
        }

        $response = $this->json('GET', "restaurant/".$this->restaurant->id.'/reports/restaurant-monthly-revenue');

        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $reports = json_decode($response, true);

        foreach($reportedMonths as $reportedMonth){
            $this->assertEquals(200, $reports[intval(--$reportedMonth)]);
        }
    }

    /**
     * @group Reports
    */
    public function testCountClients()
    {
        $this->restaurantSetup->createRandomDummyOrders(1);
        $this->restaurantSetup->createRandomDummyOrders(1);
        $this->restaurantSetup->createRandomDummyOrders(1);

        $response = $this->json('GET', "restaurant/".$this->restaurant->id.'/reports/count-clients');
        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $totalOfClients = json_decode($response, true);

        $this->assertEquals($totalOfClients, 3);
    }

    /**
     * @group Reports
    */
    public function testAverageOrders()
    {
        $this->restaurantSetup->createRandomDummyOrders(30);

        $response = $this->json('GET', "restaurant/".$this->restaurant->id.'/reports/average-orders-total');
        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $averageOrder = json_decode($response, true);

        $this->assertEquals($averageOrder, 20);
    }

    /**
     * @group Reports
    */
    public function testEarnings()
    {
        $this->restaurantSetup->createRandomDummyOrders(30);
        $this->restaurantSetup->createRandomDummyOrders(30, [
            'payment_method' => 'creditCard',	
            'delivery_type' =>  'balcony'
        ]);

        $response = $this->json('POST', "restaurant/".$this->restaurant->id.'/reports/earnings', [
            'interval' => '01/01/2000 ->'. date("d/m/Y")
        ]);

        $response->seeStatusCode(200);

        $response = $response->response->getContent();
        $report = json_decode($response, true);

        $this->assertEquals($report['delivery']['count'], 30);
        $this->assertEquals($report['delivery']['total'], 600);
        $this->assertEquals($report['delivery']['revenueCard'], 0);
        $this->assertEquals($report['delivery']['revenueMoney'], 600);
        $this->assertEquals($report['delivery']['deliveryFees'], 150);

        $this->assertEquals($report['balcony']['count'], 30);
        $this->assertEquals($report['balcony']['total'], 600);
        $this->assertEquals($report['balcony']['revenueCard'], 600);
        $this->assertEquals($report['balcony']['revenueMoney'], 0);
    }
}