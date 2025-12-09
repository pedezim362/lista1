# MWGuerra FileManager

A file manager package for Laravel and Filament v4 with dual operating modes, S3/MinIO support, file previews, and drag-and-drop uploads.

![File System Storage Mode](./docs/images/File%20System%20%28Storage%20Mode%29%20-%20Minio%20Disk.png)

## Features

- **Dual operating modes**: Database mode (tracked files with metadata) or Storage mode (direct filesystem browsing)
- **File browser**: Grid and list views, folder tree sidebar, breadcrumb navigation
- **File operations**: Upload, move, rename, delete with drag-and-drop support
- **Multi-selection**: Select multiple files with Ctrl/Cmd + click
- **File previews**: Built-in viewers for video, audio, images, PDF, and text files
- **Storage drivers**: Works with local, S3, MinIO, or any Laravel Storage driver
- **Security**: MIME validation, blocked extensions, filename sanitization, signed URLs
- **Authorization**: Configurable permissions with Laravel Policy support
- **Embeddable**: Use as standalone pages or embed in Filament forms
- **Dark mode**: Full dark mode support via Filament

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x
- Filament 4.x

## Installation

```bash
composer require mwguerra/filemanager
```

### Quick Install (Recommended)

Run the install command to set up everything automatically:

```bash
php artisan filemanager:install
```

This command will:
1. Publish Filament assets (including pre-compiled FileManager CSS)
2. Publish the configuration file
3. Run database migrations

### Manual Installation

If you prefer manual control, you can run each step separately:

```bash
# Publish configuration
php artisan vendor:publish --tag=filemanager-config

# Run migrations
php artisan migrate

# Publish Filament assets (includes FileManager CSS)
php artisan filament:assets
```

### Importing Existing Files

If you have existing files in your filesystem that you want to import into the File Manager:

```bash
php artisan filemanager:rebuild
```

This will scan your storage and create database records for all existing files.

### Register the Plugin

Register the plugin in your Panel Provider:

```php
use MWGuerra\FileManager\FileManagerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FileManagerPlugin::make(),
        ]);
}
```

### Configuring Pages

By default, the plugin registers all pages based on config settings. You can customize which pages to register:

```php
use MWGuerra\FileManager\FileManagerPlugin;
use MWGuerra\FileManager\Filament\Pages\FileManager;
use MWGuerra\FileManager\Filament\Pages\FileSystem;
use MWGuerra\FileManager\Filament\Pages\SchemaExample;
use MWGuerra\FileManager\Filament\Resources\FileSystemItemResource;

// Register all enabled pages (default)
FileManagerPlugin::make()

// Register only specific pages
FileManagerPlugin::make([
    FileManager::class,      // Database mode - full CRUD
    FileSystem::class,       // Storage mode - read-only browser
])

// Register only the File Manager page
FileManagerPlugin::make([
    FileManager::class,
])

// Register only the File System page
FileManagerPlugin::make([
    FileSystem::class,
])

// Include the Schema Example page (for testing embeds)
FileManagerPlugin::make([
    FileManager::class,
    FileSystem::class,
    SchemaExample::class,
])

// Using the fluent API
FileManagerPlugin::make()
    ->only([
        FileManager::class,
        FileSystemItemResource::class,
    ])
```

### Fluent Configuration API

The plugin provides a fluent API for configuring all aspects of the file manager directly in your Panel Provider. This approach is preferred over config file settings as it keeps your panel configuration in one place.

#### Panel Sidebar

Add a folder tree sidebar to your Filament panel navigation:

```php
use Filament\View\PanelsRenderHook;

FileManagerPlugin::make()
    // Enable panel sidebar (appears in Filament navigation)
    ->panelSidebar()
    ->panelSidebarRootLabel('My Files')
    ->panelSidebarHeading('Folders')

    // Or use the short alias
    ->sidebar()

    // Customize render hook location
    ->panelSidebar(
        enabled: true,
        renderHook: PanelsRenderHook::SIDEBAR_NAV_END,
        scopes: ['admin']
    )

    // Disable panel sidebar
    ->withoutPanelSidebar()
```

