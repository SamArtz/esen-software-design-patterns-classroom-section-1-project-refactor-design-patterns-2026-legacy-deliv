@extends('layouts.app')

@section('content')
<h1>Orden #{{ $order->id }}</h1>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
    <div class="card">
        <h3>Información General</h3>
        <p><strong>Estado:</strong> <x-order-status-badge :status="$order->status" /></p>
        <p><strong>Cliente:</strong> {{ $order->customer->user->name ?? 'N/A' }}</p>
        <p><strong>Vendor:</strong> {{ $order->vendor->business_name ?? 'N/A' }}</p>
        @if($order->courier)
        <p><strong>Courier:</strong> {{ $order->courier->user->name ?? 'N/A' }}</p>
        @endif
        <p><strong>Dirección de entrega:</strong> {{ $order->delivery_address }}</p>
        <p><strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
        @if($order->notes)
        <p><strong>Notas:</strong> {{ $order->notes }}</p>
        @endif
    </div>

    <div class="card">
        <h3>Resumen de Pago</h3>
        <p><strong>Subtotal:</strong> ${{ number_format($order->subtotal, 2) }}</p>
        <p><strong>Descuento:</strong> -${{ number_format($order->discount_total, 2) }}</p>
        <p><strong>Envío:</strong> ${{ number_format($order->delivery_fee, 2) }}</p>
        <hr>
        <p><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>
        @if($order->payment)
        <p><strong>Pago:</strong> {{ ucfirst($order->payment->status) }}</p>
        @endif
    </div>
</div>

<div class="card">
    <h3>Items del Pedido</h3>
    <table>
        <thead>
            <tr><th>Tipo</th><th>ID</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ ucfirst($item->item_type) }}</td>
                <td>#{{ $item->item_id }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->unit_price, 2) }}</td>
                <td>${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($order->discounts->isNotEmpty())
<div class="card">
    <h3>Descuentos Aplicados</h3>
    @foreach($order->discounts as $discount)
    <p>{{ $discount->code }} ({{ $discount->type }})</p>
    @endforeach
</div>
@endif
@endsection
