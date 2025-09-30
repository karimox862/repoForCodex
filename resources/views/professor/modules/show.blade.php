<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold">{{ $module->code }} &middot; {{ $module->title }}</h1>
                <p class="text-gray-600">Enrolled students can be graded with numeric marks or the literal <strong>ABI</strong>.</p>
            </div>
            <a href="{{ route('professor.modules.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Back to modules</a>
        </div>
    </x-slot>

    <div class="bg-white shadow rounded p-6">
        @if ($students->isEmpty())
            <p class="text-gray-600">No students are enrolled in this module yet.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Mark</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($students as $student)
                        @php($existing = $marks[$student->id] ?? null)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $student->name }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $student->registration_number }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $existing?->grade ?? '—' }}</td>
                            <td class="px-4 py-2 text-sm">
                                <form method="POST" action="{{ route('professor.modules.students.mark', [$module, $student]) }}" class="flex space-x-2 items-center">
                                    @csrf
                                    <input type="text" name="grade" value="{{ old('grade', $existing?->grade) }}" class="border rounded px-2 py-1 text-sm" placeholder="e.g. 15 or ABI" required>
                                    <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm">Save</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
