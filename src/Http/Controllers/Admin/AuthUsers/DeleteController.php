<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\UserType;
use Jiny\Auth\Models\ShardTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * 관리자 - 사용자 삭제 처리 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/auth-users/{id}') → DeleteController::__invoke()
 */
class DeleteController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/AuthUser.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.users.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '사용자가 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '사용자 삭제에 실패했습니다.',
            ],
        ];
    }

    /**
     * 사용자 삭제 처리
     *
     * 처리 순서:
     * 1. 사용자 조회 (샤딩 지원)
     * 2. UserType 카운트 감소
     * 3. 사용자 삭제
     */
    public function __invoke(Request $request, $id)
    {
        $shardId = $request->get('shard_id');

        if ($shardId) {
            // 샤드 테이블에서 조회 및 삭제
            $shardTable = ShardTable::where('table_name', 'users')->first();
            $tableName = $shardTable->getShardTableName($shardId);

            $userData = DB::table($tableName)->where('id', $id)->first();

            if (!$userData) {
                return redirect()
                    ->route($this->actions['routes']['success'])
                    ->with('error', '사용자를 찾을 수 없습니다.');
            }

            // UserType 카운트 감소
            if ($userData->utype) {
                $userType = UserType::where('type', $userData->utype)->first();
                if ($userType) {
                    $userType->decrementUsers();
                }
            }

            // 샤드 테이블에서 직접 삭제
            DB::table($tableName)->where('id', $id)->delete();
        } else {
            // 일반 테이블에서 삭제
            $user = AuthUser::find($id);

            if (!$user) {
                return redirect()
                    ->route($this->actions['routes']['success'])
                    ->with('error', '사용자를 찾을 수 없습니다.');
            }

            // UserType 카운트 감소
            if ($user->utype) {
                $userType = UserType::where('type', $user->utype)->first();
                if ($userType) {
                    $userType->decrementUsers();
                }
            }

            // 사용자 삭제
            $user->delete();
        }

        $redirectUrl = route($this->actions['routes']['success']);
        if ($shardId) {
            $redirectUrl .= '?shard_id=' . $shardId;
        }

        return redirect($redirectUrl)
            ->with('success', $this->actions['messages']['success']);
    }
}