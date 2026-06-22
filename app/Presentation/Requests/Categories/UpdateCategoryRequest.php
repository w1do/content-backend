<?php

namespace App\Presentation\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id', 'different:id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', "unique:categories,slug,{$id}"],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
        ];
    }
}
