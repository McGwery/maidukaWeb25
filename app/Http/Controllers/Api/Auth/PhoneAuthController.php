<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\OtpType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OtpVerificationRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\AuthUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class PhoneAuthController extends Controller
{
    /**
     * Register a new user with phone number.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Validation handled by RegisterRequest
        $request->validated();
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
            'success' => true,
            'code' => Response::HTTP_CREATED,
            'message' => 'Registration successful. Please verify your phone number.',
            'data' => [
                'user' => new AuthUserResource($user),
                'meta' => [
                    'requiresVerification' => true,
                    'verificationMethod' => 'otp'
                ]
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Login with phone and password.
     */
    public function loginWithPassword(Request $request): JsonResponse
    {
        // Validation handled by LoginRequest
        $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->phone)
            ->with(['activeShop.shop' => function($query) {
                $query->with('owner');
            }])
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'credentials' => ['Invalid phone number or password']
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Login successful',
            'data' => [
                'token' => [
                    'accessToken' => $token,
                    'tokenType' => 'Bearer'
                ],
                'user' => new AuthUserResource($user)
            ]
        ]);
    }

    /**
     * Request OTP for login.
     */
    public function requestLoginOtp(Request $request): JsonResponse
    {
        // Validation handled by OtpVerificationRequest
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user->is_phone_login_enabled) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'Phone login is not enabled for this account.',
                'errors' => [
                    'phone' => ['OTP login is disabled for this account']
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        $otp = $user->generateOtp(OtpType::LOGIN);

        // TODO: Send OTP via SMS service

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'OTP sent successfully',
            'data' => [
                'otpExpiresIn' => $otp->expires_at->diffInSeconds(now()),
                'phone' => $user->phone
            ]
        ]);
    }

    /**
     * Login with OTP.
     */
    public function loginWithOtp(Request $request): JsonResponse
    {

        // Validation handled by OtpVerificationRequest
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->phone)
            ->with(['activeShop.shop' => function($query) {
                $query->with('owner');
            }])
            ->first();

        if (!$user || !$user->verifyOtp($request->otp, OtpType::LOGIN)) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Invalid or expired OTP',
                'errors' => [
                    'otp' => ['The OTP is invalid or has expired']
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Login successful',
            'data' => [
                'token' => [
                    'accessToken' => $token,
                    'tokenType' => 'Bearer'
                ],
                'user' => new AuthUserResource($user)
            ]
        ]);
    }

    /**
     * Verify phone number.
     */
    public function verifyPhone(OtpVerificationRequest $request): JsonResponse
    {
        // Validation handled by OtpVerificationRequest
        $request->validated();

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !$user->verifyOtp($request->otp, OtpType::VERIFICATION)) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Invalid or expired OTP',
                'errors' => [
                    'otp' => ['The OTP is invalid or has expired']
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->phone_verified_at = now();
        $user->save();
        $token = $user->createToken('auth_token')->plainTextToken;
        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Phone number verified successfully',
            'data' => [
                'token' => [
                    'accessToken' => $token,
                    'tokenType' => 'Bearer'
                ],
                'user' => new AuthUserResource($user)
            ]
        ]);
    }

    /**
     * Request password reset OTP.
     */
    public function requestPasswordResetOtp(ResetPasswordRequest $request): JsonResponse
    {
        // Validation handled by ResetPasswordRequest
        $request->validated();

        $user = User::where('phone', $request->phone)->first();
        $otp = $user->generateOtp(OtpType::PASSWORD_RESET);

        // TODO: Send OTP via SMS service

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Password reset OTP sent successfully',
            'data' => [
                'otpExpiresIn' => $otp->expires_at->diffInSeconds(now()),
                'phone' => $user->phone
            ]
        ]);
    }

    /**
     * Reset password with OTP.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        // Validation handled by ResetPasswordRequest

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !$user->verifyOtp($request->otp, OtpType::PASSWORD_RESET)) {
            return new JsonResponse([
                'success' => false,
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Invalid or expired OTP',
                'errors' => [
                    'otp' => ['The OTP is invalid or has expired']
                ]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Password reset successfully',
            'data' => [
                'user' => new AuthUserResource($user)
            ]
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return new JsonResponse([
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Successfully logged out'
        ]);
    }
}
