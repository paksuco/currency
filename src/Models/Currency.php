<?php

namespace Paksuco\Currency\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = "currencies";

    public function format($value)
    {
        $output = "";
        if ($this->direction == "L") {
            $output .= $this->symbol;
        }

        if ($this->space) {
            $output .= " ";
        }

        $output .= number_format($value, $this->decimals);
        if ($this->space) {
            $output .= " ";
        }

        if ($this->direction == "R") {
            $output .= $this->symbol;
        }

        return trim($output);
    }

    public function from($value, Currency $currency)
    {
        return ($value / $currency->rate) * $this->rate;
    }
}
