<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ChatController::class, 'index'])->name('chat.index');
Route::get('/c/{conversation}', [ChatController::class, 'show'])->name('chat.show');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
Route::post('/chat/new', [ChatController::class, 'newChat'])->name('chat.new');
Route::delete('/c/{conversation}', [ChatController::class, 'destroy'])->name('chat.destroy');
Route::post('/c/{conversation}/clear', [ChatController::class, 'clear'])->name('chat.clear');
