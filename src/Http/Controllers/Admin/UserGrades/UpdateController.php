<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserGrades;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 사용자 등급 수정 처리 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/auth/user/grades/{id}') → UpdateController::__invoke()
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
        $configPath = __DIR__ . '/UserGrades.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.grades.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.grades.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '사용자 등급이 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '사용자 등급 업데이트에 실패했습니다.',
            ],
        ];
    }

    /**
     * 사용자 등급 수정 처리
     */
    public function __invoke(Request $request, $id)
    {
        $grade = \DB::table('user_grade')->where('id', $id)->first();

        if (!$grade) {
            return redirect()->route('admin.auth.user.grades.index')
                ->with('error', '사용자 등급을 찾을 수 없습니다.');
        }

        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_grade')->where('id', $id)->update([
            'name' => $request->name,
            'description' => $request->description,
            'level' => $request->level ?? 0,
            'enable' => $request->enable ?? false,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
