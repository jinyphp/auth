<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PasswordExpiryCommand extends Command
{
    protected $signature = 'auth:password-expiry 
                            {email : 사용자 이메일}
                            {--days= : 만료일 설정 (예: 90일 후)}
                            {--expire : 즉시 만료}
                            {--reset : 만료 해제}';

    protected $description = '비밀번호 만료일 관리';

    public function handle()
    {
        $email = $this->argument('email');
        $days = $this->option('days');
        $expire = $this->option('expire');
        $reset = $this->option('reset');

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("이메일 {$email}을 찾을 수 없습니다.");
            return 1;
        }

        try {
            // password_histories 테이블에서 마지막 비밀번호 변경일 확인
            $lastChange = DB::table('password_histories')
                ->where('user_id', $user->id)
                ->latest('changed_at')
                ->first();

            if ($expire) {
                // 즉시 만료 (91일 전으로 설정)
                DB::table('password_histories')
                    ->where('user_id', $user->id)
                    ->latest('changed_at')
                    ->update([
                        'changed_at' => now()->subDays(91),
                    ]);
                $this->info("✅ {$email}의 비밀번호를 즉시 만료 처리했습니다.");
                
            } elseif ($reset) {
                // 만료 해제 (오늘로 설정)
                DB::table('password_histories')
                    ->where('user_id', $user->id)
                    ->latest('changed_at')
                    ->update([
                        'changed_at' => now(),
                    ]);
                $this->info("✅ {$email}의 비밀번호 만료를 해제했습니다.");
                
            } elseif ($days) {
                // 특정 일수 후 만료
                $expireDate = now()->subDays(90 - intval($days));
                DB::table('password_histories')
                    ->where('user_id', $user->id)
                    ->latest('changed_at')
                    ->update([
                        'changed_at' => $expireDate,
                    ]);
                $this->info("✅ {$email}의 비밀번호가 {$days}일 후에 만료됩니다.");
                
            } else {
                // 현재 상태 확인
                if ($lastChange) {
                    $changedAt = \Carbon\Carbon::parse($lastChange->changed_at);
                    $daysPassed = $changedAt->diffInDays(now());
                    $daysLeft = 90 - $daysPassed;
                    
                    $this->info("📅 마지막 변경: {$changedAt->format('Y-m-d H:i:s')}");
                    $this->info("⏱️  경과 일수: {$daysPassed}일");
                    
                    if ($daysLeft > 0) {
                        $this->info("✅ 남은 일수: {$daysLeft}일");
                    } else {
                        $this->warn("⚠️  만료됨 (초과: " . abs($daysLeft) . "일)");
                    }
                } else {
                    $this->warn("비밀번호 변경 이력이 없습니다.");
                }
            }
        } catch (\Exception $e) {
            $this->error("password_histories 테이블이 없거나 접근할 수 없습니다.");
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
