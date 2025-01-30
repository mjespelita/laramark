
@extends('layouts.main')

@section('content')
    <h1>Logs Details</h1>
    <table class='table'>
        <tr>
            <th>ID</th>
            <td>{{ $item->id }}</td>
        </tr>
        
        <tr>
            <th>Log</th>
            <td>{{ $item->log }}</td>
        </tr>
    
        <tr>
            <th>Users_id</th>
            <td>{{ $item->users_id }}</td>
        </tr>
    
    </table>

    <a href='{{ route('logs.index') }}' class='btn btn-primary'>Back to List</a>
@endsection
