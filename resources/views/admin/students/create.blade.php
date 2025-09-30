<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight text-gray-800">New Student</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto bg-white shadow rounded p-6">
        <form method="POST" action="{{ route('admin.students.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Apogée Code</label>
                    <input type="text" name="apogee_code" value="{{ old('apogee_code') }}" class="mt-1 block w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Birth Date</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="mt-1 block w-full border rounded px-3 py-2">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email (optional)</label>
                <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full border rounded px-3 py-2">
            </div>
            <div class="flex justify-end space-x-2">
                <a href="{{ route('admin.students.index') }}" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Create</button>
            </div>
        </form>
    </div>
</x-app-layout>
