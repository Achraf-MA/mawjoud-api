<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJustificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_id' => 'required|exists:attendances,id',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'comment' => 'nullable|string|max:1000',
        ];
    }
}