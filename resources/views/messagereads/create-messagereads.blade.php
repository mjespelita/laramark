
@extends('layouts.main')

@section('content')
    <h1>Create a new messagereads</h1>

    <div class='card'>
        <div class='card-body'>
            <form action='{{ route('messagereads.store') }}' method='POST'>
                @csrf
                
        <div class='form-group'>
            <label for='name'>Users_id</label>
            <input type='text' class='form-control' id='users_id' name='users_id' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Messages_id</label>
            <input type='text' class='form-control' id='messages_id' name='messages_id' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Read_at</label>
            <input type='text' class='form-control' id='read_at' name='read_at' required>
        </div>
    
                <button type='submit' class='btn btn-primary mt-3'>Create</button>
            </form>
        </div>
    </div>

@endsection
