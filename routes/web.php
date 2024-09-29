<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/response', [MainController::class, 'init']);
Route::get('/call', [MainController::class, 'makeCall']);
Route::get('/save-voicemail', [MainController::class, 'saveVoiceMail']);

Route::get('logs', [LogViewerController::class, 'index']);
