<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('a user can log in with their email and password', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('correct-password'),
    ]);

    $response = $this->postJson('/api/login', [
        'login' => 'login@example.com',
        'password' => 'correct-password',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'login@example.com')
        ->assertJsonStructure(['data' => ['user' => ['id'], 'token']]);
});

test('a user can log in with their name and cannot log in with an incorrect password', function () {
    User::factory()->create([
        'name' => 'Login Customer',
        'password' => Hash::make('correct-password'),
    ]);

    $this->postJson('/api/login', [
        'login' => 'Login Customer',
        'password' => 'correct-password',
    ])->assertOk();

    $this->postJson('/api/login', [
        'login' => 'Login Customer',
        'password' => 'wrong-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('login');
});

test('login requires both credentials', function () {
    $this->postJson('/api/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['login', 'password']);
});
