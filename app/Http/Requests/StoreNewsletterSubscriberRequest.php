<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterSubscriberRequest extends FormRequest
{
    /**
     * The newsletter band renders on every page, next to other forms (e.g. the
     * contact form). Its own error bag keeps their validation errors apart.
     *
     * @var string
     */
    protected $errorBag = 'newsletter';

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
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
