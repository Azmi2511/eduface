<?php
namespace Tests\Feature\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UtilityTest extends TestCase
{
    /** @test */
    public function user_can_upload_file_successfully()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/v1/upload-file', [
            'name' => 'avatar',
            'file' => $file,
        ]);

        $response->assertStatus(201);
        Storage::disk('public')->assertExists('uploads/avatar/' . $file->hashName());
    }

    /** @test */
    public function support_route_is_accessible()
    {
        $this->get('/help-support')->assertStatus(200);
    }
}