<?php

namespace MWGuerra\FileManager\Schemas\Components;

use Closure;
use Filament\Schemas\Components\Component;

/**
 * Embeddable File Manager component for use in Filament schemas/forms.
 *
 * This component embeds the database-mode file manager into any Filament form or page.
 */
class FileManagerEmbed extends Component
{
    protected string $view = 'filemanager::schemas.components.file-manager-embed';

    protected string|Closure $height = '500px';

    protected bool|Closure $showHeader = true;

    protected bool|Closure $showSidebar = true;

    protected string|Closure $defaultViewMode = 'grid';

    protected ?string $disk = null;

    protected ?string $target = null;

    protected ?string $initialFolder = null;

    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Set the height of the embedded file manager.
     */
    public function height(string|Closure $height): static
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get the height of the embedded file manager.
     */
    public function getHeight(): string
    {
        return $this->evaluate($this->height);
    }

    /**
     * Show or hide the header controls.
     */
    public function showHeader(bool|Closure $show = true): static
    {
        $this->showHeader = $show;

        return $this;
    }

    /**
     * Hide the header controls.
     */
    public function hideHeader(): static
    {
        return $this->showHeader(false);
    }

    /**
     * Get whether to show the header.
     */
    public function shouldShowHeader(): bool
    {
        return $this->evaluate($this->showHeader);
    }

    /**
     * Show or hide the sidebar.
     */
    public function showSidebar(bool|Closure $show = true): static
    {
        $this->showSidebar = $show;

        return $this;
    }

    /**
     * Hide the sidebar.
     */
    public function hideSidebar(): static
    {
        return $this->showSidebar(false);
    }

    /**
     * Get whether to show the sidebar.
     */
    public function shouldShowSidebar(): bool
    {
        return $this->evaluate($this->showSidebar);
    }

    /**
     * Set the default view mode (grid or list).
     */
    public function defaultViewMode(string|Closure $mode): static
    {
        $this->defaultViewMode = $mode;

        return $this;
    }

    /**
     * Get the default view mode.
     */
    public function getDefaultViewMode(): string
    {
        return $this->evaluate($this->defaultViewMode);
    }

    /**
     * Set the storage disk to use.
     */
    public function disk(?string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Get the storage disk.
     */
    public function getDisk(): ?string
    {
        return $this->disk;
    }

    /**
     * Set the target directory within the disk.
     */
    public function target(?string $target): static
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get the target directory.
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }

    /**
     * Set the initial folder to navigate to on load.
     * For database mode, this is the folder ID.
     */
    public function initialFolder(?string $folder): static
    {
        $this->initialFolder = $folder;

        return $this;
    }

    /**
     * Get the initial folder.
     */
    public function getInitialFolder(): ?string
    {
        return $this->initialFolder;
    }

    /**
     * Set to compact mode (no header, no sidebar).
     */
    public function compact(): static
    {
        return $this->hideHeader()->hideSidebar();
    }
}
