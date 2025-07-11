<?php

namespace App\Http\Controllers;

use App\Models\{Logs, Chatparticipants};
use App\Http\Requests\StoreChatparticipantsRequest;
use App\Http\Requests\UpdateChatparticipantsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatparticipantsController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('chatparticipants.chatparticipants', [
            'chatparticipants' => Chatparticipants::where('isTrash', '0')->paginate(10)
        ]);
    }

    public function trash()
    {
        return view('chatparticipants.trash-chatparticipants', [
            'chatparticipants' => Chatparticipants::where('isTrash', '1')->paginate(10)
        ]);
    }

    public function restore($chatparticipantsId)
    {
        Chatparticipants::where('id', $chatparticipantsId)->update(['isTrash' => '0']);

        return redirect('/chatparticipants');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('chatparticipants.create-chatparticipants');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatparticipantsRequest $request)
    {
        Chatparticipants::create(['chats_id' => $request->chats_id,'chats_users_id' => $request->chats_users_id,'users_id' => $request->users_id]);

        return back()->with('success', 'Chatparticipants Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Chatparticipants $chatparticipants, $chatparticipantsId)
    {
        return view('chatparticipants.show-chatparticipants', [
            'item' => Chatparticipants::where('id', $chatparticipantsId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chatparticipants $chatparticipants, $chatparticipantsId)
    {
        return view('chatparticipants.edit-chatparticipants', [
            'item' => Chatparticipants::where('id', $chatparticipantsId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatparticipantsRequest $request, Chatparticipants $chatparticipants, $chatparticipantsId)
    {
        $chatparticipants = Chatparticipants::findOrFail($chatparticipantsId);

        $chatparticipants->chats_id = $request->chats_id;
        $chatparticipants->chats_users_id = $request->chats_users_id;
        $chatparticipants->users_id = $request->users_id;

        $chatparticipants->save();

        return back()->with('success', 'Chatparticipants Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Chatparticipants $chatparticipants, $chatparticipantsId)
    {
        return view('chatparticipants.delete-chatparticipants', [
            'item' => Chatparticipants::where('id', $chatparticipantsId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chatparticipants $chatparticipants, $chatparticipantsId)
    {

        Chatparticipants::where('id', $chatparticipantsId)->update(['isTrash' => '1']);

        return redirect('/chatparticipants');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = Chatparticipants::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = Chatparticipants::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            $restorable = Chatparticipants::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}