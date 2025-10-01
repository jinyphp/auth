<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * 계정 잠금 서비스
 *
 * 비밀번호 오류 횟수에 따른 단계별 접속 제한 관리
 */
class AccountLockoutService
{
    // 잠금 단계별 설정 (config에서 로드)
    protected $lockoutLevels;

    public function __construct()
    {
        $this->lockoutLevels = config('admin.auth.lockout.levels', [
            1 => ['attempts' => 5, 'duration' => 15], // 1단계: 5회 실패 시 15분
            2 => ['attempts' => 10, 'duration' => 60], // 2단계: 10회 실패 시 60분
            3 => ['attempts' => 15, 'duration' => 0], // 3단계: 15회 실패 시 영구 (관리자 해제 필요)
        ]);
    }

    /**
     * 로그인 실패 기록 및 잠금 처리
     *
     * @param string $email
     * @param string|null $userUuid
     * @param string $ipAddress
     * @return array
     */
    public function recordFailedAttempt($email, $userUuid = null, $ipAddress = null)
    {
        // 현재 잠금 상태 확인
        $lockout = $this->getCurrentLockout($email);

        if ($lockout && $lockout->status === 'locked') {
            // 이미 잠금 상태
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

            // 잠금 시간 만료 → 자동 해제
            $this->unlockAccount($lockout->id);
            $lockout = null;
        }

        // 실패 횟수 증가
        $failedAttempts = $this->getFailedAttemptsCount($email);
        $failedAttempts++;

        // 잠금 단계 결정
        $lockoutLevel = $this->determineLockoutLevel($failedAttempts);

        if ($lockoutLevel) {
            // 잠금 처리
            return $this->lockAccount($email, $userUuid, $ipAddress, $lockoutLevel, $failedAttempts);
        }

        return [
            'locked' => false,
            'failed_attempts' => $failedAttempts,
            'remaining_attempts' => $this->lockoutLevels[1]['attempts'] - $failedAttempts,
        ];
    }

    /**
     * 계정 잠금 처리
     *
     * @param string $email
     * @param string|null $userUuid
     * @param string|null $ipAddress
     * @param int $level
     * @param int $failedAttempts
     * @return array
     */
    protected function lockAccount($email, $userUuid, $ipAddress, $level, $failedAttempts)
    {
        $duration = $this->lockoutLevels[$level]['duration'];
        $requiresAdmin = ($duration === 0); // 0이면 영구 잠금 (관리자 해제 필요)

        $unlocksAt = $requiresAdmin ? null : now()->addMinutes($duration);

        // 기존 잠금 레코드가 있으면 업데이트, 없으면 생성
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

        // 캐시 업데이트
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
    }

