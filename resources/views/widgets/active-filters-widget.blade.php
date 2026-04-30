@php
    $chips = $this->getChips();
@endphp

@if (count($chips) > 0)
    <x-filament-widgets::widget>
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">
            @foreach ($chips as $chip)
                {!! $chip !!}
            @endforeach
        </div>
    </x-filament-widgets::widget>
@else
    <div></div>
@endif
