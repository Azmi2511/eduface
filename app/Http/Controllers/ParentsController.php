<?php

namespace App\Http\Controllers;

use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentsController extends AdminBaseController
{
    /**
     * Menampilkan daftar orang tua.
     */
    public function index(Request $request)
    {
        $query = ParentProfile::with('user');

        // 2. Filter Search (Nama atau Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($u) use ($search) {
                $u->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $status = $request->is_active;
            $query->whereHas('user', function($q) use ($status) {
                $q->where('is_active', $status);
            });
        }

        $parents = $query->orderBy('created_at', 'desc')->paginate(10);

        $count_total = ParentProfile::count();
        $count_active = User::where('role', 'parent')->where('is_active', 1)->count();
        $count_inactive = User::where('role', 'parent')->where('is_active', 0)->count();

        return view('admin::parents.index', compact(
            'parents',
            'count_total', 'count_active', 'count_inactive'
        ));
    }

    /**
     * Menyimpan data orang tua baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            'password'     => 'required|string|min:6',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Create User
            $username = explode('@', $request->email)[0];
            
            $user = User::create([
                'full_name' => $request->full_name,
                'username'  => $username,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'parent',
                'is_active' => 1,
                'phone'     => $request->phone_number ?? null,
            ]);
            ParentProfile::create([
                'user_id'   => $user->id,
                'relationship' => $request->relationship ?? null,
                'fcm_token' => $request->fcm_token ?? null,
            ]);
        });

        return redirect()->route('admin::parents.index')
            ->with('success', 'Data orang tua berhasil ditambahkan.');
    }

    /**
     * Memperbarui data orang tua.
     */
    public function update(Request $request, $id)
    {
        $parent = ParentProfile::where('user_id', $id)->firstOrFail();

        $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $id,
            'phone'        => 'nullable|string|max:50',
            'dob'          => 'nullable|date',
            'gender'       => 'nullable|in:L,P',
            'relationship' => 'nullable|in:Ayah,Ibu,Wali',
            'fcm_token'    => 'nullable|string',
            'status'       => 'required|in:0,1',
        ]);

        DB::transaction(function () use ($request, $parent) {
            $parent->user->update([
                'full_name' => $request->full_name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'dob'       => $request->dob,
                'gender'    => $request->gender,
                'is_active' => $request->status,
            ]);

            $parent->update([
                'relationship' => $request->relationship ?? $parent->relationship,
                'fcm_token'    => $request->fcm_token ?? $parent->fcm_token,
            ]);
        });

        return redirect()->route('admin::parents.index')
            ->with('success', 'Data orang tua berhasil diperbarui.');
    }

    /**
     * Menghapus data orang tua.
     */
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            ParentProfile::where('user_id', $id)->delete();
            User::where('id', $id)->where('role', 'parent')->delete();
        });

        return redirect()->route('admin::parents.index')
            ->with('success', 'Data orang tua berhasil dihapus.');
    }
}