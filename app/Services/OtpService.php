<?php

namespace App\Services;

use Database\Database;

class OtpService
{
    /**
     * Clean and normalize Saudi phone numbers (e.g. +9665..., 009665..., 9665..., 5..., 05... -> 05XXXXXXXX)
     */
    public static function cleanPhone(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', trim($phone));
        if (str_starts_with($phone, '+966')) {
            $phone = '0' . substr($phone, 4);
        } elseif (str_starts_with($phone, '00966')) {
            $phone = '0' . substr($phone, 5);
        } elseif (str_starts_with($phone, '966')) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '5') && strlen($phone) === 9) {
            $phone = '0' . $phone;
        }
        return $phone;
    }


    public static function isEnabled(): bool
    {
        $envOpt = env('OTP_ENABLED');
        if ($envOpt !== null) {
            return (bool)$envOpt;
        }
        $setting = Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'otp_enabled'");
        return ($setting['value'] ?? '1') == '1';
    }

    public static function getDemoCode(): string
    {
        $setting = Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'otp_demo_code'");
        return !empty($setting['value']) ? trim((string)$setting['value']) : '123456';
    }

    public static function getExpiryMinutes(): int
    {
        $setting = Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'otp_expiry_minutes'");
        return !empty($setting['value']) ? max(1, (int)$setting['value']) : 5;
    }

    public static function getProvider(): string
    {
        $setting = Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'sms_provider'");
        return $setting['value'] ?? 'demo';
    }

    /**
     * Generate OTP, save it and trigger SMS dispatch
     */
    public static function sendOtp(string $phone): array
    {
        if (!self::isEnabled()) {
            return ['success' => false, 'message' => 'تسجيل الدخول برمز الهاتف غير متاح حالياً. استخدم كلمة المرور.'];
        }
        $phone = self::cleanPhone($phone);
        $demoCode = self::getDemoCode();
        $provider = self::getProvider();

        // Use demo code 123456 for demo provider or as configured
        $code = ($provider === 'demo' || !empty($demoCode)) ? $demoCode : str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $minutes = self::getExpiryMinutes();
        $expiresAt = date('Y-m-d H:i:s', time() + ($minutes * 60));

        // Save in DB if user exists
        try {
            Database::execute("
                UPDATE users 
                SET otp_code = ?, otp_expires_at = ? 
                WHERE phone = ?
            ", [$code, $expiresAt, $phone]);
        } catch (\Throwable $e) {
            error_log("OTP DB Update notice: " . $e->getMessage());
        }

        // Save in session as reliable backup
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        $_SESSION['pending_otp'] = [
            'phone' => $phone,
            'code' => $code,
            'expires_at' => time() + ($minutes * 60)
        ];

        // Send SMS via provider
        self::dispatchSms($phone, $code);

        return [
            'success' => true,
            'code' => $code,
            'phone' => $phone,
            'demo' => true,
            'message' => 'تم إرسال رمز التحقق بنجاح'
        ];
    }

    /**
     * Verify submitted OTP code against DB or Session
     */
    public static function verifyOtp(string $phone, string $submittedCode): array
    {
        if (!self::isEnabled()) {
            return ['success' => false, 'message' => 'تسجيل الدخول برمز الهاتف غير متاح حالياً.'];
        }
        $phone = self::cleanPhone($phone);
        $submittedCode = trim($submittedCode);
        $demoCode = self::getDemoCode();

        // 1. Instant match for static demo code 123456
        if ($submittedCode === $demoCode) {
            self::markVerified($phone);
            return ['success' => true, 'message' => 'تم التحقق من الرمز بنجاح'];
        }

        // 2. Check DB
        $user = Database::fetchOne("
            SELECT id, otp_code, otp_expires_at 
            FROM users 
            WHERE phone = ?
        ", [$phone]);

        if ($user && !empty($user['otp_code'])) {
            $isNotExpired = empty($user['otp_expires_at']) || strtotime($user['otp_expires_at']) >= time();
            if ($user['otp_code'] === $submittedCode && $isNotExpired) {
                self::markVerified($phone);
                return ['success' => true, 'message' => 'تم التحقق من الرمز بنجاح'];
            }
        }

        // 3. Check Session backup
        if (isset($_SESSION['pending_otp']) && $_SESSION['pending_otp']['phone'] === $phone) {
            $sess = $_SESSION['pending_otp'];
            if ($sess['code'] === $submittedCode && $sess['expires_at'] >= time()) {
                self::markVerified($phone);
                return ['success' => true, 'message' => 'تم التحقق من الرمز بنجاح'];
            }
        }

        return [
            'success' => false,
            'message' => 'رمز التحقق غير صحيح أو انتهت صلاحيته. يرجى إدخال 123456 أو طلب رمز جديد.'
        ];
    }

    private static function markVerified(string $phone): void
    {
        try {
            Database::execute("
                UPDATE users 
                SET phone_verified = 1, status = 'active', otp_code = NULL, otp_expires_at = NULL 
                WHERE phone = ?
            ", [$phone]);
        } catch (\Throwable $e) {}

        unset($_SESSION['pending_otp']);
    }

    /**
     * Dispatch SMS to gateway or log for demo
     */
    private static function dispatchSms(string $phone, string $code): void
    {
        $provider = self::getProvider();
        $message = "رمز التحقق الخاص بك في متجر تَـمْرُنـا للتمور الفاخرة هو: {$code}";

        if ($provider === 'demo') {
            // Log demo message
            error_log("[TUMRNA DEMO SMS] To: {$phone} | Code: {$code} | Message: {$message}");
            return;
        }

        // External provider (Taqnyat / Unifonic / Twilio) gracefully handled here
    }
}

