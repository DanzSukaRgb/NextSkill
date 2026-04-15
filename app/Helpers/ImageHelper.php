<?php

namespace App\Helpers;

/**
 * ImageHelper
 *
 * Helper class for properly handling image URLs in API responses.
 * Ensures that only relative paths are stored in database and properly
 * prefixed with storage URL when returning to client.
 */
class ImageHelper
{
    /**
     * Get the full storage URL for an image path
     *
     * Handles:
     * - Null or empty values (returns null)
     * - Full URLs (returns as-is if they start with http)
     * - Relative paths (prefixes with asset('storage/'))
     *
     * @param string|null $path The image path (relative or full URL)
     * @return string|null The complete image URL
     */
    public static function getImageUrl(?string $path): ?string
    {
        // Return null if path is empty
        if (empty($path)) {
            return null;
        }

        // If it's already a full URL, return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // For relative paths, prefix with storage URL
        return asset('storage/' . $path);
    }

    /**
     * Remove the storage URL prefix from a path if present
     * Used for storing only relative paths in database
     *
     * @param string|null $url The image URL or path
     * @return string|null The relative path only
     */
    public static function extractRelativePath(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // If it's a full URL with storage prefix, extract the relative path
        $storagePrefix = asset('storage/');
        if (str_starts_with($url, $storagePrefix)) {
            return substr($url, strlen($storagePrefix));
        }

        // If it's already a relative path, return as-is
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            return $url;
        }

        // If it's an external URL, don't store it
        return null;
    }

    /**
     * Check if a path is a valid relative storage path
     * (not a full URL)
     *
     * @param string|null $path The path to check
     * @return bool True if it's a relative path
     */
    public static function isRelativePath(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return !str_starts_with($path, 'http://') && !str_starts_with($path, 'https://');
    }
}
