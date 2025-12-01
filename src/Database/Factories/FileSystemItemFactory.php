<?php

namespace MWGuerra\FileManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MWGuerra\FileManager\Enums\FileSystemItemType;
use MWGuerra\FileManager\Enums\FileType;

/**
 * Factory for FileSystemItem model.
 *
 * Usage:
 * - FileSystemItem::factory()->create()              // Creates a random file or folder
 * - FileSystemItem::factory()->folder()->create()   // Creates a folder
 * - FileSystemItem::factory()->file()->create()     // Creates a file (random type)
 * - FileSystemItem::factory()->video()->create()    // Creates a video file
 * - FileSystemItem::factory()->image()->create()    // Creates an image file
 * - FileSystemItem::factory()->document()->create() // Creates a document file
 * - FileSystemItem::factory()->audio()->create()    // Creates an audio file
 */
class FileSystemItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * This will be resolved from the config at runtime.
     */
    protected $model = null;

    /**
     * Create a new factory instance.
     */
    public function __construct(...$args)
    {
        parent::__construct(...$args);

        // Resolve the model class from config
        $this->model = config('filemanager.model');
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isFolder = $this->faker->boolean(30); // 30% chance of folder

        if ($isFolder) {
            return $this->folderDefinition();
        }

        return $this->fileDefinition();
    }

    /**
     * Generate folder attributes.
     */
    protected function folderDefinition(): array
    {
        $name = $this->faker->unique()->words(rand(1, 3), true);

        return [
            'name' => ucwords($name),
            'type' => FileSystemItemType::Folder->value,
            'file_type' => null,
            'parent_id' => null,
            'size' => null,
            'duration' => null,
            'thumbnail' => null,
            'storage_path' => null,
        ];
    }

    /**
     * Generate file attributes with random file type.
     */
    protected function fileDefinition(?FileType $fileType = null): array
    {
        $fileType = $fileType ?? $this->faker->randomElement([
            FileType::Video,
            FileType::Image,
            FileType::Document,
            FileType::Audio,
        ]);

        $extensions = [
            FileType::Video->value => ['mp4', 'webm', 'mov', 'avi'],
            FileType::Image->value => ['jpg', 'png', 'gif', 'webp'],
            FileType::Document->value => ['pdf', 'doc', 'docx', 'txt'],
            FileType::Audio->value => ['mp3', 'wav', 'flac', 'aac'],
        ];

        $extension = $this->faker->randomElement($extensions[$fileType->value] ?? ['bin']);
        $name = $this->faker->unique()->words(rand(1, 3), true);
        $fileName = str_replace(' ', '-', strtolower($name)) . '.' . $extension;

        $attributes = [
            'name' => $fileName,
            'type' => FileSystemItemType::File->value,
            'file_type' => $fileType->value,
            'parent_id' => null,
            'size' => $this->faker->numberBetween(1024, 104857600), // 1KB to 100MB
            'duration' => null,
            'thumbnail' => null,
            'storage_path' => 'uploads/' . $this->faker->uuid() . '.' . $extension,
        ];

        // Add duration for video and audio
        if (in_array($fileType, [FileType::Video, FileType::Audio])) {
            $attributes['duration'] = $this->faker->numberBetween(10, 3600); // 10s to 1h
        }

        // Add thumbnail for video and image
        if (in_array($fileType, [FileType::Video, FileType::Image])) {
            $attributes['thumbnail'] = '/thumbnails/' . $this->faker->uuid() . '.jpg';
        }

        return $attributes;
    }

    /**
     * Configure the model as a folder.
     */
    public function folder(): static
    {
        return $this->state(fn () => $this->folderDefinition());
    }

    /**
     * Configure the model as a file (random type).
     */
    public function file(): static
    {
        return $this->state(fn () => $this->fileDefinition());
    }

    /**
     * Configure the model as a video file.
     */
    public function video(): static
    {
        return $this->state(fn () => $this->fileDefinition(FileType::Video));
    }

    /**
     * Configure the model as an image file.
     */
    public function image(): static
    {
        return $this->state(fn () => $this->fileDefinition(FileType::Image));
    }

    /**
     * Configure the model as a document file.
     */
    public function document(): static
    {
        return $this->state(fn () => $this->fileDefinition(FileType::Document));
    }

    /**
     * Configure the model as an audio file.
     */
    public function audio(): static
    {
        return $this->state(fn () => $this->fileDefinition(FileType::Audio));
    }

    /**
     * Set the parent folder for this item.
     */
    public function inFolder($parentId): static
    {
        return $this->state(fn () => [
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Configure the model to be at the root level.
     */
    public function atRoot(): static
    {
        return $this->state(fn () => [
            'parent_id' => null,
        ]);
    }
}
