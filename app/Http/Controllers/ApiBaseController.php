<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ApiBaseController extends Controller
{
    protected function success(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
        ], $status);
    }

    protected function error(string $message = 'Error', mixed $errors = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $status);
    }

    protected function authorizeUser($user, string $permission): void
    {
        if (!$user->hasPermissionTo($permission) && !$user->hasRole('admin')) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }
}
