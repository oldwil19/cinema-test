For up the project you need to run in row folder

1. `composer install` run composer out of docker
2. rename `.env.example` to `.env`
3. `./vendor/bin/sail up -d` up docker
4. `./vendor/bin/sail artisan migrate --seed` run migrations and seeds
5. `sail artisan queue:work` up queue
6. `./vendor/bin/sail artisan horizon` or ` ./vendor/bin/sail artisan horizon &`
7. `./vendor/bin/sail test` for run test

**This was the way the features or tickets were prioritized.Definition of tickets**

* OMDb integration (Firts, that beacause need to get a movie for create any showtime
* Log Middleware (just logging)
* Cache Service with redis (to save the data of movies)
* MovieData Service (the service to merge the OMDb integracion and cache services)
* Auditorium (Only way to see if exist or not Auditorium, then run seeds to populate some Auditorium)
* Showtime services (the way to create any showtime with a title of movie)
* Reservation ( the most comples feature, actually ,the core of this app)
* Purchase service (Just simulate a payment for some resevation)
* Jobs(ExpireReservationJob, ProcessReservationJob) asyncronus way to register reservation, also, expire reservation by expired time for any reservation), `php artisan make:job`
* Handle event and listener to emit event to update seats
* For each service and controller I created test, to validate and continus to next task

scaffolding of project

```
tree -I vendor
.
├── README.md
├── app
│   ├── Contracts
│   │   ├── MovieApiInterface.php
│   │   ├── ReservationServiceInterface.php
│   │   └── ShowtimeInterface.php
│   ├── Events
│   │   └── ReservationCreated.php
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── AuditoriumController.php
│   │   │   ├── Controller.php
│   │   │   ├── MovieController.php
│   │   │   ├── PurchaseController.php
│   │   │   ├── ReservationController.php
│   │   │   └── ShowtimeController.php
│   │   └── Middleware
│   │       └── LogExecutionTime.php
│   ├── Jobs
│   │   ├── ExpireReservationJob.php
│   │   └── ProcessReservationJob.php
│   ├── Listeners
│   │   └── UpdateShowtimeAvailability.php
│   ├── Models
│   │   ├── Auditorium.php
│   │   ├── Payment.php
│   │   ├── Reservation.php
│   │   ├── Showtime.php
│   │   └── User.php
│   ├── Providers
│   │   ├── AppServiceProvider.php
│   │   ├── HorizonServiceProvider.php
│   │   └── RouteServiceProvider.php
│   ├── Repositories
│   │   └── ShowtimeRepository.php
│   └── Services
│       ├── CacheService.php
│       ├── MovieDataService.php
│       ├── OmdbService.php
│       ├── PurchaseService.php
│       ├── RateLimitService.php
│       ├── ReservationService.php
│       └── ShowtimeService.php
├── artisan
├── bootstrap
│   ├── app.php
│   ├── cache
│   │   ├── packages.php
│   │   └── services.php
│   └── providers.php
├── composer.json
├── composer.lock
├── config
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── horizon.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
├── database
│   ├── database.sqlite
│   ├── factories
│   │   ├── AuditoriumFactory.php
│   │   ├── ShowtimeFactory.php
│   │   └── UserFactory.php
│   ├── migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2025_03_14_200810_create_auditoriums_table.php
│   │   ├── 2025_03_14_201442_create_showtimes_table.php
│   │   ├── 2025_03_15_032543_create_reservations_table.php
│   │   └── 2025_03_16_072153_create_payments_table.php
│   └── seeders
│       ├── AuditoriumSeeder.php
│       ├── DatabaseSeeder.php
│       └── ShowtimeSeeder.php
├── docker-compose.yml
├── package.json
├── phpunit.xml
├── public
│   ├── favicon.ico
│   ├── index.php
│   └── robots.txt
├── resources
│   ├── css
│   │   └── app.css
│   ├── js
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views
│       └── welcome.blade.php
├── routes
│   ├── api.php
│   ├── console.php
│   └── web.php
├── storage
│   ├── app
│   │   ├── private
│   │   └── public
│   ├── framework
│   │   ├── cache
│   │   │   └── data
│   │   ├── sessions
│   │   ├── testing
│   │   └── views
│   │       ├── 275c7c02e2528e6029079c885e2d2418.php
│   │       ├── 9065e79a479fcdb86f244f50aa1f572b.php
│   │       └── dd310000961f2d208873a737c27d849a.php
│   └── logs
│       └── laravel.log
├── tests
│   ├── Feature
│   │   ├── AuditoriumControllerTest.php
│   │   ├── MovieControllerTest.php
│   │   ├── PurchaseTest.php
│   │   ├── ReservationTest.php
│   │   └── ShowtimeTest.php
│   ├── Pest.php
│   ├── TestCase.php
│   └── Unit
└── vite.config.js
```

* **movies**

find any movies

