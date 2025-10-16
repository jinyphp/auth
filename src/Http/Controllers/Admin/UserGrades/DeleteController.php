<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserGrades;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 사용자 등급 삭제 처리 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/auth/user/grades/{id}') → DeleteController::__invoke()
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
        $configPath = __DIR__ . '/UserGrades.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.grades.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '사용자 등급이 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '사용자 등급 삭제에 실패했습니다.',
            ],
        ];
    }

    /**
     * 사용자 등급 삭제 처리
     */
    public function __invoke($id)
    {
        $grade = \DB::table('user_grade')->where('id', $id)->first();

        if (!$grade) {
            return redirect()->route('admin.auth.user.grades.index')
                ->with('error', '사용자 등급을 찾을 수 없습니다.');
        }

        \DB::table('user_grade')->where('id', $id)->delete();

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
