<?php

namespace MWGuerra\FileManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use MWGuerra\FileManager\Filament\Pages\FileManager;
use MWGuerra\FileManager\Filament\Pages\FileSystem;
use MWGuerra\FileManager\Filament\Pages\SchemaExample;
use MWGuerra\FileManager\Filament\Resources\FileSystemItemResource;

class FileManagerPlugin implements Plugin
{
    /**
     * Components to register (pages and resources).
     * If null, all enabled components are registered based on config.
     *
     * @var array<class-string>|null
     */
    protected ?array $components = null;

    /**
     * All available page classes.
     *
     * @var array<class-string>
     */
    protected array $availablePages = [
        FileManager::class,
        FileSystem::class,
        SchemaExample::class,
    ];

    /**
     * All available resource classes.
     *
     * @var array<class-string>
     */
    protected array $availableResources = [
        FileSystemItemResource::class,
    ];

    public function getId(): string
    {
        return 'filemanager';
    }

    public function register(Panel $panel): void
    {
        $pages = [];
        $resources = [];

        if ($this->components !== null) {
            // Register only specified components
            foreach ($this->components as $component) {
                if (in_array($component, $this->availablePages, true)) {
                    $pages[] = $component;
                } elseif (in_array($component, $this->availableResources, true)) {
                    $resources[] = $component;
                }
            }
        } else {
            // Register all components based on config (default behavior)
            if (config('filemanager.file_manager.enabled', true)) {
                $pages[] = FileManager::class;
            }

            if (config('filemanager.file_system.enabled', true)) {
                $pages[] = FileSystem::class;
            }

            if (config('filemanager.schema_example.enabled', true)) {
                $pages[] = SchemaExample::class;
            }

            $resources[] = FileSystemItemResource::class;
        }

        if (!empty($pages)) {
            $panel->pages($pages);
        }

        if (!empty($resources)) {
            $panel->resources($resources);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Create a new plugin instance.
     *
     * @param array<class-string>|null $components Optional array of page/resource classes to register.
     *                                              If null, all enabled components are registered.
     *
     * @example
     * // Register all enabled components (based on config)
     * FileManagerPlugin::make()
     *
     * @example
     * // Register only specific components
     * FileManagerPlugin::make([
     *     FileManager::class,
     *     FileSystem::class,
     * ])
     *
     * @example
     * // Register only the FileManager page and FileSystemItemResource
     * FileManagerPlugin::make([
     *     FileManager::class,
     *     FileSystemItemResource::class,
     * ])
     */
    public static function make(?array $components = null): static
    {
        $plugin = app(static::class);
        $plugin->components = $components;

        return $plugin;
    }

    /**
     * Set the components to register.
     *
     * @param array<class-string> $components
     */
    public function components(array $components): static
    {
        $this->components = $components;

        return $this;
    }

    /**
     * Register only the specified pages.
     *
     * @param array<class-string> $pages
     */
    public function only(array $pages): static
    {
        $this->components = $pages;

        return $this;
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
