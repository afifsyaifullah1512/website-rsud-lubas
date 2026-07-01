<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Polyclinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory untuk {@see Polyclinic}. Slug di-generate idempoten dari
 * `name + uniqid` agar test paralel/berulang tidak menabrak unique index.
 *
 * @extends Factory<Polyclinic>
 */
class PolyclinicFactory extends Factory
{
    protected $model = Polyclinic::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Poliklinik Anak',
            'Poliklinik Penyakit Dalam',
            'Poliklinik Bedah',
            'Poliklinik Mata',
            'Poliklinik Gigi',
            'Poliklinik Kandungan',
            'Poliklinik Saraf',
        ]).' '.$this->faker->unique()->numberBetween(100, 9999);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'description' => $this->faker->paragraph(),
            'icon' => null,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 99),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
