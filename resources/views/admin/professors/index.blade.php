<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold leading-tight">Professors</h2>
            <a href="{{ route('admin.professors.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded">New Professor</a>
        </div>
    </x-slot>

    <div class="bg-white shadow rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($professors as $professor)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $professor->user->name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $professor->user->email }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $professor->department ?? '—' }}</td>
                        <td class="px-4 py-2 text-sm text-right space-x-2">
                            <a href="{{ route('admin.professors.edit', $professor) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.professors.destroy', $professor) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete this professor?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-sm text-gray-600 text-center">No professors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $professors->links() }}
    </div>
</x-app-layout>
