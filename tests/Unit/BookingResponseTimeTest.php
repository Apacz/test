<?php

namespace Tests\Unit;

use Proexe\BookingApp\Offices\Interfaces\ResponseTimeCalculatorInterface;
use Proexe\BookingApp\Utilities\ResponseTimeCalculator;
use Tests\TestCase;

class BookingResponseTimeTest extends TestCase
{
    const HOURS = [
        0 => [
            'isClosed' => false,
            'from' => '8:00',
            'to' => '16:00',
        ],
        1 => [
            'isClosed' => false,
            'from' => '8:00',
            'to' => '18:00',
        ],
        2 => [
            'isClosed' => false,
            'from' => '11:00',
            'to' => '15:00',
        ],
        3 => [
            'isClosed' => false,
            'from' => '12:00',
            'to' => '18:00',
        ],
        4 => [
            'isClosed' => true
        ],
        5 => [
            'isClosed' => false,
            'from' => '11:00',
            'to' => '19:00',
        ],
        6 => [
            'isClosed' => false,
            'from' => '8:00',
            'to' => '18:00',
        ],
    ];

    /**
     * @var ResponseTimeCalculatorInterface
     */
    private $responseTimeCalculator;

    public function setUp()
    {
        parent::setUp();
        $this->responseTimeCalculator = new ResponseTimeCalculator();
    }

    public function provider(): array
    {
        return [
            ['2018-01-08 07:03:00', '2018-01-08 11:29:00', [[209, 1]]],
            ['2018-01-08 08:03:00', '2018-01-08 12:29:00', [[266, 1]]],
            ['2018-01-08 08:03:00', '2018-01-08 21:29:00', [[597, 1]]],
            ['2018-01-08 01:03:00', '2018-01-08 23:29:00', [[600, 1]]],
            ['2018-01-11 01:03:00', '2018-01-11 23:29:00', [[0, 4]]],
            ['2018-01-08 17:00:00', '2018-01-09 11:29:00', [[60, 1], [29, 2]]],
            ['2018-01-06 17:00:00', '2018-01-07 11:29:00', [[60, 6], [209, 0]]],
            ['2018-01-10 17:00:00', '2018-01-12 11:29:00', [[60, 3], [0, 4], [29, 5]]],
        ];
    }

    /**
     * @dataProvider provider
     *
     * @test
     */
    public function testTask4($start, $end, $result)
    {
	    $this->assertEquals($result, $this->responseTimeCalculator->calculate($start, $end, self::HOURS));
    }
}
