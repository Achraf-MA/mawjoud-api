<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_class()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        $response = $this->postJson('/api/admin/classes', [
            'name' => '1A'
        ]);

        $response->assertStatus(201);
    }

    public function test_admin_can_create_subject()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        $response = $this->postJson('/api/admin/subjects', [
            'name' => 'Math'
        ]);

        $response->assertStatus(201);
    }
}