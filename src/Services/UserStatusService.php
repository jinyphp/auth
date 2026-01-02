<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\Log;

/**
 * 회원 상태 확인 서비스
 *
 * 사용자 계정의 상태를 체계적으로 확인하여 인증 및 토큰 발급을 제어합니다.
 * account_status, approval, email_verified_at, deleted_at 등의 필드를 확인합니다.
 */
class UserStatusService
{
    /**
     * 설정 배열
     *
     * @var array
     */
    protected $config;

    /**
     * 생성자
     *
     * 설정 파일을 로드하여 초기화합니다.
     */
    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * 설정 파일 로드
     *
     * 우선순위:
     * 1. config/status.json (사용자 정의)
     * 2. jiny/auth/config/status.json (패키지 기본값)
     * 3. 기본값 (fallback)
     */
    private function loadConfig()
    {
        // 1. 사용자 정의 설정 파일 확인
        $userConfigPath = config_path('status.json');
        if (file_exists($userConfigPath)) {
            try {
                $this->config = json_decode(file_get_contents($userConfigPath), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($this->config)) {
                    return;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to load status.json from config path', ['error' => $e->getMessage()]);
            }
        }

        // 2. 패키지 기본 설정 파일 확인
        $packageConfigPath = dirname(__DIR__, 2) . '/config/status.json';
        if (file_exists($packageConfigPath)) {
            try {
                $this->config = json_decode(file_get_contents($packageConfigPath), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($this->config)) {
                    return;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to load status.json from package path', ['error' => $e->getMessage()]);
            }
        }

        // 3. 기본값 (fallback)
        $this->config = [
            'account_status' => [
                'active' => 'active',
                'inactive' => 'inactive',
                'suspended' => 'suspended',
                'banned' => 'banned',
            ],
            'validation' => [
                'require_email_verification' => true,
                'require_approval' => false,
                'check_deleted_at' => true,
                'strict_mode' => true,
            ],
            'messages' => [
                'ACCOUNT_SUSPENDED' => '계정이 정지되었습니다.',
                'ACCOUNT_BANNED' => '계정이 차단되었습니다.',
                'EMAIL_NOT_VERIFIED' => '이메일 인증이 필요합니다.',
                'ACCOUNT_NOT_APPROVED' => '계정 승인이 필요합니다.',
                'ACCOUNT_DELETED' => '탈퇴한 계정입니다.',
                'ACCOUNT_INACTIVE' => '비활성화된 계정입니다.',
            ],
        ];
    }

    /**
     * 사용자 상태 전체 검증
     *
     * 모든 상태 필드를 확인하여 계정이 사용 가능한지 검증합니다.
     *
     * @param object $user 사용자 객체
     * @return array 검증 결과 ['valid' => bool, 'reason' => string|null, 'message' => string|null]
     */
    public function validateUserStatus($user): array
    {
        if (!$user) {
            return [
                'valid' => false,
                'reason' => 'USER_NOT_FOUND',
                'message' => '사용자를 찾을 수 없습니다.',
            ];
        }

        // 1. 계정 삭제 확인 (가장 우선순위가 높음)
        if ($this->isAccountDeleted($user)) {
            return [
                'valid' => false,
                'reason' => 'ACCOUNT_DELETED',
                'message' => $this->getMessage('ACCOUNT_DELETED'),
            ];
        }

        // 2. 계정 상태 확인 (account_status 또는 status 필드)
        if ($this->isAccountBanned($user)) {
            return [
                'valid' => false,
                'reason' => 'ACCOUNT_BANNED',
                'message' => $this->getMessage('ACCOUNT_BANNED'),
            ];
        }

        if ($this->isAccountSuspended($user)) {
            return [
                'valid' => false,
                'reason' => 'ACCOUNT_SUSPENDED',
                'message' => $this->getMessage('ACCOUNT_SUSPENDED'),
            ];
        }

        if (!$this->isAccountActive($user)) {
            return [
                'valid' => false,
                'reason' => 'ACCOUNT_INACTIVE',
                'message' => $this->getMessage('ACCOUNT_INACTIVE'),
            ];
        }

        // 3. 승인 상태 확인 (설정에 따라)
        if ($this->shouldCheckApproval() && !$this->isAccountApproved($user)) {
            return [
                'valid' => false,
                'reason' => 'ACCOUNT_NOT_APPROVED',
                'message' => $this->getMessage('ACCOUNT_NOT_APPROVED'),
            ];
        }

        // 4. 이메일 인증 확인 (설정에 따라)
        if ($this->shouldCheckEmailVerification() && !$this->isEmailVerified($user)) {
            return [
                'valid' => false,
                'reason' => 'EMAIL_NOT_VERIFIED',
                'message' => $this->getMessage('EMAIL_NOT_VERIFIED'),
            ];
        }

        return ['valid' => true];
    }

    /**
     * 계정이 활성 상태인지 확인
     *
     * account_status 또는 status 필드가 'active'인지 확인합니다.
     *
     * @param object $user 사용자 객체
     * @return bool
     */
    public function isAccountActive($user): bool
    {
        $status = $this->getAccountStatus($user);
        $activeStatus = $this->config['account_status']['active'] ?? 'active';

        return $status === $activeStatus;
    }

    /**
     * 계정이 정지 상태인지 확인
     *
     * account_status 또는 status 필드가 'suspended'인지 확인합니다.
     *
     * @param object $user 사용자 객체
     * @return bool
     */
    public function isAccountSuspended($user): bool
    {
        $status = $this->getAccountStatus($user);
        $suspendedStatus = $this->config['account_status']['suspended'] ?? 'suspended';

        return $status === $suspendedStatus;
    }

    /**
     * 계정이 차단 상태인지 확인
     *
     * account_status 또는 status 필드가 'banned'이거나 'blocked'인지 확인합니다.
     *
     * @param object $user 사용자 객체
     * @return bool
     */
    public function isAccountBanned($user): bool
    {
        $status = $this->getAccountStatus($user);
        $bannedStatus = $this->config['account_status']['banned'] ?? 'banned';

        return $status === $bannedStatus || $status === 'blocked';
    }

    /**
     * 계정이 승인되었는지 확인
     *
     * approval 필드가 true이거나 null인지 확인합니다.
     * (null은 승인 불필요 또는 자동 승인을 의미할 수 있음)
     *
     * @param object $user 사용자 객체
     * @return bool
     */
    public function isAccountApproved($user): bool
    {
        // approval 필드가 없으면 승인된 것으로 간주 (기존 호환성)
        if (!isset($user->approval)) {
            return true;
        }

        // approval이 true이거나 1이면 승인됨
        return $user->approval === true || $user->approval === 1 || $user->approval === '1';
    }

    /**
     * 이메일이 인증되었는지 확인
     *
     * email_verified_at 필드가 null이 아닌지 확인합니다.
     *
     * @param object $user 사용자 객체
     * @return bool
     */
    public function isEmailVerified($user): bool
    {
        // email_verified_at 필드가 없으면 인증되지 않은 것으로 간주
        if (!isset($user->email_verified_at)) {
            return false;
        }

        // null이 아니면 인증됨
        return $user->email_verified_at !== null;
    }

    /**
     * 계정이 삭제되었는지 확인
     *
     * deleted_at 필드가 null이 아닌지 확인합니다 (soft delete).
     *
     * @param object $user 사용자 객체
     * @return bool
     */
    public function isAccountDeleted($user): bool
    {
        // check_deleted_at 설정이 false이면 확인하지 않음
        if (!($this->config['validation']['check_deleted_at'] ?? true)) {
            return false;
        }

        // deleted_at 필드가 없으면 삭제되지 않은 것으로 간주
        if (!isset($user->deleted_at)) {
            return false;
        }

        // null이 아니면 삭제됨
        return $user->deleted_at !== null;
    }

    /**
     * 계정 상태 값 가져오기
     *
     * account_status 필드를 우선 확인하고, 없으면 status 필드를 확인합니다.
     * (기존 코드와의 호환성을 위해)
     *
     * @param object $user 사용자 객체
     * @return string|null
     */
    protected function getAccountStatus($user): ?string
    {
        // account_status 필드 우선 확인
        if (isset($user->account_status)) {
            return $user->account_status;
        }

        // status 필드 확인 (기존 호환성)
        if (isset($user->status)) {
            return $user->status;
        }

        // 기본값: active로 간주
        return $this->config['account_status']['active'] ?? 'active';
    }

    /**
     * 승인 상태 확인이 필요한지 확인
     *
     * @return bool
     */
    protected function shouldCheckApproval(): bool
    {
        return $this->config['validation']['require_approval'] ?? false;
    }

    /**
     * 이메일 인증 확인이 필요한지 확인
     *
     * @return bool
     */
    protected function shouldCheckEmailVerification(): bool
    {
        return $this->config['validation']['require_email_verification'] ?? true;
    }

    /**
     * 에러 메시지 가져오기
     *
     * @param string $reason 에러 코드
     * @return string
     */
    protected function getMessage(string $reason): string
    {
        return $this->config['messages'][$reason] ?? '계정 상태를 확인할 수 없습니다.';
    }
}
