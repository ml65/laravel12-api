<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'Password123',
            'gender' => 'male'
        ];

        $response = $this->postJson('/api/registration', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'email',
                    'gender'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'gender' => $userData['gender']
        ]);
    }

    public function test_user_cannot_register_with_invalid_email(): void
    {
        $userData = [
            'email' => 'invalid-email',
            'password' => 'Password123',
            'gender' => 'male'
        ];

        $response = $this->postJson('/api/registration', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_register_with_weak_password(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'weak',
            'gender' => 'male'
        ];

        $response = $this->postJson('/api/registration', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_register_with_invalid_gender(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'Password123',
            'gender' => 'invalid'
        ];

        $response = $this->postJson('/api/registration', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'gender' => 'male'
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'email',
                    'gender'
                ]
            ])
            ->assertJson([
                'user' => [
                    'email' => 'test@example.com',
                    'gender' => 'male'
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }
}
