<?php

namespace Paksuco\Currency\Components;

use Livewire\Component;

class Currency extends Component
{
    public function mount()
    {
    }

    public function render()
    {
        return view("paksuco-currency::admin.currency");
    }
}
