<?php

namespace Paksuco\Currency\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CurrencyHistory extends Model
{
    protected $table = "currency_history";

    protected $fillable = [
        "currency_code",
        "base_currency",
        "rate",
        "currency_at"
    ];

    protected $dates = [
        "currency_at"
    ];
}
