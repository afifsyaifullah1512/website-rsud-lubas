<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Enums\Day;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validasi parameter listing jadwal dokter.
 *
 * Requirement 4.6, 4.7.
 */
class ScheduleIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string,array<int,mixed>>
     */
    public function rules(): array
    {
        return [
            'polyclinic_id' => ['nullable', 'integer', 'exists:polyclinics,id'],
            'day' => ['nullable', 'string', new Enum(Day::class)],
            'q' => ['nullable', 'string', 'min:2', 'max:60'],
        ];
    }
}
