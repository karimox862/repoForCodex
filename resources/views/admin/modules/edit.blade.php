<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight text-gray-800">Edit Module</h1>
    </x-slot>

    @if (session('status'))
        <div class="max-w-4xl mx-auto mb-6">
            <div class="rounded border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('status') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="max-w-4xl mx-auto mb-6">
            <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <p class="font-semibold">There were some problems with your submission:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="max-w-4xl mx-auto bg-white shadow rounded p-6 mb-6">
        <form method="POST" action="{{ route('admin.modules.update', $module) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Code</label>
                <input type="text" name="code" value="{{ old('code', $module->code) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" value="{{ old('title', $module->title) }}" class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Professor</label>
                <select name="professor_id" class="mt-1 block w-full border rounded px-3 py-2">
                    <option value="">-- Unassigned --</option>
                    @foreach ($professors as $professor)
                        <option value="{{ $professor->id }}" @selected(old('professor_id', $module->professor_id) == $professor->id)>{{ $professor->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Enrolled Students</label>
                @php($selectedStudents = collect(old('students', $module->students->pluck('id')->all())))
                <select name="students[]" multiple size="8" class="mt-1 block w-full border rounded px-3 py-2">
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected($selectedStudents->contains($student->id))>{{ trim($student->first_name . ' ' . $student->last_name) ?: $student->name }} ({{ $student->apogee_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end space-x-2">
                <a href="{{ route('admin.modules.index') }}" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Update</button>
            </div>
        </form>
    </div>

    <div class="max-w-4xl mx-auto bg-white shadow rounded p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Import Students</h2>
        <form method="POST"
            action="{{ route('admin.modules.students.import', $module) }}"
            enctype="multipart/form-data"
            class="space-y-4">
            @csrf
            <div>
                <label for="module-import-file" class="block text-sm font-medium text-gray-700">Upload roster</label>
                <input type="file"
                    name="file"
                    id="module-import-file"
                    accept=".xlsx,.csv"
                    required
                    class="mt-1 block w-full text-sm text-gray-700">
                <p class="mt-1 text-xs text-gray-500">Upload an XLSX or CSV file starting at row 18. Columns A–D should contain the apogée code, last name, first name, and birth date.</p>
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Import students</button>
            </div>
        </form>

        @if (session('import_summary'))
            @php($summary = session('import_summary'))
            <div class="mt-4 rounded border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                <p class="font-medium">Last import summary</p>
                <ul class="mt-2 space-y-1">
                    <li><span class="font-semibold">Processed:</span> {{ $summary['processed'] }}</li>
                    <li><span class="font-semibold">Created:</span> {{ $summary['created'] }}</li>
                    <li><span class="font-semibold">Updated:</span> {{ $summary['updated'] }}</li>
                    <li><span class="font-semibold">Linked:</span> {{ $summary['attached'] }}</li>
                </ul>

                @if (!empty($summary['errors'] ?? []))
                    <div class="mt-3">
                        <p class="font-semibold text-red-700">Issues</p>
                        <ul class="mt-1 list-disc space-y-1 pl-5 text-red-700">
                            @foreach ($summary['errors'] as $importError)
                                <li>{{ $importError }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="max-w-4xl mx-auto bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Recorded Marks</h2>
            <a href="{{ route('admin.modules.report', $module) }}" class="text-sm text-indigo-600 hover:underline">Download CSV report</a>
        </div>
        @php($marks = $module->marks()->with('student')->orderBy('created_at', 'desc')->get())
        @if ($marks->isEmpty())
            <p class="text-gray-600">No marks have been submitted yet.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Re-check Requested</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($marks as $mark)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ trim($mark->student->first_name . ' ' . $mark->student->last_name) ?: $mark->student->name }} ({{ $mark->student->apogee_code }})</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $mark->grade }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ optional($mark->recheck_requested_at)?->diffForHumans() ?? 'No' }}</td>
                            <td class="px-4 py-2 text-sm text-right">
                                <form action="{{ route('admin.marks.recheck', $mark) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-indigo-600 hover:underline">Request Re-check</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
