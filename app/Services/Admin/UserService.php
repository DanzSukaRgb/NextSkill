<?php

namespace App\Services\Admin;

use App\Repositories\Admin\UserRepository;
use App\Traits\HasFileUpload;
use Illuminate\Http\UploadedFile;

class UserService
{
    use HasFileUpload;
    private $repo;

    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    private function uploadAvatar(?UploadedFile $file): ?string
    {
        return $this->uploadFile($file, 'avatars');
    }

    public function create(array $data)
    {
        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $data['avatar'] = $this->uploadAvatar($data['avatar']);
        }
        return $this->repo->create($data);
    }

    public function update(string $id, array $data)
    {
        $user = $this->repo->findById($id);
        if (!$user) {
            throw new \Exception('User tidak ditemukan');
        }

        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            if ($user->avatar) {
                $this->deleteFile($user->avatar);
            }
            $data['avatar'] = $this->uploadAvatar($data['avatar']);
        }

        return $this->repo->update($user->id, $data);
    }
}