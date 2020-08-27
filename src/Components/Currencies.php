<?php

namespace Paksuco\Currency\Components;

use Livewire\Component;
use Paksuco\Currency\Models\Currency;

class Currencies extends Component
{
    public $currency;

    public function mount(Currency $currency)
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
        return view("paksuco-currency::admin.currencies");
    }
}
