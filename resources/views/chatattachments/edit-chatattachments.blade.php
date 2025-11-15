
@extends('layouts.main')

@section('content')
    <h1>Edit Chatattachments</h1>

    <div class='card'>
        <div class='card-body'>
            <form action='{{ route('chatattachments.update', $item->id) }}' method='POST'>
                @csrf
                
        <div class='form-group'>
            <label for='name'>Chats_id</label>
            <input type='text' class='form-control' id='chats_id' name='chats_id' value='{{ $item->chats_id }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Messages_id</label>
            <input type='text' class='form-control' id='messages_id' name='messages_id' value='{{ $item->messages_id }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Original_name</label>
            <input type='text' class='form-control' id='original_name' name='original_name' value='{{ $item->original_name }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Stored_as</label>
            <input type='text' class='form-control' id='stored_as' name='stored_as' value='{{ $item->stored_as }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Path</label>
            <input type='text' class='form-control' id='path' name='path' value='{{ $item->path }}' required>
        </div>
    
                <button type='submit' class='btn btn-primary mt-3'>Update</button>
            </form>
        </div>
    </div>

@endsection