    /**
     * 현재 잠금 상태 조회
     *
     * @param string $email
     * @return object|null
     */
    public function getCurrentLockout($email)
    {
        return DB::table('account_lockouts')
            ->where('email', $email)
            ->where('status', 'locked')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * 실패 횟수 조회
     *
     * @param string $email
     * @return int
     */
    protected function getFailedAttemptsCount($email)
    {
        $timeWindow = config('admin.auth.lockout.time_window', 60); // 기본 60분

        return DB::table('auth_login_attempts')
            ->where('email', $email)
            ->where('successful', false)
            ->where('attempted_at', '>', now()->subMinutes($timeWindow))
            ->count();
    }

    /**
     * 잠금 단계 결정
     *
     * @param int $failedAttempts
     * @return int|null
     */
    protected function determineLockoutLevel($failedAttempts)
    {
        foreach ($this->lockoutLevels as $level => $config) {
            if ($failedAttempts >= $config['attempts']) {
                $nextLevel = $level + 1;
                if (isset($this->lockoutLevels[$nextLevel])) {
                    continue; // 다음 단계가 있으면 계속 확인
                }
                return $level; // 최종 단계
            }
        }

        // 1단계 잠금 확인
        if ($failedAttempts >= $this->lockoutLevels[1]['attempts']) {
            return 1;
        }

        return null; // 아직 잠금 불필요
    }

    /**
     * 계정 잠금 해제
     *
     * @param int $lockoutId
     * @param int|null $adminId
     * @param string|null $reason
     * @return bool
     */
    public function unlockAccount($lockoutId, $adminId = null, $reason = null)
    {
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

        // 실패 시도 기록 삭제
        DB::table('auth_login_attempts')
            ->where('email', $lockout->email)
            ->where('successful', false)
            ->delete();

        // 캐시 삭제
        Cache::forget("lockout_{$lockout->email}");

        return true;
    }

    /**
     * 이메일로 잠금 해제
     *
     * @param string $email
     * @param int|null $adminId
     * @param string|null $reason
     * @return bool
     */
    public function unlockByEmail($email, $adminId = null, $reason = null)
    {
        $lockout = $this->getCurrentLockout($email);

        if (!$lockout) {
            return false;
        }

        return $this->unlockAccount($lockout->id, $adminId, $reason);
    }

    /**
     * 잠금 여부 확인
     *
     * @param string $email
     * @return array
     */
    public function checkLockout($email)
    {
        // 캐시 확인
        $cacheKey = "lockout_{$email}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $lockout = $this->getCurrentLockout($email);

        if (!$lockout) {
            $result = ['locked' => false];
            Cache::put($cacheKey, $result, 300); // 5분 캐시
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
                // 자동 해제
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

        Cache::put($cacheKey, $result, 60); // 1분 캐시
        return $result;
    }

    /**
     * 잠금 메시지 생성
     *
     * @param int $level
     * @param int $duration
     * @param bool $requiresAdmin
     * @return string
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
     *
     * @param string $email
     * @param int $level
     * @param \Carbon\Carbon|null $unlocksAt
     * @param bool $requiresAdmin
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
     * 관리자 해제 필요 계정 목록
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAccountsRequiringAdminUnlock()
    {
        return DB::table('account_lockouts')
            ->where('status', 'locked')
            ->where('requires_admin_unlock', true)
            ->orderBy('locked_at', 'desc')
            ->get();
    }

    /**
     * 잠금 통계
     *
     * @return array
     */
    public function getLockoutStatistics()
    {
        return [
            'total_locked' => DB::table('account_lockouts')
                ->where('status', 'locked')
                ->count(),

            'requires_admin' => DB::table('account_lockouts')
                ->where('status', 'locked')
                ->where('requires_admin_unlock', true)
                ->count(),

            'auto_unlock_pending' => DB::table('account_lockouts')
                ->where('status', 'locked')
                ->where('requires_admin_unlock', false)
                ->where('unlocks_at', '>', now())
                ->count(),

            'by_level' => DB::table('account_lockouts')
                ->where('status', 'locked')
                ->select('lockout_level', DB::raw('count(*) as count'))
                ->groupBy('lockout_level')
                ->pluck('count', 'lockout_level')
                ->toArray(),
        ];
    }

    /**
     * 자동 해제 처리 (스케줄러에서 호출)
     *
     * @return int 해제된 계정 수
     */
    public function processAutoUnlocks()
    {
        $lockouts = DB::table('account_lockouts')
            ->where('status', 'locked')
            ->where('requires_admin_unlock', false)
            ->where('unlocks_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($lockouts as $lockout) {
            $this->unlockAccount($lockout->id, null, '자동 해제 (시간 만료)');
            $count++;
        }

        return $count;
    }

    /**
     * 잠금 이력 조회
     *
     * @param string $email
     * @return \Illuminate\Support\Collection
     */
    public function getLockoutHistory($email)
    {
        return DB::table('account_lockouts')
            ->where('email', $email)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 실패 횟수 초기화
     *
     * @param string $email
     * @return bool
     */
    public function resetFailedAttempts($email)
    {
        DB::table('auth_login_attempts')
            ->where('email', $email)
            ->where('successful', false)
            ->delete();

        Cache::forget("lockout_{$email}");

        return true;
    }
}