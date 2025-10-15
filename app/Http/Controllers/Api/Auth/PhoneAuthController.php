<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PhoneAuthController extends Controller
{
    /**
     * Register a new user with phone number.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_phone_login_enabled' => true,
        ]);

        // Generate verification OTP
        $otp = $user->generateOtp('verification');

        // TODO: Send OTP via SMS service

        return response()->json([
            'message' => 'Registration successful. Please verify your phone number.',
            'user' => $user,
        ]);
    }

    /**
     * Login with phone and password.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Request OTP for login.
     */
    public function requestLoginOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'exists:users,phone'],
        ]);

        $user = User::where('phone', $request->phone)->first();
        
        if (!$user->is_phone_login_enabled) {
            throw ValidationException::withMessages([
                'phone' => ['Phone login is not enabled for this account.'],
            ]);
        }

        $otp = $user->generateOtp('login');

        // TODO: Send OTP via SMS service

        return response()->json(['message' => 'OTP sent successfully.']);
    }

    /**
     * Login with OTP.
     */
    public function loginWithOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !$user->verifyOtp($request->otp, 'login')) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP is invalid or has expired.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Verify phone number.
     */
    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !$user->verifyOtp($request->otp, 'verification')) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP is invalid or has expired.'],
            ]);
        }

        $user->phone_verified_at = now();
        $user->save();

        return response()->json([
            'message' => 'Phone number verified successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Request password reset OTP.
     */
    public function requestPasswordResetOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'exists:users,phone'],
        ]);

        $user = User::where('phone', $request->phone)->first();
        $otp = $user->generateOtp('password_reset');

        // TODO: Send OTP via SMS service

        return response()->json(['message' => 'Password reset OTP sent successfully.']);
    }

    /**
     * Reset password with OTP.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !$user->verifyOtp($request->otp, 'password_reset')) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP is invalid or has expired.'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password reset successfully.']);
    }
}