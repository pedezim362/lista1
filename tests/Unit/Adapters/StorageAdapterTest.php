<?php

namespace MWGuerra\FileManager\Tests\Unit\Adapters;

use Illuminate\Support\Facades\Storage;
use MWGuerra\FileManager\Adapters\StorageAdapter;
use MWGuerra\FileManager\Tests\TestCase;

class StorageAdapterTest extends TestCase
{
    protected StorageAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a fresh testing disk
        Storage::fake('testing');

        $this->adapter = new StorageAdapter('testing', '', false);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_get_item_returns_folder_for_directory(): void
    {
        // Create a directory
        Storage::disk('testing')->makeDirectory('my-folder');

        // Get the item
        $item = $this->adapter->getItem('my-folder');

        // Assert it's recognized as a folder
        $this->assertNotNull($item);
        $this->assertTrue($item->isFolder(), 'Directory should be recognized as a folder');
        $this->assertEquals('my-folder', $item->getName());
    }

    public function test_get_item_returns_file_for_file(): void
    {
        // Create a file
        Storage::disk('testing')->put('test-file.txt', 'Hello World');

        // Get the item
        $item = $this->adapter->getItem('test-file.txt');

        // Assert it's recognized as a file
        $this->assertNotNull($item);
        $this->assertFalse($item->isFolder(), 'File should not be recognized as a folder');
        $this->assertEquals('test-file.txt', $item->getName());
    }

    public function test_get_item_returns_null_for_nonexistent(): void
    {
        $item = $this->adapter->getItem('nonexistent-path');

        $this->assertNull($item);
    }

    public function test_get_item_returns_folder_for_nested_directory(): void
    {
        // Create nested directories
        Storage::disk('testing')->makeDirectory('parent/child');

        // Get the child folder
        $item = $this->adapter->getItem('parent/child');

        // Assert it's recognized as a folder
        $this->assertNotNull($item);
        $this->assertTrue($item->isFolder(), 'Nested directory should be recognized as a folder');
        $this->assertEquals('child', $item->getName());
    }

    public function test_get_item_returns_file_in_directory(): void
    {
        // Create a file in a directory
        Storage::disk('testing')->makeDirectory('folder');
        Storage::disk('testing')->put('folder/document.pdf', 'PDF content');

        // Get the file
        $item = $this->adapter->getItem('folder/document.pdf');

        // Assert it's recognized as a file
        $this->assertNotNull($item);
        $this->assertFalse($item->isFolder(), 'File in directory should not be a folder');
        $this->assertEquals('document.pdf', $item->getName());
    }

    public function test_get_items_returns_folders_and_files(): void
    {
        // Create structure
        Storage::disk('testing')->makeDirectory('folder1');
        Storage::disk('testing')->makeDirectory('folder2');
        Storage::disk('testing')->put('file1.txt', 'content');
        Storage::disk('testing')->put('file2.txt', 'content');

        $items = $this->adapter->getItems();

        // Should have 4 items
        $this->assertCount(4, $items);

        // First two should be folders (sorted alphabetically, folders first)
        $this->assertTrue($items[0]->isFolder());
        $this->assertTrue($items[1]->isFolder());

        // Last two should be files
        $this->assertFalse($items[2]->isFolder());
        $this->assertFalse($items[3]->isFolder());
    }

    public function test_get_folders_returns_only_folders(): void
    {
        // Create structure
        Storage::disk('testing')->makeDirectory('folder1');
        Storage::disk('testing')->makeDirectory('folder2');
        Storage::disk('testing')->put('file.txt', 'content');

        $folders = $this->adapter->getFolders();

        // Should only have 2 folders
        $this->assertCount(2, $folders);
        $this->assertTrue($folders[0]->isFolder());
        $this->assertTrue($folders[1]->isFolder());
    }

    public function test_hidden_folders_are_not_shown_by_default(): void
    {
        // Create visible and hidden folders
        Storage::disk('testing')->makeDirectory('visible-folder');
        Storage::disk('testing')->makeDirectory('.hidden-folder');

        $items = $this->adapter->getItems();

        // Should only have 1 visible folder
        $this->assertCount(1, $items);
        $this->assertEquals('visible-folder', $items[0]->getName());
    }

    public function test_hidden_folders_shown_when_enabled(): void
    {
        // Create adapter with show_hidden enabled
        $adapter = new StorageAdapter('testing', '', true);

        // Create visible and hidden folders
        Storage::disk('testing')->makeDirectory('visible-folder');
        Storage::disk('testing')->makeDirectory('.hidden-folder');

        $items = $adapter->getItems();

        // Should have both folders
        $this->assertCount(2, $items);
    }

    public function test_create_folder_creates_directory(): void
    {
        $result = $this->adapter->createFolder('new-folder');

        $this->assertNotNull($result);
        $this->assertNotEquals('A folder with this name already exists', $result);
        $this->assertTrue(Storage::disk('testing')->exists('new-folder'));
    }

    public function test_create_folder_returns_error_for_duplicate(): void
    {
        Storage::disk('testing')->makeDirectory('existing-folder');

        $result = $this->adapter->createFolder('existing-folder');

        $this->assertEquals('A folder with this name already exists', $result);
    }

    public function test_delete_removes_file(): void
    {
        Storage::disk('testing')->put('to-delete.txt', 'content');

        $result = $this->adapter->delete('to-delete.txt');

        $this->assertTrue($result);
        $this->assertFalse(Storage::disk('testing')->exists('to-delete.txt'));
    }

    public function test_delete_removes_directory(): void
    {
        Storage::disk('testing')->makeDirectory('to-delete-folder');
        Storage::disk('testing')->put('to-delete-folder/file.txt', 'content');

        $result = $this->adapter->delete('to-delete-folder');

        $this->assertTrue($result);
        $this->assertFalse(Storage::disk('testing')->exists('to-delete-folder'));
    }

    public function test_exists_returns_true_for_existing_file(): void
    {
        Storage::disk('testing')->put('exists.txt', 'content');

        $this->assertTrue($this->adapter->exists('exists.txt'));
    }

    public function test_exists_returns_true_for_existing_directory(): void
    {
        Storage::disk('testing')->makeDirectory('exists-folder');

        $this->assertTrue($this->adapter->exists('exists-folder'));
    }

    public function test_exists_returns_false_for_nonexistent(): void
    {
        $this->assertFalse($this->adapter->exists('does-not-exist'));
    }
}
