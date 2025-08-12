<?php

namespace App\Modules\Management\UserManagement\User\Validations;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class DataStoreValidation extends FormRequest
{
    /**
     * Determine if the  is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /**
     * validateError to make this request.
     */
    public function validateError($data)
    {
        $errorPayload =  $data->getMessages();
        return response(['status' => 'validation_error', 'errors' => $errorPayload], 422);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->validateError($validator->errors()));
        if ($this->wantsJson() || $this->ajax()) {
            throw new HttpResponseException($this->validateError($validator->errors()));
        }
        parent::failedValidation($validator);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|sometimes',
            'email' => 'required|sometimes',
            'image' => 'required|sometimes',
            'role_id' => 'required|sometimes',
            'phone_number' => 'sometimes',
            'address' => 'sometimes',
            'remember_token' => 'sometimes',
            'social_media' => 'sometimes',
            'present_address' => 'sometimes',
            'permanent_address' => 'sometimes',
            'nid' => 'sometimes',
            'join_date' => 'sometimes',
            'salary' => 'sometimes',
            'can_login' => 'sometimes',
            'description' => 'sometimes',
            'designation' => 'sometimes',
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];

        // Only require password if slug is not present (create mode)
        if (!$this->route('slug')) {
            $rules['password'] = 'required|min:6';
        } else {
            // On update, password is optional and not validated if not present
            $rules['password'] = 'nullable';
        }

        return $rules;
    }
}
