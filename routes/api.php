<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinalistasController;
use App\Http\Controllers\FotoController;
use App\Http\Controllers\ReaccionController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

// AUTENTICACION
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

// GESTIÓN DE USUARIOS
Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'users'
], function($router){
    Route::get('getOne/{id}',[UsersController::class, 'getOneUser']);
    Route::get('getAllUsers',[UsersController::class, 'getAllUser']);
    Route::post('updateUser/{id}',[UsersController::class, 'updatedUser']);
    Route::post('dropUser/{id}',[UsersController::class, 'deleteUser']);
});

// GESTIÓN DE ALBUNES
Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'albums'
], function($router) {
    Route::get('getAllAlbumsActives', [AlbumController::class, 'getAlbumsActives']);
    Route::get('getAllAlbumsInactives', [AlbumController::class, 'getAlbumsIanctives']);
    Route::post('procFile', [AlbumController::class, 'uploadFile']);
    Route::post('create', [AlbumController::class, 'createAlbum']);
    Route::get('getOne/{id}', [AlbumController::class, 'getOneAlbum']);
    Route::post('updateAlbum/{id}', [AlbumController::class, 'updatedAlbum']);
    Route::post('deleteAlbum/{id}', [AlbumController::class, 'deleteAlbum']);
    Route::get('publicate/{id}', [AlbumController::class, 'publicateAlbum']);
    Route::get('depublicate/{id}', [AlbumController::class, 'DePublicateAlbum']);
});

// GESTION DE FOTOS
Route::group([
    'middleware' => ['jwt.verify'],
    'prefix' => 'fotos'
], function($router){
    Route::get('getAllFotos/{id}', [FotoController::class, 'getFotosAll']);
    Route::post('create', [FotoController::class, 'createFoto']);
    Route::post('procFile', [FotoController::class, 'uploadFile']);
    Route::get('publicate/{id}', [FotoController::class, 'publicateFoto']);
    Route::get('depublicate/{id}', [FotoController::class, 'dePublicateFoto']);
    Route::get('getOne/{id}', [FotoController::class, 'getOnePhoto']);
    Route::post('deleteFoto/{id}', [FotoController::class, 'deleteFoto']);
    Route::post('updateFoto/{id}', [FotoController::class, 'updatedPhoto']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'general'
], function ($router) {
    Route::get('albumPublico',[AlbumController::class,'getAlbumsActives']);
    Route::get('fotosAlbumPublic/{id}',[FotoController::class,'getFotosAllStatePublic']);
    Route::get('reaccion/{idFoto}/{idReact}/{tokenVotos}',[ReaccionController::class,'reaccion']);
    Route::get('reacciones',[ReaccionController::class,'reaccionesTodas']);
    Route::get('getOnePhotoPublic/{id}', [FotoController::class, 'getOnePhotoPublic']);
    Route::get('getReaccions/{id}', [ReaccionController::class, 'conteoReacciones']);
    Route::get('getFinalists', [FinalistasController::class, 'finalists']);
    Route::get('getIpClient', [ReaccionController::class, 'getIpClient']);
});
