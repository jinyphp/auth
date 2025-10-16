<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserGrades;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 사용자 등급 수정 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/grades/{id}/edit') → EditController::__invoke()
 */
class EditController extends Controller
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
        $configPath = __DIR__ . '/UserGrades.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.user-grades.edit',
            'title' => $editConfig['title'] ?? '사용자 등급 수정',
            'subtitle' => $editConfig['subtitle'] ?? '사용자 등급 정보 수정',
        ];
    }

    /**
     * 사용자 등급 수정 폼 표시
     */
    public function __invoke($id)
    {
        $grade = \DB::table('user_grade')->where('id', $id)->first();

        if (!$grade) {
            return redirect()->route('admin.auth.user.grades.index')
                ->with('error', '사용자 등급을 찾을 수 없습니다.');
        }

        return view($this->config['view'], compact('grade'));
    }
}
