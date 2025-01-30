<?php

use App\Http\Controllers\LogsController;
use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
    Route::get('/create-logs', [LogsController::class, 'create'])->name('logs.create');
    Route::get('/edit-logs/{logsId}', [LogsController::class, 'edit'])->name('logs.edit');
    Route::get('/show-logs/{logsId}', [LogsController::class, 'show'])->name('logs.show');
    Route::get('/delete-logs/{logsId}', [LogsController::class, 'delete'])->name('logs.delete');
    Route::get('/destroy-logs/{logsId}', [LogsController::class, 'destroy'])->name('logs.destroy');
    Route::post('/store-logs', [LogsController::class, 'store'])->name('logs.store');
    Route::post('/update-logs/{logsId}', [LogsController::class, 'update'])->name('logs.update');

    // Logs Search
    Route::get('/logs-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $logs = Logs::when($search, function ($query) use ($search) {
            return $query->where('log', 'like', "%$search%");
        })->paginate(10);

        return view('logs.logs', compact('logs', 'search'));
    });

});
