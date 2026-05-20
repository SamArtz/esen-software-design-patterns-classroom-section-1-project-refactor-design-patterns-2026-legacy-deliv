@props(['status'])

@php
$colors = [
    'created'   => '#95a5a6',
    'paid'      => '#3498db',
    'accepted'  => '#9b59b6',
    'preparing' => '#f39c12',
    'ready'     => '#1abc9c',
    'picked_up' => '#e67e22',
    'delivered' => '#27ae60',
    'cancelled' => '#e74c3c',
    'refunded'  => '#c0392b',
];
$color = $colors[$status] ?? '#bdc3c7';
@endphp

<span class="badge" style="background-color: {{ $color }}; color: white;">
    {{ strtoupper(str_replace('_', ' ', $status)) }}
</span>
