<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 샤딩된 사용자 테이블 생성
     *
     * users_001, users_002, ... users_010 형태로 생성
     */
    public function up(): void
    {
        // 샤딩 설정 확인
        $shardCount = config('admin.auth.sharding.shard_count', 2);
        $enabled = config('admin.auth.sharding.enable', false);

        if (!$enabled) {
            return; // 샤딩 비활성화 시 테이블 생성 안 함
        }

        // 샤드 테이블 생성
        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            Schema::create($tableName, function (Blueprint $table) {
                $table->id(); // 샤드 내 로컬 ID
                $table->uuid('uuid')->unique(); // 전역 고유 식별자
                $table->string('name');
                $table->string('email')->unique();
                $table->string('username')->unique()->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('utype', 10)->default('USR')->index();
                $table->string('status', 20)->default('active')->index();
                $table->rememberToken();
                $table->timestamp('last_login_at')->nullable();
                $table->timestamp('last_activity_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // 인덱스
                $table->index('email');
                $table->index('username');
                $table->index('uuid');
                $table->index('created_at');
            });
        }

        // 샤딩 설정 저장
        DB::table('user_sharding_configs')->insert([
            'table_name' => 'users',
            'shard_count' => $shardCount,
            'shard_key' => 'uuid',
            'shard_strategy' => 'hash',
            'is_active' => true,
            'description' => '사용자 테이블 샤딩 - UUID 기반 해시',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 테스트 사용자 시드 자동 실행
        $this->seedTestUsers($shardCount);
    }

    public function down(): void
    {
        $shardCount = config('admin.auth.sharding.shard_count', 2);

        for ($i = 1; $i <= $shardCount; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";
            Schema::dropIfExists($tableName);
        }

        DB::table('user_sharding_configs')->where('table_name', 'users')->delete();
    }

    /**
     * 테스트 사용자 시드 실행
     *
     * 샤드 테이블 생성 후 자동으로 테스트 사용자를 생성합니다.
     * 각 샤드당 30명의 test000@jinyphp.com 형식 계정을 생성합니다.
     *
     * @param int $shardCount 생성된 샤드 수
     * @return void
     */
    private function seedTestUsers(int $shardCount): void
    {
        try {
            // 환경별 시드 실행 여부 확인
            $shouldSeed = $this->shouldRunSeeder();

            if (!$shouldSeed) {
                echo "⏭️  테스트 사용자 시드를 건너뜁니다. (환경: " . app()->environment() . ")\n";
                return;
            }

            echo "🌱 테스트 사용자 시드를 시작합니다...\n";
            echo "📊 대상: {$shardCount}개 샤드 테이블\n";

            // UserShardingSeeder 직접 실행
            $seederPath = __DIR__ . '/../seeders/UserShardingSeeder.php';

            if (file_exists($seederPath)) {
                require_once($seederPath);

                $seeder = new \Jiny\Auth\Database\Seeders\UserShardingSeeder();
                $seeder->run();

                $totalUsers = $shardCount * 30; // USERS_PER_SHARD = 30
                echo "✅ 테스트 사용자 시드 완료: 총 {$totalUsers}명 생성\n";
                echo "📧 로그인 정보: test000@jinyphp.com ~ test" . str_pad($totalUsers - 1, 3, '0', STR_PAD_LEFT) . "@jinyphp.com\n";
                echo "🔐 비밀번호: password123!\n";

            } else {
                echo "⚠️  UserShardingSeeder 파일을 찾을 수 없습니다: {$seederPath}\n";
            }

        } catch (\Exception $e) {
            echo "❌ 테스트 사용자 시드 실행 중 오류 발생: " . $e->getMessage() . "\n";
            echo "💡 수동으로 시드를 실행하려면: php artisan jiny:seed-sharding-tables\n";
            // 마이그레이션 자체는 실패하지 않도록 예외를 다시 던지지 않음
        }
    }

    /**
     * 시더 실행 여부 결정
     *
     * 환경과 설정에 따라 자동 시드 실행 여부를 결정합니다.
     *
     * @return bool 시드 실행 여부
     */
    private function shouldRunSeeder(): bool
    {
        // 환경변수로 시드 실행 제어 (기본값: local, testing 환경에서만 실행)
        $autoSeed = env('MIGRATION_AUTO_SEED', null);

        if ($autoSeed !== null) {
            return filter_var($autoSeed, FILTER_VALIDATE_BOOLEAN);
        }

        // 기본적으로 local, testing 환경에서만 자동 시드 실행
        $allowedEnvironments = ['local', 'testing'];
        return in_array(app()->environment(), $allowedEnvironments);
    }
};