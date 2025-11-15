
@extends('layouts.main')

@section('content')
    <h1>Chatattachments Details</h1>

    <div class='card'>
        <div class='card-body'>
            <div class='table-responsive'>
                <table class='table'>
                    <tr>
                        <th>ID</th>
                        <td>{{ $item->id }}</td>
                    </tr>
                    
        <tr>
            <th>Chats_id</th>
            <td>{{ $item->chats_id }}</td>
        </tr>
    
        <tr>
            <th>Messages_id</th>
            <td>{{ $item->messages_id }}</td>
        </tr>
    
        <tr>
            <th>Original_name</th>
            <td>{{ $item->original_name }}</td>
        </tr>
    
        <tr>
            <th>Stored_as</th>
            <td>{{ $item->stored_as }}</td>
        </tr>
    
        <tr>
            <th>Path</th>
            <td>{{ $item->path }}</td>
        </tr>
    
                    <tr>
                        <th>Created At</th>
                        <td>{{ Smark\Smark\Dater::humanReadableDateWithDayAndTime($item->created_at) }}</td>
                    </tr>
                    <tr>
                        <th>Updated At</th>
                        <td>{{ Smark\Smark\Dater::humanReadableDateWithDayAndTime($item->updated_at) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <a href='{{ route('chatattachments.index') }}' class='btn btn-primary'>Back to List</a>
@endsection
