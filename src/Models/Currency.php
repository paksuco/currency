<?php

namespace Paksuco\Currency\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Paksuco\Currency\Facades\Currency as CurrencyService;
use Paksuco\Currency\Services\Currency as ServicesCurrency;

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

        // $output = "<div class='inline-block'>";
        $output = "";

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

        //$output .= "</div>";

        return trim($output);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where("active", "=", true);
    }

    public function convert($value, $to, $format = false, $override_decimalNumber = null, $when = null)
    {
        if (!($to instanceof Currency)) {
            $to = Currency::where(['currency_code' => $to])->first();
            if (!($to instanceof Currency)) {
                return '%ERROR%';
            }
        }

        $value = $to->from($value, $this, $when);

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

        if ($value < 0.01) {
            return 0;
        }

        if ($when == null) $when = now();

        $oldRate = $currency->rate;
        $rate = CurrencyService::getRateFor($currency, $when);
        if ($oldRate != $rate) {
            $this->refresh();
        }
        $thisRate = CurrencyService::getRateFor($this, $when);

        /*logger("Rates for conversion", [
            "amount" => $value,
            $currency->currency_code => $rate,
            $this->currency_code => $thisRate
        ]);*/

        try {
            return bcmul(bcdiv($value, $rate, 4), $thisRate, 4);
        } catch (Exception $ex) {
            logger()->info(["Value: " . $value, "Rate: " . $rate, "Convert Rate" . $thisRate]);
            throw $ex;
        }
    }
}
