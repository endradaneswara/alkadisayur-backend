<?php

use App\Models\Kategori;
use App\Models\User;

test('an authenticated user can list categories with pagination metadata', function () {
    Kategori::factory()->create(['nama_kategori' => 'sayuran']);
    $user = User::factory()->create(['role' => 'superAdmin']);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/kategori')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.nama_kategori', 'sayuran')
        ->assertJsonPath('meta.per_page', 20)
        ->assertJsonStructure(['links', 'meta']);
});

test('only a super admin can create a category', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/kategori', ['nama_kategori' => 'sayuran'])
        ->assertForbidden();

    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $this->actingAs($superAdmin, 'sanctum')
        ->postJson('/api/kategori', ['nama_kategori' => 'sayuran'])
        ->assertCreated()
        ->assertJsonPath('data.nama_kategori', 'sayuran');

    $this->assertDatabaseHas('kategori', ['nama_kategori' => 'sayuran']);
});

test('a super admin can list, show, update and delete categories', function () {
    $superAdmin = User::factory()->create(['role' => 'superAdmin']);
    $kategori = Kategori::factory()->create(['nama_kategori' => 'sayuran']);
    $this->actingAs($superAdmin, 'sanctum');

    $this->getJson('/api/kategori')
        ->assertOk()
        ->assertJsonFragment(['id' => $kategori->id]);

    $this->getJson("/api/kategori/{$kategori->id}")
        ->assertOk()
        ->assertJsonPath('data.nama_kategori', 'sayuran');

    $this->putJson("/api/kategori/{$kategori->id}", [
        'nama_kategori' => 'buah',
    ])
        ->assertOk()
        ->assertJsonPath('data.nama_kategori', 'buah');

    $this->assertDatabaseHas('kategori', [
        'id' => $kategori->id,
        'nama_kategori' => 'buah',
    ]);

    $this->deleteJson("/api/kategori/{$kategori->id}")
        ->assertOk();

    $this->assertDatabaseMissing('kategori', ['id' => $kategori->id]);
});
