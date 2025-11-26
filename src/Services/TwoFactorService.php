<?php

namespace Jiny\Auth\Services;

use App\Models\User as ApplicationUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jiny\Auth\Facades\Shard;
use PragmaRX\Google2FA\Google2FA;

/**
 * 2단계 인증(2FA) 전체 로직을 담당하는 서비스
 *
 * - 사용자별 2FA 활성화/비활성화
 * - OTP(Authenticator) 시크릿 생성 및 검증
 * - 백업 코드 발급 및 사용 처리
 * - 로그인 단계에서의 2FA 챌린지 세션 관리
 * - 2FA 관련 로그(two_factor_logs 테이블) 기록
 */
class TwoFactorService
{
    /**
     * 로그인 챌린지 세션 키
     */
    private const SESSION_PENDING = 'two_factor.pending';

    /**
     * Google Authenticator(TOTP) 헬퍼
     */
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * 패키지 전역에서 2FA 기능이 활성화되어 있는지 확인합니다.
     */
    public function isFeatureEnabled(): bool
    {
        return (bool) (config('admin.auth.two_factor.enable', true));
    }

    /**
     * 특정 사용자가 2FA를 활성화했는지 확인합니다.
     */
    public function isEnabled($user): bool
    {
        return (bool) ($user->two_factor_enabled ?? false);
    }

    /**
     * 로그인 시 2FA 챌린지를 요구해야 하는지 여부를 반환합니다.
     */
    public function requiresChallenge($user): bool
    {
        if (!$this->isFeatureEnabled()) {
            return false;
        }

        return $this->isEnabled($user);
    }

    /**
     * 로그인 1차 인증(아이디/비밀번호) 통과 후 2FA 챌린지를 시작합니다.
     * 세션에 필요한 사용자 정보를 저장하고, 감사 로그를 남깁니다.
     */
    public function beginLoginChallenge($user, array $context = []): void
    {
        $payload = [
            'user_id' => $user->id ?? null,
            'user_uuid' => $user->uuid ?? null,
            'email' => $user->email ?? null,
            'name' => $user->name ?? null,
            'method' => $user->two_factor_method ?? 'totp',
            'remember' => (bool)($context['remember'] ?? false),
            'login_method' => $context['login_method'] ?? 'jwt',
            'shard_id' => $user->shard_id ?? null,
            'requested_at' => now()->toDateTimeString(),
        ];

        session([self::SESSION_PENDING => $payload]);

        $this->log($user, 'challenge_started', '2FA 코드 입력을 요청했습니다.', [
            'method' => $payload['method'],
            'ip' => $context['ip'] ?? request()?->ip(),
            'user_agent' => $context['user_agent'] ?? request()?->userAgent(),
        ]);
    }

    /**
     * 현재 세션에 저장된 2FA 챌린지 정보를 반환합니다.
     */
    public function getPendingChallenge(): ?array
    {
        return session(self::SESSION_PENDING);
    }

    /**
     * 진행 중인 2FA 챌린지 세션을 정리합니다.
     */
    public function clearPendingChallenge(): void
    {
        session()->forget(self::SESSION_PENDING);
    }

    /**
     * 2FA 설정을 위한 시크릿, QR, 백업코드를 생성합니다.
     */
    public function generateSetupPayload($user): array
    {
        $secret = $this->google2fa->generateSecretKey(32);
        $company = config('app.name', 'JinyCMS');
        $email = $user->email ?? 'user@example.com';
        $otpauth = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($company),
            rawurlencode($email),
            $secret,
            rawurlencode($company)
        );

