<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee('logo.png', false);
    $response->assertSee('aria-label="Open chat"', false);
    $response->assertSee('AI Chat Assistant', false);
    $response->assertSee('AI Chatbot');
    $response->assertSee('aria-label="Close chat"', false);
});
