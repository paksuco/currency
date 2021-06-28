<?php

namespace Paksuco\Currency\Providers;

use Illuminate\Support\Carbon;
use Paksuco\Currency\Contracts\ICurrencyProvider;

class FixerIoProvider implements ICurrencyProvider
{
    protected $key        = "fixerio";

    public function getApiKey()
    {
        return config("currencies.providers.{$this->key}.credentials.api_key", null);
    }

    public function getBaseUrl()
    {
        return "http://data.fixer.io/api/";
    }

    public function getLatestRates()
    {
        $baseUrl          = $this->getBaseUrl();
        $endpoint         = 'latest';
        $accessKey        = $this->getApiKey();

        if (empty($accessKey)) {
            logger()->alert("Error: fixer.io access key is empty.");
            return false;
        }

        $url              = "$baseUrl$endpoint?access_key=$accessKey";
        $exchangeRates    = $this->fetchRemoteJson($url);

        if ($exchangeRates && $exchangeRates["success"]) {
            return $this->fixRates($exchangeRates, config('currencies.base_currency'));
        } else {
            logger()->alert("Fixer IO Error: " . json_encode($exchangeRates));
            return false;
        }
    }

    public function getHistoricalRates(Carbon $date)
    {
        $baseUrl          = $this->getBaseUrl();
        $accessKey        = $this->getApiKey();

        if (empty($accessKey)) {
            logger()->alert("Error: fixer.io access key is empty.");
            return false;
        }

        if ($date->isValid() === false) {
            logger()->alert("Error: fixer.io historical rates request: date is not valid (" . var_export($date, true) . ")");
            return false;
        }

        if (now()->isSameDay($date)) {
            return $this->getLatestRates();
        }

        $endpoint         = $date->format("Y-m-d");
        $url              = "$baseUrl$endpoint?access_key=$accessKey";
        $exchangeRates    = $this->fetchRemoteJson($url);

        if ($exchangeRates && $exchangeRates["success"]) {
            return $this->fixRates($exchangeRates, config('currencies.base_currency'));
        } else {
            logger()->alert("Fixer IO Error: " . json_encode($exchangeRates));
            return false;
        }
    }

    public function fixRates($rates, $base)
    {
        if ($rates['base'] == $base) return $rates['rates'];
        $base_rate = $rates['rates'][$base];
        foreach ($rates['rates'] as $key => $rate) {
            $rates['rates'][$key] = $rate / $base_rate;
        }
        return $rates['rate'];
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
