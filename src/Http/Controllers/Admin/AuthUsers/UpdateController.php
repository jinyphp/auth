<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Models\UserType;
use Illuminate\Support\Facades\DB;
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
        $shardId = $request->get('shard_id');
        $tableName = 'users';

        if ($shardId) {
            // 샤드 테이블에서 조회
            $shardTable = ShardTable::where('table_name', 'users')->first();
            $tableName = $shardTable->getShardTableName($shardId);

            $userData = DB::table($tableName)->where('id', $id)->first();

            if (!$userData) {
                abort(404, '사용자를 찾을 수 없습니다.');
            }

            $user = AuthUser::hydrate([(array)$userData])->first();
            $user->setTable($tableName);
        } else {
            // 일반 테이블에서 조회
            $user = AuthUser::findOrFail($id);
        }

        // Validation rules with unique ignore
        $rules = [
            'name' => 'required|string|max:255',
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique($tableName)->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique($tableName)->ignore($user->id),
            ],
            'utype' => 'required|string|exists:user_type,type',
            'account_status' => 'required|string|in:active,inactive,suspended',
        ];

        // 비밀번호가 제공된 경우에만 validation 추가
        if ($request->filled('password')) {
            $passwordRules = config('admin.auth.password_rules', [
                'min_length' => 8,
                'require_uppercase' => true,
                'require_lowercase' => true,
                'require_numbers' => true,
                'require_symbols' => false,
            ]);

            $passwordValidation = ['required', 'string', 'min:' . $passwordRules['min_length'], 'confirmed'];

            // 추가 규칙 적용
            if ($passwordRules['require_uppercase']) {
                $passwordValidation[] = 'regex:/[A-Z]/';
            }
            if ($passwordRules['require_lowercase']) {
                $passwordValidation[] = 'regex:/[a-z]/';
            }
            if ($passwordRules['require_numbers']) {
                $passwordValidation[] = 'regex:/[0-9]/';
            }
            if ($passwordRules['require_symbols']) {
                $passwordValidation[] = 'regex:/[!@#$%^&*(),.?":{}|<>]/';
            }

            $rules['password'] = $passwordValidation;

            // Custom validation messages
            $messages = [
                'password.regex' => '비밀번호는 ',
            ];

            if ($passwordRules['require_uppercase']) {
                $messages['password.regex'] .= '대문자, ';
            }
            if ($passwordRules['require_lowercase']) {
                $messages['password.regex'] .= '소문자, ';
            }
            if ($passwordRules['require_numbers']) {
                $messages['password.regex'] .= '숫자, ';
            }
            if ($passwordRules['require_symbols']) {
                $messages['password.regex'] .= '특수문자, ';
            }
            $messages['password.regex'] = rtrim($messages['password.regex'], ', ') . '를 포함해야 합니다.';
        } else {
            $messages = [];
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $redirectUrl = route($this->actions['routes']['error'], $id);
            if ($shardId) {
                $redirectUrl .= '?shard_id=' . $shardId;
            }
            return redirect($redirectUrl)
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['password', 'password_confirmation', 'shard_id', '_token', '_method', 'phone', 'phone_number', 'address', 'avatar']);

        // 비밀번호가 제공된 경우에만 업데이트
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // 사용자 타입 변경 시 UserType 카운트 조정
        $oldUtype = $user->utype;
        $newUtype = $request->get('utype');

        if ($oldUtype !== $newUtype) {
            // 이전 타입 카운트 감소
            if ($oldUtype) {
                $oldUserType = UserType::where('type', $oldUtype)->first();
                if ($oldUserType) {
                    $oldUserType->decrementUsers();
                }
            }

            // 새 타입 카운트 증가
            if ($newUtype) {
                $newUserType = UserType::where('type', $newUtype)->first();
                if ($newUserType) {
                    $newUserType->incrementUsers();
                }
            }
        }

        if ($shardId) {
            // 샤드 테이블 직접 업데이트
            DB::table($tableName)->where('id', $id)->update($data);
        } else {
            $user->update($data);
        }

        $redirectUrl = route($this->actions['routes']['success'], $id);
        if ($shardId) {
            $redirectUrl .= '?shard_id=' . $shardId;
        }

        return redirect($redirectUrl)
            ->with('success', $this->actions['messages']['success']);
    }
}