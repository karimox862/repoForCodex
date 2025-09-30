<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold leading-tight">Modules</h2>
            <a href="{{ route('admin.modules.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded">New Module</a>
        </div>
    </x-slot>

    <div class="bg-white shadow rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Professor</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($modules as $module)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $module->code }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ $module->title }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700">{{ optional($module->professor?->user)->name ?? 'Unassigned' }}</td>
                        <td class="px-4 py-2 text-sm text-right space-x-2">
                            <a href="{{ route('admin.modules.edit', $module) }}" class="text-indigo-600 hover:underline">Manage</a>
                            <a href="{{ route('admin.modules.report', $module) }}" class="text-gray-600 hover:underline">Download Report</a>
                            <form action="{{ route('admin.modules.destroy', $module) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete this module?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-600">No modules found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $modules->links() }}
    </div>
</x-app-layout>
