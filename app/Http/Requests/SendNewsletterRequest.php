<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'body' => ['required', 'string', 'max:20000'],
        ];
    }
}
