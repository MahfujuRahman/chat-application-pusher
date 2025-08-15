<?php

namespace App\Modules\Management\UserManagement\User\Actions;

use Illuminate\Support\Facades\Hash;

class UpdateData
{
    static $model = \App\Modules\Management\UserManagement\User\Models\Model::class;

    public static function execute($request, $slug)
    {
        try {

            if (!$data = self::$model::query()->where('slug', $slug)->first()) {
                return messageResponse('Data not found...', $data, 404, 'error');
            }

            $requestData = $request->validated();

             if (isset($requestData['password']) && !empty($requestData['password'])) {
                $requestData['password_in_text'] = 'tpit123' . $requestData['password'] . '321ittp';
                $requestData['password'] = Hash::make($requestData['password']);
            } 

            if ($requestData['password'] === null || $requestData['password'] === '') {
                unset($requestData['password']); // Remove password if not provided
            }

    

            if ($request->hasFile('image')) {
                // Remove previous image if exists
                if (!empty($data->image) && file_exists(public_path($data->image))) {
                    @unlink(public_path($data->image));
                }
                $image = $request->file('image');
                $requestData['image'] = uploader($image, 'uploads/users');
            }

            // ───── link array ────────────────────────────────────
            $payload['social_media'] = collect(
                $request->input('social_media', [])
            )
                ->filter(fn($item) => filled($item['link'] ?? null))
                ->map(fn($item) => [
                    'name' => $item['name'] ?? '',
                    'link' => $item['link'],
                ])
                ->values()
                ->all();
            $requestData = array_merge($requestData, $payload);
            $data->update($requestData);

            return messageResponse('Item updated successfully', $data, 201);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
