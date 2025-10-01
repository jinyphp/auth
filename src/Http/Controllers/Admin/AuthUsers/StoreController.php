<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 사용자 생성 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/auth-users') → StoreController::__invoke()
 */
class StoreController extends Controller
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
        $configPath = __DIR__ . '/AuthUser.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $storeConfig = $jsonConfig['store'] ?? [];

        $this->actions = [
            'validation' => $storeConfig['validation'] ?? [],
            'routes' => [
                'success' => $storeConfig['redirect']['success'] ?? 'admin.auth.users.index',
                'error' => $storeConfig['redirect']['error'] ?? 'admin.auth.users.create',
            ],
            'messages' => [
                'success' => $storeConfig['messages']['success'] ?? '사용자가 성공적으로 생성되었습니다.',
                'error' => $storeConfig['messages']['error'] ?? '사용자 생성에 실패했습니다.',
            ],
            'storage' => [
                'avatar_path' => $jsonConfig['storage']['avatar']['path'] ?? 'public/avatars',
                'avatar_public' => $jsonConfig['storage']['avatar']['public_path'] ?? 'avatars',
            ],
        ];
    }

    /**
     * 사용자 생성 처리
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
        $data['password'] = Hash::make($data['password']);

        // 아바타 이미지 처리
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = time() . '_' . $avatar->getClientOriginalName();
            $avatar->storeAs($this->actions['storage']['avatar_path'], $filename);
            $data['avatar'] = $this->actions['storage']['avatar_public'] . '/' . $filename;
        }

        AuthUser::create($data);

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}