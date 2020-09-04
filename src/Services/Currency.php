<?php

namespace Paksuco\Currency\Services;

use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Paksuco\Currency\Models\Currency as ModelsCurrency;

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
            return ModelsCurrency::where("active", "=", true)->get();
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
        setcookie('currency', $key, time() + (24 * 60 * 60), '/', url('/'), true, true);

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
        return $this->currencies->where("id", "=", $id)->first();
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
}
