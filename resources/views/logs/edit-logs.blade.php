
@extends('layouts.main')

@section('content')
    <h1>Edit Logs</h1>

    <form action='{{ route('logs.update', $item->id) }}' method='POST'>
        @csrf
        
        <div class='form-group'>
            <label for='name'>Log</label>
            <input type='text' class='form-control' id='log' name='log' value='{{ $item->log }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Users_id</label>
            <input type='text' class='form-control' id='users_id' name='users_id' value='{{ $item->users_id }}' required>
        </div>
    
        <button type='submit' class='btn btn-primary mt-3'>Update</button>
    </form>
@endsection
