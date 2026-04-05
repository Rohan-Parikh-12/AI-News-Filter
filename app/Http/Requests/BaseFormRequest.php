<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Shared rules for both create and update
    public function baseRules(): array
    {
        return [];
    }

    // Rules only for create (no id)
    public function createRules(): array
    {
        return [];
    }

    // Rules only for update (id present)
    public function updateRules(): array
    {
        return [];
    }

    public function rules(): array
    {
        $base = $this->baseRules();

        if ($this->filled('id')) {
            return array_merge($base, $this->updateRules());
        }

        return array_merge($base, $this->createRules());
    }
}
