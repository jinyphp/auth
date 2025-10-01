<?php

namespace Jiny\Auth\Http\Controllers\Account\Deletion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jiny\Auth\Services\AccountDeletionService;

/**
 * 회원 탈퇴 신청 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/account/delete') → StoreController::__invoke()
 *     ├─ 1. loadConfig() - 설정값 로드
 *     ├─ 2. validateInput() - 입력값 검증
 *     ├─ 3. verifyPassword() - 비밀번호 확인
 *     ├─ 4. checkExistingRequest() - 중복 신청 확인
 *     ├─ 5. requestDeletion() - 탈퇴 신청 처리
 *     ├─ 6. performLogout() - 로그아웃 처리
 *     └─ 7. generateResponse() - 응답 생성
 */
class StoreController extends Controller
{
    protected $deletionService;
    protected $config;

    /**
     * 생성자 - config 값을 프로퍼티로 로드
     */
    public function __construct(AccountDeletionService $deletionService)
    {
        $this->deletionService = $deletionService;
        $this->middleware('auth');

        // config 로드
        $this->loadConfig();
    }

    /**
     * [초기화] config 값을 프로퍼티로 로드
     *
     * 진입: __construct() → loadConfig()
     */
    protected function loadConfig()
    {
        $this->config['deletion_enabled'] = config('admin.auth.account_deletion.enable', true);
        $this->config['require_approval'] = config('admin.auth.account_deletion.require_approval', false);
        $this->config['require_password_confirm'] = config('admin.auth.account_deletion.require_password_confirm', true);
        $this->config['requested_view'] = config('admin.auth.account_deletion.requested_view', 'jiny-auth::account.deletion.requested');
    }

    /**
     * 탈퇴 신청 처리 (메인 진입점)
     *
     * 호출 흐름:
     * __invoke()
     *     ├─ validateInput()
     *     ├─ verifyPassword()
     *     ├─ checkExistingRequest()
     *     ├─ requestDeletion()
     *     ├─ performLogout()
     *     └─ generateResponse()
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 1단계: 입력값 검증
        $validation = $this->validateInput($request);
        if ($validation !== true) {
            return $validation;
        }

        // 2단계: 비밀번호 확인
        if ($this->config['require_password_confirm']) {
            $passwordCheck = $this->verifyPassword($request);
            if ($passwordCheck !== true) {
                return $passwordCheck;
            }
        }

        $user = Auth::user();

        // 3단계: 중복 신청 확인
        $existingCheck = $this->checkExistingRequest($user);
        if ($existingCheck !== true) {
            return $existingCheck;
        }

        // 4단계: 탈퇴 신청 처리
        try {
            $result = $this->requestDeletion($user, $request);

            // 5단계: 로그아웃 처리
            $this->performLogout($request);

            // 6단계: 응답 생성
            return $this->generateResponse($result);

        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * [1단계] 입력값 검증
     *
     * 진입: __invoke() → validateInput()
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    protected function validateInput(Request $request)
    {
        try {
            $rules = [
                'reason' => 'nullable|string|max:1000',
                'confirm' => 'required|accepted',
            ];

            if ($this->config['require_password_confirm']) {
                $rules['password'] = 'required|string';
            }

            $request->validate($rules, [
                'password.required' => '비밀번호를 입력해주세요.',
                'confirm.accepted' => '탈퇴 안내사항에 동의해야 합니다.',
            ]);

            return true;

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * [2단계] 비밀번호 확인
     *
     * 진입: __invoke() → verifyPassword()
     *
     * @param Request $request
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    protected function verifyPassword(Request $request)
    {
        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['password' => '비밀번호가 올바르지 않습니다.'])
                ->withInput();
        }

        return true;
    }

    /**
     * [3단계] 중복 신청 확인
     *
     * 진입: __invoke() → checkExistingRequest()
     *
     * @param $user
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    protected function checkExistingRequest($user)
    {
        $deletionStatus = $this->deletionService->getDeletionStatus($user->uuid);

        if ($deletionStatus) {
            return redirect()->route('account.deletion.status')
                ->with('info', '이미 탈퇴 신청이 진행 중입니다.');
        }

        return true;
    }

    /**
     * [4단계] 탈퇴 신청 처리
     *
     * 진입: __invoke() → requestDeletion()
     *
     * @param $user
     * @param Request $request
     * @return array
     */
    protected function requestDeletion($user, Request $request)
    {
        return $this->deletionService->requestDeletion(
            $user->uuid,
            $request->reason,
            $request->ip()
        );
    }

    /**
     * [5단계] 로그아웃 처리
     *
     * 진입: __invoke() → performLogout()
     *
     * @param Request $request
     */
    protected function performLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * [6단계] 응답 생성
     *
     * 진입: __invoke() → generateResponse()
     *
     * @param array $result
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function generateResponse($result)
    {
        return redirect()->route('account.deletion.requested')
            ->with('deletion_info', $result);
    }
}