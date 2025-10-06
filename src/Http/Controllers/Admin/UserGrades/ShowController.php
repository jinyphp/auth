<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserGrades;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 사용자 등급 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/grades/{id}') → ShowController::__invoke()
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
        $configPath = __DIR__ . '/UserGrades.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-grades.show',
            'title' => $showConfig['title'] ?? '사용자 등급 상세',
            'subtitle' => $showConfig['subtitle'] ?? '사용자 등급 정보 조회',
        ];
    }

    /**
     * 사용자 등급 상세 정보 표시
     */
    public function __invoke($id)
    {
        $grade = \DB::table('user_grade')->where('id', $id)->first();

        if (!$grade) {
            return redirect()->route('admin.auth.user.grades.index')
                ->with('error', '사용자 등급을 찾을 수 없습니다.');
        }

        // 해당 등급을 가진 사용자 수
        $usersCount = \DB::table('users')->where('grade_id', $id)->count();

        return view($this->config['view'], compact('grade', 'usersCount'));
    }
}
