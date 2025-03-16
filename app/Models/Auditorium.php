<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditorium extends Model
{
    use HasFactory;
    protected $table = 'auditoriums';

    protected $fillable = ['name', 'seats', 'status', 'opening_time', 'closing_time'];

    protected $casts = [
        'seats' => 'array',
    ];
}
