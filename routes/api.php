<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlayersController;
use App\Http\Controllers\PredictionController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register-user', [AuthController::class, 'register']);
Route::get('test', [AuthController::class, 'test']);


Route::middleware(['cors'])->group(function () {
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);

    Route::get('get-all-users', [AdminController::class, 'getAllUsers'])->middleware("checkRole:admin");
    Route::get('get-groups', [AdminController::class, 'getAllGroups'])->middleware("checkRole:admin");
    Route::put('update-data-users', [AdminController::class, 'updateUserData'])->middleware("checkRole:admin");

    Route::post('user-details', [AuthController::class, 'getUserDetails']);
    Route::put('update-password', [AuthController::class, 'updatePassword']);

    Route::post('get-my-players', [PlayersController::class, 'getMyPlayers']);
    Route::post('average-players', [PlayersController::class, 'getAverage']);
    Route::post('get-points-players', [PlayersController::class, 'getPointsPlayer']);
    Route::post('get-all-points-players', [PlayersController::class, 'getTotalPointsPlayer']);
    Route::post('get-all-players-not-mine', [PlayersController::class, 'getAllPlayersNotMine']);

    Route::put('buy-player', [PlayersController::class, 'buyPlayer']);

    Route::post('get-team-and-group', [PlayersController::class, 'getUserTeamAndGroup']);
    Route::delete('delete-player', [PlayersController::class, 'deletePlayer']);

    Route::get('/predictions', [PredictionController::class, 'makePredictions']);

    Route::post('logout', [AuthController::class, 'logout']);


});
