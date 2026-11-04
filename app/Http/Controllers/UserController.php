<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected UserService $userService
    ) {}

    public function index(): JsonResponse
    {
        $users = $this->userService->index();

        return $this->success(
            UserResource::collection($users),
            'Daftar pengguna berhasil diambil.'
        );
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->userService->show($id);

        return $this->success(
            new UserResource($user),
            'Detail pengguna berhasil diambil.'
        );
    }

    public function updateRole(
        UpdateUserRoleRequest $request,
        string $id
    ): JsonResponse {
        $user = $this->userService->updateRole(
            $id,
            $request->validated()['role']
        );

        return $this->success(
            new UserResource($user),
            'Role pengguna berhasil diperbarui.'
        );
    }
}
