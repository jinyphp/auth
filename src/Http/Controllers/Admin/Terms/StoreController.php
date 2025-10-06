<?php

namespace Jiny\Auth\Http\Controllers\Admin\Terms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 이용약관 생성 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/auth/terms') → StoreController::__invoke()
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
        $configPath = __DIR__ . '/Terms.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $storeConfig = $jsonConfig['store'] ?? [];

        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.terms.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.terms.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '이용약관이 성공적으로 생성되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '이용약관 생성에 실패했습니다.',
            ],
        ];
    }

    /**
     * 이용약관 생성 처리
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

        \DB::table('user_terms')->insert([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'version' => $request->version,
            'pos' => $request->pos ?? 0,
            'enable' => $request->has('enable') ? (bool)$request->enable : true,
            'required' => $request->has('required') ? (bool)$request->required : true,
            'valid_from' => $request->valid_from ? \Carbon\Carbon::parse($request->valid_from) : null,
            'valid_to' => $request->valid_to ? \Carbon\Carbon::parse($request->valid_to) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
