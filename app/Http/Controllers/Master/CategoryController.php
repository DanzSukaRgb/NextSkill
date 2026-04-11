<?php

namespace App\Http\Controllers\Master;

use App\Helpers\BaseResponse;
use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\CategoryRequest;
use App\Http\Resources\Master\CategoryResource;
use App\Repositories\Master\CategoryRepository;
use App\Services\Master\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    private $repo;
    private $service;

    public function __construct(CategoryRepository $repo, CategoryService $service)
    {
        $this->repo = $repo;
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->perPage ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search ?? null;
        $categories = $this->repo->paginate(['search' => $search], $perPage, $page);
        return BaseResponse::success('List kategori', [
            'data' => CategoryResource::collection($categories),
            'pagination' => PaginationHelper::paginate($categories),
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
    public function store(CategoryRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $category = $this->service->create($data);
            DB::commit();
            return BaseResponse::Create('Kategori berhasil dibuat', new CategoryResource($category));
        } catch (\Exception $e) {
            DB::rollBack();
            return BaseResponse::error('Gagal membuat kategori: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = $this->repo->findById($id);
        if (!$category) {
            return BaseResponse::Error('Kategori tidak ditemukan', 404);
        }

        return BaseResponse::Success('Detail kategori', new CategoryResource($category));
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
    public function update(CategoryRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $check = $this->repo->findById($id);
            if (!$check) {
                DB::rollBack();
                return BaseResponse::Error('Kategori tidak ditemukan', 404);
            }
            $data = $request->validated();
            // dd($data);
            $updatedCategory = $this->service->update($id, $data);
            DB::commit();
            return BaseResponse::Success('Kategori berhasil diperbarui', new CategoryResource($updatedCategory));
        } catch (\Exception $e) {
            DB::rollBack();
            return BaseResponse::Error('Gagal memperbarui kategori: ' . $e->getMessage(), 500);
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
                DB::rollBack();
                return BaseResponse::Error('Kategori tidak ditemukan', 404);
            }
            $this->repo->delete($id);
            DB::commit();
            return BaseResponse::Success('Kategori berhasil dihapus', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return BaseResponse::Error('Gagal menghapus kategori: ' . $e->getMessage(), 500);
        }
    }
}
