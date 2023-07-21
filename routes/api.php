<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('resetpassword', [AuthController::class, 'resetpass']);
});

Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'users'
], function($router){
    Route::get('getOne/{id}',[UsersController::class, 'getOneUser']);
    Route::get('getAllUsers',[UsersController::class, 'getAllUser']);
    Route::post('updateUser/{id}',[UsersController::class, 'updatedUser']);
    Route::post('dropUser/{id}',[UsersController::class, 'deleteUser']);
});


Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'albums'
], function($router) {
    Route::get('getAll', [AlbumController::class, 'getAll']);
    Route::post('create', [AlbumController::class, 'createAlbum']);
    Route::get('getOne/{id}', [AlbumController::class, 'showAlbum']);
    Route::post('edit', [AlbumController::class, 'updatedAlbum']);
    Route::post('delete', [AlbumController::class, 'delete']);
});
