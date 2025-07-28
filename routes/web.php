<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SmarkController;

// end of import

use App\Http\Controllers\UsersController;
use App\Models\Users;

// end of import

use App\Http\Controllers\TodosController;
use App\Models\Todos;

// end of import

use App\Http\Controllers\NotesController;
use App\Models\Notes;

// end of import


Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/user', function () {
        return response()->json(Auth::user());
    });

    // CHAT ROUTES

    Route::get('/initialize-chat/{usersId}/{recieversId}', [SmarkController::class, 'initializeChat']);
    Route::post('/send-chat', [SmarkController::class, 'sendChat']);
    Route::get('/fetch-messages/{chatsId}', [SmarkController::class, 'fetchMessages']);
    Route::get('/chat-history', [SmarkController::class, 'chatHistory']);
    Route::post('/upload-files', [SmarkController::class, 'uploadFiles']);
    Route::post('/delete-chat', [SmarkController::class, 'deleteChat']);
    Route::post('/unsent-message', [SmarkController::class, 'unsentMessage']);
    Route::post('/chat-files', [SmarkController::class, 'chatFiles']);

    // END CHAT ROUTES

    Route::get('/activity-logs', [SmarkController::class, 'activityLogs']);

    // backup

    Route::get('/backups', [SmarkController::class, 'backups']);
    Route::get('/backup-process', [SmarkController::class, 'backupProcess']);

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // MODEL VIEWER

    Route::get('/_database/{model?}', [SmarkController::class, 'modelViewer'])->name('models.index');

    // POSTMAN

    Route::get('/_postman', [SmarkController::class, 'postman']);

    // end...

    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/create-users', [UsersController::class, 'create'])->name('users.create');
    Route::get('/edit-users/{usersId}', [UsersController::class, 'edit'])->name('users.edit');
    Route::get('/show-users/{usersId}', [UsersController::class, 'show'])->name('users.show');
    Route::get('/delete-users/{usersId}', [UsersController::class, 'delete'])->name('users.delete');
    Route::get('/destroy-users/{usersId}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::post('/store-users', [UsersController::class, 'store'])->name('users.store');
    Route::post('/update-users/{usersId}', [UsersController::class, 'update'])->name('users.update');
    Route::post('/users-delete-all-bulk-data', [UsersController::class, 'bulkDelete']);
    Route::post('/users-move-to-trash-all-bulk-data', [UsersController::class, 'bulkMoveToTrash']);
    Route::post('/users-restore-all-bulk-data', [UsersController::class, 'bulkRestore']);
    Route::get('/trash-users', [UsersController::class, 'trash']);
    Route::get('/restore-users/{usersId}', [UsersController::class, 'restore'])->name('users.restore');

    // Users Search
    Route::get('/users-search', [SmarkController::class, 'usersSearch']);

    // Users Paginate
    Route::get('/users-paginate', [SmarkController::class. 'usersPaginate']);

    // Users Filter
    Route::get('/users-filter', [SmarkController::class, 'userFilter']);

    // end...

});
