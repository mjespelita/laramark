
<!DOCTYPE html>
<html lang='{{ str_replace('_', '-', app()->getLocale()) }}'>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <meta name='csrf-token' content='{{ csrf_token() }}'>
        <meta name='author' content='Mark Jason Penote Espelita'>
        <meta name='keywords' content='keyword1, keyword2'>
        <meta name='description' content='Dolorem natus ab illum beatae error voluptatem incidunt quis. Cupiditate ullam doloremque delectus culpa. Autem harum dolorem praesentium dolorum necessitatibus iure quo. Et ea aut voluptatem expedita.'>

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link href='{{ url('assets/bootstrap/bootstrap.min.css') }}' rel='stylesheet'>
        <!-- FontAwesome for icons -->
        <link href='{{ url('assets/font-awesome/css/all.min.css') }}' rel='stylesheet'>
        <link rel='stylesheet' href='{{ url('assets/custom/style.css') }}'>
        <link rel='icon' href='{{ url('assets/logo.png') }}'>
    </head>
    <body class='font-sans antialiased'>

        <!-- Sidebar for Desktop View -->
        <div class='sidebar' id='mobileSidebar'>
            <div class='logo'>
                <img src='{{ url('assets/logo.png') }}' alt='' width='100%'>
            </div>

            @php
                $navLinks = [
                    [
                        'url' => 'dashboard',
                        'icon' => 'fas fa-tachometer-alt',
                        'label' => 'Dashboard',
                        'active' => request()->is('dashboard'),
                    ],
                    [
                        'url' => 'activity-logs',
                        'icon' => 'fas fa-bars',
                        'label' => 'Logs',
                        'active' => request()->is('activity-logs', 'create-logs', 'show-logs/*', 'edit-logs/*', 'delete-logs/*', 'logs-search*'),
                    ],
                    // [
                    //     'url' => 'todos',
                    //     'icon' => 'fas fa-bars',
                    //     'label' => 'Todos',
                    //     'active' => request()->is('todos', 'create-todos', 'trash-todos', 'show-todos/*', 'edit-todos/*', 'delete-todos/*', 'todos-search*'),
                    // ],
                ];
        @endphp

        @foreach ($navLinks as $link)
            <a href="{{ url($link['url']) }}" class="{{ $link['active'] ? 'active' : '' }}">
                <i class="{{ $link['icon'] }}"></i> {{ $link['label'] }}
            </a>
        @endforeach

        <a href="{{ url('user/profile') }}">
            <i class="fas fa-user"></i> {{ Auth::user()->name }}
        </a>

        </div>

        <!-- Top Navbar -->
        <nav class='navbar navbar-expand-lg navbar-dark'>
            <div class='container-fluid'>
                <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav'
                    aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation' onclick='toggleSidebar()'>
                    <i class='fas fa-bars'></i>
                </button>
            </div>
        </nav>

        <x-main-notification />

        <div class='content'>
            @yield('content')
        </div>

        <!-- Bootstrap JS and dependencies -->
        <script src='{{ url('assets/bootstrap/bootstrap.bundle.min.js') }}'></script>

        <!-- Custom JavaScript -->
        <script src="{{ url('assets/custom/script.js') }}"></script>
        <script>
            function toggleSidebar() {
                document.getElementById('mobileSidebar').classList.toggle('active');
                document.getElementById('sidebar').classList.toggle('active');
            }
        </script>
    </body>
</html>
