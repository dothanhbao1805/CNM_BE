<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\Interfaces\UserServiceInterface;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    protected $userService;
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->getAllUsers();
        return UserResource::collection($users);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function store(CreateUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());
        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }
    public function update(UpdateUserRequest $request, User $user)
    {
        Log::info('Update User Request', [
            'user_id' => $user->id,
            'data' => $request->all(),
            'validated' => $request->validated(),
            'files' => $request->allFiles(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
        ]);
    
        // Hoặc log chi tiết hơn
        Log::info('Avatar file info', [
            'has_avatar' => $request->hasFile('avatar'),
            'avatar_info' => $request->file('avatar') ? [
                'name' => $request->file('avatar')->getClientOriginalName(),
                'size' => $request->file('avatar')->getSize(),
                'mime' => $request->file('avatar')->getMimeType(),
        ] : null
    ]);
        $updatedUser = $this->userService->updateUser($user, $request->validated());
        return new UserResource($updatedUser);
    }

    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);
        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }

}
