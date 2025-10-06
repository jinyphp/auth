<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Services\ShardingService;

class UserInfoCommand extends Command
{
    protected $signature = 'auth:user-info {email : 사용자 이메일}';

    protected $description = '사용자 계정 정보 조회';

    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        parent::__construct();
        $this->shardingService = $shardingService;
    }

    public function handle()
    {
        $email = $this->argument('email');

        if ($this->shardingService->isEnabled()) {
            $userData = $this->shardingService->getUserByEmail($email);
            if (!$userData) {
                $this->error("이메일 {$email}을 찾을 수 없습니다.");
                return 1;
            }
            $user = (object) $userData;
        } else {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->error("이메일 {$email}을 찾을 수 없습니다.");
                return 1;
            }
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("👤 사용자 정보");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("이름:        {$user->name}");
        $this->line("이메일:      {$user->email}");
        $this->line("UUID:        " . ($user->uuid ?: 'N/A'));
        $this->line("ID:          " . ($user->id ?? 'N/A'));
        $this->line("유형:        " . ($user->utype ?? 'N/A'));
        
        // 상태
        $account_status = $user->account_status ?? 'active';
        $statusColor = match($account_status) {
            'active' => '<fg=green>',
            'blocked' => '<fg=red>',
            'pending' => '<fg=yellow>',
            'inactive' => '<fg=gray>',
            default => '<fg=white>',
        };
        $this->line("상태:        {$statusColor}{$account_status}</>");
        
        // 이메일 인증
        $emailVerified = $user->email_verified_at ? '✅ 인증됨' : '❌ 미인증';
        $this->line("이메일 인증: {$emailVerified}");
        if ($user->email_verified_at) {
            $this->line("  인증일시:  {$user->email_verified_at}");
        }
        
        // 마지막 로그인
        if (isset($user->last_login_at) && $user->last_login_at) {
            $lastLogin = \Carbon\Carbon::parse($user->last_login_at);
            $this->line("마지막 로그인: {$lastLogin->format('Y-m-d H:i:s')} ({$lastLogin->diffForHumans()})");
        }
        
        // 생성일
        if (isset($user->created_at)) {
            $createdAt = \Carbon\Carbon::parse($user->created_at);
            $this->line("가입일:      {$createdAt->format('Y-m-d H:i:s')} ({$createdAt->diffForHumans()})");
        }

        // 계정 잠금 상태
        try {
            $lockout = DB::table('auth_account_lockouts')
                ->where('email', $email)
                ->first();
            
            if ($lockout) {
                $this->info("");
                $this->warn("🔒 계정 잠금 정보");
                $this->line("실패 횟수:   {$lockout->failed_attempts}회");
                $this->line("잠금 레벨:   Level {$lockout->lockout_level}");
                if ($lockout->is_permanent) {
                    $this->error("영구 잠금:   ⚠️  예");
                } else {
                    $this->line("해제 시간:   {$lockout->unlocks_at}");
                }
            }
        } catch (\Exception $e) {
            // 테이블 없으면 무시
        }

        // 로그인 시도 기록
        try {
            $attempts = DB::table('auth_login_attempts')
                ->where('email', $email)
                ->orderBy('attempted_at', 'desc')
                ->limit(5)
                ->get();
            
            if ($attempts->count() > 0) {
                $this->info("");
                $this->info("📝 최근 로그인 시도 (최근 5건)");
                foreach ($attempts as $attempt) {
                    $icon = $attempt->successful ? '✅' : '❌';
                    $time = \Carbon\Carbon::parse($attempt->attempted_at)->format('Y-m-d H:i:s');
                    $this->line("  {$icon} {$time} - {$attempt->ip_address}");
                }
            }
        } catch (\Exception $e) {
            // 테이블 없으면 무시
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        return 0;
    }
}
