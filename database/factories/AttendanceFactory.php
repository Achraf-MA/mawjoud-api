<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => \App\Models\Student::factory(),
            'teacher_id' => \App\Models\User::factory()->teacher(),
            'class_id' => \App\Models\SchoolClass::factory(),
            'subject_id' => \App\Models\Subject::factory(),
            'date' => fake()->date(),
            'status' => fake()->randomElement(['present','absent','late']),
        ];
    }
}
