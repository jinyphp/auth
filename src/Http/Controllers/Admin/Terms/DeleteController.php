<?php

namespace Jiny\Auth\Http\Controllers\Admin\Terms;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 이용약관 삭제 처리 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/auth/terms/{id}') → DeleteController::__invoke()
 */
class DeleteController extends Controller
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
        $configPath = __DIR__ . '/Terms.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.terms.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '이용약관이 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '이용약관 삭제에 실패했습니다.',
            ],
        ];
    }

    /**
     * 이용약관 삭제 처리
     */
    public function __invoke($id)
    {
        $term = \DB::table('user_terms')->where('id', $id)->first();

        if (!$term) {
            return redirect()->route('admin.auth.terms.index')
                ->with('error', '이용약관을 찾을 수 없습니다.');
        }

        \DB::table('user_terms')->where('id', $id)->delete();

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
