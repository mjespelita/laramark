@extends('layouts.main')

@section('content')
    <h1 class="mb-4">💬 Messenger Notification Setup</h1>

    <div class="card shadow-sm">
        <div class="card-body p-4 bg-light">

            <div class="border-start border-4 border-primary ps-3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong class="text-primary">Messenger Notification</strong>
                        <span class="badge bg-info text-dark">SETUP GUIDE</span>
                    </div>
                    <small class="text-muted">
                        {{ \Carbon\Carbon::now()->format('F d, Y (l) - h:i A') }}
                    </small>
                </div>

                <p class="text-secondary">
                    To be able to <strong>receive real-time notifications</strong> through Facebook Messenger,
                    please follow these simple steps:
                </p>

                <ol class="text-secondary">
                    <li>Click the button below — it will open your Messenger chat with our bot.</li>
                    <li>Once it opens, simply type or send <strong>“{{ Auth::user()->id }}”</strong> as your first message.</li>
                    <li>After that, your Messenger account will be linked and ready to receive alerts.</li>
                </ol>

                <div class="mt-3">
                    <a href="https://m.me/{{ env('FB_PAGE_ID') }}"
                       target="_blank"
                       class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                        <i class="bi bi-messenger"></i> Connect via Messenger
                    </a>
                </div>

                <small class="d-block text-muted mt-3">
                    🔒 Your Messenger ID will only be used for sending system notifications related to your account.
                </small>
            </div>

            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle"></i> If you’ve already sent <strong>“{{ Auth::user()->id }}”</strong>, you don’t need to do it again.
                You’ll start receiving notifications automatically.
            </div>
        </div>
    </div>
@endsection
