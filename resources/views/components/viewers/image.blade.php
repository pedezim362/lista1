{{--
Image Viewer Component

Variables:
- $url: The URL of the image file
- $item: The FileSystemItem model (optional)
--}}
@php
    $url = $url ?? null;
    $item = $item ?? null;
@endphp

<div class="flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg p-4">
    <img
        src="{{ $url }}"
        alt="{{ $item?->name ?? 'Image preview' }}"
        class="max-w-full max-h-[65vh] object-contain rounded"
        loading="lazy"
    />
</div>
