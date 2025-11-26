<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers\TwoFactor;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;

/**
 * 2FA 관리 컨트롤러에서 공통으로 사용하는 사용자 조회 로직
 */
abstract class BaseTwoFactorController extends Controller
{
    /**
     * 샤딩 여부에 따라 사용자를 조회합니다.
     */
    protected function resolveUser($id, ?int $shardId = null): AuthUser
    {
        if ($shardId) {
            $shardTable = ShardTable::where('table_name', 'users')->first();
            if (!$shardTable) {
                abort(404, '샤딩 구성을 찾을 수 없습니다.');
            }

            $tableName = $shardTable->getShardTableName($shardId);
            if (!DB::getSchemaBuilder()->hasTable($tableName)) {
                abort(404, '샤드 테이블이 존재하지 않습니다.');
            }

            $userData = DB::table($tableName)->where('id', $id)->first();
            if (!$userData) {
                abort(404, '사용자를 찾을 수 없습니다.');
            }

            $user = AuthUser::hydrate([(array) $userData])->first();
            $user->setTable($tableName);

            return $user;
        }

        return AuthUser::findOrFail($id);
    }

    /**
     * 2FA 설정 임시 데이터를 저장할 세션 키를 생성합니다.
     */
    protected function setupSessionKey($user): string
    {
        return 'two_factor.setup.' . ($user->uuid ?? $user->id);
    }
}

