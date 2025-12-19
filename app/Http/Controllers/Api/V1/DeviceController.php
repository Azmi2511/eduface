<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Http\Requests\Api\V1\Device\StoreRequest;
use App\Http\Resources\Api\V1\DeviceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    /**
     * Menampilkan daftar perangkat IoT.
     */
    public function index(Request $request)
    {
        $query = Device::withCount('attendanceLogs');

        if ($request->filled('search')) {
            $query->where('device_name', 'like', "%{$request->search}%")
                  ->orWhere('location', 'like', "%{$request->search}%");
        }

        return DeviceResource::collection($query->latest()->get());
    }

    /**
     * Menambahkan perangkat IoT baru.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        
        // Auto-generate token jika tidak diisi
        if (empty($data['api_token'])) {
            $data['api_token'] = Str::random(64);
        }

        $device = Device::create($data);

        return (new DeviceResource($device))
            ->additional(['message' => 'Perangkat berhasil diregistrasi']);
    }

    /**
     * Menampilkan detail perangkat IoT.
     */
    public function show(Device $device)
    {
        return new DeviceResource($device->loadCount('attendanceLogs'));
    }

    /**
     * Memperbarui informasi perangkat IoT.
     */
    public function update(Request $request, Device $device)
    {
        $request->validate([
            'device_name' => 'sometimes|string|max:255',
            'location'    => 'sometimes|string|max:255',
            'api_token'   => 'sometimes|string|max:255|unique:devices,api_token,' . $device->id,
        ]);

        $device->update($request->all());

        return (new DeviceResource($device))
            ->additional(['message' => 'Informasi perangkat diperbarui']);
    }

    /**
     * Menghapus perangkat IoT.
     */
    public function destroy(Device $device)
    {
        // Cegah hapus jika sudah ada log absensi (Integritas Data)
        if ($device->attendanceLogs()->exists()) {
            return response()->json([
                'message' => 'Perangkat tidak bisa dihapus karena memiliki riwayat log absensi.'
            ], 422);
        }

        $device->delete();
        return response()->json(['message' => 'Perangkat berhasil dihapus']);
    }
}