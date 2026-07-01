<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Support\Enums\Day;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorSchedule>
 */
class DoctorScheduleFactory extends Factory
{
    protected $model = DoctorSchedule::class;

    public function definition(): array
    {
        $startHour = $this->faker->numberBetween(7, 14);
        $duration = $this->faker->numberBetween(2, 4);
        $endHour = min($startHour + $duration, 21);

        return [
            'doctor_id' => Doctor::factory(),
            'polyclinic_id' => function (array $attributes) {
                /** @var Doctor $doctor */
                $doctor = Doctor::query()->find($attributes['doctor_id']);

                return $doctor?->polyclinic_id;
            },
            'day' => $this->faker->randomElement(Day::cases())->value,
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:00', $endHour),
            'note' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
