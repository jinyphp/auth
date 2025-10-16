<?php

namespace Jiny\Auth\Http\Controllers\Account\Deletion;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Services\AccountDeletionService;

/**
 * 회원 탈퇴 상태 확인 컨트롤러
 *
 * 진입 경로:
 * Route::get('/account/deletion/status') → StatusController::__invoke()
 *     ├─ 1. loadConfig() - 설정값 로드
 *     ├─ 2. getDeletionStatus() - 탈퇴 신청 상태 조회
 *     ├─ 3. prepareStatusData() - 상태 데이터 준비
 *     └─ 4. renderView() - 뷰 렌더링
 */
class StatusController extends Controller
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
        $this->config['status_view'] = config('admin.auth.account_deletion.status_view', 'jiny-auth::account.pending');
    }

    /**
     * 탈퇴 상태 확인 (메인 진입점)
     *
     * 호출 흐름:
     * __invoke()
     *     ├─ getDeletionStatus()
     *     ├─ prepareStatusData()
     *     └─ renderView()
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // 1단계: 탈퇴 신청 상태 조회
        $deletionStatus = $this->getDeletionStatus($user);

        if (!$deletionStatus) {
            return redirect()->route('account.delete')
                ->with('info', '탈퇴 신청 내역이 없습니다.');
        }

        // 2단계: 상태 데이터 준비
        $statusData = $this->prepareStatusData($deletionStatus);

        // 3단계: 뷰 렌더링
        return $this->renderView($statusData);
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
     * [2단계] 상태 데이터 준비
     *
     * 진입: __invoke() → prepareStatusData()
     *
     * @param array $deletionStatus
     * @return array
     */
    protected function prepareStatusData($deletionStatus)
    {
        return [
            'deletion' => $deletionStatus,
            'can_cancel' => $deletionStatus['status'] === 'pending',
        ];
    }

    /**
     * [3단계] 뷰 렌더링
     *
     * 진입: __invoke() → renderView()
     *
     * @param array $statusData
     * @return \Illuminate\View\View
     */
    protected function renderView($statusData)
    {
        return view($this->config['status_view'], $statusData);
    }
}