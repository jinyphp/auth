<?php

namespace Jiny\Auth\Http\Controllers\Admin\Terms;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 이용약관 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/terms/{id}') → ShowController::__invoke()
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
        $configPath = __DIR__ . '/Terms.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.terms.show',
            'title' => $showConfig['title'] ?? '이용약관 상세',
            'subtitle' => $showConfig['subtitle'] ?? '이용약관 정보 조회',
        ];
    }

    /**
     * 이용약관 상세 정보 표시
     */
    public function __invoke($id)
    {
        $term = \DB::table('user_terms')->where('id', $id)->first();

        if (!$term) {
            return redirect()->route('admin.auth.terms.index')
                ->with('error', '이용약관을 찾을 수 없습니다.');
        }

        return view($this->config['view'], compact('term'));
    }
}
