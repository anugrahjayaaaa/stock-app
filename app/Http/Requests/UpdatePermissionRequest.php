<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit permissions');
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission')?->id;
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($permissionId)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The permission name field is required.',
            'name.unique' => 'A permission with this name already exists.',
        ];
    }
}