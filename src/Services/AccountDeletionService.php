<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Jiny\Auth\Models\ShardedUser;

/**
 * 회원 탈퇴 서비스
 */
class AccountDeletionService
{
    protected $shardingService;

    public function __construct(ShardingService $shardingService)
    {
        $this->shardingService = $shardingService;
    }

    /**
     * 탈퇴 신청
     *
     * @param string $userUuid
     * @param string|null $reason
     * @param string|null $ipAddress
     * @return array
     */
    public function requestDeletion($userUuid, $reason = null, $ipAddress = null)
    {
        // 사용자 정보 조회
        $user = $this->getUserByUuid($userUuid);

        if (!$user) {
            throw new \Exception('사용자를 찾을 수 없습니다.');
        }

        // 이미 탈퇴 신청 중인지 확인
        $existingRequest = DB::table('account_deletions')
            ->where('user_uuid', $userUuid)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            throw new \Exception('이미 탈퇴 신청이 진행 중입니다.');
        }

        // 자동 삭제 기간 계산
        $autoDeleteDays = config('admin.auth.account_deletion.auto_delete_days', 30);
        $autoDeleteAt = now()->addDays($autoDeleteDays);

        // 탈퇴 신청 생성
        $deletionId = DB::table('account_deletions')->insertGetId([
            'user_id' => $user->id ?? null,
            'user_uuid' => $userUuid,
            'email' => $user->email,
            'name' => $user->name,
            'reason' => $reason,
            'deletion_type' => 'user_request',
            'requested_at' => now(),
            'requested_ip' => $ipAddress,
            'status' => 'pending',
            'auto_delete_days' => $autoDeleteDays,
            'auto_delete_at' => $autoDeleteAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 사용자 상태 업데이트 (탈퇴 신청 중)
        $this->updateUserStatus($userUuid, 'deleting');

        return [
            'success' => true,
            'deletion_id' => $deletionId,
            'auto_delete_at' => $autoDeleteAt,
            'auto_delete_days' => $autoDeleteDays,
        ];
    }

    /**
     * 탈퇴 신청 취소
     *
     * @param string $userUuid
     * @return bool
     */
    public function cancelDeletion($userUuid)
    {
        $deletion = DB::table('account_deletions')
            ->where('user_uuid', $userUuid)
            ->where('status', 'pending')
            ->first();

        if (!$deletion) {
            throw new \Exception('탈퇴 신청 내역을 찾을 수 없습니다.');
        }

        // 탈퇴 신청 거부 처리
        DB::table('account_deletions')->where('id', $deletion->id)->update([
            'status' => 'rejected',
            'admin_note' => '사용자 요청으로 취소',
            'updated_at' => now(),
        ]);

        // 사용자 상태 복구
        $this->updateUserStatus($userUuid, 'active');

        return true;
    }

    /**
     * 관리자 탈퇴 승인
     *
     * @param int $deletionId
     * @param int $adminId
     * @param string|null $note
     * @return array
     */
    public function approveDeletion($deletionId, $adminId, $note = null)
    {
        $deletion = DB::table('account_deletions')->where('id', $deletionId)->first();

        if (!$deletion) {
            throw new \Exception('탈퇴 신청을 찾을 수 없습니다.');
        }

        if ($deletion->status !== 'pending') {
            throw new \Exception('처리할 수 없는 상태입니다.');
        }

        // 승인 처리
        DB::table('account_deletions')->where('id', $deletionId)->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_note' => $note,
            'updated_at' => now(),
        ]);

