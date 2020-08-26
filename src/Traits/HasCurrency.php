<?php

namespace Paksuco\Currency\Traits;

use Paksuco\Currency\Models\Currency;

trait HasCurrency
{
    /**
     * Currency and Currency Code fields definitions, needs two fields, the key is
     * for defining the currency amount column, and the value is for defining the
     * currency code column.
     *
     * For example:
     *
     * [
     *      "price" => "price_currency_code"
     * ]
     *
     * @var array
     */
    public $currencyFields = [];

    public function convert($fieldName, $targetCurrency, $formatted = false)
    {
        if (property_exists($this, $fieldName) === false) {
            return null;
        }

        if (property_exists($this, $this->currencyFields[$fieldName]) === false) {
            return $this->$fieldName;
        }

        if (array_key_exists($fieldName, $this->currencyFields) === false) {
            return $this->$fieldName;
        }

        $value = $this->$fieldName;
        $currency = Currency::where([
            "code" => $this->currencyFields[$fieldName],
        ])->first();

        if (!($currency instanceof Currency)) {
            return $this->$fieldName;
        }

        $targetCurrencyModel = Currency::where([
            "code" => $targetCurrency,
        ])->first();

        if (!($targetCurrencyModel instanceof Currency)) {
            return $this->$fieldName;
        }

        $converted = $targetCurrency->from($this->$fieldName, $currency);

        if ($formatted) {
            return $targetCurrencyModel->format($converted);
        }

        return $converted;
    }
}
