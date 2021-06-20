<?php

namespace Paksuco\Currency\Contracts;

use Illuminate\Support\Carbon;

interface ICurrencyProvider {
    public function getLatestRates();
    public function getHistoricalRates(Carbon $date);
}