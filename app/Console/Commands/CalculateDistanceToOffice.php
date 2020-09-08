<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Proexe\BookingApp\Offices\Models\OfficeModel;
use Proexe\BookingApp\Utilities\DistanceCalculator;

class CalculateDistanceToOffice extends Command {

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bookingApp:calculateDistanceToOffice';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Calculates distance to office';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$distanceCalculator = new DistanceCalculator();
		$offices = OfficeModel::all()->toArray();

		$lat                = $this->ask( 'Enter Lat:', '14.12232322' );
		$lng                = $this->ask( 'Enter Lng:', '8.12232322' );

		foreach ($offices  as $office ) {

			$this->line( 'Distance to ' . $office['name']. ': ' . $distanceCalculator->calculate(
					[ $lat, $lng ],
					[ $office['lat'], $office['lng'] ],
					'km' )
			);
		}

		$this->line( 'Closest office: ' . $distanceCalculator->findClosestOffice( [ $lat, $lng ], $offices )['name'] );
	}
}
