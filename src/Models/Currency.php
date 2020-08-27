<?php

namespace Paksuco\Currency\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = "currencies";

    private function isRTL()
    {
        $rtlChar = '/[\x{0590}-\x{083F}]|[\x{08A0}-\x{08FF}]|[\x{FB1D}-\x{FDFF}]|[\x{FE70}-\x{FEFF}]/u';
        return preg_match($rtlChar, $this->symbol) != 0;
    }

    public function format($value)
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

        $output .= number_format($value, $this->decimals);

        if ($direction == "R") {
            if ($space) {
                $output .= " ";
            }
            $output .= $symbol;
        }

        $output .= "</div>";

        return trim($output);
    }

    public function convert($value, $to, $format = false)
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

        return $to->format($value);
    }

    public function from($value, Currency $currency)
    {
        return ($value / $currency->rate) * $this->rate;
    }
}
