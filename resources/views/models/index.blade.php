<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Model Viewer</title>
        <link href='{{ url('assets/bootstrap/bootstrap.min.css') }}' rel='stylesheet'>
    <style>
        body {
            padding-top: 20px;
        }
        .sidebar {
            min-height: 100vh;
            border-right: 1px solid #dee2e6;
        }
        .active-link {
            font-weight: bold;
            color: #0d6efd !important;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .list-group-item {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h5 class="mt-3">Models</h5>
                <ul class="nav flex-column">
                    @foreach ($models as $m)
                        <li class="nav-item">
                            <a class="nav-link {{ $m == $model ? 'active-link' : '' }}" href="{{ route('models.index', $m) }}">
                                {{ $m }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <h3 class="mt-3">{{ $model ?? 'Select a Model' }}</h3>

                @if ($records->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    @foreach ($records->first()->getAttributes() as $key => $value)
                                        <th>{{ $key }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($records as $record)
                                    <tr>
                                        @foreach ($record->getAttributes() as $value)
                                            <td>{{ \Str::limit($value, 50) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $records->links('pagination::bootstrap-5') }}
                @elseif ($model)
                    <div class="alert alert-info">No records found for <strong>{{ $model }}</strong>.</div>
                @else
                    <div class="row">
                        @foreach ($models as $m)
                            @php
                                $modelClass = "App\\Models\\$m";
                                $instance = class_exists($modelClass) ? $modelClass::first() : null;
                                $columns = $instance ? array_keys($instance->getAttributes()) : [];
                            @endphp

                            <div class="col-md-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $m }}</h5>
                                        
                                        @if ($columns)
                                            <ul class="list-group list-group-flush">
                                                @foreach ($columns as $col)
                                                    <li class="list-group-item">{{ $col }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <div class="text-muted mt-2">No records found (or table is empty)</div>
                                        @endif
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="{{ route('models.index', $m) }}" class="btn btn-sm btn-primary">View Records</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
