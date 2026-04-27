<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServerInfoController;

Route::get('/', [ServerInfoController::class, 'index']);
Route::get('/server', [ServerInfoController::class, 'index'])->name('server.info');
Route::get('/server/processes', [ServerInfoController::class, 'processes'])->name('server.processes');