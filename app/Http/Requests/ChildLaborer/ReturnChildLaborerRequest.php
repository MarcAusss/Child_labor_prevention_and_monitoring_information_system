<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use Illuminate\Foundation\Http\FormRequest;

class ReturnChildLaborerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $childLaborer = $this->route(
            'childLaborer'
        );

        return $childLaborer instanceof ChildLaborer
            && $this->user()?->can(
                'return',
                $childLaborer
            ) === true;
    }

    public function rules(): array
    {
        return [
            'return_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'return_reason' => trim(
                (string) $this->input(
                    'return_reason'
                )
            ),
        ]);
    }
}