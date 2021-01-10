<?php

namespace Paksuco\Currency\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Paksuco\Currency\Facades\Currency as CurrencyService;

class Currency extends Model
{
    protected $table = "currencies";

    public function format($value, $override_decimalNumber = null)
    {
        $symbol = $this->symbol;
        $direction = $this->direction;
        $space = $this->space;

        if (empty($symbol)) {
            $symbol = $this->currency_code;
            $direction = 'L';
            $space = false;
        }

        $output = "<div class='inline-block'>";

        if ($direction == "L") {
            $output .= $symbol;
            if ($space) {
                $output .= " ";
            }
        }

        $output .= number_format($value, $override_decimalNumber ? $override_decimalNumber : $this->decimals);

        if ($direction == "R") {
            if ($space) {
                $output .= " ";
            }
            $output .= $symbol;
        }

        $output .= "</div>";

        return trim($output);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where("active", "=", true);
    }

    public function convert($value, $to, $format = false, $override_decimalNumber = null, $when = null, $roundUp = false)
    {
        if (!($to instanceof Currency)) {
            $to = Currency::where(['currency_code' => $to])->first();
            if (!($to instanceof Currency)) {
                return '%ERROR%';
            }
        }

        $value = $to->from($value, $this, $when, $roundUp);

        if (!$format) {
            return $value;
        }

        return $to->format($value, $override_decimalNumber);
    }

    public function from($value, Currency $currency, $when = null, $roundUp = false)
    {
        if ($currency->id === $this->id) {
            return $value;
        }

        if ($when == null) {
            $rate = $currency->rate;
            $thisRate = $this->rate;
        } else {
            $rate = CurrencyService::getRateFor($currency, $when)->rate;
            $thisRate = CurrencyService::getRateFor($this, $when)->rate;
        }

        $round = ($value / $rate) * $thisRate;
        $round2 = round($round, 4);
        /*if ($round > $round2) {
        $round2 += 1 / pow(10, 4);
        }*/

        if ($roundUp) {
            $round2 = ceil($round2 * 100) / 100;
        }

        return $round2;
    }
}
