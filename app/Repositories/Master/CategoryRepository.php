<?php

namespace App\Repositories\Master;

use App\Models\Category;

class CategoryRepository
{
    private $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function paginate(?array $data, int $perPage = 10, int $page = 1)
    {
        $data = $data ?? [];
        $model = $this->model->query()->withCount('courses');

        if (isset($data['search'])) {
            $model->where('name', 'like', '%' . $data['search'] . '%');
        }
        return $model->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function existsById($id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $category = $this->model->find($id);
        $category->update($data);
        return $category->fresh();
    }

    public function delete($id)
    {
        $category = $this->model->find($id);
        $category->delete();
        return $category;
    }
}