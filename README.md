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

Publish configuration:

```bash
php artisan vendor:publish --tag=filemanager-config
```

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
