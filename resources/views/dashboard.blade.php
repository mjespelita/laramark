@extends('layouts.main')

@section('content')
    <h1>Dashboard</h1>
    <b class="top-b">Hello, {{ Auth::user()->name }} ({{ Auth::user()->role }})</b>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Sample Number</h5>
                    <h1 class="dashboard-h1">
                        <i class="fas fa-smile"></i> 322
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ url('assets/jquery/jquery.min.js') }}"></script>
    <script src="{{ url('assets/pusher/pusher.min.js') }}"></script>
    <script src="{{ url('assets/pusher/pusher-config.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.get('/user', function (res) {
                // console.log('👤 Authenticated User ID:', res.id);

                // ✅ Subscribe to the PRIVATE channel
                const channel = pusher.subscribe('private-hello-message.' + 23);

                // ✅ Event name matches your PHP (hello-message-event)
                channel.bind('hello-message-event', function (data) {
                    console.log('✅ Received from Laravel:', data);
                });
            });
        });
    </script>
@endsection
