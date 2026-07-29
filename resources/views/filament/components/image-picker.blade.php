@props([
    'images' => [],
])

@php
    $statePath = $getStatePath();
@endphp

<div
    x-data="{
        state: $wire.$entangle('{{ $statePath }}'),
    }"
    class="w-full"
>
    @if(count($images) > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 overflow-y-auto max-h-[60vh] p-2">
            @foreach($images as $image)
                <div
                    @click="state = '{{ $image->url }}'"
                    :class="{
                        'ring-4 ring-primary-500 ring-offset-2 z-10 scale-[1.02]': state === '{{ $image->url }}',
                        'opacity-80 hover:opacity-100 border-gray-200 dark:border-gray-700': state !== '{{ $image->url }}'
                    }"
                    class="relative cursor-pointer rounded-xl overflow-hidden border shadow-sm transition-all bg-white dark:bg-gray-800 flex flex-col h-full group"
                >
                    <div class="aspect-square w-full overflow-hidden bg-gray-100 dark:bg-gray-900">
                        <img
                            src="{{ $image->url }}"
                            alt="{{ $image->title }}"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                            loading="lazy"
                        />
                    </div>

                    @if($image->title || $image->source)
                        <div class="p-2 flex-grow flex flex-col justify-between">
                            @if($image->title)
                                <p class="text-[10px] leading-tight text-gray-700 dark:text-gray-300 line-clamp-2 mb-1" title="{{ $image->title }}">
                                    {{ $image->title }}
                                </p>
                            @endif
                            @if($image->source)
                                <p class="text-[8px] text-primary-600 dark:text-primary-400 font-bold uppercase tracking-wider truncate">
                                    {{ $image->source }}
                                </p>
                            @endif
                        </div>
                    @endif

                    <div
                        x-show="state === '{{ $image->url }}'"
                        class="absolute top-2 right-2 bg-primary-500 text-white rounded-full p-1.5 shadow-md"
                    >
                        <x-heroicon-m-check class="w-4 h-4" />
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-12 text-center bg-gray-50 dark:bg-gray-900/50 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800">
            <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-600">
                <x-heroicon-o-camera class="w-16 h-16 mb-4 opacity-20" />
                <p class="text-lg font-medium">{{ __('No images found for this query.') }}</p>
                <p class="text-sm">{{ __('Try changing the name or check your API configuration.') }}</p>
            </div>
        </div>
    @endif
</div>
