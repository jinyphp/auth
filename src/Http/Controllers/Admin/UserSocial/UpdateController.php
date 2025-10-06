<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserSocial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 소셜 계정 수정 처리 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/auth/user/social/{id}') → UpdateController::__invoke()
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
        $configPath = __DIR__ . '/UserSocial.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.social.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.social.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '소셜 계정 정보가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '소셜 계정 정보 업데이트에 실패했습니다.',
            ],
        ];
    }

    /**
     * 소셜 계정 수정 처리
     */
    public function __invoke(Request $request, $id)
    {
        $social = \DB::table('user_socials')->where('id', $id)->first();

        if (!$social) {
            return redirect()->route('admin.auth.user.social.index')
                ->with('error', '소셜 계정을 찾을 수 없습니다.');
        }

        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_socials')->where('id', $id)->update([
            'provider' => $request->provider,
            'provider_id' => $request->provider_id,
            'email' => $request->email,
            'name' => $request->name,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
