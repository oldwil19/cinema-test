<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShowtimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Puedes cambiarlo si necesitas permisos
    }

    public function rules(): array
    {
        return [
            'movie_title' => 'required|string',
            'auditorium_id' => 'required|exists:auditoriums,id',
            'start_time' => 'required|date',
        ];
    }
}
