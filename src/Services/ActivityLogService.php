<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;

/**
 * 활동 로그 서비스
 */
class ActivityLogService
{
    /**
     * 로그인 성공 기록
     */
    public function logSuccessfulLogin($user, $ipAddress)
    {
        try {
            DB::table('auth_activity_logs')->insert([
                'user_id' => $user->id,
                'activity_type' => 'login',
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // 로그 테이블이 없으면 무시
            \Log::warning('Activity log table does not exist: ' . $e->getMessage());
        }
    }

    /**
     * 로그인 실패 기록
     */
    public function logFailedLogin($email, $reason, $ipAddress)
    {
        try {
            DB::table('auth_activity_logs')->insert([
                'email' => $email,
                'activity_type' => 'failed_login',
                'reason' => $reason,
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // 로그 테이블이 없으면 무시
            \Log::warning('Activity log table does not exist: ' . $e->getMessage());
        }
    }

    /**
     * 회원가입 성공 기록
     */
    public function logUserRegistration($user, $ipAddress)
    {
        try {
            DB::table('auth_activity_logs')->insert([
                'user_id' => $user->id ?? null,
                'email' => $user->email,
                'activity_type' => 'registration',
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // 로그 테이블이 없으면 무시
            \Log::warning('Activity log table does not exist: ' . $e->getMessage());
        }
    }

    /**
     * 회원가입 시도 기록
     */
    public function logRegistrationAttempt($email, $ipAddress)
    {
        try {
            DB::table('auth_activity_logs')->insert([
                'email' => $email,
                'activity_type' => 'registration_attempt',
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // 로그 테이블이 없으면 무시
            \Log::warning('Activity log table does not exist: ' . $e->getMessage());
        }
    }

    /**
     * 회원가입 오류 기록
     */
    public function logRegistrationError($requestData, $errorMessage, $ipAddress)
    {
        try {
            DB::table('auth_activity_logs')->insert([
                'email' => $requestData['email'] ?? null,
                'activity_type' => 'registration_error',
                'reason' => $errorMessage,
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'metadata' => json_encode([
                    'name' => $requestData['name'] ?? null,
                    'error' => $errorMessage,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // 로그 테이블이 없으면 무시
            \Log::warning('Activity log table does not exist: ' . $e->getMessage());
        }
    }
}
