<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and assigned the "api"
| middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::get('/tasks/export', [TaskController::class, 'export']);
    Route::post('/tasks/import', [TaskController::class, 'import']);
    Route::get('/tasks/deleted/list', [TaskController::class, 'deletedTasks']);
    Route::post('/tasks/{id}/restore', [TaskController::class, 'restore']);     
    Route::delete('/tasks/{id}/force', [TaskController::class, 'forceDelete']); 


Route::post('/logout', [AuthController::class, 'logout']);
});
