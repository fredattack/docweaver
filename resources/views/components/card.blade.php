@props(['title' => null, 'actions' => null])

<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden shadow-sm sm:rounded-lg']) }}>
    @if($title || $actions)
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            @if($title)
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            @endif

            @if($actions)
                <div class="flex space-x-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
