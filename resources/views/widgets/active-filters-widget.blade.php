<x-filament-widgets::widget>
    @php
        $chips = $this->getChips();
    @endphp

    @if (count($chips) > 0)
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;padding:0.75rem 1rem;background:rgba(99,102,241,0.04);border:1px solid rgba(99,102,241,0.15);border-radius:0.5rem;">
            <span style="font-size:0.75rem;font-weight:500;color:rgb(75,85,99);text-transform:uppercase;letter-spacing:0.05em;">Active filters:</span>
            @foreach ($chips as $chip)
                {!! $chip !!}
            @endforeach
        </div>
    @endif
</x-filament-widgets::widget>
