<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use App\Models\ChildLaborerDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $childLaborer = $this->route(
            'childLaborer'
        );

        return $childLaborer
                instanceof ChildLaborer
            && $this->user()?->can(
                'uploadDocuments',
                $childLaborer
            ) === true;
    }

    public function rules(): array
    {
        return [
            'document_type' => [
                'required',

                Rule::in(
                    ChildLaborerDocument::documentTypes()
                ),
            ],

            'description' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'is_confidential' => [
                'required',
                'boolean',
            ],

            'document' => [
                'required',
                'file',

                /*
                 * Maximum file size: 10 MB.
                 */
                'max:10240',

                /*
                 * Accepted office and image documents.
                 */
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' =>
                'Select a document to upload.',

            'document.max' =>
                'The document must not exceed 10 MB.',

            'document.mimes' =>
                'Only PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, and XLSX files are allowed.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => $this->clean(
                $this->input('description')
            ),

            'is_confidential' => $this->boolean(
                'is_confidential'
            ),
        ]);
    }

    private function clean(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim(
            preg_replace(
                '/\s+/',
                ' ',
                (string) $value
            ) ?? ''
        );

        return $value !== ''
            ? $value
            : null;
    }
}