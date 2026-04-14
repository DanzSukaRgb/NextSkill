<?php

namespace App\Repositories\Master;

use App\Models\Course;

class CourseRepository
{
    private $model;

    public function __construct(Course $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function paginate(?array $data, int $perPage = 5, int $page = 1)
    {
        $data = $data ?? [];
        $model = $this->model->query()
            ->with('category:id,name', 'user:id,name,avatar');

        if (isset($data['search'])) {
            $model->where('title', 'like', '%' . $data['search'] . '%');
        }

        if (isset($data['category_id'])) {
            $model->where('category_id', $data['category_id']);
        }

        if (isset($data['status'])) {
            $model->where('status', $data['status']);
        }

        if(isset($data['mentor'])) {
            $model->whereHas('user', function($query) use ($data) {
                $query->where('name', 'like', '%' . $data['mentor'] . '%');
            });
        }

        $model->orderBy('created_at', 'desc');
        return $model->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        return $this->model->with('category:id,name', 'user:id,name,avatar')->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function update($id, array $data)
    {
        $course = $this->model->find($id);
        $course->update($data);
        return $course->fresh();
    }

    public function delete($id)
    {
        $course = $this->model->find($id);
        $course->delete();
        return $course;
    }

    public function addMentor($courseId, $mentorId)
    {
        $course = $this->model->find($courseId);
        $course->update(['user_id' => $mentorId]);
        return $course;
    }

    public function courseNotHaveMentor()
    {
        return $this->model->whereNull('user_id')->get();
    }

    public function listCourseActiveByMentor($mentorId)
    {
        return $this->model->where('user_id', $mentorId)->get();
    }
}
