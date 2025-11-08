@props(['type' => 'default'])

@php
    $classes = match($type) {
        'draft' => 'bg-gray-100 text-gray-800',
        'review' => 'bg-yellow-100 text-yellow-800',
        'published' => 'bg-green-100 text-green-800',
        'archived' => 'bg-red-100 text-red-800',
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'error' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$classes}"]) }}>
    {{ $slot }}
</span>
