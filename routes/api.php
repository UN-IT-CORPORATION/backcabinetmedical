<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedecinController;

// Authentification
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [AuthController::class, 'checkUser']);

    // Routes Patients
    Route::prefix('patients')->group(function () {
        Route::post('/', [PatientController::class, 'store']);
        Route::put('/{id}', [PatientController::class, 'update']);
        Route::delete('/{id}', [PatientController::class, 'destroy']);
    });

    // Routes Médecins
    Route::prefix('medecins')->group(function () {
        Route::post('/', [MedecinController::class, 'store']);
        Route::put('/{id}', [MedecinController::class, 'update']);
        Route::delete('/{id}', [MedecinController::class, 'destroy']);
    });
});

