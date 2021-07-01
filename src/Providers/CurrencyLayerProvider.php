<?php

namespace Paksuco\Currency\Providers;

use Illuminate\Support\Carbon;
use Paksuco\Currency\Contracts\ICurrencyProvider;

class CurrencyLayerProvider implements ICurrencyProvider
{
    protected $key        = "currencyLayer";

    public function getApiKey()
    {
        return config("currencies.providers.{$this->key}.credentials.api_key", null);
    }

    public function getBaseUrl()
    {
        return "https://api.currencylayer.com/";
    }

    public function getLatestRates()
    {
        $baseUrl          = $this->getBaseUrl();
        $endpoint         = 'live';
        $accessKey        = $this->getApiKey();

        if (empty($accessKey)) {
            logger()->alert("Error: currencyLayer access key is empty.");
            return false;
        }

        $url              = "$baseUrl$endpoint?access_key=$accessKey";
        $exchangeRates    = $this->fetchRemoteJson($url);

        if ($exchangeRates && $exchangeRates["success"]) {
            return $this->fixRates($exchangeRates, config('currencies.base_currency'));
        } else {
            logger()->alert("currencyLayer Error: " . json_encode($exchangeRates));
            return false;
        }
    }

    public function getHistoricalRates(Carbon $date)
    {
        $baseUrl          = $this->getBaseUrl();
        $accessKey        = $this->getApiKey();

        if (empty($accessKey)) {
            logger()->alert("Error: currencyLayer access key is empty.");
            return false;
        }

        if ($date->isValid() === false) {
            logger()->alert("Error: currencyLayer historical rates request: date is not valid (" . var_export($date, true) . ")");
            return false;
        }

        if (now()->isSameDay($date)) {
            return $this->getLatestRates();
        }

        $endpoint         = 'historical';
        $date             = $date->format("Y-m-d");
        $url              = "$baseUrl$endpoint?access_key=$accessKey&date=$date";
        $exchangeRates    = $this->fetchRemoteJson($url);

        if ($exchangeRates && $exchangeRates["success"]) {
            return $this->fixRates($exchangeRates, config('currencies.base_currency'));
        } else {
            logger()->alert("currencyLayer Error: " . json_encode($exchangeRates));
            return false;
        }
    }

    public function fixRates($rates, $base)
    {
        if ($rates['source'] !== $base) {
            $base_rate = $rates['quotes'][$rates['source'] . $base];
            foreach ($rates['quotes'] as $key => $rate) {
                $rates['quotes'][$key] = $rate / $base_rate;
            }
        }
        $newRates = [];
        array_walk($rates['quotes'], function ($value, $key) use (&$newRates, $rates) {
            $newRates[str_replace($rates['source'], '', $key, 1)] = $value;
        });
        return $newRates;
    }

    private function fetchRemoteJson($url)
    {
        $ch               = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json             = curl_exec($ch);
        curl_close($ch);
        return json_decode($json, true);
    }
}
