<?php

namespace App\Http\Controllers;

use App\Models\{Chatattachments, Chatparticipants, Chats, Logs, Messages, User, Users};
use App\Http\Requests\StoreUsersRequest;
use App\Http\Requests\UpdateUsersRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('users.users', [
            'users' => User::orderBy('id', 'desc')->paginate(10)
        ]);
    }

    public function trash()
    {
        return view('users.trash-users', [
            'users' => User::where('isTrash', '1')->paginate(10)
        ]);
    }

    public function restore($usersId)
    {
        User::where('id', $usersId)->update(['isTrash' => '0']);

        return redirect('/users');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create-users');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUsersRequest $request)
    {
        User::create(['name' => $request->name,'email' => $request->email,'password' => Hash::make($request->password),'role' => $request->role]);

        return back()->with('success', 'Users Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Users $users, $usersId)
    {
        return view('users.show-users', [
            'item' => User::where('id', $usersId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Users $users, $usersId)
    {
        return view('users.edit-users', [
            'item' => User::where('id', $usersId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUsersRequest $request, Users $users, $usersId)
    {
        $users = User::findOrFail($usersId);

        $users->name = $request->name;
        $users->email = $request->email;
        $users->role = $request->role;

        $users->save();

        return back()->with('success', 'Users Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Users $users, $usersId)
    {
        return view('users.delete-users', [
            'item' => Users::where('id', $usersId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Users $users, $usersId)
    {

        User::where('id', $usersId)->delete();

        // chatsid
        $chatIds = [];
        $chatUserParticipants = Chatparticipants::where('users_id', $usersId)->get();

        foreach ($chatUserParticipants as $key => $chatUserParticipant) {
            array_push($chatIds, $chatUserParticipant->chats_id);
        }

        foreach ($chatIds as $key => $chatId) {

            // delete attachtments

            $chatAttachments = Chatattachments::where('chats_id', $chatId)->get();

            foreach ($chatAttachments as $key => $chatAttachment) {
                unlink('storage/'.$chatAttachment['path']);
            }

            Chatattachments::where('chats_id', $chatId)->delete();

            // delete participants

            Chatparticipants::where('chats_id', $chatId)->delete();

            // delete chats

            Chats::where('id', $chatId)->delete();

            // delete messages

            Messages::where('chats_id', $chatId)->delete();

        }


        return redirect('/users');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = User::find($value);
            $deletable->delete();

            // chatsid
            $chatIds = [];
            $chatUserParticipants = Chatparticipants::where('users_id', $value)->get();

            foreach ($chatUserParticipants as $key => $chatUserParticipant) {
                array_push($chatIds, $chatUserParticipant->chats_id);
            }

            foreach ($chatIds as $key => $chatId) {

                // delete attachtments

                $chatAttachments = Chatattachments::where('chats_id', $chatId)->get();

                foreach ($chatAttachments as $key => $chatAttachment) {
                    unlink('storage/'.$chatAttachment['path']);
                }

                Chatattachments::where('chats_id', $chatId)->delete();

                // delete participants

                Chatparticipants::where('chats_id', $chatId)->delete();

                // delete chats

                Chats::where('id', $chatId)->delete();

                // delete messages

                Messages::where('chats_id', $chatId)->delete();

            }

        }
        return response()->json("Deleted");
    }

    public function bulkMoveToTrash(Request $request) {

        foreach ($request->ids as $value) {

            $deletable = User::find($value);
            $deletable->update(['isTrash' => '1']);
        }
        return response()->json("Deleted");
    }

    public function bulkRestore(Request $request)
    {
        foreach ($request->ids as $value) {

            $restorable = User::find($value);
            $restorable->update(['isTrash' => '0']);
        }
        return response()->json("Restored");
    }
}
