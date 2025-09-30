<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('students', 'email')->ignore($studentId),
            ],
            'registration_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'registration_number')->ignore($studentId),
            ],
        ];
    }
}
