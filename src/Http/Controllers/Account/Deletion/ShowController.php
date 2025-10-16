<?php

namespace Jiny\Auth\Http\Controllers\Account\Deletion;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Services\AccountDeletionService;

/**
 * 회원 탈퇴 폼 표시 컨트롤러
 *
 * 진입 경로:
 * Route::get('/account/delete') → ShowController::__invoke()
 *     ├─ 1. loadConfig() - 설정값 로드
 *     ├─ 2. checkDeletionEnabled() - 탈퇴 기능 활성화 확인
 *     ├─ 3. checkExistingRequest() - 기존 탈퇴 신청 확인
 *     ├─ 4. prepareFormData() - 폼 데이터 준비
 *     └─ 5. renderView() - 뷰 렌더링
 */
class ShowController extends Controller
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
        $this->config['auto_delete_days'] = config('admin.auth.account_deletion.auto_delete_days', 30);
        $this->config['require_password_confirm'] = config('admin.auth.account_deletion.require_password_confirm', true);
        $this->config['create_backup'] = config('admin.auth.account_deletion.create_backup', true);
        $this->config['backup_retention_days'] = config('admin.auth.account_deletion.backup_retention_days', 90);
        $this->config['form_view'] = config('admin.auth.account_deletion.form_view', 'jiny-auth::account.deletion.form');
    }

    /**
     * 탈퇴 폼 표시 (메인 진입점)
     *
     * 호출 흐름:
     * __invoke()
     *     ├─ checkDeletionEnabled()
     *     ├─ checkExistingRequest()
     *     ├─ prepareFormData()
     *     └─ renderView()
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 1단계: 탈퇴 기능 활성화 확인
        if (!$this->checkDeletionEnabled()) {
            return redirect()->route('home')
                ->with('error', '회원 탈퇴 기능이 비활성화되었습니다.');
        }

        // 2단계: 기존 탈퇴 신청 확인
        $existingCheck = $this->checkExistingRequest();
        if ($existingCheck !== true) {
            return $existingCheck;
        }

        // 3단계: 폼 데이터 준비
        $formData = $this->prepareFormData();

        // 4단계: 뷰 렌더링
        return $this->renderView($formData);
    }

    /**
     * [1단계] 탈퇴 기능 활성화 확인
     *
     * 진입: __invoke() → checkDeletionEnabled()
     *
     * @return bool
     */
    protected function checkDeletionEnabled()
    {
        return $this->config['deletion_enabled'];
    }

    /**
     * [2단계] 기존 탈퇴 신청 확인
     *
     * 진입: __invoke() → checkExistingRequest()
     *
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    protected function checkExistingRequest()
    {
        $user = Auth::user();

        $deletionStatus = $this->deletionService->getDeletionStatus($user->uuid);

        if ($deletionStatus) {
            return redirect()->route('account.deletion.status')
                ->with('info', '이미 탈퇴 신청이 진행 중입니다.');
        }

        return true;
    }

    /**
     * [3단계] 폼 데이터 준비
     *
     * 진입: __invoke() → prepareFormData()
     *
     * @return array
     */
    protected function prepareFormData()
    {
        return [
            'require_approval' => $this->config['require_approval'],
            'auto_delete_days' => $this->config['auto_delete_days'],
            'require_password_confirm' => $this->config['require_password_confirm'],
            'create_backup' => $this->config['create_backup'],
            'backup_retention_days' => $this->config['backup_retention_days'],
        ];
    }

    /**
     * [4단계] 뷰 렌더링
     *
     * 진입: __invoke() → renderView()
     *
     * @param array $formData
     * @return \Illuminate\View\View
     */
    protected function renderView($formData)
    {
        return view($this->config['form_view'], $formData);
    }
}