{{--
Audio Viewer Component

Variables:
- $url: The URL of the audio file
- $item: The FileSystemItem model (optional)
--}}
@php
    $url = $url ?? null;
    $item = $item ?? null;
@endphp

<div class="flex flex-col items-center justify-center p-8 bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-lg">
    <div class="mb-6 p-8 bg-white dark:bg-gray-800 rounded-full shadow-lg">
        <x-heroicon-o-musical-note class="w-24 h-24 text-purple-500" />
    </div>

    @if($item)
        <p class="mb-4 text-lg font-medium text-gray-900 dark:text-white">{{ $item->name }}</p>
    @endif

    <audio
        controls
        autoplay
        class="w-full max-w-md"
        preload="metadata"
    >
        <source src="{{ $url }}" type="audio/mpeg">
        <source src="{{ $url }}" type="audio/wav">
        <source src="{{ $url }}" type="audio/ogg">
        Your browser does not support the audio element.
    </audio>
</div>
