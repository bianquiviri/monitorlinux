<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServerInfoController;
use App\Http\Controllers\LoginController;

// Rutas Públicas
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('server.list');
    }
    return view('welcome');
})->name('home');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rutas Protegidas
Route::middleware('monitor.auth')->group(function () {
    Route::get('/server', [ServerInfoController::class, 'listServers'])->name('server.list');
    Route::get('/server/monitor/{id}', [ServerInfoController::class, 'index'])->name('server.info');
    Route::get('/server/processes', [ServerInfoController::class, 'processes'])->name('server.processes');
    Route::post('/server/add', [ServerInfoController::class, 'addServer'])->name('server.add');
    Route::put('/server/{id}', [ServerInfoController::class, 'updateServer'])->name('server.update');
    Route::delete('/server/{id}', [ServerInfoController::class, 'deleteServer'])->name('server.delete');
    Route::post('/server/test-connection', [ServerInfoController::class, 'testConnection'])->name('server.test-connection');
    Route::post('/server/container/manage', [ServerInfoController::class, 'manageContainer'])->name('server.container.manage');
});
