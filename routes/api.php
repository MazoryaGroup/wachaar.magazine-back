<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\homeherocontroller;
use App\Http\Controllers\Api\MagazineController;
use App\Http\Controllers\Api\WaitingListController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ArtistController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ArtistPortfolioController;




Route::prefix('home')->group(function () {
    Route::get('/hero', [HomeHeroController::class, 'show']);
    Route::post('/hero', [HomeHeroController::class, 'store']);
});




Route::prefix('v1')->group(function () {

    Route::get('/magazines', [MagazineController::class, 'index']);
    Route::get('/magazines/{id}', [MagazineController::class, 'show']);

    Route::post('/magazines', [MagazineController::class, 'store']);
    Route::put('/magazines/{id}', [MagazineController::class, 'update']);
    Route::delete('/magazines/{id}', [MagazineController::class, 'destroy']);

});

Route::prefix('waiting-list')->group(function () {
    Route::post('/', [WaitingListController::class, 'store']);
});

Route::post('/messages', [ContactController::class, 'store']);




Route::prefix('v1')->group(function () {

    Route::get('/projects', [ProjectController::class, 'index']);

    Route::get('/projects/{id}', [ProjectController::class, 'show']);

});



Route::prefix('v1')->group(function () {

    Route::get('/services', [ServiceController::class, 'index']);

    Route::get('/services/{id}', [ServiceController::class, 'show']);



});



//Route::prefix('v1')->group(function () {
//
//    Route::get('/artists', [ArtistController::class, 'index']);
//
//    Route::get('/artists/{id}', [ArtistController::class, 'show']);
//
//    Route::post('/artists', [ArtistController::class, 'store']);
//
//    // POST instead of PUT because of multipart file uploads
//    Route::post('/artists/{id}', [ArtistController::class, 'update']);
//
//    Route::delete('/artists/{id}', [ArtistController::class, 'destroy']);
//});




Route::prefix('v1/auth')->group(function () {

    // Public
    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

    // Protected
    Route::middleware('auth:api')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::post('/refresh', [AuthController::class, 'refresh']);

    });
});


/*
|--------------------------------------------------------------------------
| Artist
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')->prefix('v1/artist')->group(function () {

    Route::get('/profile', [
        ArtistController::class,
        'profile',
    ]);

    Route::put('/profile', [
        ArtistController::class,
        'updateProfile',
    ]);
    /*
      |--------------------------------------------------------------------------
      | Portfolio
      |--------------------------------------------------------------------------
      */

    Route::get('/portfolio', [
        ArtistPortfolioController::class,
        'index',
    ]);

    Route::post('/portfolio', [
        ArtistPortfolioController::class,
        'store',
    ]);

    Route::put('/portfolio/{id}', [
        ArtistPortfolioController::class,
        'update',
    ]);

    Route::delete('/portfolio/{id}', [
        ArtistPortfolioController::class,
        'destroy',
    ]);

});


Route::prefix('v1')->group(function () {

    Route::get('/blogs', [BlogController::class, 'index']);

    Route::get('/blogs/{slug}', [BlogController::class, 'show']);


});
