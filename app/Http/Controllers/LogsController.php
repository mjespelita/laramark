<?php

namespace App\Http\Controllers;

use App\Models\{Logs};
use App\Http\Requests\StoreLogsRequest;
use App\Http\Requests\UpdateLogsRequest;
use Illuminate\Http\Request;

class LogsController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('logs.logs', [
            'logs' => Logs::paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('logs.create-logs');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLogsRequest $request)
    {
        Logs::create(['log' => $request->log,'users_id' => $request->users_id]);

        return back()->with('success', 'Logs Added Successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Logs $logs, $logsId)
    {
        return view('logs.show-logs', [
            'item' => Logs::where('id', $logsId)->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Logs $logs, $logsId)
    {
        return view('logs.edit-logs', [
            'item' => Logs::where('id', $logsId)->first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLogsRequest $request, Logs $logs, $logsId)
    {
        Logs::where('id', $logsId)->update(['log' => $request->log,'users_id' => $request->users_id]);

        return back()->with('success', 'Logs Updated Successfully!');
    }

    /**
     * Show the form for deleting the specified resource.
     */
    public function delete(Logs $logs, $logsId)
    {
        return view('logs.delete-logs', [
            'item' => Logs::where('id', $logsId)->first()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Logs $logs, $logsId)
    {
        Logs::where('id', $logsId)->delete();

        return redirect('/logs');
    }

    public function bulkDelete(Request $request) {

        foreach ($request->ids as $value) {
            $deletable = Logs::find($value);
            $deletable->delete();
        }
        return response()->json("Deleted");
    }
}
