<?php

namespace Database\Factories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->time('H:i:s');
        
        return [
            'class_id' => \App\Models\SchoolClass::factory(),
            'subject_id' => \App\Models\Subject::factory(),
            'teacher_id' => \App\Models\User::factory()->teacher(),
            'day' => fake()->randomElement(['monday','tuesday','wednesday','thursday','friday','saturday']),
            'starts_at' => $start,
            'ends_at' => date('H:i:s', strtotime($start) + 3600),
        ];
    }
}
