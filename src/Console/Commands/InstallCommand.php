<?php

namespace MWGuerra\FileManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filemanager:install
                            {--css-path= : Path to the CSS file (defaults to resources/css/app.css)}
                            {--force : Overwrite existing configurations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install FileManager CSS configuration for Tailwind CSS v4';

    /**
     * The @source directive to add.
     */
    protected string $sourceDirective = "@source '../../vendor/mwguerra/filemanager/resources/views/**/*.blade.php';";

    /**
     * The @variant dark directive to add.
     */
    protected string $variantDarkDirective = "@variant dark (&:where(.dark, .dark *));";

    /**
     * The primary color mappings for @theme block.
     */
    protected array $primaryColorMappings = [
        '--color-primary-50: var(--primary-50);',
        '--color-primary-100: var(--primary-100);',
        '--color-primary-200: var(--primary-200);',
        '--color-primary-300: var(--primary-300);',
        '--color-primary-400: var(--primary-400);',
        '--color-primary-500: var(--primary-500);',
        '--color-primary-600: var(--primary-600);',
        '--color-primary-700: var(--primary-700);',
        '--color-primary-800: var(--primary-800);',
        '--color-primary-900: var(--primary-900);',
        '--color-primary-950: var(--primary-950);',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cssPath = $this->option('css-path') ?? resource_path('css/app.css');
        $force = $this->option('force');

        $this->info('FileManager Installation');
        $this->info('========================');
        $this->newLine();

        // Check if CSS file exists
        if (!File::exists($cssPath)) {
            $this->error("CSS file not found at: {$cssPath}");
            $this->line('Please specify the correct path using --css-path option');
            return self::FAILURE;
        }

        $this->info("Processing: {$cssPath}");
        $this->newLine();

        // Read the CSS file
        $cssContent = File::get($cssPath);
        $originalContent = $cssContent;

        // Track what was added
        $added = [];
        $skipped = [];

        // 1. Add @source directive
        $result = $this->addSourceDirective($cssContent, $force);
        $cssContent = $result['content'];
        if ($result['added']) {
            $added[] = '@source directive for FileManager views';
        } elseif ($result['skipped']) {
            $skipped[] = '@source directive (already exists)';
        }

        // 2. Add @variant dark directive
        $result = $this->addVariantDarkDirective($cssContent, $force);
        $cssContent = $result['content'];
        if ($result['added']) {
            $added[] = '@variant dark directive for Filament dark mode';
        } elseif ($result['skipped']) {
            $skipped[] = '@variant dark directive (already exists)';
        }

        // 3. Add primary color mappings to @theme block
        $result = $this->addPrimaryColorMappings($cssContent, $force);
        $cssContent = $result['content'];
        if ($result['added']) {
            $added[] = 'Primary color mappings in @theme block';
        } elseif ($result['skipped']) {
            $skipped[] = 'Primary color mappings (already exist)';
        }

        // Check if anything changed
        if ($cssContent === $originalContent) {
            $this->info('No changes needed. Your CSS is already configured for FileManager.');
            return self::SUCCESS;
        }

        // Write the updated content
        File::put($cssPath, $cssContent);

        // Show results
        if (!empty($added)) {
            $this->info('Added:');
            foreach ($added as $item) {
                $this->line("  ✓ {$item}");
            }
            $this->newLine();
        }

        if (!empty($skipped)) {
            $this->warn('Skipped (already configured):');
            foreach ($skipped as $item) {
                $this->line("  - {$item}");
            }
            $this->newLine();
        }

        $this->info('FileManager CSS configuration installed successfully!');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Run: npm run build (or npm run dev)');
        $this->line('  2. Clear cache: php artisan optimize:clear');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Add the @source directive for FileManager views.
     */
    protected function addSourceDirective(string $content, bool $force): array
    {
        // Check if already exists
        if (str_contains($content, 'mwguerra/filemanager')) {
            if (!$force) {
                return ['content' => $content, 'added' => false, 'skipped' => true];
            }
        }

        // Find the best position to insert (after other @source directives or after @import)
        $lines = explode("\n", $content);
        $insertIndex = null;
        $lastSourceIndex = null;
        $lastImportIndex = null;

        foreach ($lines as $index => $line) {
            $trimmedLine = trim($line);
            if (str_starts_with($trimmedLine, '@source')) {
                $lastSourceIndex = $index;
            }
            if (str_starts_with($trimmedLine, '@import')) {
                $lastImportIndex = $index;
            }
        }

        // Determine insertion point
        if ($lastSourceIndex !== null) {
            $insertIndex = $lastSourceIndex + 1;
        } elseif ($lastImportIndex !== null) {
            $insertIndex = $lastImportIndex + 1;
            // Add a blank line after imports before the source directive
            array_splice($lines, $insertIndex, 0, ['']);
            $insertIndex++;
        } else {
            // Insert at the beginning
            $insertIndex = 0;
        }

        // Insert the source directive
        array_splice($lines, $insertIndex, 0, [$this->sourceDirective]);

        return [
            'content' => implode("\n", $lines),
            'added' => true,
            'skipped' => false,
        ];
    }

    /**
     * Add the @variant dark directive for Filament dark mode.
     */
    protected function addVariantDarkDirective(string $content, bool $force): array
    {
        // Check if already exists
        if (str_contains($content, '@variant dark')) {
            if (!$force) {
                return ['content' => $content, 'added' => false, 'skipped' => true];
            }
        }

        // Find the position to insert (after @source directives, before @theme)
        $lines = explode("\n", $content);
        $insertIndex = null;
        $lastSourceIndex = null;
        $themeIndex = null;

        foreach ($lines as $index => $line) {
            $trimmedLine = trim($line);
            if (str_starts_with($trimmedLine, '@source')) {
                $lastSourceIndex = $index;
            }
            if (str_starts_with($trimmedLine, '@theme')) {
                $themeIndex = $index;
            }
        }

        // Determine insertion point
        if ($themeIndex !== null) {
            // Insert before @theme block, with comment
            $insertIndex = $themeIndex;
            // Add blank line and comment
            $toInsert = [
                '',
                '/* Configure dark mode to use Filament\'s .dark class selector instead of prefers-color-scheme */',
                $this->variantDarkDirective,
            ];
        } elseif ($lastSourceIndex !== null) {
            // Insert after last @source
            $insertIndex = $lastSourceIndex + 1;
            $toInsert = [
                '',
                '/* Configure dark mode to use Filament\'s .dark class selector instead of prefers-color-scheme */',
                $this->variantDarkDirective,
            ];
        } else {
            // Insert at the end
            $insertIndex = count($lines);
            $toInsert = [
                '',
                '/* Configure dark mode to use Filament\'s .dark class selector instead of prefers-color-scheme */',
                $this->variantDarkDirective,
            ];
        }

        // Insert the lines
        array_splice($lines, $insertIndex, 0, $toInsert);

        return [
            'content' => implode("\n", $lines),
            'added' => true,
            'skipped' => false,
        ];
    }

    /**
     * Add primary color mappings to @theme block.
     */
    protected function addPrimaryColorMappings(string $content, bool $force): array
    {
        // Check if primary color mappings already exist
        if (str_contains($content, '--color-primary-500: var(--primary-500)')) {
            if (!$force) {
                return ['content' => $content, 'added' => false, 'skipped' => true];
            }
        }

        // Check if @theme block exists
        if (str_contains($content, '@theme')) {
            // Insert into existing @theme block
            return $this->insertIntoThemeBlock($content);
        } else {
            // Create new @theme block
            return $this->createThemeBlock($content);
        }
    }

    /**
     * Insert primary color mappings into existing @theme block.
     */
    protected function insertIntoThemeBlock(string $content): array
    {
        $lines = explode("\n", $content);
        $themeStartIndex = null;
        $themeEndIndex = null;
        $braceCount = 0;
        $inTheme = false;

        // Find the @theme block boundaries
        foreach ($lines as $index => $line) {
            $trimmedLine = trim($line);

            if (str_starts_with($trimmedLine, '@theme')) {
                $themeStartIndex = $index;
                $inTheme = true;
            }

            if ($inTheme) {
                $braceCount += substr_count($line, '{');
                $braceCount -= substr_count($line, '}');

                if ($braceCount === 0 && $themeStartIndex !== null && $index > $themeStartIndex) {
                    $themeEndIndex = $index;
                    break;
                }
            }
        }

        if ($themeStartIndex === null || $themeEndIndex === null) {
            // Couldn't parse @theme block, create a new one
            return $this->createThemeBlock($content);
        }

        // Build the color mappings string with proper indentation
        $mappingsComment = "\n    /* Map Filament's primary color custom properties to Tailwind color utilities */";
        $mappings = '';
        foreach ($this->primaryColorMappings as $mapping) {
            $mappings .= "\n    {$mapping}";
        }

        // Insert before the closing brace of @theme
        $insertIndex = $themeEndIndex;
        array_splice($lines, $insertIndex, 0, [$mappingsComment . $mappings]);

        return [
            'content' => implode("\n", $lines),
            'added' => true,
            'skipped' => false,
        ];
    }

    /**
     * Create a new @theme block with primary color mappings.
     */
    protected function createThemeBlock(string $content): array
    {
        $themeBlock = "\n@theme {\n";
        $themeBlock .= "    /* Map Filament's primary color custom properties to Tailwind color utilities */\n";
        foreach ($this->primaryColorMappings as $mapping) {
            $themeBlock .= "    {$mapping}\n";
        }
        $themeBlock .= "}\n";

        // Append to the end of the file
        $content = rtrim($content) . "\n" . $themeBlock;

        return [
            'content' => $content,
            'added' => true,
            'skipped' => false,
        ];
    }
}
