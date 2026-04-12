<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Helpers\PaginationHelper;
use App\Http\Requests\Master\ApplyRequest;
use App\Http\Requests\Master\UpdateStatusRequest;
use App\Repositories\Master\CourseMentorApplicationRepository;
use App\Services\Master\CourseMentorApplicationService;
use Illuminate\Http\Request;

class CourseMentorApplicationController extends Controller
{
    private $repo;
    private $service;

    public function __construct(CourseMentorApplicationRepository $repo, CourseMentorApplicationService $service)
    {
        $this->repo = $repo;
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $perPage = $request->perPage ?? 5;
        $page = $request->page ?? 1;
        $applications = $this->repo->paginate($perPage, $page);
        return BaseResponse::success('Daftar aplikasi mentor kursus', [
            'data' => $applications->items(),
            'pagination' => PaginationHelper::paginate($applications),
        ]);
    }

    public function apply(ApplyRequest $request, $courseId)
    {
        $request->validated();

        try {
            $userId = auth()->id();
            $application = $this->service->apply($courseId, $userId, $request->motivation);
            return BaseResponse::success('Aplikasi mentor berhasil diajukan', [
                'data' => $application,
            ]);
        } catch (\Exception $e) {
            return BaseResponse::error($e->getMessage(), 400);
        }
    }

    public function updateStatus(UpdateStatusRequest $request, $id)
    {
        $request->validated();
        try {
            if ($request->status === 'approved') {
                $application = $this->service->approve($id);
            } else {
                $application = $this->service->reject($id, $request->rejection_reason ?? '');
            }

            return BaseResponse::success('Status aplikasi mentor berhasil diperbarui', [
                'data' => $application,
            ]);
        } catch (\Exception $e) {
            return BaseResponse::error($e->getMessage(), 400);
        }
    }

    public function listMentorApplyPending()
    {
        $mentorId = auth()->id();
        $applications = $this->repo->listMentorApplyPending($mentorId);
        return BaseResponse::success('Daftar aplikasi mentor kursus yang pending', [
            'data' => $applications,
        ]);
    }
}
