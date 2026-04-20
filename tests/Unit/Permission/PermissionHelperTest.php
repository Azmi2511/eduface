<?php
namespace Tests\Unit\Permission;

use Tests\TestCase;

class PermissionHelperTest extends TestCase
{
    /** @test */
    public function it_translates_day_names_correctly()
    {
        // Asumsi fungsi ini ada di helper atau controller
        $this->assertEquals('Senin', translateDay('Monday'));
        $this->assertEquals('Jumat', translateDay('Friday'));
    }

    /** @test */
    public function it_syncs_permission_with_attendance()
    {
        // Mocking atau simulasi proses sinkronisasi
        $permission = \App\Models\Permission::factory()->create(['status' => 'approved']);
        
        // Panggil fungsi sync
        syncWithAttendance($permission);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $permission->user_id,
            'status' => 'permission'
        ]);
    }
}