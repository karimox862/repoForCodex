@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto bg-white shadow rounded p-6">
        <h1 class="text-2xl font-semibold mb-4">New Module</h1>
        <form method="POST" action="{{ route('admin.modules.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Code</label>
                <input type="text" name="code" value="{{ old('code') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Professor</label>
                <select name="professor_id" class="mt-1 block w-full border rounded px-3 py-2">
                    <option value="">-- Unassigned --</option>
                    @foreach ($professors as $professor)
                        <option value="{{ $professor->id }}" @selected(old('professor_id') == $professor->id)>{{ $professor->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Enrolled Students</label>
                <select name="students[]" multiple size="8" class="mt-1 block w-full border rounded px-3 py-2">
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected(collect(old('students'))->contains($student->id))>{{ $student->name }} ({{ $student->registration_number }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Hold Ctrl (Windows) or Command (Mac) to select multiple students.</p>
            </div>
            <div class="flex justify-end space-x-2">
                <a href="{{ route('admin.modules.index') }}" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Create</button>
            </div>
        </form>
    </div>
@endsection
