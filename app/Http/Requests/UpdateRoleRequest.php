<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit roles');
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id;
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The role name field is required.',
            'name.unique' => 'A role with this name already exists.',
        ];
    }
}