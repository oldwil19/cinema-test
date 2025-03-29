<?php

namespace App\Services;

use App\Models\Auditorium;
use App\Repositories\ShowtimeRepository;
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

    /**
     * Get all showtimes.
     *
     * @return mixed
     */
    public function getAllShowtimes()
    {
        return $this->showtimeRepository->getAllShowtimes()->map(function ($showtime) {
            return [
                'id' => $showtime->id,
                'movie_id' => $showtime->movie_id,
                'movie_title' => $showtime->movie_title,
                'auditorium_id' => $showtime->auditorium_id,
                'start_time' => $showtime->start_time,
                'available_seats' => $this->decodeSeats($showtime->available_seats),
                'reserved_seats' => $this->decodeSeats($showtime->reserved_seats),
            ];
        });
    }

    /**
     * Create a new showtime.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function createShowtime(array $data)
    {
        Log::info('Creating showtime', ['data' => $data]);

        $movieData = $this->movieDataService->getMovieDetails($data['movie_title']);

        if (isset($movieData['error'])) {
            Log::error('Movie not found in external API', ['movie_title' => $data['movie_title']]);
            throw new Exception($movieData['error'], $movieData['status'] ?? 500);
        }

        $durationString = $movieData['Runtime'] ?? '120 min'; // Default value if runtime is missing
        if ($this->showtimeRepository->findShowtime($data['auditorium_id'], $data['start_time'], $durationString)) {
            Log::warning('Duplicated showtime detected', [
                'auditorium_id' => $data['auditorium_id'],
                'start_time' => $data['start_time'],
            ]);
            throw new Exception('This schedule is already occupied in this auditorium.', 422);
        }

        return $this->showtimeRepository->createShowtime([
            'movie_id' => $movieData['imdbID'],
            'movie_title' => $movieData['Title'],
            'auditorium_id' => $data['auditorium_id'],
            'start_time' => $data['start_time'],
            'available_seats' => json_encode($this->getAuditoriumSeats($data['auditorium_id'])),
            'reserved_seats' => json_encode([]),
        ]);
    }

    /**
     * Get showtime by ID.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function getShow(int $id)
    {
        $showtime = $this->showtimeRepository->findById($id);

        if (! $showtime) {
            throw new Exception('Showtime not found', 404);
        }

        return [
            'id' => $showtime->id,
            'movie_id' => $showtime->movie_id,
            'movie_title' => $showtime->movie_title,
            'auditorium_id' => $showtime->auditorium_id,
            'start_time' => $showtime->start_time,
            'available_seats' => $this->decodeSeats($showtime->available_seats),
            'reserved_seats' => $this->decodeSeats($showtime->reserved_seats),
        ];
    }

    /**
     * Get all seats available in an auditorium.
     *
     * @throws Exception
     */
    private function getAuditoriumSeats(int $auditoriumId): array
    {
        $auditorium = Auditorium::find($auditoriumId);

        if (! $auditorium) {
            throw new Exception('Auditorium not found', 404);
        }

        return $auditorium->seats ?? [];
    }

    /**
     * Decode seats only if needed.
     *
     * @param  mixed  $seats
     */
    private function decodeSeats($seats): array
    {
        return is_string($seats) ? json_decode($seats, true) : (is_array($seats) ? $seats : []);
    }
}
