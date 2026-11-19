<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && $this->user()?->can('update', $user);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $managedUser */
        $managedUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($managedUser->id),
            ],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'role' => ['required', Rule::in($this->allowedRolesFor($managedUser))],
        ];
    }

    /** @return list<string> */
    protected function allowedRolesFor(User $managedUser): array
    {
        if ($this->user()?->isSuperAdministrateur()) {
            return ['spectateur', 'administrateur', 'super_administrateur'];
        }

        if ($managedUser->is($this->user())) {
            return [$managedUser->role];
        }

        return ['spectateur', 'administrateur'];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var User $managedUser */
            $managedUser = $this->route('user');
            $newRole = $this->input('role');

            if ($newRole === 'super_administrateur' && ! $this->user()?->isSuperAdministrateur()) {
                $validator->errors()->add('role', 'Seul un super administrateur peut attribuer ce rôle.');
            }

            if ($managedUser->isSuperAdministrateur()
                && $newRole !== 'super_administrateur'
                && User::query()->where('role', 'super_administrateur')->count() <= 1) {
                $validator->errors()->add('role', 'Impossible de rétrograder le dernier super administrateur.');
            }

            if ($managedUser->is($this->user()) && $newRole !== $this->user()->role) {
                $validator->errors()->add('role', 'Vous ne pouvez pas modifier votre propre rôle.');
            }
        });
    }
}
