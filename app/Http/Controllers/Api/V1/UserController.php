<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Api\V1\User\StoreRequest;
use App\Http\Requests\Api\V1\User\UpdateRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }

        // Filters
        $query->when($request->role, fn($q) => $q->where('role', $request->role))
              ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')));

        $users = $query->latest()->paginate($request->get('per_page', 10));

        return UserResource::collection($users)->additional([
            'meta_counts' => [
                'total' => User::count(),
                'active' => User::where('is_active', 1)->count(),
            ]
        ]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user = User::create($data);
        return (new UserResource($user))->additional(['message' => 'User created successfully']);
    }

    public function show(User $user)
    {
        return new UserResource($user->load(['teacher', 'student', 'parentProfile']));
    }

    public function update(UpdateRequest $request, User $user)
    {
        $data = $request->validated();

        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) Storage::disk('public')->delete($user->profile_picture);
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user->update($data);
        return (new UserResource($user))->additional(['message' => 'User updated successfully']);
    }

    public function destroy(User $user)
    {
        if ($user->profile_picture) Storage::disk('public')->delete($user->profile_picture);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}