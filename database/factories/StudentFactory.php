<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => 'STU-' . $this->faker->unique()->numerify('#####'),
            'name'       => $this->faker->name(),
            'course'     => $this->faker->randomElement([
                'BS Computer Science',
                'BS Information Technology',
                'BS Civil Engineering',
                'BS Nursing',
                'BS Education',
                'BS Accountancy',
                'BS Architecture',
                'BS Psychology',
            ]),
            'year_level' => $this->faker->numberBetween(1, 6),
            'email'      => $this->faker->unique()->safeEmail(),
            'grade'      => $this->faker->randomFloat(2, 60, 100),
        ];
    }
}