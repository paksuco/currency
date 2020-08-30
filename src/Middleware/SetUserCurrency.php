<?php

namespace Paksuco\Currency\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Paksuco\Currency\Facades\Currency;
use Paksuco\Currency\Models\Currency as ModelsCurrency;

class SetUserCurrency
{

    private function setUserCurrency(Request $request)
    {
        if (auth()->check()) {
            $currency = Currency::auth();
            if ($currency instanceof ModelsCurrency) {
                Currency::set($currency->currency_code);
                return true;
            }
        }
        return $this->setSystemCurrency($request);
    }

    private function setSystemCurrency(Request $request)
    {
        $agent = new Agent();
        $available = $agent->languages();
        $currencies = Currency::all();
        foreach ($available as $countryCode) {
            $countryCode = strtoupper(last(explode("-", $countryCode)));
            if ($currencies->where("country_code", "=", "$countryCode")->count() > 0) {
                $currency = $currencies->where("country_code", "=", "$countryCode")->first();
                Currency::set($currency->currency_code);
                return true;
            }
        }
        return false;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('currency')) {
            Currency::set($request->currency);
        } elseif (Currency::current() != null) {
            // do nothing
        } elseif (auth()->check()) {
            $this->setUserCurrency($request);
        } else {
            $this->setSystemCurrency($request);
        }

        return $next($request);
    }
}
