@livewire(\MWGuerra\FileManager\Livewire\EmbeddedFileManager::class, [
    'height' => $getHeight(),
    'showHeader' => $shouldShowHeader(),
    'showSidebar' => $shouldShowSidebar(),
    'defaultViewMode' => $getDefaultViewMode(),
    'disk' => $getDisk(),
    'target' => $getTarget(),
    'initialFolder' => $getInitialFolder(),
])
