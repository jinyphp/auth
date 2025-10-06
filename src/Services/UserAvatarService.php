<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 사용자 아바타 샤딩 서비스
 */
class UserAvatarService
{
    /**
     * user_uuid로 올바른 샤드 테이블명 찾기
     *
     * @param string $userUuid
     * @return string
     */
    public function getShardTableName(string $userUuid): string
    {
        // user_avata 샤드 설정 가져오기
        $shardTable = DB::table('shard_tables')
            ->where('table_name', 'user_avata')
            ->first();

        if (!$shardTable) {
            throw new \Exception('user_avata 샤드 테이블이 등록되지 않았습니다.');
        }

        // UUID를 기반으로 해시 생성 (CRC32 사용)
        $hash = crc32($userUuid);
        $shardId = ($hash % $shardTable->shard_count) + 1;
        $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);

        return $shardTable->table_prefix . $shardNumber;
    }

    /**
     * 사용자의 모든 아바타 가져오기
     *
     * @param string $userUuid
     * @return \Illuminate\Support\Collection
     */
    public function getUserAvatars(string $userUuid)
    {
        $tableName = $this->getShardTableName($userUuid);

        if (!Schema::hasTable($tableName)) {
            return collect([]);
        }

        return DB::table($tableName)
            ->where('user_uuid', $userUuid)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 사용자의 기본 아바타 가져오기
     *
     * @param string $userUuid
     * @return object|null
     */
    public function getDefaultAvatar(string $userUuid): ?object
    {
        $tableName = $this->getShardTableName($userUuid);

        if (!Schema::hasTable($tableName)) {
            return null;
        }

        return DB::table($tableName)
            ->where('user_uuid', $userUuid)
            ->where('selected', '!=', '')
            ->whereNotNull('selected')
            ->first();
    }

    /**
     * 새 아바타 추가
     *
     * @param string $userUuid
     * @param string $imagePath
     * @param bool $setAsDefault
     * @param string|null $description
     * @return int
     */
    public function addAvatar(
        string $userUuid,
        string $imagePath,
        bool $setAsDefault = false,
        ?string $description = null
    ): int {
        $tableName = $this->getShardTableName($userUuid);

        if (!Schema::hasTable($tableName)) {
            throw new \Exception("샤드 테이블 {$tableName}이(가) 존재하지 않습니다.");
        }

        // 첫 아바타인 경우 자동으로 기본 선택
        $existingCount = DB::table($tableName)
            ->where('user_uuid', $userUuid)
            ->count();

        if ($existingCount === 0) {
            $setAsDefault = true;
        }

        // 기본값으로 설정하는 경우, 기존 기본값 해제
        if ($setAsDefault) {
            $this->clearDefaultAvatar($userUuid);
        }

        $id = DB::table($tableName)->insertGetId([
            'user_uuid' => $userUuid,
            'image' => $imagePath,
            'selected' => $setAsDefault ? date('Y-m-d H:i:s') : null,
            'description' => $description,
            'enable' => '1',
            'manager_id' => auth()->id() ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 기본값으로 설정한 경우, 사용자의 avatar 컬럼도 업데이트
        if ($setAsDefault) {
            $this->updateUserAvatar($userUuid, $imagePath);
        }

        return $id;
    }

    /**
     * 아바타를 기본값으로 설정
     *
     * @param string $userUuid
     * @param int $avatarId
     * @return bool
     */
    public function setDefaultAvatar(string $userUuid, int $avatarId): bool
    {
        $tableName = $this->getShardTableName($userUuid);

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 기존 기본값 해제
        $this->clearDefaultAvatar($userUuid);

        // 새 기본값 설정
        $result = DB::table($tableName)
            ->where('id', $avatarId)
            ->where('user_uuid', $userUuid)
            ->update([
                'selected' => date('Y-m-d H:i:s'),
                'updated_at' => now(),
            ]);

        if ($result) {
            // 아바타 이미지 경로 가져오기
            $avatar = DB::table($tableName)->where('id', $avatarId)->first();
            if ($avatar && $avatar->image) {
                $this->updateUserAvatar($userUuid, $avatar->image);
            }
        }

        return $result > 0;
    }

    /**
     * 기존 기본 아바타 해제
     *
     * @param string $userUuid
     * @return void
     */
    protected function clearDefaultAvatar(string $userUuid): void
    {
        $tableName = $this->getShardTableName($userUuid);

        if (!Schema::hasTable($tableName)) {
            return;
        }

        DB::table($tableName)
            ->where('user_uuid', $userUuid)
            ->whereNotNull('selected')
            ->update([
                'selected' => null,
                'updated_at' => now(),
            ]);
    }

    /**
     * 아바타 삭제
     *
     * @param string $userUuid
     * @param int $avatarId
     * @return bool
     */
    public function deleteAvatar(string $userUuid, int $avatarId): bool
    {
        $tableName = $this->getShardTableName($userUuid);

        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 삭제하려는 아바타가 기본값인지 확인
        $avatar = DB::table($tableName)
            ->where('id', $avatarId)
            ->where('user_uuid', $userUuid)
            ->first();

        if (!$avatar) {
            return false;
        }

        $wasDefault = !empty($avatar->selected);
        $imagePath = $avatar->image;

        // 아바타 삭제
        $result = DB::table($tableName)
            ->where('id', $avatarId)
            ->where('user_uuid', $userUuid)
            ->delete();

        if ($result) {
            // 이미지 파일 삭제
            if ($imagePath) {
                $uploadService = app(\Jiny\Auth\Services\AvatarUploadService::class);
                $uploadService->delete($imagePath);
            }

            // 기본값이었다면 사용자의 avatar 컬럼도 초기화
            if ($wasDefault) {
                $this->updateUserAvatar($userUuid, null);
            }
        }

        return $result > 0;
    }

    /**
     * 사용자의 avatar 컬럼 업데이트 (샤딩된 users 테이블)
     *
     * @param string $userUuid
     * @param string|null $avatarPath
     * @return void
     */
    protected function updateUserAvatar(string $userUuid, ?string $avatarPath): void
    {
        // 기본 users 테이블 확인
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar')) {
            try {
                DB::table('users')
                    ->where('uuid', $userUuid)
                    ->update(['avatar' => $avatarPath]);
            } catch (\Exception $e) {
                \Log::warning('Failed to update avatar in users table: ' . $e->getMessage());
            }
        }

        // 샤딩된 users 테이블 확인
        for ($i = 1; $i <= 16; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'avatar')) {
                try {
                    $result = DB::table($tableName)
                        ->where('uuid', $userUuid)
                        ->update(['avatar' => $avatarPath]);

                    if ($result > 0) {
                        break;
                    }
                } catch (\Exception $e) {
                    \Log::warning("Failed to update avatar in {$tableName}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * 사용자의 모든 아바타와 이미지 파일 삭제
     * (회원 삭제 시 호출)
     *
     * @param string $userUuid
     * @return bool
     */
    public function deleteAllUserAvatars(string $userUuid): bool
    {
        try {
            $tableName = $this->getShardTableName($userUuid);

            if (!Schema::hasTable($tableName)) {
                return true; // 테이블이 없으면 성공으로 간주
            }

            // 사용자의 모든 아바타 가져오기
            $avatars = DB::table($tableName)
                ->where('user_uuid', $userUuid)
                ->get();

            $uploadService = app(\Jiny\Auth\Services\AvatarUploadService::class);

            // 각 아바타의 이미지 파일 삭제
            foreach ($avatars as $avatar) {
                if ($avatar->image) {
                    $uploadService->delete($avatar->image);
                }
            }

            // 데이터베이스에서 모든 아바타 레코드 삭제
            DB::table($tableName)
                ->where('user_uuid', $userUuid)
                ->delete();

            // 사용자의 avatar 컬럼 초기화
            $this->updateUserAvatar($userUuid, null);

            return true;
        } catch (\Exception $e) {
            \Log::error('아바타 전체 삭제 실패: ' . $e->getMessage(), [
                'user_uuid' => $userUuid,
                'exception' => $e
            ]);
            return false;
        }
    }
}
