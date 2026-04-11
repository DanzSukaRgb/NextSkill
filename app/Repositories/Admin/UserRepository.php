<?php

namespace App\Repositories\Admin;

use App\Models\User;

class UserRepository
{
    private $model;

    public function __construct(User $model)
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
        $model = $this->model->query()
            ->where(function ($query) {
                $query->where('role', 'student')
                    ->orWhere('role', 'mentor');
            });

        if (isset($data['search'])) {
            $model->where(function ($query) use ($data) {
                $query->where('name', 'like', '%' . $data['search'] . '%')
                    ->orWhere('email', 'like', '%' . $data['search'] . '%');
            });
        }

        if (isset($data['role'])) {
            $model->where('role', $data['role']);
        }

        $model->orderBy('created_at', 'desc');
        return $model->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $user = $this->model->find($id);
        $user->update($data);
        return $user->fresh();
    }

    public function delete($id)
    {
        $user = $this->model->find($id);
        $user->delete();
        return $user->fresh();
    }
}