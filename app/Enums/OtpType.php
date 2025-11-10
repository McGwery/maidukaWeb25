<?php

namespace App\Enums;

enum OtpType: string
{
    case VERIFICATION = 'verification';
    case LOGIN = 'login';
    case PASSWORD_RESET = 'password_reset';

    /**
     * Get the expiration time in minutes for each OTP type
     */
    public function expirationMinutes(): int
    {
        return match($this) {
            self::VERIFICATION => 10,
            self::LOGIN => 5,
            self::PASSWORD_RESET => 15,
        };
    }

    /**
     * Get a human-readable name for the OTP type
     */
    public function label(): string
    {
        return match($this) {
            self::VERIFICATION => 'Phone Verification',
            self::LOGIN => 'Login',
            self::PASSWORD_RESET => 'Password Reset',
        };
    }
}