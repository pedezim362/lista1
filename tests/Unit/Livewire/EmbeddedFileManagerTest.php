<?php

use Illuminate\Support\Facades\Storage;
use MWGuerra\FileManager\Livewire\EmbeddedFileManager;
use MWGuerra\FileManager\Models\FileSystemItem;

beforeEach(function () {
    Storage::fake('testing');
    $this->app['config']->set('filemanager.mode', 'database');
    $this->app['config']->set('filemanager.model', FileSystemItem::class);
    $this->app['config']->set('filemanager.storage_mode.disk', 'testing');
    $this->app['config']->set('filemanager.upload.disk', 'testing');
    $this->app['config']->set('filemanager.upload.max_file_size', 10240);
});

describe('mount', function () {
    it('mounts with default configuration', function () {
        $component = new EmbeddedFileManager();
        $component->mount();

        expect($component->height)->toBe('500px');
        expect($component->showHeader)->toBeTrue();
        expect($component->showSidebar)->toBeTrue();
        expect($component->defaultViewMode)->toBe('grid');
        expect($component->viewMode)->toBe('grid');
        expect($component->disk)->toBeNull();
        expect($component->target)->toBeNull();
        expect($component->currentPath)->toBeNull();
    });

    it('mounts with custom configuration', function () {
        $component = new EmbeddedFileManager();
        $component->mount(
            height: '800px',
            showHeader: false,
            showSidebar: false,
            defaultViewMode: 'list',
            disk: 'testing',
            target: 'uploads',
            initialFolder: 'documents'
        );

        expect($component->height)->toBe('800px');
        expect($component->showHeader)->toBeFalse();
        expect($component->showSidebar)->toBeFalse();
        expect($component->defaultViewMode)->toBe('list');
        expect($component->viewMode)->toBe('list');
        expect($component->disk)->toBe('testing');
        expect($component->target)->toBe('uploads');
        expect($component->currentPath)->toBe('documents');
    });

    it('expands initial folder in tree', function () {
        $component = new EmbeddedFileManager();
        $component->mount(initialFolder: 'documents');

        expect($component->expandedFolders)->toContain('root');
        expect($component->expandedFolders)->toContain('documents');
    });
});

describe('mode', function () {
    it('returns database mode', function () {
        $component = new EmbeddedFileManager();
        $component->mount();

        expect($component->getMode())->toBe('database');
    });

    it('is not read-only', function () {
        $component = new EmbeddedFileManager();
        $component->mount();

        expect($component->isReadOnly())->toBeFalse();
    });
});

describe('navigation', function () {
    it('navigates to a folder', function () {
        $folder = FileSystemItem::factory()->folder()->create(['name' => 'documents']);

        $component = new EmbeddedFileManager();
        $component->mount();
        $component->navigateTo((string) $folder->id);

        expect($component->currentPath)->toBe((string) $folder->id);
        expect($component->selectedItems)->toBe([]);
    });

    it('clears selection when navigating', function () {
        $folder = FileSystemItem::factory()->folder()->create();
        $file = FileSystemItem::factory()->create();

        $component = new EmbeddedFileManager();
        $component->mount();
        $component->selectedItems = [(string) $file->id];
        $component->navigateTo((string) $folder->id);

        expect($component->selectedItems)->toBe([]);
    });
});

describe('folder expansion', function () {
    it('toggles folder expansion', function () {
        $folder = FileSystemItem::factory()->folder()->create();

        $component = new EmbeddedFileManager();
        $component->mount();

        expect($component->expandedFolders)->toBe(['root']);

        $component->toggleFolder((string) $folder->id);
        expect($component->expandedFolders)->toContain((string) $folder->id);

        $component->toggleFolder((string) $folder->id);
        expect($component->expandedFolders)->not->toContain((string) $folder->id);
    });

    it('checks if folder is expanded', function () {
        $folder = FileSystemItem::factory()->folder()->create();

        $component = new EmbeddedFileManager();
        $component->mount();

        expect($component->isFolderExpanded(null))->toBeTrue();
        expect($component->isFolderExpanded((string) $folder->id))->toBeFalse();

        $component->toggleFolder((string) $folder->id);

        expect($component->isFolderExpanded((string) $folder->id))->toBeTrue();
    });
});

describe('view mode', function () {
    it('sets view mode', function () {
        $component = new EmbeddedFileManager();
        $component->mount();

        expect($component->viewMode)->toBe('grid');

        $component->setViewMode('list');

        expect($component->viewMode)->toBe('list');
    });
});

describe('selection', function () {
    it('toggles single item selection', function () {
        $file = FileSystemItem::factory()->create();

        $component = new EmbeddedFileManager();
        $component->mount();

        $component->toggleSelection((string) $file->id);
        expect($component->selectedItems)->toBe([(string) $file->id]);

        $component->toggleSelection((string) $file->id);
        expect($component->selectedItems)->toBe([]);
    });

    it('toggles multi-selection', function () {
        $file1 = FileSystemItem::factory()->create();
        $file2 = FileSystemItem::factory()->create();

        $component = new EmbeddedFileManager();
        $component->mount();

        $component->toggleSelection((string) $file1->id, true);
        $component->toggleSelection((string) $file2->id, true);

        expect($component->selectedItems)->toBe([(string) $file1->id, (string) $file2->id]);
    });

    it('clears selection', function () {
        $file = FileSystemItem::factory()->create();

        $component = new EmbeddedFileManager();
        $component->mount();
        $component->selectedItems = [(string) $file->id];

        $component->clearSelection();

        expect($component->selectedItems)->toBe([]);
    });

    it('checks if item is selected', function () {
        $file = FileSystemItem::factory()->create();

        $component = new EmbeddedFileManager();
        $component->mount();
        $component->toggleSelection((string) $file->id);

        expect($component->isSelected((string) $file->id))->toBeTrue();
    });
});

describe('changes tracking', function () {
    it('gets changes count', function () {
        $component = new EmbeddedFileManager();
        $component->mount();

        // Directly access changes array since we can't render
        expect($component->changesCount)->toBe(0);
    });

    it('clears changes', function () {
        $component = new EmbeddedFileManager();
        $component->mount();
        $component->changes = [['type' => 'test']];

        $component->clearChanges();

        expect($component->getChanges())->toHaveCount(0);
    });
});

describe('upload', function () {
    it('clears uploaded files', function () {
        $component = new EmbeddedFileManager();
        $component->mount();
        $component->uploadedFiles = ['file1', 'file2'];

        $component->clearUploadedFiles();

        expect($component->uploadedFiles)->toBe([]);
    });
});

describe('move', function () {
    it('sets move target', function () {
        $folder = FileSystemItem::factory()->folder()->create();

        $component = new EmbeddedFileManager();
        $component->mount();

        $component->setMoveTarget((string) $folder->id);

        expect($component->moveTargetPath)->toBe((string) $folder->id);
    });
});
