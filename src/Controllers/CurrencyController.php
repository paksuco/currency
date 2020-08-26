<?php

namespace Paksuco\Currency\Controllers;

class CurrencyController extends \App\Http\Controllers\Controller
{
    private $extends;

    public function __construct()
    {
        $this->middleware(config("currency.middleware", ["web", "auth"]));
        $this->extends = config('currency.template_to_extend', "layouts.app");
    }

    public function index()
    {
        return view("paksuco-currency::container", ["extends" => $this->extends]);
    }
}
