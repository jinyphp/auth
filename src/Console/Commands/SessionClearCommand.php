<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SessionClearCommand extends Command
{
    protected $signature = 'auth:session-clear 
                            {email? : 사용자 이메일 (선택)}
                            {--all : 모든 세션 삭제}
                            {--expired : 만료된 세션만 삭제}';

    protected $description = '세션 및 JWT 토큰 삭제';

    public function handle()
    {
        $email = $this->argument('email');
        $all = $this->option('all');
        $expired = $this->option('expired');

        try {
            if ($all) {
                // 모든 세션 삭제
                if ($this->confirm('모든 사용자의 세션을 삭제하시겠습니까?')) {
                    DB::table('sessions')->delete();
                    $this->info("✅ 모든 세션을 삭제했습니다.");
                    
                    // JWT 토큰도 폐기
                    try {
                        DB::table('jwt_tokens')->update([
                            'revoked' => true,
                            'revoked_at' => now(),
                        ]);
                        $this->info("✅ 모든 JWT 토큰을 폐기했습니다.");
                    } catch (\Exception $e) {
                        // JWT 테이블 없으면 무시
                    }
                }
                
            } elseif ($expired) {
                // 만료된 세션만 삭제
                $deleted = DB::table('sessions')
                    ->where('last_activity', '<', now()->subMinutes(120)->timestamp)
                    ->delete();
                $this->info("✅ 만료된 세션 {$deleted}개를 삭제했습니다.");
                
                // 만료된 JWT 토큰 폐기
                try {
                    $tokenDeleted = DB::table('jwt_tokens')
                        ->where('expires_at', '<', now())
                        ->where('revoked', false)
                        ->update([
                            'revoked' => true,
                            'revoked_at' => now(),
                        ]);
                    $this->info("✅ 만료된 JWT 토큰 {$tokenDeleted}개를 폐기했습니다.");
                } catch (\Exception $e) {
                    // JWT 테이블 없으면 무시
                }
                
            } elseif ($email) {
                // 특정 사용자 세션 삭제
                $user = \App\Models\User::where('email', $email)->first();
                
                if (!$user) {
                    $this->error("이메일 {$email}을 찾을 수 없습니다.");
                    return 1;
                }

                // 세션 삭제
                $deleted = DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();
                $this->info("✅ {$email}의 세션 {$deleted}개를 삭제했습니다.");
                
                // JWT 토큰 폐기
                try {
                    $tokenDeleted = DB::table('jwt_tokens')
                        ->where('user_id', $user->id)
                        ->where('revoked', false)
                        ->update([
                            'revoked' => true,
                            'revoked_at' => now(),
                        ]);
                    $this->info("✅ {$email}의 JWT 토큰 {$tokenDeleted}개를 폐기했습니다.");
                } catch (\Exception $e) {
                    // JWT 테이블 없으면 무시
                }
                
            } else {
                $this->error('이메일을 입력하거나 --all 또는 --expired 옵션을 사용하세요.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("오류 발생: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
