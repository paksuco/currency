@extends($extends)
@section("content")
<div class="p-8 bg-white border-t">
    <div class="w-full items-end">
        <h2 class="text-3xl font-semibold mb-3" style="line-height: 1em">@lang("Currency Management")</h2>
        <p class="text-gray-600 font-light leading-5 mb-4 text-sm">Lorem ipsum dolor sit amet, consectetur
            adipiscing elit. Proin interdum urna sit amet lorem iaculis, aliquet suscipit sapien venenatis.
            Sed congue vitae velit vitae varius. Mauris egestas consequat mauris sit amet mollis. Proin porta
            tortor in urna tincidunt vehicula. Integer urna nulla, porttitor ac imperdiet eu, mattis vel lacus.
            Sed et porttitor ex. Morbi pellentesque massa a velit gravida, vitae rutrum tortor consequat. Donec
            interdum lacus ut sem consectetur elementum. Proin pellentesque maximus sem sed rhoncus. Cras eget
            neque a nisi posuere mollis vitae vitae magna. Praesent non volutpat sem, a maximus libero. </p>
        @livewire("paksuco-currency::currencies")
    </div>
</div>
@endsection
