<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function uploadFile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|mimes:jpg,jpeg,png|max:10240', // 10MB
        ]);

        $file = $validated['file'];

        $filename = time() . '_' . Str::slug($validated['name']) . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('uploads/'.$validated['name'], $filename, 'public');

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'name' => $validated['name'],
                'file_path' => $path,
                'file_url' => asset('storage/' . $path),
            ],
        ], 201);
    }
}
