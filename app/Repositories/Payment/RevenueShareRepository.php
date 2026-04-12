<?php

namespace App\Repositories\Payment;

use App\Models\RevenueShare;

class RevenueShareRepository
{
    private $model;

    public function __construct(RevenueShare $model)
    {
        $this->model = $model;
    }

    public function getCurrent()
    {
        return $this->model->latest()->first();
    }

    public function update(array $data)
    {
        $current = $this->getCurrent();
        if ($current) {
            $current->update($data);
            return $current->fresh();
        }
        return null;
    }
}