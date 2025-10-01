<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserTypes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * 관리자 - 사용자 유형 업데이트 처리 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/auth/user/types/{id}') → UpdateController::__invoke()
 */
class UpdateController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserTypes.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.types.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.types.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '사용자 유형이 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '사용자 유형 업데이트에 실패했습니다.',
            ],
        ];
    }

    /**
     * 사용자 유형 업데이트 처리
     */
    public function __invoke(Request $request, $id)
    {
        $userType = UserType::findOrFail($id);

        // Validation rules with unique exception
        $validationRules = $this->actions['validation'];
        $validationRules['type'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('user_type')->ignore($userType->id),
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        $userType->update($request->all());

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}