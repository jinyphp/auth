<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * 계정 잠금 서비스 (테이블 없이도 작동)
 */
class AccountLockoutService
{
    protected $lockoutLevels;
    protected $enabled = true;

    public function __construct()
    {
        $this->lockoutLevels = config('admin.auth.lockout.levels', [
            1 => ['attempts' => 5, 'duration' => 15],
            2 => ['attempts' => 10, 'duration' => 60],
            3 => ['attempts' => 15, 'duration' => 0],
        ]);

        // 테이블 존재 여부 확인
        try {
            $this->enabled = Schema::hasTable('account_lockouts');
        } catch (\Exception $e) {
            $this->enabled = false;
        }
    }

    /**
     * 로그인 실패 기록
     */
    public function recordFailedAttempt($email, $userUuid = null, $ipAddress = null)
    {
        if (!$this->enabled) {
            return ['locked' => false];
        }

        try {
            $lockout = $this->getCurrentLockout($email);

            if ($lockout && $lockout->status === 'locked') {
                if ($lockout->requires_admin_unlock) {
                    return [
                        'locked' => true,
                        'level' => $lockout->lockout_level,
                        'requires_admin' => true,
                        'message' => '관리자 승인이 필요합니다.',
                    ];
                }

                if ($lockout->unlocks_at && $lockout->unlocks_at->gt(now())) {
                    $remainingMinutes = now()->diffInMinutes($lockout->unlocks_at);
                    return [
                        'locked' => true,
                        'level' => $lockout->lockout_level,
                        'unlocks_at' => $lockout->unlocks_at,
                        'remaining_minutes' => $remainingMinutes,
                        'message' => "{$remainingMinutes}분 후에 다시 시도하세요.",
                    ];
                }

                $this->unlockAccount($lockout->id);
            }

            $failedAttempts = $this->getFailedAttemptsCount($email) + 1;
            $lockoutLevel = $this->determineLockoutLevel($failedAttempts);

            if ($lockoutLevel) {
                return $this->lockAccount($email, $userUuid, $ipAddress, $lockoutLevel, $failedAttempts);
            }

            return [
                'locked' => false,
                'failed_attempts' => $failedAttempts,
                'remaining_attempts' => $this->lockoutLevels[1]['attempts'] - $failedAttempts,
            ];
        } catch (\Exception $e) {
            \Log::warning('AccountLockoutService error: ' . $e->getMessage());
            return ['locked' => false];
        }
    }

