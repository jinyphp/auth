<?php

namespace Jiny\Auth\Http\Controllers\Auth\Approval;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Services\ShardingService;

/**
 * 승인 대기 상태 표시 컨트롤러
 */
class PendingController extends Controller
{
    protected $shardingService;
    protected $config;
    protected $configPath;

    public function __construct(ShardingService $shardingService)
    {
        $this->shardingService = $shardingService;
        $this->configPath = dirname(__DIR__, 5) . '/config/setting.json';
        $this->config = $this->loadSettings();
    }

    /**
     * JSON 설정 파일에서 설정 읽기
     */
    private function loadSettings()
    {
        if (file_exists($this->configPath)) {
            try {
                $jsonContent = file_get_contents($this->configPath);
                $settings = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $settings;
                }

                \Log::error('JSON 파싱 오류: ' . json_last_error_msg());
            } catch (\Exception $e) {
                \Log::error('설정 파일 읽기 오류: ' . $e->getMessage());
            }
        }

        return [];
    }

    /**
     * 승인 대기 페이지 표시
     */
    public function __invoke(Request $request)
    {
        // 세션에서 승인 대기 사용자 정보 가져오기
        $pendingUser = session('pending_user');

        \Log::info('승인 대기 페이지 접근', [
            'has_pending_user' => !empty($pendingUser),
            'session_id' => session()->getId(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip()
        ]);

        // 세션에 pending_user가 없는 경우 복구 시도
        if (!$pendingUser) {
            \Log::warning('승인 대기 사용자 정보 없음 - 복구 시도', [
                'session_data' => array_keys(session()->all()),
                'requested_url' => $request->fullUrl()
            ]);

            // URL 쿼리 파라미터에서 사용자 정보 확인 (복구용)
            $userEmail = $request->get('email');
            $userUuid = $request->get('uuid');

            if ($userEmail || $userUuid) {
                $pendingUser = $this->attemptUserRecovery($userEmail, $userUuid);
                if ($pendingUser) {
                    // 복구된 사용자 정보를 세션에 저장
                    session(['pending_user' => $pendingUser]);
                    \Log::info('승인 대기 사용자 정보 복구 성공', [
                        'email' => $pendingUser['email'] ?? null,
                        'uuid' => $pendingUser['uuid'] ?? null
                    ]);
                }
            }
        }

        // 여전히 pending_user가 없는 경우
        if (!$pendingUser) {
            return redirect()->route('login')
                ->with('info', '승인 대기 페이지에 접근하려면 먼저 로그인해주세요.')
                ->with('approval_help', true);
        }

        // 최신 승인 로그 조회
        $approvalLogs = $this->getApprovalLogs($pendingUser);

        // 사용자 정보 업데이트 (샤딩 지원)
        $currentUser = $this->getCurrentUserStatus($pendingUser);

        // 승인 완료된 경우 자동으로 로그인 페이지로 이동
        if ($currentUser && $currentUser->status === 'active') {
            session()->forget('pending_user'); // 세션 정리
            return redirect()->route('login')
                ->with('success', '계정이 승인되었습니다! 이제 로그인할 수 있습니다.');
        }

        // 뷰 데이터 준비
        $viewData = [
            'title' => '계정 승인 대기',
            'subtitle' => '관리자의 승인을 기다리고 있습니다',
            'user' => $pendingUser,
            'current_user' => $currentUser,
            'approval_logs' => $approvalLogs,
            'config' => $this->config,
            'has_valid_session' => true, // JavaScript에서 세션 유효성 확인용
        ];

        return view($this->config['approval']['approval_view'] ?? 'jiny-auth::auth.approval.pending', $viewData);
    }

    /**
     * 승인 로그 조회
     */
    protected function getApprovalLogs($pendingUser)
    {
        try {
            $query = DB::table('user_approval_logs')
                ->orderBy('created_at', 'desc')
                ->limit(10);

            // 샤딩 활성화 시 uuid로 조회, 비활성화 시 user_id로 조회
            if (($this->config['sharding']['enable'] ?? false) && isset($pendingUser['uuid'])) {
                $query->where('user_uuid', $pendingUser['uuid']);
            } elseif (isset($pendingUser['id'])) {
                $query->where('user_id', $pendingUser['id']);
            } else {
                return collect();
            }

            $logs = $query->get();

            return $logs->map(function ($log) {
                $log->processed_at_formatted = $log->processed_at
                    ? \Carbon\Carbon::parse($log->processed_at)->format('Y년 m월 d일 H시 i분')
                    : null;
                $log->created_at_formatted = \Carbon\Carbon::parse($log->created_at)->format('Y년 m월 d일 H시 i분');
                $log->processed_at_diff = $log->processed_at
                    ? \Carbon\Carbon::parse($log->processed_at)->diffForHumans()
                    : null;
                $log->created_at_diff = \Carbon\Carbon::parse($log->created_at)->diffForHumans();

                return $log;
            });

        } catch (\Exception $e) {
            \Log::warning('승인 로그 조회 실패', [
                'error' => $e->getMessage(),
                'user' => $pendingUser
            ]);
            return collect();
        }
    }

    /**
     * 현재 사용자 상태 조회
     */
    protected function getCurrentUserStatus($pendingUser)
    {
        try {
            // 샤딩 활성화 시 ShardingService 사용
            if (($this->config['sharding']['enable'] ?? false) && isset($pendingUser['uuid'])) {
                $userData = $this->shardingService->getUserByUuid($pendingUser['uuid']);
                return $userData;
            }

            // 샤딩 비활성화 시 기본 users 테이블 사용
            if (isset($pendingUser['id'])) {
                return DB::table('users')->where('id', $pendingUser['id'])->first();
            }

            return null;

        } catch (\Exception $e) {
            \Log::warning('현재 사용자 상태 조회 실패', [
                'error' => $e->getMessage(),
                'user' => $pendingUser
            ]);
            return null;
        }
    }

    /**
     * 승인 상태 새로고침 (AJAX)
     */
    public function refresh(Request $request)
    {
        $pendingUser = session('pending_user');

        if (!$pendingUser) {
            return response()->json([
                'success' => false,
                'message' => '사용자 정보를 찾을 수 없습니다.'
            ], 404);
        }

        $currentUser = $this->getCurrentUserStatus($pendingUser);

        if (!$currentUser) {
            return response()->json([
                'success' => false,
                'message' => '사용자 상태를 조회할 수 없습니다.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'status' => $currentUser->status ?? 'unknown',
            'updated_at' => $currentUser->updated_at ?? null,
            'message' => $this->getStatusMessage($currentUser->status ?? 'unknown')
        ]);
    }

    /**
     * 상태별 메시지 반환
     */
    protected function getStatusMessage($status)
    {
        switch ($status) {
            case 'active':
                return '계정이 승인되었습니다! 이제 로그인할 수 있습니다.';
            case 'pending':
                return '승인 대기 중입니다.';
            case 'rejected':
                return '계정 승인이 거부되었습니다.';
            case 'blocked':
                return '계정이 차단되었습니다.';
            case 'inactive':
                return '계정이 비활성화되었습니다.';
            default:
                return '상태를 확인할 수 없습니다.';
        }
    }

    /**
     * 사용자 복구 시도 (세션 만료 시)
     */
    protected function attemptUserRecovery($email = null, $uuid = null)
    {
        if (!$email && !$uuid) {
            return null;
        }

        try {
            // 샤딩 활성화 시 ShardingService 사용
            if (($this->config['sharding']['enable'] ?? false) && $uuid) {
                $userData = $this->shardingService->getUserByUuid($uuid);

                if ($userData && isset($userData->status) && $userData->status === 'pending') {
                    return [
                        'id' => $userData->id ?? null,
                        'uuid' => $userData->uuid ?? null,
                        'name' => $userData->name ?? null,
                        'email' => $userData->email ?? null,
                        'created_at' => $userData->created_at ?? null,
                        'status' => $userData->status ?? null,
                    ];
                }
            }

            // 샤딩 비활성화 시 또는 email로 검색 시
            if ($email) {
                $user = null;

                // 기본 users 테이블 검색
                try {
                    $user = DB::table('users')
                        ->where('email', $email)
                        ->where('status', 'pending')
                        ->first();
                } catch (\Exception $e) {
                    // 테이블이 없을 수 있음
                }

                // 샤딩 테이블들 검색
                if (!$user && ($this->config['sharding']['enable'] ?? false)) {
                    $shardTables = ['users_001', 'users_002'];
                    foreach ($shardTables as $table) {
                        try {
                            $user = DB::table($table)
                                ->where('email', $email)
                                ->where('status', 'pending')
                                ->first();
                            if ($user) break;
                        } catch (\Exception $e) {
                            // 테이블이 없을 수 있음
                            continue;
                        }
                    }
                }

                if ($user) {
                    return [
                        'id' => $user->id ?? null,
                        'uuid' => $user->uuid ?? null,
                        'name' => $user->name ?? null,
                        'email' => $user->email ?? null,
                        'created_at' => $user->created_at ?? null,
                        'status' => $user->status ?? null,
                    ];
                }
            }

            return null;

        } catch (\Exception $e) {
            \Log::warning('사용자 복구 실패', [
                'error' => $e->getMessage(),
                'email' => $email,
                'uuid' => $uuid
            ]);
            return null;
        }
    }
}