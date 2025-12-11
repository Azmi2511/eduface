<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    private $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->testUser = User::create([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'is_active' => true
        ]);
    }

    protected function tearDown(): void
    {
        User::where('username', 'testuser')->delete();
        parent::tearDown();
    }

    /**
     * Test successful login with valid credentials
     */
    public function test_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'username',
                    'email',
                    'role'
                ]
            ])
            ->assertJson([
                'message' => 'Login successful',
                'user' => [
                    'username' => 'testuser',
                    'email' => 'test@example.com',
                    'role' => 'student'
                ]
            ]);
    }

    /**
     * Test login with invalid username
     */
    public function test_login_with_invalid_username()
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'invaliduser',
            'password' => 'password123'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Credentials not valid'
            ]);
    }

    /**
     * Test login with invalid password
     */
    public function test_login_with_invalid_password()
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Credentials not valid'
            ]);
    }

    /**
     * Test login without username
     */
    public function test_login_without_username()
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123'
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test login without password
     */
    public function test_login_without_password()
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'testuser'
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test logout with valid token
     */
    public function test_logout_with_valid_token()
    {
        // First login to get token
        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('token');

        // Then logout using token
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful'
            ]);
    }

    /**
     * Test logout without token
     */
    public function test_logout_without_token()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Token not provided'
            ]);
    }

    /**
     * Test access user endpoint with valid token
     */
    public function test_access_user_endpoint_with_valid_token()
    {
        // Login first
        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('token');

        // Access protected endpoint
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user_id',
                'username',
                'role',
                'email'
            ]);
    }

    /**
     * Test access user endpoint without token
     */
    public function test_access_user_endpoint_without_token()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Token not provided'
            ]);
    }

    /**
     * Test access user endpoint with invalid token
     */
    public function test_access_user_endpoint_with_invalid_token()
    {
        $response = $this->withHeader('Authorization', 'Bearer invalidtoken')
            ->getJson('/api/user');

        $response->assertStatus(401);
    }

    /**
     * Test token contains correct user data
     */
    public function test_token_contains_correct_user_data()
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'testuser',
            'password' => 'password123'
        ]);

        $token = $response->json('token');

        // Access user endpoint with token and verify data
        $userResponse = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/user');

        $userResponse->assertStatus(200)
            ->assertJson([
                'username' => 'testuser',
                'email' => 'test@example.com',
                'role' => 'student',
                'user_id' => $this->testUser->id
            ]);
    }
}
