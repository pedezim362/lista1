<?php

use MWGuerra\FileManager\Livewire\EmbeddedFileSystem;
use MWGuerra\FileManager\Models\FileSystemItem;

beforeEach(function () {
    $this->testPath = sys_get_temp_dir() . '/filemanager-embedded-unit-' . uniqid();
    mkdir($this->testPath, 0777, true);

    $this->app['config']->set('filesystems.disks.testing', [
        'driver' => 'local',
        'root' => $this->testPath,
    ]);

    $this->app['config']->set('filemanager.mode', 'storage');
    $this->app['config']->set('filemanager.model', FileSystemItem::class);
    $this->app['config']->set('filemanager.storage_mode.disk', 'testing');

    // Create test folder structure
    mkdir($this->testPath . '/documents', 0777, true);
    mkdir($this->testPath . '/images', 0777, true);
    file_put_contents($this->testPath . '/readme.txt', 'readme content');
    file_put_contents($this->testPath . '/documents/contract.pdf', 'pdf content');
});

afterEach(function () {
    if (isset($this->testPath) && is_dir($this->testPath)) {
        deleteDirectory($this->testPath);
    }
});

describe('mount', function () {
    it('mounts with default configuration', function () {
        $component = new EmbeddedFileSystem();
        $component->mount();

        expect($component->height)->toBe('500px');
        expect($component->showHeader)->toBeTrue();
        expect($component->showSidebar)->toBeTrue();
        expect($component->defaultViewMode)->toBe('grid');
        expect($component->viewMode)->toBe('grid');
        expect($component->currentPath)->toBeNull();
    });

    it('mounts with custom configuration', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(
            height: '600px',
            showHeader: false,
            showSidebar: false,
            defaultViewMode: 'list',
            disk: 'testing',
            target: 'uploads',
            initialFolder: 'documents'
        );

        expect($component->height)->toBe('600px');
        expect($component->showHeader)->toBeFalse();
        expect($component->showSidebar)->toBeFalse();
        expect($component->defaultViewMode)->toBe('list');
        expect($component->disk)->toBe('testing');
        expect($component->target)->toBe('uploads');
        expect($component->currentPath)->toBe('documents');
    });
});

describe('mode', function () {
    it('returns storage mode', function () {
        $component = new EmbeddedFileSystem();
        $component->mount();

        expect($component->getMode())->toBe('storage');
    });

    it('is always read-only', function () {
        $component = new EmbeddedFileSystem();
        $component->mount();

        expect($component->isReadOnly())->toBeTrue();
    });
});

describe('navigation', function () {
    it('navigates to a folder', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(disk: 'testing');

        $component->navigateTo('documents');

        expect($component->currentPath)->toBe('documents');
        expect($component->selectedItems)->toBe([]);
    });

    it('navigates to root', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(disk: 'testing');
        $component->currentPath = 'documents';

        $component->navigateTo(null);

        expect($component->currentPath)->toBeNull();
    });
});

describe('folder expansion', function () {
    it('toggles folder expansion', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(disk: 'testing');

        expect($component->expandedFolders)->toBe(['root']);

        $component->toggleFolder('documents');
        expect($component->expandedFolders)->toContain('documents');

        $component->toggleFolder('documents');
        expect($component->expandedFolders)->not->toContain('documents');
    });

    it('checks if folder is expanded', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(disk: 'testing');

        expect($component->isFolderExpanded(null))->toBeTrue();
        expect($component->isFolderExpanded('documents'))->toBeFalse();

        $component->toggleFolder('documents');

        expect($component->isFolderExpanded('documents'))->toBeTrue();
    });
});

describe('view mode', function () {
    it('sets view mode to list', function () {
        $component = new EmbeddedFileSystem();
        $component->mount();

        expect($component->viewMode)->toBe('grid');

        $component->setViewMode('list');

        expect($component->viewMode)->toBe('list');
    });

    it('sets view mode to grid', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(defaultViewMode: 'list');

        expect($component->viewMode)->toBe('list');

        $component->setViewMode('grid');

        expect($component->viewMode)->toBe('grid');
    });
});

describe('selection', function () {
    it('toggles single item selection', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(disk: 'testing');

        $component->toggleSelection('readme.txt');
        expect($component->selectedItems)->toBe(['readme.txt']);

        $component->toggleSelection('readme.txt');
        expect($component->selectedItems)->toBe([]);
    });

    it('toggles multi-selection', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(disk: 'testing');

        $component->toggleSelection('readme.txt', true);
        $component->toggleSelection('documents', true);

        expect($component->selectedItems)->toBe(['readme.txt', 'documents']);
    });

    it('clears selection', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(disk: 'testing');
        $component->selectedItems = ['readme.txt'];

        $component->clearSelection();

        expect($component->selectedItems)->toBe([]);
    });

    it('checks if item is selected', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(disk: 'testing');
        $component->toggleSelection('readme.txt');

        expect($component->isSelected('readme.txt'))->toBeTrue();
        expect($component->isSelected('documents'))->toBeFalse();
    });
});

describe('read-only behavior', function () {
    it('does not record changes in read-only mode', function () {
        $component = new EmbeddedFileSystem();
        $component->mount(disk: 'testing');

        // Since it's read-only, the component shouldn't have any changes
        expect($component->getChanges())->toHaveCount(0);
    });
});
