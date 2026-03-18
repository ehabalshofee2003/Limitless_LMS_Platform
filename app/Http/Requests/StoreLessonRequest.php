<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'course_id' => 'required|exists:courses,id',
        'title' => 'required|string|max:255',
        'type' => 'required|in:video,pdf,link,live_session',
        'resource_path' => 'nullable|string', // أو file إذا كنا نرفع ملفات
        'order' => 'nullable|integer',
        'duration_minutes' => 'nullable|integer'
    ];
}
}
