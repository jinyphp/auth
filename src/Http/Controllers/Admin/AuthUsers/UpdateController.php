<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * 관리자 - 사용자 수정 처리 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/auth-users/{id}') → UpdateController::__invoke()
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
        $configPath = __DIR__ . '/AuthUser.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.users.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.users.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '사용자 정보가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '사용자 정보 업데이트에 실패했습니다.',
            ],
            'storage' => [
                'avatar_path' => $jsonConfig['storage']['avatar']['path'] ?? 'public/avatars',
                'avatar_public' => $jsonConfig['storage']['avatar']['public_path'] ?? 'avatars',
            ],
        ];
    }

    /**
     * 사용자 수정 처리
     */
    public function __invoke(Request $request, $id)
    {
        $user = AuthUser::findOrFail($id);

        // Validation rules with unique ignore
        $rules = $this->actions['validation'];
        $rules['username'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('users')->ignore($user->id),
        ];
        $rules['email'] = [
            'required',
            'string',
            'email',
            'max:255',
            Rule::unique('users')->ignore($user->id),
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['password', 'password_confirmation']);

        // 비밀번호가 제공된 경우에만 업데이트
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // 아바타 이미지 처리
        if ($request->hasFile('avatar')) {
            // 기존 아바타 삭제
            if ($user->avatar) {
                \Storage::delete($this->actions['storage']['avatar_path'] . '/' . basename($user->avatar));
            }

            $avatar = $request->file('avatar');
            $filename = time() . '_' . $avatar->getClientOriginalName();
            $avatar->storeAs($this->actions['storage']['avatar_path'], $filename);
            $data['avatar'] = $this->actions['storage']['avatar_public'] . '/' . $filename;
        }

        $user->update($data);

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}