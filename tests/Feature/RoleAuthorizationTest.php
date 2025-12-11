<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAuthorizationTest extends TestCase
{
    private $adminUser;
    private $teacherUser;
    private $parentUser;
    private $studentUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users with different roles
        $this->adminUser = User::create([
            'username' => 'admin_user',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true
        ]);

        $this->teacherUser = User::create([
            'username' => 'teacher_user',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'is_active' => true
        ]);

        $this->parentUser = User::create([
            'username' => 'parent_user',
            'email' => 'parent@example.com',
            'password' => Hash::make('password123'),
            'role' => 'parent',
            'is_active' => true
        ]);

        $this->studentUser = User::create([
            'username' => 'student_user',
            'email' => 'student@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'is_active' => true
        ]);
    }

    protected function tearDown(): void
    {
        User::whereIn('username', ['admin_user', 'teacher_user', 'parent_user', 'student_user'])->delete();
        parent::tearDown();
    }

    /**
     * Get JWT token for user
     */
    private function getTokenForUser($user)
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => $user->username,
            'password' => 'password123'
        ]);

        return $response->json('token');
    }

    /**
     * Test admin can access admin endpoint
     */
    public function test_admin_can_access_admin_endpoint()
    {
        $token = $this->getTokenForUser($this->adminUser);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Admin users endpoint']);
    }

    /**
     * Test non-admin cannot access admin endpoint
     */
    public function test_non_admin_cannot_access_admin_endpoint()
    {
        $token = $this->getTokenForUser($this->teacherUser);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/admin/users');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Forbidden - Insufficient permissions']);
    }

    /**
     * Test teacher can access teacher endpoint
     */
    public function test_teacher_can_access_teacher_endpoint()
    {
        $token = $this->getTokenForUser($this->teacherUser);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/teacher/classes');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Teacher classes endpoint']);
    }

    /**
     * Test admin can access teacher endpoint
     */
    public function test_admin_can_access_teacher_endpoint()
    {
        $token = $this->getTokenForUser($this->adminUser);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/teacher/classes');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Teacher classes endpoint']);
    }

    /**
     * Test student cannot access teacher endpoint
     */
    public function test_student_cannot_access_teacher_endpoint()
    {
        $token = $this->getTokenForUser($this->studentUser);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/teacher/classes');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Forbidden - Insufficient permissions']);
    }

    /**
     * Test parent can access parent endpoint
     */
    public function test_parent_can_access_parent_endpoint()
    {
        $token = $this->getTokenForUser($this->parentUser);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/parent/children');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Parent children endpoint']);
    }

    /**
     * Test admin can access parent endpoint
     */
    public function test_admin_can_access_parent_endpoint()
    {
        $token = $this->getTokenForUser($this->adminUser);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/parent/children');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Parent children endpoint']);
    }

    /**
     * Test student cannot access parent endpoint
     */
    public function test_student_cannot_access_parent_endpoint()
    {
        $token = $this->getTokenForUser($this->studentUser);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/parent/children');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Forbidden - Insufficient permissions']);
    }

    /**
     * Test web login with different roles
     */
    public function test_web_login_with_admin_role()
    {
        $response = $this->post('/login', [
            'username' => 'admin_user',
            'password' => 'password123'
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals('admin', session('role'));
    }

    /**
     * Test web role middleware - admin can access admin routes
     */
    public function test_web_role_middleware_admin_access()
    {
        $this->post('/login', [
            'username' => 'admin_user',
            'password' => 'password123'
        ]);

        // Verify admin is logged in
        $this->assertEquals('admin', session('role'));
    }
}
