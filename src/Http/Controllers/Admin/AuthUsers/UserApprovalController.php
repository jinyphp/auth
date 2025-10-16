<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Services\ShardingService;

/**
 * 사용자별 승인 관리 컨트롤러
 *
 * 특정 사용자의 승인 관리 전용 페이지
 * - 승인 기록 히스토리 조회
 * - 승인 상태 변경 (대기->승인->거절->재신청->반려)
 */
class UserApprovalController extends Controller
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
     * 사용자별 승인 관리 페이지
     */
    public function __invoke(Request $request, $id)
    {
        $shardId = $request->get('shard_id');
        $user = null;
        $userTable = 'users';

        if ($shardId) {
            // 샤드 테이블에서 사용자 조회
            $shardTable = ShardTable::where('table_name', 'users')->first();
            $userTable = $shardTable->getShardTableName($shardId);
            $user = DB::table($userTable)->where('id', $id)->first();
        } else {
            // 일반 테이블에서 사용자 조회
            $user = AuthUser::find($id);
        }

        if (!$user) {
            return back()->with('error', '사용자를 찾을 수 없습니다.');
        }

        // 승인 로그 히스토리 조회
        $approvalLogs = $this->getApprovalLogs($user->id, $user->uuid ?? null, $shardId);

        // 승인 상태 통계
        $approvalStats = $this->getApprovalStats($user->id, $user->uuid ?? null, $shardId);

        return view('jiny-auth::admin.auth-users.approval', [
            'user' => $user,
            'shardId' => $shardId,
            'userTable' => $userTable,
            'approvalLogs' => $approvalLogs,
            'approvalStats' => $approvalStats,
            'config' => $this->config,
        ]);
    }

    /**
     * 사용자의 승인 로그 히스토리 조회
     */
    protected function getApprovalLogs($userId, $userUuid = null, $shardId = null)
    {
        $query = DB::table('user_approval_logs');

        if ($userUuid) {
            $query->where('user_uuid', $userUuid);
        } else {
            $query->where('user_id', $userId);
        }

        if ($shardId) {
            $query->where('shard_id', $shardId);
        }

        return $query->orderBy('processed_at', 'desc')->get();
    }

    /**
     * 승인 상태별 통계
     */
    protected function getApprovalStats($userId, $userUuid = null, $shardId = null)
    {
        $query = DB::table('user_approval_logs');

        if ($userUuid) {
            $query->where('user_uuid', $userUuid);
        } else {
            $query->where('user_id', $userId);
        }

        if ($shardId) {
            $query->where('shard_id', $shardId);
        }

        $stats = $query->select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->get()
            ->pluck('count', 'action')
            ->toArray();

        return [
            'total' => array_sum($stats),
            'approved' => $stats['approved'] ?? 0,
            'rejected' => $stats['rejected'] ?? 0,
            'pending' => $stats['pending'] ?? 0,
            'auto_approved' => $stats['auto_approved'] ?? 0,
        ];
    }

    /**
     * 승인 상태 변경 AJAX 처리
     */
    public function updateApprovalStatus(Request $request, $id)
    {
        $shardId = $request->get('shard_id');
        $newApproval = $request->input('approval'); // 'approved', 'rejected', 'pending'
        $comment = $request->input('comment', '관리자에 의한 승인 상태 변경');

        // 현재 로그인한 관리자 정보
        $adminUser = auth()->user();
        $adminUserId = $adminUser->id ?? null;
        $adminUserName = $adminUser->name ?? 'System';

        if ($shardId) {
            // 샤드 테이블에서 처리
            $shardTable = ShardTable::where('table_name', 'users')->first();
            $tableName = $shardTable->getShardTableName($shardId);

            $user = DB::table($tableName)->where('id', $id)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => '사용자를 찾을 수 없습니다.'], 404);
            }

            // 승인 상태 업데이트
            $updateData = [
                'approval' => $newApproval,
                'approval_at' => now(),
                'updated_at' => now(),
            ];

            // 승인 상태에 따른 계정 상태 자동 설정 (샤드 테이블은 status 컬럼 사용)
            if ($newApproval === 'approved') {
                $updateData['status'] = 'active';
            } elseif ($newApproval === 'rejected') {
                $updateData['status'] = 'inactive';
            }

            DB::table($tableName)
                ->where('id', $id)
                ->update($updateData);

            // 승인 로그 기록
            $this->recordApprovalLog([
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
                'shard_id' => $shardId,
                'email' => $user->email,
                'name' => $user->name,
                'action' => $newApproval,
                'comment' => $comment,
                'admin_user_id' => $adminUserId,
                'admin_user_name' => $adminUserName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'processed_at' => now(),
            ]);

        } else {
            // 일반 테이블에서 처리
            $user = AuthUser::findOrFail($id);

            // 승인 상태 업데이트
            $user->approval = $newApproval;
            $user->approval_at = now();

            // 승인 상태에 따른 계정 상태 자동 설정
            if ($newApproval === 'approved') {
                $user->account_status = 'active';
            } elseif ($newApproval === 'rejected') {
                $user->account_status = 'inactive';
            }

            $user->save();

            // 승인 로그 기록
            $this->recordApprovalLog([
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
                'shard_id' => null,
                'email' => $user->email,
                'name' => $user->name,
                'action' => $newApproval,
                'comment' => $comment,
                'admin_user_id' => $adminUserId,
                'admin_user_name' => $adminUserName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'processed_at' => now(),
            ]);
        }

        $statusMessage = $this->getStatusMessage($newApproval);

        return response()->json([
            'success' => true,
            'message' => $statusMessage,
            'user' => [
                'id' => isset($user) ? $user->id : null,
                'approval' => $newApproval,
                'approval_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]
        ]);
    }

    /**
     * 상태 메시지 생성
     */
    protected function getStatusMessage($approvalStatus)
    {
        return match($approvalStatus) {
            'approved' => '사용자가 승인되었습니다.',
            'rejected' => '사용자의 승인이 거부되었습니다.',
            'pending' => '사용자가 승인 대기 상태로 변경되었습니다.',
            default => '승인 상태가 변경되었습니다.',
        };
    }

    /**
     * 승인 로그 기록
     */
    protected function recordApprovalLog(array $logData)
    {
        try {
            DB::table('user_approval_logs')->insert([
                'user_id' => $logData['user_id'],
                'user_uuid' => $logData['user_uuid'],
                'shard_id' => $logData['shard_id'],
                'email' => $logData['email'],
                'name' => $logData['name'],
                'action' => $logData['action'],
                'comment' => $logData['comment'],
                'admin_user_id' => $logData['admin_user_id'],
                'admin_user_name' => $logData['admin_user_name'],
                'ip_address' => $logData['ip_address'],
                'user_agent' => $logData['user_agent'],
                'processed_at' => $logData['processed_at'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info('승인 로그 기록 성공', [
                'user_id' => $logData['user_id'],
                'user_uuid' => $logData['user_uuid'],
                'action' => $logData['action'],
                'admin_user_name' => $logData['admin_user_name'],
            ]);

        } catch (\Exception $e) {
            \Log::error('승인 로그 기록 실패', [
                'error' => $e->getMessage(),
                'log_data' => $logData,
            ]);
        }
    }
}