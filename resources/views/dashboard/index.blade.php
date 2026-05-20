@extends('layouts.app')

@section('content')
<h1>Dashboard</h1>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem;">
    <div class="card" style="text-align: center;">
        <h2 style="margin: 0; font-size: 2.5rem; color: #2c3e50;">{{ $totalOrders }}</h2>
        <p style="color: #666;">Total Órdenes</p>
    </div>
    <div class="card" style="text-align: center;">
        <h2 style="margin: 0; font-size: 2.5rem; color: #27ae60;">{{ $activeVendors }}</h2>
        <p style="color: #666;">Vendors Activos</p>
    </div>
    <div class="card" style="text-align: center;">
        <h2 style="margin: 0; font-size: 2.5rem; color: #e74c3c;">{{ $ordersByStatus['cancelled'] ?? 0 }}</h2>
        <p style="color: #666;">Canceladas</p>
    </div>
</div>

<div class="card">
    <h2>Órdenes por Estado</h2>
    <table>
        <thead>
            <tr><th>Estado</th><th>Cantidad</th></tr>
        </thead>
        <tbody>
            @foreach($ordersByStatus as $status => $count)
            <tr>
                <td><x-order-status-badge :status="$status" /></td>
                <td>{{ $count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Órdenes Recientes</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Cliente</th><th>Vendor</th><th>Total</th><th>Estado</th></tr>
        </thead>
        <tbody>
            @foreach($recentOrders as $order)
            <tr>
                <td><a href="{{ route('orders.show', $order) }}">#{{ $order->id }}</a></td>
                <td>{{ $order->customer->user->name ?? 'N/A' }}</td>
                <td>{{ $order->vendor->business_name ?? 'N/A' }}</td>
                <td>${{ number_format($order->total, 2) }}</td>
                <td><x-order-status-badge :status="$order->status" /></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
