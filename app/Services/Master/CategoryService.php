<?php

namespace App\Services\Master;

use App\Models\Category;
use App\Repositories\Master\CategoryRepository;
use App\Traits\HasFileUpload;
use Illuminate\Http\UploadedFile;

class CategoryService
{
    use HasFileUpload;
    private $repo;

    public function __construct(CategoryRepository $repo)
    {
        $this->repo = $repo;
        $this->uploadPath = 'categories';
    }

    /**
     * Upload icon kategori
     */
    public function uploadIcon(?UploadedFile $file): ?string
    {
        return $this->uploadFile($file);
    }

    /**
     * Hapus icon kategori
     */
    public function deleteIcon(?string $iconPath): bool
    {
        return $this->deleteFile($iconPath);
    }

    /**
     * Update icon kategori
     */
    public function handleUpdateIcon(Category $category, UploadedFile $newFile): ?string
    {
        return $this->updateFile($newFile, $category->icon);
    }

    /**
     * Delete icon kategori
     */
    public function handleDeleteIcon(Category $category): void
    {
        if ($category->icon) {
            $this->deleteIcon($category->icon);
        }
    }

    public function create(mixed $data): Category
    {
        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            $data['icon'] = $this->uploadIcon($data['icon']);
        }

        return $this->repo->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            $data['icon'] = $this->handleUpdateIcon($category, $data['icon']);
        }

        return $this->repo->update($category->id, $data);
    }
}
