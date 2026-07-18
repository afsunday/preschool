<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('team.staff') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $staff = $this->route('user');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff)],
            'password' => ['nullable', 'string', 'min:8'],
            'permissions' => ['array'],
            'permissions.*' => [Rule::exists('permissions', 'name')],
        ];
    }
}