        // 외부 QR 생성 API 사용 (SVG)
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
            'size' => '220x220',
            'data' => $otpauth,
            'format' => 'svg',
        ]);

        return [
            'secret' => $secret,
            'qr_url' => $qrUrl,
            'backup_codes' => $this->generateBackupCodes(),
        ];
    }

    /**
     * 생성된 시크릿으로 2FA를 활성화합니다.
     */
    public function enableFromSetup($user, array $payload, string $verificationCode, string $method = 'totp'): bool
    {
        if (empty($payload['secret'])) {
            return false;
        }

        if (!$this->google2fa->verifyKey($payload['secret'], $verificationCode)) {
            $this->log($user, 'enable_failed', '잘못된 검증 코드로 인해 2FA 활성화에 실패했습니다.', [
                'method' => $method,
                'status' => 'failed',
            ]);
            return false;
        }

        $this->persistUserAttributes($user, [
            'two_factor_enabled' => true,
            'two_factor_method' => $method,
            'two_factor_secret' => encrypt($payload['secret']),
            'two_factor_recovery_codes' => encrypt(json_encode($payload['backup_codes'] ?? [])),
            'used_backup_codes' => null,
            'two_factor_confirmed_at' => now(),
            'last_2fa_used_at' => null,
        ]);

        $this->log($user, 'enabled', '2FA가 활성화되었습니다.', [
            'method' => $method,
            'status' => 'success',
            'backup_codes' => count($payload['backup_codes'] ?? []),
        ]);

        return true;
    }

    /**
     * 2FA를 비활성화합니다.
     */
    public function disable($user, bool $force = false): void
    {
        $this->persistUserAttributes($user, [
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'used_backup_codes' => null,
            'two_factor_confirmed_at' => null,
            'last_2fa_used_at' => null,
        ]);

        $this->log($user, $force ? 'force_disabled' : 'disabled', '2FA가 비활성화되었습니다.', [
            'status' => 'success',
        ]);
    }

    /**
     * 활성화된 사용자에 대해 백업 코드를 재생성합니다.
     */
    public function regenerateBackupCodes($user): array
    {
        $codes = $this->generateBackupCodes();

        $this->persistUserAttributes($user, [
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
            'used_backup_codes' => null,
        ]);

        $this->log($user, 'backup_regenerated', '2FA 백업 코드가 재생성되었습니다.', [
            'count' => count($codes),
        ]);

        return $codes;
    }

    /**
     * 로그인 시 입력된 2FA 코드를 검증합니다.
     *
     * @return array{success:bool,method?:string,message?:string}
     */
    public function verifyLoginCode($user, string $code): array
    {
        $code = trim($code);

        if ($this->verifyTotp($user, $code)) {
            $this->markLastUsed($user);
            $this->log($user, 'challenge_success', 'TOTP 코드가 확인되었습니다.', [
                'method' => 'totp',
                'status' => 'success',
            ]);

            return ['success' => true, 'method' => 'totp'];
        }

        if ($this->verifyBackupCode($user, $code)) {
            $this->markLastUsed($user);
            $this->log($user, 'challenge_success', '백업 코드가 사용되었습니다.', [
                'method' => 'backup',
                'status' => 'success',
            ]);

            return ['success' => true, 'method' => 'backup'];
        }

        $this->log($user, 'challenge_failed', '잘못된 2FA 코드가 입력되었습니다.', [
            'status' => 'failed',
        ]);

        return ['success' => false, 'message' => '인증 코드를 다시 확인해주세요.'];
    }

    /**
     * 2FA 상태 정보를 반환합니다. (관리자 페이지 표시용)
     */
    public function getStatus($user): array
    {
        $backupCodes = $this->getDecryptedBackupCodes($user);
        $usedCodes = $this->getUsedBackupCodes($user);

        return [
            'enabled' => $this->isEnabled($user),
            'method' => $user->two_factor_method ?? 'totp',
            'confirmed_at' => $user->two_factor_confirmed_at,
            'last_used_at' => $user->last_2fa_used_at,
            'backup_codes_total' => count($backupCodes),
            'backup_codes_used' => count($usedCodes),
            'backup_codes_remaining' => max(count($backupCodes) - count($usedCodes), 0),
            'has_secret' => !empty($user->two_factor_secret),
        ];
    }

    /**
     * 가장 최근 2FA 로그를 조회합니다.
     */
    public function getRecentLogs($user, int $limit = 10)
    {
        return DB::table('two_factor_logs')
            ->where(function ($query) use ($user) {
                if ($user->uuid ?? null) {
                    $query->where('user_uuid', $user->uuid);
                } elseif ($user->id ?? null) {
                    $query->where('user_id', $user->id);
                }

                if (($user->uuid ?? null) && ($user->id ?? null)) {
                    $query->orWhere('user_id', $user->id);
                }
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * pending 세션 데이터를 이용해 로그인에 필요한 ApplicationUser 인스턴스를 구성합니다.
     */
    public function hydrateUserFromPending(array $pending): ?ApplicationUser
    {
        if (($pending['user_uuid'] ?? null) && Shard::isEnabled()) {
            $userData = Shard::getUserByUuid($pending['user_uuid']);
            if (!$userData) {
                return null;
            }

            $user = new ApplicationUser();
            foreach ((array) $userData as $key => $value) {
                $user->{$key} = $value;
            }
            $user->exists = true;

            return $user;
        }

        if ($pending['user_id'] ?? null) {
            return ApplicationUser::find($pending['user_id']);
        }

        return null;
    }

    /**
     * 6자리 백업 코드를 여러 개 생성합니다.
     */
    protected function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(4) . '-' . Str::random(4));
        }

        return $codes;
    }

    /**
     * TOT 코드 검증
     */
    protected function verifyTotp($user, string $code): bool
    {
        $secret = $this->getDecryptedSecret($user);
        if (!$secret) {
            return false;
        }

        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * 백업 코드 검증 및 사용 처리
     */
    protected function verifyBackupCode($user, string $code): bool
    {
        $codes = $this->getDecryptedBackupCodes($user);
        if (empty($codes)) {
            return false;
        }

        $normalized = strtoupper($code);
        if (!in_array($normalized, $codes, true)) {
            return false;
        }

        $used = $this->getUsedBackupCodes($user);
        $used[] = $normalized;

        $remaining = array_values(array_diff($codes, [$normalized]));

        $this->persistUserAttributes($user, [
            'two_factor_recovery_codes' => encrypt(json_encode($remaining)),
            'used_backup_codes' => json_encode($used),
        ]);

        return true;
    }

    /**
     * 마지막 2FA 사용 시간을 갱신합니다.
     */
    public function markLastUsed($user): void
    {
        $this->persistUserAttributes($user, [
            'last_2fa_used_at' => now(),
        ]);
    }

    /**
     * 사용자 정보 저장 로직 (샤딩/일반 테이블 모두 지원)
     */
    protected function persistUserAttributes($user, array $attributes): void
    {
        $synced = false;

        // 샤딩 사용 시 실제 샤드 테이블에 우선 반영
        if (($user->uuid ?? null) && Shard::isEnabled()) {
            Shard::updateUser($user->uuid, $attributes);
            $synced = true;
        }

        // Eloquent 모델이 주어졌다면 기본 users 테이블에도 반영 (샤딩 OFF 또는 미러 테이블 유지용)
        if ($user instanceof Model) {
            $user->fill($attributes);
            try {
                $user->save();
                $synced = true;
            } catch (\Throwable $e) {
                \Log::warning('TwoFactorService: 모델 업데이트 실패', [
                    'message' => $e->getMessage(),
                ]);
            }
        } elseif (!Shard::isEnabled() && ($user->id ?? null)) {
            DB::table('users')->where('id', $user->id)->update($attributes);
            $synced = true;
        }

        if ($synced) {
            foreach ($attributes as $key => $value) {
                $user->{$key} = $value;
            }
        }
    }

    /**
     * 암호화된 시크릿을 복호화합니다.
     */
    protected function getDecryptedSecret($user): ?string
    {
        if (empty($user->two_factor_secret)) {
            return null;
        }

        try {
            return decrypt($user->two_factor_secret);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 암호화된 백업 코드를 복호화합니다.
     */
    protected function getDecryptedBackupCodes($user): array
    {
        if (empty($user->two_factor_recovery_codes)) {
            return [];
        }

        try {
            return json_decode(decrypt($user->two_factor_recovery_codes), true) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 사용된 백업 코드 목록을 반환합니다.
     */
    protected function getUsedBackupCodes($user): array
    {
        if (empty($user->used_backup_codes)) {
            return [];
        }

        try {
            return json_decode($user->used_backup_codes, true) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * two_factor_logs 테이블에 기록합니다.
     */
    public function log($user, string $action, string $description, array $context = []): void
    {
        try {
            DB::table('two_factor_logs')->insert([
                'user_id' => $user->id ?? null,
                'user_uuid' => $user->uuid ?? null,
                'email' => $user->email ?? null,
                'action' => $action,
                'method' => $context['method'] ?? ($user->two_factor_method ?? 'totp'),
                'status' => $context['status'] ?? 'info',
                'description' => $description,
                'ip_address' => $context['ip'] ?? request()?->ip(),
                'user_agent' => $context['user_agent'] ?? request()?->userAgent(),
                'metadata' => $context ? json_encode($context) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // 로그 저장 실패 시에도 로그인 플로우는 계속 진행되어야 함
            \Log::warning('TwoFactorService log failed', [
                'action' => $action,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