        // 즉시 삭제 처리
        return $this->executeAccountDeletion($deletionId);
    }

    /**
     * 관리자 탈퇴 거부
     *
     * @param int $deletionId
     * @param int $adminId
     * @param string $note
     * @return bool
     */
    public function rejectDeletion($deletionId, $adminId, $note)
    {
        DB::table('account_deletions')->where('id', $deletionId)->update([
            'status' => 'rejected',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'admin_note' => $note,
            'updated_at' => now(),
        ]);

        // 사용자 상태 복구
        $deletion = DB::table('account_deletions')->where('id', $deletionId)->first();
        $this->updateUserStatus($deletion->user_uuid, 'active');

        return true;
    }

    /**
     * 계정 실제 삭제 처리
     *
     * @param int $deletionId
     * @return array
     */
    public function executeAccountDeletion($deletionId)
    {
        $deletion = DB::table('account_deletions')->where('id', $deletionId)->first();

        if (!$deletion) {
            throw new \Exception('탈퇴 신청을 찾을 수 없습니다.');
        }

        return DB::transaction(function () use ($deletion, $deletionId) {
            $userUuid = $deletion->user_uuid;
            $deletedTables = [];

            // 1. 데이터 백업 (선택적)
            $backupPath = null;
            if (config('admin.auth.account_deletion.create_backup', true)) {
                $backupPath = $this->createUserDataBackup($userUuid);
            }

            // 2. 관련 데이터 삭제
            $deletedTables = $this->deleteUserRelatedData($userUuid);

            // 3. 사용자 계정 삭제
            $this->deleteUserAccount($userUuid);
            $deletedTables[] = 'users';

            // 4. 삭제 완료 기록
            DB::table('account_deletions')->where('id', $deletionId)->update([
                'status' => 'completed',
                'data_deleted' => true,
                'deleted_at' => now(),
                'deleted_tables' => json_encode($deletedTables),
                'backup_created' => $backupPath ? true : false,
                'backup_path' => $backupPath,
                'backup_expires_at' => $backupPath ? now()->addDays(90) : null,
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'deleted_tables' => $deletedTables,
                'backup_path' => $backupPath,
            ];
        });
    }

    /**
     * 사용자 관련 데이터 삭제
     *
     * @param string $userUuid
     * @return array
     */
    protected function deleteUserRelatedData($userUuid)
    {
        $deletedTables = [];

        // 삭제할 테이블 목록
        $tables = [
            'user_profile',
            'user_point',
            'user_point_log',
            'user_emoney',
            'user_emoney_log',
            'users_social',
            'users_phone',
            'users_address',
            'user_terms_logs',
            'auth_sessions',
            'auth_activity_logs',
            'user_devices',
            'jwt_tokens',
            'user_mail',
            'user_messages',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                $deleted = DB::table($tableName)->where('user_uuid', $userUuid)->delete();

                if ($deleted > 0) {
                    $deletedTables[] = $tableName;
                }
            }
        }

        // 로그인 시도 기록 삭제 (이메일 기반)
        $user = $this->getUserByUuid($userUuid);
        if ($user) {
            DB::table('auth_login_attempts')->where('email', $user->email)->delete();
            DB::table('user_email_index')->where('email', $user->email)->delete();
            DB::table('user_username_index')->where('uuid', $userUuid)->delete();
            DB::table('user_uuid_index')->where('uuid', $userUuid)->delete();
        }

        return $deletedTables;
    }

    /**
     * 사용자 계정 삭제
     *
     * @param string $userUuid
     * @return bool
     */
    protected function deleteUserAccount($userUuid)
    {
        if ($this->shardingService->isEnabled()) {
            return $this->shardingService->deleteUser($userUuid);
        } else {
            return DB::table('users')->where('uuid', $userUuid)->delete();
        }
    }

    /**
     * 사용자 데이터 백업 생성
     *
     * @param string $userUuid
     * @return string|null
     */
    protected function createUserDataBackup($userUuid)
    {
        try {
            $user = $this->getUserByUuid($userUuid);

            if (!$user) {
                return null;
            }

            $backupData = [
                'user' => $user,
                'profile' => DB::table('user_profile')->where('user_uuid', $userUuid)->first(),
                'point' => DB::table('user_point')->where('user_uuid', $userUuid)->first(),
                'point_logs' => DB::table('user_point_log')->where('user_uuid', $userUuid)->get(),
                'emoney' => DB::table('user_emoney')->where('user_uuid', $userUuid)->first(),
                'social' => DB::table('users_social')->where('user_uuid', $userUuid)->get(),
                'deleted_at' => now(),
            ];

            $filename = "user_backup_{$userUuid}_" . now()->format('YmdHis') . ".json";
            $path = "user-backups/{$filename}";

            Storage::put($path, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $path;

        } catch (\Exception $e) {
            Log::error("사용자 백업 생성 실패: {$e->getMessage()}", [
                'user_uuid' => $userUuid,
            ]);

            return null;
        }
    }

    /**
     * 자동 삭제 처리 (스케줄러에서 호출)
     *
     * @return int
     */
    public function processAutoDeletions()
    {
        $deletions = DB::table('account_deletions')
            ->where('status', 'pending')
            ->where('auto_deleted', false)
            ->where('auto_delete_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($deletions as $deletion) {
            try {
                // 자동 삭제 승인
                DB::table('account_deletions')->where('id', $deletion->id)->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'admin_note' => '자동 삭제 (기간 만료)',
                    'auto_deleted' => true,
                    'updated_at' => now(),
                ]);

                // 실제 삭제 처리
                $this->executeAccountDeletion($deletion->id);

                $count++;

            } catch (\Exception $e) {
                Log::error("자동 탈퇴 처리 실패: {$e->getMessage()}", [
                    'deletion_id' => $deletion->id,
                    'user_uuid' => $deletion->user_uuid,
                ]);
            }
        }

        return $count;
    }

    /**
     * 탈퇴 신청 상태 확인
     *
     * @param string $userUuid
     * @return array|null
     */
    public function getDeletionStatus($userUuid)
    {
        $deletion = DB::table('account_deletions')
            ->where('user_uuid', $userUuid)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$deletion) {
            return null;
        }

        $remainingDays = 0;
        if ($deletion->auto_delete_at) {
            $remainingDays = now()->diffInDays(\Carbon\Carbon::parse($deletion->auto_delete_at), false);
        }

        return [
            'deletion_id' => $deletion->id,
            'status' => $deletion->status,
            'requested_at' => $deletion->requested_at,
            'auto_delete_at' => $deletion->auto_delete_at,
            'remaining_days' => max(0, $remainingDays),
            'requires_approval' => config('admin.auth.account_deletion.require_approval', false),
        ];
    }

    /**
     * 탈퇴 대기 목록
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPendingDeletions()
    {
        return DB::table('account_deletions')
            ->where('status', 'pending')
            ->orderBy('requested_at', 'asc')
            ->get();
    }

    /**
     * 탈퇴 통계
     *
     * @return array
     */
    public function getDeletionStatistics()
    {
        return [
            'pending' => DB::table('account_deletions')->where('status', 'pending')->count(),
            'approved' => DB::table('account_deletions')->where('status', 'approved')->count(),
            'rejected' => DB::table('account_deletions')->where('status', 'rejected')->count(),
            'completed' => DB::table('account_deletions')->where('status', 'completed')->count(),
            'auto_delete_soon' => DB::table('account_deletions')
                ->where('status', 'pending')
                ->where('auto_delete_at', '<=', now()->addDays(7))
                ->count(),
        ];
    }

    /**
     * 사용자 상태 업데이트
     *
     * @param string $userUuid
     * @param string $status
     * @return bool
     */
    protected function updateUserStatus($userUuid, $status)
    {
        if ($this->shardingService->isEnabled()) {
            return $this->shardingService->updateUser($userUuid, ['status' => $status]);
        } else {
            return DB::table('users')->where('uuid', $userUuid)->update(['status' => $status]);
        }
    }

    /**
     * UUID로 사용자 조회
     *
     * @param string $userUuid
     * @return object|null
     */
    protected function getUserByUuid($userUuid)
    {
        if ($this->shardingService->isEnabled()) {
            return $this->shardingService->getUserByUuid($userUuid);
        } else {
            return DB::table('users')->where('uuid', $userUuid)->first();
        }
    }

    /**
     * 탈퇴 알림 발송 (자동 삭제 X일 전)
     *
     * @param int $days
     * @return int
     */
    public function notifyUpcomingDeletions($days = 7)
    {
        $deletions = DB::table('account_deletions')
            ->where('status', 'pending')
            ->whereBetween('auto_delete_at', [now(), now()->addDays($days)])
            ->get();

        $count = 0;

        foreach ($deletions as $deletion) {
            // 이메일/SMS 알림 발송
            // Mail::to($deletion->email)->send(new DeletionNotice($deletion));

            $count++;
        }

        return $count;
    }

    /**
     * 백업 파일 정리 (만료된 백업)
     *
     * @return int
     */
    public function cleanupExpiredBackups()
    {
        $expiredBackups = DB::table('account_deletions')
            ->where('backup_created', true)
            ->whereNotNull('backup_path')
            ->where('backup_expires_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expiredBackups as $deletion) {
            if (Storage::exists($deletion->backup_path)) {
                Storage::delete($deletion->backup_path);

                DB::table('account_deletions')->where('id', $deletion->id)->update([
                    'backup_path' => null,
                    'updated_at' => now(),
                ]);

                $count++;
            }
        }

        return $count;
    }
}