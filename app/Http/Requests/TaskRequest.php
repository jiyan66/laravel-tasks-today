<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'status' => ['required', 'string', Rule::in(['pending', 'in_progress', 'done'])],
            'task_date' => ['required', 'date_format:Y-m-d'],
            // Archiving is its own authenticated DELETE action, not a form field.
            'is_archived' => ['prohibited'],
        ];
    }
    public function attributes(): array
    {
        return ['task_date' => 'scheduled date'];
    }
}
