<?php

namespace App\Http\Controllers\Master\User;

use App\Helpers\BaseResponse;
use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\admin\UserRequest;
use App\Repositories\Admin\UserRepository;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private $repo;
    private $service;
    public function __construct(UserRepository $repo, UserService $service)
    {
        $this->repo = $repo;
        $this->service = $service;
    }

     /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->perPage ?? 6;
        $page = $request->page ?? 1;
        $payload = $request->only([
            'search',
            'role',
        ]);
        $users = $this->repo->paginate($payload, $perPage, $page);
        return BaseResponse::Success('List user', [
            'data' => $users->items(),
            'pagination' => PaginationHelper::paginate($users),
        ]);
    }

    public function show(string $id)
    {
        $check = $this->repo->findById($id);
        if (!$check) {
            return BaseResponse::Error('User tidak ditemukan', 404);
        }
        return BaseResponse::Success('Detail user', $check);
    }

    public function store(UserRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $user = $this->service->create($data);
            DB::commit();
            return BaseResponse::Create('User berhasil dibuat', $user);
        } catch (\Exception $e) {
            DB::rollback();
            return BaseResponse::Error($e->getMessage(), 500);
        }
    }

    public function update(UserRequest $request, string $id)
    {
        DB::beginTransaction();
        try {
            $check = $this->repo->findById($id);
            if (!$check) {
                return BaseResponse::Error('User tidak ditemukan', 404);
            }
            $data = $request->validated();
            $user = $this->service->update($id, $data);
            DB::commit();
            return BaseResponse::Success('User berhasil diupdate', $user);
        } catch (\Exception $e) {
            DB::rollback();
            return BaseResponse::Error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $check = $this->repo->findById($id);
            if (!$check) {
                return BaseResponse::Error('User tidak ditemukan', 404);
            }
            $user = $this->repo->delete($id);
            DB::commit();
            return BaseResponse::Success('User berhasil dihapus', $user);
        } catch (\Exception $e) {
            DB::rollback();
            return BaseResponse::Error($e->getMessage(), 500);
        }
    }

    public function listMentors(Request $request)
    {
        $perPage = $request->perPage ?? 6;
        $page = $request->page ?? 1;
        $search = $request->search ?? null;
        $mentors = $this->repo->mentorsPaginate([ 'search' => $search], $perPage, $page);
        return BaseResponse::Success('List mentor', [
            'data' => $mentors->items(),
            'pagination' => PaginationHelper::paginate($mentors),
        ]);
    }
}
