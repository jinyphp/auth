<?php

namespace Jiny\Auth\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Models\UserProfile;
use Jiny\Auth\Models\ShardedUser;
use Jiny\Auth\Services\ShardingService;

class UserCreateCommand extends Command
{
    protected $signature = 'auth:user-create 
                            {email : 사용자 이메일}
                            {--name= : 사용자 이름}
                            {--password= : 비밀번호}
                            {--status=active : 계정 상태 (active|pending|blocked|inactive)}
                            {--verified : 이메일 인증됨}
                            {--admin : 관리자 계정으로 생성}';

    protected $description = '테스트용 사용자 생성';

    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        parent::__construct();
        $this->shardingService = $shardingService;
    }

    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?? explode('@', $email)[0];
        $password = $this->option('password') ?? 'Password123!';
        $status = $this->option('status');
        $verified = $this->option('verified');
        $admin = $this->option('admin');

        // 중복 확인
        $existing = $this->shardingService->isEnabled()
            ? $this->shardingService->getUserByEmail($email)
            : User::where('email', $email)->first();

        if ($existing) {
            $this->error("이메일 {$email}은 이미 사용 중입니다.");
            return 1;
        }

        // 사용자 데이터
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'utype' => $admin ? 'ADM' : 'USR',
            'account_status' => $status,  // status → account_status
            'email_verified_at' => $verified ? now() : null,
        ];

        try {
            DB::beginTransaction();

            // 사용자 생성
            if ($this->shardingService->isEnabled()) {
                $user = ShardedUser::createUser($userData);
            } else {
                $userData['uuid'] = (string) \Str::uuid();
                $user = User::create($userData);
            }

            // 프로필 생성
            UserProfile::create([
                'user_id' => $user->id ?? null,
                'user_uuid' => $user->uuid,
            ]);

            DB::commit();

            $this->info("✅ 사용자를 생성했습니다.");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->line("이메일:      {$email}");
            $this->line("비밀번호:    {$password}");
            $this->line("이름:        {$name}");
            $this->line("상태:        {$status}");
            $this->line("유형:        " . ($admin ? '관리자' : '일반'));
            $this->line("이메일 인증: " . ($verified ? '✅' : '❌'));
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("사용자 생성 실패: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
