<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLanguage;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->loadActions();
    }

    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserLanguage.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $updateConfig = $jsonConfig['update'] ?? [];
        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.languages.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.languages.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '언어가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '언어 업데이트에 실패했습니다.',
            ],
        ];
    }

    public function __invoke(Request $request, $id)
    {
        $language = \DB::table('user_language')->where('id', $id)->first();
        if (!$language) {
            return redirect()->route('admin.auth.user.languages.index')
                ->with('error', '언어를 찾을 수 없습니다.');
        }
        $validator = Validator::make($request->all(), $this->actions['validation']);
        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }
        \DB::table('user_language')->where('id', $id)->update([
            'code' => $request->code,
            'name' => $request->name,
            'flag' => $request->flag,
            'enable' => $request->enable ?? '1',
            'updated_at' => now(),
        ]);
        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
