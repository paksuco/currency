<?php

namespace Paksuco\Currency\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = "currencies";

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where("active", "=", 1);
        });
    }

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

    public function convert($value, $to, $format = false, $override_decimalNumber = null)
    {
        if (!($to instanceof Currency)) {
            $to = Currency::where(['currency_code' => $to])->first();
            if (!($to instanceof Currency)) {
                return '%ERROR%';
            }
        }

        $value = $to->from($value, $this);

        if (!$format) {
            return $value;
        }

        return $to->format($value, $override_decimalNumber);
    }

    public function from($value, Currency $currency)
    {
        $round = ($value / $currency->rate) * $this->rate;
        $round2 = round($round, 4);
        if ($round > $round2) {
            $round2 += 1 / pow(10, 4);
        }

        return $round2;
    }

    public function fromId($value, $id)
    {
        $currency = Currency::find($id);
        if ($currency == null) {
            throw new \Exception("Currency not found.");
        }
        $round = ($value / $currency->rate) * $this->rate;
        $round2 = round($round, 4);
        if ($round > $round2) {
            $round2 += 1 / pow(10, 4);
        }

        return $round2;
    }

    public function fromCode($value, string $code)
    {
        $currency = Currency::where("currency_code", "=", $code)->first();
        if ($currency == null) {
            throw new \Exception("Currency not found.");
        }
        $round = ($value / $currency->rate) * $this->rate;
        $round2 = round($round, 4);
        if ($round > $round2) {
            $round2 += 1 / pow(10, 4);
        }

        return $round2;
    }
}
