<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeHeroController;
use App\Http\Controllers\Api\MagazineController;
use App\Http\Controllers\Api\WaitingListController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ArtistPortfolioController;
use App\Http\Controllers\Api\ArtistProjectController;
use App\Http\Controllers\Api\PublicArtistController;


/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::prefix('home')->group(function () {

    Route::get('/hero', [HomeHeroController::class, 'show']);

    Route::post('/hero', [HomeHeroController::class, 'store']);

});


/*
|--------------------------------------------------------------------------
| Magazines
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    Route::get('/magazines', [MagazineController::class, 'index']);

    Route::get('/magazines/{id}', [MagazineController::class, 'show']);

    Route::post('/magazines', [MagazineController::class, 'store']);

    Route::put('/magazines/{id}', [MagazineController::class, 'update']);

    Route::delete('/magazines/{id}', [MagazineController::class, 'destroy']);

});


/*
|--------------------------------------------------------------------------
| Waiting List
|--------------------------------------------------------------------------
*/

Route::prefix('waiting-list')->group(function () {

    Route::post('/', [WaitingListController::class, 'store']);

});


/*
|--------------------------------------------------------------------------
| Contact / Messages
|--------------------------------------------------------------------------
*/

Route::post('/messages', [ContactController::class, 'store']);


/*
|--------------------------------------------------------------------------
| Public Projects
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    Route::get('/projects', [ProjectController::class, 'index']);

    Route::get('/projects/{id}', [ProjectController::class, 'show']);

});


/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    Route::get('/services', [ServiceController::class, 'index']);

    Route::get('/services/{id}', [ServiceController::class, 'show']);

});


/*
|--------------------------------------------------------------------------
| Artists - Admin / Management
|--------------------------------------------------------------------------
|
| این مسیرها مربوط به مدیریت Artist هستند.
| لیست و پروفایل عمومی Artist در Public Artists پایین‌تر قرار دارد.
|
*/

Route::prefix('v1')->group(function () {

    Route::post('/artists', [ArtistController::class, 'store']);

    // POST instead of PUT because of multipart file uploads
    Route::post('/artists/{id}', [ArtistController::class, 'update']);

    Route::delete('/artists/{id}', [ArtistController::class, 'destroy']);

});


/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('v1/auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Auth
    |--------------------------------------------------------------------------
    */

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    /*
    |--------------------------------------------------------------------------
    | Protected Auth
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:api')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::post('/refresh', [AuthController::class, 'refresh']);

    });

});


/*
|--------------------------------------------------------------------------
| Artist - Authenticated
|--------------------------------------------------------------------------
|
| این بخش فقط برای Artist لاگین‌شده است.
| تمام این endpoint ها به JWT Token نیاز دارند.
|
*/

Route::middleware('auth:api')
    ->prefix('v1/artist')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [ArtistController::class, 'profile']);

        Route::put('/profile', [ArtistController::class, 'updateProfile']);


        /*
        |--------------------------------------------------------------------------
        | Portfolio
        |--------------------------------------------------------------------------
        */

        Route::get('/portfolio', [ArtistPortfolioController::class, 'index']);

        Route::post('/portfolio', [ArtistPortfolioController::class, 'store']);

        Route::put('/portfolio/{id}', [ArtistPortfolioController::class, 'update']);

        Route::delete('/portfolio/{id}', [ArtistPortfolioController::class, 'destroy']);


        /*
        |--------------------------------------------------------------------------
        | Projects
        |--------------------------------------------------------------------------
        */

        Route::get('/projects', [ArtistProjectController::class, 'index']);

        Route::post('/projects', [ArtistProjectController::class, 'store']);

        Route::get('/projects/{id}', [ArtistProjectController::class, 'show']);

        Route::put('/projects/{id}', [ArtistProjectController::class, 'update']);

        Route::delete('/projects/{id}', [ArtistProjectController::class, 'destroy']);

        Route::post(
            '/projects/{id}/submit',
            [ArtistProjectController::class, 'submit']
        );

    });


/*
|--------------------------------------------------------------------------
| Public Blogs
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    Route::get('/blogs', [BlogController::class, 'index']);

    Route::get('/blogs/{slug}', [BlogController::class, 'show']);

});


/*
|--------------------------------------------------------------------------
| Public Artists
|--------------------------------------------------------------------------
|
| این بخش عمومی است و JWT Token نمی‌خواهد.
|
| Visitor:
|
| GET /api/v1/artists
| GET /api/v1/artists/{id}
| GET /api/v1/artists/{id}/projects
|
|--------------------------------------------------------------------------
*/

Route::prefix('v1/artists')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | List Approved Artists
    |--------------------------------------------------------------------------
    */

    Route::get('/', [PublicArtistController::class, 'index']);


    /*
    |--------------------------------------------------------------------------
    | Public Artist Projects
    |--------------------------------------------------------------------------
    |
    | باید قبل از /{id} باشد.
    |
    */




    /*
    |--------------------------------------------------------------------------
    | Public Artist Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/{id}',
        [PublicArtistController::class, 'show']
    );

});
