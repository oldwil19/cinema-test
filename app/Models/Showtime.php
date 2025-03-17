<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    use HasFactory;

    protected $fillable = ['movie_id', 'movie_title', 'auditorium_id', 'start_time', 'available_seats', 'reserved_seats'];

    protected $casts = [
        'available_seats' => 'array',
        'reserved_seats' => 'array',
    ];

    public function auditorium()
    {
        return $this->belongsTo(Auditorium::class, 'auditorium_id');
    }
}
