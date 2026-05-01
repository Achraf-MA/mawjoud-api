<?php

namespace Database\Factories;

use App\Models\Justification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Justification>
 */
class JustificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_id' => \App\Models\Attendance::factory(),
            'parent_id' => \App\Models\User::factory()->parent(),
            'file_path' => null,
            'comment' => fake()->sentence(),
            'status' => fake()->randomElement(['pending','accepted','rejected']),
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
