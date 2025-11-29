<?php

use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installation Routes
|--------------------------------------------------------------------------
|
| These routes handle the web-based installation wizard.
| They are protected by the CheckNotInstalled middleware.
|
*/

Route::middleware('check.not.installed')->prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::get('/step1', [InstallController::class, 'step1'])->name('step1');
    Route::get('/step2', [InstallController::class, 'step2'])->name('step2');
    Route::post('/step2', [InstallController::class, 'step2Post'])->name('step2.post');
    Route::get('/step3', [InstallController::class, 'step3'])->name('step3');
    Route::post('/step3', [InstallController::class, 'step3Post'])->name('step3.post');
    Route::get('/step4', [InstallController::class, 'step4'])->name('step4');
    Route::post('/step4/process', [InstallController::class, 'step4Process'])->name('step4.process');
    Route::get('/step5', [InstallController::class, 'step5'])->name('step5');
});
