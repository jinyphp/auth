<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Models\UserType;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * 관리자 - 사용자 수정 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth-users/{id}/edit') → EditController::__invoke()
 */
class EditController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/AuthUser.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.auth-users.edit',
            'title' => $editConfig['title'] ?? '사용자 수정',
            'subtitle' => $editConfig['subtitle'] ?? '사용자 정보 수정',
        ];
    }

    /**
     * 사용자 수정 폼 표시
     */
    public function __invoke(Request $request, $id)
    {
        $shardId = $request->get('shard_id');

        if ($shardId) {
            // 샤드 테이블에서 조회
            $shardTable = ShardTable::where('table_name', 'users')->first();
            $tableName = $shardTable->getShardTableName($shardId);

            $userData = DB::table($tableName)->where('id', $id)->first();

            if (!$userData) {
                abort(404, '사용자를 찾을 수 없습니다.');
            }

            $user = AuthUser::hydrate([(array)$userData])->first();
            $user->setTable($tableName);
        } else {
            // 일반 테이블에서 조회
            $user = AuthUser::findOrFail($id);
        }

        // 사용자 타입 목록 가져오기
        $userTypes = UserType::where('enable', '1')->orderBy('type')->get();

        // 비밀번호 규칙 가져오기
        $passwordRules = config('admin.auth.password_rules', [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => false,
        ]);

        return view($this->config['view'], compact('user', 'shardId', 'userTypes', 'passwordRules'));
    }
}