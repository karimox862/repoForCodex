<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight text-gray-800">Edit Professor</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto bg-white shadow rounded p-6">
        <form method="POST" action="{{ route('admin.professors.update', $professor) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $professor->user->name) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $professor->user->email) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Department</label>
                <input type="text" name="department" value="{{ old('department', $professor->department) }}" class="mt-1 block w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">New Password <span class="text-gray-500 text-xs">(optional)</span></label>
                <input type="password" name="password" class="mt-1 block w-full border rounded px-3 py-2">
            </div>
            <div class="flex justify-end space-x-2">
                <a href="{{ route('admin.professors.index') }}" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Update</button>
            </div>
        </form>
    </div>
</x-app-layout>
