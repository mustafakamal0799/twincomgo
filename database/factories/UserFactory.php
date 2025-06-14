<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            // 'email_verified_at' => now(), // kolom ini dihapus karena tidak ada di database
            'password' => bcrypt('password'), // password default
            'remember_token' => Str::random(10),
            'status' => 'karyawan',
        ];
    }
}
