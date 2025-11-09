<?php

use App\Livewire\UploadManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/upload', UploadManager::class);
