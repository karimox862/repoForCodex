<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfessorRequest;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class ProfessorController extends Controller
{
    public function index(): View
    {
        $professors = Professor::with('user')->orderBy(User::select('name')->whereColumn('users.id', 'professors.user_id'))->paginate(10);

        return view('admin.professors.index', compact('professors'));
    }

    public function create(): View
    {
        return view('admin.professors.create');
    }

    public function store(ProfessorRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => User::ROLE_PROFESSOR,
        ]);

        $user->professor()->create([
            'department' => $request->input('department'),
        ]);

        return redirect()->route('admin.professors.index')->with('status', 'Professor created successfully.');
    }

    public function edit(Professor $professor): View
    {
        $professor->load('user');

        return view('admin.professors.edit', compact('professor'));
    }

    public function update(ProfessorRequest $request, Professor $professor): RedirectResponse
    {
        $professor->user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        if ($request->filled('password')) {
            $professor->user->update([
                'password' => Hash::make($request->input('password')),
            ]);
        }

        $professor->update([
            'department' => $request->input('department'),
        ]);

        return redirect()->route('admin.professors.index')->with('status', 'Professor updated successfully.');
    }

    public function destroy(Professor $professor): RedirectResponse
    {
        $professor->user->delete();

        return redirect()->route('admin.professors.index')->with('status', 'Professor removed.');
    }
}
