<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $moduleId = $this->route('module')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('modules', 'code')->ignore($moduleId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'professor_id' => ['nullable', 'exists:professors,id'],
            'students' => ['nullable', 'array'],
            'students.*' => ['integer', 'exists:students,id'],
            'student_labels' => ['nullable', 'array'],
            'student_labels.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
