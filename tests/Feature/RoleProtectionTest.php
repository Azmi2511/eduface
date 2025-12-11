<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleProtectionTest extends TestCase
{
    private $users = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users with all roles
        $roles = ['admin', 'teacher', 'parent', 'student'];
        
        foreach ($roles as $role) {
            $this->users[$role] = User::create([
                'username' => "{$role}_user",
                'email' => "{$role}@example.com",
                'password' => Hash::make('password123'),
                'role' => $role,
                'is_active' => true
            ]);
        }
    }

    protected function tearDown(): void
    {
        User::whereIn('role', ['admin', 'teacher', 'parent', 'student'])->delete();
        parent::tearDown();
    }

    /**
     * Get JWT token for user
     */
    private function getToken($role)
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => "{$role}_user",
            'password' => 'password123'
        ]);

        return $response->json('token');
    }

    /**
     * Test Admin Role Protection
     */
    public function test_admin_endpoint_only_admin_can_access()
    {
        $adminToken = $this->getToken('admin');
        $teacherToken = $this->getToken('teacher');
        $parentToken = $this->getToken('parent');
        $studentToken = $this->getToken('student');

        // Admin dapat akses
        $this->withHeader('Authorization', "Bearer $adminToken")
            ->getJson('/api/admin/users')
            ->assertStatus(200);

        // Teacher tidak dapat akses
        $this->withHeader('Authorization', "Bearer $teacherToken")
            ->getJson('/api/admin/users')
            ->assertStatus(403);

        // Parent tidak dapat akses
        $this->withHeader('Authorization', "Bearer $parentToken")
            ->getJson('/api/admin/users')
            ->assertStatus(403);

        // Student tidak dapat akses
        $this->withHeader('Authorization', "Bearer $studentToken")
            ->getJson('/api/admin/users')
            ->assertStatus(403);
    }

    /**
     * Test Teacher Role Protection
     */
    public function test_teacher_endpoint_only_teacher_and_admin_can_access()
    {
        $adminToken = $this->getToken('admin');
        $teacherToken = $this->getToken('teacher');
        $parentToken = $this->getToken('parent');
        $studentToken = $this->getToken('student');

        // Admin dapat akses
        $this->withHeader('Authorization', "Bearer $adminToken")
            ->getJson('/api/teacher/classes')
            ->assertStatus(200);

        // Teacher dapat akses
        $this->withHeader('Authorization', "Bearer $teacherToken")
            ->getJson('/api/teacher/classes')
            ->assertStatus(200);

        // Parent tidak dapat akses
        $this->withHeader('Authorization', "Bearer $parentToken")
            ->getJson('/api/teacher/classes')
            ->assertStatus(403);

        // Student tidak dapat akses
        $this->withHeader('Authorization', "Bearer $studentToken")
            ->getJson('/api/teacher/classes')
            ->assertStatus(403);
    }

    /**
     * Test Parent Role Protection
     */
    public function test_parent_endpoint_only_parent_and_admin_can_access()
    {
        $adminToken = $this->getToken('admin');
        $teacherToken = $this->getToken('teacher');
        $parentToken = $this->getToken('parent');
        $studentToken = $this->getToken('student');

        // Admin dapat akses
        $this->withHeader('Authorization', "Bearer $adminToken")
            ->getJson('/api/parent/children')
            ->assertStatus(200);

        // Parent dapat akses
        $this->withHeader('Authorization', "Bearer $parentToken")
            ->getJson('/api/parent/children')
            ->assertStatus(200);

        // Teacher tidak dapat akses
        $this->withHeader('Authorization', "Bearer $teacherToken")
            ->getJson('/api/parent/children')
            ->assertStatus(403);

        // Student tidak dapat akses
        $this->withHeader('Authorization', "Bearer $studentToken")
            ->getJson('/api/parent/children')
            ->assertStatus(403);
    }

    /**
     * Test Protected Endpoint Response Format
     */
    public function test_protected_endpoint_forbidden_response_format()
    {
        $studentToken = $this->getToken('student');

        $response = $this->withHeader('Authorization', "Bearer $studentToken")
            ->getJson('/api/admin/users');

        $response->assertStatus(403)
            ->assertJsonStructure(['message'])
            ->assertJson(['message' => 'Forbidden - Insufficient permissions']);
    }

    /**
     * Test User Endpoint (No Role Restriction)
     */
    public function test_user_endpoint_accessible_to_all_authenticated_roles()
    {
        $roles = ['admin', 'teacher', 'parent', 'student'];

        foreach ($roles as $role) {
            $token = $this->getToken($role);

            $response = $this->withHeader('Authorization', "Bearer $token")
                ->getJson('/api/user');

            $response->assertStatus(200)
                ->assertJson([
                    'username' => "{$role}_user",
                    'role' => $role
                ]);
        }
    }

    /**
     * Test Multiple Roles in Single Route
     */
    public function test_multiple_roles_in_route()
    {
        // /api/teacher/classes allows: teacher, admin
        $adminToken = $this->getToken('admin');
        $teacherToken = $this->getToken('teacher');

        // Both should succeed
        $this->withHeader('Authorization', "Bearer $adminToken")
            ->getJson('/api/teacher/classes')
            ->assertStatus(200);

        $this->withHeader('Authorization', "Bearer $teacherToken")
            ->getJson('/api/teacher/classes')
            ->assertStatus(200);
    }

    /**
     * Test Invalid Token Cannot Bypass Role Check
     */
    public function test_invalid_token_cannot_bypass_role_check()
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid.token.here')
            ->getJson('/api/admin/users');

        $response->assertStatus(401);
    }

    /**
     * Test Missing Token Returns Proper Error
     */
    public function test_missing_token_returns_error()
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Token not provided']);
    }

    /**
     * Test Role Case Sensitivity
     */
    public function test_role_protection_is_case_sensitive()
    {
        $studentToken = $this->getToken('student');

        // Token contains role: 'student'
        // Route expects role: 'admin' or 'teacher' or 'parent'
        $response = $this->withHeader('Authorization', "Bearer $studentToken")
            ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    /**
     * Test All Roles Can Access Public Endpoint
     */
    public function test_all_roles_can_access_public_endpoint()
    {
        $roles = ['admin', 'teacher', 'parent', 'student'];

        foreach ($roles as $role) {
            $response = $this->postJson('/api/auth/login', [
                'username' => "{$role}_user",
                'password' => 'password123'
            ]);

            $response->assertStatus(200)
                ->assertJson(['message' => 'Login successful'])
                ->assertJsonStructure(['token']);
        }
    }

    /**
     * Test Permission Matrix
     */
    public function test_complete_permission_matrix()
    {
        $endpoints = [
            '/api/admin/users' => ['admin'],
            '/api/teacher/classes' => ['admin', 'teacher'],
            '/api/parent/children' => ['admin', 'parent'],
        ];

        $roles = ['admin', 'teacher', 'parent', 'student'];

        foreach ($endpoints as $endpoint => $allowedRoles) {
            foreach ($roles as $role) {
                $token = $this->getToken($role);
                $response = $this->withHeader('Authorization', "Bearer $token")
                    ->getJson($endpoint);

                if (in_array($role, $allowedRoles)) {
                    $response->assertStatus(200);
                } else {
                    $response->assertStatus(403);
                }
            }
        }
    }
}
