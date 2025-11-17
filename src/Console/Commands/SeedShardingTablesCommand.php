<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;

/**
 * 샤딩 테이블 시드 명령어 클래스
 *
 * 이 명령어는 사용자 샤딩 테이블에 테스트 데이터를 생성하는
 * Artisan 명령어를 제공합니다.
 *
 * 사용법:
 * php artisan jiny:seed-sharding-tables
 * php artisan jiny:seed-sharding-tables --validate
 * php artisan jiny:seed-sharding-tables --force
 *
 * @package Jiny\Auth\Console\Commands
 * @author JinyPHP Team
 * @version 1.0.0
 */
class SeedShardingTablesCommand extends Command
{
    /**
     * 명령어 시그니처
     *
     * --validate: 시드 후 데이터 검증 실행
     * --force: 확인 없이 강제 실행 (운영환경에서도 실행)
     *
     * @var string
     */
    protected $signature = 'jiny:seed-sharding-tables
                           {--validate : 시드 완료 후 데이터 검증 실행}
                           {--force : 확인 프롬프트 없이 강제 실행}';

    /**
     * 명령어 설명
     *
     * @var string
     */
    protected $description = '사용자 샤딩 테이블에 테스트 계정 데이터를 생성합니다. 각 샤드당 30개 계정(test000@jinyphp.com 형식)이 생성됩니다.';

