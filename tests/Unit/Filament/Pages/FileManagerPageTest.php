<?php

use Illuminate\Support\Facades\Storage;
use MWGuerra\FileManager\Filament\Pages\FileManager;
use MWGuerra\FileManager\Models\FileSystemItem;

beforeEach(function () {
    Storage::fake('testing');
    $this->app['config']->set('filemanager.mode', 'database');
    $this->app['config']->set('filemanager.model', FileSystemItem::class);
    $this->app['config']->set('filemanager.storage_mode.disk', 'testing');
    $this->app['config']->set('filemanager.upload.disk', 'testing');
    $this->app['config']->set('filemanager.upload.max_file_size', 10240);
    $this->app['config']->set('filemanager.file_manager.navigation.label', 'File Manager');
    $this->app['config']->set('filemanager.file_manager.navigation.icon', 'heroicon-o-folder');
    $this->app['config']->set('filemanager.file_manager.navigation.sort', 1);
    $this->app['config']->set('filemanager.file_manager.navigation.group', 'FileManager');
});

describe('page configuration', function () {
    it('has correct navigation icon', function () {
        expect(FileManager::getNavigationIcon())->toBe('heroicon-o-folder');
    });

    it('has correct navigation label', function () {
        expect(FileManager::getNavigationLabel())->toBe('File Manager');
    });

    it('has correct navigation sort', function () {
        expect(FileManager::getNavigationSort())->toBe(1);
    });

    it('has correct navigation group', function () {
        expect(FileManager::getNavigationGroup())->toBe('FileManager');
    });
});

describe('mount', function () {
    it('mounts with default state', function () {
        $page = new FileManager();
        $page->mount();

        expect($page->currentPath)->toBeNull();
        expect($page->viewMode)->toBe('grid');
        expect($page->selectedItems)->toBe([]);
        expect($page->expandedFolders)->toBe(['root']);
    });
});

describe('mode', function () {
    it('returns configured mode', function () {
        $page = new FileManager();

        expect($page->getMode())->toBe('database');
    });

    it('detects database mode', function () {
        $page = new FileManager();

        expect($page->isDatabaseMode())->toBeTrue();
        expect($page->isStorageMode())->toBeFalse();
    });

    it('is not read-only', function () {
        $page = new FileManager();

        expect($page->isReadOnly())->toBeFalse();
    });
});

describe('navigation', function () {
    it('navigates to a folder', function () {
        $folder = FileSystemItem::factory()->folder()->create();

        $page = new FileManager();
        $page->mount();
        $page->navigateTo((string) $folder->id);

        expect($page->currentPath)->toBe((string) $folder->id);
        expect($page->selectedItems)->toBe([]);
    });

    it('navigates to folder by id', function () {
        $folder = FileSystemItem::factory()->folder()->create();

        $page = new FileManager();
        $page->mount();
        $page->navigateToId($folder->id);

        expect($page->currentPath)->toBe((string) $folder->id);
    });

    it('navigates to root by id', function () {
        $page = new FileManager();
        $page->mount();
        $page->currentPath = '1';

        $page->navigateToId(null);

        expect($page->currentPath)->toBeNull();
    });
});

describe('folder expansion', function () {
    it('toggles folder expansion', function () {
        $folder = FileSystemItem::factory()->folder()->create();

        $page = new FileManager();
        $page->mount();

        expect($page->expandedFolders)->toBe(['root']);

        $page->toggleFolder((string) $folder->id);
        expect($page->expandedFolders)->toContain((string) $folder->id);

        $page->toggleFolder((string) $folder->id);
        expect($page->expandedFolders)->not->toContain((string) $folder->id);
    });

    it('checks if folder is expanded', function () {
        $folder = FileSystemItem::factory()->folder()->create();

        $page = new FileManager();
        $page->mount();

        expect($page->isFolderExpanded(null))->toBeTrue();
        expect($page->isFolderExpanded((string) $folder->id))->toBeFalse();

        $page->toggleFolder((string) $folder->id);

        expect($page->isFolderExpanded((string) $folder->id))->toBeTrue();
    });
});

describe('view mode', function () {
    it('sets view mode', function () {
        $page = new FileManager();
        $page->mount();

        expect($page->viewMode)->toBe('grid');

        $page->setViewMode('list');

        expect($page->viewMode)->toBe('list');
    });
});

describe('selection', function () {
    it('toggles single item selection', function () {
        $file = FileSystemItem::factory()->create();

        $page = new FileManager();
        $page->mount();

        $page->toggleSelection((string) $file->id);
        expect($page->selectedItems)->toBe([(string) $file->id]);

        $page->toggleSelection((string) $file->id);
        expect($page->selectedItems)->toBe([]);
    });

    it('toggles multi-selection', function () {
        $file1 = FileSystemItem::factory()->create();
        $file2 = FileSystemItem::factory()->create();

        $page = new FileManager();
        $page->mount();

        $page->toggleSelection((string) $file1->id, true);
        $page->toggleSelection((string) $file2->id, true);

        expect($page->selectedItems)->toBe([(string) $file1->id, (string) $file2->id]);
    });

    it('clears selection', function () {
        $file = FileSystemItem::factory()->create();

        $page = new FileManager();
        $page->mount();
        $page->selectedItems = [(string) $file->id];

        $page->clearSelection();

        expect($page->selectedItems)->toBe([]);
    });

    it('checks if item is selected', function () {
        $file = FileSystemItem::factory()->create();

        $page = new FileManager();
        $page->mount();
        $page->toggleSelection((string) $file->id);

        expect($page->isSelected((string) $file->id))->toBeTrue();
    });
});

describe('upload', function () {
    it('clears uploaded files', function () {
        $page = new FileManager();
        $page->mount();
        $page->uploadedFiles = ['file1', 'file2'];

        $page->clearUploadedFiles();

        expect($page->uploadedFiles)->toBe([]);
    });
});

describe('move', function () {
    it('sets move target', function () {
        $folder = FileSystemItem::factory()->folder()->create();

        $page = new FileManager();
        $page->mount();

        $page->setMoveTarget((string) $folder->id);

        expect($page->moveTargetPath)->toBe((string) $folder->id);
    });
});

describe('storage disk property', function () {
    it('returns storage disk in storage mode', function () {
        $this->app['config']->set('filemanager.mode', 'storage');
        $this->app['config']->set('filemanager.storage_mode.disk', 'public');

        $page = new FileManager();

        expect($page->storageDisk)->toBe('public');
    });

    it('returns null storage disk in database mode', function () {
        $this->app['config']->set('filemanager.mode', 'database');

        $page = new FileManager();

        expect($page->storageDisk)->toBeNull();
    });
});
