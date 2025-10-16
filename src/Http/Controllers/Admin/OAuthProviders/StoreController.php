<?php

namespace Jiny\Auth\Http\Controllers\Admin\OAuthProviders;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserOAuthProvider;

/**
 * 관리자 - OAuth 프로바이더 생성 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/auth/oauth/providers') → StoreController::__invoke()
 */
class StoreController extends Controller
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
        $configPath = __DIR__ . '/OAuthProviders.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $storeConfig = $jsonConfig['store'] ?? [];

        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'defaults' => $storeConfig['defaults'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.oauth.providers.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.oauth.providers.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '소셜 로그인 프로바이더가 추가되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '소셜 로그인 프로바이더 추가에 실패했습니다.',
            ],
        ];
    }

    /**
     * OAuth 프로바이더 생성 처리
     */
    public function __invoke(Request $request)
    {
        // Validation
        $validated = $request->validate($this->actions['validation']);

        // 기본값 적용
        foreach ($this->actions['defaults'] as $key => $value) {
            $validated[$key] = $validated[$key] ?? $value;
        }

        UserOAuthProvider::create($validated);

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}