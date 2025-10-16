<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserSocial;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 소셜 계정 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/social/{id}') → ShowController::__invoke()
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
        $configPath = __DIR__ . '/UserSocial.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-social.show',
            'title' => $showConfig['title'] ?? '소셜 계정 상세',
            'subtitle' => $showConfig['subtitle'] ?? '소셜 계정 정보',
        ];
    }

    /**
     * 소셜 계정 상세 정보 표시
     */
    public function __invoke($id)
    {
        $social = \DB::table('user_socials')->where('id', $id)->first();

        if (!$social) {
            return redirect()->route('admin.auth.user.social.index')
                ->with('error', '소셜 계정을 찾을 수 없습니다.');
        }

        // 사용자 정보
        $user = \App\Models\User::find($social->user_id);

        return view($this->config['view'], compact('social', 'user'));
    }
}
