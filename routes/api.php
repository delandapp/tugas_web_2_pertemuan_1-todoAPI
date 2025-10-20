<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;

Route::prefix('v1')->group(function () {
    Route::apiResource('todos', TodoController::class)->parameters([
        'todos' => 'todo',
    ]);
});
