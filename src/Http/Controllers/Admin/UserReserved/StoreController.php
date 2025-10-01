<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserReserved;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadActions();
    }

    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserReserved.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);
        $storeConfig = $jsonConfig['store'] ?? [];
        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.user.reserved.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.user.reserved.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '예약 키워드가 성공적으로 생성되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '예약 키워드 생성에 실패했습니다.',
            ],
        ];
    }

    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), $this->actions['validation']);
        if ($validator->fails()) {
            return redirect()->route($this->actions['routes']['error'])->withErrors($validator)->withInput();
        }
        \DB::table('user_reserved')->insert([
            'keyword' => $request->keyword,
            'type' => $request->type,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route($this->actions['routes']['success'])->with('success', $this->actions['messages']['success']);
    }
}
