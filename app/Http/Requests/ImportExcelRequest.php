<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportExcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user() !== null;
    }

    public function rules(): array
    {
        return [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'excel_file.required' => 'Por favor, selecciona un archivo Excel.',
            'excel_file.file' => 'El archivo debe ser un archivo válido.',
            'excel_file.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV.',
            'excel_file.max' => 'El archivo no debe exceder 5MB.',
        ];
    }
}
