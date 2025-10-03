<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModuleRequest;
use App\Models\Module;
use App\Models\Professor;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ModuleController extends Controller
{
    public function index(): View
    {
        $modules = Module::with('professor.user')->orderBy('code')->paginate(10);

        return view('admin.modules.index', compact('modules'));
    }

    public function create(): View
    {
        $professors = Professor::with('user')->get()->sortBy(fn ($professor) => $professor->user->name);
        $students = Student::orderBy('name')->get();

        return view('admin.modules.create', compact('professors', 'students'));
    }

    public function store(ModuleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $studentIds = $data['students'] ?? [];
        $studentLabels = $data['student_labels'] ?? [];
        unset($data['students'], $data['student_labels']);

        $module = Module::create($data);
        $module->students()->sync($this->mapStudentsToLabels($studentIds, $studentLabels));

        return redirect()->route('admin.modules.index')->with('status', 'Module created successfully.');
    }

    public function edit(Module $module): View
    {
        $module->load('students', 'professor.user');
        $professors = Professor::with('user')->get()->sortBy(fn ($professor) => $professor->user->name);
        $students = Student::orderBy('name')->get();

        return view('admin.modules.edit', compact('module', 'professors', 'students'));
    }

    public function update(ModuleRequest $request, Module $module): RedirectResponse
    {
        $data = $request->validated();
        $studentIds = $data['students'] ?? [];
        $studentLabels = $data['student_labels'] ?? [];
        unset($data['students'], $data['student_labels']);

        $module->update($data);
        $module->students()->sync($this->mapStudentsToLabels($studentIds, $studentLabels));

        return redirect()->route('admin.modules.index')->with('status', 'Module updated successfully.');
    }

    /**
     * @param  array<int, int|string>  $studentIds
     * @param  array<int|string, mixed>  $studentLabels
     * @return array<int, array{label: string|null}>
     */
    private function mapStudentsToLabels(array $studentIds, array $studentLabels): array
    {
        $syncData = [];

        foreach ($studentIds as $studentId) {
            $label = $studentLabels[$studentId] ?? null;

            if (is_string($label)) {
                $label = trim($label);
            }

            $syncData[$studentId] = ['label' => $label === '' ? null : $label];
        }

        return $syncData;
    }

    public function destroy(Module $module): RedirectResponse
    {
        $module->delete();

        return redirect()->route('admin.modules.index')->with('status', 'Module removed.');
    }
}
