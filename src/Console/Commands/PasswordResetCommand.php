<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Services\ShardingService;

class PasswordResetCommand extends Command
{
    protected $signature = 'auth:password-reset 
                            {email : 사용자 이메일}
                            {password? : 새 비밀번호 (미입력시 자동 생성)}
                            {--show : 생성된 비밀번호 표시}';

    protected $description = '사용자 비밀번호 재설정';

    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        parent::__construct();
        $this->shardingService = $shardingService;
    }

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $show = $this->option('show');

        // 비밀번호 자동 생성
        if (!$password) {
            $password = $this->generateSecurePassword();
        }

        // 비밀번호 규칙 검증
        $validation = $this->validatePassword($password);
        if (!$validation['valid']) {
            $this->error("❌ " . $validation['message']);
            return 1;
        }

        // 사용자 조회
        if ($this->shardingService->isEnabled()) {
            $userData = $this->shardingService->getUserByEmail($email);
            if (!$userData) {
                $this->error("이메일 {$email}을 찾을 수 없습니다.");
                return 1;
            }
            
            $this->shardingService->updateUser($userData->uuid, [
                'password' => Hash::make($password),
                'updated_at' => now(),
            ]);
            
        } else {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("이메일 {$email}을 찾을 수 없습니다.");
                return 1;
            }

            $user->password = Hash::make($password);
            $user->save();

            // 비밀번호 이력 저장
            try {
                DB::table('password_histories')->insert([
                    'user_id' => $user->id,
                    'password_hash' => Hash::make($password),
                    'changed_at' => now(),
                    'changed_by' => 'admin_command',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                // 테이블 없으면 무시
            }
        }

        $this->info("✅ {$email}의 비밀번호를 재설정했습니다.");
        
        if ($show || !$this->argument('password')) {
            $this->warn("🔑 새 비밀번호: {$password}");
            $this->warn("⚠️  안전한 곳에 보관하세요!");
        }

        return 0;
    }

    protected function generateSecurePassword()
    {
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghjkmnpqrstuvwxyz';
        $numbers = '23456789';
        $symbols = '!@#$%^&*';

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        return str_shuffle($password);
    }

    protected function validatePassword($password)
    {
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => '비밀번호는 8자 이상이어야 합니다.'];
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => '대문자를 포함해야 합니다.'];
        }

        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => '소문자를 포함해야 합니다.'];
        }

        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => '숫자를 포함해야 합니다.'];
        }

        if (!preg_match('/[!@#$%^&*]/', $password)) {
            return ['valid' => false, 'message' => '특수문자를 포함해야 합니다.'];
        }

        return ['valid' => true];
    }
}
