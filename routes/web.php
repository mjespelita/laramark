<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// end of import

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use OwenIt\Auditing\Models\Audit;

// end of import

use App\Http\Controllers\ChatsController;
use App\Models\Chats;

// end of import

use App\Http\Controllers\ChatparticipantsController;
use App\Models\Chatparticipants;

// end of import

use App\Http\Controllers\MessagesController;
use App\Models\Messages;

// end of import

use App\Http\Controllers\MessagereadsController;
use App\Models\Messagereads;

// end of import

use App\Http\Controllers\ChatattachmentsController;
use App\Models\Chatattachments;

// end of import

use App\Http\Controllers\UsersController;
use App\Models\Users;

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

    Route::get('/initialize-chat/{usersId}/{recieversId}', function ($usersId, $recieversId) {
        // Check if a chat already exists with both users (not a group chat)
        $existingChat = DB::table('chats')
            ->join('chatparticipants as cp1', 'chats.id', '=', 'cp1.chats_id')
            ->join('chatparticipants as cp2', 'chats.id', '=', 'cp2.chats_id')
            ->where('chats.is_group', 0)
            ->where('cp1.users_id', $usersId)
            ->where('cp2.users_id', $recieversId)
            ->select('chats.id')
            ->first();

        if ($existingChat) {
            // Chat already exists, return existing chat ID
            return response()->json($existingChat->id);
        }

        // Create a new chat
        $user = User::where('id', $usersId)->value('name');
        $reciever = User::where('id', $recieversId)->value('name');

        $newChat = Chats::create([
            'name' => $user . ' - ' . $reciever,
            'is_group' => 0,
            'users_id' => $usersId
        ]);

        Chatparticipants::create([
            'chats_id' => $newChat->id,
            'chats_users_id' => $usersId,
            'users_id' => $usersId
        ]);

        Chatparticipants::create([
            'chats_id' => $newChat->id,
            'chats_users_id' => $usersId,
            'users_id' => $recieversId
        ]);

        Messages::create([
            'chats_id' => $newChat->id,
            'chats_users_id' => $usersId,
            'users_id' => $usersId,
            'message' => $user . ' started a new chat!',
        ]);

        return response()->json($newChat->id);
    });

    Route::post('/send-chat', function (Request $request) {

        $request->validate([
            'hasAttachments' => 'required',
            'message' => 'required',
            'senders_id' => 'required',
            'chats_id' => 'required',
        ]);

        // get the chat users id (chat creator)

        $chatCreator = Chats::where('id', $request->chats_id)->value('users_id');

        $newMessage = Messages::create([
            'has_attachments' => $request->hasAttachments,
            'message' => $request->message,
            'chats_id' => $request->chats_id,
            'chats_users_id' => $chatCreator,
            'users_id' => $request->senders_id,
        ]);

        // Message Reads

        Messagereads::create([
            'read_at' => date('F j, Y'),
            'messages_id' => $newMessage->id,
            'users_id' => $request->senders_id,
        ]);

        // return response()->json('message sent');

        return response()->json([
            'success' => true,
            'message_id' => $newMessage->id,
        ]);

    });

    Route::get('/fetch-messages/{chatsId}', function ($chatsId) {
        $authId = Auth::user()->id;

        $chat = Chats::where('id', $chatsId)->first();
        $messages = Messages::where('chats_id', $chatsId)->get();

        $messagesWithAttachments = $messages->map(function ($message) use ($authId) {
            // Check for attachments
            if ($message->has_attachments) {
                $attachments = Chatattachments::where('messages_id', $message->id)->get();
                $message->files = $attachments;
            } else {
                $message->files = [];
            }

            // Check if message is seen by the current user
            $message->seen = Messagereads::where('messages_id', $message->id)
                                ->where('users_id', $authId)
                                ->exists();

            return $message;
        });

        return response()->json([
            'chatId' => $chatsId,
            'chat' => $chat,
            'messages' => $messagesWithAttachments,
        ]);
    });

    Route::get('/chat-history', function () {
        $authId = Auth::user()->id;

        $chats = Chats::with([
            'chatParticipants.users',
            'messages'
        ])
        ->whereHas('chatParticipants', function ($query) use ($authId) {
            $query->where('users_id', $authId);
        })
        ->get()
        ->map(function ($chat) use ($authId) {
            $receiver = $chat->chatParticipants
                ->where('users_id', '!=', $authId)
                ->first()
                ?->users;

            $latestMessage = $chat->messages->sortByDesc('updated_at')->first();

            $seen = true;
            if ($latestMessage) {
                $seen = Messagereads::where('messages_id', $latestMessage->id)
                    ->where('users_id', $authId)
                    ->exists();
            }

            return [
                'chat_id' => $chat->id,
                'chat_name' => $chat->name,
                'receiver_id' => $receiver?->id,
                'receiver_name' => $receiver?->name,
                'receiver_profile_picture' => $receiver?->profile_photo_path,
                'latest_message' => Smark\Smark\Stringer::truncateString($latestMessage?->message, 10),
                'latest_message_at' => $latestMessage?->updated_at,
                'seen' => $seen,
            ];
        })
        ->sortByDesc('latest_message_at')
        ->values();

        return response()->json($chats);
    });


    Route::post('/upload-files', function (Request $request) {
        $uploadedFiles = [];

        $request->validate([
            'messages_id' => 'required',
            'chats_id' => 'required',
            'files.*' => 'file|max:10240', // max 10MB per file
        ]);

        $messageId = $request->messages_id;
        $chatId = $request->chats_id;

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {

                Smark\Smark\File::upload($file, 'files/chat_uploads');

                $filename = Smark\Smark\File::$filename;
                $path = 'files/chat_uploads/'.$filename;

                Chatattachments::create([
                    'messages_id' => $messageId,
                    'chats_id' => $chatId,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_as' => $filename,
                    'path' => $path
                ]);

                $uploadedFiles[] = [
                    'original' => $file->getClientOriginalName(),
                    'stored_as' => $filename,
                    'path' => $path
                ];
            }
        }

        return response()->json([
            'success' => true,
            'files' => $uploadedFiles
        ]);
    });

    Route::post('/delete-chat', function (Request $request) {
        $request->validate([
            'chatId' => 'required'
        ]);

        Chatparticipants::where('chats_id', $request->chatId)->delete(); // delete chat participants
        Messages::where('chats_id', $request->chatId)->delete(); // delete chat messages

        foreach(Chatattachments::where('chats_id', $request->chatId)->get() as $file) {
            if ($file['path'] != 'files/chat_uploads/file-placeholder.jpg') {
                unlink('storage/'.$file['path']); // delete individual files
            }
        }

        Chatattachments::where('chats_id', $request->chatId)->delete();

        Chats::where('id', $request->chatId)->delete();

        return true;

    });

    Route::post('/unsent-message', function (Request $request) {
        $request->validate([
            'messageId' => 'required'
        ]);

        Messages::where('id', $request->messageId)->update([
            'message' => 'Unsent a message'
        ]); // delete chat messages

        foreach(Chatattachments::where('messages_id', $request->messageId)->get() as $file) {
            if ($file['path'] != 'files/chat_uploads/file-placeholder.jpg') {
                unlink('storage/'.$file['path']); // delete individual files
            }
        }

        Chatattachments::where('messages_id', $request->messageId)->delete();

        return true;

    });

    Route::post('/chat-files', function (Request $request) {
        $request->validate([
            'chatId' => 'required'
        ]);

        $chatFiles = Chatattachments::where('chats_id', $request->chatId)->get();

        return response()->json($chatFiles);

    });

    // END CHAT ROUTES

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

    Route::get('/chats', [ChatsController::class, 'index'])->name('chats.index');
    Route::get('/create-chats', [ChatsController::class, 'create'])->name('chats.create');
    Route::get('/edit-chats/{chatsId}', [ChatsController::class, 'edit'])->name('chats.edit');
    Route::get('/show-chats/{chatsId}', [ChatsController::class, 'show'])->name('chats.show');
    Route::get('/delete-chats/{chatsId}', [ChatsController::class, 'delete'])->name('chats.delete');
    Route::get('/destroy-chats/{chatsId}', [ChatsController::class, 'destroy'])->name('chats.destroy');
    Route::post('/store-chats', [ChatsController::class, 'store'])->name('chats.store');
    Route::post('/update-chats/{chatsId}', [ChatsController::class, 'update'])->name('chats.update');
    Route::post('/chats-delete-all-bulk-data', [ChatsController::class, 'bulkDelete']);
    Route::post('/chats-move-to-trash-all-bulk-data', [ChatsController::class, 'bulkMoveToTrash']);
    Route::post('/chats-restore-all-bulk-data', [ChatsController::class, 'bulkRestore']);
    Route::get('/trash-chats', [ChatsController::class, 'trash']);
    Route::get('/restore-chats/{chatsId}', [ChatsController::class, 'restore'])->name('chats.restore');

    // Chats Search
    Route::get('/chats-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $chats = Chats::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('chats.chats', compact('chats', 'search'));
    });

    // Chats Paginate
    Route::get('/chats-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the chats based on the 'paginate' value
        $chats = Chats::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated chats
        return view('chats.chats', compact('chats'));
    });

    // Chats Filter
    Route::get('/chats-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for chats
        $query = Chats::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $chats = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all chats without filtering
            $chats = $query->paginate(10);
        }

        // Return the view with chats and the selected date range
        return view('chats.chats', compact('chats', 'from', 'to'));
    });

    // end...

    Route::get('/chatparticipants', [ChatparticipantsController::class, 'index'])->name('chatparticipants.index');
    Route::get('/create-chatparticipants', [ChatparticipantsController::class, 'create'])->name('chatparticipants.create');
    Route::get('/edit-chatparticipants/{chatparticipantsId}', [ChatparticipantsController::class, 'edit'])->name('chatparticipants.edit');
    Route::get('/show-chatparticipants/{chatparticipantsId}', [ChatparticipantsController::class, 'show'])->name('chatparticipants.show');
    Route::get('/delete-chatparticipants/{chatparticipantsId}', [ChatparticipantsController::class, 'delete'])->name('chatparticipants.delete');
    Route::get('/destroy-chatparticipants/{chatparticipantsId}', [ChatparticipantsController::class, 'destroy'])->name('chatparticipants.destroy');
    Route::post('/store-chatparticipants', [ChatparticipantsController::class, 'store'])->name('chatparticipants.store');
    Route::post('/update-chatparticipants/{chatparticipantsId}', [ChatparticipantsController::class, 'update'])->name('chatparticipants.update');
    Route::post('/chatparticipants-delete-all-bulk-data', [ChatparticipantsController::class, 'bulkDelete']);
    Route::post('/chatparticipants-move-to-trash-all-bulk-data', [ChatparticipantsController::class, 'bulkMoveToTrash']);
    Route::post('/chatparticipants-restore-all-bulk-data', [ChatparticipantsController::class, 'bulkRestore']);
    Route::get('/trash-chatparticipants', [ChatparticipantsController::class, 'trash']);
    Route::get('/restore-chatparticipants/{chatparticipantsId}', [ChatparticipantsController::class, 'restore'])->name('chatparticipants.restore');

    // Chatparticipants Search
    Route::get('/chatparticipants-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $chatparticipants = Chatparticipants::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('chatparticipants.chatparticipants', compact('chatparticipants', 'search'));
    });

    // Chatparticipants Paginate
    Route::get('/chatparticipants-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the chatparticipants based on the 'paginate' value
        $chatparticipants = Chatparticipants::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated chatparticipants
        return view('chatparticipants.chatparticipants', compact('chatparticipants'));
    });

    // Chatparticipants Filter
    Route::get('/chatparticipants-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for chatparticipants
        $query = Chatparticipants::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $chatparticipants = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all chatparticipants without filtering
            $chatparticipants = $query->paginate(10);
        }

        // Return the view with chatparticipants and the selected date range
        return view('chatparticipants.chatparticipants', compact('chatparticipants', 'from', 'to'));
    });

    // end...

    Route::get('/messages', [MessagesController::class, 'index'])->name('messages.index');
    Route::get('/create-messages', [MessagesController::class, 'create'])->name('messages.create');
    Route::get('/edit-messages/{messagesId}', [MessagesController::class, 'edit'])->name('messages.edit');
    Route::get('/show-messages/{messagesId}', [MessagesController::class, 'show'])->name('messages.show');
    Route::get('/delete-messages/{messagesId}', [MessagesController::class, 'delete'])->name('messages.delete');
    Route::get('/destroy-messages/{messagesId}', [MessagesController::class, 'destroy'])->name('messages.destroy');
    Route::post('/store-messages', [MessagesController::class, 'store'])->name('messages.store');
    Route::post('/update-messages/{messagesId}', [MessagesController::class, 'update'])->name('messages.update');
    Route::post('/messages-delete-all-bulk-data', [MessagesController::class, 'bulkDelete']);
    Route::post('/messages-move-to-trash-all-bulk-data', [MessagesController::class, 'bulkMoveToTrash']);
    Route::post('/messages-restore-all-bulk-data', [MessagesController::class, 'bulkRestore']);
    Route::get('/trash-messages', [MessagesController::class, 'trash']);
    Route::get('/restore-messages/{messagesId}', [MessagesController::class, 'restore'])->name('messages.restore');

    // Messages Search
    Route::get('/messages-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $messages = Messages::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('messages.messages', compact('messages', 'search'));
    });

    // Messages Paginate
    Route::get('/messages-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the messages based on the 'paginate' value
        $messages = Messages::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated messages
        return view('messages.messages', compact('messages'));
    });

    // Messages Filter
    Route::get('/messages-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for messages
        $query = Messages::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $messages = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all messages without filtering
            $messages = $query->paginate(10);
        }

        // Return the view with messages and the selected date range
        return view('messages.messages', compact('messages', 'from', 'to'));
    });

    // end...

    Route::get('/messagereads', [MessagereadsController::class, 'index'])->name('messagereads.index');
    Route::get('/create-messagereads', [MessagereadsController::class, 'create'])->name('messagereads.create');
    Route::get('/edit-messagereads/{messagereadsId}', [MessagereadsController::class, 'edit'])->name('messagereads.edit');
    Route::get('/show-messagereads/{messagereadsId}', [MessagereadsController::class, 'show'])->name('messagereads.show');
    Route::get('/delete-messagereads/{messagereadsId}', [MessagereadsController::class, 'delete'])->name('messagereads.delete');
    Route::get('/destroy-messagereads/{messagereadsId}', [MessagereadsController::class, 'destroy'])->name('messagereads.destroy');
    Route::post('/store-messagereads', [MessagereadsController::class, 'store'])->name('messagereads.store');
    Route::post('/update-messagereads/{messagereadsId}', [MessagereadsController::class, 'update'])->name('messagereads.update');
    Route::post('/messagereads-delete-all-bulk-data', [MessagereadsController::class, 'bulkDelete']);
    Route::post('/messagereads-move-to-trash-all-bulk-data', [MessagereadsController::class, 'bulkMoveToTrash']);
    Route::post('/messagereads-restore-all-bulk-data', [MessagereadsController::class, 'bulkRestore']);
    Route::get('/trash-messagereads', [MessagereadsController::class, 'trash']);
    Route::get('/restore-messagereads/{messagereadsId}', [MessagereadsController::class, 'restore'])->name('messagereads.restore');

    // Messagereads Search
    Route::get('/messagereads-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $messagereads = Messagereads::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('messagereads.messagereads', compact('messagereads', 'search'));
    });

    // Messagereads Paginate
    Route::get('/messagereads-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the messagereads based on the 'paginate' value
        $messagereads = Messagereads::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated messagereads
        return view('messagereads.messagereads', compact('messagereads'));
    });

    // Messagereads Filter
    Route::get('/messagereads-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for messagereads
        $query = Messagereads::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $messagereads = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all messagereads without filtering
            $messagereads = $query->paginate(10);
        }

        // Return the view with messagereads and the selected date range
        return view('messagereads.messagereads', compact('messagereads', 'from', 'to'));
    });

    // end...

    Route::get('/chatattachments', [ChatattachmentsController::class, 'index'])->name('chatattachments.index');
    Route::get('/create-chatattachments', [ChatattachmentsController::class, 'create'])->name('chatattachments.create');
    Route::get('/edit-chatattachments/{chatattachmentsId}', [ChatattachmentsController::class, 'edit'])->name('chatattachments.edit');
    Route::get('/show-chatattachments/{chatattachmentsId}', [ChatattachmentsController::class, 'show'])->name('chatattachments.show');
    Route::get('/delete-chatattachments/{chatattachmentsId}', [ChatattachmentsController::class, 'delete'])->name('chatattachments.delete');
    Route::get('/destroy-chatattachments/{chatattachmentsId}', [ChatattachmentsController::class, 'destroy'])->name('chatattachments.destroy');
    Route::post('/store-chatattachments', [ChatattachmentsController::class, 'store'])->name('chatattachments.store');
    Route::post('/update-chatattachments/{chatattachmentsId}', [ChatattachmentsController::class, 'update'])->name('chatattachments.update');
    Route::post('/chatattachments-delete-all-bulk-data', [ChatattachmentsController::class, 'bulkDelete']);
    Route::post('/chatattachments-move-to-trash-all-bulk-data', [ChatattachmentsController::class, 'bulkMoveToTrash']);
    Route::post('/chatattachments-restore-all-bulk-data', [ChatattachmentsController::class, 'bulkRestore']);
    Route::get('/trash-chatattachments', [ChatattachmentsController::class, 'trash']);
    Route::get('/restore-chatattachments/{chatattachmentsId}', [ChatattachmentsController::class, 'restore'])->name('chatattachments.restore');

    // Chatattachments Search
    Route::get('/chatattachments-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $chatattachments = Chatattachments::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('chatattachments.chatattachments', compact('chatattachments', 'search'));
    });

    // Chatattachments Paginate
    Route::get('/chatattachments-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the chatattachments based on the 'paginate' value
        $chatattachments = Chatattachments::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated chatattachments
        return view('chatattachments.chatattachments', compact('chatattachments'));
    });

    // Chatattachments Filter
    Route::get('/chatattachments-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for chatattachments
        $query = Chatattachments::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $chatattachments = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all chatattachments without filtering
            $chatattachments = $query->paginate(10);
        }

        // Return the view with chatattachments and the selected date range
        return view('chatattachments.chatattachments', compact('chatattachments', 'from', 'to'));
    });

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
    Route::get('/users-search', function (Request $request) {
        $search = $request->get('search');

        // Perform the search logic
        $users = User::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('users.users', compact('users', 'search'));
    });

    // Users Paginate
    Route::get('/users-paginate', function (Request $request) {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the users based on the 'paginate' value
        $users = User::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated users
        return view('users.users', compact('users'));
    });

    // Users Filter
    Route::get('/users-filter', function (Request $request) {
        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Retrieve 'from' and 'to' dates from the URL
        $from = $request->input('from');
        $to = $request->input('to');

        // Default query for users
        $query = User::query();

        // Convert dates to Carbon instances for better comparison
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate = $to ? Carbon::parse($to)->endOfDay() : null;

        // Check if both 'from' and 'to' dates are provided
        if ($fromDate && $toDate) {
            // Ensure correct date filtering with full day range
            $users = $query->whereBetween('created_at', [$fromDate, $toDate])
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        } else {
            // If 'from' or 'to' are missing, show all users without filtering
            $users = $query->paginate(10);
        }

        // Return the view with users and the selected date range
        return view('users.users', compact('users', 'from', 'to'));
    });

    // end...

});
