<?php

namespace Jiny\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * 사용자 샤딩 테이블 시드 클래스
 *
 * 이 시더는 샤딩된 사용자 테이블(users_001, users_002, ...)에
 * 테스트용 사용자 데이터를 생성합니다.
 *
 * @package Jiny\Auth\Database\Seeders
 * @author JinyPHP Team
 * @version 1.0.0
 */
class UserShardingSeeder extends Seeder
{
    /**
     * 샤드당 생성할 사용자 수
     * 각 샤드 테이블마다 30개의 테스트 계정을 생성합니다.
     */
    const USERS_PER_SHARD = 30;

    /**
     * 테스트 사용자 이메일 도메인
     * 모든 테스트 계정은 @jinyphp.com 도메인을 사용합니다.
     */
    const EMAIL_DOMAIN = '@jinyphp.com';

    /**
     * 기본 사용자 비밀번호
     * 모든 테스트 계정에 동일한 비밀번호를 사용합니다.
     * 실제 운영환경에서는 각기 다른 안전한 비밀번호를 사용해야 합니다.
     */
    const DEFAULT_PASSWORD = 'password123!';

    /**
     * 콘솔 커맨드 인스턴스
     * 시더 실행 중 콘솔 출력을 위해 사용됩니다.
     */
    protected $command;

    /**
     * 콘솔 커맨드 인스턴스 설정
     *
     * @param \Illuminate\Console\Command $command 콘솔 커맨드 인스턴스
     * @return void
     */
    public function setCommand($command): void
    {
        $this->command = $command;
    }

    /**
     * 샤딩 시드 실행 메인 메서드
     *
     * 설정된 샤드 수만큼 반복하여 각 샤드 테이블에 테스트 사용자를 생성합니다.
     * 샤딩이 비활성화된 경우 시드를 실행하지 않습니다.
     *
     * @return void
     */
    public function run(): void
    {
        // 샤딩 설정 확인
        $shardingConfig = $this->getShardingConfig();

        if (!$shardingConfig['enabled']) {
            if ($this->command) {
                $this->command->info('샤딩이 비활성화되어 있습니다. 시드를 건너뜁니다.');
            }
            return;
        }

        if ($this->command) {
            $this->command->info("샤딩 시드 시작: {$shardingConfig['shard_count']}개 샤드에 각각 " . self::USERS_PER_SHARD . "명의 사용자 생성");
        }

        // 각 샤드 테이블에 사용자 생성
        for ($shardIndex = 1; $shardIndex <= $shardingConfig['shard_count']; $shardIndex++) {
            $this->seedShardTable($shardIndex);
        }

        $totalUsers = $shardingConfig['shard_count'] * self::USERS_PER_SHARD;
        if ($this->command) {
            $this->command->info("샤딩 시드 완료: 총 {$totalUsers}명의 사용자가 생성되었습니다.");
        }
    }

    /**
     * 샤딩 설정 정보 조회
     *
     * auth config에서 샤딩 관련 설정을 읽어옵니다.
     * 환경변수와 config 파일의 값을 모두 고려합니다.
     *
     * @return array 샤딩 설정 배열
     */
    private function getShardingConfig(): array
    {
        // Laravel config에서 admin.auth.sharding 설정 조회
        $authConfig = config('admin.auth.sharding', []);

        // 환경변수 우선 적용
        $enabled = env('SHARDING_ENABLE', $authConfig['enable'] ?? false);
        $shardCount = env('SHARDING_COUNT', $authConfig['shard_count'] ?? 2);

        return [
            'enabled' => $enabled,
            'shard_count' => $shardCount,
            'shard_key' => $authConfig['shard_key'] ?? 'uuid',
            'strategy' => $authConfig['strategy'] ?? 'hash'
        ];
    }

    /**
     * 특정 샤드 테이블에 사용자 데이터 생성
     *
     * 지정된 샤드 인덱스에 해당하는 테이블에 테스트 사용자들을 생성합니다.
     * 각 사용자는 고유한 UUID, 이메일, 사용자명을 가집니다.
     *
     * @param int $shardIndex 샤드 인덱스 (1부터 시작)
     * @return void
     */
    private function seedShardTable(int $shardIndex): void
    {
        // 샤드 테이블명 생성 (예: users_001, users_002)
        $shardNumber = str_pad($shardIndex, 3, '0', STR_PAD_LEFT);
        $tableName = "users_{$shardNumber}";

        if ($this->command) {
            $this->command->info("샤드 테이블 '{$tableName}' 시드 시작...");
        }

        // 테이블 존재 확인
        if (!$this->tableExists($tableName)) {
            if ($this->command) {
                $this->command->warn("테이블 '{$tableName}'이 존재하지 않습니다. 건너뜁니다.");
            }
            return;
        }

        // 기존 테스트 데이터 정리 (jinyphp.com 도메인 이메일만)
        $this->cleanupExistingTestData($tableName);

        // 배치 처리를 위한 사용자 데이터 배열
        $users = [];
        $currentTime = Carbon::now();

        // 각 샤드에 대해 연속된 번호로 사용자 생성
        $startIndex = ($shardIndex - 1) * self::USERS_PER_SHARD;

        for ($i = 0; $i < self::USERS_PER_SHARD; $i++) {
            $userIndex = $startIndex + $i;
            $users[] = $this->generateUserData($userIndex, $currentTime);
        }

        // 배치 인서트로 성능 최적화
        DB::table($tableName)->insert($users);

        if ($this->command) {
            $this->command->info("샤드 테이블 '{$tableName}': " . self::USERS_PER_SHARD . "명의 사용자 생성 완료");
        }
    }

