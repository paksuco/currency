<div class="p-1 w-1/6 flex">
    <div
         class="relative flex-1 h-24 border rounded shadow p-3 text-sm {{$currency->active ? 'bg-indigo-700 border-indigo-800 text-white' : 'bg-white'}}">
        <div class="absolute text-xs top-3 right-3 rounded px-1 pb-px {{$currency->active ? 'bg-red-600' : 'bg-green-400 text-white'}} cursor-pointer"
             wire:click='toggleCurrency()'>
            {{$currency->active ? __('Disable') : __('Enable')}}
        </div>
        <div class="font-semibold w-2/3 leading-4">{{$currency->currency_name}} ({{$currency->currency_code}})</div>
        <div class="text-xs {{$currency->active ? 'text-gray-300' : 'text-gray-600'}}">{{$currency->country_name}}
            ({{$currency->country_code}})</div>
        <div
             class="text-lg mt-2 absolute text-right p-1 px-2 bottom-0 inset-x-0 {{$currency->active ? 'bg-indigo-900' : 'bg-gray-100'}} rounded-b">
            {!! $currency->format(1) !!} <i class="fa fa-arrows-alt-h px-1"></i> {!! $currency->convert(1, 'TRY', true)
            !!}
        </div>
    </div>
</div>
