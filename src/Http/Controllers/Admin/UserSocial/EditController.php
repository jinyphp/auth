<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserSocial;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 소셜 계정 수정 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/social/{id}/edit') → EditController::__invoke()
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
        $configPath = __DIR__ . '/UserSocial.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-social.edit',
            'title' => $editConfig['title'] ?? '소셜 계정 수정',
            'subtitle' => $editConfig['subtitle'] ?? '소셜 계정 정보 수정',
        ];
    }

    /**
     * 소셜 계정 수정 폼 표시
     */
    public function __invoke($id)
    {
        $social = \DB::table('user_socials')->where('id', $id)->first();

        if (!$social) {
            return redirect()->route('admin.auth.user.social.index')
                ->with('error', '소셜 계정을 찾을 수 없습니다.');
        }

        $user = \App\Models\User::find($social->user_id);

        // 소셜 프로바이더 목록
        $providers = ['google', 'facebook', 'twitter', 'github', 'kakao', 'naver'];

        return view($this->config['view'], compact('social', 'user', 'providers'));
    }
}
