<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Polyclinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        $name = 'dr. '.$this->faker->firstName();

        return [
            'polyclinic_id' => Polyclinic::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'photo' => null,
            'specialization' => $this->faker->randomElement([
                'Penyakit Dalam', 'Anak', 'Bedah', 'Mata', 'Gigi', 'Kandungan',
            ]),
            'bio' => $this->faker->paragraph(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
