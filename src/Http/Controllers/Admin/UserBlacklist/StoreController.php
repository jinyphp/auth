<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserBlacklist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 블랙리스트 생성 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/auth/user/blacklist') → StoreController::__invoke()
 */
class StoreController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserBlacklist.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $storeConfig = $jsonConfig['store'] ?? [];

        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.user.blacklist.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.user.blacklist.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '블랙리스트가 성공적으로 추가되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '블랙리스트 추가에 실패했습니다.',
            ],
        ];
    }

    /**
     * 블랙리스트 생성 처리
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'])
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_blacklist')->insert([
            'keyword' => $request->keyword,
            'description' => $request->description,
            'type' => $request->type ?? 'username',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
