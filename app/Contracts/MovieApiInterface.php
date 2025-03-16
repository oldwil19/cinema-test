<?php

namespace App\Contracts;

interface MovieApiInterface
{
    public function getMovieDetails(string $title): array;
}
