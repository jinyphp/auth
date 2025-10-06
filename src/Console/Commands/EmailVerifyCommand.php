<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Jiny\Auth\Services\ShardingService;

class EmailVerifyCommand extends Command
{
    protected $signature = 'auth:email-verify 
                            {email : 사용자 이메일}
                            {--unverify : 인증 상태 해제}';

    protected $description = '이메일 인증 상태 변경';

    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        parent::__construct();
        $this->shardingService = $shardingService;
    }

    public function handle()
    {
        $email = $this->argument('email');
        $unverify = $this->option('unverify');

        // 사용자 조회
        if ($this->shardingService->isEnabled()) {
            $userData = $this->shardingService->getUserByEmail($email);
            if (!$userData) {
                $this->error("이메일 {$email}을 찾을 수 없습니다.");
                return 1;
            }
            
            $this->shardingService->updateUser($userData->uuid, [
                'email_verified_at' => $unverify ? null : now(),
                'updated_at' => now(),
            ]);
            
        } else {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("이메일 {$email}을 찾을 수 없습니다.");
                return 1;
            }

            if ($unverify) {
                $user->email_verified_at = null;
                $this->info("✅ {$email}의 이메일 인증을 해제했습니다.");
            } else {
                $user->email_verified_at = now();
                $this->info("✅ {$email}의 이메일을 인증했습니다.");
            }
            
            $user->save();
        }

        return 0;
    }
}
