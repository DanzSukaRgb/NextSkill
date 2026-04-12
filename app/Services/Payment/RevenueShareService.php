<?php

namespace App\Services\Payment;

use App\Helpers\BaseResponse;
use App\Repositories\Payment\RevenueShareRepository;

class RevenueShareService
{
    private $repo;

    public function __construct(RevenueShareRepository $repo)
    {
        $this->repo = $repo;
    }

    public function updateRevenueShare(array $data)
    {
        $revenu = $this->repo->getCurrent();
        
        if (!$revenu) {
            return BaseResponse::error('Data revenue share tidak ditemukan', 404);
        }
        
        if ($data['mentor_revenue_share'] + $data['platform_revenue_share'] != 100) {
            return BaseResponse::error('Total persentase pembagian harus 100%', 422); 
        }

        return $this->repo->update($data);
    }
}