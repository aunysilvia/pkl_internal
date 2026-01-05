@props(['status'])

@php
$colors = [
'pending' => 'bg-yellow-100 text-yellow-800',
'processing' => 'bg-blue-100 text-blue-800',
'completed' => 'bg-green-100 text-green-800',
'cancelled' => 'bg-red-100 text-red-800',
];
$colorClass = $colors[$status] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="px-2 py-1 text-xs rounded-full font-semibold {{ $colorClass }}">
    {{ ucfirst($status) }}
</span>