    /**
     * 테이블 존재 여부 확인
     *
     * 지정된 테이블이 데이터베이스에 존재하는지 확인합니다.
     *
     * @param string $tableName 확인할 테이블명
     * @return bool 테이블 존재 여부
     */
    private function tableExists(string $tableName): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($tableName);
        } catch (\Exception $e) {
            if ($this->command) {
                $this->command->error("테이블 존재 확인 중 오류 발생: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * 기존 테스트 데이터 정리
     *
     * jinyphp.com 도메인을 사용하는 기존 테스트 계정들을 삭제합니다.
     * 실제 사용자 데이터는 보호됩니다.
     *
     * @param string $tableName 정리할 테이블명
     * @return void
     */
    private function cleanupExistingTestData(string $tableName): void
    {
        $deletedCount = DB::table($tableName)
            ->where('email', 'like', '%' . self::EMAIL_DOMAIN)
            ->delete();

        if ($deletedCount > 0 && $this->command) {
            $this->command->info("기존 테스트 데이터 {$deletedCount}건을 정리했습니다.");
        }
    }

    /**
     * 개별 사용자 데이터 생성
     *
     * 주어진 인덱스를 기반으로 테스트 사용자의 모든 필드 데이터를 생성합니다.
     * 각 사용자는 고유한 UUID와 이메일을 가지며, 일관된 형식을 따릅니다.
     *
     * @param int $index 사용자 인덱스 (0부터 시작)
     * @param Carbon $currentTime 생성 시간
     * @return array 사용자 데이터 배열
     */
    private function generateUserData(int $index, Carbon $currentTime): array
    {
        // 3자리 0패딩 인덱스 생성 (예: 000, 001, 002...)
        $paddedIndex = str_pad($index, 3, '0', STR_PAD_LEFT);

        // 이메일과 사용자명 생성
        $email = "test{$paddedIndex}" . self::EMAIL_DOMAIN;
        $username = "test{$paddedIndex}";
        $name = "테스트 사용자 {$paddedIndex}";

        return [
            // 고유 식별자 (UUID v4 사용)
            'uuid' => Str::uuid()->toString(),

            // 기본 사용자 정보
            'name' => $name,
            'email' => $email,
            'username' => $username,

            // 인증 관련 정보
            'password' => Hash::make(self::DEFAULT_PASSWORD),
            'email_verified_at' => $currentTime, // 테스트 계정은 즉시 인증 처리

            // 사용자 유형 및 상태
            'utype' => 'USR', // 일반 사용자
            'status' => 'active', // 활성 상태

            // 활동 로그
            'last_login_at' => null, // 아직 로그인하지 않음
            'last_activity_at' => $currentTime,

            // 타임스탬프
            'created_at' => $currentTime,
            'updated_at' => $currentTime,
            'deleted_at' => null, // 소프트 삭제 미적용

            // 기타
            'remember_token' => null,
        ];
    }

    /**
     * 생성된 사용자 데이터 검증
     *
     * 시드 완료 후 각 샤드 테이블의 데이터 정합성을 확인합니다.
     * 중복 이메일, UUID 등의 무결성 제약조건을 검증합니다.
     *
     * @return void
     */
    public function validateSeedData(): void
    {
        $shardingConfig = $this->getShardingConfig();

        if (!$shardingConfig['enabled']) {
            return;
        }

        if ($this->command) {
            $this->command->info("시드 데이터 검증 시작...");
        }

        for ($shardIndex = 1; $shardIndex <= $shardingConfig['shard_count']; $shardIndex++) {
            $shardNumber = str_pad($shardIndex, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            if (!$this->tableExists($tableName)) {
                continue;
            }

            // 테스트 사용자 수 확인
            $testUserCount = DB::table($tableName)
                ->where('email', 'like', '%' . self::EMAIL_DOMAIN)
                ->count();

            if ($this->command) {
                $this->command->info("테이블 '{$tableName}': 테스트 사용자 {$testUserCount}명");
            }

            // 중복 이메일 확인
            $duplicateEmails = DB::table($tableName)
                ->select('email', DB::raw('COUNT(*) as count'))
                ->where('email', 'like', '%' . self::EMAIL_DOMAIN)
                ->groupBy('email')
                ->having('count', '>', 1)
                ->get();

            if ($duplicateEmails->count() > 0 && $this->command) {
                $this->command->warn("테이블 '{$tableName}'에서 중복 이메일 발견: " . $duplicateEmails->count() . "건");
            }

            // 중복 UUID 확인
            $duplicateUuids = DB::table($tableName)
                ->select('uuid', DB::raw('COUNT(*) as count'))
                ->whereNotNull('uuid')
                ->groupBy('uuid')
                ->having('count', '>', 1)
                ->get();

            if ($duplicateUuids->count() > 0 && $this->command) {
                $this->command->warn("테이블 '{$tableName}'에서 중복 UUID 발견: " . $duplicateUuids->count() . "건");
            }
        }

        if ($this->command) {
            $this->command->info("시드 데이터 검증 완료");
        }
    }
}