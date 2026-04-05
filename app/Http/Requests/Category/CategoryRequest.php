<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseFormRequest;

class CategoryRequest extends BaseFormRequest
{
    public function baseRules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'api_keyword' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'sort_order'  => 'nullable|integer|min:0',
            'status'      => 'nullable|in:0,1,2',
        ];
    }

    public function createRules(): array
    {
        return [
            'name' => 'unique:categories,name',
        ];
    }

    public function updateRules(): array
    {
        return [
            'id'   => 'required|integer|exists:categories,id',
            'name' => 'unique:categories,name,' . $this->id,
        ];
    }
}
