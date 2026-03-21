<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($data);
        $user->assignRole('user');

        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'accessToken'      => $token,
            'userData'         => $this->userData($user),
            'userAbilityRules' => $user->abilityRules(),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'accessToken'      => $token,
            'userData'         => $this->userData($user),
            'userAbilityRules' => $user->abilityRules(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'userData'         => $this->userData($user),
            'userAbilityRules' => $user->abilityRules(),
        ]);
    }

    private function userData(User $user): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => $user->getRoleNames()->first() ?? 'user',
            'fullName' => $user->name,
            'username' => explode('@', $user->email)[0],
            'avatar'   => null,
        ];
    }
}
