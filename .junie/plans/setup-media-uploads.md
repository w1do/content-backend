---
sessionId: session-260701-173803-1wue
---

# Requirements

### Overview & Goals
Configure Spatie Media Library to store uploaded images into the `/public/uploads` directory, making them publicly accessible.

### Scope
- **In Scope:**
  - Create a new filesystem disk `uploads` in the application configuration.
  - Map the public `uploads` directory to a storage directory via a symbolic link.
  - Change the default Spatie Media Library disk to the new `uploads` disk.
- **Out of Scope:**
  - Changing the `CustomPathGenerator` (it already cleanly groups files by model into subfolders).

# Technical Design

### Current Implementation
Spatie Media Library is installed and configured to use a custom path generator (`App\Infrastructure\Media\CustomPathGenerator`). The `filesystems.php` config uses Laravel defaults for disks, plus an extra `media` disk, but currently, `media-library.php` uses `public` as the default disk.

### Key Decisions
- **Storage Strategy:** Following Laravel best practices, we will not save files directly into the `public/uploads` folder. Instead, files will be saved in `storage/app/uploads` and symlinked to `public/uploads`. This keeps the `public` directory clean for version control and makes managing backups easier.

### Proposed Changes
- **`config/filesystems.php`**: 
  - Add an `uploads` disk:
    ```php
    'uploads' => [
        'driver' => 'local',
        'root' => storage_path('app/uploads'),
        'url' => env('APP_URL').'/uploads',
        'visibility' => 'public',
        'throw' => false,
        'report' => false,
    ],
    ```
  - Add a symlink entry: `public_path('uploads') => storage_path('app/uploads')`
- **`config/media-library.php`**: 
  - Update the disk name property to default to `'uploads'`: `'disk_name' => env('MEDIA_DISK', 'uploads')`
- **Commands**: Run `./vendor/bin/sail artisan storage:link` to ensure the symlink is created locally.

# Delivery Steps

### ✓ Step 1: Configure uploads filesystem disk
The application has a new `uploads` disk configured that symlinks to `public/uploads`.

- Add an `uploads` disk configuration in `config/filesystems.php` pointing to `storage_path('app/uploads')` with visibility set to `public`.
- Add the corresponding link mapping in the `links` array: `public_path('uploads') => storage_path('app/uploads')`.

### ✓ Step 2: Update Media Library default disk
Spatie Media Library uses the new `uploads` disk by default.

- Modify `config/media-library.php` to set `'disk_name' => env('MEDIA_DISK', 'uploads')`.

### ✓ Step 3: Generate storage symbolic links
The `/public/uploads` directory is created as a symlink and accessible.

- Run `./vendor/bin/sail artisan storage:link` to create the symbolic link in the local environment.