    /**
     * 명령어 실행 메인 메서드
     *
     * 사용자 확인 후 샤딩 테이블 시드를 실행합니다.
     * 운영환경에서는 추가 확인 절차를 거칩니다.
     *
     * @return int 명령어 실행 결과 코드 (0: 성공, 1: 실패)
     */
    public function handle(): int
    {
        try {
            // 환경 확인 및 사용자 동의
            if (!$this->confirmExecution()) {
                $this->info('시드 실행이 취소되었습니다.');
                return 0;
            }

            // 시드 실행 전 정보 출력
            $this->displayPreExecutionInfo();

            // 시드 실행
            $this->info('사용자 샤딩 테이블 시드를 시작합니다...');
            $this->newLine();

            // Seeder 클래스를 require하고 인스턴스 생성
            require_once(__DIR__ . '/../../../database/seeders/UserShardingSeeder.php');
            $seeder = new \Jiny\Auth\Database\Seeders\UserShardingSeeder();
            $seeder->setCommand($this); // 콘솔 출력을 위해 커맨드 인스턴스 전달
            $seeder->run();

            $this->newLine();
            $this->info('✅ 샤딩 테이블 시드가 성공적으로 완료되었습니다!');

            // 데이터 검증 실행 (옵션)
            if ($this->option('validate')) {
                $this->newLine();
                $this->info('🔍 시드 데이터 검증을 시작합니다...');
                $seeder->validateSeedData();
                $this->info('✅ 데이터 검증이 완료되었습니다!');
            }

            // 실행 후 안내사항 출력
            $this->displayPostExecutionInfo();

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ 시드 실행 중 오류가 발생했습니다: ' . $e->getMessage());
            $this->error('스택 트레이스: ' . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * 실행 확인 및 환경 검증
     *
     * 운영환경에서의 실행을 방지하고 사용자 확인을 받습니다.
     * --force 옵션이 주어진 경우 확인을 건너뜁니다.
     *
     * @return bool 실행 승인 여부
     */
    private function confirmExecution(): bool
    {
        // force 옵션이 설정된 경우 즉시 승인
        if ($this->option('force')) {
            $this->warn('⚠️  --force 옵션으로 인해 확인 절차를 건너뜁니다.');
            return true;
        }

        // 운영환경 경고
        if (app()->environment('production')) {
            $this->error('⚠️  운영환경에서 테스트 데이터 시드를 실행하려고 합니다!');
            $this->error('이 작업은 실제 데이터베이스에 테스트 계정을 추가합니다.');
            $this->newLine();

            if (!$this->confirm('정말로 운영환경에서 시드를 실행하시겠습니까?', false)) {
                return false;
            }
        }

        // 일반 확인
        $this->warn('이 명령어는 사용자 샤딩 테이블에 테스트 계정을 추가합니다.');
        $this->warn('기존의 test***@jinyphp.com 계정들은 삭제됩니다.');
        $this->newLine();

        return $this->confirm('계속 진행하시겠습니까?', true);
    }

    /**
     * 실행 전 정보 출력
     *
     * 시드 실행 전 현재 설정과 예상 결과를 표시합니다.
     *
     * @return void
     */
    private function displayPreExecutionInfo(): void
    {
        $this->newLine();
        $this->info('📋 실행 정보:');
        $this->line('┌─────────────────────────────────────────────┐');
        $this->line('│ 사용자 샤딩 테이블 시드 정보                 │');
        $this->line('├─────────────────────────────────────────────┤');

        // 환경 정보
        $environment = app()->environment();
        $this->line("│ 환경: {$environment}");

        // 샤딩 설정 정보
        $shardingEnabled = config('admin.auth.sharding.enable', false);
        $shardCount = config('admin.auth.sharding.shard_count', 2);

        $this->line("│ 샤딩 활성화: " . ($shardingEnabled ? '예' : '아니오'));
        $this->line("│ 샤드 수: {$shardCount}");

        // 생성될 데이터 정보
        $usersPerShard = 30; // UserShardingSeeder::USERS_PER_SHARD
        $totalUsers = $shardCount * $usersPerShard;
        $emailDomain = '@jinyphp.com'; // UserShardingSeeder::EMAIL_DOMAIN

        $this->line("│ 샤드당 사용자 수: {$usersPerShard}명");
        $this->line("│ 총 생성 사용자 수: {$totalUsers}명");
        $this->line("│ 이메일 형식: test000{$emailDomain}");
        $this->line("│ 기본 비밀번호: password123!");

        $this->line('└─────────────────────────────────────────────┘');
        $this->newLine();
    }

    /**
     * 실행 후 안내사항 출력
     *
     * 시드 완료 후 사용자에게 유용한 정보를 제공합니다.
     *
     * @return void
     */
    private function displayPostExecutionInfo(): void
    {
        $this->newLine();
        $this->info('📝 안내사항:');
        $this->line('┌─────────────────────────────────────────────┐');
        $this->line('│ 생성된 테스트 계정 정보                      │');
        $this->line('├─────────────────────────────────────────────┤');
        $this->line('│ 로그인 방법:                               │');
        $this->line('│ - 이메일: test000@jinyphp.com ~ test059@... │');
        $this->line('│ - 비밀번호: password123!                     │');
        $this->line('│                                            │');
        $this->line('│ 추가 명령어:                               │');
        $this->line('│ - 데이터 검증: --validate 옵션 사용         │');
        $this->line('│ - 강제 실행: --force 옵션 사용              │');
        $this->line('│                                            │');
        $this->line('│ 주의사항:                                  │');
        $this->line('│ - 테스트 계정은 즉시 이메일 인증 상태입니다  │');
        $this->line('│ - 실제 서비스에서는 계정을 삭제해 주세요     │');
        $this->line('└─────────────────────────────────────────────┘');
        $this->newLine();

        // 추가 명령어 예시
        $this->info('🔧 관련 명령어:');
        $this->line('php artisan jiny:seed-sharding-tables --validate  # 검증 포함 실행');
        $this->line('php artisan jiny:seed-sharding-tables --force     # 강제 실행');
        $this->line('php artisan migrate:fresh                         # 테이블 재생성');
        $this->newLine();
    }

    /**
     * 현재 샤딩 상태 확인
     *
     * 샤딩 설정과 테이블 상태를 확인하여 시드 실행 가능 여부를 판단합니다.
     *
     * @return array 상태 정보 배열
     */
    private function getShardingStatus(): array
    {
        $shardingEnabled = config('admin.auth.sharding.enable', false);
        $shardCount = config('admin.auth.sharding.shard_count', 2);

        $tables = [];
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";
            $tables[] = [
                'name' => $tableName,
                'exists' => \Schema::hasTable($tableName)
            ];
        }

        return [
            'enabled' => $shardingEnabled,
            'shard_count' => $shardCount,
            'tables' => $tables
        ];
    }

    /**
     * 샤딩 상태 정보 출력
     *
     * 현재 샤딩 테이블의 상태를 사용자에게 표시합니다.
     *
     * @return void
     */
    public function showShardingStatus(): void
    {
        $status = $this->getShardingStatus();

        $this->info('🔍 현재 샤딩 상태:');
        $this->line("샤딩 활성화: " . ($status['enabled'] ? '예' : '아니오'));
        $this->line("설정된 샤드 수: {$status['shard_count']}");
        $this->newLine();

        $this->info('📊 샤드 테이블 상태:');
        foreach ($status['tables'] as $table) {
            $statusIcon = $table['exists'] ? '✅' : '❌';
            $statusText = $table['exists'] ? '존재함' : '존재하지 않음';
            $this->line("{$statusIcon} {$table['name']}: {$statusText}");
        }
        $this->newLine();
    }
}