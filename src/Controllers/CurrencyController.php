<?php

namespace Paksuco\Currency\Controllers;

use Paksuco\Currency\Models\Currency;

class CurrencyController extends \App\Http\Controllers\Controller
{
    private $extends;

    public function __construct()
    {
        $this->middleware(config("currencies.middleware", ["web", "auth"]));
        $this->extends = config('currencies.template_to_extend', "layouts.app");
    }

    public function index()
    {
        return view("paksuco-currency::container", [
            "extends" => $this->extends,
            "currencies" => Currency::orderByDesc("active")->get()
        ]);
    }
}
