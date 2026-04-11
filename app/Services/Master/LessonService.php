<?php

namespace App\Services\Master;

use App\Models\Lesson;
use App\Repositories\Master\LessonRepository;
use App\Traits\HasFileUpload;
use Illuminate\Http\UploadedFile;

class LessonService
{
    use HasFileUpload;
    private $repo;

    public function __construct(LessonRepository $repo)
    {
        $this->repo = $repo;
        $this->uploadPath = 'lessons';
    }

    /**
     * Upload file materi lesson
     */
    public function uploadLessonFile(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }
        return $this->uploadFile($file);
    }

    /**
     * Hapus file materi lesson
     */
    public function deleteLessonFile(?string $filePath): bool
    {
        return $this->deleteFile($filePath);
    }

    public function create(string $courseId, array $data)
    {
        // Auto-assign order number jika tidak ada
        if (!isset($data['order_number'])) {
            $data['order_number'] = $this->repo->getMaxOrderNumber($courseId) + 1;
        }

        $data['course_id'] = $courseId;

        if (isset($data['file_path']) && $data['file_path'] instanceof UploadedFile) {
            $data['file_path'] = $this->uploadLessonFile($data['file_path']);
        }

        return $this->repo->create($data);
    }

    public function update(string $id, array $data): Lesson
    {
        $lesson = $this->repo->findById($id);

        if (isset($data['file_path']) && $data['file_path'] instanceof UploadedFile) {
            if ($lesson->file_path) {
                $this->deleteLessonFile($lesson->file_path);
            }
            $data['file_path'] = $this->uploadLessonFile($data['file_path']);
        }

        return $this->repo->update($id, $data);
    }
}
