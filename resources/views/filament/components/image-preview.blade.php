@props([
    'images' => [],
])

<div
    x-data="{
        selectedUrl: $wire.$entangle('data.search_image_url'),
    }"
    class="p-4"
>
    @if(count($images) > 0)
        <div class="grid grid-cols-2 gap-4 overflow-y-auto max-h-[60vh] p-1">
            @foreach($images as $image)
                <div
                    @click="selectedUrl = '{{ $image->url }}'"
                    :class="{
                        'ring-4 ring-primary-500 ring-offset-2': selectedUrl === '{{ $image->url }}',
                        'opacity-75 hover:opacity-100': selectedUrl !== '{{ $image->url }}'
                    }"
                    class="relative cursor-pointer rounded-lg overflow-hidden border border-gray-200 shadow-sm transition-all bg-white dark:bg-gray-800"
                >
                    <img
                        src="{{ $image->url }}"
                        alt="{{ $image->title }}"
                        class="w-full h-32 object-cover"
                        loading="lazy"
                    />
                    @if($image->title)
                        <div class="p-2">
                            <p class="text-[10px] leading-tight text-gray-600 dark:text-gray-400 line-clamp-2" title="{{ $image->title }}">
                                {{ $image->title }}
                            </p>
                            @if($image->source)
                                <p class="text-[8px] text-gray-400 mt-1 uppercase">{{ $image->source }}</p>
                            @endif
                        </div>
                    @endif

                    <div
                        x-show="selectedUrl === '{{ $image->url }}'"
                        class="absolute top-2 right-2 bg-primary-500 text-white rounded-full p-1"
                    >
                        <x-heroicon-m-check class="w-4 h-4" />
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="p-8 text-center">
            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                <x-heroicon-o-camera class="w-12 h-12 mb-2 opacity-20" />
                <p>{{ __('No images found for this query.') }}</p>
            </div>
        </div>
    @endif
</div>
