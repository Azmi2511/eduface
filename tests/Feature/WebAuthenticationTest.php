<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class WebAuthenticationTest extends TestCase
{
    private $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->testUser = User::create([
            'username' => 'webuser',
            'email' => 'web@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'is_active' => true
        ]);
    }

    protected function tearDown(): void
    {
        User::where('username', 'webuser')->delete();
        parent::tearDown();
    }

    /**
     * Test login page is accessible
     */
    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertViewIs('auth.login');
    }

    /**
     * Test successful login with session
     */
    public function test_successful_login_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'username' => 'webuser',
            'password' => 'password123'
        ]);

        $response->assertRedirect(route('dashboard'));
        
        // Check session has user data
        $this->assertEquals($this->testUser->id, session('user_id'));
        $this->assertEquals('webuser', session('full_name'));
        $this->assertEquals('student', session('role'));
    }

    /**
     * Test login fails with invalid username
     */
    public function test_login_fails_with_invalid_username()
    {
        $response = $this->post('/login', [
            'username' => 'invaliduser',
            'password' => 'password123'
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors('username');
    }

    /**
     * Test login fails with invalid password
     */
    public function test_login_fails_with_invalid_password()
    {
        $response = $this->post('/login', [
            'username' => 'webuser',
            'password' => 'wrongpassword'
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors('username');
    }

    /**
     * Test login validation requires username
     */
    public function test_login_validation_requires_username()
    {
        $response = $this->post('/login', [
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors('username');
    }

    /**
     * Test login validation requires password
     */
    public function test_login_validation_requires_password()
    {
        $response = $this->post('/login', [
            'username' => 'webuser'
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test logout clears session
     */
    public function test_logout_clears_session()
    {
        // Login first
        $this->post('/login', [
            'username' => 'webuser',
            'password' => 'password123'
        ]);

        // Check session is set
        $this->assertEquals($this->testUser->id, session('user_id'));

        // Logout
        $response = $this->post('/logout');

        // Should redirect to login
        $response->assertRedirect(route('login.show'));

        // Session should be cleared
        $this->assertNull(session('user_id'));
    }

    /**
     * Test authenticated user can access dashboard
     */
    public function test_authenticated_user_can_access_dashboard()
    {
        $this->post('/login', [
            'username' => 'webuser',
            'password' => 'password123'
        ]);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertViewIs('dashboard');
    }

    /**
     * Test unauthenticated user cannot access dashboard
     */
    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login.show'));
    }
}
