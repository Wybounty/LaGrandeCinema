<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;

test('spectator cannot access user management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('administrator can access user list', function () {
    $admin = User::factory()->administrateur()->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('users/Index'));
});

test('super administrator can create user with super role', function () {
    $super = User::factory()->superAdministrateur()->create();

    $this->actingAs($super)
        ->post(route('users.store'), [
            'name' => 'Super Cible',
            'email' => 'super2@example.com',
            'password' => 'MotDePasse1!',
            'password_confirmation' => 'MotDePasse1!',
            'role' => 'super_administrateur',
        ])
        ->assertRedirect(route('users.index'));

    expect(User::query()->where('email', 'super2@example.com')->value('role'))
        ->toBe('super_administrateur');
});

test('administrator cannot assign super role', function () {
    $admin = User::factory()->administrateur()->create();

    $this->actingAs($admin)
        ->from(route('users.create'))
        ->post(route('users.store'), [
            'name' => 'Tentative',
            'email' => 'tentative@example.com',
            'password' => 'MotDePasse1!',
            'password_confirmation' => 'MotDePasse1!',
            'role' => 'super_administrateur',
        ])
        ->assertSessionHasErrors('role');
});

test('administrator can update a spectator', function () {
    $admin = User::factory()->administrateur()->create();
    $spectator = User::factory()->create(['name' => 'Jean']);

    $this->actingAs($admin)
        ->patch(route('users.update', $spectator), [
            'name' => 'Jean-Paul',
            'email' => $spectator->email,
            'role' => 'spectateur',
        ])
        ->assertRedirect(route('users.index'));

    expect($spectator->fresh()->name)->toBe('Jean-Paul');
});

test('user cannot change own role', function () {
    $admin = User::factory()->administrateur()->create();

    $this->actingAs($admin)
        ->from(route('users.edit', $admin))
        ->patch(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'spectateur',
        ])
        ->assertSessionHasErrors('role');

    expect($admin->fresh()->role)->toBe('administrateur');
});

test('cannot delete own account', function () {
    $admin = User::factory()->administrateur()->create();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $admin))
        ->assertForbidden();

    expect(User::query()->find($admin->id))->not->toBeNull();
});

test('cannot delete last super administrator', function () {
    $super = User::factory()->superAdministrateur()->create();
    $otherAdmin = User::factory()->administrateur()->create();

    $this->actingAs($otherAdmin)
        ->delete(route('users.destroy', $super))
        ->assertForbidden();
});

test('admin user seeder creates super admin when none exists', function () {
    expect(User::query()->whereIn('role', ['administrateur', 'super_administrateur'])->exists())
        ->toBeFalse();

    $this->seed(AdminUserSeeder::class);

    $admin = User::query()->where('email', 'admin@legrandcinema.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->role)->toBe('super_administrateur');

    $this->seed(AdminUserSeeder::class);

    expect(User::query()->where('email', 'admin@legrandcinema.test')->count())->toBe(1);
});

test('administrator cannot update super administrator', function () {
    $admin = User::factory()->administrateur()->create();
    $super = User::factory()->superAdministrateur()->create();

    $this->actingAs($admin)
        ->get(route('users.edit', $super))
        ->assertForbidden();
});
