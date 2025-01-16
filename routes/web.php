<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CardNumController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('cardnum')->controller(CardNumController::class)->group(function () {
    Route::get('import', fn() => view('import'))->name('import');
    Route::post('import', 'import');
});
