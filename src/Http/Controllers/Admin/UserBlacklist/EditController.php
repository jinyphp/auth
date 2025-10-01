<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserBlacklist;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 블랙리스트 수정 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/blacklist/{id}/edit') → EditController::__invoke()
 */
class EditController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserBlacklist.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-blacklist.edit',
            'title' => $editConfig['title'] ?? '블랙리스트 수정',
            'subtitle' => $editConfig['subtitle'] ?? '블랙리스트 정보 수정',
        ];
    }

    /**
     * 블랙리스트 수정 폼 표시
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
