<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Reservation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['id', 'showtime_id', 'seats', 'status', 'expires_at'];

    protected $casts = [
        'seats' => 'array',
        'expires_at' => 'datetime',
    ];

    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }
}
