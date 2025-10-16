<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserGrades;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 사용자 등급 생성 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/auth/user/grades') → StoreController::__invoke()
 */
class StoreController extends Controller
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

        $storeConfig = $jsonConfig['store'] ?? [];

        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.user.grades.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.user.grades.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '사용자 등급이 성공적으로 생성되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '사용자 등급 생성에 실패했습니다.',
            ],
        ];
    }

    /**
     * 사용자 등급 생성 처리
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'])
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_grade')->insert([
            'name' => $request->name,
            'description' => $request->description,
            'level' => $request->level ?? 0,
            'enable' => $request->enable ?? false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
