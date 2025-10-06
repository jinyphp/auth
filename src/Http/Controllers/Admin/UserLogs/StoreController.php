<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 사용자 로그 생성 처리 컨트롤러
 */
class StoreController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->loadActions();
    }

    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserLogs.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $storeConfig = $jsonConfig['store'] ?? [];

        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.user.logs.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.user.logs.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '로그가 성공적으로 생성되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '로그 생성에 실패했습니다.',
            ],
        ];
    }

    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'])
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_logs')->insert([
            'user_id' => $request->user_id,
            'provider' => $request->provider,
            'ref' => $request->ref,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
