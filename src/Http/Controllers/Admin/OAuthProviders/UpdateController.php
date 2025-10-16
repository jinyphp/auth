<?php

namespace Jiny\Auth\Http\Controllers\Admin\OAuthProviders;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - OAuth 프로바이더 수정 처리 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/auth/oauth-providers/{id}') → UpdateController::__invoke()
 */
class UpdateController extends Controller
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

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.oauth.providers.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.oauth.providers.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '소셜 로그인 프로바이더가 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '소셜 로그인 프로바이더 업데이트에 실패했습니다.',
            ],
        ];
    }

    /**
     * OAuth 프로바이더 수정 처리
     */
    public function __invoke(Request $request, $id)
    {
        $provider = \DB::table('user_oauth_providers')->where('id', $id)->first();

        if (!$provider) {
            return redirect()->route('admin.auth.oauth.providers.index')
                ->with('error', 'OAuth 프로바이더를 찾을 수 없습니다.');
        }

        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_oauth_providers')->where('id', $id)->update([
            'name' => $request->name,
            'provider' => $request->provider,
            'client_id' => $request->client_id,
            'client_secret' => $request->client_secret,
            'callback_url' => $request->callback_url,
            'enable' => $request->enable ?? 'no',
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
