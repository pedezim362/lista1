<?php

namespace MWGuerra\FileManager\Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
use MWGuerra\FileManager\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    protected static string $testCssPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temp directory for test CSS files
        static::$testCssPath = sys_get_temp_dir() . '/filemanager-install-test-' . uniqid();
        mkdir(static::$testCssPath, 0777, true);
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (isset(static::$testCssPath) && is_dir(static::$testCssPath)) {
            $this->deleteDirectory(static::$testCssPath);
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

    private function createTestCssFile(string $content): string
    {
        $path = static::$testCssPath . '/app.css';
        file_put_contents($path, $content);
        return $path;
    }

    public function test_install_command_fails_with_nonexistent_file(): void
    {
        $this->artisan('filemanager:install', [
            '--css-path' => '/nonexistent/path/app.css',
        ])
            ->assertExitCode(1)
            ->expectsOutputToContain('CSS file not found');
    }

    public function test_install_command_adds_source_directive(): void
    {
        $cssPath = $this->createTestCssFile("@import 'tailwindcss';\n");

        $this->artisan('filemanager:install', [
            '--css-path' => $cssPath,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('@source directive');

        $content = file_get_contents($cssPath);
        $this->assertStringContainsString('mwguerra/filemanager', $content);
    }

    public function test_install_command_adds_variant_dark_directive(): void
    {
        $cssPath = $this->createTestCssFile("@import 'tailwindcss';\n");

        $this->artisan('filemanager:install', [
            '--css-path' => $cssPath,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('@variant dark directive');

        $content = file_get_contents($cssPath);
        $this->assertStringContainsString('@variant dark', $content);
        $this->assertStringContainsString('.dark', $content);
    }

    public function test_install_command_adds_primary_color_mappings(): void
    {
        $cssPath = $this->createTestCssFile("@import 'tailwindcss';\n");

        $this->artisan('filemanager:install', [
            '--css-path' => $cssPath,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('Primary color mappings');

        $content = file_get_contents($cssPath);
        $this->assertStringContainsString('@theme', $content);
        $this->assertStringContainsString('--color-primary-500', $content);
        $this->assertStringContainsString('var(--primary-500)', $content);
    }

    public function test_install_command_inserts_into_existing_theme_block(): void
    {
        $cssPath = $this->createTestCssFile(<<<CSS
@import 'tailwindcss';

@theme {
    --font-sans: 'Inter', sans-serif;
}
CSS);

        $this->artisan('filemanager:install', [
            '--css-path' => $cssPath,
        ])
            ->assertExitCode(0);

        $content = file_get_contents($cssPath);
        // Should only have one @theme block
        $this->assertEquals(1, substr_count($content, '@theme'));
        // Should contain both the original and new values
        $this->assertStringContainsString('--font-sans', $content);
        $this->assertStringContainsString('--color-primary-500', $content);
    }

    public function test_install_command_skips_if_already_configured(): void
    {
        $cssContent = <<<CSS
@import 'tailwindcss';

@source '../../vendor/mwguerra/filemanager/resources/views/**/*.blade.php';

@variant dark (&:where(.dark, .dark *));

@theme {
    --color-primary-500: var(--primary-500);
}
CSS;
        $cssPath = $this->createTestCssFile($cssContent);

        $this->artisan('filemanager:install', [
            '--css-path' => $cssPath,
        ])
            ->assertExitCode(0)
            ->expectsOutputToContain('No changes needed');
    }

    public function test_install_command_adds_source_after_existing_sources(): void
    {
        $cssPath = $this->createTestCssFile(<<<CSS
@import 'tailwindcss';

@source '../**/*.blade.php';
@source '../**/*.js';

@theme {
    --font-sans: 'Inter', sans-serif;
}
CSS);

        $this->artisan('filemanager:install', [
            '--css-path' => $cssPath,
        ])
            ->assertExitCode(0);

        $content = file_get_contents($cssPath);
        $lines = explode("\n", $content);

        // Find positions of @source directives
        $sourcePositions = [];
        foreach ($lines as $index => $line) {
            if (str_contains($line, '@source')) {
                $sourcePositions[] = $index;
            }
        }

        // The filemanager source should be after the existing sources
        $this->assertCount(3, $sourcePositions);
    }

    public function test_install_command_places_variant_dark_before_theme(): void
    {
        $cssPath = $this->createTestCssFile(<<<CSS
@import 'tailwindcss';

@source '../**/*.blade.php';

@theme {
    --font-sans: 'Inter', sans-serif;
}
CSS);

        $this->artisan('filemanager:install', [
            '--css-path' => $cssPath,
        ])
            ->assertExitCode(0);

        $content = file_get_contents($cssPath);

        // @variant dark should appear before @theme
        $variantPos = strpos($content, '@variant dark');
        $themePos = strpos($content, '@theme');

        $this->assertNotFalse($variantPos);
        $this->assertNotFalse($themePos);
        $this->assertLessThan($themePos, $variantPos);
    }

    public function test_install_command_with_force_option(): void
    {
        // First install
        $cssPath = $this->createTestCssFile("@import 'tailwindcss';\n");

        $this->artisan('filemanager:install', [
            '--css-path' => $cssPath,
        ])->assertExitCode(0);

        // Modify the file to have partial config
        $content = file_get_contents($cssPath);
        $content = str_replace('--color-primary-500', '--color-primary-MODIFIED', $content);
        file_put_contents($cssPath, $content);

        // Run with --force should re-add everything
        $this->artisan('filemanager:install', [
            '--css-path' => $cssPath,
            '--force' => true,
        ])->assertExitCode(0);

        $content = file_get_contents($cssPath);
        // Should have the original value again (though the modified one may also remain)
        $this->assertStringContainsString('--color-primary-500: var(--primary-500)', $content);
    }
}
