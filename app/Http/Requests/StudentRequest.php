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

    protected function prepareForValidation(): void
    {
        $firstName = trim((string) $this->input('first_name'));
        $lastName = trim((string) $this->input('last_name'));

        $this->merge([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => trim($firstName . ' ' . $lastName) ?: null,
        ]);
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'apogee_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'apogee_code')->ignore($studentId),
            ],
            'birth_date' => ['nullable', 'date'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('students', 'email')->ignore($studentId),
            ],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
