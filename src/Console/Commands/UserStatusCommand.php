<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Jiny\Auth\Services\ShardingService;

class UserStatusCommand extends Command
{
    protected $signature = 'auth:user-status 
                            {email : 사용자 이메일}
                            {status : 변경할 상태 (active|pending|blocked|inactive)}
                            {--reason= : 변경 사유}';

    protected $description = '사용자 계정 상태 변경';

    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        parent::__construct();
        $this->shardingService = $shardingService;
    }

    public function handle()
    {
        $email = $this->argument('email');
        $status = $this->argument('status');
        $reason = $this->option('reason') ?? '관리자 직접 변경';

        // 상태 값 검증
        if (!in_array($status, ['active', 'pending', 'blocked', 'inactive'])) {
            $this->error('유효하지 않은 상태값입니다. (active|pending|blocked|inactive)');
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
                'account_status' => $status,
                'updated_at' => now(),
            ]);
            
            $this->info("✅ 사용자 {$email}의 상태를 {$status}로 변경했습니다.");
            
        } else {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("이메일 {$email}을 찾을 수 없습니다.");
                return 1;
            }

            $oldStatus = $user->account_status;
            $user->account_status = $status;
            $user->save();

            $this->info("✅ 사용자 {$email}의 상태를 {$oldStatus} → {$status}로 변경했습니다.");
            $this->info("📝 사유: {$reason}");
        }

        return 0;
    }
}
