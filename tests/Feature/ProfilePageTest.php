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
