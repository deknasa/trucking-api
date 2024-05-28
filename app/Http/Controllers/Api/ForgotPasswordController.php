<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\PasswordReset;
use App\Mail\ResetPassword;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(ForgotPasswordRequest $request)
    {
        if (isset($request->check) && $request->check == true) {
            return response([
                'message' => 'user exist'
            ]);
        } else {


            $user = User::where('user', $request->user)->first();
            PasswordReset::where('email', $user->email)->delete();
            $expirationInMinutes = config('app.jwt_exp');

            $payload = [
                'email' => strtolower($user->email),
                'iat' => microtime(true),
                'exp' => microtime(true) + ($expirationInMinutes * 60)
            ];
            // 60 itu detik, jadi 30*60detik = 1800. karna microtime = microdetik
            $token = JWT::encode($payload, config('app.jwt_key'), config('app.jwt_alg'));


            $resetLink = config('app.web_url') . "reset-password/$token";

            $passwordReset = new PasswordReset();
            $passwordReset->email = $user->email;
            $passwordReset->token = $token;
            $passwordReset->timestamps = false;

            if ($passwordReset->save()) {
                Mail::to($user->email)->send(new ResetPassword($resetLink));

                return response([
                    'message' => "LINK RESET PASSWORD AKAN DIKIRIMKAN KE $user->email",
                ]);
            }
        }
    }

    public function resetPassword($token, ResetPasswordRequest $request)
    {
        try {
            $passwordReset = PasswordReset::where('token', $token)->first();

            if ($passwordReset !== null) {
                $payload = JWT::decode($token, new Key(config('app.jwt_key'), config('app.jwt_alg')));

                $user = User::where('email', $payload->email)->first();
                $user->password = Hash::make($request->password);

                if ($passwordReset->where('token', $token)->delete() && $user->save()) {
                    return response([
                        'message' => 'password berhasil diubah'
                    ]);
                }
            } else {
                return response([
                    'message' => 'Invalid token'
                ], 401);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