`http://localhost/api/movies/?title=Guardians of the Galaxy Vol. 2`

response

```
 {
    "Title": "Guardians of the Galaxy Vol. 2",
    "Year": "2017",
    "Rated": "PG-13",
    "Released": "05 May 2017",
    "Runtime": "136 min",
    "Genre": "Action, Adventure, Comedy",
    "Director": "James Gunn",
    "Writer": "James Gunn, Dan Abnett, Andy Lanning",
    "Actors": "Chris Pratt, Zoe Saldaña, Dave Bautista",
    "Plot": "The Guardians struggle to keep together as a team while dealing with their personal family issues, notably Star-Lord's encounter with his father, the ambitious celestial being Ego.",
    "Language": "English",
    "Country": "United States",
    "Awards": "Nominated for 1 Oscar. 15 wins & 61 nominations total",
    "Poster": "https://m.media-amazon.com/images/M/MV5BNWE5MGI3MDctMmU5Ni00YzI2LWEzMTQtZGIyZDA5MzQzNDBhXkEyXkFqcGc@._V1_SX300.jpg",
    "Ratings": [
        {
            "Source": "Internet Movie Database",
            "Value": "7.6/10"
        },
        {
            "Source": "Rotten Tomatoes",
            "Value": "85%"
        },
        {
            "Source": "Metacritic",
            "Value": "67/100"
        }
    ],
    "Metascore": "67",
    "imdbRating": "7.6",
    "imdbVotes": "788,570",
    "imdbID": "tt3896198",
    "Type": "movie",
    "DVD": "N/A",
    "BoxOffice": "$389,813,101",
    "Production": "N/A",
    "Website": "N/A",
    "Response": "True",
    "source": "api"
}
```

**showtime**

*get all showtimes*
`curl --location 'http://localhost/api/showtimes'`

```
[
 
    {
        "id": 4,
        "movie_id": "tt0816692",
        "movie_title": "Interstellar",
        "auditorium_id": 1,
        "start_time": "2025-03-22 11:30:30",
        "available_seats": [
            "A1",
            "A2",
            "A3",
            "A4",
            "A5",
            "A6",
            "A7",
            "A8",
            "A9",
            "A10",
            "A13",
            "A14",
            "A15",
            "B1",
            "B2",
            "B3",
            "B4",
            "B5",
            "B6",
            "B7",
          ..
        ],
        "reserved_seats": [
            "A11",
            "A12"
        ]
    },..
]
```

**see showtime by id**

`http://localhost/api/showtimes/11`

**create a showtime**

```
curl --location 'http://localhost/api/showtimes' \
--header 'Content-Type: application/json' \
--data '{
    "movie_title": "Interstellar",
    "auditorium_id": 1,
    "start_time": "2025-03-22 11:30:30"
}'
```

example of response

```

{
    "movie_id": "tt0816692",
    "movie_title": "Interstellar",
    "auditorium_id": 1,
    "start_time": "2025-03-22 11:30:30",
    "available_seats": "[\"A1\",\"A2\",\"A3\",\"A4\",\"A5\",\"A6\",\"A7\",\"A8\",\"A9\",\"A10\",\"A11\",\"A12\",\"A13\",\"A14\",\"A15\",\"B1\",\"B2\",\"B3\",\"B4\",\"B5\",\"B6\",\"B7\",\"B8\",\"B9\",\"B10\",\"B11\",\"B12\",\"B13\",\"B14\",\"B15\",\"C1\",\"C2\",\"C3\",\"C4\",\"C5\",\"C6\",\"C7\",\"C8\",\"C9\",\"C10\",\"C11\",\"C12\",\"C13\",\"C14\",\"C15\",\"D1\",\"D2\",\"D3\",\"D4\",\"D5\",\"D6\",\"D7\",\"D8\",\"D9\",\"D10\",\"D11\",\"D12\",\"D13\",\"D14\",\"D15\",\"E1\",\"E2\",\"E3\",\"E4\",\"E5\",\"E6\",\"E7\",\"E8\",\"E9\",\"E10\",\"E11\",\"E12\",\"E13\",\"E14\",\"E15\",\"F1\",\"F2\",\"F3\",\"F4\",\"F5\",\"F6\",\"F7\",\"F8\",\"F9\",\"F10\",\"F11\",\"F12\",\"F13\",\"F14\",\"F15\",\"G1\",\"G2\",\"G3\",\"G4\",\"G5\",\"G6\",\"G7\",\"G8\",\"G9\",\"G10\",\"G11\",\"G12\",\"G13\",\"G14\",\"G15\",\"H1\",\"H2\",\"H3\",\"H4\",\"H5\",\"H6\",\"H7\",\"H8\",\"H9\",\"H10\",\"H11\",\"H12\",\"H13\",\"H14\",\"H15\"]",
    "reserved_seats": "[]",
    "updated_at": "2025-03-17T20:14:53.000000Z",
    "created_at": "2025-03-17T20:14:53.000000Z",
    "id": 4
}
```

