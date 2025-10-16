<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Services\ShardingService;

/**
 * 사용자 상태 토글 컨트롤러
 *
 * 사용자의 account_status를 active ↔ inactive로 전환
 * approval 상태와 user_approval_logs 기록도 함께 관리
 */
class ToggleStatusController extends Controller
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
     * 사용자 상태 토글
     */
    public function __invoke(Request $request, $id)
    {
        $shardId = $request->get('shard_id');
        $newStatus = $request->input('status'); // 'active' or 'inactive' or 'suspended'
        $approvalAction = $request->input('approval_action'); // 'approved' or 'rejected' or null
        $comment = $request->input('comment', '관리자에 의한 상태 변경');

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
                return back()->with('error', '사용자를 찾을 수 없습니다.');
            }

            // 상태와 승인 정보 업데이트 (샤드 테이블은 status 컬럼 사용)
            $updateData = [
                'status' => $newStatus,
                'updated_at' => now(),
            ];

            // approval 관련 업데이트 결정
            if ($approvalAction || $this->shouldUpdateApproval($newStatus)) {
                $approval = $this->determineApprovalStatus($newStatus, $approvalAction);
                $updateData['approval'] = $approval;
                if ($approval !== 'pending') {
                    $updateData['approval_at'] = now();
                }
            }

            DB::table($tableName)
                ->where('id', $id)
                ->update($updateData);

            // 승인 로그 기록
            if (isset($approval)) {
                $this->recordApprovalLog([
                    'user_id' => $user->id,
                    'user_uuid' => $user->uuid ?? null,
                    'shard_id' => $shardId,
                    'email' => $user->email,
                    'name' => $user->name,
                    'action' => $approval === 'approved' ? 'approved' : 'rejected',
                    'comment' => $comment,
                    'admin_user_id' => $adminUserId,
                    'admin_user_name' => $adminUserName,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'processed_at' => now(),
                ]);
            }

            $statusMessage = $this->getStatusMessage($newStatus, $approval ?? null);

            // AJAX 요청인 경우 JSON 응답
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $statusMessage,
                    'user' => [
                        'id' => $user->id,
                        'account_status' => $newStatus,
                        'approval' => $approval ?? null,
                        'updated_at' => now()->toDateTimeString(),
                    ]
                ]);
            }

            return back()->with('success', $statusMessage);

        } else {
            // 일반 테이블에서 처리
            $user = AuthUser::findOrFail($id);

            // 상태 업데이트
            $user->account_status = $newStatus;

            // approval 관련 업데이트 결정
            if ($approvalAction || $this->shouldUpdateApproval($newStatus)) {
                $approval = $this->determineApprovalStatus($newStatus, $approvalAction);
                $user->approval = $approval;
                if ($approval !== 'pending') {
                    $user->approval_at = now();
                }
            }

            $user->save();

            // 승인 로그 기록
            if (isset($approval)) {
                $this->recordApprovalLog([
                    'user_id' => $user->id,
                    'user_uuid' => $user->uuid ?? null,
                    'shard_id' => null,
                    'email' => $user->email,
                    'name' => $user->name,
                    'action' => $approval === 'approved' ? 'approved' : 'rejected',
                    'comment' => $comment,
                    'admin_user_id' => $adminUserId,
                    'admin_user_name' => $adminUserName,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'processed_at' => now(),
                ]);
            }

            $statusMessage = $this->getStatusMessage($newStatus, $approval ?? null);

            // AJAX 요청인 경우 JSON 응답
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $statusMessage,
                    'user' => [
                        'id' => $user->id,
                        'account_status' => $newStatus,
                        'approval' => $approval ?? null,
                        'updated_at' => $user->updated_at->toDateTimeString(),
                    ]
                ]);
            }

            return back()->with('success', $statusMessage);
        }
    }

    /**
     * 상태 변경에 따라 승인 상태를 업데이트해야 하는지 결정
     */
    protected function shouldUpdateApproval($newStatus)
    {
        // inactive나 suspended 상태로 변경 시 승인을 거부로 처리
        return in_array($newStatus, ['inactive', 'suspended']);
    }

    /**
     * 새로운 승인 상태 결정
     */
    protected function determineApprovalStatus($newStatus, $approvalAction)
    {
        // 명시적인 승인 액션이 있으면 우선 적용
        if ($approvalAction) {
            return $approvalAction === 'approved' ? 'approved' : 'rejected';
        }

        // 상태에 따른 자동 승인 상태 결정
        return match($newStatus) {
            'active' => 'approved',
            'inactive', 'suspended' => 'rejected',
            default => 'pending',
        };
    }

    /**
     * 상태 메시지 생성
     */
    protected function getStatusMessage($newStatus, $approval = null)
    {
        $statusMessage = match($newStatus) {
            'active' => '활성화',
            'inactive' => '비활성화',
            'suspended' => '정지',
            default => '변경',
        };

        if ($approval) {
            $approvalMessage = match($approval) {
                'approved' => '승인',
                'rejected' => '거부',
                'pending' => '대기',
                default => '',
            };

            if ($approvalMessage) {
                return "사용자 계정이 {$statusMessage}되고 승인이 {$approvalMessage}되었습니다.";
            }
        }

        return "사용자 계정이 {$statusMessage}되었습니다.";
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
