<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Services\AccountLockoutService;

class LockoutResetCommand extends Command
{
    protected $signature = 'auth:lockout-reset 
                            {email : 사용자 이메일}
                            {--all : 모든 사용자 잠금 해제}';

    protected $description = '계정 잠금 해제 및 로그인 실패 횟수 초기화';

    protected $lockoutService;

    public function __construct(AccountLockoutService $lockoutService)
    {
        parent::__construct();
        $this->lockoutService = $lockoutService;
    }

    public function handle()
    {
        $email = $this->argument('email');
        $all = $this->option('all');

        if ($all) {
            // 모든 잠금 해제
            if ($this->confirm('모든 사용자의 계정 잠금을 해제하시겠습니까?')) {
                try {
                    DB::table('auth_account_lockouts')->delete();
                    DB::table('auth_login_attempts')
                        ->where('successful', false)
                        ->delete();
                    
                    $this->info("✅ 모든 사용자의 계정 잠금을 해제했습니다.");
                } catch (\Exception $e) {
                    $this->error("테이블에 접근할 수 없습니다.");
                    return 1;
                }
            }
        } else {
            // 특정 사용자 잠금 해제
            try {
                // 잠금 상태 확인
                $lockout = DB::table('auth_account_lockouts')
                    ->where('email', $email)
                    ->first();

                if ($lockout) {
                    if ($lockout->is_permanent) {
                        $this->warn("⚠️  영구 잠금 상태입니다.");
                    } else {
                        $this->info("⏰ 잠금 해제 시간: {$lockout->unlocks_at}");
                    }
                }

                // 잠금 해제
                $this->lockoutService->unlockByEmail($email, null, '관리자 직접 해제');
                
                // 로그인 실패 기록 삭제
                DB::table('auth_login_attempts')
                    ->where('email', $email)
                    ->where('successful', false)
                    ->delete();

                $this->info("✅ {$email}의 계정 잠금을 해제하고 실패 횟수를 초기화했습니다.");
                
            } catch (\Exception $e) {
                $this->error("처리 중 오류가 발생했습니다: " . $e->getMessage());
                return 1;
            }
        }

        return 0;
    }
}
