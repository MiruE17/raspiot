<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TokenController as AdminTokenController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminHomeController;
use App\Livewire\Admin\TokenManager as AdminTokenManager;
use App\Livewire\User\SchemeDashboard;
use App\Livewire\User\SchemeManager;
use App\Livewire\User\TokenManager as UserTokenManager;
use App\Http\Controllers\SchemeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/home', [HomeController::class, 'index'])
        ->middleware(['verified'])
        ->name('home');

    // User routes - fixed the syntax error
    Route::get('/tokens', function () {
        return view('access.user.user-token-index');
    })->name('tokens');

    // New scheme management routes
    Route::get('/schemes', function () {
        return view('access.user.user-schemes-index');
    })->name('user.schemes');
    
    Route::get('scheme/{schemeId}', function ($schemeId) {
        return view('access.user.user-schemes-index', [
            'schemeId' => $schemeId
        ]);
    })->name('scheme.show')->middleware('auth');

    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Admin dashboard
        Route::get('/home', [AdminHomeController::class, 'index'])->name('home');
        
        // Changed to use Livewire component directly
        Route::get('/tokens', function () {
            return view('access.admin.admin-token-index');
        })->name('tokens.index');

        // Livewire-based admin resources - using only these and removing duplicates
        Route::get('/users', function () {
            return view('access.admin.admin-user-index');
        })->name('users.index');
        
        Route::get('/sensors', function () {
            return view('access.admin.admin-sensor-index');
        })->name('sensors.index');

        // Admin scheme management
       Route::get('/schemes', function () {
        return view('access.user.user-schemes-index');
    })->name('schemes.index');
    });

    // Export routes
    Route::get('/export/excel', [App\Http\Controllers\ExportController::class, 'exportExcel'])
        ->name('export.excel')
        ->middleware(['auth']);
});

require __DIR__.'/auth.php';
