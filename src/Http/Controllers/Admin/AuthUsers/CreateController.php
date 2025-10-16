<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use Illuminate\Routing\Controller;
use Jiny\Auth\Models\UserType;

/**
 * 관리자 - 사용자 생성 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth-users/create') → CreateController::__invoke()
 */
class CreateController extends Controller
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
        $configPath = __DIR__ . '/AuthUser.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $createConfig = $jsonConfig['create'] ?? [];

        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.auth-users.create',
            'title' => $createConfig['title'] ?? '사용자 생성',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 사용자 추가',
        ];
    }

    /**
     * 사용자 생성 폼 표시
     */
    public function __invoke()
    {
        // 활성화된 사용자 유형 목록
        $userTypes = UserType::where('enable', '1')->orderBy('type')->get();

        // 기본 유형 찾기 (is_default = 1)
        $defaultType = UserType::where('enable', '1')
            ->where('is_default', true)
            ->first();

        // 기본 유형이 없으면 첫 번째 유형 사용
        if (!$defaultType && $userTypes->isNotEmpty()) {
            $defaultType = $userTypes->first();
        }

        // 비밀번호 규칙 가져오기
        $passwordRules = config('admin.auth.password_rules', [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => false,
        ]);

        return view($this->config['view'], compact('userTypes', 'defaultType', 'passwordRules'));
    }
}