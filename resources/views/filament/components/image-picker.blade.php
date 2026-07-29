@props([
    'images' => [],
])

@php
    $statePath = $getStatePath();
@endphp

<div
    x-data="{
        selected: @js($getState()),
        select(url) {
            this.selected = url;
            $wire.set(@js($statePath), url, false);
        },
    }"
    style="width: 100%;"
>
    @if (count($images) > 0)
        <div
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.75rem; max-height: 60vh; overflow-y: auto; padding: 0.25rem;"
        >
            @foreach ($images as $image)
                <div
                    wire:key="{{ $statePath }}-{{ $loop->index }}"
                    x-on:click="select(@js($image->url))"
                    x-bind:style="selected === @js($image->url)
                        ? 'border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.35);'
                        : 'border-color: rgba(148, 163, 184, 0.35);'"
                    style="position: relative; aspect-ratio: 1 / 1; cursor: pointer; border-radius: 0.5rem; overflow: hidden; border: 2px solid rgba(148, 163, 184, 0.35); background-color: rgba(148, 163, 184, 0.12); transition: box-shadow 150ms ease, border-color 150ms ease;"
                >
                    <img
                        src="{{ $image->url }}"
                        alt="{{ $image->title }}"
                        loading="lazy"
                        style="width: 100%; height: 100%; object-fit: cover; display: block;"
                    />

                    <div
                        x-cloak
                        x-show="selected === @js($image->url)"
                        style="position: absolute; top: 0.375rem; right: 0.375rem; width: 1.5rem; height: 1.5rem; display: flex; align-items: center; justify-content: center; border-radius: 9999px; background-color: #f59e0b; color: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.35);"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem;">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div
            style="padding: 2.5rem; text-align: center; border: 2px dashed rgba(148, 163, 184, 0.4); border-radius: 0.75rem; color: rgba(107, 114, 128, 1);"
        >
            <p style="font-weight: 500; margin-bottom: 0.25rem;">Изображения не найдены</p>
            <p style="font-size: 0.875rem;">Измените наименование или проверьте настройки SERP_API.</p>
        </div>
    @endif
</div>
