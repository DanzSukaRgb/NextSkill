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


    public function create(mixed $data)
    {
        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            $data['icon'] = $this->uploadIcon($data['icon']);
        }

        return $this->repo->create($data);
    }

    public function update(string $id, array $data): Category
    {
        $category = $this->repo->findById($id);
        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            if ($category->icon) {
                $this->deleteIcon($category->icon);
            }
            
            $data['icon'] = $this->uploadIcon($data['icon']);
        }

        return $this->repo->update($category->id, $data);
    }
}
