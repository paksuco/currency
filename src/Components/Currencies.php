<?php

namespace Paksuco\Currency\Components;

use Livewire\Component;
use Paksuco\Currency\Models\Currency;

class Currencies extends Component
{
    public $currencies;

    public $query;

    public function mount()
    {
        $this->currencies = Currency::query()
            ->orderByDesc("active")
            ->orderBy("currency_code")
            ->get();
    }

    public function updatedQuery()
    {
        $this->currencies = Currency::query()
            ->where("currency_code", "like", "%" . $this->query . "%")
            ->orWhere("currency_name", "like", "%" . $this->query . "%")
            ->orWhere("country_code", "like", "%" . $this->query . "%")
            ->orWhere("country_name", "like", "%" . $this->query . "%")
            ->orderByDesc("active")
            ->orderBy("currency_code")
            ->get();
    }

    public function render()
    {
        return view("paksuco-currency::admin.currencies");
    }
}
