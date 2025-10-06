<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\UserType;
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
     *
     * 처리 순서:
     * 1. Validation (utype은 user_type 테이블에 존재하는지 확인)
     * 2. 사용자명 자동 생성 (없으면 이메일 앞부분 사용)
     * 3. 비밀번호 해싱
     * 4. AuthUser::create() 호출 (샤딩 자동 처리)
     * 5. UserType 카운트 증가
     *
     * 샤딩 처리 (AuthUser 모델에서 자동):
     * - email → hash → shard_id 결정
     * - UUID 자동 생성
     * - sharding_enabled = true → users_00X 테이블에 저장
     * - sharding_enabled = false → users 테이블에 저장
     */
    public function __invoke(Request $request)
    {
        // 비밀번호 규칙 가져오기
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

        // Validation 규칙
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => $passwordValidation,
            'utype' => 'required|string|exists:user_type,type',
            'account_status' => 'required|in:active,inactive,suspended',
        ];

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

        // Validation
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'])
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['_token', 'phone_number', 'phone', 'address', 'avatar']);

        // 사용자명 자동 생성 (없으면 이메일에서 생성)
        if (empty($data['username'])) {
            $emailParts = explode('@', $data['email']);
            $baseUsername = $emailParts[0];

            // 중복 체크 후 고유한 username 생성
            $username = $baseUsername;
            $counter = 1;
            while (AuthUser::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $data['username'] = $username;
        }

        // 비밀번호 해싱
        $data['password'] = Hash::make($data['password']);

        // isAdmin 설정 (ADM 타입이면 관리자)
        $data['isAdmin'] = ($data['utype'] === 'ADM') ? '1' : '0';

        try {
            // 사용자 생성 (샤딩 자동 처리)
            $user = AuthUser::create($data);

            // UserType 카운트 증가
            if (isset($data['utype'])) {
                $userType = UserType::where('type', $data['utype'])->first();
                if ($userType) {
                    $userType->incrementUsers();
                }
            }

            // 성공 메시지에 샤드 정보 포함
            $successMessage = $this->actions['messages']['success'];

            // 샤딩이 활성화되어 있고 shard_id가 있으면 샤드 테이블 정보 추가
            if ($user->shard_id) {
                $shardTableName = 'users_' . str_pad($user->shard_id, 3, '0', STR_PAD_LEFT);
                $successMessage .= " (샤드 테이블: {$shardTableName})";
            }

            return redirect()
                ->route($this->actions['routes']['success'])
                ->with('success', $successMessage);

        } catch (\Illuminate\Database\QueryException $e) {
            // UNIQUE constraint violation 처리
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed') && str_contains($e->getMessage(), '.email')) {
                return redirect()
                    ->route($this->actions['routes']['error'])
                    ->withInput()
                    ->withErrors(['email' => '이미 등록된 이메일 주소입니다. 다른 이메일을 사용해주세요.']);
            }

            if (str_contains($e->getMessage(), 'UNIQUE constraint failed') && str_contains($e->getMessage(), '.username')) {
                return redirect()
                    ->route($this->actions['routes']['error'])
                    ->withInput()
                    ->withErrors(['username' => '이미 사용 중인 사용자명입니다. 다른 사용자명을 입력해주세요.']);
            }

            // 기타 DB 오류
            return redirect()
                ->route($this->actions['routes']['error'])
                ->withInput()
                ->withErrors(['error' => '사용자 생성 중 오류가 발생했습니다: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            // 일반 예외 처리
            return redirect()
                ->route($this->actions['routes']['error'])
                ->withInput()
                ->withErrors(['error' => '사용자 생성 중 예상치 못한 오류가 발생했습니다.']);
        }
    }
}