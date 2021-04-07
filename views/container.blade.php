@extends($extends)
@section("content")
<div class="p-8">
    <div class="items-end w-full">
        <h2 class="mb-3 text-3xl font-semibold" style="line-height: 1em">@lang("Currency Management")</h2>
        <p class="mb-8 text-sm font-light leading-5 text-gray-600">@lang("This page contains the available currencies, their rates and the configuration options that you can enable a site-wide currency or disable it.")</p>
        @livewire("paksuco-currency::currencies")
    </div>
</div>
@endsection
