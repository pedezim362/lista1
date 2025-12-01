<?php

namespace MWGuerra\FileManager\Tests\Feature\Commands;

use MWGuerra\FileManager\Tests\TestCase;

class ListFilesCommandTest extends TestCase
{
    protected static string $testPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test disk that points to a temp directory
        static::$testPath = sys_get_temp_dir() . '/filemanager-test-' . uniqid();
        mkdir(static::$testPath, 0777, true);

        $this->app['config']->set('filesystems.disks.testing', [
            'driver' => 'local',
            'root' => static::$testPath,
        ]);

        // Create test folder structure
        mkdir(static::$testPath . '/folder1', 0777, true);
        mkdir(static::$testPath . '/folder2', 0777, true);
        mkdir(static::$testPath . '/folder1/subfolder', 0777, true);
        file_put_contents(static::$testPath . '/file1.txt', 'content');
        file_put_contents(static::$testPath . '/folder1/file2.txt', 'content');
        file_put_contents(static::$testPath . '/.hidden', 'hidden content');
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (isset(static::$testPath) && is_dir(static::$testPath)) {
            $this->deleteDirectory(static::$testPath);
        }
        parent::tearDown();
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function test_list_command_runs_successfully_in_storage_mode(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
        ])
            ->assertExitCode(0);
    }

    public function test_list_command_fails_with_invalid_mode(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'invalid',
            '--disk' => 'testing',
        ])
            ->assertExitCode(1)
            ->expectsOutputToContain("Invalid mode 'invalid'");
    }

    public function test_list_command_fails_with_invalid_type(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
            '--type' => 'invalid',
        ])
            ->assertExitCode(1)
            ->expectsOutputToContain("Invalid type 'invalid'");
    }

    public function test_list_command_fails_with_invalid_format(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
            '--format' => 'invalid',
        ])
            ->assertExitCode(1)
            ->expectsOutputToContain("Invalid format 'invalid'");
    }

    public function test_list_command_fails_with_nonexistent_disk(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'nonexistent',
        ])
            ->assertExitCode(1)
            ->expectsOutputToContain('not configured or accessible');
    }

    public function test_list_command_outputs_table_format(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
            '--format' => 'table',
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('Summary');
    }

    public function test_list_command_outputs_json_format_structure(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
            '--format' => 'json',
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('"items"');
    }

    public function test_list_command_outputs_csv_format_header(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
            '--format' => 'csv',
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('type,name,id,path,size,modified');
    }

    public function test_list_command_with_folder_type_filter(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
            '--type' => 'folder',
        ])
            ->assertExitCode(0);
    }

    public function test_list_command_with_file_type_filter(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
            '--type' => 'file',
        ])
            ->assertExitCode(0);
    }

    public function test_list_command_with_recursive_option(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
            '--recursive' => true,
        ])
            ->assertExitCode(0);
    }

    public function test_list_command_with_show_hidden_option(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
            '--show-hidden' => true,
        ])
            ->assertExitCode(0);
    }

    public function test_list_command_with_path_argument(): void
    {
        $this->artisan('filemanager:list', [
            'path' => 'folder1',
            '--mode' => 'storage',
            '--disk' => 'testing',
        ])
            ->assertExitCode(0);
    }

    public function test_list_command_with_target_option(): void
    {
        $this->artisan('filemanager:list', [
            '--mode' => 'storage',
            '--disk' => 'testing',
            '--target' => 'folder1',
        ])
            ->assertExitCode(0);
    }
}
