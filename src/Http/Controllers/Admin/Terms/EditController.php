<?php

namespace Jiny\Auth\Http\Controllers\Admin\Terms;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 이용약관 수정 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/terms/{id}/edit') → EditController::__invoke()
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
        $configPath = __DIR__ . '/Terms.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.terms.edit',
            'title' => $editConfig['title'] ?? '이용약관 수정',
            'subtitle' => $editConfig['subtitle'] ?? '이용약관 정보 수정',
        ];
    }

    /**
     * 이용약관 수정 폼 표시
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