    /**
     * 계정 잠금 처리
     */
    protected function lockAccount($email, $userUuid, $ipAddress, $level, $failedAttempts)
    {
        if (!$this->enabled) {
            return ['locked' => false];
        }

        try {
            $duration = $this->lockoutLevels[$level]['duration'];
            $requiresAdmin = ($duration === 0);
            $unlocksAt = $requiresAdmin ? null : now()->addMinutes($duration);

            $existingLockout = $this->getCurrentLockout($email);

            if ($existingLockout) {
                DB::table('account_lockouts')->where('id', $existingLockout->id)->update([
                    'lockout_level' => $level,
                    'failed_attempts' => $failedAttempts,
                    'lockout_duration' => $duration,
                    'status' => 'locked',
                    'locked_at' => now(),
                    'unlocks_at' => $unlocksAt,
                    'requires_admin_unlock' => $requiresAdmin,
                    'updated_at' => now(),
                ]);
                $lockoutId = $existingLockout->id;
            } else {
                $lockoutId = DB::table('account_lockouts')->insertGetId([
                    'user_uuid' => $userUuid,
                    'email' => $email,
                    'ip_address' => $ipAddress,
                    'lockout_level' => $level,
                    'failed_attempts' => $failedAttempts,
                    'lockout_duration' => $duration,
                    'status' => 'locked',
                    'locked_at' => now(),
                    'unlocks_at' => $unlocksAt,
                    'requires_admin_unlock' => $requiresAdmin,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->updateLockoutCache($email, $level, $unlocksAt, $requiresAdmin);

            return [
                'locked' => true,
                'level' => $level,
                'duration' => $duration,
                'unlocks_at' => $unlocksAt,
                'requires_admin' => $requiresAdmin,
                'lockout_id' => $lockoutId,
                'message' => $this->getLockoutMessage($level, $duration, $requiresAdmin),
            ];
        } catch (\Exception $e) {
            \Log::warning('lockAccount error: ' . $e->getMessage());
            return ['locked' => false];
        }
    }

    /**
     * 현재 잠금 상태 조회
     */
    public function getCurrentLockout($email)
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            return DB::table('account_lockouts')
                ->where('email', $email)
                ->where('status', 'locked')
                ->orderBy('created_at', 'desc')
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 실패 횟수 조회
     */
    protected function getFailedAttemptsCount($email)
    {
        try {
            $timeWindow = config('admin.auth.lockout.time_window', 60);

            return DB::table('auth_login_attempts')
                ->where('email', $email)
                ->where('successful', false)
                ->where('attempted_at', '>', now()->subMinutes($timeWindow))
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 잠금 단계 결정
     */
    protected function determineLockoutLevel($failedAttempts)
    {
        foreach ($this->lockoutLevels as $level => $config) {
            if ($failedAttempts >= $config['attempts']) {
                $nextLevel = $level + 1;
                if (isset($this->lockoutLevels[$nextLevel])) {
                    continue;
                }
                return $level;
            }
        }

        if ($failedAttempts >= $this->lockoutLevels[1]['attempts']) {
            return 1;
        }

        return null;
    }

    /**
     * 계정 잠금 해제
     */
    public function unlockAccount($lockoutId, $adminId = null, $reason = null)
    {
        if (!$this->enabled) {
            return true;
        }

        try {
            $lockout = DB::table('account_lockouts')->where('id', $lockoutId)->first();

            if (!$lockout) {
                return false;
            }

            DB::table('account_lockouts')->where('id', $lockoutId)->update([
                'status' => 'unlocked',
                'unlocked_by' => $adminId,
                'unlocked_at' => now(),
                'unlock_reason' => $reason ?? '자동 해제',
                'updated_at' => now(),
            ]);

            DB::table('auth_login_attempts')
                ->where('email', $lockout->email)
                ->where('successful', false)
                ->delete();

            Cache::forget("lockout_{$lockout->email}");

            return true;
        } catch (\Exception $e) {
            \Log::warning('unlockAccount error: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * 이메일로 잠금 해제
     */
    public function unlockByEmail($email, $adminId = null, $reason = null)
    {
        if (!$this->enabled) {
            return true;
        }

        try {
            $lockout = $this->getCurrentLockout($email);

            if (!$lockout) {
                return true;
            }

            return $this->unlockAccount($lockout->id, $adminId, $reason);
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * 잠금 여부 확인
     */
    public function checkLockout($email)
    {
        if (!$this->enabled) {
            return ['locked' => false];
        }

        try {
            $cacheKey = "lockout_{$email}";
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $lockout = $this->getCurrentLockout($email);

            if (!$lockout) {
                $result = ['locked' => false];
                Cache::put($cacheKey, $result, 300);
                return $result;
            }

            if ($lockout->requires_admin_unlock) {
                $result = [
                    'locked' => true,
                    'level' => $lockout->lockout_level,
                    'requires_admin' => true,
                    'locked_at' => $lockout->locked_at,
                    'message' => '관리자 승인이 필요합니다. 고객센터로 문의하세요.',
                ];
            } else {
                if ($lockout->unlocks_at && $lockout->unlocks_at->lte(now())) {
                    $this->unlockAccount($lockout->id);
                    $result = ['locked' => false];
                } else {
                    $remainingMinutes = now()->diffInMinutes($lockout->unlocks_at);
                    $result = [
                        'locked' => true,
                        'level' => $lockout->lockout_level,
                        'unlocks_at' => $lockout->unlocks_at,
                        'remaining_minutes' => $remainingMinutes,
                        'message' => "{$remainingMinutes}분 후에 다시 시도하세요.",
                    ];
                }
            }

            Cache::put($cacheKey, $result, 60);
            return $result;
        } catch (\Exception $e) {
            \Log::warning('checkLockout error: ' . $e->getMessage());
            return ['locked' => false];
        }
    }

    /**
     * 잠금 메시지 생성
     */
    protected function getLockoutMessage($level, $duration, $requiresAdmin)
    {
        if ($requiresAdmin) {
            return "계정이 영구 잠금되었습니다. 관리자에게 문의하세요.";
        }

        return "비밀번호를 {$this->lockoutLevels[$level]['attempts']}회 이상 잘못 입력하여 {$duration}분간 로그인이 제한됩니다.";
    }

    /**
     * 잠금 캐시 업데이트
     */
    protected function updateLockoutCache($email, $level, $unlocksAt, $requiresAdmin)
    {
        $cacheKey = "lockout_{$email}";
        $cacheData = [
            'locked' => true,
            'level' => $level,
            'unlocks_at' => $unlocksAt,
            'requires_admin' => $requiresAdmin,
        ];

        Cache::put($cacheKey, $cacheData, $unlocksAt ? now()->diffInSeconds($unlocksAt) : 86400);
    }

    /**
     * 실패 횟수 초기화
     */
    public function resetFailedAttempts($email)
    {
        try {
            DB::table('auth_login_attempts')
                ->where('email', $email)
                ->where('successful', false)
                ->delete();

            Cache::forget("lockout_{$email}");

            return true;
        } catch (\Exception $e) {
            return true;
        }
    }
}
