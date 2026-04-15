<?php

namespace App\Repositories\Master;

use App\Models\Lesson;

class LessonRepository
{
    private $model;

    public function __construct(Lesson $model)
    {
        $this->model = $model;
    }

    public function getByCourseId(string $courseId)
    {
        return $this->model->where('course_id', $courseId)
            ->orderBy('order_number', 'asc')
            ->get();
    }

    public function paginateByCourseId(string $courseId, ?array $data, int $perPage = 10, int $page = 1)
    {
        $data = $data ?? [];
        $model = $this->model->where('course_id', $courseId)
            ->with('quizzes');

        if (isset($data['search'])) {
            $model->where('title', 'like', '%' . $data['search'] . '%');
        }

        $model->orderBy('order_number', 'asc');
        return $model->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById(string $id)
    {
        return $this->model->with('course')->find($id);
    }

    public function findByCourseAndId(string $courseId, string $lessonId)
    {
        return $this->model->where('course_id', $courseId)->find($lessonId);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data)
    {
        $lesson = $this->model->find($id);
        $lesson->update($data);
        return $lesson->fresh();
    }

    public function delete(string $id)
    {
        $lesson = $this->model->find($id);
        $lesson->delete();
        return $lesson;
    }

    public function getMaxOrderNumber(string $courseId): int
    {
        $maxOrder = $this->model
            ->where('course_id', $courseId)
            ->max('order_number');
        return $maxOrder ?? 0;
    }
}
