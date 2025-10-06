<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserBlacklist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 블랙리스트 수정 처리 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/auth/user/blacklist/{id}') → UpdateController::__invoke()
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
        $configPath = __DIR__ . '/UserBlacklist.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.blacklist.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.blacklist.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '블랙리스트가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '블랙리스트 업데이트에 실패했습니다.',
            ],
        ];
    }

    /**
     * 블랙리스트 수정 처리
     */
    public function __invoke(Request $request, $id)
    {
        $blacklist = \DB::table('user_blacklist')->where('id', $id)->first();

        if (!$blacklist) {
            return redirect()->route('admin.auth.user.blacklist.index')
                ->with('error', '블랙리스트를 찾을 수 없습니다.');
        }

        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_blacklist')->where('id', $id)->update([
            'keyword' => $request->keyword,
            'description' => $request->description,
            'type' => $request->type ?? 'username',
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
