<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    protected $model = \App\Models\Permission::class;

    public function definition()
    {
        $student = Student::inRandomOrder()->first() ?: Student::factory()->create();
        $parent = ParentProfile::where('id', $student->parent_id)->first() ?: ParentProfile::inRandomOrder()->first() ?: ParentProfile::factory()->create();

        return [
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'type' => $this->faker->randomElement(['Sakit','Izin']),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'description' => $this->faker->sentence(),
            'proof_file_path' => null,
            'approval_status' => $this->faker->randomElement(['Pending','Approved','Rejected']),
            'approved_by' => null,
        ];
    }
}
