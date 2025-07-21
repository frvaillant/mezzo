<?php

namespace App\Service;

class DateTimeService
{


    public static function getToday(): string
    {
        $today = new \DateTime();
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            'Europe/Paris',
            \IntlDateFormatter::GREGORIAN,
            'EEEE d MMMM y'
        );

        return ucfirst($formatter->format($today));
    }

}
