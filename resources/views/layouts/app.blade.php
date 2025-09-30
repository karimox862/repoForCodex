<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.9/dist/tailwind.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow mb-8">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/" class="text-xl font-semibold text-gray-800">{{ config('app.name', 'Laravel') }}</a>
            @auth
                <div class="space-x-4 text-sm">
                    @if (auth()->user()->isProfessor())
                        <a href="{{ route('professor.modules.index') }}" class="text-gray-700 hover:text-gray-900">My Modules</a>
                    @endif
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.professors.index') }}" class="text-gray-700 hover:text-gray-900">Professors</a>
                        <a href="{{ route('admin.modules.index') }}" class="text-gray-700 hover:text-gray-900">Modules</a>
                        <a href="{{ route('admin.students.index') }}" class="text-gray-700 hover:text-gray-900">Students</a>
                    @endif
                </div>
            @endauth
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4">
        @if (session('status'))
            <div class="mb-4 rounded bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-100 border border-red-300 text-red-800 px-4 py-3">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>
</body>
</html>
