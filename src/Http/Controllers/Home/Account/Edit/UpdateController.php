<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Edit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * 사용자 정보 업데이트
 */
class UpdateController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 유효성 검사
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
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            // 비밀번호 변경 시 해시 처리
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
                $validated['password_changed_at'] = now();
            } else {
                unset($validated['password']);
            }

            // 사용자 테이블 찾기 (샤딩 고려)
            $tableName = $this->findUserTable($user->id, $user->shard_id ?? null);

            if (!$tableName) {
                \Log::error('User table not found', [
                    'user_id' => $user->id,
                    'shard_id' => $user->shard_id ?? null,
                ]);
                return redirect()->back()
                    ->with('error', '사용자 테이블을 찾을 수 없습니다.');
            }

            // 테이블 스키마에 존재하는 컬럼만 필터링
            $tableColumns = $this->getTableColumns($tableName);
            $updateData = [];

            foreach ($validated as $key => $value) {
                if (in_array($key, $tableColumns)) {
                    $updateData[$key] = $value;
                }
            }

            // updated_at 추가
            if (in_array('updated_at', $tableColumns)) {
                $updateData['updated_at'] = now();
            }

            // 사용자 정보 업데이트
            $updated = DB::table($tableName)
                ->where('id', $user->id)
                ->update($updateData);

            \Log::info('User profile updated', [
                'user_id' => $user->id,
                'table' => $tableName,
                'fields' => array_keys($validated),
                'updated' => $updated,
            ]);

            // 세션 재로그인 (사용자 정보 갱신)
            auth()->logout();
            $updatedUser = DB::table($tableName)->where('id', $user->id)->first();
            if ($updatedUser) {
                auth()->loginUsingId($user->id);
            }

            return redirect()->route('home.account.edit')
                ->with('success', '프로필이 성공적으로 업데이트되었습니다.');
        } catch (\Exception $e) {
            \Log::error('Profile update failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '프로필 업데이트 중 오류가 발생했습니다: ' . $e->getMessage());
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
}
