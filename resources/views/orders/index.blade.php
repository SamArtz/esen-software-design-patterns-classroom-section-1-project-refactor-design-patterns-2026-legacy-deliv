@extends('layouts.app')

@section('content')
<h1>Todas las Órdenes</h1>
{{-- SMELL: carga todas las órdenes sin paginación --}}
<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Vendor</th>
                <th>Subtotal</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td>#{{ $order->id }}</td>
                <td>{{ $order->customer->user->name ?? 'N/A' }}</td>
                <td>{{ $order->vendor->business_name ?? 'N/A' }}</td>
                <td>${{ number_format($order->subtotal, 2) }}</td>
                <td>${{ number_format($order->total, 2) }}</td>
                <td><x-order-status-badge :status="$order->status" /></td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td><a href="{{ route('orders.show', $order) }}">Ver</a></td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align: center;">No hay órdenes</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
