<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

// end of import

use App\Http\Controllers\LogsController;
use App\Models\Logs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use OwenIt\Auditing\Models\Audit;

// end of import




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

    Route::get('/activity-logs', function () {
        $audits = Audit::latest()->paginate(10);

        return view('logs.logs', [
            'audits' => $audits]);
    });

    // backup

    Route::get('/backups', function () {
        // Path to the backups folder
        $backupFolder = public_path('backup'); // Adjust the path as needed
        $files = File::allFiles($backupFolder);

        return view('backups', compact('files')); // needs backup view
    });

    Route::get('/backup-process', function () {
        // Call the backup artisan command
        Artisan::call('backup');

        // Optional: show the output in the browser
        $output = Artisan::output();

        // Return to a view or just show confirmation
        return redirect('/backups')->with('success', '✅ Backup completed.')->with('output', $output);
    });

    // end...

});
