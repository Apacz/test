<?php
/**
 * Date: 09/08/2018
 * Time: 00:16
 * @author Artur Bartczak <artur.bartczak@code4.pl>
 */

namespace Proexe\BookingApp\Utilities;

use Proexe\BookingApp\Exceptions\ClosedException;
use Proexe\BookingApp\Offices\Interfaces\ResponseTimeCalculatorInterface;

class ResponseTimeCalculator implements ResponseTimeCalculatorInterface
{
    public const DAY_CONVERTER = [
        0 => 'Sun',
        1 => 'Mon',
        2 => 'Tue',
        3 => 'Wed',
        4 => 'Thu',
        5 => 'Fri',
        6 => 'Sat',
    ];

    public function calculate( $bookingDateTime, $responseDateTime, $officeHours )
    {
        $booking = new \DateTime($bookingDateTime);
        $response = new \DateTime($responseDateTime);

        $fromDay = $this->getDayOfWeek($booking);
        $toDay = $this->getDayOfWeek($response);

        if ($fromDay === $toDay) {
            try {
                $booking = $this->getOpeningOrCurrentTime($booking, $officeHours, $fromDay);
                $response = $this->getClosingOrCurrentTime($response, $officeHours, $fromDay);
                return [[$this->calculateMinutes($booking, $response), $fromDay]];
            } catch (ClosedException $closedException) {
                return [[0, $fromDay]];
            }
        }
        $result = [
            [
                $this->calculateMinutes(
                    $booking,
                    $this->setTime($booking, $this->getCloseHourForDay($officeHours, $fromDay))
                ),
                $fromDay
            ]
        ];
        while($fromDay !== $toDay)
        {
            $fromDay++;
            if ($fromDay === 7) {
                $fromDay = 0;
            }
            try {
                $openHours = $this->setTime($response, $this->getOpenHourForDay($officeHours, $fromDay));
                if ($fromDay === $toDay) {
                    $closedHours = $this->getClosingOrCurrentTime($response, $officeHours, $toDay);
                } else {
                    $closedHours = $this->setTime($response, $this->getCloseHourForDay($officeHours, $fromDay));
                }
                $result[] = [$this->calculateMinutes($openHours, $closedHours), $fromDay];
            } catch (ClosedException $closedException) {
                $result[] = [0, $fromDay];
            }
        }

        return $result;
    }

    private function getOpeningOrCurrentTime(\DateTime $dateTime, array $officeHours, int $day): \DateTime
    {
        $secondDateTime = $this->setTime($dateTime, $this->getOpenHourForDay($officeHours, $day));
        if ($secondDateTime > $dateTime) {
            return $secondDateTime;
        } else {
            return $dateTime;
        }
    }

    private function getClosingOrCurrentTime(\DateTime $dateTime, array $officeHours, int $day): \DateTime
    {
        $secondDateTime = $this->setTime($dateTime, $this->getCloseHourForDay($officeHours, $day));
        if ($secondDateTime > $dateTime) {
            return $dateTime;
        } else {
            return $secondDateTime;
        }
    }

    private function setTime(\DateTime $dateTime, string $time): \DateTime
    {
        $newDateTime = clone $dateTime;
        $time = explode(':', $time);
        $newDateTime->setTime($time[0], $time[1]);

        return $newDateTime;
    }

    private function getOpenHourForDay(array $officeHours, int $day): string
    {
        if ($officeHours[$day]['isClosed']) {
            throw new ClosedException();
        }
        return $officeHours[$day]['from'];
    }

    private function getCloseHourForDay(array $officeHours, int $day): string
    {
        if ($officeHours[$day]['isClosed']) {
            throw new ClosedException();
        }
        return $officeHours[$day]['to'];
    }

    private function getDayOfWeek(\DateTime $dateTime): int
    {
        $day = $dateTime->format('N');
        if ((int)$day === 7) {
            return 0;
        }

        return (int)$day;
    }

    private function calculateMinutes(\DateTime $from, \DateTime $to): int
    {
        $interval = $from->diff($to);

        return $interval->h * 60 + $interval->i;
    }
}