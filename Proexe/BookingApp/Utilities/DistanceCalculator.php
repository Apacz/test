<?php
/**
 * Date: 08/08/2018
 * Time: 16:20
 * @author Artur Bartczak <artur.bartczak@code4.pl>
 */

namespace Proexe\BookingApp\Utilities;


class DistanceCalculator {
    const CONVERTER = [
        'm' => 1000,
        'km' => 1
    ];
    const LAT_INDEX = 0;
    const LNG_INDEX = 1;
	/**
	 * @param array  $from
	 * @param array  $to
	 * @param string $unit - m, km
	 *
	 * @return mixed
	 */
	public function calculate(array $from, array $to, string $unit = 'm'): float
    {
        $pi = pi();
        $x = sin($from[self::LAT_INDEX] * $pi/180) *
            sin($to[self::LAT_INDEX] * $pi/180) +
            cos($from[self::LNG_INDEX] * $pi/180) *
            cos($to[self::LNG_INDEX] * $pi/180) *
            cos(($to[self::LNG_INDEX] * $pi/180) - ($from[self::LNG_INDEX] * $pi/180));
        $x = atan((sqrt(abs(1 - pow($x, 2)))) / $x);
        $distance = abs((1.852 * 60.0 * (($x/$pi) * 180)) * self::CONVERTER[$unit]);

        return $distance;
	}

	/**
	 * @param array $from
	 * @param array $offices
	 *
	 * @return array
	 */
	public function findClosestOffice(array $from, array $offices): array
    {
        $distance = null;
        $closestOffice = null;
	    foreach ($offices as $office) {
	        if (!$distance || $this->calculate($from, $this->convertOfficeToDistande($office)) < $distance) {
                $distance = $this->calculate($from, $this->convertOfficeToDistande($office));
	            $closestOffice = $office;
            }
        }

		return $closestOffice;
	}

    /**
     * @param array $office
     *
     * @return array
     */
	private function convertOfficeToDistande(array $office): array
    {
        return [$office['lat'], $office['lng']];
    }
}