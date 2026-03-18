<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreInstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // السماح لأي مستخدم مسجل الدخول بمحاولة إنشاء مؤسسة
        return true; 
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}