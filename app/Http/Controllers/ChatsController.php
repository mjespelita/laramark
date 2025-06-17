<?php

namespace App\Http\Controllers;

use App\Models\{Logs, Chats};
use App\Http\Requests\StoreChatsRequest;
use App\Http\Requests\UpdateChatsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatsController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('chats.chats', [
            'chats' => Chats::where('isTrash', '0')->paginate(10)
        ]);
    }

    public function trash()
    {
        return view('chats.trash-chats', [
            'chats' => Chats::where('isTrash', '1')->paginate(10)
        ]);
    }

    public function restore($chatsId)
    {
        Chats::where('id', $chatsId)->update(['isTrash' => '0']);

        return redirect('/chats');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('chats.create-chats');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatsRequest $request)
    {
        Chats::create(['name' => $request->name,'is_group' => $request->is_group,'users_id' => $request->users_id]);

        return back()->with('success', 'Chats Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Chats $chats, $chatsId)
    {
        return view('chats.show-chats', [
            'item' => Chats::where('id', $chatsId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chats $chats, $chatsId)
    {
        return view('chats.edit-chats', [
            'item' => Chats::where('id', $chatsId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChatsRequest $request, Chats $chats, $chatsId)
    {
        $chats = Chats::findOrFail($chatsId);

        $chats->name = $request->name;
        $chats->is_group = $request->is_group;
        $chats->users_id = $request->users_id;

        $chats->save();

        return back()->with('success', 'Chats Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Chats $chats, $chatsId)
    {
        return view('chats.delete-chats', [
            'item' => Chats::where('id', $chatsId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chats $chats, $chatsId)
    {

        Chats::where('id', $chatsId)->update(['isTrash' => '1']);

        return redirect('/chats');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = Chats::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = Chats::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            $restorable = Chats::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}