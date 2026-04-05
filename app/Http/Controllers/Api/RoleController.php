<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function getRoleData(Request $request): JsonResponse
    {
        $query = Role::with('permissions')->withCount('users');

        if ($request->filled('searchQuery')) {
            $query->where('name', 'like', '%' . $request->searchQuery . '%');
        }

        $total    = $query->count();
        $page     = (int) $request->get('page', 1);
        $perPage  = (int) $request->get('itemsPerPage', 10);
        $sortBy   = $request->get('sortBy', 'name');
        $orderBy  = $request->get('orderBy', 'asc');

        $roles = $query->orderBy($sortBy, $orderBy)
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'name'        => $r->name,
                'permissions' => $r->permissions->pluck('name'),
                'users_count' => $r->users_count,
            ]);

        return response()->json(['data' => $roles, 'total' => $total]);
    }

    public function getRoleDetails(Request $request): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($request->id);

        return response()->json([
            'data' => [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'users_count' => $role->users()->count(),
            ],
        ]);
    }

    public function manageRole(Request $request): JsonResponse
    {
        $isUpdate = $request->filled('id');

        $data = $request->validate([
            'id'            => 'nullable|integer|exists:roles,id',
            'name'          => 'required|string|unique:roles,name,' . ($request->id ?? 'NULL'),
            'permissions'   => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($isUpdate) {
            $role = Role::findOrFail($data['id']);
            $role->update(['name' => $data['name']]);
        } else {
            $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        }

        $role->syncPermissions($data['permissions'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Role ' . ($isUpdate ? 'updated' : 'created') . ' successfully.',
            'data'    => [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ], $isUpdate ? 200 : 201);
    }

    public function roleDelete(Request $request): JsonResponse
    {
        $ids = (array) ($request->ids ?? [$request->id]);

        foreach ($ids as $id) {
            $role = Role::findOrFail($id);
            if ($role->users()->count() > 0) {
                return response()->json(['success' => false, 'message' => "Role '{$role->name}' has assigned users and cannot be deleted."], 422);
            }
            $role->delete();
        }

        return response()->json(['success' => true, 'message' => 'Role(s) deleted successfully.']);
    }

    public function roleStatistic(): JsonResponse
    {
        return response()->json([
            'data' => [
                'total'       => Role::count(),
                'with_users'  => Role::has('users')->count(),
                'permissions' => Permission::count(),
                'users'       => \App\Models\User::count(),
            ],
        ]);
    }

    public function getAllPermissions(): JsonResponse
    {
        return response()->json(['data' => Permission::orderBy('name')->pluck('name')]);
    }
}
