<?php

namespace Paksuco\Currency\Contracts;

use Illuminate\Support\Carbon;

/**
 * All rates should be returned with "USD" as the base.
 * Conversion will be executed later.
 */
interface ICurrencyProvider {
    public function getLatestRates();
    public function getHistoricalRates(Carbon $date);
}