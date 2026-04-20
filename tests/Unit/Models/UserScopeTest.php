<?php
namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserScopeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_only_teachers()
    {
        User::factory()->create(['role' => 'teacher']);
        User::factory()->create(['role' => 'student']);

        $this->assertCount(1, User::teacher()->get());
    }
}