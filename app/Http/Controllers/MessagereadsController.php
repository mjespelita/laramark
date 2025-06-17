<?php

namespace App\Http\Controllers;

use App\Models\{Logs, Messagereads};
use App\Http\Requests\StoreMessagereadsRequest;
use App\Http\Requests\UpdateMessagereadsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagereadsController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('messagereads.messagereads', [
            'messagereads' => Messagereads::where('isTrash', '0')->paginate(10)
        ]);
    }

    public function trash()
    {
        return view('messagereads.trash-messagereads', [
            'messagereads' => Messagereads::where('isTrash', '1')->paginate(10)
        ]);
    }

    public function restore($messagereadsId)
    {
        Messagereads::where('id', $messagereadsId)->update(['isTrash' => '0']);

        return redirect('/messagereads');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('messagereads.create-messagereads');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMessagereadsRequest $request)
    {
        Messagereads::create(['users_id' => $request->users_id,'messages_id' => $request->messages_id,'read_at' => $request->read_at]);

        return back()->with('success', 'Messagereads Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Messagereads $messagereads, $messagereadsId)
    {
        return view('messagereads.show-messagereads', [
            'item' => Messagereads::where('id', $messagereadsId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Messagereads $messagereads, $messagereadsId)
    {
        return view('messagereads.edit-messagereads', [
            'item' => Messagereads::where('id', $messagereadsId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMessagereadsRequest $request, Messagereads $messagereads, $messagereadsId)
    {
        $messagereads = Messagereads::findOrFail($messagereadsId);

        $messagereads->users_id = $request->users_id;
        $messagereads->messages_id = $request->messages_id;
        $messagereads->read_at = $request->read_at;

        $messagereads->save();

        return back()->with('success', 'Messagereads Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Messagereads $messagereads, $messagereadsId)
    {
        return view('messagereads.delete-messagereads', [
            'item' => Messagereads::where('id', $messagereadsId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Messagereads $messagereads, $messagereadsId)
    {

        Messagereads::where('id', $messagereadsId)->update(['isTrash' => '1']);

        return redirect('/messagereads');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = Messagereads::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = Messagereads::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            $restorable = Messagereads::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}