<?php

namespace App\Modules\Management\Message\Validations;

use Illuminate\Foundation\Http\FormRequest;

class CreateGroupChatValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'required|integer|exists:users,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Group name is required',
            'name.string' => 'Group name must be a string',
            'name.max' => 'Group name cannot exceed 255 characters',
            'participant_ids.required' => 'At least one participant is required',
            'participant_ids.array' => 'Participants must be an array',
            'participant_ids.min' => 'At least one participant is required',
            'participant_ids.*.required' => 'Participant ID is required',
            'participant_ids.*.integer' => 'Participant ID must be an integer',
            'participant_ids.*.exists' => 'Selected participant does not exist',
        ];
    }
}
