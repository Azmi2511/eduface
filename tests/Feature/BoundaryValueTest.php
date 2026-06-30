<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User;

class BoundaryValueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Password registrasi 7 karakter (Data gagal diinput)
     */
    public function test_registration_password_7_characters()
    {
        $response = $this->postJson('/register/send-otp', [
            'name' => 'Budi Santoso',
            'email' => 'budi1@mail.com',
            'password' => 'Pass123', // 7 karakter
            'password_confirmation' => 'Pass123',
            'role' => 'student',
            'gender' => 'L',
            'class_id' => 1
        ]);

        // Ekspektasi gagal karena validasi minimum 8 karakter
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /**
     * 2. Password registrasi 8 karakter (Data berhasil diinput)
     */
    public function test_registration_password_8_characters()
    {
        $response = $this->postJson('/register/send-otp', [
            'name' => 'Budi Santoso',
            'email' => 'budi2@mail.com',
            'password' => 'Pass1234', // 8 karakter
            'password_confirmation' => 'Pass1234',
            'role' => 'student',
            'gender' => 'L',
            'class_id' => 1
        ]);

        // Ekspektasi berhasil lolos validasi, JSON mengembalikan status success
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }

    /**
     * 3. Nama registrasi 255 karakter (Data berhasil diinput)
     */
    public function test_registration_name_255_characters()
    {
        $name255 = Str::random(255); // String acak 255 karakter

        $response = $this->postJson('/register/send-otp', [
            'name' => $name255,
            'email' => 'budi3@mail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'gender' => 'L',
            'class_id' => 1
        ]);

        // Ekspektasi berhasil karena string masih masuk batas maksimum
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }

    /**
     * 4. Nama registrasi 256 karakter (Data gagal diinput)
     */
    public function test_registration_name_256_characters()
    {
        $name256 = Str::random(256); // String acak 256 karakter

        $response = $this->postJson('/register/send-otp', [
            'name' => $name256,
            'email' => 'budi4@mail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'gender' => 'L',
            'class_id' => 1
        ]);

        // Ekspektasi gagal karena melewati batas maksimum 255
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /**
     * 5. Kode OTP 5 digit (12345) - (Data gagal diverifikasi)
     */
    public function test_otp_code_5_digits()
    {
        $user = User::factory()->create(['email' => 'otp_user5@mail.com']);
        Cache::put('otp_' . $user->email, '123456', 300);

        $response = $this->postJson('/password/verify-code', [
            'email' => $user->email,
            'code' => '12345' // 5 karakter
        ]);

        // Gagal karena Laravel form validation untuk digits:6
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['code']);
    }

    /**
     * 6. Kode OTP 6 digit (123456) - (Data berhasil diverifikasi)
     */
    public function test_otp_code_6_digits()
    {
        $user = User::factory()->create(['email' => 'otp_user6@mail.com']);
        Cache::put('otp_' . $user->email, '123456', 300);

        $response = $this->postJson('/password/verify-code', [
            'email' => $user->email,
            'code' => '123456' // 6 karakter
        ]);

        // Berhasil memverifikasi OTP
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }

    /**
     * 7. Nomor telepon 20 karakter (Data berhasil diperbarui)
     */
    public function test_update_phone_20_characters()
    {
        $user = User::factory()->create(['phone' => '08123456789']);

        $this->actingAs($user);
        session(['id' => $user->id]);

        $phone20 = str_repeat('1', 20); // String 20 karakter

        $response = $this->patch('/profile', [
            'full_name' => 'Updated Name',
            'email' => $user->email,
            'phone' => $phone20
        ]);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        // Pastikan phone berhasil disimpan di database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => $phone20
        ]);
    }

    /**
     * 8. Nomor telepon 21 karakter (Data gagal diperbarui)
     */
    public function test_update_phone_21_characters()
    {
        $user = User::factory()->create(['phone' => '08123456789']);

        $this->actingAs($user);
        session(['id' => $user->id]);

        $phone21 = str_repeat('1', 21); // String 21 karakter

        $response = $this->patch('/profile', [
            'full_name' => 'Updated Name',
            'email' => $user->email,
            'phone' => $phone21
        ]);

        // Ekspektasi gagal (session dikembalikan dengan pesan error phone)
        $response->assertSessionHasErrors(['phone']);
    }
}
