<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserCountry;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UpdateController extends Controller
{
    protected $actions;

    public function __construct()
    {
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
        $country = DB::table('user_country')->where('id', $id)->first();
        if (!$country) {
            return redirect()->route('admin.auth.user.countries.index')
                ->with('error', '국가를 찾을 수 없습니다.');
        }
        // Validation 규칙에서 {id}를 실제 ID로 치환
        $validationRules = $this->actions['validation'];
        if (isset($validationRules['code']) && strpos($validationRules['code'], '{id}') !== false) {
            $validationRules['code'] = str_replace('{id}', $id, $validationRules['code']);
        }
        
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }
        DB::table('user_country')->where('id', $id)->update([
            'code' => $request->code,
            'name' => $request->name,
            'emoji' => $request->emoji ?? null,
            'flag' => $request->flag ?? null,
            'description' => $request->description ?? null,
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
            'enable' => $request->enable ?? '1',
            'updated_at' => now(),
        ]);
        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
