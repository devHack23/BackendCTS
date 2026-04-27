<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\PositionController;
use App\Http\Controllers\Api\CandidatController;
use App\Http\Controllers\Api\VoteController;


/********** Routes d'authentification **********/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);

/********** Routes protégées par JWT **********/
Route::middleware('auth:api')->group(function () {
    // Positions
    Route::get('/positions', [PositionController::class, 'index']);
    Route::post('/positions', [PositionController::class, 'store'])->middleware('admin');
    Route::put('/positions/{id}', [PositionController::class, 'update'])->middleware('admin');
    Route::delete('/positions/{id}', [PositionController::class, 'destroy'])->middleware('admin');
    Route::get('/positions/{id}', [PositionController::class, 'show']);
    
    // Candidats
    Route::get('/candidates', [CandidatController::class, 'index']);
    Route::post('/candidates', [CandidatController::class, 'store'])->middleware('admin');
    Route::put('/candidates/{id}', [CandidatController::class, 'update'])->middleware('admin');
    Route::delete('/candidates/{id}', [CandidatController::class, 'destroy'])->middleware('admin');
    Route::get('/candidates/{id}', [CandidatController::class, 'show']);

    // Votes
    Route::post('/votes', [VoteController::class, 'store']);
    Route::get('/votes/results', [VoteController::class, 'results']);
});
