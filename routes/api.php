<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TraitementController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\RendezvousController;
use App\Models\Consultation;
use App\Models\Rendezvous;

// guest
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('login/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('login/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::get('password/reset/{token}', function ($token) {
    return response()->json(['message' => 'Password reset token received', 'token' => $token]);
})->name('password.reset');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::post('/email/resend', [AuthController::class, 'resend']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'profile']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/email/resend', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification email sent'], 200);
    });

    Route::middleware('auth:sanctum')->prefix('consultations')->group(function () {
        Route::post('/', [ConsultationController::class, 'store']);
        Route::get('/', [ConsultationController::class, 'index']);
        Route::get('/patients/{id}/consultations', [ConsultationController::class, 'getByPatient']);
        Route::get('/{id}',[ConsultationController::class,'finishSceance']);
        Route::get('/addseance/{id}',[ConsultationController::class,'addSceance']);
    });

    Route::middleware('auth:sanctum')->prefix('payments')->group(function(){
     Route::post('/',[PaiementController::class,'paiement']);
     Route::get('/{id}',[PaiementController::class,'listpayementConsultation']);
     Route::get('/restant/{id}',[PaiementController::class,'payementrestant']);
    });

    Route::get('/services', [ServiceController::class, 'index']);
    Route::post('/services', [ServiceController::class, 'store']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);
    Route::put('/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

});

Route::middleware('auth:sanctum')->prefix('rendezvous')->group(function(){
    Route::post('/',[RendezvousController::class,'store']);
    Route::get('/',[RendezvousController::class,'getAllRendezvous']);
    Route::post('/bymonth',[RendezvousController::class,'getByMonth']);
    Route::get('/{id}',[RendezvousController::class,'getRendezvousById']);
   });

Route::middleware('auth:sanctum')->prefix('doctors')->group(function () {
    Route::get('/',     [DoctorController::class, 'index']);
    Route::get('/{id}', [DoctorController::class, 'show']);
    Route::put('/{id}', [DoctorController::class, 'update']);
     Route::delete('/{id}', [DoctorController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->prefix('stocks')->group(function () {
    Route::get('/',     [StockController::class, 'index']);
    Route::post('/',     [StockController::class, 'store']);
    Route::get('/{id}', [StockController::class, 'show']);
    Route::put('/{id}', [StockController::class, 'update']);
    Route::delete('/{id}', [StockController::class, 'destroy']);
});


Route::middleware('auth:sanctum')->prefix('patients')->group(function () {
    Route::get('/search', [PatientController::class, 'search']);
    Route::get('/',     [PatientController::class, 'index']);
    Route::get('/{id}', [PatientController::class, 'show']);
    Route::put('/{id}', [PatientController::class, 'update']);
    Route::delete('/{id}', [PatientController::class, 'destroy']);

});

Route::middleware(('auth:sanctum'))->group(function () {
    Route::get('/traitements', [TraitementController::class, 'index']);
    Route::post('/traitements', [TraitementController::class, 'store']);
    Route::get('/traitements/{id}', [TraitementController::class, 'show']);
    Route::put('/traitements/{id}', [TraitementController::class, 'update']);
    Route::delete('/traitements/{id}', [TraitementController::class, 'destroy']);
});
