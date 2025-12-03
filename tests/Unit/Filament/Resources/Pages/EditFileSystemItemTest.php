<?php

use MWGuerra\FileManager\Filament\Resources\FileSystemItemResource;
use MWGuerra\FileManager\Filament\Resources\FileSystemItemResource\Pages\EditFileSystemItem;
use MWGuerra\FileManager\Models\FileSystemItem;

beforeEach(function () {
    $this->app['config']->set('filemanager.model', FileSystemItem::class);
});

describe('page configuration', function () {
    it('has correct resource class', function () {
        $page = new EditFileSystemItem();

        // Using reflection to access protected static property
        $reflection = new ReflectionClass($page);
        $property = $reflection->getProperty('resource');

        expect($property->getValue($page))->toBe(FileSystemItemResource::class);
    });

    it('extends EditRecord', function () {
        expect(is_subclass_of(EditFileSystemItem::class, \Filament\Resources\Pages\EditRecord::class))->toBeTrue();
    });
});

describe('header actions', function () {
    it('has getHeaderActions method', function () {
        expect(method_exists(EditFileSystemItem::class, 'getHeaderActions'))->toBeTrue();
    });
});
