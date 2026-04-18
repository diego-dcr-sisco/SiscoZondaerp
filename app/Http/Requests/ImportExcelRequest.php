<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;

class ImportExcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'excel_file' => 'required|file|mimes:csv|max:5120', // Max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'excel_file.required' => 'Por favor, selecciona un archivo CSV.',
            'excel_file.file' => 'El archivo debe ser un archivo válido.',
            'excel_file.mimetypes' => 'El archivo debe ser un CSV válido.',
            'excel_file.mimes' => 'El archivo debe ser CSV (.csv).',
            'excel_file.max' => 'El archivo no debe exceder 5MB.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $uploadedFile = $this->file('excel_file');

        Log::warning('ImportExcelRequest validation failed', [
            'user_id' => optional($this->user())->id,
            'ip' => $this->ip(),
            'url' => $this->fullUrl(),
            'method' => $this->method(),
            'content_length' => $this->server('CONTENT_LENGTH'),
            'has_excel_file' => $this->hasFile('excel_file'),
            'all_file_keys' => array_keys($this->allFiles()),
            'excel_file_name' => $uploadedFile?->getClientOriginalName(),
            'excel_file_mime' => $uploadedFile?->getMimeType(),
            'excel_file_size' => $uploadedFile?->getSize(),
            'excel_upload_error_code' => $uploadedFile?->getError(),
            'php_upload_max_filesize' => ini_get('upload_max_filesize'),
            'php_post_max_size' => ini_get('post_max_size'),
            'errors' => $validator->errors()->toArray(),
        ]);

        parent::failedValidation($validator);
    }
}
