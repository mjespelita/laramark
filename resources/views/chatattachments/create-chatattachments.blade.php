
@extends('layouts.main')

@section('content')
    <h1>Create a new chatattachments</h1>

    <div class='card'>
        <div class='card-body'>
            <form action='{{ route('chatattachments.store') }}' method='POST'>
                @csrf
                
        <div class='form-group'>
            <label for='name'>Chats_id</label>
            <input type='text' class='form-control' id='chats_id' name='chats_id' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Messages_id</label>
            <input type='text' class='form-control' id='messages_id' name='messages_id' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Original_name</label>
            <input type='text' class='form-control' id='original_name' name='original_name' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Stored_as</label>
            <input type='text' class='form-control' id='stored_as' name='stored_as' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Path</label>
            <input type='text' class='form-control' id='path' name='path' required>
        </div>
    
                <button type='submit' class='btn btn-primary mt-3'>Create</button>
            </form>
        </div>
    </div>

@endsection
