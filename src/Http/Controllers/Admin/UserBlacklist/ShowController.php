<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserBlacklist;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 블랙리스트 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/blacklist/{id}') → ShowController::__invoke()
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
        $configPath = __DIR__ . '/UserBlacklist.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-blacklist.show',
            'title' => $showConfig['title'] ?? '블랙리스트 상세',
            'subtitle' => $showConfig['subtitle'] ?? '블랙리스트 정보 조회',
        ];
    }

    /**
     * 블랙리스트 상세 정보 표시
     */
    public function __invoke($id)
    {
        $blacklist = \DB::table('user_blacklist')->where('id', $id)->first();

        if (!$blacklist) {
            return redirect()->route('admin.auth.user.blacklist.index')
                ->with('error', '블랙리스트를 찾을 수 없습니다.');
        }

        return view($this->config['view'], compact('blacklist'));
    }
}
