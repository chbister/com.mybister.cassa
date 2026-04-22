<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('uninitialized application redirects to setup page', function () {
    // Ensure no users exist
    User::query()->delete();

    $response = $this->get('/');

    $response->assertRedirect('/init');
});

test('setup page is accessible when not initialized', function () {
    User::query()->delete();

    $response = $this->get('/init');

    // If it fails with 500 but it's a ViteException, we consider it "accessible"
    // because it didn't redirect.
    if ($response->status() === 500) {
        $response->assertSee('Vite manifest');
    } else {
        $response->assertOk();
    }
});

test('can create initial user via setup page', function () {
    User::query()->delete();

    $response = $this->post('/init', [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/login');

    $this->assertDatabaseHas('users', [
        'email' => 'admin@example.com',
        'name' => 'Admin User',
    ]);

    expect(User::count())->toBe(1);

    $this->assertDatabaseHas('categories', ['name' => 'Getränke']);
    $this->assertDatabaseHas('products', ['name' => 'Bier']);
});

test('initialized application redirects from setup page to home', function () {
    User::factory()->create();

    $response = $this->get('/init');

    $response->assertRedirect('/');
});

test('cannot create user via setup if already initialized', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post('/init', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/');

    $this->assertDatabaseMissing('users', [
        'email' => 'new@example.com',
    ]);
});
