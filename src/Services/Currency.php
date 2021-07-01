<?php

namespace Paksuco\Currency\Services;

use DateInterval;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Paksuco\Currency\Contracts\ICurrencyProvider;
use Paksuco\Currency\Models\Currency as ModelsCurrency;
use Paksuco\Currency\Models\CurrencyHistory;
use RuntimeException;

class Currency
{
    private $currencies;
    private $request;

    public function __construct(Request $request)
    {
        $this->currencies = Cache::remember('system_currencies', new DateInterval("PT5M"), function () {
            /** @var Collection $currencies */
            $currencies = ModelsCurrency::active()->get();
            $default = Config::get("currencies.default", "USD");
            if ($currencies->where("currency_code", $default)->count() == 0) {
                ModelsCurrency::where("currency_code", "=", $default)->update([
                    "active" => true,
                ]);
                $currencies = ModelsCurrency::active()->get();
            }
            return $currencies;
        });
        $this->request = $request;
    }

    public function auth()
    {
        if (Auth::check() == false || Config::get("currencies.users_have_currencies", false) === false) {
            return false;
        }

        $currency_id = Auth::user()->currency_id;
        $currency = $this->currencies->where("id", "=", $currency_id)->first();
        if ($currency instanceof ModelsCurrency) {
            return $currency;
        }

        return false;
    }

    /**
     * @return ModelsCurrency
     */
    public function current($fallbackCurrencyId = null)
    {
        try {
            $driver = $this->driver();
            $key = $driver == "session" ?
                $this->request->session()->get('currency', null) :
                $this->request->cookie('currency');

            return ($key ? $this->get($key) : null) ?? $this->getDefault();
        } catch (RuntimeException $ex) {
            if ($fallbackCurrencyId) {
                return $this->find($fallbackCurrencyId) ?? $this->getDefault();
            }
            return $this->getDefault();
        }
    }

    public function get($key)
    {
        return $key
            ? $this->currencies->where("currency_code", "=", $key)->first()
            : null;
    }

    public function getCode($id)
    {
        $currency = $this->find($id);
        return $currency ? $currency->currency_code : null;
    }

    public function set($key)
    {
        $driver = $this->driver();

        $driver == "session" ?
            Session::put('currency', $key) :
            setcookie('currency', $key, time() + (24 * 60 * 60), '/', URL::to('/'));

        if (Auth::check() && Config::get("currencies.users_have_currencies", false) === true) {
            /** @var App\User */
            $user = Auth::user();
            $user->setAttribute(Config::get("currencies.currency_column"), $this->get($key)->id);
            $user->save();
        }
    }

    public function all()
    {
        return $this->currencies;
    }

    /**
     * @return  ModelsCurrency|null
     */
    public function find($id)
    {
        return $this->currencies->find($id);
    }

    /**
     * @return  ModelsCurrency|null
     */
    public function getDefault()
    {
        $default = Config::get("currencies.default", "USD");
        return $this->get($default);
    }

    public function driver()
    {
        return Config::get("currencies.method", "session");
    }

    public function toCurrent($model, $key, $when = null, $fallbackCurrencyId = null)
    {
        $model = (object) $model;
        $amount = floatval($model->$key) ?? 0;
        $currency = $model->{$key . "_currency_id"} ?? null;
        $currency = intval($currency);

        if ($amount && $currency) {
            /** @var ModelsCurrency */
            $currencyModel = Cache::remember("currency_model_$currency", new \DateInterval("PT1H"), function () use ($currency) {
                return ModelsCurrency::find($currency);
            });

            return $currencyModel->convert($amount, $this->current($fallbackCurrencyId), false, $when);
        }

        return $amount;
    }

    public function format($model, $key, $when = null, $fallbackCurrencyId = null)
    {
        $model = (object) $model;
        $amount = floatval($model->$key) ?? 0;
        $currency = $model->{$key . "_currency_id"} ?? null;

        if ($amount && $currency) {
            /** @var ModelsCurrency */
            $currencyModel = Cache::remember("currency_model_$currency", new \DateInterval("PT1H"), function () use ($currency) {
                return ModelsCurrency::find($currency);
            });

            return $currencyModel->convert($amount, $this->current($fallbackCurrencyId), true, null, $when);
        }

        return $this->current($fallbackCurrencyId)->format($amount);
    }

    public function getRateFor(ModelsCurrency $currency, Carbon $date)
    {
        if ($date->isSameDay(now())) {
            if (
                $currency->currency_code != config('currencies.base_currency')
                && $currency->rate == 1
            ) {
                $this->updateRates();
                $currency->refresh();
            }
            return $currency->rate;
        }

        $date = $date->startOfDay();

        /** @var CurrencyHistory $historicalRate */
        $historicalRate = CurrencyHistory::where("currency_at", "=", $date)
            ->where("currency_code", "=", $currency->currency_code)
            ->first();

        if ($historicalRate instanceof CurrencyHistory) {
            return $historicalRate->rate;
        } else {
            $this->updateRates($date);
            return $this->getRateFor($currency, $date);
        }
    }

    public function updateRates(Carbon $date = null)
    {
        $result = false;
        $availableProviders = config("currencies.providers", []);
        foreach ($availableProviders as $provider) {
            $providerInstance = new $provider["class"];
            if ($providerInstance instanceof ICurrencyProvider) {
                if ($date && $date->isSameDay(Carbon::now()) == false) {
                    $hasRates = CurrencyHistory::where('currency_at', '=', $date)->count() > 0;
                    if ($hasRates == false) {
                        $result = $providerInstance->getHistoricalRates($date);
                    } else {
                        break;
                    }
                } else {
                    /** @var Carbon $latest */
                    $latest = ModelsCurrency::max('updated_at');
                    $latest = $latest ? Carbon::parse($latest) : null;
                    if (
                        $latest == null
                        || $latest->diffInMinutes(now(), true) > 30
                        || ModelsCurrency::where('rate', '=', 1)->count() > 5
                    ) {
                        $result = $providerInstance->getLatestRates();
                    } else {
                        break;
                    }
                }
                if ($result !== false) {
                    $this->processRates($result, $date);
                    break;
                }
            }
        }
    }

    protected function processRates($rates, Carbon $date = null)
    {
        if ($date == null || $date->isSameDay(Carbon::now())) {
            // Fill currency table
            foreach ($rates as $key => $rate) {
                $currency = ModelsCurrency::withoutGlobalScopes()->where("currency_code", "=", $key)->first();
                if ($currency instanceof ModelsCurrency) {
                    $currency->updated_at = now();
                    $currency->rate = $rate;
                    $currency->save();
                }
            }
        } else {
            $date = $date->startOfDay();
            foreach ($rates as $key => $rate) {
                $currencyHistory = CurrencyHistory::withoutGlobalScopes()->where([
                    "currency_code" => $key,
                    "currency_at" => $date
                ])->first();
                if ($currencyHistory instanceof CurrencyHistory) {
                    $currencyHistory->rate = $rate;
                    $currencyHistory->save();
                } else {
                    $currencyHistory = new CurrencyHistory();
                    $currencyHistory->base_currency = "TRY";
                    $currencyHistory->currency_at = $date;
                    $currencyHistory->currency_code = $key;
                    $currencyHistory->rate = $rate;
                    $currencyHistory->save();
                }
            }
        }
    }
}
