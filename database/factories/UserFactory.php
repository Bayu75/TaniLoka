<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'peran' => 'pengusaha',
            'no_wa' => fake()->unique()->numerify('0819########'),
            'status_verifikasi' => 'belum_diajukan',
            'terverifikasi_pada' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function petani(): static
    {
        return $this->state(fn () => ['peran' => 'petani']);
    }

    public function pengusaha(): static
    {
        return $this->state(fn () => ['peran' => 'pengusaha']);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['peran' => 'admin', 'no_wa' => null]);
    }

    public function terverifikasi(): static
    {
        return $this->state(fn () => [
            'status_verifikasi' => 'terverifikasi',
            'terverifikasi_pada' => now(),
        ]);
    }
}