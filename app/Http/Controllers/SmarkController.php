<?php

namespace App\Http\Controllers;

use App\Models\Chatattachments;
use App\Models\Chatparticipants;
use App\Models\Chats;
use App\Models\Messagereads;
use App\Models\Messages;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Http;
use OwenIt\Auditing\Models\Audit;
use Smark\Smark\File;
use Smark\Smark\Stringer;

class SmarkController extends Controller
{
    // CHAT ROUTES

    public function initializeChat($usersId, $recieversId)
    {
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
    }

    public function sendChat(Request $request)
    {
        $request->validate([
            'hasAttachments' => 'required',
            'message' => '',
            'senders_id' => 'required',
            'chats_id' => 'required',
        ]);

        // get the chat users id (chat creator)

        $chatCreator = Chats::where('id', $request->chats_id)->value('users_id');

        $emojis = ['👍', '❤️', '😂', '🔥', '😎', '🎉', '🙌', '👌', '👏', '💯'];

        $messageContent = trim($request->message);
        $finalMessage = $messageContent !== '' ? $request->message : $emojis[array_rand($emojis)];

        $newMessage = Messages::create([
            'has_attachments' => $request->hasAttachments,
            'message' => $finalMessage,
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
    }

    public function fetchMessages($chatsId)
    {
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
    }

    public function chatHistory()
    {
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
                'receiver_id' => (Chatparticipants::where('chats_id', $chat->id)->get()[1]['users_id'] === 4) ? 4 : $receiver?->id,
                'receiver_name' => (Chatparticipants::where('chats_id', $chat->id)->get()[1]['users_id'] === 4) ? $chat->name : $receiver?->name,
                'receiver_profile_picture' => (Chatparticipants::where('chats_id', $chat->id)->get()[1]['users_id'] === 4) ? $chat->name : $receiver?->profile_photo_path,
                'latest_message' => Stringer::truncateString($latestMessage?->message, 10),
                'latest_message_at' => $latestMessage?->updated_at,
                'seen' => $seen,
            ];
        })
        ->sortByDesc('latest_message_at')
        ->values();

        return response()->json($chats);
    }

    public function uploadFiles(Request $request)
    {
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

                File::upload($file, 'files/chat_uploads');

                $filename = File::$filename;
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
    }

    public function deleteChat(Request $request)
    {
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
    }

    public function unsentMessage(Request $request)
    {
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
    }

    public function chatFiles(Request $request)
    {
        $request->validate([
            'chatId' => 'required'
        ]);

        $chatFiles = Chatattachments::where('chats_id', $request->chatId)->get();

        return response()->json($chatFiles);
    }

    // END OF CHAT ROUTES

    // ACTIVITY LOGS

    public function activityLogs()
    {
        $audits = Audit::latest()->paginate(10);

        return view('logs.logs', [
            'audits' => $audits]);
    }

    // END OF ACTIVITY LOGS

    // BACKUPS

    public function backups()
    {
        // Path to the backups folder
        $backupFolder = public_path('backup'); // Adjust the path as needed
        $files = FacadesFile::allFiles($backupFolder);

        return view('backups', compact('files')); // needs backup view
    }

    public function backupProcess()
    {
        // Call the backup artisan command
        Artisan::call('backup');

        // Optional: show the output in the browser
        $output = Artisan::output();

        // Return to a view or just show confirmation
        return redirect('/backups')->with('success', '✅ Backup completed.')->with('output', $output);
    }

    // END OF BACKUPS

    // MODEL VIEWER

    public function modelViewer($model = null)
    {

        // Get all model class names in app/Models directory
        $modelPath = app_path('Models');
        $files = FacadesFile::files($modelPath);

        $models = collect($files)->map(function ($file) {
            return $file->getFilenameWithoutExtension();
        });

        $records = collect();
        $perPage = 10;

        if ($model && class_exists("App\\Models\\$model")) {
            $modelClass = "App\\Models\\$model";
            $records = $modelClass::paginate($perPage);
        }

        return view('models.index', compact('models', 'records', 'model'));
    }

    // END OF MODEL VIEWER

    // USERS

    public function usersSearch(Request $request)
    {

        $search = $request->get('search');

        // Perform the search logic
        $users = User::when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%$search%");
        })->paginate(10);

        return view('users.users', compact('users', 'search'));
    }

    public function usersPaginate(Request $request)
    {
        // Retrieve the 'paginate' parameter from the URL (e.g., ?paginate=10)
        $paginate = $request->input('paginate', 10); // Default to 10 if no paginate value is provided

        // Paginate the users based on the 'paginate' value
        $users = User::paginate($paginate); // Paginate with the specified number of items per page

        // Return the view with the paginated users
        return view('users.users', compact('users'));
    }

    public function userFilter(Request $request)
    {

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
    }

    // END OF USERS

    // POSTMAN

    // Show the form
    public function postman()
    {
        return view('postman.index');
    }

}
