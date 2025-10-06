<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * 관리자 - 사용자 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth-users/{id}') → ShowController::__invoke()
 */
class ShowController extends Controller
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

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.auth-users.show',
            'title' => $showConfig['title'] ?? '사용자 상세',
            'subtitle' => $showConfig['subtitle'] ?? '사용자 정보 조회',
        ];
    }

    /**
     * 사용자 상세 정보 표시
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

            // 아바타 정보 가져오기
            $avatarTableName = 'user_avata_' . str_pad($shardId, 3, '0', STR_PAD_LEFT);
            if (DB::getSchemaBuilder()->hasTable($avatarTableName) && isset($user->uuid)) {
                // 먼저 selected=1인 아바타 찾기
                $avatar = DB::table($avatarTableName)
                    ->where('user_uuid', $user->uuid)
                    ->where('selected', '1')
                    ->whereNotNull('image')
                    ->first();

                // 없으면 가장 최근 아바타 찾기
                if (!$avatar) {
                    $avatar = DB::table($avatarTableName)
                        ->where('user_uuid', $user->uuid)
                        ->whereNotNull('image')
                        ->orderBy('created_at', 'desc')
                        ->first();
                }

                if ($avatar && $avatar->image) {
                    $user->avatar = $avatar->image;
                }
            }
        } else {
            // 일반 테이블에서 조회
            $user = AuthUser::findOrFail($id);

            // 아바타 정보 가져오기
            if (DB::getSchemaBuilder()->hasTable('user_avata') && isset($user->uuid)) {
                // 먼저 selected=1인 아바타 찾기
                $avatar = DB::table('user_avata')
                    ->where('user_uuid', $user->uuid)
                    ->where('selected', '1')
                    ->whereNotNull('image')
                    ->first();

                // 없으면 가장 최근 아바타 찾기
                if (!$avatar) {
                    $avatar = DB::table('user_avata')
                        ->where('user_uuid', $user->uuid)
                        ->whereNotNull('image')
                        ->orderBy('created_at', 'desc')
                        ->first();
                }

                if ($avatar && $avatar->image) {
                    $user->avatar = $avatar->image;
                }
            }
        }

        return view($this->config['view'], compact('user', 'shardId'));
    }
}