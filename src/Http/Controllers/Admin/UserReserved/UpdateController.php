<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserReserved;

use App\Http\Controllers\Controller;
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
        $configPath = __DIR__ . '/UserReserved.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $updateConfig = $jsonConfig['update'] ?? [];
        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.reserved.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.reserved.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '예약 키워드 정보가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '예약 키워드 정보 업데이트에 실패했습니다.',
            ],
        ];
    }

    public function __invoke(Request $request, $id)
    {
        $reserved = \DB::table('user_reserved')->where('id', $id)->first();
        if (!$reserved) {
            return redirect()->route('admin.auth.user.reserved.index')->with('error', '예약 키워드를 찾을 수 없습니다.');
        }
        $validator = Validator::make($request->all(), $this->actions['validation']);
        if ($validator->fails()) {
            return redirect()->route($this->actions['routes']['error'], $id)->withErrors($validator)->withInput();
        }
        \DB::table('user_reserved')->where('id', $id)->update([
            'keyword' => $request->keyword,
            'type' => $request->type,
            'description' => $request->description,
            'updated_at' => now(),
        ]);
        return redirect()->route($this->actions['routes']['success'], $id)->with('success', $this->actions['messages']['success']);
    }
}
