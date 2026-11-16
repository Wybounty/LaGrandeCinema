<?php

namespace App\Http\Requests\Admin;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagedUserRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => $this->passwordRules(),
            'role' => ['required', Rule::in($this->allowedRoles())],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('role') === 'super_administrateur'
                && ! $this->user()?->isSuperAdministrateur()) {
                $validator->errors()->add('role', 'Seul un super administrateur peut attribuer ce rôle.');
            }
        });
    }

    /** @return list<string> */
    protected function allowedRoles(): array
    {
        if ($this->user()?->isSuperAdministrateur()) {
            return ['spectateur', 'administrateur', 'super_administrateur'];
        }

        return ['spectateur', 'administrateur'];
    }
}
