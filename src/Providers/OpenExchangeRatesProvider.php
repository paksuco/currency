<?php

namespace Paksuco\Currency\Providers;

use Illuminate\Support\Carbon;
use Paksuco\Currency\Contracts\ICurrencyProvider;

class OpenExchangeRatesProvider implements ICurrencyProvider
{
    protected $key        = "openexchangerates";
    protected $base       = "USD";

    public function getApiKey()
    {
        return config("currencies.providers.{$this->key}.credentials.api_key", null);
    }

    public function getBaseUrl()
    {
        return "https://openexchangerates.org/api/";
    }

    public function getLatestRates()
    {
        $baseUrl          = $this->getBaseUrl();
        $endpoint         = 'latest.json';
        $accessKey        = $this->getApiKey();

        if (empty($accessKey)) {
            logger()->alert("OpenExchRates Access Key is empty");
            return false;
        }

        $url              = "$baseUrl$endpoint?app_id=$accessKey";
        $exchangeRates    = $this->fetchRemoteJson($url);

        if ($exchangeRates && $this->hasError($exchangeRates) == false) {
            return $this->fixRates($exchangeRates);
        } else {
            logger()->alert("OpenExchRates Error: " . json_encode($exchangeRates));
            return false;
        }
    }

    public function getHistoricalRates(Carbon $date)
    {
        $baseUrl          = $this->getBaseUrl();
        $accessKey        = $this->getApiKey();

        if (empty($accessKey)) {
            logger()->alert("OpenExchRates Access Key is empty");
            return false;
        }

        if ($date->isValid() === false) {
            logger()->alert("Error: open exchange rates historical rates request: Date is not valid (" . var_export($date, true) . ")");
            return false;
        }

        if (now()->isSameDay($date)) {
            return $this->getLatestRates();
        }

        $endpoint         = $date->format("Y-m-d") . ".json";
        $url              = "$baseUrl$endpoint?app_id=$accessKey";
        $exchangeRates    = $this->fetchRemoteJson($url);

        if ($exchangeRates && $this->hasError($exchangeRates) == false) {
            return $this->fixRates($exchangeRates);
        } else {
            logger()->alert("OpenExchRates Error: " . json_encode($exchangeRates));
            return false;
        }
    }

    private function fixRates($response)
    {
        if($response["base"] === "USD") return $response["rates"];
        //$baseRate = $response["rates"][resp]
        return $response["rates"];
    }

    private function hasError($response)
    {
        return isset($response["error"]) && $response["error"] === "true";
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
