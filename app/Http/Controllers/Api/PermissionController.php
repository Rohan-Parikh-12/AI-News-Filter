<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function getPermissionData(Request $request): JsonResponse
    {
        $query = Permission::with('roles');

        if ($request->filled('searchQuery')) {
            $query->where('name', 'like', '%' . $request->searchQuery . '%');
        }

        $total   = $query->count();
        $page    = (int) $request->get('page', 1);
        $perPage = (int) $request->get('itemsPerPage', 10);
        $sortBy  = $request->get('sortBy', 'name');
        $orderBy = $request->get('orderBy', 'asc');

        $permissions = $query->orderBy($sortBy, $orderBy)
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'roles'      => $p->roles->pluck('name'),
                'created_at' => $p->created_at?->format('d M Y, g:i A'),
            ]);

        return response()->json(['data' => $permissions, 'total' => $total]);
    }

    public function getPermissionDetails(Request $request): JsonResponse
    {
        $permission = Permission::with('roles')->findOrFail($request->id);

        return response()->json([
            'data' => [
                'id'    => $permission->id,
                'name'  => $permission->name,
                'roles' => $permission->roles->pluck('name'),
            ],
        ]);
    }

    public function managePermission(Request $request): JsonResponse
    {
        $isUpdate = $request->filled('id');

        $data = $request->validate([
            'id'   => 'nullable|integer|exists:permissions,id',
            'name' => 'required|string|unique:permissions,name,' . ($request->id ?? 'NULL'),
        ]);

        if ($isUpdate) {
            $permission = Permission::findOrFail($data['id']);
            $permission->update(['name' => $data['name']]);
        } else {
            $permission = Permission::create(['name' => $data['name'], 'guard_name' => 'web']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permission ' . ($isUpdate ? 'updated' : 'created') . ' successfully.',
            'data'    => ['id' => $permission->id, 'name' => $permission->name, 'roles' => $permission->roles->pluck('name'), 'created_at' => $permission->created_at?->format('d M Y, g:i A')],
        ], $isUpdate ? 200 : 201);
    }

    public function permissionDelete(Request $request): JsonResponse
    {
        $ids = (array) ($request->ids ?? [$request->id]);

        foreach ($ids as $id) {
            Permission::findOrFail($id)->delete();
        }

        return response()->json(['success' => true, 'message' => 'Permission(s) deleted successfully.']);
    }

    public function permissionStatistic(): JsonResponse
    {
        $permissions = Permission::with('roles')->get();

        return response()->json([
            'data' => [
                'total'    => $permissions->count(),
                'assigned' => $permissions->filter(fn($p) => $p->roles->count() > 0)->count(),
                'roles'    => \Spatie\Permission\Models\Role::count(),
                'modules'  => $permissions->map(fn($p) => explode('-', $p->name)[0])->unique()->count(),
            ],
        ]);
    }
}
