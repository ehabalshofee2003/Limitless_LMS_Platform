<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // هنا يمكن التحقق مما إذا كان المستخدم يملك المؤسسة،
        // لكننا نفضل عمل التحقق في Service Layer، لذا نجعلها true هنا.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}