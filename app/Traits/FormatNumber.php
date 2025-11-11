<?php

namespace App\Traits;

use Illuminate\Support\Number;
use Carbon\Carbon;

trait FormatNumber
{
    /**
     * Format a number with precision, safely handling null values.
     */
    public function formatWithPrecision($number, $comma = true)
    {
        $number = is_numeric($number) ? (float) $number : 0;

        if ($comma) {
            return Number::format($number, app('company')['number_precision']);
        } else {
            return str_replace(',', '', Number::format($number, app('company')['number_precision']));
        }
    }

    /**
     * Format a quantity safely (no commas).
     */
    public function formatQuantity($number)
    {
        $number = is_numeric($number) ? (float) $number : 0;

        return str_replace(',', '', Number::format($number, app('company')['quantity_precision']));
    }

    /**
     * Spell out a number in words safely.
     */
    public function spell($number)
    {
        $number = is_numeric($number) ? (float) $number : 0;

        return Number::spell($number);
    }
}
