<?php

namespace Jiny\Auth\Http\Controllers\Admin\AccountLockout;

use App\Http\Controllers\Controller;
use Jiny\Auth\Services\AccountLockoutService;
use Jiny\Auth\Services\ShardingService;

/**
 * 관리자 - 계정 잠금 상세 컨트롤러
 */
class ShowController extends Controller
{
    protected $lockoutService;
    protected $shardingService;
    protected $config;

    public function __construct(
        AccountLockoutService $lockoutService,
        ShardingService $shardingService
    ) {
        $this->lockoutService = $lockoutService;
        $this->shardingService = $shardingService;
        $this->middleware(['auth', 'admin']);

        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/AccountLockout.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.lockout.show',
            'title' => $showConfig['title'] ?? '계정 잠금 상세',
            'subtitle' => $showConfig['subtitle'] ?? '잠금 정보 조회',
        ];
    }

    public function __invoke($id)
    {
        $lockout = \DB::table('account_lockouts')->where('id', $id)->first();

        if (!$lockout) {
            return redirect()->route('admin.lockouts.index')
                ->with('error', '잠금 정보를 찾을 수 없습니다.');
        }

        // 사용자 정보
        $user = null;
        if ($lockout->user_uuid) {
            if ($this->shardingService->isEnabled()) {
                $user = \Jiny\Auth\Models\ShardedUser::findByUuid($lockout->user_uuid);
            } else {
                $user = \App\Models\User::where('uuid', $lockout->user_uuid)->first();
            }
        }

        // 로그인 시도 이력
        $loginAttempts = \DB::table('auth_login_attempts')
            ->where('email', $lockout->email)
            ->orderBy('attempted_at', 'desc')
            ->limit(20)
            ->get();

        // 잠금 이력
        $lockoutHistory = $this->lockoutService->getLockoutHistory($lockout->email);

        return view($this->config['view'], compact('lockout', 'user', 'loginAttempts', 'lockoutHistory'));
    }
}