#### File Manager Page Configuration

Configure the database mode File Manager page:

```php
FileManagerPlugin::make()
    // Enable/disable the page
    ->fileManager(true)
    ->withoutFileManager()  // Disable

    // Configure page sidebar (folder tree on the page itself)
    ->fileManagerPageSidebar(true)
    ->fileManagerSidebarRootLabel('Root')
    ->fileManagerSidebarHeading('Folders')

    // Configure navigation
    ->fileManagerNavigation(
        icon: 'heroicon-o-folder',
        label: 'File Manager',
        sort: 1,
        group: 'Content'
    )
```

#### File System Page Configuration

Configure the storage mode File System page (read-only):

```php
FileManagerPlugin::make()
    // Enable/disable the page
    ->fileSystem(true)
    ->withoutFileSystem()  // Disable

    // Configure page sidebar
    ->fileSystemPageSidebar(true)
    ->fileSystemSidebarRootLabel('Root')
    ->fileSystemSidebarHeading('Storage')

    // Configure navigation
    ->fileSystemNavigation(
        icon: 'heroicon-o-server-stack',
        label: 'File System',
        sort: 2,
        group: 'Content'
    )
```

#### Schema Example Page

Enable/disable the demo page for testing embedded components:

```php
FileManagerPlugin::make()
    ->schemaExample(true)
    ->withoutSchemaExample()  // Disable
```

#### Complete Configuration Example

```php
use MWGuerra\FileManager\FileManagerPlugin;
use Filament\View\PanelsRenderHook;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FileManagerPlugin::make()
                // Panel sidebar (in Filament navigation)
                ->panelSidebar()
                ->panelSidebarRootLabel('All Files')
                ->panelSidebarHeading('Folders')

                // File Manager page (database mode)
                ->fileManager()
                ->fileManagerPageSidebar(true)
                ->fileManagerSidebarRootLabel('Root')
                ->fileManagerSidebarHeading('Folders')
                ->fileManagerNavigation(
                    icon: 'heroicon-o-folder',
                    label: 'Files',
                    sort: 1,
                    group: 'Content'
                )

                // File System page (storage mode, read-only)
                ->fileSystem()
                ->fileSystemPageSidebar(true)
                ->fileSystemSidebarRootLabel('Storage Root')
                ->fileSystemSidebarHeading('Directories')
                ->fileSystemNavigation(
                    icon: 'heroicon-o-server-stack',
                    label: 'Storage',
                    sort: 2,
                    group: 'Content'
                )

                // Disable demo page
                ->withoutSchemaExample(),
        ]);
}
```

#### Configuration Precedence

Configuration values follow this precedence (highest to lowest):

1. **Fluent API** - Values set via the plugin methods
2. **Config file** - Values from `config/filemanager.php`
3. **Defaults** - Built-in default values

For sidebar labels, page-specific settings fall back to panel sidebar settings:

```php
FileManagerPlugin::make()
    ->panelSidebarRootLabel('Root')  // Default for all sidebars
    ->fileManagerSidebarRootLabel('Files Root')  // Override for File Manager only
```

| Page Class | URL | Description |
|------------|-----|-------------|
| `FileManager::class` | `/admin/file-manager` | Database mode with full CRUD |
| `FileSystem::class` | `/admin/file-system` | Storage mode (read-only) |
| `SchemaExample::class` | `/admin/schema-example` | Demo page for embedded components |
| `FileSystemItemResource::class` | `/admin/file-system-items` | Filament resource for files |

## Quick Start

After installation, access the file manager at:

| Page | URL | Description |
|------|-----|-------------|
| File Manager | `/admin/file-manager` | Database mode with full CRUD operations |
| File System | `/admin/file-system` | Storage mode for browsing files (read-only) |

## Embedding in Forms

```php
use MWGuerra\FileManager\Schemas\Components\FileManagerEmbed;
use MWGuerra\FileManager\Schemas\Components\FileSystemEmbed;

// Database mode (full CRUD)
FileManagerEmbed::make()
    ->height('400px')
    ->disk('s3')
    ->target('uploads'),

// Storage mode (read-only browser)
FileSystemEmbed::make()
    ->height('400px')
    ->disk('public')
    ->target('media'),
```

