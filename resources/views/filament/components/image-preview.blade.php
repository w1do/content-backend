@if($imageUrl)
    <div class="flex flex-col items-center justify-center p-4">
        <img src="{{ $imageUrl }}" alt="Search result" class="max-w-full h-auto rounded-lg shadow-lg mb-4" />
        @if($title)
            <p class="text-sm text-gray-500 italic">{{ $title }}</p>
        @endif
        @if($source)
            <p class="text-xs text-gray-400">Source: {{ $source }}</p>
        @endif
    </div>
@else
    <div class="p-4 text-center text-gray-500">
        {{ __('No images found for this query.') }}
    </div>
@endif
