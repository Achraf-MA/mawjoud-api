<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Class ' . fake()->unique()->numberBetween(1, 20),
        ];
    }
}
