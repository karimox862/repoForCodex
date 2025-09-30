@extends('layouts.app')

@section('content')
    <div class="bg-white shadow rounded p-6">
        <h1 class="text-2xl font-semibold mb-4">Assigned Modules</h1>
        @if ($modules->isEmpty())
            <p class="text-gray-600">You currently have no assigned modules.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled Students</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($modules as $module)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $module->code }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $module->title }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $module->students_count }}</td>
                            <td class="px-4 py-2 text-sm text-right">
                                <a href="{{ route('professor.modules.show', $module) }}" class="text-indigo-600 hover:underline">Manage</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
