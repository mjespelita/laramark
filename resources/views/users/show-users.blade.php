
@extends('layouts.main')

@section('content')
    <h1>Users Details</h1>

    <div class='card'>
        <div class='card-body'>
    
            {{-- Avatar --}}
            @if ($item->profile_photo_path)
                <img src="{{ url('storage/'.$item->profile_photo_path) }}" 
                     style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 15px;" 
                     alt="Profile Photo">
            @else
                @php
                    $names = explode(' ', trim($item->name));
                    $initials = strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1));
                @endphp
                <div style="
                    width: 120px;
                    height: 120px;
                    border-radius: 50%;
                    background: linear-gradient(to bottom, #2196F3, #1976D2);
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 40px;
                    font-family: sans-serif;
                    margin-bottom: 15px;
                ">
                    {{ $initials }}
                </div>
            @endif
    
            <div class='table-responsive'>
                <table class='table'>
    
                    <tr>
                        <th>Name</th>
                        <td>{{ $item->name }}</td>
                    </tr>
    
                    <tr>
                        <th>Email</th>
                        <td>{{ $item->email }}</td>
                    </tr>
    
                    <tr>
                        <th>Role</th>
                        <td>{{ $item->role }}</td>
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
    

    <a href='{{ route('users.index') }}' class='btn btn-primary'>Back to List</a>
@endsection
