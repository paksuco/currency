<?php

namespace Paksuco\Currency\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = "currencies";

    public function format($value)
    {
        $output = "<div class='inline-block'>";

        if ($this->direction == "L") {
            $output .= $this->symbol;
            if ($this->space) {
                $output .= " ";
            }
        }

        $output .= number_format($value, $this->decimals);

        if ($this->direction == "R") {
            if ($this->space) {
                $output .= " ";
            }
            $output .= $this->symbol;
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
