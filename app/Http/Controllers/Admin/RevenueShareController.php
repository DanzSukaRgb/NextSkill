<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\RevenueShareRequest;
use App\Repositories\Payment\RevenueShareRepository;
use App\Services\Payment\RevenueShareService;
// use Illuminate\Http\Request;

class RevenueShareController extends Controller
{
    private $repo;
    private $service;

    public function __construct(RevenueShareRepository $repo, RevenueShareService $service)
    {
        $this->repo = $repo;
        $this->service = $service;
    }

    public function updateRevenueShare(RevenueShareRequest $request)
    {
        $updated = $this->service->updateRevenueShare($request->validated());

        if (!$updated) {
            return BaseResponse::error('Gagal memperbarui revenue share', 500);
        }

        return BaseResponse::success('Revenue share berhasil diperbarui', $updated);
    }

    public function getCurrentRevenueShare()
    {
        $current = $this->repo->getCurrent();

        if (!$current) {
            return BaseResponse::error('Data revenue share tidak ditemukan', 404);
        }

        return BaseResponse::success('Data revenue share saat ini', $current);
    }
}
