<?php

namespace Paksuco\Currency\Services;

use Carbon\Carbon;
use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Paksuco\Currency\Models\Currency as ModelsCurrency;
use Paksuco\Currency\Models\CurrencyHistory;
use Paksuco\Settings\Facades\Settings;

class Currency
{
    private $currencies;

    public function __construct()
    {
        $this->currencies = Cache::remember('system_currencies', new DateInterval("PT1H"), function () {
            $default = config("currencies.default", "USD");
            ModelsCurrency::where("currency_code", "=", $default)->update([
                "active" => true,
            ]);
            return ModelsCurrency::active()->get();
        });
    }

    public function auth()
    {
        if (config("currencies.users_have_currencies", false) === false || Auth::check() == false) {
            return false;
        }

        $currency_id = Auth::user()->currency_id;
        $currency = $this->currencies->where("id", "=", $currency_id)->first();
        if ($currency instanceof ModelsCurrency) {
            return $currency;
        }

        return false;
    }

    public function current()
    {
        $driver = $this->driver();

        /** @var Request $request */
        $request = request();

        $key = $driver == "session" ?
        $request->session()->get('currency', null) :
        $request->cookie('currency');

        return $key ? $this->get($key) : null;
    }

    public function get($key)
    {
        if (!$key) {
            return null;
        }

        return $this->currencies->where("currency_code", $key)->first() ?? null;
    }

    public function set($key)
    {
        $driver = $this->driver();

        $driver == "session" ?
        Session::put('currency', $key) :
        setcookie('currency', $key, time() + (24 * 60 * 60), '/', url('/'));

        if (config("currencies.users_have_currencies", false) === true && Auth::check()) {
            $user = request()->user();
            $user->{config("currencies.currency_column")} = $this->get($key)->id;
            $user->save();
        }
    }

    public function all()
    {
        return $this->currencies;
    }

    public function find($id)
    {
        return $this->currencies->find($id);
    }

    public function getDefault()
    {
        $default = config("currencies.default", "USD");
        return $this->get($default);
    }

    public function driver()
    {
        return config("currencies.method", "session");
    }

    public function toCurrent($model, $key, $when = null, $roundUp = false)
    {
        $amount = floatval($model->$key) ?? 0;
        $currency = $model->{$key . "_currency_id"} ?? null;

        if ($amount && $currency) {
            /** @var ModelsCurrency */
            $currencyModel = Cache::remember("currency_model_$currency", new \DateInterval("PT1H"), function () use ($currency) {
                return ModelsCurrency::find($currency);
            });
            return $currencyModel->convert($amount, $this->current(), false, $when, $roundUp);
        }

        return $amount;
    }

    public function format($model, $key, $when = null, $roundUp = false)
    {
        $amount = floatval($model->$key) ?? 0;
        $currency = $model->{$key . "_currency_id"} ?? null;

        if ($amount && $currency) {
            /** @var ModelsCurrency */
            $currencyModel = Cache::remember("currency_model_$currency", new \DateInterval("PT1H"), function () use ($currency) {
                return ModelsCurrency::find($currency);
            });

            return $currencyModel->convert($amount, $this->current(), true, $when, $roundUp);
        }

        return $this->current()->format($amount);
    }

    public function update($date = null)
    {
        // set API Endpoint and API key
        $endpoint = 'latest';
        if ($date) {
            $endpoint = $date;
        }

        $access_key = Settings::get('fixer_api_key', "");

        if ($access_key == "") {
            return 0;
        }

        // Initialize CURL:
        $ch = curl_init('http://data.fixer.io/api/' . $endpoint . '?access_key=' . $access_key . '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Store the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $exchangeRates = json_decode($json, true);

        if ($exchangeRates && $exchangeRates["success"]) {
            $currencies = $exchangeRates["rates"];
            foreach ($currencies as $key => $value) {
                $currency = ModelsCurrency::where("currency_code", "=", $key)->first();
                if ($currency instanceof ModelsCurrency) {
                    if (!$date) {
                        $currency->rate = $value;
                        $currency->save();
                    }
                    if ($currency->active) {
                        CurrencyHistory::create([
                            "base_currency" => $exchangeRates["base"],
                            "currency_code" => $key,
                            "rate" => $value,
                            "currency_at" => $date ?
                            Carbon::createFromFormat("Y-m-d", $date)->startOfDay() :
                            Carbon::createFromTimestamp($exchangeRates["timestamp"]),
                        ]);
                    }
                }
            }
        }

        return 0;
    }
}
