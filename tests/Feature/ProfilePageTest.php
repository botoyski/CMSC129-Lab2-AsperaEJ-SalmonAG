<?php

use App\Models\User;

test('guests are redirected to login when visiting profile page', function () {
    $response = $this->get('/profile');

    $response->assertRedirect('/login');
});

test('authenticated users can visit the profile page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');

    $response->assertOk();
    $response->assertSee('Profile');
    $response->assertSee('Log Out');
});

test('authenticated users can update their profile details', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $response = $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);

    $response
        ->assertRedirect(route('profile'))
        ->assertSessionHas('status', 'profile-updated');

    $user->refresh();

    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
});

test('email must be unique when updating profile details', function () {
    $currentUser = User::factory()->create();
    $existingUser = User::factory()->create();

    $response = $this->actingAs($currentUser)
        ->from(route('profile'))
        ->patch(route('profile.update'), [
            'name' => $currentUser->name,
            'email' => $existingUser->email,
        ]);

    $response
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('profile'));
});