**get all reservations**
`http://localhost/api/reservations`

```
[
    {
        "id": "64ec00e4-6220-4050-a3a1-d8223bc44147",
        "showtime_id": 4,
        "seats": "[\"A11\",\"A12\"]",
        "status": "pending", //"expired" 
        "expires_at": "2025-03-17T20:26:42.000000Z",
        "created_at": "2025-03-17T20:16:42.000000Z",
        "updated_at": "2025-03-17T20:16:42.000000Z"
    }
]
```

**create a reservation**

```

curl --location 'http://localhost/api/reservations' \
--header 'Content-Type: application/json' \
--data '{
    "showtime_id":4,
    "seats": 
       [ "A11","A12"]
  
}'
```

example of response

```
{
    "reservation_id": "64ec00e4-6220-4050-a3a1-d8223bc44147",
    "message": "Reservation is being processed"
}
```

see reservation
http://localhost/api/reservations/a5367360-4413-43f0-adb1-4e506af04faa(uuid)

example of response

```
{
    "id": "64ec00e4-6220-4050-a3a1-d8223bc44147",
    "showtime_id": 4,
    "seats": "[\"A11\",\"A12\"]",
    "status": "pending",
    "expires_at": "2025-03-17T20:26:42.000000Z",
    "created_at": "2025-03-17T20:16:42.000000Z",
    "updated_at": "2025-03-17T20:16:42.000000Z"
}
```

Purchase Reservation

```
curl --location 'http://localhost/api/purchase' \
--header 'Content-Type: application/json' \
--data '{
    "reservation_id": "b2e05ee3-5d5d-499c-8d7c-b45d5780d459"
}'
```

Response example

```
{
    "message": "Reservation successfully confirmed."
}
```

get all Payments

`http://localhost/api/payments`

```
[
    {
        "id": "efb3b46b-3760-4da9-b770-c4245856a91c",
        "reservation_id": "b2e05ee3-5d5d-499c-8d7c-b45d5780d459",
        "status": "completed",
        "created_at": "2025-03-17T20:47:50.000000Z",
        "updated_at": "2025-03-17T20:47:50.000000Z",
        "reservation": {
            "id": "b2e05ee3-5d5d-499c-8d7c-b45d5780d459",
            "showtime_id": 4,
            "seats": "[\"A1\",\"A2\"]",
            "status": "confirmed",
            "expires_at": "2025-03-17T20:57:41.000000Z",
            "created_at": "2025-03-17T20:47:41.000000Z",
            "updated_at": "2025-03-17T20:47:50.000000Z"
        }
    }
]
```

auditorium

get all auditoriums

`http://localhost/api/auditoriums`

```
[
    {
        "id": 1,
        "name": "Auditorium 1",
        "seats": [
            "A1",
            "A2",
            "A3",
            "A4",
            "A5",
            "A6",
            "A7",
            "A8",
            "A9",
            "A10",
            "A11",
            "A12",
            "A13",
            "A14",
            "A15",
            "B1"

        ],
        "status": "active",
        "opening_time": "10:00:00",
        "closing_time": "01:00:00",
        "created_at": "2025-03-17T20:07:38.000000Z",
        "updated_at": "2025-03-17T20:07:38.000000Z"
    },..
]
```

see auditorium

`http://localhost/api/auditoriums/1`

```
{
    "id": 1,
    "name": "Auditorium 1",
    "seats": [
        "A1",
        "A2",
        "A3",
        "A4",
        "A5",
        "A6",
        "A7",
        "A8",
        "A9",
        "A10",
        "A11",
        "A12",
        "A13",
        "A14",
        "A15",
        "B1",
        "B2",
        "B3",
        "B4",
        "B5",
        "B6",
        "B7",
        "B8",
        "B9",
        "B10",
        "B11",
        "B12",
        "B13",
        "B14",
        "B15",
        "C1",
        "C2",
        "C3",
        "C4",
        "C5",
        "C6",
        "C7",
        "C8",
        "C9",
        "C10",
        "C11",
        "C12",
        "C13",
        "C14",
        "C15",
        "D1",
        "D2",
        "D3",
        "D4",
        "D5",
        "D6",
        "D7",
        "D8",
        "D9",
        "D10",
        "D11",
        "D12",
        "D13",
        "D14",
        "D15",
        "E1",
        "E2",
        "E3",
        "E4",
        "E5",
        "E6",
        "E7",
        "E8",
        "E9",
        "E10",
        "E11",
        "E12",
        "E13",
        "E14",
 
    ],
    "status": "active",
    "opening_time": "10:00:00",
    "closing_time": "01:00:00",
    "created_at": "2025-03-17T20:07:38.000000Z",
    "updated_at": "2025-03-17T20:07:38.000000Z"
}
```
