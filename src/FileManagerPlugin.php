<?php

namespace MWGuerra\FileManager;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
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

    /**
     * Whether to render the sidebar in the panel navigation.
     */
    protected bool $sidebarEnabled = false;

    /**
     * The render hook to use for the sidebar.
     */
    protected string $sidebarRenderHook = PanelsRenderHook::SIDEBAR_NAV_START;

    /**
     * Custom scopes for the render hook.
     */
    protected ?array $sidebarScopes = null;

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
        if ($this->sidebarEnabled && config('filemanager.sidebar.enabled', true)) {
            $this->registerSidebarRenderHook();
        }
    }

    /**
     * Register the sidebar render hook.
     */
    protected function registerSidebarRenderHook(): void
    {
        FilamentView::registerRenderHook(
            $this->sidebarRenderHook,
            fn (): View => view('filemanager::components.panel-sidebar'),
            scopes: $this->sidebarScopes,
        );
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

    /**
     * Enable the sidebar folder tree in the panel navigation.
     *
     * @param string $renderHook The render hook to use (default: SIDEBAR_NAV_START)
     * @param array|null $scopes Optional scopes for the render hook
     *
     * @example
     * // Enable sidebar at the start of navigation
     * FileManagerPlugin::make()->sidebar()
     *
     * @example
     * // Enable sidebar at the end of navigation
     * FileManagerPlugin::make()->sidebar(PanelsRenderHook::SIDEBAR_NAV_END)
     *
     * @example
     * // Enable sidebar with custom scopes
     * FileManagerPlugin::make()->sidebar(scopes: [MyResource::class])
     */
    public function sidebar(
        string $renderHook = PanelsRenderHook::SIDEBAR_NAV_START,
        ?array $scopes = null
    ): static {
        $this->sidebarEnabled = true;
        $this->sidebarRenderHook = $renderHook;
        $this->sidebarScopes = $scopes;

        return $this;
    }

    /**
     * Disable the sidebar.
     */
    public function withoutSidebar(): static
    {
        $this->sidebarEnabled = false;

        return $this;
    }

    /**
     * Check if sidebar is enabled.
     */
    public function isSidebarEnabled(): bool
    {
        return $this->sidebarEnabled;
    }
}
