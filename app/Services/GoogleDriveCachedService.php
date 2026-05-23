<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleDriveCachedService
{
    public function cacheKey(string $path): string
    {
        $path = $this->normalizePath($path);

        return 'google_drive_path_' . md5($path);
    }

    public function directoryExistsCacheKey(string $path): string
    {
        return 'google_drive_path_' . md5('exists.directory:' . $this->normalizePath($path));
    }

    public function cacheMinutes(): int
    {
        return max((int) config('filesystems.disks.google.cacheMinutes', 10), 1);
    }

    public function timingEnabled(): bool
    {
        return filter_var(config('filesystems.disks.google.logTiming', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function rememberDirectoryListing(FilesystemAdapter $disk, string $path, string $diskName = 'google'): array
    {
        if ($diskName !== 'google') {
            return $this->listDirectoryContentsByType($disk, $path, $diskName);
        }

        $cacheKey = $this->cacheKey($path);
        $startedAt = microtime(true);
        $cachedListing = Cache::get($cacheKey);

        if ($cachedListing !== null) {
            $this->logTiming('directories.files.cached', $path, $startedAt, [
                'files_count' => count($cachedListing[1] ?? []),
                'directories_count' => count($cachedListing[0] ?? []),
                'source' => 'cache',
                'cache_key' => $cacheKey,
            ]);

            return $cachedListing;
        }

        try {
            if (!$this->directoryExists($disk, $path, $diskName, false)) {
                $this->logDriveWarning('Google Drive folder not found while listing.', $path, $startedAt);

                return [[], []];
            }

            $listing = $this->listDirectoryContentsByType($disk, $path, $diskName, false);
            Cache::put($cacheKey, $listing, now()->addMinutes($this->cacheMinutes()));
        } catch (Throwable $e) {
            $this->logDriveWarning('Google Drive listing failed.', $path, $startedAt, $e);

            return [[], []];
        }

        $this->logTiming('directories.files.cached', $path, $startedAt, [
            'files_count' => count($listing[1] ?? []),
            'directories_count' => count($listing[0] ?? []),
            'source' => 'google_drive',
            'cache_key' => $cacheKey,
        ]);

        return $listing;
    }

    public function rememberDirectoryExists(FilesystemAdapter $disk, string $path, string $diskName = 'google'): bool
    {
        if ($diskName !== 'google') {
            return $this->directoryExists($disk, $path, $diskName);
        }

        $cacheKey = $this->directoryExistsCacheKey($path);
        $startedAt = microtime(true);
        $cachedExists = Cache::get($cacheKey);

        if ($cachedExists !== null) {
            $this->logTiming('exists.directory.cached', $path, $startedAt, [
                'source' => 'cache',
                'cache_key' => $cacheKey,
                'exists' => $cachedExists,
            ]);

            return (bool) $cachedExists;
        }

        try {
            $exists = $this->directoryExists($disk, $path, $diskName, false);
            Cache::put($cacheKey, $exists, now()->addMinutes($this->cacheMinutes()));
        } catch (Throwable $e) {
            $this->logDriveWarning('Google Drive directory exists check failed.', $path, $startedAt, $e);

            return false;
        }

        $this->logTiming('exists.directory.cached', $path, $startedAt, [
            'source' => 'google_drive',
            'cache_key' => $cacheKey,
            'exists' => $exists,
        ]);

        return $exists;
    }

    public function directoryExists(FilesystemAdapter $disk, string $path, string $diskName = 'google', bool $log = true): bool
    {
        $startedAt = microtime(true);

        try {
            $exists = $disk->getDriver()->directoryExists($path);
        } catch (Throwable $e) {
            if (!$log) {
                throw $e;
            }

            $this->logDriveWarning('Google Drive directory exists check failed.', $path, $startedAt, $e, $diskName);

            return false;
        }

        if ($log) {
            $this->logTiming('exists.directory', $path, $startedAt, [
                'source' => $diskName === 'google' ? 'google_drive' : $diskName,
                'exists' => $exists,
            ]);
        }

        return $exists;
    }

    public function fileExists(FilesystemAdapter $disk, string $path, string $diskName = 'google'): bool
    {
        $startedAt = microtime(true);

        try {
            $exists = $disk->getDriver()->fileExists($path);
        } catch (Throwable $e) {
            $this->logDriveWarning('Google Drive file exists check failed.', $path, $startedAt, $e, $diskName);

            return false;
        }

        $this->logTiming('exists.file', $path, $startedAt, [
            'source' => $diskName === 'google' ? 'google_drive' : $diskName,
            'exists' => $exists,
        ]);

        return $exists;
    }

    public function listContents(FilesystemAdapter $disk, string $path, bool $recursive = false, string $diskName = 'google')
    {
        $startedAt = microtime(true);

        try {
            $contents = $disk->getDriver()->listContents($path, $recursive);
        } catch (Throwable $e) {
            $this->logDriveWarning('Google Drive listContents failed.', $path, $startedAt, $e, $diskName);

            return collect();
        }

        $this->logTiming('listContents', $path, $startedAt, [
            'source' => $diskName === 'google' ? 'google_drive' : $diskName,
            'recursive' => $recursive,
        ]);

        return $contents;
    }

    public function read(FilesystemAdapter $disk, string $path, string $diskName = 'google'): string
    {
        $startedAt = microtime(true);
        $contents = $disk->getDriver()->read($path);

        $this->logTiming('get', $path, $startedAt, [
            'source' => $diskName === 'google' ? 'google_drive' : $diskName,
            'bytes' => strlen($contents),
        ]);

        return $contents;
    }

    public function mimeType(FilesystemAdapter $disk, string $path, string $diskName = 'google'): string
    {
        $startedAt = microtime(true);
        $mimeType = $disk->getDriver()->mimeType($path);

        $this->logTiming('mimeType', $path, $startedAt, [
            'source' => $diskName === 'google' ? 'google_drive' : $diskName,
            'mime_type' => $mimeType,
        ]);

        return $mimeType;
    }

    public function forgetPath(string ...$paths): void
    {
        foreach ($paths as $path) {
            $path = $this->normalizePath($path);

            if ($path === '') {
                continue;
            }

            Cache::forget($this->cacheKey($path));
            Cache::forget($this->directoryExistsCacheKey($path));
        }
    }

    public function forgetRelatedTo(string ...$paths): void
    {
        foreach ($paths as $path) {
            $normalizedPath = $this->normalizePath($path);

            if ($normalizedPath === '') {
                continue;
            }

            $this->forgetPath($normalizedPath, $this->parentPath($normalizedPath));
        }
    }

    private function listDirectoryContentsByType(FilesystemAdapter $disk, string $path, string $diskName, bool $log = true): array
    {
        $startedAt = microtime(true);
        $directories = [];
        $files = [];

        try {
            foreach ($disk->getDriver()->listContents($path, false) as $item) {
                if ($item->isDir()) {
                    $directories[] = $item->path();
                    continue;
                }

                if ($item->isFile()) {
                    $files[] = $item->path();
                }
            }
        } catch (Throwable $e) {
            if (!$log) {
                throw $e;
            }

            $this->logDriveWarning('Google Drive directory listing failed.', $path, $startedAt, $e, $diskName);

            return [[], []];
        }

        if ($log) {
            $this->logTiming('directories.files', $path, $startedAt, [
                'files_count' => count($files),
                'directories_count' => count($directories),
                'source' => $diskName === 'google' ? 'google_drive' : $diskName,
            ]);
        }

        return [$directories, $files];
    }

    private function logTiming(string $operation, string $path, float $startedAt, array $context = []): void
    {
        if (!$this->timingEnabled() || !in_array($context['source'] ?? 'google_drive', ['google_drive', 'cache'], true)) {
            return;
        }

        Log::info('Google Drive timing', array_merge([
            'operation' => $operation,
            'path' => $path,
            'url' => request()?->fullUrl(),
            'route_name' => request()?->route()?->getName(),
            'seconds' => round(microtime(true) - $startedAt, 4),
            'files_count' => $context['files_count'] ?? null,
            'directories_count' => $context['directories_count'] ?? null,
            'source' => $context['source'] ?? 'google_drive',
        ], $context));
    }

    private function logDriveWarning(
        string $message,
        string $path,
        float $startedAt,
        ?Throwable $exception = null,
        string $diskName = 'google'
    ): void {
        if ($diskName !== 'google') {
            return;
        }

        Log::warning($message, [
            'path' => $path,
            'url' => request()?->fullUrl(),
            'route_name' => request()?->route()?->getName(),
            'user_id' => auth()->id(),
            'seconds' => round(microtime(true) - $startedAt, 4),
            'error' => $exception ? $this->shortErrorMessage($exception) : 'Folder not found.',
        ]);
    }

    private function shortErrorMessage(Throwable $exception): string
    {
        $message = preg_replace('/\s+/', ' ', $exception->getMessage()) ?? $exception->getMessage();

        return mb_substr($message, 0, 300);
    }

    private function normalizePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', rawurldecode($path)), '/');

        return preg_replace('~/+~', '/', $path) ?? $path;
    }

    private function parentPath(string $path): string
    {
        $parent = dirname($this->normalizePath($path));

        return $parent === '.' ? '' : $parent;
    }
}
