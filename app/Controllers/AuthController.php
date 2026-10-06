<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Logger;
use App\Core\Security;
use App\Services\OtpService;
use Database\Database;

class AuthController
{
    public function showLogin(Request $request): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user['role_name'] === 'super_admin' || $user['role_name'] === 'store_manager') {
                Response::redirect('/admin');
                return;
            }
            Response::redirect('/profile');
            return;
        }

        $notice = null;
        if ($request->get('required_login')) {
            $notice = 'يرجى تسجيل الدخول أو إنشاء حساب جديد لإتمام طلب التمور الفاخرة (سلتك ومشترياتك محفوظة بالكامل).';
        }

        Response::view('auth/login', [
            'error' => null,
            'notice' => $notice,
            'success' => $request->get('registered') ? 'تم إنشاء حسابك بنجاح! يمكنك تسجيل الدخول الآن.' : null
        ]);
    }

    public function login(Request $request): void
    {
        // 1. Phone + OTP Login
        $loginType = $request->get('login_type');
        if ($loginType === 'otp') {
            if (!OtpService::isEnabled()) {
                Response::view('auth/login', ['error' => 'الدخول برمز الهاتف غير متاح حالياً. استخدم البريد وكلمة المرور.']);
                return;
            }
            $phone = OtpService::cleanPhone((string)$request->get('phone'));
            if (empty($phone) || strlen($phone) < 9) {
                Response::view('auth/login', ['error' => 'يرجى إدخال رقم جوال سعودي صحيح (مثال: 0555123456)']);
                return;
            }

            $user = Database::fetchOne("SELECT * FROM users WHERE phone = ?", [$phone]);
            if (!$user) {
                // Auto register customer seamlessly if phone is new
                $defaultEmail = $phone . '@tamrna.store';
                Database::execute("
                    INSERT INTO users (role_id, name, email, password, phone, status, phone_verified)
                    VALUES (3, ?, ?, ?, ?, 'active', 0)
                ", ['عميل جديد', $defaultEmail, password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT), $phone]);
            }

            $_SESSION['pending_otp_phone'] = $phone;
            OtpService::sendOtp($phone);
            Response::redirect('/verify-otp');
            return;
        }

        // 2. Email / Phone + Password Login
        $identifier = trim((string)($request->get('identity') ?: $request->get('email') ?: $request->get('phone')));
        $password = (string)$request->get('password');

        // Rate limit password login attempts by IP
        $ip = Logger::getClientIp();
        if (!Security::rateLimit("login_attempt_{$ip}", 6, 300)) {
            $retryAfter = Security::rateLimitResetSeconds("login_attempt_{$ip}");
            Logger::security("Rate limit exceeded on password login attempt", ['ip' => $ip, 'identifier' => $identifier]);
            Response::view('auth/login', [
                'error' => "تم حظر محاولات الدخول مؤقتاً لتكرار المحاولات الخاطئة. يرجى الانتظار {$retryAfter} ثانية.",
                'email' => $identifier
            ]);
            return;
        }

        $result = Auth::login($identifier, $password);

        if ($result['success']) {
            Security::clearRateLimit("login_attempt_{$ip}");
            $user = $result['user'];
            Logger::auth("User logged in via password", ['id' => $user['id'], 'email' => $user['email'] ?? $user['phone']]);

            if ($user['role_name'] === 'super_admin' || $user['role_name'] === 'store_manager') {
                Response::redirect('/admin');
            } else {
                if (!empty($_SESSION['intended_checkout'])) {
                    unset($_SESSION['intended_checkout']);
                    Response::redirect('/checkout');
                    return;
                }
                Response::redirect('/profile');
            }
            return;
        }

        Logger::security("Failed password login attempt", ['ip' => $ip, 'identifier' => $identifier]);

        Response::view('auth/login', [
            'error' => $result['message'],
            'email' => $identifier
        ]);
    }

    public function showForgotPassword(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/profile');
            return;
        }
        Response::view('auth/forgot_password');
    }

    public function forgotPassword(Request $request): void
    {
        $phone = OtpService::cleanPhone((string)$request->get('phone'));
        if (empty($phone) || strlen($phone) < 9) {
            Response::view('auth/forgot_password', [
                'error' => 'يرجى إدخال رقم جوال سعودي صحيح (مثال: 0555123456)',
                'phone' => $phone
            ]);
            return;
        }

        $user = Database::fetchOne("SELECT * FROM users WHERE phone = ?", [$phone]);
        if ($user) {
            $_SESSION['pending_otp_phone'] = $phone;
            $_SESSION['otp_purpose'] = 'reset';
            OtpService::sendOtp($phone);
            Response::redirect('/verify-otp?notice=reset');
            return;
        }

        Response::view('auth/forgot_password', [
            'error' => 'رقم الجوال غير مسجل لدينا في تمرنا.'
        ]);
    }

    public function showRegister(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/profile');
            return;
        }
        Response::view('auth/register');
    }

    public function register(Request $request): void
    {
        if (!OtpService::isEnabled()) {
            Response::view('auth/register', ['error' => 'التسجيل برمز الهاتف غير متاح حالياً.']);
            return;
        }
        $first = trim((string)$request->get('first'));
        $last = trim((string)$request->get('last'));
        $name = trim((string)$request->get('name'));
        if (empty($name) && (!empty($first) || !empty($last))) {
            $name = trim($first . ' ' . $last);
        }
        $phone = OtpService::cleanPhone((string)$request->get('phone'));
        $email = trim((string)$request->get('email'));
        $password = (string)$request->get('password');
        $confirm = (string)$request->get('confirm');

        if (!empty($password) && !empty($confirm) && $password !== $confirm) {
            Response::view('auth/register', [
                'error' => 'كلمتا المرور غير متطابقتين',
                'first' => $first,
                'last' => $last,
                'name' => $name,
                'phone' => $phone,
                'email' => $email
            ]);
            return;
        }

        if (empty($name) || empty($phone)) {
            Response::view('auth/register', [
                'error' => 'يرجى إدخال الاسم ورقم الجوال السعودي',
                'first' => $first,
                'last' => $last,
                'name' => $name,
                'phone' => $phone,
                'email' => $email
            ]);
            return;
        }

        if (strlen($phone) < 9) {
            Response::view('auth/register', [
                'error' => 'يرجى إدخال رقم جوال سعودي صحيح (مثال: 0555123456)',
                'name' => $name,
                'phone' => $phone,
                'email' => $email
            ]);
            return;
        }

        // Check if phone already registered
        $existing = Database::fetchOne("SELECT * FROM users WHERE phone = ?", [$phone]);
        if ($existing && $existing['phone_verified'] == 1) {
            $_SESSION['pending_otp_phone'] = $phone;
            OtpService::sendOtp($phone);
            Response::redirect('/verify-otp?notice=registered');
            return;
        }

        // Generate fallback email if not provided
        if (empty($email)) {
            $email = $phone . '@tamrna.store';
        } else {
            $emailUser = Database::fetchOne("SELECT id FROM users WHERE email = ? AND phone != ?", [$email, $phone]);
            if ($emailUser) {
                Response::view('auth/register', [
                    'error' => 'البريد الإلكتروني مسجل بالفعل لمستخدم آخر',
                    'name' => $name,
                    'phone' => $phone
                ]);
                return;
            }
        }

        $passToHash = !empty($password) ? $password : 'pass_' . bin2hex(random_bytes(4));
        $hash = password_hash($passToHash, PASSWORD_DEFAULT);

        if ($existing) {
            Database::execute("
                UPDATE users 
                SET name = ?, email = ?, password = ? 
                WHERE id = ?
            ", [$name, $email, $hash, $existing['id']]);
        } else {
            Database::execute("
                INSERT INTO users (role_id, name, email, password, phone, status, phone_verified)
                VALUES (3, ?, ?, ?, ?, 'active', 0)
            ", [$name, $email, $hash, $phone]);
        }

        // Send OTP (Default 123456 in demo mode)
        $_SESSION['pending_otp_phone'] = $phone;
        OtpService::sendOtp($phone);

        Response::redirect('/verify-otp');
    }

    public function showVerifyOtp(Request $request): void
    {
        $phone = $request->get('phone') ?: ($_SESSION['pending_otp_phone'] ?? null);
        if (!$phone) {
            Response::redirect('/register');
            return;
        }

        $notice = null;
        if ($request->get('notice') === 'registered') {
            $notice = 'هذا الجوال مسجل مسبقاً، تم إرسال رمز OTP لتسجيل دخولك السريع.';
        }

        Response::view('auth/verify_otp', [
            'phone' => $phone,
            'notice' => $notice,
            'success' => $request->get('resent') ? 'تمت إعادة إرسال رمز التحقق بنجاح!' : null,
            'error' => null
        ]);
    }

    public function verifyOtp(Request $request): void
    {
        $phone = OtpService::cleanPhone((string)($request->get('phone') ?: ($_SESSION['pending_otp_phone'] ?? '')));
        $otp = trim((string)$request->get('otp'));

        if (empty($phone) || empty($otp)) {
            Response::view('auth/verify_otp', [
                'phone' => $phone,
                'error' => 'يرجى إدخال رمز التحقق المكون من 6 أرقام (123456)'
            ]);
            return;
        }

        // Rate limit OTP verify attempts (phone + IP)
        $ip = Logger::getClientIp();
        if (!Security::rateLimit("otp_attempt_{$phone}_{$ip}", 5, 300)) {
            $retryAfter = Security::rateLimitResetSeconds("otp_attempt_{$phone}_{$ip}");
            Logger::security("Rate limit exceeded for OTP verification", ['phone' => $phone, 'ip' => $ip]);
            Response::view('auth/verify_otp', [
                'phone' => $phone,
                'error' => "تم حظر إدخال الرمز مؤقتاً لتكرار المحاولات الخاطئة. يرجى الانتظار {$retryAfter} ثانية."
            ]);
            return;
        }

        $verifyResult = OtpService::verifyOtp($phone, $otp);
        if (!$verifyResult['success']) {
            Logger::security("Failed OTP verification attempt", ['phone' => $phone, 'ip' => $ip]);
            Response::view('auth/verify_otp', [
                'phone' => $phone,
                'error' => $verifyResult['message']
            ]);
            return;
        }

        // OTP Success -> Clear rate limit & Login
        Security::clearRateLimit("otp_attempt_{$phone}_{$ip}");

        $user = Database::fetchOne("SELECT * FROM users WHERE phone = ?", [$phone]);
        if (!$user) {
            Response::redirect('/register');
            return;
        }

        Auth::loginUsingId((int)$user['id']);
        Logger::auth("Customer authenticated successfully via OTP", ['id' => $user['id'], 'phone' => $phone]);
        unset($_SESSION['pending_otp_phone']);

        if (!empty($_SESSION['intended_checkout'])) {
            unset($_SESSION['intended_checkout']);
            Response::redirect('/checkout');
            return;
        }

        Response::redirect('/profile?welcome=1');
    }

    public function resendOtp(Request $request): void
    {
        $phone = OtpService::cleanPhone((string)($request->get('phone') ?: ($_SESSION['pending_otp_phone'] ?? '')));
        if (!empty($phone)) {
            OtpService::sendOtp($phone);
            Response::redirect('/verify-otp?resent=1');
            return;
        }
        Response::redirect('/register');
    }

    public function logout(Request $request): void
    {
        if (Auth::check()) {
            $u = Auth::user();
            Logger::auth("User logged out", ['id' => $u['id'] ?? 0, 'email' => $u['email'] ?? $u['phone'] ?? '']);
        }
        Auth::logout();
        Response::redirect('/');
    }
}
