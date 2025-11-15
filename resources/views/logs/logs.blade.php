@extends('layouts.main')

@section('content')
    <h1 class="mb-4">📝 Activity Logs</h1>

    <div class="card shadow-sm">
        <div class="card-body p-4 bg-light">

            @forelse ($audits as $audit)
                <div class="border-start border-4 ps-3 mb-4
                    @if($audit->event === 'created') border-success
                    @elseif($audit->event === 'updated') border-warning
                    @elseif($audit->event === 'deleted') border-danger
                    @else border-secondary @endif">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong class="text-primary">{{ $audit->user ? $audit->user->name : 'System' }}</strong>
                            <span class="badge
                                @if($audit->event === 'created') bg-success
                                @elseif($audit->event === 'updated') bg-warning text-dark
                                @elseif($audit->event === 'deleted') bg-danger
                                @else bg-secondary @endif">
                                {{ strtoupper($audit->event) }}
                            </span>
                            <strong class="ms-1">{{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}</strong>
                        </div>
                        <small class="text-muted">
                            {{ Smark\Smark\Dater::humanReadableDateWithDayAndTime($audit->created_at) }}
                        </small>
                    </div>

                    @if ($audit->event === 'updated')
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle bg-white border">
                                <thead class="table-light">
                                    <tr>
                                        <th>Field</th>
                                        <th class="text-danger">Old</th>
                                        <th class="text-success">New</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($audit->new_values as $key => $value)
                                        @php $old = $audit->old_values[$key] ?? 'N/A'; @endphp
                                        <tr>
                                            <td><code>{{ ucfirst($key) }}</code></td>
                                            <td class="text-danger">{{ $old }}</td>
                                            <td class="text-success">{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif ($audit->event === 'created')
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle bg-white border">
                                <thead class="table-light">
                                    <tr>
                                        <th>Field</th>
                                        <th class="text-success">New Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($audit->new_values as $key => $value)
                                        <tr>
                                            <td><code>{{ ucfirst($key) }}</code></td>
                                            <td class="text-success">{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif ($audit->event === 'deleted')
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle bg-white border">
                                <thead class="table-light">
                                    <tr>
                                        <th>Field</th>
                                        <th class="text-danger">Deleted Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($audit->old_values as $key => $value)
                                        <tr>
                                            <td><code>{{ ucfirst($key) }}</code></td>
                                            <td class="text-danger">{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @php
                        $pairs = explode(',', $audit->tags);
                        $tagsArray = [];
                        foreach ($pairs as $pair) {
                            $parts = explode(':', $pair, 2);
                            if(count($parts) == 2) {
                                $tagsArray[$parts[0]] = $parts[1];
                            }
                        }
                    @endphp

                    @if (!empty($tagsArray))
                        <div class="mt-2">
                            @foreach ($tagsArray as $key => $val)
                                <span class="badge bg-secondary me-1">{{ $key }}: {{ $val }}</span>
                            @endforeach
                        </div>
                    @endif

                    <small class="d-block text-muted mt-2">
                        IP: <code>{{ $audit->ip_address }}</code> |
                        URL: <code>{{ $audit->url }}</code> |
                        Agent: <code>{{ Str::limit($audit->user_agent, 50) }}</code>
                    </small>
                </div>
            @empty
                <div class="alert alert-info text-center">
                    No audit logs available.
                </div>
            @endforelse

            <div class="mt-4">
                {{ $audits->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
