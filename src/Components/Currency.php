<?php

namespace Paksuco\Currency\Components;

use Livewire\Component;
use Paksuco\Currency\Models\Currency as CurrencyModel;

class Currency extends Component
{
    public $currency;

    public function mount(CurrencyModel $currency)
    {
        $this->currency = $currency;
    }

    public function toggleCurrency()
    {
        $this->currency->active = !$this->currency->active;
        $this->currency->save();
    }

    public function render()
    {
        return view("paksuco-currency::admin.currency");
    }
}
