{{--
PDF Viewer Component

Variables:
- $url: The URL of the PDF file
- $item: The FileSystemItem model (optional)
--}}
@php
    $url = $url ?? null;
    $item = $item ?? null;
@endphp

<div class="bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden">
    <iframe
        src="{{ $url }}"
        class="w-full h-[65vh]"
        frameborder="0"
        title="{{ $item?->name ?? 'PDF preview' }}"
    ></iframe>
</div>
