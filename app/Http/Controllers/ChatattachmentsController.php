<?php

namespace App\Http\Controllers;

use App\Models\{Logs, Chatattachments};
use App\Http\Requests\StoreChatattachmentsRequest;
use App\Http\Requests\UpdateChatattachmentsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatattachmentsController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('chatattachments.chatattachments', [
            'chatattachments' => Chatattachments::where('isTrash', '0')->paginate(10)
        ]);
    }

    public function trash()
    {
        return view('chatattachments.trash-chatattachments', [
            'chatattachments' => Chatattachments::where('isTrash', '1')->paginate(10)
        ]);
    }

    public function restore($chatattachmentsId)
    {
        Chatattachments::where('id', $chatattachmentsId)->update(['isTrash' => '0']);

        return redirect('/chatattachments');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('chatattachments.create-chatattachments');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatattachmentsRequest $request)
    {
        Chatattachments::create(['chats_id' => $request->chats_id,'messages_id' => $request->messages_id,'original_name' => $request->original_name,'stored_as' => $request->stored_as,'path' => $request->path]);

        return back()->with('success', 'Chatattachments Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Chatattachments $chatattachments, $chatattachmentsId)
    {
        return view('chatattachments.show-chatattachments', [
            'item' => Chatattachments::where('id', $chatattachmentsId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chatattachments $chatattachments, $chatattachmentsId)
    {
        return view('chatattachments.edit-chatattachments', [
            'item' => Chatattachments::where('id', $chatattachmentsId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatattachmentsRequest $request, Chatattachments $chatattachments, $chatattachmentsId)
    {
        $chatattachments = Chatattachments::findOrFail($chatattachmentsId);

        $chatattachments->chats_id = $request->chats_id;
        $chatattachments->messages_id = $request->messages_id;
        $chatattachments->original_name = $request->original_name;
        $chatattachments->stored_as = $request->stored_as;
        $chatattachments->path = $request->path;

        $chatattachments->save();

        return back()->with('success', 'Chatattachments Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Chatattachments $chatattachments, $chatattachmentsId)
    {
        return view('chatattachments.delete-chatattachments', [
            'item' => Chatattachments::where('id', $chatattachmentsId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chatattachments $chatattachments, $chatattachmentsId)
    {

        Chatattachments::where('id', $chatattachmentsId)->update(['isTrash' => '1']);

        return redirect('/chatattachments');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = Chatattachments::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = Chatattachments::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            $restorable = Chatattachments::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}