<?php

namespace App\Http\Controllers\Master;

use App\Helpers\BaseResponse;
use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\CourseRequest;
use App\Http\Resources\Master\CourseResource;
use App\Http\Resources\Master\CourseUpdateResource;
use App\Repositories\Master\CourseRepository;
use App\Services\Master\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    private $repo;
    private $service;

    public function __construct(CourseRepository $repo, CourseService $service)
    {
        $this->repo = $repo;
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->perPage ?? 5;
        $page = $request->page ?? 1;
        $payload = $request->only([
            'search',
            'category_id',
            'status',
        ]);
        $courses = $this->repo->paginate($payload, $perPage, $page);
        return BaseResponse::success('Daftar kursus', [
            'data' => CourseResource::collection($courses),
            'pagination' => PaginationHelper::paginate($courses),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CourseRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $course = $this->service->create($data);
            DB::commit();
            return BaseResponse::Create('Kursus berhasil dibuat', new CourseResource($course));
        } catch (\Exception $e) {
            DB::rollback();
            return BaseResponse::error('Gagal membuat kursus: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = $this->repo->findById($id);
        if (!$course) {
            return BaseResponse::Error('Kursus tidak ditemukan', 404);
        }

        return BaseResponse::Success('Detail kursus', new CourseResource($course));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CourseRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $check = $this->repo->findById($id);
            if (!$check) {
                DB::rollback();
                return BaseResponse::Error('Kursus tidak ditemukan', 404);
            }
            $data = $request->validated();
            $course = $this->service->update($id, $data);
            DB::commit();
            return BaseResponse::Success('Kursus berhasil diupdate', new CourseResource($course));
        } catch (\Exception $e) {
            DB::rollback();
            return BaseResponse::error('Gagal update kursus: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $check = $this->repo->findById($id);
            if (!$check) {
                DB::rollback();
                return BaseResponse::Error('Kursus tidak ditemukan', 404);
            }

            // Hapus thumbnail jika ada
            if ($check->thumbnail) {
                $this->service->deleteThumbnail($check->thumbnail);
            }

            $course = $this->repo->delete($id);
            DB::commit();
            return BaseResponse::Success('Kursus berhasil dihapus', null);
        } catch (\Exception $e) {
            DB::rollback();
            return BaseResponse::error('Gagal hapus kursus: ' . $e->getMessage(), 500);
        }
    }
}
