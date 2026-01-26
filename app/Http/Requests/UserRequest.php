<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Puedes establecer aquí la lógica de autorización si es necesario
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'work_department_id' => 'nullable|exists:work_department,id',
            'status_id' => 'nullable|exists:status,id',
            'role_id' => 'nullable|exists:simple_role,id',
            'type_id' => 'required|exists:usertype,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:user,email',
        ];
    }
}
