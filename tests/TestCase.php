<?php

namespace MWGuerra\FileManager\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MWGuerra\FileManager\FileManagerServiceProvider;
use MWGuerra\FileManager\Models\FileSystemItem;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            FileManagerServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Use SQLite in-memory database for testing
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Use a testing disk for storage tests
        $app['config']->set('filesystems.disks.testing', [
            'driver' => 'local',
            'root' => storage_path('framework/testing'),
        ]);

        // FileManager configuration
        $app['config']->set('filemanager.mode', 'storage');
        $app['config']->set('filemanager.model', FileSystemItem::class);
        $app['config']->set('filemanager.storage_mode.disk', 'testing');
        $app['config']->set('filemanager.storage_mode.root', '');
        $app['config']->set('filemanager.storage_mode.show_hidden', false);
        $app['config']->set('filemanager.upload.disk', 'testing');
        $app['config']->set('filemanager.upload.directory', 'uploads');
    }
}
