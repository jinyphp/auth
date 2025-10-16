<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Services\ShardingService;

/**
 * 사용자 승인 전용 컨트롤러
 *
 * 사용자의 승인/거부를 전담하고 로그를 기록
 */
class ApprovalController extends Controller
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
     * 사용자 승인 처리
     */
    public function approve(Request $request, $id)
    {
        return $this->processApproval($request, $id, 'approved', 'active');
    }

    /**
     * 사용자 승인 거부 처리
     */
    public function reject(Request $request, $id)
    {
        return $this->processApproval($request, $id, 'rejected', 'inactive');
    }

    /**
     * 사용자 승인 대기 처리
     */
    public function pending(Request $request, $id)
    {
        return $this->processApproval($request, $id, 'pending', 'inactive');
    }

    /**
     * 승인 처리 공통 로직
     */
    protected function processApproval(Request $request, $id, $approvalStatus, $accountStatus)
    {
        $shardId = $request->get('shard_id');
        $comment = $request->input('comment', $this->getDefaultComment($approvalStatus));

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
                'status' => $accountStatus,
                'approval' => $approvalStatus,
                'approval_at' => now(),
                'updated_at' => now(),
            ];

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
                'action' => $approvalStatus,
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

            // 상태 업데이트
            $user->account_status = $accountStatus;
            $user->approval = $approvalStatus;
            $user->approval_at = now();
            $user->save();

            // 승인 로그 기록
            $this->recordApprovalLog([
                'user_id' => $user->id,
                'user_uuid' => $user->uuid ?? null,
                'shard_id' => null,
                'email' => $user->email,
                'name' => $user->name,
                'action' => $approvalStatus,
                'comment' => $comment,
                'admin_user_id' => $adminUserId,
                'admin_user_name' => $adminUserName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'processed_at' => now(),
            ]);
        }

        $statusMessage = $this->getStatusMessage($approvalStatus);

        // AJAX 요청인 경우 JSON 응답
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $statusMessage,
                'user' => [
                    'id' => isset($user) ? $user->id : null,
                    'account_status' => $accountStatus,
                    'approval' => $approvalStatus,
                    'approval_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ]
            ]);
        }

        return back()->with('success', $statusMessage);
    }

    /**
     * 기본 코멘트 생성
     */
    protected function getDefaultComment($approvalStatus)
    {
        return match($approvalStatus) {
            'approved' => '관리자에 의한 수동 승인',
            'rejected' => '관리자에 의한 가입 거부',
            default => '관리자에 의한 승인 상태 변경',
        };
    }

    /**
     * 상태 메시지 생성
     */
    protected function getStatusMessage($approvalStatus)
    {
        return match($approvalStatus) {
            'approved' => '사용자가 승인되었습니다. 이제 로그인할 수 있습니다.',
            'rejected' => '사용자의 가입이 거부되었습니다.',
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