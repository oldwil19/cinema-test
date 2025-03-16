<?php

namespace App\Services;

use App\Repositories\ShowtimeRepository;
use App\Models\Auditorium;
use App\Services\MovieDataService;
use Exception;
use Illuminate\Support\Facades\Log;

class ShowtimeService
{
    protected ShowtimeRepository $showtimeRepository;
    protected MovieDataService $movieDataService;

    public function __construct(ShowtimeRepository $showtimeRepository, MovieDataService $movieDataService)
    {
        $this->showtimeRepository = $showtimeRepository;
        $this->movieDataService = $movieDataService;
    }

    public function getAllShowtimes()
    {
        return $this->showtimeRepository->getAllShowtimes();
    }

    public function createShowtime(array $data)
    {
        Log::info("ShowtimeService@createShowtime iniciado", ['data' => $data]);

        $movieData = $this->movieDataService->getMovieDetails($data['movie_title']);

        if (isset($movieData['error'])) {
            Log::error("Película no encontrada en OMDb", ['movie_title' => $data['movie_title']]);
            throw new Exception($movieData['error'], $movieData['status'] ?? 500);
        }

        $durationString = $movieData['Runtime'] ?? "120 min"; // Valor por defecto si no existe
        if ($this->showtimeRepository->findShowtime($data['auditorium_id'], $data['start_time'], $durationString)) {
            Log::warning("Showtime duplicado detectado", [
                'auditorium_id' => $data['auditorium_id'],
                'start_time' => $data['start_time']
            ]);
            throw new Exception("Este horario ya está ocupado en este auditorium.", 422);
        }

        return $this->showtimeRepository->createShowtime([
            'movie_id' => $movieData['imdbID'],
            'movie_title' =>  $movieData['Title'],
            'auditorium_id' => $data['auditorium_id'],
            'start_time' => $data['start_time'],
            'available_seats' => Auditorium::findOrFail($data['auditorium_id'])->seats,
            'reserved_seats' => [],
        ]);
    }


    public function getShow(int $id)
    {
        $showtime = $this->showtimeRepository->findById($id);

        if (!$showtime) {
            throw new Exception("Showtime no encontrado", 404);
        }

        return $showtime;
    }
}
