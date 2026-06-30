<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DestinationApiController extends Controller
{
    /**
     * 1. READ: Mengambil Semua Data Destinasi
     */
    public function index()
    {
        try {
            $destinations = DB::table('tbl_destination')->orderBy('id', 'desc')->get();
            // Ubah path foto menjadi URL lengkap agar bisa dibaca langsung oleh Flutter
            foreach ($destinations as $dest) {
                if ($dest->foto && !filter_var($dest->foto, FILTER_VALIDATE_URL)) {
                    $dest->foto = url('storage/' . $dest->foto);
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Daftar destinasi berhasil diambil.',
                'data' => $destinations
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data destinasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * 2. CREATE: Menambahkan Destinasi Baru (Dengan Upload Foto)
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'nama_destinasi' => 'required|string|max:150',
            'lokasi' => 'required|string|max:150',
            'harga_tiket' => 'required|numeric',
            'kategori' => 'required|string',
            'deskripsi' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            // Upload Foto ke storage/app/public/destinations
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('destinations', 'public');
            }
            // Simpan ke database
            $id = DB::table('tbl_destination')->insertGetId([
                'foto' => $fotoPath,
                'nama_destinasi' => $request->nama_destinasi,
                'lokasi' => $request->lokasi,
                'harga_tiket' => $request->harga_tiket,
                'kategori' => $request->kategori,
                'deskripsi' => $request->deskripsi,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $newDestination = DB::table('tbl_destination')->where('id', $id)->first();
            if ($newDestination->foto) {
                $newDestination->foto = url('storage/' . $newDestination->foto);
            }
            return response()->json([
                'success' => true,
                'message' => 'Destinasi baru berhasil disimpan.',
                'data' => $newDestination
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan destinasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * 3. SHOW: Menampilkan Detail Destinasi Berdasarkan ID
     */
    public function show($id)
    {
        try {
            $destination = DB::table('tbl_destination')->where('id', $id)->first();
            if (!$destination) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destinasi tidak ditemukan.'
                ], 404);
            }
            if (
                $destination->foto && !filter_var(
                    $destination->foto,
                    FILTER_VALIDATE_URL
                )
            ) {
                $destination->foto = url('storage/' . $destination->foto);
            }
            return response()->json([
                'success' => true,
                'message' => 'Detail destinasi berhasil ditemukan.',
                'data' => $destination
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail destinasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * 4. UPDATE: Mengubah Data Destinasi
     */
    public function update(Request $request, $id)
    {
        $destination = DB::table('tbl_destination')->where('id', $id)->first();
        if (!$destination) {
            return response()->json([
                'success' => false,
                'message' => 'Destinasi tidak ditemukan.'
            ], 404);
        }
        // Validasi input (foto bersifat opsional saat update)
        $validator = Validator::make($request->all(), [
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'nama_destinasi' => 'required|string|max:150',
            'lokasi' => 'required|string|max:150',
            'harga_tiket' => 'required|numeric',
            'kategori' => 'required|string',
            'deskripsi' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $fotoPath = $destination->foto;
            // Jika ada upload foto baru
            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada di storage
                if ($destination->foto && Storage::disk('public')->exists($destination->foto)) {
                    Storage::disk('public')->delete($destination->foto);
                }
                // Simpan foto baru
                $fotoPath = $request->file('foto')->store('destinations', 'public');
            }
            // Update di database
            DB::table('tbl_destination')->where('id', $id)->update([
                'foto' => $fotoPath,
                'nama_destinasi' => $request->nama_destinasi,
                'lokasi' => $request->lokasi,
                'harga_tiket' => $request->harga_tiket,
                'kategori' => $request->kategori,
                'deskripsi' => $request->deskripsi,
                'updated_at' => now(),
            ]);
            $updatedData = DB::table('tbl_destination')->where('id', $id)->first();
            if ($updatedData->foto) {
                $updatedData->foto = url('storage/' . $updatedData->foto);
            }
            return response()->json([
                'success' => true,
                'message' => 'Destinasi berhasil diperbarui.',
                'data' => $updatedData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui destinasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * 5. DELETE: Menghapus Data Destinasi
     */
    public function destroy($id)
    {
        try {
            $destination = DB::table('tbl_destination')->where('id', $id)->first();
            if (!$destination) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destinasi tidak ditemukan.'
                ], 404);
            }
            // Hapus berkas file foto dari storage
            if ($destination->foto && Storage::disk('public')->exists($destination->foto)) {
                Storage::disk('public')->delete($destination->foto);
            }
            // Hapus baris dari database
            DB::table('tbl_destination')->where('id', $id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Destinasi berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus destinasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}