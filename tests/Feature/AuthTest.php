<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'teacher'
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);
    }

    public function test_user_can_login()
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'teacher'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@test.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_user_can_logout()
    {
        $user = \App\Models\User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200);
    }
}