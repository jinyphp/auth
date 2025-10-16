<?php

namespace Jiny\Auth\Http\Controllers\Account\Deletion;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

/**
 * 회원 탈퇴 신청 완료 페이지 컨트롤러
 *
 * 진입 경로:
 * Route::get('/account/deletion/requested') → RequestedController::__invoke()
 */
class RequestedController extends Controller
{
    protected $config;

    /**
     * 생성자 - config 값을 프로퍼티로 로드
     */
    public function __construct()
    {
        // config 로드
        $this->loadConfig();
    }

    /**
     * [초기화] config 값을 프로퍼티로 로드
     */
    protected function loadConfig()
    {
        $this->config['requested_view'] = config('admin.auth.account_deletion.requested_view', 'jiny-auth::account.deletion.pending');
        $this->config['require_approval'] = config('admin.auth.account_deletion.require_approval', false);
    }

    /**
     * 탈퇴 신청 완료 페이지 표시
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $deletionInfo = session('deletion_info');

        if (!$deletionInfo) {
            return redirect()->route('home');
        }

        return view($this->config['requested_view'], [
            'deletion_info' => $deletionInfo,
            'require_approval' => $this->config['require_approval'],
        ]);
    }
}