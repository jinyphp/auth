<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserTypes;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserType;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 사용자 유형 생성 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/auth/user/types') → StoreController::__invoke()
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
        $configPath = __DIR__ . '/UserTypes.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $storeConfig = $jsonConfig['store'] ?? [];

        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'defaults' => $storeConfig['defaults'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.user.types.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.user.types.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '사용자 유형이 성공적으로 생성되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '사용자 유형 생성에 실패했습니다.',
            ],
        ];
    }

    /**
     * 사용자 유형 생성 처리
     */
    public function __invoke(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'])
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // 기본값 적용
        foreach ($this->actions['defaults'] as $key => $value) {
            $data[$key] = $value;
        }

        UserType::create($data);

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}