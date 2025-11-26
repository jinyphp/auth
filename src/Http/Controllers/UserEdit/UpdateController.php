<?php

namespace Jiny\Auth\Http\Controllers\UserEdit;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Jiny\Auth\Facades\Shard;

/**
 * 사용자 정보 업데이트
 */
class UpdateController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return $this->responseError('로그인이 필요합니다.', 401);
        }

        $formType = $request->input('form_type', 'profile');

        return $formType === 'password'
            ? $this->handlePasswordUpdate($request, $user)
            : $this->handleProfileUpdate($request, $user);
    }

    /**
     * 프로필 기본 정보 업데이트 (AJAX)
     */
    protected function handleProfileUpdate(Request $request, $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone_number' => 'nullable|string|max:20',
            'language' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:50',
        ]);

        try {
            if ($this->shouldUseShard($user)) {
                return $this->updateShardedProfile($user, $validated);
            }

            $tableName = $this->findUserTable($user->id, $user->shard_id ?? null);

            if (!$tableName) {
                \Log::error('User table not found', [
                    'user_id' => $user->id,
                    'shard_id' => $user->shard_id ?? null,
                ]);

                return $this->responseError('사용자 테이블을 찾을 수 없습니다.');
            }

            $updateData = $this->filterColumns($validated, $tableName);

            if (empty($updateData)) {
                return $this->responseError('업데이트할 필드가 없습니다.', 400);
            }

            if (in_array('updated_at', $this->getTableColumns($tableName))) {
                $updateData['updated_at'] = now();
            }

            DB::table($tableName)
                ->where('id', $user->id)
                ->update($updateData);

            $this->syncBaseUserTable($user, $updateData);

            $freshUser = DB::table($tableName)->where('id', $user->id)->first();

            $this->updateSessionUser($user, $freshUser);

            \Log::info('User profile updated (AJAX)', [
                'user_id' => $user->id,
                'table' => $tableName,
                'fields' => array_keys($updateData),
            ]);

            return response()->json([
                'success' => true,
                'message' => '프로필이 성공적으로 업데이트되었습니다.',
                'user' => $this->formatUserResponse($freshUser, $user),
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile update failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->responseError(
                '프로필 업데이트 중 오류가 발생했습니다. ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * 비밀번호 업데이트 (AJAX)
     */
    protected function handlePasswordUpdate(Request $request, $user): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            if ($this->shouldUseShard($user)) {
                return $this->updateShardedPassword($user, $validated['password']);
            }

            $tableName = $this->findUserTable($user->id, $user->shard_id ?? null);

            if (!$tableName) {
                \Log::error('User table not found (password)', [
                    'user_id' => $user->id,
                    'shard_id' => $user->shard_id ?? null,
                ]);

                return $this->responseError('사용자 테이블을 찾을 수 없습니다.');
            }

            $updateData = [
                'password' => Hash::make($validated['password']),
                'password_changed_at' => now(),
            ];

            if (in_array('updated_at', $this->getTableColumns($tableName))) {
                $updateData['updated_at'] = now();
            }

            DB::table($tableName)
                ->where('id', $user->id)
                ->update($updateData);

            $this->syncBaseUserTable($user, $updateData);

            \Log::info('User password updated (AJAX)', [
                'user_id' => $user->id,
                'table' => $tableName,
            ]);

            return response()->json([
                'success' => true,
                'message' => '비밀번호가 성공적으로 변경되었습니다.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Password update failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->responseError(
                '비밀번호 변경 중 오류가 발생했습니다. ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * 사용자 테이블 찾기 (샤딩 고려)
     *
     * @param int $userId
     * @param int|null $shardId
     * @return string|null
     */
    protected function findUserTable(int $userId, ?int $shardId): ?string
    {
        // 샤드 ID가 지정된 경우
        if ($shardId) {
            $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            if (Schema::hasTable($tableName)) {
                $exists = DB::table($tableName)->where('id', $userId)->exists();
                if ($exists) {
                    return $tableName;
                }
            }
        }

        // 기본 users 테이블 확인
        if (Schema::hasTable('users')) {
            $exists = DB::table('users')->where('id', $userId)->exists();
            if ($exists) {
                return 'users';
            }
        }

        // 모든 샤드 테이블 검색
        for ($i = 1; $i <= 16; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tableName = "users_{$shardNumber}";

            if (Schema::hasTable($tableName)) {
                $exists = DB::table($tableName)->where('id', $userId)->exists();
                if ($exists) {
                    return $tableName;
                }
            }
        }

        return null;
    }

    /**
     * 테이블의 컬럼 목록 가져오기
     *
     * @param string $tableName
     * @return array
     */
    protected function getTableColumns(string $tableName): array
    {
        try {
            $columns = Schema::getColumnListing($tableName);
            return $columns;
        } catch (\Exception $e) {
            \Log::error('Failed to get table columns', [
                'table' => $tableName,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * 지정된 테이블 컬럼에 맞춰 데이터 필터링
     */
    protected function filterColumns(array $data, string $tableName): array
    {
        $columns = $this->getTableColumns($tableName);

        return collect($data)
            ->filter(fn ($value, $key) => in_array($key, $columns))
            ->toArray();
    }

    /**
     * 기본 users 테이블과 동기화 (세션 사용자와 동일한 소스 유지)
     */
    protected function syncBaseUserTable($user, array $updateData): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $columns = $this->getTableColumns('users');
        $baseUpdate = collect($updateData)
            ->filter(fn ($value, $key) => in_array($key, $columns))
            ->toArray();

        if (empty($baseUpdate)) {
            return;
        }

        $query = DB::table('users');

        if (isset($user->id) && $query->where('id', $user->id)->exists()) {
            $query->where('id', $user->id)->update($baseUpdate);
            return;
        }

        if (isset($user->uuid) && DB::table('users')->where('uuid', $user->uuid)->exists()) {
            DB::table('users')->where('uuid', $user->uuid)->update($baseUpdate);
        }
    }

    /**
     * 프론트로 반환할 사용자 데이터 포맷
     */
    protected function formatUserResponse($freshUser, $authUser): array
    {
        $source = $freshUser ?? $authUser;

        return [
            'id' => $source->id,
            'name' => $source->name,
            'email' => $source->email,
            'username' => $source->username ?? null,
            'status' => $source->status ?? $authUser->status,
            'grade' => $source->grade ?? $authUser->grade,
            'avatar' => $source->avatar ?? $authUser->avatar,
            'created_at' => $authUser->created_at?->toISOString(),
            'last_login_at' => $authUser->last_login_at?->toISOString(),
        ];
    }

    /**
     * 에러 응답 헬퍼
     */
    protected function responseError(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * 샤딩 사용 가능 여부
     */
    protected function shouldUseShard($user): bool
    {
        return ($user->uuid ?? null) && class_exists(Shard::class) && Shard::isEnabled();
    }

    /**
     * 샤딩 사용자 프로필 업데이트
     */
    protected function updateShardedProfile($user, array $validated): JsonResponse
    {
        $tableName = Shard::getShardTableName($user->uuid);
        $updateData = $this->filterColumns($validated, $tableName);

        if (empty($updateData)) {
            return $this->responseError('업데이트할 필드가 없습니다.', 400);
        }

        if (in_array('updated_at', $this->getTableColumns($tableName))) {
            $updateData['updated_at'] = now();
        }

        Shard::updateUser($user->uuid, $updateData);

        $freshUser = Shard::getUserByUuid($user->uuid);

        $this->syncBaseUserTable($user, $updateData);
        $this->updateSessionUser($user, $freshUser);

        \Log::info('Sharded user profile updated', [
            'user_uuid' => $user->uuid,
            'table' => $tableName,
            'fields' => array_keys($updateData),
        ]);

        return response()->json([
            'success' => true,
            'message' => '프로필이 성공적으로 업데이트되었습니다.',
            'user' => $this->formatUserResponse($freshUser, $user),
        ]);
    }

    /**
     * 샤딩 사용자 비밀번호 업데이트
     */
    protected function updateShardedPassword($user, string $password): JsonResponse
    {
        $tableName = Shard::getShardTableName($user->uuid);
        $tableColumns = $this->getTableColumns($tableName);

        $updateData = [];
        if (in_array('password', $tableColumns)) {
            $updateData['password'] = Hash::make($password);
        }
        if (in_array('password_changed_at', $tableColumns)) {
            $updateData['password_changed_at'] = now();
        }

        if (in_array('updated_at', $tableColumns)) {
            $updateData['updated_at'] = now();
        }

        if (empty($updateData)) {
            return $this->responseError('샤딩 사용자 테이블에 업데이트 가능한 컬럼이 없습니다.', 500);
        }

        Shard::updateUser($user->uuid, $updateData);

        $this->syncBaseUserTable($user, $updateData);

        \Log::info('Sharded user password updated', [
            'user_uuid' => $user->uuid,
            'table' => $tableName,
        ]);

        return response()->json([
            'success' => true,
            'message' => '비밀번호가 성공적으로 변경되었습니다.',
        ]);
    }

    /**
     * 세션 사용자 정보를 최신 상태로 유지
     */
    protected function updateSessionUser($authUser, $freshUser): void
    {
        if (!$authUser || !$freshUser) {
            return;
        }

        $attributes = collect((array) $freshUser)->only([
            'name',
            'email',
            'username',
            'status',
            'grade',
            'avatar',
            'last_login_at',
            'created_at',
            'login_count',
        ])->toArray();

        foreach ($attributes as $key => $value) {
            if (in_array($key, ['last_login_at', 'created_at']) && $value) {
                $authUser->{$key} = Carbon::parse($value);
                continue;
            }
            $authUser->{$key} = $value;
        }

        if (method_exists($authUser, 'syncOriginal')) {
            $authUser->syncOriginal();
        }

        auth()->setUser($authUser);
    }
}
