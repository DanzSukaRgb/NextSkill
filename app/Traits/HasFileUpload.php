<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasFileUpload
{
    /**
     * Disk storage yang digunakan
     */
    protected string $uploadDisk = 'public';

    /**
     * Folder untuk upload
     */
    protected string $uploadPath = 'uploads';

    /**
     * Upload file ke storage
     * @param UploadedFile $file
     * @param string $folder (optional override path)
     * @return string|null
     */
    public function uploadFile(UploadedFile $file, ?string $folder = null): ?string
    {
        try {
            $path = $folder ?? $this->uploadPath;

            // Generate nama file unik
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Simpan file
            $filePath = Storage::disk($this->uploadDisk)->putFileAs(
                $path,
                $file,
                $fileName
            );

            return $filePath;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Hapus file dari storage
     * @param string|null $filePath
     * @return bool
     */
    public function deleteFile(?string $filePath): bool
    {
        if (!$filePath) {
            return true;
        }

        if (Storage::disk($this->uploadDisk)->exists($filePath)) {
            return Storage::disk($this->uploadDisk)->delete($filePath);
        }

        return true;
    }

    /**
     * Update file - hapus old, upload new
     * @param UploadedFile $newFile
     * @param string|null $oldFilePath
     * @param string|null $folder (optional)
     * @return string|null
     */
    public function updateFile(UploadedFile $newFile, ?string $oldFilePath = null, ?string $folder = null): ?string
    {
        // Hapus file lama
        if ($oldFilePath) {
            $this->deleteFile($oldFilePath);
        }

        // Upload file baru
        return $this->uploadFile($newFile, $folder);
    }

    /**
     * Get full URL dari file path
     * @param string|null $filePath
     * @return string|null
     */
    public function getFileUrl(?string $filePath): ?string
    {
        if (!$filePath) {
            return null;
        }

        return asset('storage/' . $filePath);
    }
}
