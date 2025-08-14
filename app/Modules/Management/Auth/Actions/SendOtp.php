<?php

namespace App\Modules\Management\Auth\Actions;


use App\Modules\Mail\OTPSendMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOtp
{
    static $userModel = \App\Modules\Management\UserManagement\User\Models\Model::class;

    public static function execute($request)
    {
        try {

            $requestData = $request->all();
            $user = self::$userModel::where('email', $requestData['email'])->first();

            if (!$user) {
                return messageResponse('User not found please register', $requestData, 400, 'error');
            }

            $otp = self::generateOTPCode();

            $isExist = DB::table('otp_codes')->where('email', $requestData['email'])->exists();

            if ($isExist) {
                DB::table('otp_codes')->where('email', $requestData['email'])->delete();
            }

            DB::table('otp_codes')->insert([
                'email' => $requestData['email'],
                'otp' => $otp,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            // send or queue the OTP mail with robust error handling
            try {
                $mailable = new OTPSendMail($otp);
                // if queue driver is configured and not sync, queue instead of immediate send
                if (config('queue.default') && config('queue.default') !== 'sync') {
                    Mail::to($requestData['email'])->queue($mailable);
                } else {
                    Mail::to($requestData['email'])->send($mailable);
                }
            } catch (\Exception $e) {
                // Log full exception and detect known quota errors (Gmail 550-5.4.5)
                Log::error('Failed to send OTP mail', [
                    'email' => $requestData['email'],
                    'otp' => $otp,
                    'exception' => $e->getMessage(),
                ]);

                $msg = $e->getMessage();
                if (stripos($msg, '550-5.4.5') !== false || stripos($msg, 'Daily user sending limit exceeded') !== false) {
                    return messageResponse('Email provider quota exceeded, please try again later', [], 503, 'server_error');
                }

                return messageResponse('Failed to send OTP email', [], 500, 'server_error');
            }

            return messageResponse('OTP successfully send', [
                'email' => $requestData['email'],
                'otp' => $otp
            ]);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
    public static function generateOTPCode()
    {
        return rand(100000, 999999);
    }
}
