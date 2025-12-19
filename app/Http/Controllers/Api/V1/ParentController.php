<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\User;
use App\Http\Requests\Api\V1\Parent\StoreRequest;
use App\Http\Requests\Api\V1\Parent\UpdateRequest;
use App\Http\Resources\Api\V1\ParentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ParentController extends Controller
{
    /**
     * Menampilkan daftar orang tua.
     */
    public function index(Request $request)
    {
        $query = ParentProfile::with(['user']);

        // Search by User Fields (Name/Email)
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Filter by Relationship
        if ($request->filled('relationship')) {
            $query->where('relationship', $request->relationship);
        }

        $parents = $query->latest()->paginate($request->get('per_page', 10));
        return ParentResource::collection($parents);
    }

    /**
     * Menambahkan akun orang tua baru.
     */
    public function store(StoreRequest $request)
    {
        try {
            $parent = DB::transaction(function () use ($request) {
                // Generate Unique Username
                $baseUsername = Str::slug(explode('@', $request->email)[0]);
                $username = $baseUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter++;
                }

                $user = User::create([
                    'full_name' => $request->full_name,
                    'username'  => $username,
                    'email'     => $request->email,
                    'password'  => Hash::make($request->password),
                    'role'      => 'parent',
                    'phone'     => $request->phone,
                    'gender'    => $request->gender,
                    'is_active' => 1,
                ]);

                return ParentProfile::create([
                    'user_id'      => $user->id,
                    'relationship' => $request->relationship,
                    'fcm_token'    => $request->fcm_token,
                ]);
            });

            return (new ParentResource($parent->load('user')))
                ->additional(['message' => 'Akun orang tua berhasil dibuat']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat akun', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan detail akun orang tua.
     */
    public function show($id)
    {
        $parent = ParentProfile::with(['user', 'students'])->findOrFail($id);
        return new ParentResource($parent);
    }

    /**
     * Memperbarui informasi akun orang tua.
     */
    public function update(UpdateRequest $request, $id)
    {
        $parent = ParentProfile::findOrFail($id);

        try {
            DB::transaction(function () use ($request, $parent) {
                // Update User table
                $userData = $request->only(['full_name', 'email', 'phone', 'gender', 'is_active']);
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                $parent->user->update($userData);

                // Update Parent table
                $parent->update($request->only(['relationship', 'fcm_token']));
            });

            return (new ParentResource($parent->fresh('user')))
                ->additional(['message' => 'Data orang tua berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui data', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus akun orang tua.
     */
    public function destroy($id)
    {
        $parent = ParentProfile::findOrFail($id);
        
        // Hapus User (Otomatis hapus ParentProfile karena cascade)
        $parent->user->delete();
        
        return response()->json(['message' => 'Akun orang tua berhasil dihapus']);
    }

    /**
     * Memperbarui FCM Token.
     */
    public function updateFcmToken(Request $request, $id)
    {
        $request->validate(['fcm_token' => 'required|string']);
        $parent = ParentProfile::findOrFail($id);
        $parent->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'FCM Token updated']);
    }
}