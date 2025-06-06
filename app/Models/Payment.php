<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = ['id', 'reservation_id', 'status'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
