<?php

namespace App\Http\Controllers;

use App\Models\{Logs, Messages};
use App\Http\Requests\StoreMessagesRequest;
use App\Http\Requests\UpdateMessagesRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('messages.messages', [
            'messages' => Messages::where('isTrash', '0')->paginate(10)
        ]);
    }

    public function trash()
    {
        return view('messages.trash-messages', [
            'messages' => Messages::where('isTrash', '1')->paginate(10)
        ]);
    }

    public function restore($messagesId)
    {
        Messages::where('id', $messagesId)->update(['isTrash' => '0']);

        return redirect('/messages');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('messages.create-messages');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMessagesRequest $request)
    {
        Messages::create(['message' => $request->message,'chats_id' => $request->chats_id,'chats_users_id' => $request->chats_users_id,'users_id' => $request->users_id,'has_attachments' => $request->has_attachments]);

        return back()->with('success', 'Messages Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Messages $messages, $messagesId)
    {
        return view('messages.show-messages', [
            'item' => Messages::where('id', $messagesId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Messages $messages, $messagesId)
    {
        return view('messages.edit-messages', [
            'item' => Messages::where('id', $messagesId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMessagesRequest $request, Messages $messages, $messagesId)
    {
        $messages = Messages::findOrFail($messagesId);

        $messages->message = $request->message;
        $messages->chats_id = $request->chats_id;
        $messages->chats_users_id = $request->chats_users_id;
        $messages->users_id = $request->users_id;
        $messages->has_attachments = $request->has_attachments;

        $messages->save();

        return back()->with('success', 'Messages Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Messages $messages, $messagesId)
    {
        return view('messages.delete-messages', [
            'item' => Messages::where('id', $messagesId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Messages $messages, $messagesId)
    {

        Messages::where('id', $messagesId)->update(['isTrash' => '1']);

        return redirect('/messages');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = Messages::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = Messages::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            $restorable = Messages::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}