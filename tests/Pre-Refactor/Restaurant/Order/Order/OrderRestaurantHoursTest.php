<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

class RestaurantHoursTest extends TestCase
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
     * @group Hours
    */
    public function testRestaurantHoursInterval()
    {
        $result = \RestaurantHours::isOpen(5, 'sunday', '15:00:00');
        $this->assertFalse($result);

        $result = \RestaurantHours::isOpen(5, 'sunday', '17:00:00');
        $this->assertTrue($result);

        $result = \RestaurantHours::isOpen(5, 'monday', '22:00:00');
        $this->assertTrue($result);

        $result = \RestaurantHours::isOpen(5, 'monday', '16:00:00');
        $this->assertFalse($result);

        $result = \RestaurantHours::isOpen(5, 'tuesday', '02:00:00');
        $this->assertTrue($result);

        $result = \RestaurantHours::isOpen(5, 'wednesday', '02:00:00');
        $this->assertTrue($result);

        $result = \RestaurantHours::isOpen(5, 'wednesday', '13:00:00');
        $this->assertFalse($result);

        $result = \RestaurantHours::isOpen(5, 'tuesday', '16:00:00');
        $this->assertFalse($result);

        $result = \RestaurantHours::isOpen(6, 'sunday', '00:00:00');
        $this->assertFalse($result);

        $result = \RestaurantHours::isOpen(6, 'sunday', '22:30:00');
        $this->assertTrue($result);

        $result = \RestaurantHours::isOpen(6, 'monday', '12:30:00');
        $this->assertTrue($result);

        $result = \RestaurantHours::isOpen(6, 'monday', '22:30:00');
        $this->assertTrue($result);
    }
}
