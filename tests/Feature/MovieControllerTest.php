<?php

use App\Services\MovieDataService;

use function Pest\Laravel\getJson;

beforeEach(function () {
    $this->mock(MovieDataService::class, function ($mock) {
        $mock->shouldReceive('getMovieDetails')->andReturn([
            'Title' => 'Interstellar',
            'Year' => '2014',
            'Director' => 'Christopher Nolan',
            'source' => 'api',
        ]);
    });
});

it('can fetsh movie details', function () {
    getJson('/api/movies?title=Interstellar')
        ->assertOk()
        ->assertJsonPath('Title', 'Interstellar')
        ->assertJsonPath('source', 'api');
});

it('returnss error if title is mising', function () {
    getJson('/api/movies')
        ->assertStatus(400)
        ->assertJson(['error' => 'Movie title is required.']);
});
