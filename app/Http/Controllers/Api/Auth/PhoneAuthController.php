<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\OtpType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

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
        $otp = $user->generateOtp(OtpType::VERIFICATION);

        // TODO: Send OTP via SMS service

        return new JsonResponse([
            'status' => Response::HTTP_CREATED,
            'message' => 'Registration successful. Please verify your phone number.',
            'data' => ['user' => $user],
        ], Response::HTTP_CREATED);
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
            return $this->responseUnAuthenticated('The provided credentials are incorrect.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return new JsonResponse([
            'status' => Response::HTTP_OK,
            'message' => 'Login successful',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ],
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
            return $this->responseUnAuthorized('Phone login is not enabled for this account.');
        }

        $otp = $user->generateOtp(OtpType::LOGIN);

        // TODO: Send OTP via SMS service

        return new JsonResponse([
            'status' => Response::HTTP_OK,
            'message' => 'OTP sent successfully'
        ]);
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

        if (!$user || !$user->verifyOtp($request->otp, OtpType::LOGIN)) {
            return $this->responseUnprocessable(['otp' => ['The OTP is invalid or has expired.']]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return new JsonResponse([
            'status' => Response::HTTP_OK,
            'message' => 'Login successful',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ],
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

        if (!$user || !$user->verifyOtp($request->otp, OtpType::VERIFICATION)) {
            return $this->responseUnprocessable(['otp' => ['The OTP is invalid or has expired.']]);
        }

        $user->phone_verified_at = now();
        $user->save();

        return new JsonResponse([
            'status' => Response::HTTP_OK,
            'message' => 'Phone number verified successfully',
            'data' => ['user' => $user]
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
        $otp = $user->generateOtp(OtpType::PASSWORD_RESET);

        // TODO: Send OTP via SMS service

        return new JsonResponse([
            'status' => Response::HTTP_OK,
            'message' => 'Password reset OTP sent successfully'
        ]);
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

        if (!$user || !$user->verifyOtp($request->otp, OtpType::PASSWORD_RESET)) {
            return $this->responseUnprocessable(['otp' => ['The OTP is invalid or has expired.']]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return new JsonResponse([
            'status' => Response::HTTP_OK,
            'message' => 'Password reset successfully'
        ]);
    }
}