## Artisan Commands

### filemanager:install

Install FileManager with all required assets and configuration:

```bash
php artisan filemanager:install [options]
```

| Option | Description |
|--------|-------------|
| `--skip-assets` | Skip publishing Filament assets |
| `--skip-config` | Skip publishing configuration |
| `--skip-migrations` | Skip running migrations |
| `--with-css` | Also configure your app.css for style customization |
| `--css-path=` | Path to CSS file (default: resources/css/app.css) |
| `--force` | Overwrite existing configurations |

**Note:** The `--with-css` option is only needed if you want to customize FileManager styles in your project's CSS. The plugin includes pre-compiled CSS with all necessary Tailwind classes.

### filesystem:list

List files directly from a storage disk (recursive by default):

```bash
php artisan filesystem:list [path] [options]
```

| Option | Description |
|--------|-------------|
| `path` | Folder path to list (default: root) |
| `--disk=` | Storage disk (default: from config/env) |
| `--type=` | Filter: `folder`, `file`, or `all` |
| `--no-recursive` | Disable recursive listing |
| `--format=` | Output: `table`, `json`, or `csv` |
| `--show-hidden` | Include hidden files (starting with .) |

### filemanager:list

List files with database or storage mode support:

```bash
php artisan filemanager:list [path] [options]
```

| Option | Description |
|--------|-------------|
| `path` | Folder path or ID to list (default: root) |
| `--disk=` | Storage disk (default: from config/env) |
| `--mode=` | Mode: `database` or `storage` (default: database) |
| `--target=` | Target directory within disk |
| `--type=` | Filter: `folder`, `file`, or `all` |
| `--recursive` | Enable recursive listing |
| `--format=` | Output: `table`, `json`, or `csv` |
| `--show-hidden` | Include hidden files |

### filemanager:rebuild

Rebuild database from filesystem (clears existing records):

```bash
php artisan filemanager:rebuild [options]
```

| Option | Description |
|--------|-------------|
| `--disk=` | Storage disk to scan (default: from config/env) |
| `--root=` | Root directory to scan |
| `--force` | Skip confirmation prompt |

### filemanager:upload

Upload a local folder to storage:

```bash
php artisan filemanager:upload <path> [options]
```

| Option | Description |
|--------|-------------|
| `path` | Local folder path to upload (required) |
| `--disk=` | Target storage disk (default: from config/env) |
| `--target=` | Target directory within disk |
| `--no-database` | Skip creating database records |
| `--force` | Skip confirmation prompt |

## Publishable Assets

| Tag | Description |
|-----|-------------|
| `filemanager-config` | Configuration file |
| `filemanager-migrations` | Database migrations |
| `filemanager-views` | Blade view templates |
| `filemanager-model` | Customizable model |
| `filemanager-stubs` | Config stubs (filesystems, env) |
| `filemanager-upload-config` | Upload configuration |

## Development

### Composer Scripts

The package includes several composer scripts for development:

```bash
# Run tests
composer test

# Run tests in parallel
composer test:parallel

# Run tests with coverage
composer test:coverage

# Build CSS assets
composer build

# Create a new release (builds, commits, tags, and pushes)
composer release
```

### Release Process

To create a new release, run:

```bash
composer release
```

This interactive script will:
1. Build CSS assets (`npm run build`)
2. Show the last git tag
3. Prompt for a new version (validates semver format and ensures it's higher)
4. Commit any uncommitted changes
5. Push to remote
6. Create and push the new git tag

## License

MIT License. See [LICENSE](LICENSE) for details.

## Author

### **Marcelo W. Guerra**
- Website: [mwguerra.com](https://mwguerra.com/)
- Github: [mwguerra](https://github.com/mwguerra/)
- Linkedin: [marcelowguerra](https://www.linkedin.com/in/marcelowguerra/)

## Contributors

- [Claudio Pereira](https://github.com/cpereiraweb)
- [Fernando dos Santos Souza](https://github.com/nandinhos)
