<?php

namespace App\Http\Requests;

use App\Enums\EmploymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployerEnquiry extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190'],
            'company' => ['nullable', 'string', 'max:160'],
            'role_title' => ['nullable', 'string', 'max:160'],
            'employment_type' => ['nullable', Rule::in(EmploymentType::values())],
            'link' => ['nullable', 'string', 'max:500', 'regex:/^https?:\/\//i'],
            'body' => ['required', 'string', 'min:20', 'max:4000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.min' => 'Tell us a little more — at least a sentence about the role.',
            'link.regex' => 'Use a full http:// or https:// link.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'company' => trim((string) $this->input('company')) ?: null,
            'role_title' => trim((string) $this->input('role_title')) ?: null,
            'link' => trim((string) $this->input('link')) ?: null,
            'employment_type' => $this->input('employment_type') ?: null,
        ]);
    }
}
