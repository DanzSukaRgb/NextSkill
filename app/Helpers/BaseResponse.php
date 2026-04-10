<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class BaseResponse
{
    public static function Success(string $message, mixed $data): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'code' => 200,
            'data' => $data,
        ], 200);
    }

    public static function Error(string $message, mixed $data = null): JsonResponse
    {
        if ($message === null) {
            $message = 'Terjadi Kesalahan. Silahkan coba lagi';
        }

        return response()->json([
            'status' => false,
            'message' => $message,
            'code' => 500,
            'data' => $data,
        ], 500);
    }

    public static function NotFound(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Data tidak ditemukan',
            'code' => 404,
            'data' => null,
        ], 404);
    }

    public static function Custom(bool $status, string $message, mixed $data, int $code): JsonResponse
    {
        return response()->json([
            "success" => $status,
            "message" => $message,
            "code" => $code,
            "data" => $data
        ])->setStatusCode($code);
    }

    public static function Paginate(string $message, mixed $data, mixed $paginate): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'code' => 200,
            'paginate' => $paginate,
        ], 200);
    }

    public static function Create(string $message, mixed $data): JsonResponse
    {
        return response()->json([
            "success" => true,
            "message" => $message,
            "code" => 201,
            "data" => $data
        ])->setStatusCode(201);
    }

    public static function Forbidden(string $message = 'Akses ditolak'): JsonResponse
    {
        return response()->json([
            "success" => false,
            "message" => $message,
            "code" => 403,
            "data" => null
        ])->setStatusCode(403);
    }
}