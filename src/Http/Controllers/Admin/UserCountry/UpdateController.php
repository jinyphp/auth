<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserCountry;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadActions();
    }

    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserCountry.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $updateConfig = $jsonConfig['update'] ?? [];
        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.countries.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.countries.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '국가가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '국가 업데이트에 실패했습니다.',
            ],
        ];
    }

    public function __invoke(Request $request, $id)
    {
        $country = \DB::table('user_country')->where('id', $id)->first();
        if (!$country) {
            return redirect()->route('admin.auth.user.countries.index')
                ->with('error', '국가를 찾을 수 없습니다.');
        }
        $validator = Validator::make($request->all(), $this->actions['validation']);
        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }
        \DB::table('user_country')->where('id', $id)->update([
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
