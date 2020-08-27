<div>
    <h3 class="text-2xl font-semibold mb-3" style="line-height: 1em">@lang("Enabled Currencies")</h3>
    <div class="flex-1 flex flex-row items-center relative mb-4">
        <input wire:model.debounce.500ms="query"
            class="bg-gray-200 rounded shadow
                    placeholder-gray-800 py-2 px-3 text-gray-700 flex-1 leading-tight
                    focus:bg-white min-w-0 relative text-sm"
            type="text" placeholder="@lang('Currency name')">
        <i class="fa fa-search absolute right-2 flex text-gray-500 justify-end items-center"></i>
    </div>
    <div class="flex items-stretch flex-wrap">
        @foreach($currencies as $currency)
        @livewire("paksuco-currency::currency", ["currency" => $currency], key("currency-".$currency->id))
        @endforeach
    </div>
</div>
