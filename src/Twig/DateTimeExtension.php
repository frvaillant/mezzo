<?php

namespace App\Twig;

use App\Service\DateTimeService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DateTimeExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('today', [$this, 'today']),
            new TwigFunction('timer', [$this, 'timer'], ['is_safe' => ['html']]),
            ];
    }


    public function today()
    {
        return DateTimeService::getToday();
    }

    public function timer()
    {
        $today = new \DateTime();
        $html = '<span data-controller="timer">%s</span>';
        return sprintf($html, $today->format('H:i'));
    }


}
