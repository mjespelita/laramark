
@extends('layouts.main')

@section('content')
    <h1>Create a new logs</h1>

    <form action='{{ route('logs.store') }}' method='POST'>
        @csrf
        
        <div class='form-group'>
            <label for='name'>Log</label>
            <input type='text' class='form-control' id='log' name='log' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Users_id</label>
            <input type='text' class='form-control' id='users_id' name='users_id' required>
        </div>
    
        <button type='submit' class='btn btn-primary mt-3'>Create</button>
    </form>
@endsection
