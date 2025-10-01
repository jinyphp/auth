<?php

namespace Jiny\Auth\Http\Controllers\Admin\Terms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 이용약관 수정 처리 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/auth/terms/{id}') → UpdateController::__invoke()
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
        $configPath = __DIR__ . '/Terms.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.terms.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.terms.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '이용약관이 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '이용약관 업데이트에 실패했습니다.',
            ],
        ];
    }

    /**
     * 이용약관 수정 처리
     */
    public function __invoke(Request $request, $id)
    {
        $term = \DB::table('user_terms')->where('id', $id)->first();

        if (!$term) {
            return redirect()->route('admin.auth.terms.index')
                ->with('error', '이용약관을 찾을 수 없습니다.');
        }

        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_terms')->where('id', $id)->update([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'version' => $request->version,
            'pos' => $request->pos ?? 0,
            'enable' => $request->enable ?? false,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
