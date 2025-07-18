
@extends('layouts.main')

@section('content')
    <h1>Edit Messages</h1>

    <div class='card'>
        <div class='card-body'>
            <form action='{{ route('messages.update', $item->id) }}' method='POST'>
                @csrf
                
        <div class='form-group'>
            <label for='name'>Message</label>
            <input type='text' class='form-control' id='message' name='message' value='{{ $item->message }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Chats_id</label>
            <input type='text' class='form-control' id='chats_id' name='chats_id' value='{{ $item->chats_id }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Chats_users_id</label>
            <input type='text' class='form-control' id='chats_users_id' name='chats_users_id' value='{{ $item->chats_users_id }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Users_id</label>
            <input type='text' class='form-control' id='users_id' name='users_id' value='{{ $item->users_id }}' required>
        </div>
    
        <div class='form-group'>
            <label for='name'>Has_attachments</label>
            <input type='text' class='form-control' id='has_attachments' name='has_attachments' value='{{ $item->has_attachments }}' required>
        </div>
    
                <button type='submit' class='btn btn-primary mt-3'>Update</button>
            </form>
        </div>
    </div>

@endsection
