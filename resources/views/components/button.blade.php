@props(['variant' => 'primary', 'size' => 'md', 'href' => null])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors';

    $variantClasses = match($variant) {
        'primary' => 'bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
        'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
        'outline' => 'border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 focus:ring-indigo-500',
        default => 'bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2 text-sm',
    };

    $classes = "{$baseClasses} {$variantClasses} {$sizeClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>
        {{ $slot }}
    </button>
@endif
