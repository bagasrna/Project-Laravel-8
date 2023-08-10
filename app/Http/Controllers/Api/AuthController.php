<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use App\Libraries\ResponseBase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
            'phone' => 'required|numeric|starts_with:+62|min:5',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return ResponseBase::error($validator->errors(), 422);

        $otp = rand(1000, 9999);

        try{
            DB::beginTransaction();

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->otp = $otp;
            $user->password = Hash::make($request->password);
            $user->save();
    
            $this->sendWhatsappNotification($otp, $user->phone);
            DB::commit();
            return ResponseBase::success("Berhasil register!", $user);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal merubah data inspection: ' . $e->getMessage());
            return ResponseBase::error('Gagal register!', 409);
        }
    }

    private function sendWhatsappNotification(string $otp, string $recipient)
    {
        $twilio_whatsapp_number = env("TWILIO_WHATSAPP_NUMBER");
        $account_sid = env("TWILIO_ACCOUNT_SID");
        $auth_token = env("TWILIO_AUTH_TOKEN");

        $client = new Client($account_sid, $auth_token);
        $message = "Your registration pin code is $otp";
        return $client->messages->create(
            "whatsapp:$recipient",
            array(
                'from' => "whatsapp:$twilio_whatsapp_number",
                'body' => $message
            )
        );
    }
}
