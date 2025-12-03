<?php

use Illuminate\Support\Facades\Storage;
use MWGuerra\FileManager\Adapters\StorageAdapter;

beforeEach(function () {
    Storage::fake('testing');
    $this->adapter = new StorageAdapter('testing', '', false);
});

it('returns folder for directory', function () {
    Storage::disk('testing')->makeDirectory('my-folder');

    $item = $this->adapter->getItem('my-folder');

    expect($item)->not->toBeNull()
        ->and($item->isFolder())->toBeTrue()
        ->and($item->getName())->toBe('my-folder');
});

it('returns file for file', function () {
    Storage::disk('testing')->put('test-file.txt', 'Hello World');

    $item = $this->adapter->getItem('test-file.txt');

    expect($item)->not->toBeNull()
        ->and($item->isFolder())->toBeFalse()
        ->and($item->getName())->toBe('test-file.txt');
});

it('returns null for nonexistent path', function () {
    $item = $this->adapter->getItem('nonexistent-path');

    expect($item)->toBeNull();
});

it('returns folder for nested directory', function () {
    Storage::disk('testing')->makeDirectory('parent/child');

    $item = $this->adapter->getItem('parent/child');

    expect($item)->not->toBeNull()
        ->and($item->isFolder())->toBeTrue()
        ->and($item->getName())->toBe('child');
});

it('returns file in directory', function () {
    Storage::disk('testing')->makeDirectory('folder');
    Storage::disk('testing')->put('folder/document.pdf', 'PDF content');

    $item = $this->adapter->getItem('folder/document.pdf');

    expect($item)->not->toBeNull()
        ->and($item->isFolder())->toBeFalse()
        ->and($item->getName())->toBe('document.pdf');
});

it('returns folders and files with correct order', function () {
    Storage::disk('testing')->makeDirectory('folder1');
    Storage::disk('testing')->makeDirectory('folder2');
    Storage::disk('testing')->put('file1.txt', 'content');
    Storage::disk('testing')->put('file2.txt', 'content');

    $items = $this->adapter->getItems();

    expect($items)->toHaveCount(4)
        ->and($items[0]->isFolder())->toBeTrue()
        ->and($items[1]->isFolder())->toBeTrue()
        ->and($items[2]->isFolder())->toBeFalse()
        ->and($items[3]->isFolder())->toBeFalse();
});

it('returns only folders when requested', function () {
    Storage::disk('testing')->makeDirectory('folder1');
    Storage::disk('testing')->makeDirectory('folder2');
    Storage::disk('testing')->put('file.txt', 'content');

    $folders = $this->adapter->getFolders();

    expect($folders)->toHaveCount(2)
        ->and($folders[0]->isFolder())->toBeTrue()
        ->and($folders[1]->isFolder())->toBeTrue();
});

it('hides hidden folders by default', function () {
    Storage::disk('testing')->makeDirectory('visible-folder');
    Storage::disk('testing')->makeDirectory('.hidden-folder');

    $items = $this->adapter->getItems();

    expect($items)->toHaveCount(1)
        ->and($items[0]->getName())->toBe('visible-folder');
});

it('shows hidden folders when enabled', function () {
    $adapter = new StorageAdapter('testing', '', true);

    Storage::disk('testing')->makeDirectory('visible-folder');
    Storage::disk('testing')->makeDirectory('.hidden-folder');

    $items = $adapter->getItems();

    expect($items)->toHaveCount(2);
});

it('creates folder successfully', function () {
    $result = $this->adapter->createFolder('new-folder');

    expect($result)->not->toBeNull()
        ->and($result)->not->toBe('A folder with this name already exists')
        ->and(Storage::disk('testing')->exists('new-folder'))->toBeTrue();
});

it('returns error for duplicate folder', function () {
    Storage::disk('testing')->makeDirectory('existing-folder');

    $result = $this->adapter->createFolder('existing-folder');

    expect($result)->toBe('A folder with this name already exists');
});

it('deletes file successfully', function () {
    Storage::disk('testing')->put('to-delete.txt', 'content');

    $result = $this->adapter->delete('to-delete.txt');

    expect($result)->toBeTrue()
        ->and(Storage::disk('testing')->exists('to-delete.txt'))->toBeFalse();
});

it('deletes directory with contents', function () {
    Storage::disk('testing')->makeDirectory('to-delete-folder');
    Storage::disk('testing')->put('to-delete-folder/file.txt', 'content');

    $result = $this->adapter->delete('to-delete-folder');

    expect($result)->toBeTrue()
        ->and(Storage::disk('testing')->exists('to-delete-folder'))->toBeFalse();
});

it('returns true for existing file', function () {
    Storage::disk('testing')->put('exists.txt', 'content');

    expect($this->adapter->exists('exists.txt'))->toBeTrue();
});

it('returns true for existing directory', function () {
    Storage::disk('testing')->makeDirectory('exists-folder');

    expect($this->adapter->exists('exists-folder'))->toBeTrue();
});

it('returns false for nonexistent path', function () {
    expect($this->adapter->exists('does-not-exist'))->toBeFalse();
});
