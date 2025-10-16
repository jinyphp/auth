<?php

namespace Jiny\Auth\Http\Controllers\Admin\OAuthProviders;

use Illuminate\Routing\Controller;

/**
 * 관리자 - OAuth 프로바이더 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/oauth-providers/{id}') → ShowController::__invoke()
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
        $configPath = __DIR__ . '/OAuthProviders.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.oauth-providers.show',
            'title' => $showConfig['title'] ?? 'OAuth 프로바이더 상세',
            'subtitle' => $showConfig['subtitle'] ?? 'OAuth 프로바이더 정보 조회',
        ];
    }

    /**
     * OAuth 프로바이더 상세 정보 표시
     */
    public function __invoke($id)
    {
        $provider = \DB::table('user_oauth_providers')->where('id', $id)->first();

        if (!$provider) {
            return redirect()->route('admin.auth.oauth.providers.index')
                ->with('error', 'OAuth 프로바이더를 찾을 수 없습니다.');
        }

        // 연결된 계정 수
        $accountsCount = \DB::table('user_oauth_accounts')
            ->where('provider', $provider->provider)
            ->count();

        return view($this->config['view'], compact('provider', 'accountsCount'));
    }
}
