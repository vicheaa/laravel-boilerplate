<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if the authenticated user has admin role
        return Auth::check() && Auth::user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'role_id'       => 'nullable|exists:roles,id',
        ];
    }

    /**
     * Get custom error messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'     => 'The name field is required.',
            'email.required'    => 'The email field is required.',
            'email.email'       => 'Please provide a valid email address.',
            'email.unique'      => 'This email address is already registered.',
            'password.required' => 'The password field is required.',
            'password.min'      => 'The password must be at least 6 characters.',
            'role_id.exists'    => 'The selected role is invalid.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        abort(403, 'You must have admin role to create users.');
    }
}
