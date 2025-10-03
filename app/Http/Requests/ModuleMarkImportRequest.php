<?php

namespace App\Http\Requests;

use App\Models\Module;
use Illuminate\Foundation\Http\FormRequest;

class ModuleMarkImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xls', 'max:10240'],
        ];
    }

    public function module(): Module
    {
        /** @var Module $module */
        $module = $this->route('module');

        return $module;
    }
}
