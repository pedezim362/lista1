<?php

namespace MWGuerra\FileManager\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MWGuerra\FileManager\FileManagerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
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

    protected function getEnvironmentSetUp($app): void
    {
        // Use a testing disk for storage tests
        $app['config']->set('filesystems.disks.testing', [
            'driver' => 'local',
            'root' => storage_path('framework/testing'),
        ]);

        $app['config']->set('filemanager.storage_mode.disk', 'testing');
        $app['config']->set('filemanager.storage_mode.root', '');
        $app['config']->set('filemanager.storage_mode.show_hidden', false);
    }
}
