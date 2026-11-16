<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManagedUserRequest;
use App\Http\Requests\Admin\UpdateManagedUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $search = $request->string('search')->trim()->toString();
        $role = $request->string('role')->trim()->toString();

        $users = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($role, ['spectateur', 'administrateur', 'super_administrateur'], true),
                fn ($query) => $query->where('role', $role),
            )
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at?->toIso8601String(),
                'can' => [
                    'update' => $request->user()->can('update', $user),
                    'delete' => $request->user()->can('delete', $user),
                ],
            ]);

        return Inertia::render('users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search !== '' ? $search : null,
                'role' => in_array($role, ['spectateur', 'administrateur', 'super_administrateur'], true)
                    ? $role
                    : null,
            ],
            'canAssignSuperRole' => $request->user()->can('assignSuperRole', User::class),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('users/Create', [
            'canAssignSuperRole' => $request->user()->can('assignSuperRole', User::class),
        ]);
    }

    public function store(StoreManagedUserRequest $request): RedirectResponse
    {
        User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'role' => $request->string('role')->toString(),
            'email_verified_at' => now(),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Utilisateur créé.',
        ]);

        return redirect()->route('users.index');
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('users/Edit', [
            'managedUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'canAssignSuperRole' => auth()->user()?->can('assignSuperRole', User::class) ?? false,
            'isSelf' => auth()->id() === $user->id,
        ]);
    }

    public function update(UpdateManagedUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'role' => $request->string('role')->toString(),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->string('password')->toString();
        }

        $user->update($data);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Utilisateur mis à jour.',
        ]);

        return redirect()->route('users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Utilisateur supprimé.',
        ]);

        return redirect()->route('users.index');
    }
}
