<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMarkRequest;
use App\Models\Mark;
use App\Models\Module;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller
{
    public function index(Request $request): View
    {
        $professor = $request->user()->professor;

        $modules = $professor?->modules()->withCount('students')->orderBy('title')->get() ?? collect();

        return view('professor.modules.index', compact('modules'));
    }

    public function show(Module $module): View
    {
        Gate::authorize('view', $module);

        $students = $module->students()->orderBy('name')->get();
        $marks = $module->marks()->get()->keyBy('student_id');

        return view('professor.modules.show', compact('module', 'students', 'marks'));
    }

    public function storeMark(StoreMarkRequest $request, Module $module, Student $student): RedirectResponse
    {
        Gate::authorize('grade', $module);

        if (! $module->students()->whereKey($student->id)->exists()) {
            abort(403);
        }

        Mark::updateOrCreate(
            [
                'module_id' => $module->id,
                'student_id' => $student->id,
            ],
            [
                'grade' => $request->input('grade'),
                'recheck_requested_at' => null,
            ],
        );

        return redirect()
            ->route('professor.modules.show', $module)
            ->with('status', 'Mark saved successfully.');
    }
}
