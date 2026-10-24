<?php

namespace App\Support;

use Carbon\CarbonInterface;

final class PhilippineTime
{
    public static function format(CarbonInterface $timestamp, bool $compact = false): string
    {
        $format = $compact ? 'M j, Y – g:i A' : 'F j, Y – g:i A';

        return $timestamp->copy()
            ->setTimezone(config('app.display_timezone', 'Asia/Manila'))
            ->format($format);
    }

    public static function date(CarbonInterface $timestamp, string $format = 'M j, Y'): string
    {
        return $timestamp->copy()
            ->setTimezone(config('app.display_timezone', 'Asia/Manila'))
            ->format($format);
    }
}
