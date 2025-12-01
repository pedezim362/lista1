{{--
Video Viewer Component

Variables:
- $url: The URL of the video file
- $item: The FileSystemItem model (optional)
--}}
@php
    $url = $url ?? null;
    $item = $item ?? null;
@endphp

<div class="flex items-center justify-center bg-black rounded-lg overflow-hidden">
    <video
        controls
        autoplay
        class="max-w-full max-h-[65vh]"
        preload="metadata"
        @if($item && $item->thumbnail)
            poster="{{ $item->thumbnail }}"
        @endif
    >
        <source src="{{ $url }}" type="video/mp4">
        <source src="{{ $url }}" type="video/webm">
        <source src="{{ $url }}" type="video/ogg">
        Your browser does not support the video tag.
    </video>
</div>
