<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserSocial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 소셜 계정 생성 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/auth/user/social') → StoreController::__invoke()
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
        $configPath = __DIR__ . '/UserSocial.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $storeConfig = $jsonConfig['store'] ?? [];

        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.user.social.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.user.social.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '소셜 계정이 성공적으로 생성되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '소셜 계정 생성에 실패했습니다.',
            ],
        ];
    }

    /**
     * 소셜 계정 생성 처리
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

        \DB::table('user_socials')->insert([
            'user_id' => $request->user_id,
            'provider' => $request->provider,
            'provider_id' => $request->provider_id,
            'email' => $request->email,
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
