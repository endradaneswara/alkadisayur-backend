<?php

use App\Models\Kategori;
use App\Models\User;

test('successful api responses use the standard envelope', function () {
    $this->actingAs(User::factory()->create(), 'sanctum')
        ->getJson('/api/profile')
        ->assertOk()
        ->assertJsonStructure(['success', 'message', 'data'])
        ->assertJsonPath('success', true);
});

test('paginated api responses expose data links and pagination metadata at the top level', function () {
    Kategori::factory()->create(['nama_kategori' => 'sayuran']);

    $this->actingAs(User::factory()->create(['role' => 'superAdmin']), 'sanctum')
        ->getJson('/api/kategori')
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'per_page', 'total'],
        ])
        ->assertJsonPath('meta.per_page', 20)
        ->assertJsonPath('meta.total', 1);
});

test('validation errors use the api validation response format', function () {
    $this->postJson('/api/login', [])
        ->assertUnprocessable()
        ->assertJsonStructure(['message', 'errors' => ['login', 'password']]);
});
