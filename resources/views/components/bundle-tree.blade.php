@props(['bundle', 'depth' => 0])

<div style="margin-left: {{ $depth * 1.5 }}rem; border-left: {{ $depth > 0 ? '2px solid #e5e7eb' : 'none' }}; padding-left: {{ $depth > 0 ? '0.75rem' : '0' }}; margin-bottom: 0.25rem;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: {{ $depth === 0 ? '600' : '400' }};">
            {{ $depth > 0 ? '↳ ' : '' }}{{ $bundle->name }}
            @if($bundle->discount_percentage > 0)
                <span style="color: #16a34a; font-size: 0.8em;">({{ $bundle->discount_percentage }}% dto)</span>
            @endif
        </span>
        <span style="font-family: monospace; color: #374151;">
            ${{ number_format($bundle->getTotalPrice(), 2) }}
        </span>
    </div>

    @foreach($bundle->products as $product)
        <div style="margin-left: 1.5rem; color: #6b7280; font-size: 0.9em;">
            {{ $product->name }} × {{ $product->pivot->quantity }}
            <span style="font-family: monospace;">${{ number_format($product->price * $product->pivot->quantity, 2) }}</span>
        </div>
    @endforeach

    @foreach($bundle->childBundles as $child)
        <x-bundle-tree :bundle="$child" :depth="$depth + 1" />
    @endforeach
</div>
