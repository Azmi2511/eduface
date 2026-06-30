<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class Destination extends Model
{
    use HasFactory;
    protected $table = 'tbl_destination';
    protected $fillable = [
        'foto',
        'nama_destinasi',
        'lokasi',
        'harga_tiket',
        'kategori',
        'deskripsi',
    ];
    protected $casts = [
        'harga_tiket' => 'double',
    ];
    public function getFotoUrlAttribute()
    {
        if (!$this->foto) {
            return null;
        }
        // Jika foto disimpan sebagai URL eksternal (misal dummy data/seeder)
        if (filter_var($this->foto, FILTER_VALIDATE_URL)) {
            return $this->foto;
        }
        // Jika foto disimpan di lokal storage public
        return url('storage/' . $this->foto);
    }
}
