<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Schedule;

class EquivalencePartitioningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Registrasi dengan email tidak valid (budi-mail)
     */
    public function test_registration_with_invalid_email_format()
    {
        $response = $this->postJson('/register/send-otp', [
            'name' => 'Budi Santoso',
            'email' => 'budi-mail',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'gender' => 'L',
            'class_id' => 1
        ]);

        // Assert validasi gagal untuk email (HTTP 422 Unprocessable Entity)
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * 2. Registrasi dengan role di luar pilihan (superadmin)
     */
    public function test_registration_with_invalid_role()
    {
        $response = $this->postJson('/register/send-otp', [
            'name' => 'Budi Santoso',
            'email' => 'budi@mail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'superadmin',
            'gender' => 'L',
            'class_id' => 1
        ]);

        // Assert validasi gagal untuk role
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['role']);
    }

    /**
     * 3. Verifikasi OTP dengan kode salah (000000)
     */
    public function test_verify_otp_with_incorrect_code()
    {
        $user = User::factory()->create(['email' => 'user@mail.com']);
        // Set OTP valid di cache
        Cache::put('otp_' . $user->email, '123456', 300);

        $response = $this->postJson('/password/verify-code', [
            'email' => $user->email,
            'code' => '000000' // Kode salah
        ]);

        // Controller manual me-return JSON status error dan HTTP 400
        $response->assertStatus(400)
                 ->assertJson([
                     'status' => 'error', 
                     'message' => 'Kode OTP salah atau kedaluwarsa.'
                 ]);
    }

    /**
     * 4. Verifikasi OTP dengan email tidak terdaftar
     */
    public function test_verify_otp_with_unregistered_email()
    {
        $response = $this->postJson('/password/verify-code', [
            'email' => 'ghost@mail.com',
            'code' => '123456'
        ]);

        // Email tidak ada di tabel users, harus gagal validasi 'exists'
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * 5. Update profil dengan phone kosong (null)
     */
    public function test_update_profile_with_empty_phone()
    {
        $user = User::factory()->create(['phone' => '08123456789']);

        // Login sebagai user menggunakan actingAs
        $this->actingAs($user);

        // Simulasi session agar update controller yang bergantung pada session Auth jalan
        session(['id' => $user->id]);

        $response = $this->patch('/profile', [
            'full_name' => 'Updated Name',
            'email' => $user->email,
            'phone' => null // Phone dikosongkan
        ]);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        // Pastikan phone berhasil diubah menjadi null di database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => null
        ]);
    }

    /**
     * 6. Update profil menggunakan email milik user lain
     */
    public function test_update_profile_with_other_user_email()
    {
        $user1 = User::factory()->create(['email' => 'user1@mail.com']);
        $user2 = User::factory()->create(['email' => 'user2@mail.com']);

        $this->actingAs($user1);
        session(['id' => $user1->id]);

        $response = $this->patch('/profile', [
            'full_name' => 'Updated Name',
            'email' => $user2->email, // Menggunakan email user lain yang sudah terdaftar
            'phone' => '08123456789'
        ]);

        // Pastikan gagal karena validasi 'unique'
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * 7. Manajemen jadwal dengan hari tidak valid (Holiday)
     */
    public function test_schedule_management_with_invalid_day()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Menggunakan manual create agar tidak bergantung pada Factory yang mungkin tidak ada
        $schoolClass = SchoolClass::create(['class_name' => '10A']);
        $subject = Subject::create(['subject_name' => 'Matematika']);
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'nip' => '11112222']);

        $response = $this->post('/schedules', [
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Holiday', // Hari tidak valid (hanya Monday-Sunday)
            'start_time' => '08:00',
            'end_time' => '09:00'
        ]);

        $response->assertSessionHasErrors(['day_of_week']);
    }

    /**
     * 8. Manajemen jadwal dengan waktu bentrok
     */
    public function test_schedule_management_with_conflicting_time()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $schoolClass = SchoolClass::create(['class_name' => '10A']);
        $subject1 = Subject::create(['subject_name' => 'Matematika']);
        $subject2 = Subject::create(['subject_name' => 'Fisika']);
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create(['user_id' => $teacherUser->id, 'nip' => '33334444']);

        // Buat jadwal pertama
        Schedule::create([
            'class_id' => $schoolClass->id,
            'subject_id' => $subject1->id,
            'teacher_id' => $teacher->id,
            'day_of_week' => 'Monday',
            'start_time' => '08:00',
            'end_time' => '09:00'
        ]);

        // Percobaan buat jadwal kedua dengan waktu bentrok (overlap)
        $response = $this->post('/schedules', [
            'class_id' => $schoolClass->id,
            'subject_id' => $subject2->id,
            'teacher_id' => $teacher->id, // Guru yang sama mengajar
            'day_of_week' => 'Monday', // Di hari yang sama
            'start_time' => '08:30', // Bentrok dengan 08:00 - 09:00
            'end_time' => '09:30'
        ]);

        // Pastikan error bentrok yang di-set di ScheduleController tertangkap
        $response->assertSessionHasErrors(['conflict']);
    }
}
