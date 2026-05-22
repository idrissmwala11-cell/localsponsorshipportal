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
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
            'role' => User::ROLE_USER,
            'project_name' => User::PROJECT_COMPASSION,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'approved_at' => now(),
            'approved_by' => null,
            'admin_onboarded_at' => null,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function unapproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function admin(?string $centerId = 'TZ0001'): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
            'center_id' => $centerId,
            'cluster_name' => null,
            'project_name' => User::defaultPortalTitle(),
            'admin_onboarded_at' => now(),
        ]);
    }

    public function officialAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_OFFICIAL_ADMIN,
            'center_id' => null,
            'cluster_name' => null,
            'project_name' => User::defaultPortalTitle(),
            'admin_onboarded_at' => now(),
        ]);
    }
}
