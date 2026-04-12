<?php

namespace App\Repositories\Payment;

use App\Models\Transaction;

class TransactionRepository
{
    private $model;

    public function __construct(Transaction $model)
    {
        $this->model = $model;
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
        $transaction = $this->model->find($id);
        if ($transaction) {
            $transaction->update($data);
            return $transaction->fresh();
        }
        return null;
    }
}
