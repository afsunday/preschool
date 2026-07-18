<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:100000'],
            'audience' => ['required', Rule::in(['all', 'selected'])],
            // Required only when hand-picking recipients.
            'recipients' => ['array', 'required_if:audience,selected'],
            'recipients.*' => ['integer', Rule::exists('newsletter_subscribers', 'id')],
        ];
    }
}
