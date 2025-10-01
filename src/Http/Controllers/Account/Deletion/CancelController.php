<?php

namespace Jiny\Auth\Http\Controllers\Account\Deletion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Services\AccountDeletionService;

/**
 * 회원 탈퇴 신청 취소 컨트롤러
 *
 * 진입 경로:
 * Route::post('/account/deletion/cancel') → CancelController::__invoke()
 *     ├─ 1. loadConfig() - 설정값 로드
 *     ├─ 2. getDeletionStatus() - 탈퇴 신청 확인
 *     ├─ 3. validateCancellation() - 취소 가능 여부 확인
 *     ├─ 4. cancelDeletion() - 탈퇴 취소 처리
 *     └─ 5. generateResponse() - 응답 생성
 */
class CancelController extends Controller
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
        $this->config['allow_cancel'] = config('admin.auth.account_deletion.allow_cancel', true);
        $this->config['redirect_after_cancel'] = config('admin.auth.home.default_route', '/home');
    }

    /**
     * 탈퇴 취소 처리 (메인 진입점)
     *
     * 호출 흐름:
     * __invoke()
     *     ├─ getDeletionStatus()
     *     ├─ validateCancellation()
     *     ├─ cancelDeletion()
     *     └─ generateResponse()
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // 1단계: 탈퇴 신청 확인
        $deletionStatus = $this->getDeletionStatus($user);

        if (!$deletionStatus) {
            return redirect()->route('home')
                ->with('error', '탈퇴 신청 내역이 없습니다.');
        }

        // 2단계: 취소 가능 여부 확인
        $validation = $this->validateCancellation($deletionStatus);
        if ($validation !== true) {
            return $validation;
        }

        // 3단계: 탈퇴 취소 처리
        try {
            $this->cancelDeletion($user);

            // 4단계: 응답 생성
            return $this->generateResponse();

        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * [1단계] 탈퇴 신청 상태 조회
     *
     * 진입: __invoke() → getDeletionStatus()
     *
     * @param $user
     * @return array|null
     */
    protected function getDeletionStatus($user)
    {
        return $this->deletionService->getDeletionStatus($user->uuid);
    }

    /**
     * [2단계] 취소 가능 여부 확인
     *
     * 진입: __invoke() → validateCancellation()
     *
     * @param array $deletionStatus
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    protected function validateCancellation($deletionStatus)
    {
        // 취소 기능 비활성화
        if (!$this->config['allow_cancel']) {
            return back()
                ->with('error', '탈퇴 취소가 허용되지 않습니다.');
        }

        // 이미 승인된 경우
        if ($deletionStatus['status'] !== 'pending') {
            return back()
                ->with('error', '이미 처리된 탈퇴 신청은 취소할 수 없습니다.');
        }

        return true;
    }

    /**
     * [3단계] 탈퇴 취소 처리
     *
     * 진입: __invoke() → cancelDeletion()
     *
     * @param $user
     * @return bool
     */
    protected function cancelDeletion($user)
    {
        return $this->deletionService->cancelDeletion($user->uuid);
    }

    /**
     * [4단계] 응답 생성
     *
     * 진입: __invoke() → generateResponse()
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function generateResponse()
    {
        return redirect($this->config['redirect_after_cancel'])
            ->with('success', '탈퇴 신청이 취소되었습니다.');
    }
}