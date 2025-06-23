<?php

use App\Http\Controllers\API\DataIotApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route untuk menerima data IoT (tidak menggunakan auth:sanctum)
Route::post('/data', [DataIotApiController::class, 'store']);
Route::get('/data', [DataIotApiController::class, 'index']);

