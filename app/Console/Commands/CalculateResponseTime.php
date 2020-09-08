<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Proexe\BookingApp\Bookings\Models\BookingModel;
use Proexe\BookingApp\Offices\Interfaces\ResponseTimeCalculatorInterface;
use Proexe\BookingApp\Utilities\ResponseTimeCalculator;

class CalculateResponseTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookingApp:calculateResponseTime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates response time';

    /**
     * @var ResponseTimeCalculatorInterface
     */
    private $responseTimeCalculator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
//    public function __construct(ResponseTimeCalculatorInterface $responseTimeCalculator)
    public function __construct()
    {
        parent::__construct();
        $this->responseTimeCalculator = new ResponseTimeCalculator();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $bookings = BookingModel::with('office')->get()->toArray();
        foreach ($bookings as $booking) {
            $result = $this->responseTimeCalculator->calculate(
                $booking['created_at'],
                $booking['updated_at'],
                $booking['office']['office_hours']
            );
            $this->writeOutput($booking['created_at'], $booking['updated_at'], $result);
        }
    }

    private function writeOutput(string $createdAt, string $updatedAt, array $result)
    {
        $string = 'created_at: ' . $createdAt . "\nupdated_at " . $updatedAt . "\n";
        $output = [];
        foreach ($result as $day) {
            $output[] = $day[0] . ' minutes on ' . ResponseTimeCalculator::DAY_CONVERTER[$day[1]];
        }
        $this->line($string . implode(' + ', $output));
    }
}
