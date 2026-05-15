<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from'            => ['required', 'string', 'max:320'],
            'to'              => ['required', 'array', 'min:1'],
            'to.*'            => ['required', 'email'],
            'cc'              => ['sometimes', 'array'],
            'cc.*'            => ['email'],
            'bcc'             => ['sometimes', 'array'],
            'bcc.*'           => ['email'],
            'subject'         => ['required_without:template_id', 'string', 'max:998'],
            'html'            => ['sometimes', 'string'],
            'text'            => ['sometimes', 'string'],
            'template_id'     => ['sometimes', 'string'],
            'variables'       => ['sometimes', 'array'],
            'tags'            => ['sometimes', 'array'],
            'tags.*'          => ['string', 'max:64'],
            'headers'         => ['sometimes', 'array'],
            'idempotency_key' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
