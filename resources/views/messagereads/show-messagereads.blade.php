
@extends('layouts.main')

@section('content')
    <h1>Messagereads Details</h1>

    <div class='card'>
        <div class='card-body'>
            <div class='table-responsive'>
                <table class='table'>
                    <tr>
                        <th>ID</th>
                        <td>{{ $item->id }}</td>
                    </tr>
                    
        <tr>
            <th>Users_id</th>
            <td>{{ $item->users_id }}</td>
        </tr>
    
        <tr>
            <th>Messages_id</th>
            <td>{{ $item->messages_id }}</td>
        </tr>
    
        <tr>
            <th>Read_at</th>
            <td>{{ $item->read_at }}</td>
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

    <a href='{{ route('messagereads.index') }}' class='btn btn-primary'>Back to List</a>
@endsection
