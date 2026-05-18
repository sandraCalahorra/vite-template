<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Controller;

class PasswordResetController extends Controller
{
    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');
        return redirect()->to("/restablecer_acceso?token={$token}&email={$email}");
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $response = Password::sendResetLink(
            $request->only('email')
        );

        if ($response == Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'We have e-mailed your password reset link.'], 200);
        }

        return response()->json(['message' => 'Unable to send reset link.'], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'token' => 'required',
        ]);

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset successfully.'], 200);
        }

        // Handle expired or invalid token
        if ($response == Password::INVALID_TOKEN) {
            return response()->json(['message' => 'The password reset link has expired. Please request a new one.'], 400);
        }
        return response()->json(['message' => 'Failed to reset password.'], 400);
    }
}
