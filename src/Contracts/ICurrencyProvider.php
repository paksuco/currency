<?php

namespace Paksuco\Currency\Contracts;

use Illuminate\Support\Carbon;

/**
 * All rates should be returned with "USD" as the base.
 * Conversion will be executed later.
 */
interface ICurrencyProvider {
    /**
     * Fetches the latest rates from the service
     *
     * @return array
     */
    public function getLatestRates();

    /**
     * Fetches the historical rates from the service
     *
     * @param Carbon $date Fetching the rates for this date
     *
     * @return array
     */
    public function getHistoricalRates(Carbon $date);

    /**
     * Converts the rates to the specified base currency
     *
     * @param array $rates The rates to convert
     * @param string $base The base currency to convert to
     *
     * @return array
     */
    public function fixRates($rates, $base);
}