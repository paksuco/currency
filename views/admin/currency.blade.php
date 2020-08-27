<div class="p-1 w-full xs:w-1/2 sm:w-1/3 md:w-1/4 xl:w-1/5 flex">
    <div
         class="relative flex flex-col flex-1 border rounded shadow text-sm {{$currency->active ? 'bg-indigo-700 border-indigo-800 text-white' : 'bg-white'}}">
        <div class="absolute text-xs top-3 right-3 rounded px-1 pb-px {{$currency->active ? 'bg-red-600' : 'bg-green-400 text-white'}} cursor-pointer"
             wire:click='toggleCurrency()'>
            {{$currency->active ? __('Disable') : __('Enable')}}
        </div>
        <div class="font-semibold w-2/3  pt-3 px-3 leading-4">{{$currency->currency_name}} ({{$currency->currency_code}})</div>
        <div class="text-xs pt-1 flex-1 px-3 {{$currency->active ? 'text-gray-300' : 'text-gray-600'}}">{{$currency->country_name}}
            ({{$currency->country_code}})</div>
        <div
             class="text-lg mt-2 text-right p-1 px-2 bottom-0 inset-x-0 {{$currency->active ? 'bg-indigo-900' : 'bg-gray-100'}} rounded-b">
            {!! $currency->format(1) !!} <i class="fa fa-arrows-alt-h px-1"></i> {!! $currency->convert(1, 'TRY', true)
            !!}
        </div>
    </div>
</div>
