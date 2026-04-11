<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  Roles yang diizinkan (bisa multiple: 'admin,moderator,editor')
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        // Check jika user authenticated
        if (!$request->user()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'code' => 401
            ], 401);
        }

        // Parse multiple roles (admin,moderator,editor)
        $allowedRoles = array_map('trim', explode(',', $roles));
        $userRole = $request->user()->role;

        // Check apakah user punya salah satu dari role yang diizinkan
        if (!in_array($userRole, $allowedRoles)) {
            // Generate pesan error yang lebih descriptive berdasarkan route
            $message = $this->getCustomMessage($request, $allowedRoles);

            return response()->json([
                'status' => false,
                'message' => $message,
                'code' => 403,
                'data' => [
                    'required_role' => $allowedRoles,
                    'user_role' => $userRole
                ]
            ], 403);
        }

        return $next($request);
    }

    /**
     * Get custom message berdasarkan route
     */
    private function getCustomMessage($request, array $allowedRoles): string
    {
        $path = $request->path();
        $roles = implode(' atau ', $allowedRoles);

        // Custom messages untuk berbagai module
        $messages = [
            'categories' => "Menu Kategori khusus untuk {$roles}",
            'reports' => "Menu Laporan khusus untuk {$roles}",
            'users' => "Menu User khusus untuk {$roles}",
            'settings' => "Menu Pengaturan khusus untuk {$roles}",
        ];

        // Check apakah path match dengan known route
        foreach ($messages as $routePattern => $customMessage) {
            if (str_starts_with($path, "api/{$routePattern}") || str_starts_with($path, "{$routePattern}")) {
                return $customMessage;
            }
        }

        // Default message
        return "Akses ditolak - Diperlukan role: {$roles}";
    }
}

