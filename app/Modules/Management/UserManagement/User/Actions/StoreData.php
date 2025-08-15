<?php

namespace App\Modules\Management\UserManagement\User\Actions;

use Illuminate\Support\Facades\Hash;

class StoreData
{
    static $model = \App\Modules\Management\UserManagement\User\Models\Model::class;

    public static function execute($request)
    {
        try {
            $requestData = $request->validated();

            // Hash password if present
            if (isset($requestData['password']) && !empty($requestData['password'])) {
                // Store plain password in password_in_text with 'tpit' before and after
                $requestData['password_in_text'] = 'tpit123' . $requestData['password'] . '321ittp';
                // Encrypt the plain password for storage (not hashing)
                
                // Hash password for authentication
                $requestData['password'] = Hash::make($requestData['password']);
            }
   
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $requestData['image'] = uploader($image, 'uploads/users');
            }

         

            // Handle new separated project document links and files
            $links = [];
            if ($request->has('social_media') && is_array($request->social_media)) {
                foreach ($request->social_media as $doc) {
                    if (isset($doc['link'])) {
                        $links[] = [
                            'name' => $doc['name'] ?? '',
                            'link' => $doc['link']
                        ];
                    }
                }
            }
             if (!empty($links)) {
                $requestData['social_media'] = $links;
            }
        
            if ($data = self::$model::query()->create($requestData)) {
                return messageResponse('Item added successfully', $data, 201);
            }
        


        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
