<?php

namespace App\Services\Master;

use App\Models\Course;
use App\Repositories\Master\CourseRepository;
use App\Traits\HasFileUpload;
use Illuminate\Http\UploadedFile;

class CourseService
{
    use HasFileUpload;
    private $repo;

    public function __construct(CourseRepository $repo)
    {
        $this->repo = $repo;
        $this->uploadPath = 'courses';
    }

    /**
     * Upload thumbnail kursus
     */
    public function uploadThumbnail(?UploadedFile $file): ?string
    {
        return $this->uploadFile($file);
    }

    /**
     * Hapus thumbnail kursus
     */
    public function deleteThumbnail(?string $thumbnailPath): bool
    {
        return $this->deleteFile($thumbnailPath);
    }

    public function create(array $data)
    {
        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
            $data['thumbnail'] = $this->uploadThumbnail($data['thumbnail']);
        }

        return $this->repo->create($data);
    }

    public function update(string $id, array $data): Course
    {
        $course = $this->repo->findById($id);
        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
            if ($course->thumbnail) {
                $this->deleteThumbnail($course->thumbnail);
            }

            $data['thumbnail'] = $this->uploadThumbnail($data['thumbnail']);
        }

        return $this->repo->update($course->id, $data);
    }
}
