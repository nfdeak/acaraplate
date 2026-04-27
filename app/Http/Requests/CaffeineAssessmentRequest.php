<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @codeCoverageIgnore
 */
final class CaffeineAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'height_cm' => ['required', 'integer', 'min:90', 'max:230'],
            'sensitivity' => ['required', 'string', Rule::in(['low', 'normal', 'high'])],
            'context' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
