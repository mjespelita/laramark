
@extends('layouts.main')

@section('content')
    <h1>Edit Messagereads</h1>

    <div class='card'>
        <div class='card-body'>
            <form action='{{ route('messagereads.update', $item->id) }}' method='POST'>
                @csrf
                
        <div class='form-group'>
            <label for='name'>Users_id</label>
            <input type='text' class='form-control' id='users_id' name='users_id' value='{{ $item->users_id }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Messages_id</label>
            <input type='text' class='form-control' id='messages_id' name='messages_id' value='{{ $item->messages_id }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Read_at</label>
            <input type='text' class='form-control' id='read_at' name='read_at' value='{{ $item->read_at }}' required>
        </div>
    
                <button type='submit' class='btn btn-primary mt-3'>Update</button>
            </form>
        </div>
    </div>

@endsection
