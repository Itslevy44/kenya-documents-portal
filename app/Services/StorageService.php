<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    /**
     * Get full path to a document file
     *
     * @param string $relativePath Path relative to storage/app
     * @return string Full filesystem path
     */
    public function getFullPath(string $relativePath): string
    {
        return storage_path('app/' . $relativePath);
    }

    /**
     * Check if a document file exists
     *
     * @param string $relativePath
     * @return bool
     */
    public function exists(string $relativePath): bool
    {
        return Storage::exists($relativePath);
    }

    /**
     * Delete a document file
     *
     * @param string $relativePath
     * @return bool
     */
    public function delete(string $relativePath): bool
    {
        if ($this->exists($relativePath)) {
            return Storage::delete($relativePath);
        }
        return false;
    }

    /**
     * Delete multiple document files
     *
     * @param array $relativePaths
     * @return void
     */
    public function deleteMultiple(array $relativePaths): void
    {
        foreach ($relativePaths as $path) {
            $this->delete($path);
        }
    }

    /**
     * Get file size in bytes
     *
     * @param string $relativePath
     * @return int
     */
    public function getSize(string $relativePath): int
    {
        return Storage::size($relativePath);
    }

    /**
     * Get file MIME type
     *
     * @param string $relativePath
     * @return string
     */
    public function getMimeType(string $relativePath): string
    {
        return Storage::mimeType($relativePath);
    }

    /**
     * Stream a file for download
     *
     * @param string $relativePath
     * @param string|null $downloadName Optional download filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamDownload(string $relativePath, ?string $downloadName = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $downloadName = $downloadName ?? basename($relativePath);
        
        return Storage::download($relativePath, $downloadName);
    }

    /**
     * Ensure documents directory exists
     *
     * @return void
     */
    public function ensureDocumentsDirectoryExists(): void
    {
        $path = storage_path('app/documents');
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }
}
